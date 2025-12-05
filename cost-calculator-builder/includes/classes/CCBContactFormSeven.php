<?php

namespace cBuilder\Classes;

class CCBContactFormSeven {
	public static function get_template_preview_cf7() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		check_ajax_referer( 'ccb_get_template_cf7', 'nonce' );

		$sanitized_data = apply_filters( 'stm_ccb_sanitize_array', $_POST );
		$content_data   = ! empty( $sanitized_data['content'] ) ? sanitize_text_field( $sanitized_data['content'] ) : null;

		if ( empty( $content_data ) ) {
			wp_send_json_error( __( 'No content provided', 'cost-calculator-builder' ) );
		}

		$decoded_content = json_decode( stripslashes( $content_data ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( __( 'Invalid JSON format', 'cost-calculator-builder' ) );
		}

		if ( empty( $decoded_content['cf7_id'] ) || ! is_numeric( $decoded_content['cf7_id'] ) ) {
			wp_send_json_error( __( 'Invalid or missing contact form ID', 'cost-calculator-builder' ) );
		}

		$contact_form_id = intval( $decoded_content['cf7_id'] );

		$shortcode_content = do_shortcode( '[contact-form-7 id="' . $contact_form_id . '"]' );

		if ( empty( $shortcode_content ) ) {
			wp_send_json_error( __( 'Failed to load contact form content', 'cost-calculator-builder' ) );
		}

		$content = array(
			'cf_7'           => $contact_form_id,
			'cf_7_shortcode' => $shortcode_content,
		);

		wp_send_json(
			array(
				'status' => 200,
				'content' => $content,
			)
		);
	}
}
