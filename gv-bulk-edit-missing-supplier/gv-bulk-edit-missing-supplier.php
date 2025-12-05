<?php
/**
 * Plugin Name: GV Bulk Edit – Missing Supplier (Lean, mbstring-safe)
 * Description: Lists products missing a supplier and uses AI to suggest a supplier (from supplier posts) and a Supplier SKU.
 * Uses ONLY:
 *  - Product meta: _gv_proc_supplier_id, _gv_proc_supplier_sku
 *  - Supplier posts: post_type = gv_supplier (post_title = name), meta _gv_website (optional)
 * Version: 1.2.1
 */
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------ */
/* Constants                                                           */
/* ------------------------------------------------------------------ */
define('GVBES_SLUG', 'gv-bulk-edit-missing-supplier');
define('GVBES_CAP',  'manage_woocommerce');
define('GVBES_VER',  '1.2.1');
define('GVBES_URL',  plugin_dir_url(__FILE__));
define('GVBES_DIR',  plugin_dir_path(__FILE__));

/* ------------------------------------------------------------------ */
/* Helpers                                                             */
/* ------------------------------------------------------------------ */
function gvbes_lc($s) {
    $s = (string)$s;
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
}
function gvbes_get_api_key() {
    $key = get_option('gv_openai_api_key');
    if (is_string($key) && ($key = trim($key))) return $key;
    if (defined('GV_OPENAI_API_KEY') && GV_OPENAI_API_KEY) return GV_OPENAI_API_KEY;
    return '';
}
function gvbes_get_org_id() {
    $org = get_option('gv_openai_org_id');
    return is_string($org) ? trim($org) : '';
}

/* ------------------------------------------------------------------ */
/* Supplier logic (ONLY your schema)                                   */
/* ------------------------------------------------------------------ */
function gvbes_product_has_supplier($product_id) {
    $val = get_post_meta($product_id, '_gv_proc_supplier_id', true);
    return !empty($val);
}
function gvbes_collect_supplier_posts() {
    $names = []; // lcname => [name,id]

    // Primary: CPT gv_supplier (even if not public)
    $paged = 1; $per_page = 200; $max_pages = 20;
    while ($paged <= $max_pages) {
        $q = new WP_Query([
            'post_type'      => 'gv_supplier',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        if (empty($q->posts)) break;
        foreach ($q->posts as $sid) {
            $name = get_the_title($sid);
            if ($name) $names[ gvbes_lc($name) ] = ['name'=>$name, 'id'=>$sid];
        }
        if (count($q->posts) < $per_page) break;
        $paged++;
    }
    wp_reset_postdata();

    // Fallback: any post with _gv_website meta (in case some suppliers live elsewhere)
    $paged = 1; $per_page = 200; $max_pages = 5;
    while ($paged <= $max_pages) {
        $q2 = new WP_Query([
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'meta_key'       => '_gv_website',
            'meta_compare'   => 'EXISTS',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        if (empty($q2->posts)) break;
        foreach ($q2->posts as $sid) {
            $name = get_the_title($sid);
            if ($name) $names[ gvbes_lc($name) ] = ['name'=>$name, 'id'=>$sid];
        }
        if (count($q2->posts) < $per_page) break;
        $paged++;
    }
    wp_reset_postdata();

    return $names; // lc => [name,id]
}
function gvbes_collect_site_suppliers() {
    $out = [];
    foreach (gvbes_collect_supplier_posts() as $lc => $obj) $out[$lc] = $obj['name'];
    return array_values($out); // list of names
}

/* ------------------------------------------------------------------ */
/* OpenAI                                                              */
/* ------------------------------------------------------------------ */
function gvbes_openai_chat($api_key, $prompt_msg) {
    $endpoint = 'https://api.openai.com/v1/chat/completions';
    $org_id   = gvbes_get_org_id();
    $body = [
        'model'       => 'gpt-4o-mini',
        'messages'    => [$prompt_msg],
        'temperature' => 0.2,
        'max_tokens'  => 200,
    ];
    $headers = [
        'Authorization' => 'Bearer '.$api_key,
        'Content-Type'  => 'application/json',
    ];
    if ($org_id) $headers['OpenAI-Organization'] = $org_id;

    return wp_remote_post($endpoint, [
        'headers' => $headers,
        'body'    => wp_json_encode($body),
        'timeout' => 45,
    ]);
}
function gvbes_build_prompt($ctx) {
    return [
        'role'    => 'user',
        'content' => wp_json_encode([
            'instruction' => 'From the provided supplier names (taken from this site) pick the most likely supplier for the product. If the product Supplier SKU is empty, suggest a plausible supplier-specific SKU. ONLY choose a supplier from the provided list. Respond strictly as JSON: {"supplier":"","sku_suggestion":"","confidence":0-1,"reason":""}.',
            'product' => [
                'title'      => (string)$ctx['title'],
                'content'    => (string)$ctx['content'],
                'short'      => (string)$ctx['short'],
                'sku'        => (string)$ctx['sku'],
                'attributes' => (string)$ctx['attrs'],
            ],
            'available_suppliers' => array_values((array)$ctx['suppliers']),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
}

/* ------------------------------------------------------------------ */
/* Admin UI                                                            */
/* ------------------------------------------------------------------ */
function gvbes_get_bulk_edit_parent_slug() {
    $desired_title = 'Bulk Edit';
    $desired_slug  = 'gv-bulk-edit';
    global $menu;
    if (is_array($menu)) {
        foreach ($menu as $item) {
            $title = isset($item[0]) ? trim(wp_strip_all_tags($item[0])) : '';
            $slug  = isset($item[2]) ? $item[2] : '';
            if (strcasecmp($slug, $desired_slug) === 0 || strcasecmp($title, $desired_title) === 0) return $slug;
        }
    }
    add_menu_page($desired_title, $desired_title, GVBES_CAP, $desired_slug, '__return_null', 'dashicons-edit-large', 56);
    return $desired_slug;
}
add_action('admin_menu', function() {
    if (!current_user_can(GVBES_CAP)) return;
    $parent = gvbes_get_bulk_edit_parent_slug();
    add_submenu_page($parent, 'Enrich: Missing Supplier', 'Missing Supplier', GVBES_CAP, GVBES_SLUG, 'gvbes_render_page');
}, 50);

function gvbes_render_page() {
    if (!current_user_can(GVBES_CAP)) return;
    $sup_count = count(gvbes_collect_site_suppliers());
    ?>
    <div class="wrap gvbes-wrap">
        <h1>Bulk Edit → Enrich data: Missing Supplier</h1>
        <p>Uses ONLY: product meta <code>_gv_proc_supplier_id</code>, <code>_gv_proc_supplier_sku</code>; supplier posts <code>gv_supplier</code> (post_title=name, optional <code>_gv_website</code>).</p>
        <p>Detected suppliers (CPT <code>gv_supplier</code> + fallback by <code>_gv_website</code>): <strong><?php echo (int)$sup_count; ?></strong></p>

        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr(GVBES_SLUG); ?>">
            <input type="hidden" name="post_type" value="product">
            <p class="search-box">
                <label class="screen-reader-text" for="gvbes-s">Search products:</label>
                <input type="search" id="gvbes-s" name="s" value="<?php echo isset($_GET['s']) ? esc_attr(wp_unslash($_GET['s'])) : ''; ?>" placeholder="Search by title/SKU">
                <input type="number" min="5" step="5" name="per_page" value="<?php echo isset($_GET['per_page'])?(int)$_GET['per_page']:20; ?>" style="width:100px" title="Per page">
                <button class="button">Apply</button>
            </p>
        </form>

        <div id="gvbes-table" class="gvbes-table"><p><em>Loading products without supplier…</em></p></div>
    </div>
    <?php
}

/* ------------------------------------------------------------------ */
/* Assets                                                              */
/* ------------------------------------------------------------------ */
add_action('admin_enqueue_scripts', function() {
    if (empty($_GET['page']) || $_GET['page'] !== GVBES_SLUG) return;

    // Build supplier list once for the UI dropdown
    $sup_map = gvbes_collect_supplier_posts(); // lc => [name,id]
    // Make it a simple [ [id,name], ... ] sorted by name
    $suppliers = array_values(array_map(function($o){ return ['id'=>(int)$o['id'], 'name'=>$o['name']]; }, $sup_map));
    usort($suppliers, function($a,$b){ return strcasecmp($a['name'], $b['name']); });

    wp_enqueue_script('gvbes-admin', GVBES_URL.'assets/gvbes-admin.js', ['jquery'], GVBES_VER, true);
    wp_localize_script('gvbes-admin', 'GVBES', [
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('gvbes_nonce'),
        'messages'  => [
            'loading' => 'Loading products without supplier…',
            'none'    => 'No products found without a supplier.',
        ],
        'suppliers' => $suppliers,
    ]);
});

/* ------------------------------------------------------------------ */
/* AJAX: list / AI suggest / apply                                     */
/* ------------------------------------------------------------------ */
add_action('wp_ajax_gvbes_list', function() {
    if (!current_user_can(GVBES_CAP)) wp_send_json_error(['message'=>'No permission']);
    check_ajax_referer('gvbes_nonce','nonce');

    $search   = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $paged    = isset($_GET['paged']) ? max(1,(int)$_GET['paged']) : 1;
    $per_page = isset($_GET['per_page']) ? max(5,(int)$_GET['per_page']) : 20;

    $q = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $search,
        'fields'         => 'ids',
        'posts_per_page' => 500,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    $ids = $q->posts;

    $no_supplier = [];
    foreach ($ids as $pid) if (!gvbes_product_has_supplier($pid)) $no_supplier[] = $pid;

    $total       = count($no_supplier);
    $total_pages = max(1, (int)ceil($total / $per_page));
    $paged       = min($paged, $total_pages);
    $slice       = array_slice($no_supplier, ($paged-1)*$per_page, $per_page);

    $rows = [];


foreach ($slice as $pid) {
    $product = function_exists('wc_get_product') ? wc_get_product($pid) : null;
    $rows[] = [
        'id'          => $pid,
        'edit'        => get_edit_post_link($pid,''),
        'title'       => get_the_title($pid),
        'sku'         => $product ? (string)$product->get_sku() : '',
        'price'       => $product ? (string)$product->get_price() : '',
        'supplier_id' => (int) get_post_meta($pid, '_gv_proc_supplier_id', true), // <— NEW
    ];
}


    wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$paged,'total_pages'=>$total_pages]);
});


add_action('wp_ajax_gvbes_set_supplier', function() {
    if (!current_user_can(GVBES_CAP)) wp_send_json_error(['message'=>'No permission']);
    check_ajax_referer('gvbes_nonce','nonce');

    $pid  = isset($_POST['product_id'])  ? (int) $_POST['product_id']  : 0;
    $sid  = isset($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : 0;

    if (!$pid || !$sid) wp_send_json_error(['message'=>'Missing product or supplier']);

    // Validate supplier post exists
    $post = get_post($sid);
    if (!$post || 'publish' !== $post->post_status) {
        wp_send_json_error(['message'=>'Supplier post not found or not published']);
    }

    update_post_meta($pid, '_gv_proc_supplier_id', $sid);
    wp_send_json_success(['message'=>'Supplier saved']);
});




add_action('wp_ajax_gvbes_ai_suggest', function() {
    try {
        if (!current_user_can(GVBES_CAP)) wp_send_json_error(['message'=>'No permission']);
        check_ajax_referer('gvbes_nonce','nonce');

        $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        if (!$pid) wp_send_json_error(['message'=>'Invalid product']);
        $product = function_exists('wc_get_product') ? wc_get_product($pid) : null;

        $api_key = gvbes_get_api_key();
        if (!$api_key) wp_send_json_error(['message'=>'Missing OpenAI API key (gv_openai_api_key).']);

        $title   = get_the_title($pid);
        $content = wp_strip_all_tags(get_post_field('post_content', $pid));
        $short   = wp_strip_all_tags(get_post_field('post_excerpt', $pid));
        $sku     = $product ? (string)$product->get_sku() : '';

        $attrs = [];
        if ($product && method_exists($product,'get_attributes')) {
            foreach ((array)$product->get_attributes() as $attr) {
                $label   = is_object($attr) && method_exists($attr,'get_name')    ? $attr->get_name()    : '';
                $options = is_object($attr) && method_exists($attr,'get_options') ? (array)$attr->get_options() : [];
                $vals = [];
                foreach ($options as $opt) {
                    if (is_numeric($opt)) { $term = get_term((int)$opt); if ($term && !is_wp_error($term)) $vals[] = $term->name; }
                    else { $vals[] = (string)$opt; }
                }
                if ($label || $vals) $attrs[] = trim($label.': '.implode('|',$vals));
            }
        }

        $suppliers_list = gvbes_collect_site_suppliers();
        if (empty($suppliers_list)) wp_send_json_error(['message'=>'No suppliers found on site. Expected suppliers in post_type=gv_supplier (post_title=name). Optional meta _gv_website for website URLs.']);

        $prompt   = gvbes_build_prompt(['title'=>$title,'content'=>$content,'short'=>$short,'sku'=>$sku,'attrs'=>implode(', ',$attrs),'suppliers'=>$suppliers_list]);
        $response = gvbes_openai_chat($api_key, $prompt);
        if (is_wp_error($response)) wp_send_json_error(['message'=>'OpenAI error: '.$response->get_error_message()]);

        $code = (int)wp_remote_retrieve_response_code($response);
        $body = (string)wp_remote_retrieve_body($response);
        if ($code < 200 || $code >= 300) wp_send_json_error(['message'=>'OpenAI HTTP '.$code.' – '.substr($body,0,200)]);

        $data = json_decode($body, true);
        $suggestion = ['supplier'=>'','sku_suggestion'=>'','confidence'=>0.0,'reason'=>''];
        if (isset($data['choices'][0]['message']['content'])) {
            $parsed = json_decode(trim($data['choices'][0]['message']['content']), true);
            if (is_array($parsed)) $suggestion = array_merge($suggestion, array_intersect_key($parsed, $suggestion));
        }
        if (empty($suggestion['supplier'])) wp_send_json_error(['message'=>'AI did not return a supplier.']);

        wp_send_json_success(['suggestion'=>$suggestion]);
    } catch (Throwable $e) {
        wp_send_json_error(['message'=>'Server error: '.$e->getMessage()]);
    }
});

add_action('wp_ajax_gvbes_apply', function() {
    if (!current_user_can(GVBES_CAP)) wp_send_json_error(['message'=>'No permission']);
    check_ajax_referer('gvbes_nonce','nonce');

    $pid      = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $supplier = isset($_POST['supplier']) ? sanitize_text_field(wp_unslash($_POST['supplier'])) : '';
    $sku_s    = isset($_POST['sku_suggestion']) ? sanitize_text_field(wp_unslash($_POST['sku_suggestion'])) : '';

    if (!$pid) wp_send_json_error(['message'=>'Invalid product']);
    if (!$supplier) wp_send_json_error(['message'=>'Missing supplier to apply']);

    $post_suppliers = gvbes_collect_supplier_posts(); // lc => [name,id]
    $lc_sup = gvbes_lc($supplier);
    if (!isset($post_suppliers[$lc_sup])) {
        wp_send_json_error(['message'=>'Supplier post not found (match by post_title).']);
    }
    $supplier_post_id = (int)$post_suppliers[$lc_sup]['id'];

    // Write canonical metas
    update_post_meta($pid, '_gv_proc_supplier_id',  $supplier_post_id);
    $existing_supplier_sku = get_post_meta($pid, '_gv_proc_supplier_sku', true);
    if (empty($existing_supplier_sku) && $sku_s) {
        update_post_meta($pid, '_gv_proc_supplier_sku', $sku_s);
    }

    wp_send_json_success(['message'=>'Supplier saved to _gv_proc_supplier_id'.(empty($existing_supplier_sku)&&$sku_s?' + _gv_proc_supplier_sku set':'')]);
});
