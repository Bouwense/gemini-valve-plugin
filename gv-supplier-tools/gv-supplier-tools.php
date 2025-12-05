<?php
/**
 * Plugin Name: GV – Supplier Tools (Consolidated + Debug)
 * Description: Suppliers CPT UI (enrichment + address/contact + commercials) and WooCommerce product procurement tab. Includes debug logging and a Tools page for quick meta inspection.
 * Version:     1.8.4
 * Author:      Gemini Valve
 */
if ( ! defined('ABSPATH') ) exit;

/* Toggle plugin-level logging (also respects WP_DEBUG_LOG). */
if ( ! defined('GV_ST_DEBUG') ) define('GV_ST_DEBUG', true);

/* wc_format_decimal() fallback if WooCommerce not loaded yet. */
if ( ! function_exists('gvst_wc_format_decimal') ) {
	function gvst_wc_format_decimal($val) {
		if ( function_exists('wc_format_decimal') ) return wc_format_decimal($val);
		$val = is_string($val) ? str_replace(',', '.', $val) : $val;
		return (string) round((float) $val, 6);
	}
}

class GV_Supplier_Tools {
	/* === Meta keys === */
	const K_MARGIN        = '_gv_margin_percent';
	const K_LEADTIME      = '_gv_supplier_lead_time_days';
	const K_ADDR1         = '_gv_addr_line1';
	const K_ADDR2         = '_gv_addr_line2';
	const K_CITY          = '_gv_addr_city';
	const K_POSTCODE      = '_gv_addr_postcode';
	const K_STATE         = '_gv_addr_state';
	const K_COUNTRY       = '_gv_addr_country';
	const K_CONTACT       = '_gv_contact_name';
	const K_EMAIL         = '_gv_email';
	const K_PO_EMAIL      = '_gv_po_email';
	const K_PHONE         = '_gv_phone';
	const K_VAT           = '_gv_vat_id';
	const K_WEBSITE       = '_gv_website';
	const K_NOTES         = '_gv_notes';

	/* Enrichment */
	const K_METHOD        = '_gv_sup_method';
	const K_SEARCH_TPL    = '_gv_sup_search_url_tpl';
	const K_PRODUCT_TPL   = '_gv_sup_product_url_tpl';
	const K_LINK_REGEX    = '_gv_sup_result_link_regex';
	const K_HEADERS_JSON  = '_gv_sup_headers_json';
	const K_AI_HINTS      = '_gv_sup_ai_hints';
	const K_AI_MODEL      = '_gv_sup_ai_model';
	const K_AI_SIZE_KEYS  = '_gv_sup_ai_size_keys';
	const K_WEBHOOK_URL   = '_gv_sup_webhook_url';

	/* WooCommerce product meta */
	const PRODUCT_SUPPLIER_META = '_gv_proc_supplier_id';
	const PRODUCT_COST          = '_gv_proc_cost_price';
	const PRODUCT_SUP_SKU       = '_gv_proc_supplier_sku';
	const PRODUCT_DESC          = '_gv_proc_description';

	/* Metabox IDs */
	const MB_ENRICHMENT_ID  = 'gvst_enrichment';
	const MB_LEAD_ID        = 'gvst_lead';
	const MB_ADDRCONTACT_ID = 'gvst_address_contact';
	const MB_COMMERCIALS_ID = 'gvst_commercials';

	/* Admin transient for last error notice */
	const T_LAST_ERROR      = 'gvst_last_error_notice';

	public function __construct() {
		/* Remove legacy conflicting savers as early as possible */
		add_action('plugins_loaded', [$this, 'unhook_legacy_savers'], 1);

		/* Debug hooks */
		if ( GV_ST_DEBUG ) {
			add_action('init', function(){ register_shutdown_function([$this,'shutdown_catcher']); });
			add_action('admin_notices', [$this, 'maybe_show_admin_error']);
			add_action('admin_menu',    [$this, 'add_tools_debug_page']);
		}

		/* CPT + UI */
		add_action('init',                 [$this, 'register_cpt']);
		add_action('add_meta_boxes',       [$this, 'register_metaboxes'], 20);

		/* Saves (ordered so Enrichment runs first if all fire) */
		add_action('save_post_gv_supplier',[$this, 'save_metabox_enrichment'], 5);
		add_action('save_post_gv_supplier',[$this, 'save_metabox_leadtime']);
		add_action('save_post_gv_supplier',[$this, 'save_metabox_address_contact']);
		add_action('save_post_gv_supplier',[$this, 'save_metabox_commercials']);

		/* Admin list */
		add_filter('manage_gv_supplier_posts_columns', [$this, 'cols']);
		add_action('manage_gv_supplier_posts_custom_column', [$this, 'coldata'], 10, 2);
		add_filter('manage_edit-gv_supplier_sortable_columns', function($c){ $c['gv_margin']='gv_margin'; return $c; });
		add_action('pre_get_posts', function($q){
			if ( is_admin() && $q->is_main_query() && $q->get('post_type')==='gv_supplier' && $q->get('orderby')==='gv_margin' ) {
				$q->set('meta_key', self::K_MARGIN);
				$q->set('orderby', 'meta_value_num');
			}
		});
		add_action('pre_get_posts', function($q){
			if ( is_admin() && $q->is_main_query() && $q->get('post_type')==='gv_supplier' && ! $q->get('orderby') ) {
				$q->set('orderby','title'); $q->set('order','ASC');
			}
		});

		/* WooCommerce product tab */
		add_filter('woocommerce_product_data_tabs',       [$this, 'wc_tab']);
		add_action('woocommerce_product_data_panels',     [$this, 'wc_panel']);
		add_action('woocommerce_admin_process_product_object', [$this, 'wc_save']);
	}

	/* ========== Debug helpers ========== */
	public static function log($msg, $ctx = []) {
		if ( ! GV_ST_DEBUG ) return;
		if ( is_array($msg) || is_object($msg) ) $msg = print_r($msg, true);
		if ( ! empty($ctx) ) $msg .= ' | ' . print_r($ctx, true);
		@error_log('[GVST] '.$msg);
	}
	private function record_error_notice($msg) {
		set_transient(self::T_LAST_ERROR, sanitize_text_field($msg), 60);
	}
	public function maybe_show_admin_error() {
		$err = get_transient(self::T_LAST_ERROR);
		if ( $err ) {
			delete_transient(self::T_LAST_ERROR);
			echo '<div class="notice notice-error is-dismissible"><p>'
				. esc_html__('GV Supplier Tools error:', 'gv') . ' ' . esc_html($err) .
			'</p></div>';
		}
	}
	public function shutdown_catcher() {
		$e = error_get_last();
		if ( $e && in_array($e['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR], true) ) {
			self::log('Shutdown fatal', $e);
			$this->record_error_notice('Fatal (see debug.log): '.$e['file'].' #'.$e['line']);
		}
	}

	/* Unhook any legacy savers that might still be around */
	public function unhook_legacy_savers() {
		$targets = [
			'gvst_save_enrichment_fields',
			'gv_supplier_save_enrichment',
			'gv_supplier_save_details',
			'gv_supplier_save_address',
		];
		foreach ($targets as $fn) {
			if ( has_action('save_post_gv_supplier', $fn) ) {
				remove_action('save_post_gv_supplier', $fn);
				self::log('Removed legacy saver', ['callback'=>$fn]);
			}
		}
		/* Also scan callbacks and remove matches by name */
		global $wp_filter;
		if ( isset($wp_filter['save_post_gv_supplier']) ) {
			$hook = $wp_filter['save_post_gv_supplier'];
			$callbacks = is_object($hook) ? $hook->callbacks : (array) $hook;
			foreach ($callbacks as $prio => $arr) {
				foreach ($arr as $id => $cb) {
					if ( is_array($cb['function']) ) {
						$name = is_string($cb['function'][0]) ? $cb['function'][0].'::'.$cb['function'][1] : $cb['function'][1];
					} else {
						$name = is_string($cb['function']) ? $cb['function'] : $id;
					}
					if ( stripos($name, 'gvst_save_enrichment_fields') !== false ) {
						remove_action('save_post_gv_supplier', $cb['function'], (int)$prio);
						self::log('Removed detected legacy saver', ['name'=>$name,'prio'=>$prio]);
					}
				}
			}
		}
	}

	/* ========== CPT ========== */
	public function register_cpt() {
		if ( post_type_exists('gv_supplier') ) return;
		register_post_type('gv_supplier', [
			'labels' => [
				'name'=>__('Suppliers','gv'), 'singular_name'=>__('Supplier','gv'),
				'add_new_item'=>__('Add New Supplier','gv'), 'edit_item'=>__('Edit Supplier','gv'),
			],
			'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>['title','editor'],
		]);
	}

	/* ========== Metaboxes ========== */
	public function register_metaboxes() {
		/* Remove any legacy boxes by known IDs (prevents duplicate “top” section) */
		$legacy = ['gv_supplier_details','gv_supplier_address','gv_supplier_enrichment','gv_supplier_lead','gv_supplier_lead_time','gv_supplier_proc_lead'];
		foreach ($legacy as $lid) {
			remove_meta_box($lid,'gv_supplier','normal');
			remove_meta_box($lid,'gv_supplier','side');
			remove_meta_box($lid,'gv_supplier','advanced');
		}

		// 1) Enrichment
		add_meta_box(self::MB_ENRICHMENT_ID, __('Enrichment (AI/API)','gv'), function($post){
			try {
				wp_nonce_field('gv_enrich_save','gv_enrich_nonce');
				$fields = [
					/* key, label, type, options? */
					array(self::K_METHOD,     __('Method','gv'), 'select', array('html_ai'=>'HTML + ChatGPT','json_webhook'=>'JSON Webhook')),
					array(self::K_SEARCH_TPL,  __('Search URL template','gv'), 'text'),
					array(self::K_PRODUCT_TPL, __('Product URL template (optional)','gv'), 'text'),
					array(self::K_LINK_REGEX,  __('Result link regex','gv'), 'text'),
					array(self::K_HEADERS_JSON,__('Extra HTTP headers (JSON)','gv'), 'textarea'),
					array(self::K_AI_MODEL,    __('AI model','gv'), 'text'),
					array(self::K_AI_HINTS,    __('AI instructions / hints','gv'), 'textarea'),
					array(self::K_AI_SIZE_KEYS,__('AI size keys (CSV)','gv'), 'text'),
					array(self::K_WEBHOOK_URL, __('Webhook URL (if JSON Webhook)','gv'), 'url'),
				];
				echo '<table class="form-table"><tbody>';
				foreach ($fields as $row) {
					$key  = $row[0]; $lbl = $row[1]; $type = $row[2]; $opts = isset($row[3]) ? $row[3] : null;
					$val  = get_post_meta($post->ID, $key, true);
					$val  = ($val !== '' && $val !== null) ? $val : '';
					echo '<tr><th><label for="'.$key.'">'.esc_html($lbl).'</label></th><td>';
					if ( $type === 'textarea' ) {
						echo '<textarea class="large-text" rows="4" id="'.$key.'" name="'.$key.'">'.esc_textarea($val).'</textarea>';
					} elseif ( $type === 'select' ) {
						echo '<select id="'.$key.'" name="'.$key.'">';
						if ( is_array($opts) ) {
							foreach ($opts as $v => $text) {
								echo '<option value="'.esc_attr($v).'" '.selected($val,$v,false).'>'.esc_html($text).'</option>';
							}
						}
						echo '</select>';
					} else {
						echo '<input type="'.$type.'" class="regular-text" id="'.$key.'" name="'.$key.'" value="'.esc_attr($val).'" />';
					}
					echo '</td></tr>';
				}
				echo '</tbody></table>';
			} catch (\Throwable $t) {
				self::log('ENRICH render fail', ['err'=>$t->getMessage()]);
				echo '<p>'.esc_html__('Error rendering box (see debug.log).','gv').'</p>';
			}
		}, 'gv_supplier', 'normal', 'default');

		// 2) Lead time
		add_meta_box(self::MB_LEAD_ID, __('Procurement Lead Time (days)','gv'), function($post){
			try {
				wp_nonce_field('gv_lead_save','gv_lead_nonce');
				$lead = (string) get_post_meta($post->ID, self::K_LEADTIME, true);
				$lead = ($lead === '') ? '' : (int) $lead;
				echo '<p><input type="number" min="0" step="1" name="'.esc_attr(self::K_LEADTIME).'" value="'.esc_attr($lead).'" style="width:100px" /> '.esc_html__('days','gv').'</p>';
				echo '<p class="description">'.esc_html__('Default when product lead time is empty.','gv').'</p>';
			} catch (\Throwable $t) {
				self::log('LEAD render fail', ['err'=>$t->getMessage()]);
				echo '<p>'.esc_html__('Error rendering box (see debug.log).','gv').'</p>';
			}
		}, 'gv_supplier', 'normal', 'default');

		// 3) Address & Contact (+ website, notes)
		add_meta_box(self::MB_ADDRCONTACT_ID, __('Supplier Address & Contact','gv'), function($post){
			try {
				wp_nonce_field('gv_addr_save','gv_addr_nonce');
				$rows = array(
					array(self::K_ADDR1,   __('Address line 1','gv'), 'text'),
					array(self::K_ADDR2,   __('Address line 2','gv'), 'text'),
					array(self::K_CITY,    __('City','gv'),           'text'),
					array(self::K_POSTCODE,__('Postcode','gv'),       'text'),
					array(self::K_STATE,   __('State/Province','gv'),'text'),
					array(self::K_COUNTRY, __('Country','gv'),        'text'),
					array(self::K_CONTACT, __('Contact name','gv'),   'text'),
					array(self::K_EMAIL,   __('Email','gv'),          'email'),
					array(self::K_PO_EMAIL,__('PO email (orders)','gv'),'email'),
					array(self::K_PHONE,   __('Phone','gv'),          'text'),
					array(self::K_VAT,     __('VAT/Tax ID','gv'),     'text'),
					array(self::K_WEBSITE, __('Website','gv'),        'url'),
				);
				echo '<table class="form-table"><tbody>';
				foreach ($rows as $row) {
					$key  = $row[0]; $lbl = $row[1]; $type = $row[2];
					$val  = get_post_meta($post->ID, $key, true);
					echo '<tr><th><label for="'.$key.'">'.esc_html($lbl).'</label></th><td>';
					echo '<input type="'.$type.'" class="regular-text" id="'.$key.'" name="'.$key.'" value="'.esc_attr($val).'" />';
					echo '</td></tr>';
				}
				$notes = get_post_meta($post->ID, self::K_NOTES, true);
				echo '<tr><th><label for="'.self::K_NOTES.'">'.esc_html__('Notes','gv').'</label></th><td>';
				echo '<textarea class="large-text" rows="3" id="'.self::K_NOTES.'" name="'.self::K_NOTES.'">'.esc_textarea($notes).'</textarea>';
				echo '</td></tr>';
				echo '</tbody></table>';
			} catch (\Throwable $t) {
				self::log('ADDRESS render fail', ['err'=>$t->getMessage()]);
				echo '<p>'.esc_html__('Error rendering box (see debug.log).','gv').'</p>';
			}
		}, 'gv_supplier', 'normal', 'default');

		// 4) Commercials
		add_meta_box(self::MB_COMMERCIALS_ID, __('Supplier Commercials','gv'), function($post){
			try {
				wp_nonce_field('gv_comm_save','gv_comm_nonce');
				$margin = get_post_meta($post->ID, self::K_MARGIN, true);
				echo '<p><label for="'.self::K_MARGIN.'">'.esc_html__('Margin %','gv').'</label></p>';
				echo '<p><input type="number" min="0" step="0.01" style="width:120px" id="'.self::K_MARGIN.'" name="'.self::K_MARGIN.'" value="'.esc_attr($margin).'" /></p>';
			} catch (\Throwable $t) {
				self::log('COMM render fail', ['err'=>$t->getMessage()]);
				echo '<p>'.esc_html__('Error rendering box (see debug.log).','gv').'</p>';
			}
		}, 'gv_supplier', 'normal', 'default');
	}

	/* ========== Save handlers ========== */
	public function save_metabox_leadtime($post_id){
		try {
			if ( ! isset($_POST['gv_lead_nonce']) || ! wp_verify_nonce($_POST['gv_lead_nonce'],'gv_lead_save') ) return;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			if ( ! current_user_can('edit_post',$post_id) ) return;
			if ( array_key_exists(self::K_LEADTIME, $_POST) ) {
				$val = trim((string) wp_unslash($_POST[self::K_LEADTIME]));
				update_post_meta($post_id, self::K_LEADTIME, ($val === '') ? '' : max(0, (int) $val));
			}
		} catch (\Throwable $t) { self::log('lead save fail',['err'=>$t->getMessage(),'POST'=>$_POST]); $this->record_error_notice('Lead time save failed.'); }
	}

	public function save_metabox_address_contact($post_id){
		try {
			if ( ! isset($_POST['gv_addr_nonce']) || ! wp_verify_nonce($_POST['gv_addr_nonce'],'gv_addr_save') ) return;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			if ( ! current_user_can('edit_post',$post_id) ) return;

			$map = array(
				self::K_ADDR1=>'text', self::K_ADDR2=>'text', self::K_CITY=>'text', self::K_POSTCODE=>'text',
				self::K_STATE=>'text', self::K_COUNTRY=>'text', self::K_CONTACT=>'text', self::K_EMAIL=>'email',
				self::K_PO_EMAIL=>'email', self::K_PHONE=>'text', self::K_VAT=>'text', self::K_WEBSITE=>'url'
			);
			foreach ($map as $k=>$mode) {
				if ( ! array_key_exists($k, $_POST) ) continue;
				$raw = wp_unslash($_POST[$k]);
				if ( $mode==='email' )      $v = sanitize_email($raw);
				elseif ( $mode==='url' )    $v = esc_url_raw($raw);
				else                        $v = sanitize_text_field($raw);
				update_post_meta($post_id, $k, $v);
			}
			if ( array_key_exists(self::K_NOTES, $_POST) ) {
				update_post_meta($post_id, self::K_NOTES, wp_kses_post(wp_unslash($_POST[self::K_NOTES])) );
			}
		} catch (\Throwable $t) { self::log('addr save fail',['err'=>$t->getMessage(),'POST'=>$_POST]); $this->record_error_notice('Address/Contact save failed.'); }
	}

	public function save_metabox_commercials($post_id){
		try {
			if ( ! isset($_POST['gv_comm_nonce']) || ! wp_verify_nonce($_POST['gv_comm_nonce'],'gv_comm_save') ) return;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			if ( ! current_user_can('edit_post',$post_id) ) return;
			if ( array_key_exists(self::K_MARGIN, $_POST) ) {
				$raw = (string) wp_unslash($_POST[self::K_MARGIN]);
				update_post_meta($post_id, self::K_MARGIN, ($raw === '') ? '' : gvst_wc_format_decimal($raw) );
			}
		} catch (\Throwable $t) { self::log('comm save fail',['err'=>$t->getMessage(),'POST'=>$_POST]); $this->record_error_notice('Commercials save failed.'); }
	}

	public function save_metabox_enrichment($post_id){
		try {
			if ( ! isset($_POST['gv_enrich_nonce']) || ! wp_verify_nonce($_POST['gv_enrich_nonce'],'gv_enrich_save') ) return;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			if ( ! current_user_can('edit_post',$post_id) ) return;

			$defs = array(
				self::K_METHOD=>'text', self::K_SEARCH_TPL=>'text', self::K_PRODUCT_TPL=>'text',
				self::K_LINK_REGEX=>'raw', self::K_HEADERS_JSON=>'raw', self::K_AI_HINTS=>'textarea',
				self::K_AI_MODEL=>'text', self::K_AI_SIZE_KEYS=>'text', self::K_WEBHOOK_URL=>'url',
			);
			foreach ($defs as $k=>$mode) {
				if ( ! array_key_exists($k, $_POST) ) continue;
				$raw = wp_unslash($_POST[$k]);
				if      ( $mode==='url' )      $v = esc_url_raw($raw);
				elseif  ( $mode==='textarea' ) $v = sanitize_textarea_field($raw);
				elseif  ( $mode==='raw' )      $v = is_string($raw) ? trim($raw) : '';
				else                           $v = sanitize_text_field($raw);
				update_post_meta($post_id, $k, $v);
			}
		} catch (\Throwable $t) { self::log('enrich save fail',['err'=>$t->getMessage(),'POST'=>$_POST]); $this->record_error_notice('Enrichment save failed.'); }
	}

	/* ========== List columns ========== */
	public function cols($cols){
		$out = array();
		foreach ($cols as $k=>$v){
			$out[$k]=$v;
			if ($k==='title'){
				$out['gv_margin']=__('Margin %','gv');
				$out['gv_contact']=__('Contact','gv');
				$out['gv_email']=__('Email','gv');
				$out['gv_phone']=__('Phone','gv');
			}
		}
		return $out;
	}
	public function coldata($col,$post_id){
		switch ($col){
			case 'gv_margin':
				$val=get_post_meta($post_id,self::K_MARGIN,true);
				echo ($val!=='')?esc_html(number_format((float)$val,2)):'—';
				break;
			case 'gv_contact':
				echo esc_html(get_post_meta($post_id,self::K_CONTACT,true)?:'—'); break;
			case 'gv_email':
				$e=get_post_meta($post_id,self::K_EMAIL,true);
				echo $e?'<a href="mailto:'.esc_attr($e).'">'.esc_html($e).'</a>':'—'; break;
			case 'gv_phone':
				echo esc_html(get_post_meta($post_id,self::K_PHONE,true)?:'—'); break;
		}
	}

	/* ========== WooCommerce product tab ========== */
	private function supplier_options(){
		$opts = array(''=>__('Select a supplier','gv'));
		$posts = get_posts(array('post_type'=>'gv_supplier','post_status'=>'publish','numberposts'=>-1,'orderby'=>'title','order'=>'ASC'));
		foreach ($posts as $p) $opts[$p->ID]=$p->post_title;
		return $opts;
	}
	public function wc_tab($tabs){
		$tabs['gv_procurement']=array(
			'label'=>__('Procurement','gv'),
			'target'=>'gv_procurement_data',
			'class'=>array('show_if_simple','show_if_variable','show_if_grouped','show_if_external'),
			'priority'=>80,
		);
		return $tabs;
	}
	public function wc_panel(){
		try {
			$product_id = get_the_ID();
			$current = (int) get_post_meta($product_id, self::PRODUCT_SUPPLIER_META, true);

			echo '<div id="gv_procurement_data" class="panel woocommerce_options_panel"><div class="options_group">';
			woocommerce_wp_select(array(
				'id'=>self::PRODUCT_SUPPLIER_META,
				'label'=>__('Supplier','gv'),
				'desc_tip'=>true,
				'description'=>__('Select the supplier for this product (from Suppliers manager).','gv'),
				'options'=>$this->supplier_options(),
				'value'=>$current ? (string)$current : '',
			));

			if ($current){
				$margin=get_post_meta($current, self::K_MARGIN, true);
				$lead=get_post_meta($current, self::K_LEADTIME, true);
				echo '<p style="margin:4px 0 12px 0;"><small><em>';
				if ($margin!=='') echo esc_html__('Supplier margin:','gv').' '.esc_html(number_format((float)$margin,2)).'%. ';
				if ($lead!=='')   echo esc_html__('Lead time:','gv').' '.esc_html((int)$lead).' '.esc_html__('days','gv');
				echo '</em></small></p>';
			}

			woocommerce_wp_text_input(array(
				'id'=>self::PRODUCT_COST,'label'=>__('Cost Price','gv'),'type'=>'number',
				'custom_attributes'=>array('step'=>'0.01','min'=>'0'),
				'desc_tip'=>true,'description'=>__('Internal cost price (not shown to customers).','gv'),
			));
			woocommerce_wp_text_input(array(
				'id'=>self::PRODUCT_SUP_SKU,'label'=>__('Supplier SKU','gv'),
				'desc_tip'=>true,'description'=>__('SKU/code used by your supplier.','gv'),
			));
			woocommerce_wp_textarea_input(array(
				'id'=>self::PRODUCT_DESC,'label'=>__('Procurement Description','gv'),'rows'=>4,
				'desc_tip'=>true,'description'=>__('Internal notes for purchasing/procurement.','gv'),
			));
			echo '</div></div>';
		} catch (\Throwable $t) {
			self::log('wc_panel fail', ['err'=>$t->getMessage()]);
			echo '<div class="notice notice-error"><p>'.esc_html__('Error loading Procurement tab (see debug.log).','gv').'</p></div>';
		}
	}
	public function wc_save($product){
		try {
			if ( isset($_POST[self::PRODUCT_SUPPLIER_META]) ){
				$sid=(int)$_POST[self::PRODUCT_SUPPLIER_META];
				if ($sid>0 && get_post_type($sid)!=='gv_supplier') $sid=0;
				$product->update_meta_data(self::PRODUCT_SUPPLIER_META,$sid);
			}
			if ( isset($_POST[self::PRODUCT_COST]) ){
				$raw=wp_unslash($_POST[self::PRODUCT_COST]);
				$product->update_meta_data(self::PRODUCT_COST, ($raw==='')?'':gvst_wc_format_decimal($raw));
			}
			if ( isset($_POST[self::PRODUCT_SUP_SKU]) ){
				$product->update_meta_data(self::PRODUCT_SUP_SKU, sanitize_text_field(wp_unslash($_POST[self::PRODUCT_SUP_SKU])));
			}
			if ( isset($_POST[self::PRODUCT_DESC]) ){
				$product->update_meta_data(self::PRODUCT_DESC, wp_kses_post(wp_unslash($_POST[self::PRODUCT_DESC])));
			}
		} catch (\Throwable $t) {
			self::log('wc_save fail', ['err'=>$t->getMessage(),'POST'=>$_POST]);
			$this->record_error_notice('Product procurement save failed.');
		}
	}

	/* ========== Tools ▸ GV Supplier Debug ========== */
	public function add_tools_debug_page() {
		add_management_page(
			'GV Supplier Debug','GV Supplier Debug','manage_options','gvst-debug',
			function () {
				echo '<div class="wrap"><h1>GV Supplier Debug</h1>';
				echo '<form method="get"><input type="hidden" name="page" value="gvst-debug" />';
				echo '<p><label>Supplier ID: <input type="number" name="sid" value="'.esc_attr(isset($_GET['sid'])?$_GET['sid']:'').'" /></label> ';
				submit_button('Inspect', 'primary', '', false);
				echo '</p></form>';

				if ( isset($_GET['sid']) && ($sid=(int)$_GET['sid']) ) {
					if ( get_post_type($sid) !== 'gv_supplier' ) { echo '<p><strong>Not a gv_supplier post.</strong></p></div>'; return; }
					$keys = array(
						self::K_MARGIN,self::K_LEADTIME,
						self::K_ADDR1,self::K_ADDR2,self::K_CITY,self::K_POSTCODE,self::K_STATE,self::K_COUNTRY,
						self::K_CONTACT,self::K_EMAIL,self::K_PO_EMAIL,self::K_PHONE,self::K_VAT,self::K_WEBSITE,self::K_NOTES,
						self::K_METHOD,self::K_SEARCH_TPL,self::K_PRODUCT_TPL,self::K_LINK_REGEX,self::K_HEADERS_JSON,
						self::K_AI_MODEL,self::K_AI_HINTS,self::K_AI_SIZE_KEYS,self::K_WEBHOOK_URL,
					);
					echo '<table class="widefat striped"><thead><tr><th>Key</th><th>Value</th></tr></thead><tbody>';
					foreach ($keys as $k) {
						echo '<tr><td><code>'.esc_html($k).'</code></td><td>'.esc_html((string)get_post_meta($sid,$k,true)).'</td></tr>';
					}
					echo '</tbody></table>';
				}
				echo '</div>';
			}
		);
	}
}
new GV_Supplier_Tools();
