<?php
/**
 * Plugin Name: GV – Bulk Edit: Enrich Image
 * Description: Finds/attaches product images for WooCommerce products that have no featured image. Menu: Bulk Edit → Enrich Image.
 * Version: 1.0.21
 * Author: Gemini Valve
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class GV_Bulk_Edit_Enrich_Image {
    const SLUG     = 'gv-bulk-edit-enrich-image';
    const CAP      = 'manage_woocommerce';
    const PER_PAGE = 20;

    private $hook_suffix = '';
    private $parent_slug = 'gv-bulk-edit'; // Your “Bulk Edit” parent menu

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menu' ], 51 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );

        // AJAX endpoints
        add_action( 'wp_ajax_gv_enrich_image_suggest', [ $this, 'ajax_suggest' ] );
        add_action( 'wp_ajax_gv_enrich_image_import',  [ $this, 'ajax_import' ] );
        add_action( 'wp_ajax_gv_enrich_image_bulk',    [ $this, 'ajax_bulk' ] );
    }

    /* ----------------------------- Menu --------------------------------- */

    public function register_menu() {
        $parent = $this->detect_parent_menu_slug();

        $this->hook_suffix = add_submenu_page(
            $parent,
            __( 'Enrich Image', 'gv' ),
            __( 'Enrich Image', 'gv' ),
            self::CAP,
            self::SLUG,
            [ $this, 'render' ],
            30
        );
    }

    private function detect_parent_menu_slug() {
        global $menu;
        foreach ( (array) $menu as $m ) {
            if ( isset( $m[2] ) && $m[2] === $this->parent_slug ) {
                return $this->parent_slug;
            }
        }
        // Fallback under Products if custom parent isn't there
        return 'edit.php?post_type=product';
    }

    /* ---------------------------- Assets -------------------------------- */

    public function enqueue( $hook ) {
        // Load assets only on our page (robust check)
        $is_our_page = ($hook === $this->hook_suffix)
            || ( isset($_GET['page']) && $_GET['page'] === self::SLUG )
            || ( strpos($hook ?? '', self::SLUG) !== false );

        if ( ! $is_our_page ) return;

        $css_path = plugin_dir_path(__FILE__) . 'assets/enrich-image.css';
        $js_path  = plugin_dir_path(__FILE__) . 'assets/enrich-image.js';
        $css_ver  = file_exists($css_path) ? filemtime($css_path) : '1.0.0';
        $js_ver   = file_exists($js_path)  ? filemtime($js_path)  : '1.0.0';

        wp_enqueue_style(
            'gv-ei-css',
            plugin_dir_url(__FILE__) . 'assets/enrich-image.css',
            [],
            $css_ver
        );
        wp_enqueue_script(
            'gv-ei-js',
            plugin_dir_url(__FILE__) . 'assets/enrich-image.js',
            [ 'jquery' ],
            $js_ver,
            true
        );
        wp_localize_script( 'gv-ei-js', 'GV_EI', [
            'ajax'  => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'gv_ei' ),
        ] );
    }

    /* ----------------------------- Page --------------------------------- */

    public function render() {
        if ( ! current_user_can( self::CAP ) ) {
            wp_die( __( 'You do not have permission to view this page.', 'gv' ) );
        }

        $s     = isset($_GET['s']) ? sanitize_text_field( wp_unslash($_GET['s']) ) : '';
        $paged = max( 1, absint( $_GET['paged'] ?? 1 ) );

        $args = [
            'post_type'      => 'product',
            'post_status'    => [ 'publish','draft','pending','private' ],
            'posts_per_page' => self::PER_PAGE,
            'paged'          => $paged,
            's'              => $s,
            'meta_query'     => [
                'relation' => 'OR',
                [ 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ],
                [ 'key' => '_thumbnail_id', 'value'   => '',  'compare' => '=' ],
                [ 'key' => '_thumbnail_id', 'value'   => '0', 'compare' => '=' ],
            ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        $q = new WP_Query( $args );

        echo '<div class="wrap gv-ei-wrap">';
        echo '<h1>'.esc_html__('Enrich Image','gv').'</h1>';

        // Filter/search form (GET)
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="'.esc_attr(self::SLUG).'">';
        echo '<input type="search" name="s" value="'.esc_attr($s).'" placeholder="'.esc_attr__('Search products…','gv').'" />';
        submit_button( __('Filter','gv'), 'secondary', '', false );
        echo '</form>';

        // Nonce (available for any snippet fallback) + bulk toolbar wrapper (no real form to avoid submit)
        $nonce = wp_create_nonce( 'gv_ei' );
        echo '<div id="gv-ei-bulk-form" data-nonce="'.esc_attr($nonce).'">';
        echo '<div class="gv-ei-bulkbar">';
        echo '<button type="button" class="button" id="gv-ei-bulk-scan">'.esc_html__('Scan selected','gv').'</button>';
        echo '<button type="button" class="button button-primary" id="gv-ei-bulk-import">'.esc_html__('Import & set image for selected','gv').'</button>';
        echo '</div>';

        echo '<table class="widefat fixed striped gv-ei-table"><thead><tr>';
        echo '<th class="check-col"><input type="checkbox" class="gv-ei-check-all" /></th>';
        echo '<th>'.esc_html__('Product','gv').'</th>';
        echo '<th>'.esc_html__('SKU','gv').'</th>';
        echo '<th>'.esc_html__('Suggestion','gv').'</th>';
        echo '<th>'.esc_html__('Actions','gv').'</th>';
        echo '</tr></thead><tbody>';

        if ( $q->have_posts() ) {
            foreach ( $q->posts as $p ) {
                $product = wc_get_product( $p->ID );
                if ( ! $product ) continue;
                $pid  = $product->get_id();
                $sku  = $product->get_sku();
                $edit = get_edit_post_link( $pid, '' );
                $view = get_permalink( $pid );

                echo '<tr data-id="'.esc_attr($pid).'">';
                echo '<td class="check-col"><input type="checkbox" class="gv-ei-row-check" value="'.esc_attr($pid).'"></td>';
                echo '<td><strong><a href="'.esc_url($edit).'">'.esc_html($product->get_name()).'</a></strong> ';
                echo '<div class="row-actions"><a href="'.esc_url($view).'" target="_blank">'.esc_html__('View','gv').'</a></div>';
                echo '</td>';
                echo '<td>'.esc_html($sku ?: '—').'</td>';
                echo '<td class="gv-ei-suggestion"><em>'.esc_html__('Not scanned yet','gv').'</em></td>';
                echo '<td class="gv-ei-actions">';
                echo '<button type="button" class="button gv-ei-scan">'.esc_html__('Find image','gv').'</button> ';
                echo '<button type="button" class="button button-primary gv-ei-import" disabled>'.esc_html__('Import & set','gv').'</button> ';
                echo '<span class="spinner" style="float:none;"></span>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">'.esc_html__('No products without image found (based on current filter).','gv').'</td></tr>';
        }

        echo '</tbody></table>';

        // Pagination
        $total = max( 1, ceil( ($q->found_posts ?: 0) / self::PER_PAGE ) );
        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links([
            'base'      => add_query_arg( array_merge($_GET, ['paged' => '%#%']) ),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ]);
        echo '</div></div>';

        echo '</div>'; // #gv-ei-bulk-form
        echo '</div>'; // .wrap
    }

    /* ----------------------- Suggestion logic --------------------------- */

    /**
     * Suggest a best image:
     *  1) Look in Media Library for likely matches by title/SKU/alt.
     *  2) If none, allow an external provider via `gv_enrich_image_find` (e.g., OpenAI connector).
     * Return array:
     *   - media:   ['type'=>'media','attachment_id'=>ID,'thumb'=>url,'title'=>...,'source'=>'media']
     *   - external:['type'=>'external','url'=>...,'thumb'=>...,'source'=>'supplier|brand|geminivalve|web','title'=>...]
     */
    private function find_best_suggestion( $product_id ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) return null;

        // 1) Local media search
        $media = $this->search_media_for_product( $product );
        if ( $media ) {
            return [
                'type'          => 'media',
                'attachment_id' => $media['id'],
                'thumb'         => $media['thumb'],
                'title'         => get_the_title( $media['id'] ),
                'source'        => 'media',
            ];
        }

        // 2) External via hook (e.g., OpenAI connector)
        $ai = apply_filters( 'gv_enrich_image_find', null, $product_id, $product );
        if ( is_array($ai) && ! empty($ai['type']) ) {
            if ( $ai['type'] === 'external' && ! empty($ai['url']) ) {
                $out = [
                    'type'   => 'external',
                    'url'    => esc_url_raw( $ai['url'] ),
                    'thumb'  => ! empty($ai['thumb']) ? esc_url_raw($ai['thumb']) : esc_url_raw($ai['url']),
                    'source' => ! empty($ai['source']) ? sanitize_text_field($ai['source']) : 'web',
                    'title'  => ! empty($ai['title'])  ? sanitize_text_field($ai['title'])  : $product->get_name(),
                ];
                return $out['url'] ? $out : null;
            } elseif ( $ai['type'] === 'media' && ! empty($ai['attachment_id']) && wp_attachment_is_image( $ai['attachment_id'] ) ) {
                return [
                    'type'          => 'media',
                    'attachment_id' => absint($ai['attachment_id']),
                    'thumb'         => wp_get_attachment_image_url( $ai['attachment_id'], 'thumbnail' ),
                    'title'         => get_the_title( $ai['attachment_id'] ),
                    'source'        => 'media',
                ];
            }
        }
        return null;
    }

    private function search_media_for_product( WC_Product $product ) {
        $tokens = array_filter( array_unique( array_map( 'trim', array_merge(
            preg_split('/[\s\-_,]+/', (string) $product->get_name() ),
            [ (string) $product->get_sku() ]
        ) ) ) );

        $tokens = array_values( array_filter( $tokens, function($t){ return mb_strlen($t) >= 3; } ) );
        if ( empty( $tokens ) ) return null;

        $q = new WP_Query([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 8,
            'post_mime_type' => 'image',
            's'              => implode(' ', $tokens),
        ]);

        if ( $q->have_posts() ) {
            $candidates = [];
            foreach ( $q->posts as $att ) {
                if ( ! wp_attachment_is_image( $att->ID ) ) continue;
                $alt = get_post_meta( $att->ID, '_wp_attachment_image_alt', true );
                $hay = strtolower( $att->post_title . ' ' . $alt );
                $score = 0;
                foreach ( $tokens as $t ) {
                    if ( false !== mb_stripos( $hay, $t ) ) $score++;
                }
                $candidates[] = [
                    'id'    => $att->ID,
                    'score' => $score,
                    'thumb' => wp_get_attachment_image_url( $att->ID, 'thumbnail' ),
                ];
            }
            if ( $candidates ) {
                usort( $candidates, fn($a,$b) => $b['score'] <=> $a['score'] );
                return $candidates[0];
            }
        }
        return null;
    }

    /* ----------------------------- AJAX -------------------------------- */

    public function ajax_suggest() {
        check_ajax_referer( 'gv_ei', 'nonce' );
        if ( ! current_user_can( self::CAP ) ) wp_send_json_error([ 'message' => 'Permission denied' ], 403);

        $pid = absint( $_POST['id'] ?? 0 );
        if ( ! $pid ) wp_send_json_error([ 'message' => 'Bad request' ], 400);

        $sug = $this->find_best_suggestion( $pid );
        if ( ! $sug ) {
            wp_send_json_success([ 'found' => false ]);
        } else {
            wp_send_json_success([ 'found' => true, 'suggestion' => $sug ]);
        }
    }

    public function ajax_import() {
        check_ajax_referer( 'gv_ei', 'nonce' );
        if ( ! current_user_can( self::CAP ) ) wp_send_json_error([ 'message' => 'Permission denied' ], 403);

        $pid  = absint( $_POST['id'] ?? 0 );
        $type = sanitize_text_field( $_POST['type'] ?? '' );
        if ( ! $pid || ! in_array($type, ['media','external'], true) ) wp_send_json_error([ 'message'=>'Bad request' ], 400);

        if ( $type === 'media' ) {
            $att_id = absint( $_POST['attachment_id'] ?? 0 );
            if ( ! $att_id || ! wp_attachment_is_image( $att_id ) ) wp_send_json_error([ 'message' => 'Invalid attachment' ], 400);
            set_post_thumbnail( $pid, $att_id );
            wp_send_json_success([ 'set' => true, 'attachment_id' => $att_id ]);
        }

        // External: download and attach
        $url = esc_url_raw( $_POST['url'] ?? '' );
        if ( ! $url ) wp_send_json_error([ 'message' => 'Missing URL' ], 400);

        $res = $this->import_external_for_pid( $pid, $url );
        if ( ! empty($res['imported']) ) {
            wp_send_json_success( $res );
        } else {
            wp_send_json_error( [ 'message' => $res['error'] ?? 'Import failed' ], 500 );
        }
    }

    public function ajax_bulk() {
        check_ajax_referer( 'gv_ei', 'nonce' );
        if ( ! current_user_can( self::CAP ) ) wp_send_json_error([ 'message' => 'Permission denied' ], 403);

        $ids  = array_map( 'absint', (array) ($_POST['ids'] ?? []) );
        $mode = sanitize_key( $_POST['mode'] ?? 'scan' ); // scan|import
        if ( empty($ids) ) wp_send_json_error([ 'message'=>'No products' ], 400);

        $out = [];
        foreach ( $ids as $pid ) {
            if ( 'scan' === $mode ) {
                $sug = $this->find_best_suggestion( $pid );
                $out[] = [ 'id'=>$pid, 'found'=> (bool)$sug, 'suggestion'=>$sug ];
            } else {
                $sug = $this->find_best_suggestion( $pid );
                if ( ! $sug ) { $out[] = [ 'id'=>$pid, 'imported'=>false, 'error'=>'No suggestion' ]; continue; }

                if ( $sug['type'] === 'media' ) {
                    set_post_thumbnail( $pid, absint($sug['attachment_id']) );
                    $out[] = [ 'id'=>$pid, 'imported'=>true, 'attachment_id'=>absint($sug['attachment_id']) ];
                } else {
                    $out[] = $this->import_external_for_pid( $pid, $sug['url'] );
                }
            }
        }
        wp_send_json_success([ 'results' => $out ]);
    }

    /* ---------------------------- Helpers ------------------------------- */

    private function import_external_for_pid( $pid, $url ) {
        if ( ! function_exists('media_handle_sideload') ) require_once ABSPATH.'wp-admin/includes/media.php';
        if ( ! function_exists('download_url') )         require_once ABSPATH.'wp-admin/includes/file.php';
        if ( ! function_exists('wp_read_image_metadata') ) require_once ABSPATH.'wp-admin/includes/image.php';

        $tmp = download_url( $url, 60 );
        if ( is_wp_error($tmp) ) return [ 'id'=>$pid, 'imported'=>false, 'error'=>$tmp->get_error_message() ];

        $file_array = [
            'name'     => basename( parse_url($url, PHP_URL_PATH) ) ?: 'product-image.jpg',
            'tmp_name' => $tmp,
        ];

        $att_id = media_handle_sideload( $file_array, $pid, get_the_title($pid) );
        if ( is_wp_error( $att_id ) ) {
            @unlink( $file_array['tmp_name'] );
            return [ 'id'=>$pid, 'imported'=>false, 'error'=>$att_id->get_error_message() ];
        }

        set_post_thumbnail( $pid, $att_id );
        return [
            'id'            => $pid,
            'imported'      => true,
            'attachment_id' => $att_id,
            'thumb'         => wp_get_attachment_image_url( $att_id, 'thumbnail' ),
        ];
    }
}

new GV_Bulk_Edit_Enrich_Image();
