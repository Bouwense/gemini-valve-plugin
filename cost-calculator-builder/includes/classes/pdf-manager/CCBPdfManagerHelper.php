<?php

namespace cBuilder\Classes\pdfManager;

class CCBPdfManagerHelper {
	public static function get_pdf_tab_options_data( $label = '', $key = 'text_align', $value = 'left' ) {
		$options = array();

		if ( 'text_align' === $key ) {
			$options = self::get_text_align_options();
		}

		if ( 'layout' === $key ) {
			$options = self::get_layout_options();
		}

		if ( 'border_type' === $key ) {
			$options = self::get_border_type_options();
		}

		if ( 'line_type' === $key ) {
			$options = self::get_border_type_options();
		}

		return array(
			'label'   => $label,
			'key'     => $key,
			'value'   => $value,
			'type'    => 'ccb-pdf-tab-options',
			'options' => $options,
		);
	}

	public static function get_pdf_switch_with_text_data( $label, $key, $status, $value ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'type'  => 'ccb-pdf-switch-with-text',
			'value' => $value,
			'data'  => array(
				'status' => $status,
			),
		);
	}

	public static function get_pdf_hr_data() {
		return array(
			'label' => '',
			'key'   => 'separator',
			'value' => '',
			'type'  => 'ccb-pdf-hr',
		);
	}

	public static function get_pdf_link_data( $value = '' ) {
		return array(
			'title'       => '',
			'placeholder' => __( 'Paste Link', 'cost-calculator-builder-pro' ),
			'key'         => 'qr_code_link',
			'value'       => $value,
			'type'        => 'ccb-pdf-input-link',
		);
	}

	public static function get_pdf_contact_info_data( $status, $label, $value, $key, $placeholder = '' ) {
		return array(
			'title'       => __( 'Show', 'cost-calculator-builder-pro' ),
			'value'       => $value,
			'key'         => $key,
			'placeholder' => $placeholder,
			'type'        => 'ccb-pdf-contact-info',
			'data'        => array(
				'status' => $status,
				'label'  => $label,
			),
		);
	}

	public static function get_pdf_several_options_data( $label, $key, $date_format = 'dd.mm.yyyy', $time_format = 'hh:mm' ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'type'  => 'ccb-pdf-several-options',
			'data'  => array(
				'date_format' => array(
					'key'     => 'date_format',
					'value'   => $date_format,
					'options' => array(
						array(
							'key'   => 'dd.mm.yyyy',
							'label' => 'DD.MM.YYYY',
						),
						array(
							'key'   => 'mm.dd.yyyy',
							'label' => 'MM.DD.YYYY',
						),
					),
				),
				'time_format' => array(
					'key'     => 'time_format',
					'value'   => $time_format,
					'options' => array(
						array(
							'key'   => 'hh:mm',
							'label' => '13:00',
						),
						array(
							'key'   => 'hh:mm:ss',
							'label' => '13:00:00',
						),
					),
				),
			),
		);
	}

	public static function get_pdf_counter_data( $label, $key, $value, $extra = 'px' ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'value' => $value,
			'type'  => 'ccb-pdf-counter',
			'extra' => $extra,
		);
	}

	public static function get_pdf_dropDown_data( $label, $key, $value, $options ) {
		return array(
			'label'   => $label,
			'key'     => $key,
			'value'   => $value,
			'type'    => 'ccb-pdf-drop-down',
			'options' => $options,
		);
	}

	public static function get_pdf_color_data( $label, $key, $value ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'value' => $value,
			'type'  => 'ccb-pdf-color',
		);
	}

	public static function get_pdf_upload_image_data( $label, $key, $value = '' ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'value' => $value,
			'type'  => 'ccb-pdf-upload-image',
		);
	}

	public static function get_pdf_switch_with_color_data( $label, $key, $value = '', $status = true ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'value' => $value,
			'type'  => 'ccb-pdf-switch-with-color',
			'data'  => array(
				'status' => $status,
			),
		);
	}

	public static function get_pdf_switch_data( $label, $key, $value = true ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'value' => $value,
			'type'  => 'ccb-pdf-switch',
		);
	}

	public static function get_pdf_input_text_data( $label, $key, $value = '' ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'type'  => 'ccb-pdf-input-text',
			'value' => $value,
		);
	}

	public static function get_pdf_input_text_with_label_data( $label, $key, $value = '' ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'type'  => 'ccb-pdf-input-text-with-label',
			'value' => $value,
		);
	}

	public static function get_pdf_text_area_data( $label, $key, $value = '' ) {
		return array(
			'label' => $label,
			'key'   => $key,
			'type'  => 'ccb-pdf-input-text-area',
			'value' => $value,
		);
	}

	public static function get_layout_options() {
		return array(
			'left_sidebar'  => array(
				'key'  => 'left_sidebar',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Sidebar-Left',
			),
			'no_sidebar'    => array(
				'key'  => 'no_sidebar',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Sidebar-1',
			),
			'right_sidebar' => array(
				'key'  => 'right_sidebar',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Sidebar-Right',
			),
		);
	}

	public static function get_text_align_options() {
		return array(
			'left'      => array(
				'key'  => 'left',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Align-Left',
			),
			'center'    => array(
				'key'  => 'right',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Align-Center',
			),
			'right'     => array(
				'key'  => 'center',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Align-Right',
			),
			'justified' => array(
				'key'  => 'justified',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-New-Align-Justified',
			),
		);
	}

	public static function get_border_type_options() {
		return array(
			'solid'  => array(
				'key'  => 'solid',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Border-All',
			),
			'left'   => array(
				'key'  => 'left',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Border-Left',
			),
			'right'  => array(
				'key'  => 'right',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Border-Right',
			),
			'top'    => array(
				'key'  => 'top',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Border-Top',
			),
			'bottom' => array(
				'key'  => 'bottom',
				'icon' => 'ccb-icon-Cost-Calculator-Admin-Bottom',
			),
		);
	}

	public static function get_border_style_options() {
		return array(
			'dashed' => array(
				'label' => 'Dashed',
				'value' => 'dashed',
			),
			'dotted' => array(
				'label' => 'Dotted',
				'value' => 'dotted',
			),
			'solid'  => array(
				'label' => 'Solid',
				'value' => 'solid',
			),
		);
	}
}
