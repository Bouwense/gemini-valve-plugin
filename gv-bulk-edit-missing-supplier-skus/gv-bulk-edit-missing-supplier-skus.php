<?php
/**
 * Plugin Name: GV – Bulk Edit: Missing Supplier SKUs
 * Description: Admin page under Bulk Edit that lists products with supplier selected but no Supplier SKU. Tools: copy product SKU or use AI to suggest supplier SKU.
 * Author: Gemini Valve
 * Version: 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Prevent double-declare if a snippet or old file exists */
if ( ! class_exists( 'GV_Bulk_Edit_Missing_Supplier_SKUs' ) ) :

class GV_Bulk_Edit_Missing_Supplier_SKUs {
    const SLUG  = 'gv-bulk-edit-missing-supplier-skus';
    const NONCE = 'gv_bemss_nonce';

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'menu' ], 99 );
        add_action( 'wp_ajax_gv_bemss_fetch',[ $this, 'ajax_fetch' ] );
        add_action( 'wp_ajax_gv_bemss_copy', [ $this, 'ajax_copy' ] );
        add_action( 'wp_ajax_gv_bemss_save', [ $this, 'ajax_save' ] );
        add_action( 'wp_ajax_gv_bemss_ai',   [ $this, 'ajax_ai' ] );

        // Inline CSS/JS only on our page
        add_action( 'admin_print_styles',          [ $this, 'inline_css' ] );
        add_action( 'admin_print_footer_scripts',  [ $this, 'inline_js' ] );
    }

    /** Try to nest under your “Bulk Edit” page; fall back to WooCommerce */
    public function menu() {
        $parent = 'gv-bulk-edit'; // change if your parent slug differs
        $parent_exists = false;

        global $submenu;
        if ( isset( $submenu['woocommerce'] ) ) {
            foreach ( $submenu['woocommerce'] as $item ) {
                if ( isset( $item[2] ) && $item[2] === $parent ) { $parent_exists = true; break; }
            }
        }
        if ( ! $parent_exists ) $parent = 'woocommerce';

        add_submenu_page(
            $parent,
            'Missing Supplier SKUs',
            'Missing Supplier SKUs',
            'manage_woocommerce',
            self::SLUG,
            [ $this, 'render' ]
        );
    }

    public function render() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Insufficient permissions.' );

        $key = get_option( 'gv_openai_api_key' );
        ?>
        <div class="wrap gv-bemss-wrap">
            <h1>Missing Supplier SKUs</h1>
            <p>Shows products that have a supplier selected (<code>_gv_proc_supplier_id</code>) but no Supplier SKU (<code>_gv_proc_supplier_sku</code>).</p>
            <?php if ( empty( $key ) ): ?>
                <div class="notice notice-warning"><p><strong>OpenAI key not set.</strong> Set <code>gv_openai_api_key</code> (optional <code>gv_openai_org_id</code>) to enable AI suggestions.</p></div>
            <?php endif; ?>

            <div class="gv-bemss-controls">
                <button class="button button-secondary" id="gv-bemss-refresh">Refresh</button>
                <label style="margin-left:12px;">Search: <input type="search" id="gv-bemss-q" placeholder="Title / SKU contains…"></label>
                <label style="margin-left:12px;">Per page:
                    <input type="number" id="gv-bemss-limit" value="20" min="5" max="200" step="5">
                </label>
            </div>

            <table class="widefat fixed striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Supplier</th>
                    <th>Website</th>
                    <th>Product SKU</th>
                    <th>Supplier SKU (edit)</th>
                    <th style="width:260px;">Actions</th>
                </tr>
                </thead>
                <tbody id="gv-bemss-rows">
                    <tr><td colspan="7">Loading…</td></tr>
                </tbody>
            </table>

            <div class="tablenav"><div class="tablenav-pages" id="gv-bemss-pager"></div></div>

            <script>
                // Localize without needing a registered script
                window.GV_BEMSS = {
                    ajax:  "<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>",
                    nonce: "<?php echo esc_js( wp_create_nonce( self::NONCE ) ); ?>"
                };
            </script>
        </div>
        <?php
    }

    /** Data fetch */
    public function ajax_fetch() {
        $this->assert_ajax();

        global $wpdb;
        $q       = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';
        $limit   = max(5, min(200, isset($_POST['limit']) ? (int) $_POST['limit'] : 20 ));
        $page    = max(1, (int) ($_POST['page'] ?? 1));
        $offset  = ($page - 1) * $limit;

        $p  = $wpdb->posts;
        $pm = $wpdb->postmeta;

        $where_q = '';
        if ( $q ) {
            $like = '%' . $wpdb->esc_like( $q ) . '%';
            $where_q = $wpdb->prepare(" AND (p.post_title LIKE %s OR sku_meta.meta_value LIKE %s)", $like, $like);
        }

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS p.ID
            FROM {$p} p
            INNER JOIN {$pm} sup
              ON sup.post_id = p.ID
             AND sup.meta_key = '_gv_proc_supplier_id'
             AND sup.meta_value IS NOT NULL
             AND TRIM(sup.meta_value) <> ''
             AND TRIM(sup.meta_value) <> '0'
            LEFT JOIN {$pm} ssku
              ON ssku.post_id = p.ID
             AND ssku.meta_key = '_gv_proc_supplier_sku'
            LEFT JOIN {$pm} sku_meta
              ON sku_meta.post_id = p.ID
             AND sku_meta.meta_key = '_sku'
            WHERE p.post_type = 'product'
              AND p.post_status IN ('publish','draft','pending','private')
              AND (ssku.meta_id IS NULL OR ssku.meta_value IS NULL OR TRIM(ssku.meta_value) = '')
              {$where_q}
            ORDER BY p.ID DESC
            LIMIT %d OFFSET %d
        ";

        $ids   = $wpdb->get_col( $wpdb->prepare( $sql, $limit, $offset ) );
        $total = (int) $wpdb->get_var("SELECT FOUND_ROWS()");

        $rows = [];
        foreach ( $ids as $pid ) {
            $prod      = function_exists('wc_get_product') ? wc_get_product( $pid ) : null;
            $title     = $prod ? $prod->get_formatted_name() : get_the_title( $pid );
            $sku       = $prod ? ( $prod->get_sku() ?: '' ) : get_post_meta( $pid, '_sku', true );
            $sup_id    = (int) get_post_meta( $pid, '_gv_proc_supplier_id', true );
            $sup_title = $sup_id ? get_the_title( $sup_id ) : '';
            $sup_web   = $sup_id ? get_post_meta( $sup_id, '_gv_website', true ) : '';

            $rows[] = [
                'id'    => (int) $pid,
                'title' => $title,
                'sku'   => $sku,
                'sup'   => $sup_title,
                'web'   => $sup_web,
            ];
        }

        wp_send_json_success([
            'rows'  => $rows,
            'total' => $total,
            'limit' => $limit,
            'page'  => $page,
        ]);
    }

    /** Copy product SKU -> supplier SKU */
    public function ajax_copy() {
        $this->assert_ajax();
        $pid = (int) ($_POST['product_id'] ?? 0);
        if ( ! $pid ) wp_send_json_error(['msg'=>'Missing product_id']);
        $sku = get_post_meta( $pid, '_sku', true );
        if ( ! $sku ) wp_send_json_error(['msg'=>'Product has no SKU to copy.']);
        update_post_meta( $pid, '_gv_proc_supplier_sku', sanitize_text_field( $sku ) );
        wp_send_json_success([ 'msg' => 'Copied', 'sku' => $sku ]);
    }

    /** Save supplier SKU */
    public function ajax_save() {
        $this->assert_ajax();
        $pid = (int) ($_POST['product_id'] ?? 0);
        $sku = isset($_POST['supplier_sku']) ? sanitize_text_field($_POST['supplier_sku']) : '';
        if ( ! $pid ) wp_send_json_error(['msg'=>'Missing product_id']);
        if ( $sku === '' ) wp_send_json_error(['msg'=>'Supplier SKU cannot be empty.']);
        update_post_meta( $pid, '_gv_proc_supplier_sku', $sku );
        wp_send_json_success([ 'msg' => 'Saved', 'sku' => $sku ]);
    }

    /** Ask AI for supplier SKU candidates */
    public function ajax_ai() {
        $this->assert_ajax();

        $pid = (int) ($_POST['product_id'] ?? 0);
        if ( ! $pid ) wp_send_json_error(['msg'=>'Missing product_id']);

        $prod      = function_exists('wc_get_product') ? wc_get_product( $pid ) : null;
        $title     = $prod ? $prod->get_name() : get_the_title($pid);
        $prod_sku  = $prod ? ( $prod->get_sku() ?: '' ) : get_post_meta($pid,'_sku',true);
        $desc      = $prod ? wp_strip_all_tags( $prod->get_description() ) : '';
        $short     = $prod ? wp_strip_all_tags( $prod->get_short_description() ) : '';

        $sup_id    = (int) get_post_meta( $pid, '_gv_proc_supplier_id', true );
        $sup_name  = $sup_id ? get_the_title( $sup_id ) : '';
        $sup_web   = $sup_id ? get_post_meta( $sup_id, '_gv_website', true ) : '';

        $api_key   = get_option( 'gv_openai_api_key' );
        $org_id    = get_option( 'gv_openai_org_id' );
        if ( empty( $api_key ) ) wp_send_json_error([ 'msg' => 'OpenAI key missing (gv_openai_api_key).' ]);

        $prompt = [
            'role'    => 'user',
            'content' => "You're helping map WooCommerce products to supplier catalog SKUs.\n".
                         "Supplier: {$sup_name}\nSupplier website: {$sup_web}\n".
                         "Product title: {$title}\nProduct SKU (internal): {$prod_sku}\n".
                         "Description: {$desc}\nShort: {$short}\n\n".
                         "Task: Suggest the most likely supplier catalog SKU. If unsure, give up to 3 candidates. ".
                         "Return compact JSON: {\"suggestions\":[{\"sku\":\"...\",\"confidence\":0-1,\"reasoning\":\"...\"}],\"search_query\":\"...\"}"
        ];

        $body = json_encode([
            'model'     => 'gpt-4o-mini',
            'messages'  => [
                ['role'=>'system','content'=>'You are a precise product data assistant. Output valid JSON only.'],
                $prompt
            ],
            'temperature' => 0.2,
        ]);

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ];
        if ( ! empty( $org_id ) ) $headers['OpenAI-Organization'] = $org_id;

        $resp = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'timeout' => 25,
            'headers' => $headers,
            'body'    => $body,
        ] );

        if ( is_wp_error( $resp ) ) {
            wp_send_json_error([ 'msg' => 'OpenAI request failed: ' . $resp->get_error_message() ]);
        }

        $data = json_decode( wp_remote_retrieve_body( $resp ), true );
        $text = $data['choices'][0]['message']['content'] ?? '';
        $parsed = json_decode( trim( $text ), true );
        if ( ! is_array( $parsed ) ) {
            wp_send_json_error([ 'msg' => 'AI response could not be parsed.', 'raw' => $text ]);
        }

        wp_send_json_success([
            'ai'     => $parsed,
            'google' => 'https://www.google.com/search?q=' . rawurlencode( $parsed['search_query'] ?? ( $sup_name . ' ' . $title ) ),
        ]);
    }

    private function assert_ajax() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error(['msg'=>'No permission']);
        check_ajax_referer( self::NONCE, 'nonce' );
    }

    /** Inline admin CSS */
    public function inline_css() {
        if ( isset($_GET['page']) && $_GET['page'] === self::SLUG ) {
            echo '<style>
                .gv-bemss-wrap .actions { display:flex; gap:6px; flex-wrap:wrap }
                .gv-bemss-controls { margin:10px 0 }
                .gv-ai-note { font-size:11px; color:#555 }
                .gv-sku-input { width: 100%; max-width: 240px; }
            </style>';
        }
    }

    /** Inline admin JS (no Underscore dependency) */
    public function inline_js() {
        if ( isset($_GET['page']) && $_GET['page'] === self::SLUG ) : ?>
<script>
(function($){
    const S = window.GV_BEMSS || {};
    const $rows = $('#gv-bemss-rows'), $pager = $('#gv-bemss-pager');

    function esc(s){ return String(s===null||s===undefined?'':s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

    function fetch(page=1){
        $rows.html('<tr><td colspan="7">Loading…</td></tr>');
        $.post(S.ajax, { action:'gv_bemss_fetch', nonce:S.nonce,
            q: $('#gv-bemss-q').val()||'',
            limit: $('#gv-bemss-limit').val()||20,
            page: page
        }, function(r){
            if(!r || !r.success){ $rows.html('<tr><td colspan="7">'+((r&&r.data&&r.data.msg)||'Error')+'</td></tr>'); return; }
            const rows = r.data.rows||[];
            if(!rows.length){ $rows.html('<tr><td colspan="7">No products found.</td></tr>'); $pager.empty(); return; }
            let html='';
            rows.forEach(o=>{
                html+=`<tr data-id="${o.id}">
                    <td>${o.id}</td>
                    <td>${esc(o.title)}</td>
                    <td>${esc(o.sup||'')}</td>
                    <td>${o.web ? `<a href="${o.web}" target="_blank" rel="noreferrer">${esc(o.web)}</a>` : ''}</td>
                    <td>${esc(o.sku||'')}</td>
                    <td><input type="text" class="regular-text gv-sku-input" placeholder="Supplier SKU…" value=""></td>
                    <td class="actions">
                        <button class="button copy">Copy product SKU</button>
                        <button class="button ai">AI: Suggest</button>
                        <button class="button button-primary save">Save</button>
                    </td>
                </tr>
                <tr class="ai-row" style="display:none"><td colspan="7"><div class="gv-ai-note">AI suggestions will appear here.</div></td></tr>`;
            });
            $rows.html(html);
            const total = r.data.total, limit = r.data.limit, pageNow = r.data.page;
            const pages = Math.ceil(total/limit);
            let phtml = '';
            if (pages>1){
                phtml += `Page ${pageNow} / ${pages} `;
                if(pageNow>1) phtml += `<a href="#" data-p="${pageNow-1}" class="page-numbers prev">&laquo;</a> `;
                const max = Math.min(pages, 10);
                for(let i=1;i<=max;i++){
                    phtml += i===pageNow ? `<span class="page-numbers current">${i}</span> ` : `<a href="#" data-p="${i}" class="page-numbers">${i}</a> `;
                }
                if(pageNow<pages) phtml += `<a href="#" data-p="${pageNow+1}" class="page-numbers next">&raquo;</a>`;
            }
            $pager.html(phtml);
        });
    }

    $(document).on('click', '#gv-bemss-refresh', function(e){ e.preventDefault(); fetch(1); });
    $(document).on('keypress', '#gv-bemss-q', function(e){ if(e.which===13){ fetch(1); }} );
    $(document).on('change', '#gv-bemss-limit', function(){ fetch(1); });

    $pager.on('click', 'a.page-numbers', function(e){
        e.preventDefault(); fetch( parseInt($(this).data('p')||1,10) );
    });

    $('#gv-bemss-rows').on('click', 'button.copy', function(e){
        e.preventDefault();
        const $tr = $(this).closest('tr'), id = $tr.data('id');
        $.post(S.ajax, { action:'gv_bemss_copy', nonce:S.nonce, product_id:id }, res=>{
            if(res.success){ $tr.find('.gv-sku-input').val(res.data.sku).focus(); }
            else alert((res.data&&res.data.msg)||'Copy failed');
        });
    });

    $('#gv-bemss-rows').on('click', 'button.save', function(e){
        e.preventDefault();
        const $tr = $(this).closest('tr'), id = $tr.data('id');
        const sku = $tr.find('.gv-sku-input').val().trim();
        $.post(S.ajax, { action:'gv_bemss_save', nonce:S.nonce, product_id:id, supplier_sku:sku }, res=>{
            if(res.success){ $tr.next('.ai-row').remove(); $tr.fadeOut(200, ()=>{ $tr.remove(); }); }
            else alert((res.data&&res.data.msg)||'Save failed');
        });
    });

    $('#gv-bemss-rows').on('click', 'button.ai', function(e){
        e.preventDefault();
        const $tr = $(this).closest('tr'), id = $tr.data('id');
        const $ai = $tr.next('.ai-row');
        $ai.show().find('.gv-ai-note').text('Asking AI…');
        $.post(S.ajax, { action:'gv_bemss_ai', nonce:S.nonce, product_id:id }, res=>{
            if(!res.success){ $ai.find('.gv-ai-note').text((res.data&&res.data.msg)||'AI error'); return; }
            const ai = res.data.ai||{};
            const list = (ai.suggestions||[]).map(s=>`<li><strong>${esc(s.sku)}</strong> (conf ${s.confidence}) – ${esc(s.reasoning||'')}</li>`).join('');
            const gq  = res.data.google ? `<a href="${res.data.google}" target="_blank" rel="noreferrer">Google it</a>` : '';
            $ai.find('.gv-ai-note').html(`<ul>${list||'<li>No suggestion.</li>'}</ul>${gq}`);
            if(ai.suggestions && ai.suggestions[0] && ai.suggestions[0].sku){
                $tr.find('.gv-sku-input').val(ai.suggestions[0].sku).focus();
            }
        });
    });

    fetch(1);
})(jQuery);
</script>
<?php
        endif;
    }
}

new GV_Bulk_Edit_Missing_Supplier_SKUs();

endif; // class_exists guard
