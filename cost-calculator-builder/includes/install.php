<?php
function ccb_get_nonces() {
	return array(
		'ccb_payment'             => wp_create_nonce( 'ccb_payment' ),
		'ccb_contact_form'        => wp_create_nonce( 'ccb_contact_form' ),
		'ccb_woo_checkout'        => wp_create_nonce( 'ccb_woo_checkout' ),
		'ccb_add_order'           => wp_create_nonce( 'ccb_add_order' ),
		'ccb_orders'              => wp_create_nonce( 'ccb_orders' ),
		'ccb_update_order'        => wp_create_nonce( 'ccb_update_order' ),
		'ccb_send_invoice'        => wp_create_nonce( 'ccb_send_invoice' ),
		'ccb_get_invoice'         => wp_create_nonce( 'ccb_get_invoice' ),
		'ccb_wp_hook_nonce'       => wp_create_nonce( 'ccb_wp_hook_nonce' ),
		'ccb_razorpay_receive'    => wp_create_nonce( 'ccb_razorpay_receive' ),
		'ccb_woocommerce_payment' => wp_create_nonce( 'ccb_woocommerce_payment' ),
		'ccb_calc_views'          => wp_create_nonce( 'ccb_calc_views' ),
		'ccb_calc_interactions'   => wp_create_nonce( 'ccb_calc_interactions' ),
	);
}

function ccb_add_nonces() {
	$nonces = ccb_get_nonces();
	echo ( '<script type="text/javascript">window.ccb_nonces = ' . json_encode( $nonces ) . ';</script>' ); //phpcs:ignore
}

function ccb_add_admin_nonces() {
	$variables = array(
		'ccb_ajax_add_feedback'         => wp_create_nonce( 'ccb_ajax_add_feedback' ),
		'ccb_create_id'                 => wp_create_nonce( 'ccb_create_id' ),
		'ccb_edit_calc'                 => wp_create_nonce( 'ccb_edit_calc' ),
		'ccb_delete_calc'               => wp_create_nonce( 'ccb_delete_calc' ),
		'ccb_save_custom'               => wp_create_nonce( 'ccb_save_custom' ),
		'ccb_create_discount'           => wp_create_nonce( 'ccb_create_discount' ),
		'ccb_update_discount'           => wp_create_nonce( 'ccb_update_discount' ),
		'ccb_delete_discount'           => wp_create_nonce( 'ccb_delete_discount' ),
		'ccb_duplicate_discount'        => wp_create_nonce( 'ccb_duplicate_discount' ),
		'calc_skip_quick_tour'          => wp_create_nonce( 'calc_skip_quick_tour' ),
		'calc_skip_hint'                => wp_create_nonce( 'calc_skip_hint' ),
		'ccb_get_existing'              => wp_create_nonce( 'ccb_get_existing' ),
		'ccb_get_discounts'             => wp_create_nonce( 'ccb_get_discounts' ),
		'ccb_get_invoice'               => wp_create_nonce( 'ccb_get_invoice' ),
		'ccb_get_preview_discounts'     => wp_create_nonce( 'ccb_get_preview_discounts' ),
		'ccb_save_settings'             => wp_create_nonce( 'ccb_save_settings' ),
		'ccb_save_general_settings'     => wp_create_nonce( 'ccb_save_general_settings' ),
		'ccb_save_ai_api_key'           => wp_create_nonce( 'ccb_save_ai_api_key' ),
		'ccb_duplicate_calc'            => wp_create_nonce( 'ccb_duplicate_calc' ),
		'ccb_update_preset_title'       => wp_create_nonce( 'ccb_update_preset_title' ),
		'ccb_update_preset'             => wp_create_nonce( 'ccb_update_preset' ),
		'ccb_reset_type'                => wp_create_nonce( 'ccb_reset_type' ),
		'ccb_add_preset'                => wp_create_nonce( 'ccb_add_preset' ),
		'ccb_delete_preset'             => wp_create_nonce( 'ccb_delete_preset' ),
		'ccb_update_banner'             => wp_create_nonce( 'ccb_update_banner' ),
		'ccb_preset_hide_notice'        => wp_create_nonce( 'ccb_preset_hide_notice' ),
		'ccb_demo_import_apply'         => wp_create_nonce( 'ccb_demo_import_apply' ),
		'ccb_demo_import_run'           => wp_create_nonce( 'ccb_demo_import_run' ),
		'ccb_run_calc_updates'          => wp_create_nonce( 'ccb_run_calc_updates' ),
		'ccb_custom_import'             => wp_create_nonce( 'ccb_custom_import' ),
		'ccb_orders'                    => wp_create_nonce( 'ccb_orders' ),
		'ccb_update_order'              => wp_create_nonce( 'ccb_update_order' ),
		'ccb_delete_order'              => wp_create_nonce( 'ccb_delete_order' ),
		'ccb_complete_order'            => wp_create_nonce( 'ccb_complete_order' ),
		'ccb_send_quote'                => wp_create_nonce( 'ccb_send_quote' ),
		'ccb_get_templates'             => wp_create_nonce( 'ccb_get_templates' ),
		'ccb_delete_template'           => wp_create_nonce( 'ccb_delete_template' ),
		'ccb_use_template'              => wp_create_nonce( 'ccb_use_template' ),
		'ccb_save_as_template'          => wp_create_nonce( 'ccb_save_as_template' ),
		'ccb_add_category'              => wp_create_nonce( 'ccb_add_category' ),
		'ccb_delete_category'           => wp_create_nonce( 'ccb_delete_category' ),
		'ccb_update_category'           => wp_create_nonce( 'ccb_update_category' ),
		'ccb_get_categories'            => wp_create_nonce( 'ccb_get_categories' ),
		'ccb_save_config'               => wp_create_nonce( 'ccb_save_config' ),
		'ccb_toggle_favorite'           => wp_create_nonce( 'ccb_toggle_favorite' ),
		'embed_create_page'             => wp_create_nonce( 'embed_create_page' ),
		'embed_get_pages'               => wp_create_nonce( 'embed_get_pages' ),
		'embed_insert_pages'            => wp_create_nonce( 'embed_insert_pages' ),
		'ccb_get_code'                  => wp_create_nonce( 'ccb_get_code' ),
		'ccb_send_code'                 => wp_create_nonce( 'ccb_send_code' ),
		'ccb_save_invoice_logo'         => wp_create_nonce( 'ccb_save_invoice_logo' ),
		'ccb_save_email_logo'           => wp_create_nonce( 'ccb_save_email_logo' ),
		'ccb_export_nonce'              => wp_create_nonce( 'ccb_export_nonce' ),
		'ccb_webhook_nonce'             => wp_create_nonce( 'ccb_webhook_nonce' ),
		'ccb_wp_hook_nonce'             => wp_create_nonce( 'ccb_wp_hook_nonce' ),
		'ccb_rollback_nonce'            => wp_create_nonce( 'ccb_rollback_nonce' ),
		'ccb_delete_payment'            => wp_create_nonce( 'ccb_delete_payment' ),
		'ccb_save_pickup_icon'          => wp_create_nonce( 'ccb_save_pickup_icon' ),
		'ccb_save_marker_icon'          => wp_create_nonce( 'ccb_save_marker_icon' ),
		'ccb_generate_formula'          => wp_create_nonce( 'ccb_generate_formula' ),
		'ccb_get_forms'                 => wp_create_nonce( 'ccb_get_forms' ),
		'ccb_get_active_form_fields'    => wp_create_nonce( 'ccb_get_active_form_fields' ),
		'ccb_create_default_form'       => wp_create_nonce( 'ccb_create_default_form' ),
		'ccb_delete_form'               => wp_create_nonce( 'ccb_delete_form' ),
		'ccb_apply_form_id'             => wp_create_nonce( 'ccb_apply_form_id' ),
		'ccb_update_form'               => wp_create_nonce( 'ccb_update_form' ),
		'ccb_duplicate_form'            => wp_create_nonce( 'ccb_duplicate_form' ),
		'ccb_edit_pdf_template'         => wp_create_nonce( 'ccb_edit_pdf_template' ),
		'ccb_delete_pdf_template'       => wp_create_nonce( 'ccb_delete_pdf_template' ),
		'ccb_restore_pdf_template'      => wp_create_nonce( 'ccb_restore_pdf_template' ),
		'ccb_extend_pdf_template'       => wp_create_nonce( 'ccb_extend_pdf_template' ),
		'ccb_get_pdf_data'              => wp_create_nonce( 'ccb_get_pdf_data' ),
		'ccb_get_template_cf7'          => wp_create_nonce( 'ccb_get_template_cf7' ),
		'calc_load_more_products'       => wp_create_nonce( 'calc_load_more_products' ),
		'calc_load_pages'               => wp_create_nonce( 'calc_load_pages' ),
		'ccb_get_analytics_data'        => wp_create_nonce( 'ccb_get_analytics_data' ),
		'ccb_analytics_get_calculators' => wp_create_nonce( 'ccb_analytics_get_calculators' ),
		'ccb_single_calc_analytics'     => wp_create_nonce( 'ccb_single_calc_analytics' ),
		'ccb_analytics_page_settings'   => wp_create_nonce( 'ccb_analytics_page_settings' ),
		'ccb_analytics_add_widget'      => wp_create_nonce( 'ccb_analytics_add_widget' ),
	);

	echo ( '<script type="text/javascript">window.ccb_nonces = ' . json_encode( $variables ) . ';</script>' ); //phpcs:ignore
	echo ( '<script type="text/javascript">window.ccb_frontend_nonces = ' . json_encode( ccb_get_nonces() ) . ';</script>' ); //phpcs:ignore
}

add_action( 'wp_head', 'ccb_add_nonces' );
add_action( 'admin_head', 'ccb_add_admin_nonces' );

function ccb_ajax_add_feedback() {
	check_ajax_referer( 'ccb_ajax_add_feedback', 'security' );
	update_option( 'ccb_feedback_added', true );
}

add_action( 'wp_ajax_ccb_ajax_add_feedback', 'ccb_ajax_add_feedback' );
