<?php
/**
 * Plugin Name: GV – Procurement & Serials
 * Description: Procurement Suggestions (Buy/Build by cost), product & supplier lead_time_days, POs with per-line confirmed ETA (inline), Work Orders, Quick Receive + Serials, Printable PO with logo/brand colors & supplier prefill, Email PO as PDF, and scan-to-assign.
 * Author: Gemini Valve
 * Version: 1.6.0
 */

if (!defined('ABSPATH')) exit;

class GV_Procurement {
    /* ===== Tables / CPTs / Options ===== */
    const TBL_PO_ITEMS = 'gv_po_items';
    const TBL_SERIALS  = 'gv_serials';
    const CPT_PO       = 'gv_po';
    const CPT_WO       = 'gv_wo';
    const DBV_OPTION   = 'gv_proc_dbv';
    const DBV          = 4;

    /* ===== Meta Keys ===== */
    const MK_SUPPLIER_ID        = '_gv_proc_supplier_id';
    const MK_SUPPLIER_SKU       = '_gv_proc_supplier_sku';
    const MK_COST_PRICE         = '_gv_proc_cost_price';
    const MK_IS_COMPOSITE       = '_gv_is_composite';
    const MK_CHILDREN_JSON      = '_gv_composite_children';
    const MK_DEFAULT_MODE       = '_gv_default_proc_mode';  // buy|build
    const MK_ROP                = '_gv_reorder_point';
    const MK_MIN_ORDER_QTY      = '_gv_min_order_qty';
    const MK_LEADTIME_PRODUCT   = '_gv_proc_lead_time_days';     // product
    const MK_LEADTIME_SUPPLIER  = '_gv_supplier_lead_time_days'; // supplier

    /* Supplier contact/address (prefill PO print & email) */
    const MK_SUPP_ADDR_1   = '_gv_supplier_address_1';
    const MK_SUPP_ADDR_2   = '_gv_supplier_address_2';
    const MK_SUPP_CITY     = '_gv_supplier_city';
    const MK_SUPP_POSTCODE = '_gv_supplier_postcode';
    const MK_SUPP_STATE    = '_gv_supplier_state';
    const MK_SUPP_COUNTRY  = '_gv_supplier_country';
    const MK_SUPP_EMAIL    = '_gv_supplier_email';
    const MK_SUPP_PHONE    = '_gv_supplier_phone';
    const MK_SUPP_CONTACT  = '_gv_supplier_contact_name';
    const MK_SUPP_VAT      = '_gv_supplier_vat';
    const MK_SUPP_PO_EMAIL = '_gv_supplier_po_email';

    /* Options */
    const OPT_SAFETY_PCT       = 'gv_proc_safety_pct'; // 0..100
    const OPT_BRAND_LOGO_URL   = 'gv_proc_brand_logo_url';
    const OPT_BRAND_PRIMARY    = 'gv_proc_brand_primary_hex';
    const OPT_BRAND_ACCENT     = 'gv_proc_brand_accent_hex';
    const OPT_FROM_EMAIL       = 'gv_proc_from_email';
    const OPT_FROM_NAME        = 'gv_proc_from_name';
    const OPT_PO_EMAIL_SUBJECT = 'gv_proc_po_email_subject';
    const OPT_PO_EMAIL_BODY    = 'gv_proc_po_email_body';

    /* Nonces / Actions */
    const NONCE_RECEIVE        = 'gv_po_receive';

    public static function init(){
        add_action('init', [__CLASS__, 'register_cpts']);
        register_activation_hook(__FILE__, [__CLASS__, 'install']);
        add_action('plugins_loaded', [__CLASS__, 'maybe_upgrade']);

        add_action('admin_menu',   [__CLASS__, 'admin_menu']);
        add_action('rest_api_init',[__CLASS__, 'rest']);

        /* PO header ETA + list column */
        add_action('add_meta_boxes_' . self::CPT_PO, [__CLASS__, 'add_po_header_metabox']);
        add_action('save_post_' . self::CPT_PO,      [__CLASS__, 'save_po_header_metabox']);

        /* PO Lines (qty + per-line ETA) */
        add_action('add_meta_boxes_' . self::CPT_PO, [__CLASS__, 'add_po_lines_metabox']);
        add_action('save_post_' . self::CPT_PO,      [__CLASS__, 'save_po_lines_metabox']);

        /* Quick Receive (auto-serials) */
        add_action('add_meta_boxes_' . self::CPT_PO, [__CLASS__, 'add_po_receive_metabox']);
        add_action('save_post_' . self::CPT_PO,      [__CLASS__, 'save_po_receive_metabox']);

        /* Serials finalize */
        add_action('woocommerce_order_status_completed', [__CLASS__, 'on_order_completed']);

        /* Product lead time */
        add_action('woocommerce_product_options_inventory_product_data', [__CLASS__, 'product_leadtime_field']);
        add_action('woocommerce_process_product_meta', [__CLASS__, 'product_leadtime_save']);

        /* Supplier default lead time box (assumes CPT slug 'gv_supplier'; adjust if needed) */
        add_action('add_meta_boxes', [__CLASS__, 'supplier_leadtime_metabox']);

        /* Settings */
        add_action('admin_init', [__CLASS__, 'register_settings']);

        /* Print link in PO list */
        add_filter('post_row_actions', [__CLASS__, 'po_row_actions'], 10, 2);

        /* Email PO handler */
        add_action('admin_post_gv_send_po_pdf', [__CLASS__, 'handle_send_po_pdf']);
    }

    /* =================== Install / Upgrade =================== */
    public static function install(){
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $po_items = "CREATE TABLE {$wpdb->prefix}".self::TBL_PO_ITEMS." (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            po_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            supplier_id BIGINT UNSIGNED NOT NULL,
            description TEXT NULL,
            supplier_sku VARCHAR(190) NULL,
            unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
            qty_ordered DECIMAL(12,3) NOT NULL DEFAULT 0,
            qty_received DECIMAL(12,3) NOT NULL DEFAULT 0,
            confirmed_eta DATETIME NULL,
            meta LONGTEXT NULL
        ) $charset;";

        $serials = "CREATE TABLE {$wpdb->prefix}".self::TBL_SERIALS." (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            serial VARCHAR(64) UNIQUE,
            product_id BIGINT UNSIGNED NOT NULL,
            batch_id BIGINT UNSIGNED NULL,
            status ENUM('in_stock','allocated','sold','retired','lost') NOT NULL DEFAULT 'in_stock',
            po_item_id BIGINT UNSIGNED NULL,
            order_id BIGINT UNSIGNED NULL,
            order_item_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            received_at DATETIME NULL,
            sold_at DATETIME NULL,
            meta LONGTEXT NULL
        ) $charset;";

        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        dbDelta($po_items);
        dbDelta($serials);

        if (get_option(self::OPT_SAFETY_PCT, null) === null) update_option(self::OPT_SAFETY_PCT, 0);
        if (get_option(self::OPT_BRAND_PRIMARY, null) === null) update_option(self::OPT_BRAND_PRIMARY, '#891734');
        if (get_option(self::OPT_BRAND_ACCENT, null) === null) update_option(self::OPT_BRAND_ACCENT, '#f0e4d3');
        if (get_option(self::OPT_PO_EMAIL_SUBJECT, null) === null) update_option(self::OPT_PO_EMAIL_SUBJECT, 'Purchase Order #{PO_NUMBER} – Gemini Valve Europe');
        if (get_option(self::OPT_PO_EMAIL_BODY, null) === null) update_option(self::OPT_PO_EMAIL_BODY, "Dear {SUPPLIER_NAME},\n\nPlease find attached Purchase Order #{PO_NUMBER}.\n\nBest regards,\n{FROM_NAME}");
        update_option(self::DBV_OPTION, self::DBV);
    }

    public static function maybe_upgrade(){
        global $wpdb;
        $have = (int) get_option(self::DBV_OPTION, 0);
        if ($have >= self::DBV) return;

        $table = $wpdb->prefix . self::TBL_PO_ITEMS;
        $col = $wpdb->get_results( $wpdb->prepare("SHOW COLUMNS FROM `$table` LIKE %s", 'confirmed_eta') );
        if (!$col){
            $wpdb->query("ALTER TABLE `$table` ADD `confirmed_eta` DATETIME NULL AFTER `qty_received`");
        }
        if (get_option(self::OPT_SAFETY_PCT, null) === null) update_option(self::OPT_SAFETY_PCT, 0);
        if (get_option(self::OPT_BRAND_PRIMARY, null) === null) update_option(self::OPT_BRAND_PRIMARY, '#891734');
        if (get_option(self::OPT_BRAND_ACCENT, null) === null) update_option(self::OPT_BRAND_ACCENT, '#f0e4d3');
        if (get_option(self::OPT_PO_EMAIL_SUBJECT, null) === null) update_option(self::OPT_PO_EMAIL_SUBJECT, 'Purchase Order #{PO_NUMBER} – Gemini Valve Europe');
        if (get_option(self::OPT_PO_EMAIL_BODY, null) === null) update_option(self::OPT_PO_EMAIL_BODY, "Dear {SUPPLIER_NAME},\n\nPlease find attached Purchase Order #{PO_NUMBER}.\n\nBest regards,\n{FROM_NAME}");
        update_option(self::DBV_OPTION, self::DBV);
    }

    /* =================== CPTs =================== */
    public static function register_cpts(){
        register_post_type(self::CPT_PO, [
            'label' => 'Purchase Orders',
            'public' => false,
            'show_ui' => true,
            'capability_type' => 'shop_order',
            'map_meta_cap' => true,
            'supports' => ['title','editor','custom-fields'],
            'menu_icon' => 'dashicons-clipboard'
        ]);
        register_post_type(self::CPT_WO, [
            'label' => 'Work Orders',
            'public' => false,
            'show_ui' => true,
            'capability_type' => 'shop_order',
            'map_meta_cap' => true,
            'supports' => ['title','editor','custom-fields'],
            'menu_icon' => 'dashicons-hammer'
        ]);
    }

    /* =================== Time helpers =================== */
    private static function local_input_to_utc($local){
        if (!$local) return '';
        try {
            $tz = wp_timezone();
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $local, $tz);
            if(!$dt) return '';
            $dt->setTimezone(new DateTimeZone('UTC'));
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) { return ''; }
    }
    private static function utc_to_local_input($utc){
        if (!$utc) return '';
        try {
            $dt = new DateTime($utc, new DateTimeZone('UTC'));
            $dt->setTimezone(wp_timezone());
            return $dt->format('Y-m-d\TH:i');
        } catch (\Throwable $e) { return ''; }
    }

    /* =================== Settings =================== */
    public static function register_settings(){
        add_settings_section('gv_proc_section','Procurement Settings', function(){
            echo '<p>Configure Procurement suggestions and PO branding/email.</p>';
        }, 'general');

        register_setting('general', self::OPT_SAFETY_PCT, [
            'type'=>'integer','sanitize_callback'=>function($v){ $v=(int)$v; return max(0,min(100,$v)); }
        ]);
        add_settings_field(self::OPT_SAFETY_PCT, 'Safety stock percentage', function(){
            $v = (int) get_option(self::OPT_SAFETY_PCT, 0);
            echo '<input type="number" min="0" max="100" name="'.esc_attr(self::OPT_SAFETY_PCT).'" value="'.esc_attr($v).'" class="small-text"> %';
            echo '<p class="description">Applied to current demand; combined with per-product ROP (take the higher).</p>';
        }, 'general', 'gv_proc_section');

        register_setting('general', self::OPT_BRAND_LOGO_URL, ['type'=>'string','sanitize_callback'=>'esc_url_raw']);
        register_setting('general', self::OPT_BRAND_PRIMARY, ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
        register_setting('general', self::OPT_BRAND_ACCENT, ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
        register_setting('general', self::OPT_FROM_EMAIL, ['type'=>'string','sanitize_callback'=>'sanitize_email']);
        register_setting('general', self::OPT_FROM_NAME, ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
        register_setting('general', self::OPT_PO_EMAIL_SUBJECT, ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
        register_setting('general', self::OPT_PO_EMAIL_BODY, ['type'=>'string','sanitize_callback'=>'wp_kses_post']);

        add_settings_field(self::OPT_BRAND_LOGO_URL, 'PO brand logo URL', function(){
            $v = get_option(self::OPT_BRAND_LOGO_URL, '');
            echo '<input type="url" name="'.esc_attr(self::OPT_BRAND_LOGO_URL).'" value="'.esc_attr($v).'" class="regular-text" placeholder="https://.../logo.png">';
        }, 'general', 'gv_proc_section');

        add_settings_field(self::OPT_BRAND_PRIMARY, 'PO primary color (hex)', function(){
            $v = get_option(self::OPT_BRAND_PRIMARY, '#891734');
            echo '<input type="text" name="'.esc_attr(self::OPT_BRAND_PRIMARY).'" value="'.esc_attr($v).'" class="regular-text small-text" placeholder="#891734">';
        }, 'general', 'gv_proc_section');

        add_settings_field(self::OPT_BRAND_ACCENT, 'PO accent color (hex)', function(){
            $v = get_option(self::OPT_BRAND_ACCENT, '#f0e4d3');
            echo '<input type="text" name="'.esc_attr(self::OPT_BRAND_ACCENT).'" value="'.esc_attr($v).'" class="regular-text small-text" placeholder="#f0e4d3">';
        }, 'general', 'gv_proc_section');

        add_settings_field(self::OPT_FROM_NAME, 'PO email From name', function(){
            $v = get_option(self::OPT_FROM_NAME, get_bloginfo('name'));
            echo '<input type="text" name="'.esc_attr(self::OPT_FROM_NAME).'" value="'.esc_attr($v).'" class="regular-text">';
        }, 'general', 'gv_proc_section');

        add_settings_field(self::OPT_FROM_EMAIL, 'PO email From address', function(){
            $v = get_option(self::OPT_FROM_EMAIL, get_bloginfo('admin_email'));
            echo '<input type="email" name="'.esc_attr(self::OPT_FROM_EMAIL).'" value="'.esc_attr($v).'" class="regular-text">';
        }, 'general', 'gv_proc_section');

        add_settings_field(self::OPT_PO_EMAIL_SUBJECT, 'PO email subject', function(){
            $v = get_option(self::OPT_PO_EMAIL_SUBJECT, 'Purchase Order #{PO_NUMBER} – Gemini Valve Europe');
            echo '<input type="text" name="'.esc_attr(self::OPT_PO_EMAIL_SUBJECT).'" value="'.esc_attr($v).'" class="regular-text">';
            echo '<p class="description">Placeholders: {PO_NUMBER}, {SUPPLIER_NAME}</p>';
        }, 'general', 'gv_proc_section');

        add_settings_field(self::OPT_PO_EMAIL_BODY, 'PO email body', function(){
            $v = get_option(self::OPT_PO_EMAIL_BODY, "Dear {SUPPLIER_NAME},\n\nPlease find attached Purchase Order #{PO_NUMBER}.\n\nBest regards,\n{FROM_NAME}");
            echo '<textarea name="'.esc_attr(self::OPT_PO_EMAIL_BODY).'" rows="6" class="large-text">'.esc_textarea($v).'</textarea>';
            echo '<p class="description">Plain text (we’ll send as HTML). Placeholders: {PO_NUMBER}, {SUPPLIER_NAME}, {FROM_NAME}</p>';
        }, 'general', 'gv_proc_section');
    }

    /* =================== Product lead time =================== */
    public static function product_leadtime_field(){
        echo '<div class="options_group">';
        woocommerce_wp_text_input([
            'id'          => self::MK_LEADTIME_PRODUCT,
            'label'       => __('Procurement lead time (days)','gv'),
            'desc_tip'    => true,
            'description' => __('If empty, falls back to supplier default.','gv'),
            'type'        => 'number',
            'custom_attributes' => ['min'=>'0','step'=>'1'],
        ]);
        echo '</div>';
    }
    public static function product_leadtime_save($post_id){
        if (isset($_POST[self::MK_LEADTIME_PRODUCT])) {
            $v = sanitize_text_field($_POST[self::MK_LEADTIME_PRODUCT]);
            if ($v === '' || $v === null) {
                delete_post_meta($post_id, self::MK_LEADTIME_PRODUCT);
            } else {
                update_post_meta($post_id, self::MK_LEADTIME_PRODUCT, (int)$v);
            }
        }
    }

    /* =================== Supplier lead time box =================== */
    public static function supplier_leadtime_metabox(){
        $supplier_cpt_slug = 'gv_supplier'; // change if your supplier CPT slug differs
        if (!post_type_exists($supplier_cpt_slug)) return;

        add_meta_box('gv_supplier_lt','Procurement Lead Time (days)', function($post){
            $v = get_post_meta($post->ID, self::MK_LEADTIME_SUPPLIER, true);
            wp_nonce_field('gv_supplier_lt','gv_supplier_lt_nonce');
            echo '<p><input type="number" min="0" step="1" name="'.esc_attr(self::MK_LEADTIME_SUPPLIER).'" value="'.esc_attr($v).'" style="width:120px"> days</p>';
            echo '<p class="description">Default when product lead time is empty.</p>';

            /* Optional supplier address/contact fields */
            $fields = [
                self::MK_SUPP_ADDR_1 => 'Address line 1',
                self::MK_SUPP_ADDR_2 => 'Address line 2',
                self::MK_SUPP_CITY => 'City',
                self::MK_SUPP_POSTCODE => 'Postcode',
                self::MK_SUPP_STATE => 'State/Province',
                self::MK_SUPP_COUNTRY => 'Country',
                self::MK_SUPP_CONTACT => 'Contact name',
                self::MK_SUPP_EMAIL => 'Email',
                self::MK_SUPP_PO_EMAIL => 'PO email (orders)',
                self::MK_SUPP_PHONE => 'Phone',
                self::MK_SUPP_VAT => 'VAT/Tax ID',
            ];
            echo '<hr><p><strong>Supplier Address & Contact</strong></p>';
            foreach ($fields as $key=>$label){
                $val = get_post_meta($post->ID, $key, true);
                echo '<p><label>'.$label.'<br><input type="text" name="'.esc_attr($key).'" value="'.esc_attr($val).'" style="width:100%"></label></p>';
            }
        }, $supplier_cpt_slug, 'side', 'default');

        add_action('save_post_'.$supplier_cpt_slug, function($post_id){
            if (!isset($_POST['gv_supplier_lt_nonce']) || !wp_verify_nonce($_POST['gv_supplier_lt_nonce'],'gv_supplier_lt')) return;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (!current_user_can('edit_post', $post_id)) return;

            $lt = isset($_POST[GV_Procurement::MK_LEADTIME_SUPPLIER]) ? sanitize_text_field($_POST[GV_Procurement::MK_LEADTIME_SUPPLIER]) : '';
            if ($lt === '' || $lt === null) delete_post_meta($post_id, GV_Procurement::MK_LEADTIME_SUPPLIER);
            else update_post_meta($post_id, GV_Procurement::MK_LEADTIME_SUPPLIER, (int)$lt);

            $keys = [
                GV_Procurement::MK_SUPP_ADDR_1, GV_Procurement::MK_SUPP_ADDR_2, GV_Procurement::MK_SUPP_CITY,
                GV_Procurement::MK_SUPP_POSTCODE, GV_Procurement::MK_SUPP_STATE, GV_Procurement::MK_SUPP_COUNTRY,
                GV_Procurement::MK_SUPP_CONTACT, GV_Procurement::MK_SUPP_EMAIL, GV_Procurement::MK_SUPP_PO_EMAIL,
                GV_Procurement::MK_SUPP_PHONE, GV_Procurement::MK_SUPP_VAT
            ];
            foreach ($keys as $k){
                if (isset($_POST[$k])) {
                    $v = sanitize_text_field($_POST[$k]);
                    if ($v === '') delete_post_meta($post_id, $k);
                    else update_post_meta($post_id, $k, $v);
                }
            }
        });
    }

    private static function resolve_lead_time_days($product_id, $supplier_id){
        $p = get_post_meta($product_id, self::MK_LEADTIME_PRODUCT, true);
        if ($p !== '' && $p !== null) return (int)$p;
        if ($supplier_id) {
            $s = get_post_meta($supplier_id, self::MK_LEADTIME_SUPPLIER, true);
            if ($s !== '' && $s !== null) return (int)$s;
        }
        return null;
    }

    /* =================== Admin Menu =================== */
    public static function admin_menu(){
        add_menu_page('Procurement','Procurement','manage_woocommerce','gv-procure',[__CLASS__,'page_suggest'],'dashicons-clipboard',56);
        add_submenu_page('gv-procure','Suggestions','Suggestions','manage_woocommerce','gv-procure',[__CLASS__,'page_suggest']);
        add_submenu_page('gv-procure','Label Print','Label Print','manage_woocommerce','gv-labels',[__CLASS__,'page_labels']);
        add_submenu_page('gv-procure','Print PO','Print PO','manage_woocommerce','gv-po-print',[__CLASS__,'page_po_print']);
    }
    public static function po_row_actions($actions, $post){
        if ($post->post_type !== self::CPT_PO) return $actions;
        $url = admin_url('admin.php?page=gv-po-print&po_id='.$post->ID);
        $actions['gv_print'] = '<a href="'.esc_url($url).'" target="_blank">Print</a>';
        return $actions;
    }

    /* =================== PO header ETA =================== */
    public static function add_po_header_metabox(){
        add_meta_box('gv_po_dates','Supplier Confirmation',[__CLASS__,'mb_po_dates'], self::CPT_PO, 'side','default');
        add_filter('manage_edit-'.self::CPT_PO.'_columns', function($cols){
            $cols['gv_conf_eta'] = 'Confirmed ETA'; return $cols;
        });
        add_action('manage_'.self::CPT_PO.'_posts_custom_column', function($col,$post_id){
            if ($col !== 'gv_conf_eta') return;
            $v = get_post_meta($post_id, '_gv_po_confirmed_eta', true);
            echo $v ? esc_html( get_date_from_gmt($v,'Y-m-d H:i') ) : '—';
        },10,2);
    }
    public static function mb_po_dates($post){
        $val = get_post_meta($post->ID, '_gv_po_confirmed_eta', true);
        echo '<p><label for="gv_po_confirmed_eta"><strong>Confirmed delivery (ETA)</strong></label></p>';
        echo '<input type="datetime-local" id="gv_po_confirmed_eta" name="gv_po_confirmed_eta" value="'.esc_attr(self::utc_to_local_input($val)).'" style="width:100%;">';
        echo '<p class="description">Supplier-confirmed delivery for the whole PO. Per-line ETAs below.</p>';
        wp_nonce_field('gv_po_dates','gv_po_dates_nonce');
    }
    public static function save_po_header_metabox($post_id){
        if (!isset($_POST['gv_po_dates_nonce']) || !wp_verify_nonce($_POST['gv_po_dates_nonce'],'gv_po_dates')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        $local = isset($_POST['gv_po_confirmed_eta']) ? sanitize_text_field($_POST['gv_po_confirmed_eta']) : '';
        if ($local){
            $utc = self::local_input_to_utc($local);
            if ($utc) update_post_meta($post_id, '_gv_po_confirmed_eta', $utc);
        } else {
            delete_post_meta($post_id, '_gv_po_confirmed_eta');
        }
    }

    /* =================== PO Lines (qty + per-line ETA) =================== */
    public static function add_po_lines_metabox(){
        add_meta_box('gv_po_lines','PO Lines (qty + Confirmed ETA)',[__CLASS__,'mb_po_lines'], self::CPT_PO,'normal','high');
    }
    public static function mb_po_lines($post){
        global $wpdb;
        $table = $wpdb->prefix . self::TBL_PO_ITEMS;
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table` WHERE po_id=%d ORDER BY id ASC", $post->ID), ARRAY_A);

        wp_nonce_field('gv_po_lines_save','gv_po_lines_nonce');

        if (!$items){
            echo '<p>No PO lines found for this Purchase Order.</p>';
            return;
        }

        echo '<style>
            .gvpo-table{width:100%;border-collapse:collapse;}
            .gvpo-table th,.gvpo-table td{border:1px solid #ddd;padding:6px;vertical-align:middle;}
            .gvpo-table th{background:#f8f8f8;text-align:left;}
            .gvpo-num{width:110px;}
            .gvpo-dt{width:200px;}
            .gvpo-sku{color:#666;font-size:11px;}
        </style>';

        echo '<table class="gvpo-table"><thead><tr>
            <th>#</th><th>Product</th><th>Supplier SKU</th>
            <th>Unit Cost</th><th>Qty Ordered</th><th>Qty Received</th><th>Confirmed ETA</th>
        </tr></thead><tbody>';

        foreach($items as $row){
            $pid  = (int)$row['product_id'];
            $prod = $pid ? wc_get_product($pid) : null;
            $name = $prod ? $prod->get_name() : ('Product #'.$pid);
            $sku  = $prod ? $prod->get_sku()  : '';
            $edit = $prod ? get_edit_post_link($pid) : '';
            $eta  = self::utc_to_local_input($row['confirmed_eta']);

            echo '<tr>';
            echo '<td>'.esc_html($row['id']).'</td>';
            echo '<td>'.($edit ? '<a href="'.esc_url($edit).'" target="_blank">'.esc_html($name).'</a>' : esc_html($name));
            if ($sku) echo '<div class="gvpo-sku">SKU: '.esc_html($sku).'</div>';
            echo '</td>';
            echo '<td>'.esc_html($row['supplier_sku']).'</td>';
            echo '<td><input class="gvpo-num" type="number" step="0.01" name="gv_lines['.$row['id'].'][unit_cost]" value="'.esc_attr($row['unit_cost']).'"></td>';
            echo '<td><input class="gvpo-num" type="number" step="0.001" name="gv_lines['.$row['id'].'][qty_ordered]" value="'.esc_attr($row['qty_ordered']).'"></td>';
            echo '<td>'.esc_html($row['qty_received']).'</td>';
            echo '<td><input class="gvpo-dt" type="datetime-local" name="gv_lines['.$row['id'].'][confirmed_eta]" value="'.esc_attr($eta).'"></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p><button type="submit" class="button button-primary">Save PO Lines</button></p>';
        echo '<p class="description">Edit Qty, Unit Cost and per-line Confirmed ETA. Saved in UTC. Changes also save inline via REST.</p>';

        /* Inline REST save for ETA change (no reload) */
        $rest_nonce = wp_create_nonce('wp_rest');
        $rest_url   = esc_url_raw( rest_url('gv/v1') );
        echo '<script>
        (function(){
          const rest = "'.$rest_url.'";
          const nonce = "'.$rest_nonce.'";
          document.querySelectorAll(".gvpo-dt").forEach(function(inp){
            inp.addEventListener("change", function(){
              const m = this.name.match(/gv_lines\\[(\\d+)\\]\\[confirmed_eta\\]/);
              if(!m) return;
              const id = m[1];
              const local = this.value;
              let body = {confirmed_eta:""};
              if(local){
                const dt = new Date(local);
                if(!isNaN(dt)){
                  const iso = new Date(dt.getTime() - dt.getTimezoneOffset()*60000).toISOString().slice(0,19).replace("T"," ");
                  body.confirmed_eta = iso;
                }
              }
              fetch(rest+"/po-item/"+id+"/confirm", {
                method:"POST",
                headers:{"X-WP-Nonce": nonce, "Content-Type":"application/json"},
                body: JSON.stringify(body)
              });
            });
          });
        })();
        </script>';
    }
    public static function save_po_lines_metabox($post_id){
        if (!isset($_POST['gv_po_lines_nonce']) || !wp_verify_nonce($_POST['gv_po_lines_nonce'],'gv_po_lines_save')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (empty($_POST['gv_lines']) || !is_array($_POST['gv_lines'])) return;

        global $wpdb;
        $table = $wpdb->prefix . self::TBL_PO_ITEMS;

        foreach($_POST['gv_lines'] as $id => $row){
            $id = (int)$id;
            $unit_cost   = isset($row['unit_cost']) ? floatval($row['unit_cost']) : 0;
            $qty_ordered = isset($row['qty_ordered']) ? floatval($row['qty_ordered']) : 0;
            $eta_local   = isset($row['confirmed_eta']) ? sanitize_text_field($row['confirmed_eta']) : '';
            $eta_utc     = $eta_local ? self::local_input_to_utc($eta_local) : null;

            $owner = $wpdb->get_var($wpdb->prepare("SELECT po_id FROM `$table` WHERE id=%d", $id));
            if ((int)$owner !== (int)$post_id) continue;

            $data = ['unit_cost'=>$unit_cost, 'qty_ordered'=>$qty_ordered, 'confirmed_eta'=>$eta_utc];
            if (!$eta_utc) $data['confirmed_eta'] = null;

            $wpdb->update($table, $data, ['id'=>$id]);
        }
    }

    /* =================== Quick Receive (auto-serials) =================== */
    public static function add_po_receive_metabox(){
        add_meta_box('gv_po_receive','Quick Receive (auto-serials)',[__CLASS__,'mb_po_receive'], self::CPT_PO,'normal','default');
    }
    public static function mb_po_receive($post){
        global $wpdb;
        $t = $wpdb->prefix . self::TBL_PO_ITEMS;
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$t` WHERE po_id=%d ORDER BY id ASC", $post->ID), ARRAY_A);

        wp_nonce_field(self::NONCE_RECEIVE, 'gv_po_receive_nonce');

        if (!$items){ echo '<p>No PO lines.</p>'; return; }

        echo '<style>.gvrecv{width:100%;border-collapse:collapse}
        .gvrecv th,.gvrecv td{border:1px solid #ddd;padding:6px}
        .w80{width:80px;text-align:right}</style>';

        echo '<table class="gvrecv"><thead><tr>
            <th>#</th><th>Product</th><th>Ordered</th><th>Received</th><th>Remaining</th><th>Receive now</th>
        </tr></thead><tbody>';

        foreach($items as $row){
            $rem = max(0, (float)$row['qty_ordered'] - (float)$row['qty_received']);
            $pid = (int)$row['product_id'];
            $prod = $pid ? wc_get_product($pid) : null;
            $name = $prod ? $prod->get_name() : ('#'.$pid);
            echo '<tr>';
            echo '<td>'.esc_html($row['id']).'</td>';
            echo '<td>'.esc_html($name).'</td>';
            echo '<td class="w80">'.esc_html($row['qty_ordered']).'</td>';
            echo '<td class="w80">'.esc_html($row['qty_received']).'</td>';
            echo '<td class="w80">'.esc_html($rem).'</td>';
            echo '<td><input class="w80" type="number" min="0" step="1" name="recv['.$row['id'].']" value="'.esc_attr($rem).'"></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p><label><input type="checkbox" name="recv_make_serials" value="1" checked> Auto-generate serials (one per piece)</label></p>';
        echo '<p><button type="submit" class="button button-primary" name="gv_po_receive_submit" value="1">Receive Selected</button></p>';
    }
    public static function save_po_receive_metabox($post_id){
        if (!isset($_POST['gv_po_receive_nonce']) || !wp_verify_nonce($_POST['gv_po_receive_nonce'], self::NONCE_RECEIVE)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (empty($_POST['gv_po_receive_submit'])) return;

        global $wpdb;
        $t = $wpdb->prefix . self::TBL_PO_ITEMS;
        $serials_t = $wpdb->prefix . self::TBL_SERIALS;

        $recv = isset($_POST['recv']) && is_array($_POST['recv']) ? $_POST['recv'] : [];
        $make_serials = !empty($_POST['recv_make_serials']);

        foreach ($recv as $po_item_id => $q){
            $po_item_id = (int)$po_item_id;
            $qty = (int) max(0, (float)$q);
            if ($qty <= 0) continue;

            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$t` WHERE id=%d AND po_id=%d", $po_item_id, $post_id), ARRAY_A);
            if (!$row) continue;

            $remaining = max(0, (float)$row['qty_ordered'] - (float)$row['qty_received']);
            $receive_now = min($qty, (int)$remaining);
            if ($receive_now <= 0) continue;

            // 1) Update received qty
            $wpdb->update($t, [
                'qty_received' => (float)$row['qty_received'] + $receive_now,
            ], ['id'=>$po_item_id]);

            // 2) Generate serials per piece
            if ($make_serials){
                for($i=0; $i<$receive_now; $i++){
                    $sn = self::make_serial((int)$row['product_id']);
                    $wpdb->insert($serials_t, [
                        'serial'      => $sn,
                        'product_id'  => (int)$row['product_id'],
                        'po_item_id'  => $po_item_id,
                        'status'      => 'in_stock',
                        'received_at' => current_time('mysql', 1),
                        'meta'        => null,
                    ]);
                }
            }
        }
    }

    /* =================== Serials helpers + REST =================== */
    public static function rest(){
        /* Per-line ETA confirm */
        register_rest_route('gv/v1', '/po-item/(?P<po_item_id>\d+)/confirm', [
            'methods' => 'POST',
            'permission_callback' => function(){ return current_user_can('manage_woocommerce'); },
            'callback' => function($req){
                global $wpdb;
                $po_item_id = absint($req['po_item_id']);
                $eta = sanitize_text_field($req->get_param('confirmed_eta')); // UTC iso or empty
                $table = $wpdb->prefix . self::TBL_PO_ITEMS;
                $exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM `$table` WHERE id=%d", $po_item_id) );
                if (!$exists) return new WP_Error('not_found','PO item not found',['status'=>404]);
                $wpdb->update($table, ['confirmed_eta'=> ($eta ?: null)], ['id'=>$po_item_id]);
                return ['ok'=>true,'po_item_id'=>$po_item_id,'confirmed_eta'=>$eta ?: null];
            }
        ]);

        /* Serial lookups + scan-to-assign */
        register_rest_route('gv/v1', '/serial/(?P<sn>[^/]+)', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => [__CLASS__, 'rest_serial_lookup'],
        ]);
        register_rest_route('gv/v1', '/scan/assign', [
            'methods' => 'POST',
            'permission_callback' => function(){ return current_user_can('edit_shop_orders'); },
            'callback' => [__CLASS__, 'rest_scan_assign'],
        ]);
    }
    public static function rest_serial_lookup($req){
        global $wpdb;
        $sn = sanitize_text_field($req['sn']);
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}".self::TBL_SERIALS." WHERE serial=%s", $sn), ARRAY_A);
        if(!$row) return new WP_Error('not_found','Serial not found', ['status'=>404]);
        return $row;
    }
    public static function rest_scan_assign($req){
        global $wpdb;
        $order_id = absint($req['order_id']);
        $sn = sanitize_text_field($req['serial']);
        $order = wc_get_order($order_id);
        if(!$order) return new WP_Error('no_order','Order not found', ['status'=>404]);

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}".self::TBL_SERIALS." WHERE serial=%s", $sn));
        if(!$row) return new WP_Error('not_found','Serial not found', ['status'=>404]);
        if($row->status !== 'in_stock') return new WP_Error('bad_state','Serial not available', ['status'=>409]);

        $match_item_id = null;
        foreach($order->get_items() as $item_id => $item){
            if((int)$item->get_product_id() === (int)$row->product_id){ $match_item_id = $item_id; break; }
        }
        if(!$match_item_id) return new WP_Error('no_line','No matching line for product', ['status'=>409]);

        $wpdb->update($wpdb->prefix.self::TBL_SERIALS, [
            'status'=>'allocated','order_id'=>$order_id,'order_item_id'=>$match_item_id,
        ], ['id'=>$row->id]);

        return ['ok'=>true,'order_item_id'=>$match_item_id];
    }
    public static function on_order_completed($order_id){
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}".self::TBL_SERIALS."
             SET status='sold', sold_at=NOW()
             WHERE order_id=%d AND status='allocated'", $order_id));
    }

    public static function make_serial($product_id){
        $yw = gmdate('yW'); $seq = wp_rand(1, 9999);
        return sprintf('GV-%d-%s-%04d', $product_id, $yw, $seq);
    }
    public static function product_shortlink($product_id){
        return get_shortlink($product_id);
    }
    public static function serial_qr_target($product_id, $serial){
        return add_query_arg(['sn'=>$serial], self::product_shortlink($product_id));
    }

    /* =================== Suggestions engine =================== */
    public static function page_suggest(){
        if (!current_user_can('manage_woocommerce')) return;

        if (isset($_POST['gv_create_pos']) && check_admin_referer('gv_proc_suggest','gv_proc_suggest_nonce')) {
            $selected = isset($_POST['sel']) ? (array) $_POST['sel'] : [];
            $qtys     = isset($_POST['qty']) ? (array) $_POST['qty'] : [];
            $modes    = isset($_POST['mode']) ? (array) $_POST['mode'] : [];
            self::create_pos_from_selection($selected, $qtys, $modes);
        }

        $rows = self::compute_suggestions();

        echo '<div class="wrap"><h1>Procurement Suggestions</h1>';
        echo '<form method="post">';
        wp_nonce_field('gv_proc_suggest','gv_proc_suggest_nonce');

        echo '<p class="description">Safety stock = max(ROP, % of demand). Excludes Pending. Composites: auto buy/build by cost unless default set per product.</p>';

        echo '<style>.gv-sug{width:100%;border-collapse:collapse;margin-top:10px}
            .gv-sug th,.gv-sug td{border:1px solid #ddd;padding:6px}
            .num{width:90px;text-align:right}
            .mode{width:110px}
            .small{font-size:11px;color:#666}
        </style>';

        if (!$rows){
            echo '<p>No suggestions right now.</p></div>';
            return;
        }

        echo '<table class="gv-sug"><thead><tr>
            <th>Select</th><th>Product</th><th>Supplier</th>
            <th>On-hand</th><th>Reserved</th><th>Incoming</th>
            <th>Needed</th><th>Suggested</th><th>Mode</th><th>Unit cost</th><th>Lead time</th>
        </tr></thead><tbody>';

        foreach ($rows as $r){
            $pid = $r['product_id'];
            $prod = wc_get_product($pid);
            $name = $prod ? $prod->get_name() : ('#'.$pid);
            $edit = $prod ? get_edit_post_link($pid) : '';
            $supplier_name = $r['supplier_id'] ? get_the_title($r['supplier_id']) : '—';
            $lead_label = $r['lead_time_days'] !== null ? $r['lead_time_days'].' d' : '<span class="small">ask supplier</span>';

            echo '<tr>';
            echo '<td><input type="checkbox" name="sel['.$pid.']" value="1" '.checked(true, $r['suggested']>0, false).'></td>';
            echo '<td>'.($edit?'<a href="'.esc_url($edit).'" target="_blank">'.esc_html($name).'</a>':esc_html($name)).'<div class="small">SKU: '.esc_html($prod ? $prod->get_sku() : '').'</div></td>';
            echo '<td>'.esc_html($supplier_name).'</td>';
            echo '<td class="num">'.esc_html($r['on_hand']).'</td>';
            echo '<td class="num">'.esc_html($r['reserved']).'</td>';
            echo '<td class="num">'.esc_html($r['incoming']).'</td>';
            echo '<td class="num">'.esc_html($r['needed']).'</td>';
            echo '<td><input class="num" type="number" min="0" step="1" name="qty['.$pid.']" value="'.esc_attr($r['suggested']).'"></td>';
            if ($r['is_composite']) {
                echo '<td class="mode"><select name="mode['.$pid.']">
                        <option value="auto"'.selected($r['mode'],'auto',false).'>Auto</option>
                        <option value="buy" '.selected($r['mode'],'buy',false).'>Buy</option>
                        <option value="build" '.selected($r['mode'],'build',false).'>Build</option>
                      </select></td>';
            } else {
                echo '<td class="mode"><em class="small">n/a</em></td>';
            }
            echo '<td class="num">'.esc_html(number_format((float)$r['unit_cost'],2)).'</td>';
            echo '<td>'.$lead_label.'</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<p><button type="submit" name="gv_create_pos" class="button button-primary">Create Draft POs / WOs</button></p>';
        echo '</form></div>';
    }

    private static function compute_suggestions(){
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 500,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'OR',
                ['key' => '_manage_stock', 'value' => 'yes'],
                ['key' => self::MK_IS_COMPOSITE, 'value' => 'yes'],
            ],
        ];
        $q = new WP_Query($args);
        if (!$q->have_posts()) return [];

        $safety_pct = (int) get_option(self::OPT_SAFETY_PCT, 0);
        $rows = [];

        foreach ($q->posts as $pid){
            $supplier_id = (int) get_post_meta($pid, self::MK_SUPPLIER_ID, true);
            $is_comp = get_post_meta($pid, self::MK_IS_COMPOSITE, true) === 'yes';

            $on_hand  = (float) get_post_meta($pid, '_stock', true);
            $reserved = self::get_reserved_qty_for_product($pid);
            $incoming = self::get_incoming_qty_for_product($pid);

            $demand = max(0, $reserved);
            $rop    = (float) get_post_meta($pid, self::MK_ROP, true);

            $safety_from_pct = $safety_pct > 0 ? ceil( ($safety_pct/100) * $demand ) : 0;
            $safety = max((int)$rop, (int)$safety_from_pct);

            $required = max(0, $demand + $safety - max(0,$on_hand) - max(0,$incoming));

            $min_mult = max(1, (int) get_post_meta($pid, self::MK_MIN_ORDER_QTY, true));
            $suggested = $required > 0 ? (int) (ceil($required / $min_mult) * $min_mult) : 0;

            $unit_cost_buy = (float) get_post_meta($pid, self::MK_COST_PRICE, true);

            $mode = 'auto';
            if ($is_comp){
                $def = get_post_meta($pid, self::MK_DEFAULT_MODE, true);
                if ($def === 'buy' || $def === 'build') $mode = $def;
                if ($mode === 'auto'){
                    $unit_cost_build = self::rollup_build_cost($pid);
                    if ($unit_cost_build !== null && $unit_cost_buy > 0){
                        $mode = ($unit_cost_build < $unit_cost_buy) ? 'build' : 'buy';
                    } elseif ($def) {
                        $mode = $def;
                    } else {
                        $mode = 'buy';
                    }
                }
            }

            $lead_time = self::resolve_lead_time_days($pid, $supplier_id);

            $rows[] = [
                'product_id'   => $pid,
                'supplier_id'  => $supplier_id ?: 0,
                'is_composite' => $is_comp,
                'on_hand'      => (int)$on_hand,
                'reserved'     => (int)$reserved,
                'incoming'     => (int)$incoming,
                'needed'       => (int)$required,
                'suggested'    => (int)$suggested,
                'mode'         => $mode,
                'unit_cost'    => $unit_cost_buy,
                'lead_time_days'=> $lead_time,
            ];
        }
        return array_values(array_filter($rows, function($r){ return $r['suggested'] > 0; }));
    }

    private static function get_reserved_qty_for_product($product_id){
        $statuses = ['wc-processing','wc-on-hold']; // exclude wc-pending
        $qty = 0;
        $orders = wc_get_orders(['status'=>$statuses,'limit'=>-1,'return'=>'ids']);
        foreach ($orders as $oid){
            $order = wc_get_order($oid);
            foreach ($order->get_items() as $item){
                if ((int)$item->get_product_id() === (int)$product_id){
                    $qty += (float)$item->get_quantity();
                }
            }
        }
        return $qty;
    }
    private static function get_incoming_qty_for_product($product_id){
        global $wpdb;
        $t = $wpdb->prefix . self::TBL_PO_ITEMS;
        $sum = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(qty_ordered - qty_received),0) FROM `$t` WHERE product_id=%d", $product_id
        ));
        return max(0,$sum);
    }

    private static function rollup_build_cost($product_id, $depth=0){
        if ($depth > 5) return null;
        $is_comp = get_post_meta($product_id, self::MK_IS_COMPOSITE, true) === 'yes';
        if (!$is_comp){
            $c = (float) get_post_meta($product_id, self::MK_COST_PRICE, true);
            return $c > 0 ? $c : null;
        }
        $json = get_post_meta($product_id, self::MK_CHILDREN_JSON, true);
        $arr = $json ? json_decode($json, true) : null;
        if (!is_array($arr)) return null;

        $total = 0;
        foreach ($arr as $row){
            $cid = isset($row['id']) ? (int)$row['id'] : 0;
            $cq  = isset($row['qty']) ? (float)$row['qty'] : 0;
            if ($cid && $cq > 0){
                $c = self::rollup_build_cost($cid, $depth+1);
                if ($c === null) return null;
                $total += $c * $cq;
            }
        }
        return $total;
    }

    private static function explode_bom($product_id, $parent_qty=1, $depth=0){
        if ($depth > 6) return [];
        $is_comp = (get_post_meta($product_id, self::MK_IS_COMPOSITE, true) === 'yes');
        if (!$is_comp){
            return [ ['product_id'=>(int)$product_id, 'qty'=>(float)$parent_qty ] ];
        }
        $json = get_post_meta($product_id, self::MK_CHILDREN_JSON, true);
        $arr = $json ? json_decode($json, true) : null;
        if (!is_array($arr) || !$arr) return [];

        $flat = [];
        foreach ($arr as $row){
            $cid = isset($row['id']) ? (int)$row['id'] : 0;
            $cq  = isset($row['qty']) ? (float)$row['qty'] : 0;
            if ($cid && $cq > 0){
                $sub = self::explode_bom($cid, $parent_qty * $cq, $depth+1);
                foreach ($sub as $s){
                    $key = $s['product_id'];
                    if (!isset($flat[$key])) $flat[$key] = 0;
                    $flat[$key] += $s['qty'];
                }
            }
        }
        $out = [];
        foreach ($flat as $pid=>$q) $out[] = ['product_id'=>$pid, 'qty'=>$q];
        return $out;
    }

    private static function create_pos_from_selection(array $sel, array $qtys, array $modes){
        if (!$sel) { echo '<div class="notice notice-warning"><p>No lines selected.</p></div>'; return; }

        $lines_by_supplier = [];
        foreach ($sel as $pid => $flag){
            if (!$flag) continue;
            $pid = (int)$pid;
            $qty = isset($qtys[$pid]) ? (int)$qtys[$pid] : 0;
            if ($qty <= 0) continue;

            $supplier_id = (int) get_post_meta($pid, self::MK_SUPPLIER_ID, true);
            $supplier_id = $supplier_id ?: 0;

            $mode = isset($modes[$pid]) ? sanitize_text_field($modes[$pid]) : 'auto';
            if (!in_array($mode, ['auto','buy','build'], true)) $mode = 'auto';

            $lines_by_supplier[$supplier_id][] = [
                'product_id' => $pid,
                'qty'        => $qty,
                'mode'       => $mode,
            ];
        }
        if (!$lines_by_supplier){ echo '<div class="notice notice-warning"><p>No valid quantities.</p></div>'; return; }

        foreach ($lines_by_supplier as $supplier_id => $lines){
            $po_id = null;
            $need_lt_confirm = false;

            foreach ($lines as $L){
                $pid = (int)$L['product_id'];
                $qty = (int)$L['qty'];
                $mode = $L['mode'];
                $is_comp = (get_post_meta($pid, self::MK_IS_COMPOSITE, true) === 'yes');

                if ($is_comp && $mode === 'build') {
                    $wo_id = wp_insert_post([
                        'post_type'   => self::CPT_WO,
                        'post_status' => 'draft',
                        'post_title'  => 'WO – '.get_the_title($pid).' × '.$qty.' – '.current_time('mysql'),
                    ]);
                    if (!is_wp_error($wo_id)) {
                        $bom = self::explode_bom($pid, $qty);
                        update_post_meta($wo_id, '_gv_wo_product_id', $pid);
                        update_post_meta($wo_id, '_gv_wo_qty', $qty);
                        update_post_meta($wo_id, '_gv_wo_bom', wp_json_encode($bom));
                    }
                    continue;
                }

                if (!$po_id){
                    $po_id = wp_insert_post([
                        'post_type'   => self::CPT_PO,
                        'post_status' => 'draft',
                        'post_title'  => 'PO Draft – '.($supplier_id ? get_the_title($supplier_id) : 'Unassigned Supplier').' – '.current_time('mysql'),
                    ]);
                    if (!is_wp_error($po_id) && $supplier_id) update_post_meta($po_id, '_gv_po_supplier_id', $supplier_id);
                }
                if (is_wp_error($po_id)) continue;

                $desc = get_the_title($pid);
                $ssku = get_post_meta($pid, self::MK_SUPPLIER_SKU, true);
                $cost = (float) get_post_meta($pid, self::MK_COST_PRICE, true);
                $lead = self::resolve_lead_time_days($pid, $supplier_id);
                if ($lead === null) $need_lt_confirm = true;

                self::insert_po_item($po_id, $pid, $supplier_id, $desc, $ssku, $cost, $qty, [
                    'mode' => 'buy',
                    'lead_time_days' => $lead,
                ]);
            }

            if ($po_id && $need_lt_confirm){
                $note = "Lead time request: One or more lines have no lead_time_days set. Please confirm delivery time in your order confirmation.";
                $existing = get_post_field('post_content', $po_id);
                wp_update_post(['ID'=>$po_id, 'post_content'=> $existing ? ($existing."\n\n".$note) : $note ]);
            }
        }

        echo '<div class="notice notice-success"><p>Draft POs (for buy lines) and Work Orders (for build lines) have been created.</p></div>';
    }

    private static function insert_po_item($po_id, $product_id, $supplier_id, $description, $supplier_sku, $unit_cost, $qty_ordered, array $extra_meta){
        global $wpdb;
        $table = $wpdb->prefix . self::TBL_PO_ITEMS;
        $meta = wp_json_encode($extra_meta);
        $wpdb->insert($table, [
            'po_id'        => (int)$po_id,
            'product_id'   => (int)$product_id,
            'supplier_id'  => (int)$supplier_id,
            'description'  => $description,
            'supplier_sku' => $supplier_sku,
            'unit_cost'    => (float)$unit_cost,
            'qty_ordered'  => (float)$qty_ordered,
            'qty_received' => 0,
            'confirmed_eta'=> null,
            'meta'         => $meta,
        ]);
    }

    /* =================== Labels page (placeholder) =================== */
    public static function page_labels(){
        echo '<div class="wrap"><h1>Print Labels</h1><div id="gv-labels"></div>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script></div>';
    }

    /* =================== Printable PO (with brand + email) =================== */
    public static function page_po_print(){
        if (!current_user_can('manage_woocommerce')) return;
        $po_id = isset($_GET['po_id']) ? absint($_GET['po_id']) : 0;
        if (!$po_id || get_post_type($po_id)!==self::CPT_PO){ echo '<div class="wrap"><p>PO not found.</p></div>'; return; }

        $html = self::build_po_html($po_id, true);
        // Echo complete document
        echo $html; exit;
    }

    /** Build the full HTML for a PO; $with_buttons adds Print/Email controls. */
    private static function build_po_html($po_id, $with_buttons=false){
        global $wpdb;
        $t = $wpdb->prefix . self::TBL_PO_ITEMS;
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$t` WHERE po_id=%d ORDER BY id ASC", $po_id), ARRAY_A);
        $supplier_id = (int) get_post_meta($po_id, '_gv_po_supplier_id', true);
        $supplier = $supplier_id ? get_post($supplier_id) : null;
        $eta = get_post_meta($po_id, '_gv_po_confirmed_eta', true);

        $supp = self::get_supplier_contact_block($supplier_id);

        $logo   = get_option(self::OPT_BRAND_LOGO_URL, '');
        $primary= get_option(self::OPT_BRAND_PRIMARY, '#891734');
        $accent = get_option(self::OPT_BRAND_ACCENT, '#f0e4d3');

        ob_start();
        ?>
<html>
<head>
<meta charset="utf-8">
<title>PO <?php echo esc_html($po_id); ?></title>
<style>
    :root { --primary: <?php echo esc_html($primary); ?>; --accent: <?php echo esc_html($accent); ?>; }
    body{font:12px/1.35 system-ui,Segoe UI,Roboto,Arial;color:#111;margin:24px}
    .noprint{margin-bottom:10px}
    .bar{display:flex;align-items:center;gap:16px;border:1px solid #ddd;padding:10px;background:var(--accent)}
    .brand{font-size:18px;font-weight:700;color:#111}
    .logo{max-height:40px}
    .muted{color:#666}
    .tag{display:inline-block;background:var(--primary);color:#fff;padding:2px 8px;border-radius:4px;font-weight:600}
    table{width:100%;border-collapse:collapse;margin-top:14px}
    th,td{border:1px solid #ddd;padding:6px}
    th{background:#f7f7f7;text-align:left}
    .right{text-align:right}
    .small{font-size:11px;color:#666}
    .box{border:1px solid #ddd;padding:10px}
    @media print {.noprint{display:none}}
</style>
</head>
<body>
<?php if ($with_buttons): ?>
<div class="noprint">
    <a class="button" href="#" onclick="window.print();return false;">Print / Save as PDF</a>
    <?php if (!empty($supp['po_email']) || !empty($supp['email'])): ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-left:8px">
            <input type="hidden" name="action" value="gv_send_po_pdf">
            <input type="hidden" name="po_id" value="<?php echo esc_attr($po_id); ?>">
            <?php wp_nonce_field('gv_send_po_pdf_'.$po_id); ?>
            <button class="button button-primary">Email Supplier</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="bar">
    <?php if ($logo): ?><img class="logo" src="<?php echo esc_url($logo); ?>" alt="Logo"><?php endif; ?>
    <div class="brand">Gemini Valve Europe</div>
    <div class="tag">Purchase Order</div>
</div>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-top:14px">
    <div>
        <div class="muted">PO #: <?php echo esc_html($po_id); ?></div>
        <div class="muted">Date: <?php echo esc_html( get_date_from_gmt( get_post_time('c', true, $po_id), 'Y-m-d') ); ?></div>
        <?php if ($eta): ?><div class="muted">Confirmed ETA: <?php echo esc_html(get_date_from_gmt($eta, 'Y-m-d H:i')); ?></div><?php endif; ?>
    </div>
    <div class="box" style="min-width:280px">
        <div><strong>Supplier</strong></div>
        <div><?php echo esc_html($supplier ? $supplier->post_title : '—'); ?></div>
        <?php if ($supp['address_html']): ?><div class="small" style="margin-top:6px"><?php echo $supp['address_html']; //safe ?></div><?php endif; ?>
        <?php if ($supp['contact_html']): ?><div class="small" style="margin-top:6px"><?php echo $supp['contact_html']; //safe ?></div><?php endif; ?>
        <?php if (!empty($supp['po_email']) || !empty($supp['email'])): ?>
            <div class="small" style="margin-top:6px">PO email: <strong><?php echo esc_html($supp['po_email'] ?: $supp['email']); ?></strong></div>
        <?php endif; ?>
    </div>
</div>

<table>
    <thead><tr>
        <th>#</th><th>Product</th><th>Supplier SKU</th><th class="right">Qty</th><th class="right">Unit cost</th><th class="right">Line total</th>
    </tr></thead>
    <tbody>
        <?php $sum = 0;
        foreach($items as $row):
            $pid = (int)$row['product_id'];
            $name = get_the_title($pid);
            $qty  = (float)$row['qty_ordered'];
            $cost = (float)$row['unit_cost'];
            $line = $qty * $cost; $sum += $line; ?>
            <tr>
                <td><?php echo esc_html($row['id']); ?></td>
                <td><?php echo esc_html($name); ?></td>
                <td><?php echo esc_html($row['supplier_sku']); ?></td>
                <td class="right"><?php echo esc_html($qty); ?></td>
                <td class="right"><?php echo number_format($cost,2); ?></td>
                <td class="right"><?php echo number_format($line,2); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr><td colspan="5" class="right"><strong>Total</strong></td><td class="right"><strong><?php echo number_format($sum,2); ?></strong></td></tr>
    </tbody>
</table>

<p class="small" style="margin-top:10px">If any line has no known lead time, please confirm the delivery time in your order confirmation.</p>

</body>
</html>
        <?php
        return ob_get_clean();
    }

    private static function get_supplier_contact_block($supplier_id){
        if (!$supplier_id) return ['address_html'=>'','contact_html'=>'','po_email'=>'','email'=>''];

        $addr1 = get_post_meta($supplier_id, self::MK_SUPP_ADDR_1, true);
        $addr2 = get_post_meta($supplier_id, self::MK_SUPP_ADDR_2, true);
        $city  = get_post_meta($supplier_id, self::MK_SUPP_CITY, true);
        $pc    = get_post_meta($supplier_id, self::MK_SUPP_POSTCODE, true);
        $st    = get_post_meta($supplier_id, self::MK_SUPP_STATE, true);
        $cty   = get_post_meta($supplier_id, self::MK_SUPP_COUNTRY, true);

        $contact = get_post_meta($supplier_id, self::MK_SUPP_CONTACT, true);
        $email   = get_post_meta($supplier_id, self::MK_SUPP_EMAIL, true);
        $poemail = get_post_meta($supplier_id, self::MK_SUPP_PO_EMAIL, true);
        $phone   = get_post_meta($supplier_id, self::MK_SUPP_PHONE, true);
        $vat     = get_post_meta($supplier_id, self::MK_SUPP_VAT, true);

        /* Build address */
        $a = [];
        if ($addr1) $a[] = esc_html($addr1);
        if ($addr2) $a[] = esc_html($addr2);
        $line = trim(($pc ? esc_html($pc).' ' : '').($city ? esc_html($city) : ''));
        if ($line) $a[] = $line;
        $line2 = trim(($st ? esc_html($st).', ' : '').($cty ? esc_html($cty) : ''));
        if ($line2) $a[] = $line2;
        $addr_html = $a ? implode('<br>', $a) : '';

        /* Build contact */
        $c = [];
        if ($contact) $c[] = 'Attn: '.esc_html($contact);
        if ($email)   $c[] = 'Email: '.esc_html($email);
        if ($phone)   $c[] = 'Phone: '.esc_html($phone);
        if ($vat)     $c[] = 'VAT: '.esc_html($vat);
        $contact_html = $c ? implode(' · ', $c) : '';

        return ['address_html'=>$addr_html,'contact_html'=>$contact_html,'po_email'=>$poemail,'email'=>$email];
    }

    /* =================== Email PO (PDF) handler =================== */
    public static function handle_send_po_pdf(){
        if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
        $po_id = isset($_POST['po_id']) ? absint($_POST['po_id']) : 0;
        if (!$po_id || get_post_type($po_id)!==self::CPT_PO) wp_die('PO not found');
        check_admin_referer('gv_send_po_pdf_'.$po_id);

        $supplier_id = (int) get_post_meta($po_id, '_gv_po_supplier_id', true);
        $supplier = $supplier_id ? get_post($supplier_id) : null;
        $supp = self::get_supplier_contact_block($supplier_id);
        $to = $supp['po_email'] ?: $supp['email'];
        if (!$to) wp_die('No supplier email found (set _gv_supplier_po_email or _gv_supplier_email).');

        $from_email = get_option(self::OPT_FROM_EMAIL, get_bloginfo('admin_email'));
        $from_name  = get_option(self::OPT_FROM_NAME, get_bloginfo('name'));

        $repl = [
            '{PO_NUMBER}'     => $po_id,
            '{SUPPLIER_NAME}' => $supplier ? $supplier->post_title : 'Supplier',
            '{FROM_NAME}'     => $from_name,
        ];
        $subject_tpl = get_option(self::OPT_PO_EMAIL_SUBJECT, 'Purchase Order #{PO_NUMBER} – Gemini Valve Europe');
        $body_tpl    = get_option(self::OPT_PO_EMAIL_BODY, "Dear {SUPPLIER_NAME},\n\nPlease find attached Purchase Order #{PO_NUMBER}.\n\nBest regards,\n{FROM_NAME}");
        $subject = strtr($subject_tpl, $repl);
        $body_txt = strtr($body_tpl, $repl);
        $body_html = nl2br(esc_html($body_txt));

        // Prepare attachment (PDF if Dompdf available; else HTML)
        $uploads = wp_upload_dir();
        $dir = trailingslashit($uploads['basedir']).'gv_po';
        if (!file_exists($dir)) wp_mkdir_p($dir);
        $pdf_path = $dir.'/PO-'.$po_id.'.pdf';
        $html_path = $dir.'/PO-'.$po_id.'.html';

        $html = self::build_po_html($po_id, false);

        $attachments = [];
        if (class_exists('\\Dompdf\\Dompdf')) {
            // Render PDF via Dompdf
            try {
                $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled'=>true]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4','portrait');
                $dompdf->render();
                $pdf = $dompdf->output();
                file_put_contents($pdf_path, $pdf);
                $attachments[] = $pdf_path;
            } catch (\Throwable $e){
                // Fallback to HTML attachment
                file_put_contents($html_path, $html);
                $attachments[] = $html_path;
            }
        } else {
            // Fallback to HTML attachment
            file_put_contents($html_path, $html);
            $attachments[] = $html_path;
        }

        // Send mail
        $headers = [];
        if ($from_name || $from_email){
            $headers[] = 'From: '.($from_name ? $from_name.' ' : '').'<'.$from_email.'>';
        }
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        $ok = wp_mail($to, $subject, '<html><body>'.$body_html.'</body></html>', $headers, $attachments);

        // Redirect back with admin notice
        $dest = add_query_arg([
            'page'  => 'gv-po-print',
            'po_id' => $po_id,
            'gv_po_sent' => $ok ? '1' : '0'
        ], admin_url('admin.php'));
        wp_safe_redirect($dest);
        exit;
    }

    /* =================== Helpers =================== */
}




GV_Procurement::init();

