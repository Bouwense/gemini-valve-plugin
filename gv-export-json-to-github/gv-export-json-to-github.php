<?php
/**
 * Plugin Name: GV - Step Export to GitHub
 * Description: Step-based JSON export of WooCommerce & WP data to GitHub (with auto-next-step redirects).
 * Author: Gemini Valve
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =============================================================================
 * CONFIG
 * ========================================================================== */

// GitHub repo info
const GV_GH_OWNER      = 'Bouwense';
const GV_GH_REPO       = 'Gemini-Valve-BV';
const GV_GH_BRANCH     = 'main';

// Base path in repo for exports (folder must exist in repo!)
const GV_GH_BASE_PATH  = 'data';

// KB article post type (change if your KB plugin uses another slug)
const GV_KB_POST_TYPE  = 'kb_article';

// Batch sizes
const GV_PRODUCTS_PER_PAGE = 50;
const GV_IMAGES_PER_PAGE   = 150;

// Token comes from wp-config.php for safety
if ( ! defined( 'GV_GITHUB_TOKEN' ) ) {
    // Hard fail only for admins, others see nothing.
    if ( current_user_can( 'manage_options' ) ) {
        wp_die( 'GV GitHub export: GV_GITHUB_TOKEN is not defined in wp-config.php' );
    }
    return;
}
const GV_GH_TOKEN = GV_GITHUB_TOKEN;

/* =============================================================================
 * COMMON HELPERS
 * ========================================================================== */

/**
 * Build full repo path under GV_GH_BASE_PATH.
 */
function gv_gh_path( $file ) {
    return trim( GV_GH_BASE_PATH, '/' ) . '/' . ltrim( $file, '/' );
}

/**
 * Simple redirect page that auto-loads the next URL.
 */
function gv_export_redirect( $next_url, $message = 'Step completed, continuing…' ) {
    $next_url = esc_url_raw( $next_url );
    echo '<!doctype html><html><head><meta charset="utf-8"><title>GV Export</title></head><body>';
    echo '<p>' . esc_html( $message ) . '</p>';
    echo '<p>Next: <code>' . esc_html( $next_url ) . '</code></p>';
    echo '<script>setTimeout(function(){ window.location.href = ' . json_encode( $next_url ) . '; }, 500);</script>';
    echo '</body></html>';
    exit;
}

/**
 * Generic GitHub API call.
 */
function gv_gh_request( $method, $path, $args = [] ) {
    $url = 'https://api.github.com/repos/' . GV_GH_OWNER . '/' . GV_GH_REPO . '/' . ltrim( $path, '/' );

    $defaults = [
        'method'  => $method,
        'headers' => [
            'Authorization' => 'token ' . GV_GH_TOKEN,
            'User-Agent'    => 'geminivalve-wp',
            'Accept'        => 'application/vnd.github+json',
        ],
        'timeout' => 60,
    ];

    $response = wp_remote_request( $url, array_replace_recursive( $defaults, $args ) );

    if ( is_wp_error( $response ) ) {
        error_log( 'GV GitHub API error: ' . $response->get_error_message() );
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( $code >= 300 ) {
        error_log( 'GV GitHub API HTTP ' . $code . ': ' . $body );
    }

    return [
        'code' => $code,
        'body' => $body,
    ];
}

/**
 * Put a JSON file into the repo (create or update).
 */
function gv_gh_put_json( $relative_path, $json ) {

    $contents_path = 'contents/' . gv_gh_path( $relative_path );

    // Get SHA if file exists
    $existing = gv_gh_request( 'GET', $contents_path );

    $sha = null;
    if ( ! is_wp_error( $existing ) && isset( $existing['code'] ) && $existing['code'] === 200 ) {
        $data = json_decode( $existing['body'], true );
        if ( isset( $data['sha'] ) ) {
            $sha = $data['sha'];
        }
    }

    $payload = [
        'message' => 'GV export update: ' . $relative_path,
        'branch'  => GV_GH_BRANCH,
        'content' => base64_encode( $json ),
    ];
    if ( $sha ) {
        $payload['sha'] = $sha;
    }

    gv_gh_request( 'PUT', $contents_path, [
        'body' => wp_json_encode( $payload ),
    ] );
}

/**
 * Delete all files currently in GV_GH_BASE_PATH.
 */
function gv_gh_delete_all_in_export_folder() {

    $list = gv_gh_request( 'GET', 'contents/' . trim( GV_GH_BASE_PATH, '/' ) );

    if ( is_wp_error( $list ) ) {
        return;
    }
    if ( ! isset( $list['code'] ) || $list['code'] !== 200 ) {
        return;
    }

    $items = json_decode( $list['body'], true );
    if ( ! is_array( $items ) ) {
        return;
    }

    foreach ( $items as $item ) {
        if ( ! isset( $item['path'], $item['sha'] ) ) {
            continue;
        }

        gv_gh_request( 'DELETE', 'contents/' . $item['path'], [
            'body' => wp_json_encode( [
                'message' => 'GV export cleanup: delete ' . $item['path'],
                'sha'     => $item['sha'],
                'branch'  => GV_GH_BRANCH,
            ] ),
        ] );
    }
}

/* =============================================================================
 * EXPORTERS
 * ========================================================================== */

/**
 * Products – one page.
 * Exports core fields + ALL meta (incl. procurement data).
 */
function gv_export_products_page( $page ) {

    if ( ! function_exists( 'wc_get_product' ) ) {
        return [ 'items' => 0, 'max_pages' => 0 ];
    }

    $q = new WP_Query( [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'paged'          => max( 1, (int) $page ),
        'posts_per_page' => GV_PRODUCTS_PER_PAGE,
    ] );

    if ( ! $q->have_posts() ) {
        wp_reset_postdata();
        return [ 'items' => 0, 'max_pages' => $q->max_num_pages ];
    }

    $out = [];

    foreach ( $q->posts as $product_id ) {

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            continue;
        }

        // Categories (basic)
        $cat_terms = get_the_terms( $product_id, 'product_cat' );
        $cats      = [];
        if ( ! is_wp_error( $cat_terms ) && ! empty( $cat_terms ) ) {
            foreach ( $cat_terms as $t ) {
                $cats[] = [
                    'id'   => $t->term_id,
                    'slug' => $t->slug,
                    'name' => $t->name,
                ];
            }
        }

        // Images (basic)
        $image_ids   = $product->get_gallery_image_ids();
        $featured_id = $product->get_image_id();
        if ( $featured_id ) {
            array_unshift( $image_ids, $featured_id );
            $image_ids = array_unique( $image_ids );
        }
        $images = [];
        foreach ( $image_ids as $iid ) {
            $images[] = [
                'id'  => $iid,
                'url' => wp_get_attachment_url( $iid ),
            ];
        }

        // ALL meta (this includes your Procurement tab fields)
        $meta_raw = get_post_meta( $product_id );
        $meta     = [];
        foreach ( $meta_raw as $key => $values ) {
            // Flatten single values, keep arrays when needed
            if ( is_array( $values ) && count( $values ) === 1 ) {
                $meta[ $key ] = maybe_unserialize( $values[0] );
            } else {
                $meta[ $key ] = array_map( 'maybe_unserialize', $values );
            }
        }

        $out[] = [
            'id'           => $product_id,
            'sku'          => $product->get_sku(),
            'name'         => $product->get_name(),
            'slug'         => $product->get_slug(),
            'type'         => $product->get_type(),
            'status'       => $product->get_status(),
            'description'  => $product->get_description(),
            'short_desc'   => $product->get_short_description(),
            'price'        => $product->get_price(),
            'regular_price'=> $product->get_regular_price(),
            'sale_price'   => $product->get_sale_price(),
            'stock_status' => $product->get_stock_status(),
            'manage_stock' => $product->get_manage_stock(),
            'categories'   => $cats,
            'images'       => $images,
            'permalink'    => get_permalink( $product_id ),
            'meta'         => $meta, // includes procurement fields
        ];
    }

    wp_reset_postdata();

    $json = wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    gv_gh_put_json( 'products-page-' . $page . '.json', $json );

    return [ 'items' => count( $out ), 'max_pages' => $q->max_num_pages ];
}

/**
 * Images – one page.
 */
function gv_export_images_page( $page ) {

    $q = new WP_Query( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'fields'         => 'ids',
        'paged'          => max( 1, (int) $page ),
        'posts_per_page' => GV_IMAGES_PER_PAGE,
    ] );

    if ( ! $q->have_posts() ) {
        wp_reset_postdata();
        return [ 'items' => 0, 'max_pages' => $q->max_num_pages ];
    }

    $out = [];

    foreach ( $q->posts as $img_id ) {
        $meta = wp_get_attachment_metadata( $img_id );

        $out[] = [
            'id'       => $img_id,
            'title'    => get_the_title( $img_id ),
            'filename' => isset( $meta['file'] ) ? basename( $meta['file'] ) : '',
            'url'      => wp_get_attachment_url( $img_id ),
            'alt'      => get_post_meta( $img_id, '_wp_attachment_image_alt', true ),
            'width'    => isset( $meta['width'] )  ? $meta['width']  : null,
            'height'   => isset( $meta['height'] ) ? $meta['height'] : null,
        ];
    }

    wp_reset_postdata();

    $json = wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    gv_gh_put_json( 'images-page-' . $page . '.json', $json );

    return [ 'items' => count( $out ), 'max_pages' => $q->max_num_pages ];
}

/**
 * Product categories – single file.
 */
function gv_export_categories_all() {
    $terms = get_terms( [
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ] );

    if ( is_wp_error( $terms ) ) {
        $terms = [];
    }

    $out = [];
    foreach ( $terms as $t ) {
        $out[] = [
            'id'          => $t->term_id,
            'slug'        => $t->slug,
            'name'        => $t->name,
            'description' => $t->description,
            'parent'      => $t->parent,
        ];
    }

    $json = wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    gv_gh_put_json( 'product-categories.json', $json );
}

/**
 * Posts – single file.
 */
function gv_export_posts_all() {

    $posts = get_posts( [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'numberposts'    => -1,
    ] );

    $out = [];
    foreach ( $posts as $p ) {
        $out[] = [
            'id'        => $p->ID,
            'slug'      => $p->post_name,
            'title'     => get_the_title( $p ),
            'date'      => get_the_date( 'c', $p ),
            'modified'  => get_the_modified_date( 'c', $p ),
            'excerpt'   => get_the_excerpt( $p ),
            'content'   => apply_filters( 'the_content', $p->post_content ),
            'permalink' => get_permalink( $p ),
        ];
    }

    $json = wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    gv_gh_put_json( 'posts.json', $json );
}

/**
 * KB articles – single file.
 */
function gv_export_kb_all() {

    $posts = get_posts( [
        'post_type'      => GV_KB_POST_TYPE,
        'post_status'    => 'publish',
        'numberposts'    => -1,
    ] );

    if ( empty( $posts ) ) {
        gv_gh_put_json( 'kb-articles.json', '[]' );
        return;
    }

    $out = [];
    foreach ( $posts as $p ) {
        $out[] = [
            'id'        => $p->ID,
            'slug'      => $p->post_name,
            'title'     => get_the_title( $p ),
            'date'      => get_the_date( 'c', $p ),
            'modified'  => get_the_modified_date( 'c', $p ),
            'excerpt'   => get_the_excerpt( $p ),
            'content'   => apply_filters( 'the_content', $p->post_content ),
            'permalink' => get_permalink( $p ),
        ];
    }

    $json = wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    gv_gh_put_json( 'kb-articles.json', $json );
}

/* =============================================================================
 * CONTROLLER – STEP MACHINE WITH AUTO REDIRECTS
 * ========================================================================== */

function gv_handle_export_steps() {

    if ( ! isset( $_GET['gv_export'] ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'GV export: admin only.' );
    }

    // Increase PHP max execution time per step
    @set_time_limit( 300 );

    $step = sanitize_text_field( wp_unslash( $_GET['gv_export'] ) );
    $page = isset( $_GET['page'] ) ? max( 1, (int) $_GET['page'] ) : 1;

    $base = home_url( '/' );

    switch ( $step ) {

        case 'start':
            gv_export_redirect(
                add_query_arg( 'gv_export', 'cleanup', $base ),
                'Starting export: cleaning up previous files…'
            );
            break;

        case 'cleanup':
            gv_gh_delete_all_in_export_folder();
            gv_export_redirect(
                add_query_arg(
                    [ 'gv_export' => 'products', 'page' => 1 ],
                    $base
                ),
                'Cleanup done. Exporting products (page 1)…'
            );
            break;

        case 'products':
            $result = gv_export_products_page( $page );
            if ( $result['items'] === 0 || $page >= $result['max_pages'] ) {
                gv_export_redirect(
                    add_query_arg( [ 'gv_export' => 'images', 'page' => 1 ], $base ),
                    "Products page $page done (last). Continue with images…"
                );
            } else {
                gv_export_redirect(
                    add_query_arg(
                        [ 'gv_export' => 'products', 'page' => $page + 1 ],
                        $base
                    ),
                    "Products page $page exported ({$result['items']} items). Next page…"
                );
            }
            break;

        case 'images':
            $result = gv_export_images_page( $page );
            if ( $result['items'] === 0 || $page >= $result['max_pages'] ) {
                gv_export_redirect(
                    add_query_arg( 'gv_export', 'categories', $base ),
                    "Images page $page done (last). Continue with categories…"
                );
            } else {
                gv_export_redirect(
                    add_query_arg(
                        [ 'gv_export' => 'images', 'page' => $page + 1 ],
                        $base
                    ),
                    "Images page $page exported ({$result['items']} items). Next page…"
                );
            }
            break;

        case 'categories':
            gv_export_categories_all();
            gv_export_redirect(
                add_query_arg( 'gv_export', 'posts', $base ),
                'Categories exported. Continue with posts…'
            );
            break;

        case 'posts':
            gv_export_posts_all();
            gv_export_redirect(
                add_query_arg( 'gv_export', 'kb', $base ),
                'Posts exported. Continue with KB articles…'
            );
            break;

        case 'kb':
            gv_export_kb_all();
            echo '<!doctype html><html><head><meta charset="utf-8"><title>GV Export</title></head><body>';
            echo '<h1>Export complete ✅</h1>';
            echo '<p>All steps finished. Check your GitHub repo under <code>' . esc_html( GV_GH_BASE_PATH ) . '/</code>.</p>';
            echo '</body></html>';
            exit;

        default:
            wp_die( 'Unknown gv_export step.' );
    }
}
add_action( 'init', 'gv_handle_export_steps' );
