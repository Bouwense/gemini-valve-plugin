<?php

namespace cBuilder\Classes\pdfManager;

class CCBPdfManager {
	public static function ccb_get_pdf_manager_data() {
		$templates   = self::ccb_get_pdf_template();
		$current_key = self::get_template_key();

		if ( empty( $templates ) || 'default' === $current_key || empty( $templates[ $current_key ] ) ) {
			$static_templates     = CCBPdfManagerTemplates::ccb_get_templates();
			$ccb_pdf_tool_manager = $static_templates['default'];
		} else {
			$ccb_pdf_tool_manager = $templates[ $current_key ];
		}

		return CCBPdfManagerTemplates::ccb_get_template_skeleton( $ccb_pdf_tool_manager );
	}

	public static function update_template_key( $value ) {
		update_option( 'ccb_pdf_tool_manager_template_key', strval( $value ) );
	}

	public static function get_template_key() {
		return get_option( 'ccb_pdf_tool_manager_template_key', 'default' );
	}

	public static function ccb_add_or_update_pdf_tempalte( $key, $data ) {
		$templates         = self::ccb_get_pdf_template();
		$templates[ $key ] = $data;
		update_option( 'ccb_pdf_templates', $templates );
	}

	public static function update_tempaltes( $templates ) {
		update_option( 'ccb_pdf_templates', $templates );
	}

	public static function ccb_remove_pdf_template( $key ) {
		$templates = self::ccb_get_pdf_template();
		if ( isset( $templates[ $key ] ) ) {
			unset( $templates[ $key ] );
		}

		update_option( 'ccb_pdf_templates', $templates );
	}

	public static function ccb_get_pdf_template() {
		$templates = get_option( 'ccb_pdf_templates' );
		if ( empty( $templates ) ) {
			$templates = array();
		}

		return $templates;
	}
}
