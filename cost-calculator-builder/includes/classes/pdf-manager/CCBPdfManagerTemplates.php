<?php

namespace cBuilder\Classes\pdfManager;

class CCBPdfManagerTemplates {
	public static function ccb_get_templates_list( $with_key = false ) {
		$templates = array();
		foreach ( self::ccb_get_templates() as $template ) {
			if ( $with_key ) {
				$templates[ $template['key'] ] = array(
					'name'      => $template['name'] ?? 'Default',
					'key'       => $template['key'],
					'image'     => $template['image'],
					'is_custom' => false,
				);
			} else {
				$templates[] = array(
					'name'      => $template['name'] ?? 'Default',
					'key'       => $template['key'],
					'image'     => $template['image'],
					'is_custom' => false,
				);
			}
		}

		foreach ( self::get_pdf_templates_from_db() as $template ) {
			if ( $with_key ) {
				$templates[ $template['key'] ] = array(
					'name'      => $template['name'] ?? 'Default',
					'key'       => $template['key'],
					'image'     => $template['image'],
					'is_custom' => true,
				);
			} else {
				$templates[] = array(
					'name'      => $template['name'] ?? 'Default',
					'key'       => $template['key'],
					'image'     => $template['image'],
					'is_custom' => true,
				);
			}
		}

		return $templates;
	}

	public static function ccb_get_template_skeleton( $store ) {
		$top_text_block        = $store['sections']['top_text_block'];
		$order_id_and_date     = $store['sections']['order_id_and_date'];
		$order_block           = $store['sections']['order_block'];
		$footer_text           = $store['sections']['footer_text'];
		$brand_block           = $store['sections']['brand_block'];
		$image_block           = $store['sections']['image_block'];
		$company_block         = $store['sections']['company_block'];
		$customer_block        = $store['sections']['customer_block'];
		$additional_text_block = $store['sections']['additional_text_block'];

		return array(
			'key'      => $store['key'] ?? 'default',
			'image'    => $store['image'] ?? '',
			'name'     => $store['name'] ?? 'Default',
			'document' => array(
				'title' => __( 'Document', 'cost-calculator-builder-pro' ),
				'key'   => 'document',
				'tabs'  => array(
					'body'    => array(
						'title' => __( 'Body', 'cost-calculator-builder-pro' ),
						'key'   => 'body',
						'data'  => array(
							'text_color'       => CCBPdfManagerHelper::get_pdf_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $store['document']['body']['text_color'] ),
							'background_color' => CCBPdfManagerHelper::get_pdf_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $store['document']['body']['background_color'] ),
							'background_image' => CCBPdfManagerHelper::get_pdf_upload_image_data( __( 'Background Image', 'cost-calculator-builder-pro' ), 'background_image', $store['document']['body']['background_image'] ),
							'side_paddings'    => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Side Paddings', 'cost-calculator-builder-pro' ), 'side_paddings', $store['document']['body']['side_paddings'] ),
						),
					),
					'sidebar' => array(
						'title' => __( 'Sidebar', 'cost-calculator-builder-pro' ),
						'key'   => 'sidebar',
						'data'  => array(
							'layout'           => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'layout', $store['document']['sidebar']['layout'] ),
							'text_color'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $store['document']['sidebar']['text_color'], $store['document']['sidebar']['text_color_status'] ),
							'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $store['document']['sidebar']['background_color'], $store['document']['sidebar']['background_color_status'] ),
						),
					),
					'border'  => array(
						'title' => __( 'Border', 'cost-calculator-builder-pro' ),
						'key'   => 'border',
						'data'  => array(
							'show_border'  => CCBPdfManagerHelper::get_pdf_switch_data( __( 'Show Border', 'cost-calculator-builder-pro' ), 'show_border', $store['document']['border']['show_border'] ),
							'border_type'  => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'border_type', $store['document']['border']['border_type'] ),
							'border_color' => CCBPdfManagerHelper::get_pdf_color_data( __( 'Border Color', 'cost-calculator-builder-pro' ), 'border_color', $store['document']['border']['border_color'] ),
							'border_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Border Size', 'cost-calculator-builder-pro' ), 'border_size', $store['document']['border']['border_size'] ),
							'border_style' => CCBPdfManagerHelper::get_pdf_dropDown_data( __( 'Border Style', 'cost-calculator-builder-pro' ), 'border_style', $store['document']['border']['border_style'], CCBPdfManagerHelper::get_border_style_options() ),
						),
					),
				),
			),
			'sections' => array(
				'top_text_block'        => array(
					'enable'  => $top_text_block['enable'],
					'sort_id' => $top_text_block['sort_id'],
					'is_body' => $top_text_block['is_body'],
					'title'   => 'Top text block',
					'key'     => 'top_text_block',
					'tabs'    => array(
						'text'   => array(
							'title' => __( 'Text', 'cost-calculator-builder-pro' ),
							'key'   => 'text',
							'data'  => array(
								'title'       => CCBPdfManagerHelper::get_pdf_input_text_data( '', 'title', $top_text_block['text']['title'] ),
								'description' => CCBPdfManagerHelper::get_pdf_text_area_data( '', 'description', $top_text_block['text']['description'] ),
							),
						),
						'design' => array(
							'title' => 'Design',
							'key'   => 'design',
							'data'  => array(
								'text_align'       => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'text_align', $top_text_block['design']['text_align'] ),
								'title_font_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Heading Font Size', 'cost-calculator-builder-pro' ), 'title_font_size', $top_text_block['design']['title_font_size'] ),
								'title_color'      => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Heading Color', 'cost-calculator-builder-pro' ), 'title_color', $top_text_block['design']['title_color'], $top_text_block['design']['title_color_status'] ),
								'font_size'        => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Font Size', 'cost-calculator-builder-pro' ), 'font_size', $top_text_block['design']['font_size'] ),
								'text_color'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $top_text_block['design']['text_color'], $top_text_block['design']['text_color_status'] ),
								'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $top_text_block['design']['background_color'], $top_text_block['design']['background_color_status'] ),
							),
						),
					),
				),
				'order_id_and_date'     => array(
					'enable'  => $order_id_and_date['enable'],
					'sort_id' => $order_id_and_date['sort_id'],
					'is_body' => $order_id_and_date['is_body'],
					'title'   => __( 'Order ID and Date', 'cost-calculator-builder-pro' ),
					'key'     => 'order_id_and_date',
					'tabs'    => array(
						'id'     => array(
							'title' => __( 'ID', 'cost-calculator-builder-pro' ),
							'key'   => 'id',
							'data'  => array(
								'show_order_id' => CCBPdfManagerHelper::get_pdf_switch_with_text_data( __( 'Show', 'cost-calculator-builder-pro' ), 'show_order_id', $order_id_and_date['id']['show_order_id_status'], $order_id_and_date['id']['show_order_id'] ),
							),
						),
						'date'   => array(
							'title' => 'Date',
							'key'   => 'date',
							'data'  => array(
								'show_date' => CCBPdfManagerHelper::get_pdf_switch_with_text_data( __( 'Show', 'cost-calculator-builder-pro' ), 'show_date', $order_id_and_date['date']['show_date_status'], $order_id_and_date['date']['show_date'] ),
								'format'    => CCBPdfManagerHelper::get_pdf_several_options_data( __( 'Format', 'cost-calculator-builder-pro' ), 'format', $order_id_and_date['date']['date_format'], $order_id_and_date['date']['time_format'] ),
							),
						),
						'design' => array(
							'title' => __( 'Design', 'cost-calculator-builder-pro' ),
							'key'   => 'design',
							'data'  => array(
								'text_align'       => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'text_align', $order_id_and_date['design']['text_align'] ),
								'font_size'        => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Font Size', 'cost-calculator-builder-pro' ), 'font_size', $order_id_and_date['design']['font_size'] ),
								'text_color'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $order_id_and_date['design']['text_color'], $order_id_and_date['design']['text_color_status'] ),
								'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $order_id_and_date['design']['background_color'], $order_id_and_date['design']['background_color_status'] ),
							),
						),
					),
				),
				'order_block'           => array(
					'enable'  => $order_block['enable'],
					'sort_id' => $order_block['sort_id'],
					'is_body' => $order_block['is_body'],
					'key'     => 'order_block',
					'title'   => 'Order Block',
					'tabs'    => array(
						'content' => array(
							'title' => 'Content',
							'key'   => 'content',
							'data'  => array(
								'show_payment_method' => CCBPdfManagerHelper::get_pdf_switch_data( __( 'Show Payment Method', 'cost-calculator-builder-pro' ), 'show_payment_method', $order_block['content']['show_payment_method'] ),
								'show_grand_total'    => CCBPdfManagerHelper::get_pdf_switch_data( __( 'Show Grand Total', 'cost-calculator-builder-pro' ), 'show_grand_total', $order_block['content']['show_grand_total'] ),
								'separator'           => CCBPdfManagerHelper::get_pdf_hr_data(),
								'show_heading'        => CCBPdfManagerHelper::get_pdf_switch_data( __( 'Show Heading', 'cost-calculator-builder-pro' ), 'show_heading', $order_block['content']['show_heading'] ),
								'heading_name'        => CCBPdfManagerHelper::get_pdf_input_text_data( '', 'heading_name', $order_block['content']['heading_name'] ),
								'heading_unit'        => CCBPdfManagerHelper::get_pdf_input_text_data( '', 'heading_unit', $order_block['content']['heading_unit'] ),
								'heading_value'       => CCBPdfManagerHelper::get_pdf_input_text_data( '', 'heading_value', $order_block['content']['heading_value'] ),
								'separator_second'    => CCBPdfManagerHelper::get_pdf_hr_data(),
								'show_qr_code'        => CCBPdfManagerHelper::get_pdf_switch_data( __( 'Show QR Code', 'cost-calculator-builder-pro' ), 'show_qr_code', $order_block['content']['show_qr_code'] ),
								'qr_code_link'        => CCBPdfManagerHelper::get_pdf_link_data( $order_block['content']['qr_code_link'] ),
							),
						),
						'images'  => array(
							'title' => 'Images',
							'key'   => 'images',
							'data'  => array(
								'stamp_image'     => CCBPdfManagerHelper::get_pdf_upload_image_data( __( 'Stamp Image', 'cost-calculator-builder-pro' ), 'stamp_image', $order_block['images']['stamp_image'] ),
								'stamp_size'      => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Stamp Size', 'cost-calculator-builder-pro' ), 'stamp_size', $order_block['images']['stamp_size'], '%' ),
								'separator'       => CCBPdfManagerHelper::get_pdf_hr_data(),
								'signature_image' => CCBPdfManagerHelper::get_pdf_upload_image_data( __( 'Signature Image', 'cost-calculator-builder-pro' ), 'signature_image', $order_block['images']['signature_image'] ),
								'signature_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Signature Size', 'cost-calculator-builder-pro' ), 'signature_size', $order_block['images']['signature_size'], '%' ),
							),
						),
						'lines'   => array(
							'title' => 'Lines',
							'key'   => 'lines',
							'data'  => array(
								'show_lines'         => CCBPdfManagerHelper::get_pdf_switch_data( __( 'Show Lines', 'cost-calculator-builder-pro' ), 'show_lines', $order_block['lines']['show_lines'] ),
								'line_border_color'  => CCBPdfManagerHelper::get_pdf_color_data( __( 'Line Color', 'cost-calculator-builder-pro' ), 'line_border_color', $order_block['lines']['line_border_color'] ),
								'line_border_size'   => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Line Size', 'cost-calculator-builder-pro' ), 'line_border_size', $order_block['lines']['line_border_size'] ),
								'line_border_style'  => CCBPdfManagerHelper::get_pdf_dropDown_data( __( 'Line Style', 'cost-calculator-builder-pro' ), 'line_border_style', $order_block['lines']['line_border_style'], CCBPdfManagerHelper::get_border_style_options() ),
								'separator'          => CCBPdfManagerHelper::get_pdf_hr_data(),
								'show_border'        => CCBPdfManagerHelper::get_pdf_switch_data( __( 'Show Border', 'cost-calculator-builder-pro' ), 'show_border', $order_block['lines']['show_border'] ),
								'border_color'       => CCBPdfManagerHelper::get_pdf_color_data( __( 'Border Color', 'cost-calculator-builder-pro' ), 'border_color', $order_block['lines']['border_color'] ),
								'border_style'       => CCBPdfManagerHelper::get_pdf_dropDown_data( __( 'Border Style', 'cost-calculator-builder-pro' ), 'border_style', $order_block['lines']['border_style'], CCBPdfManagerHelper::get_border_style_options() ),
								'border_border_size' => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Border Size', 'cost-calculator-builder-pro' ), 'border_border_size', $order_block['lines']['border_border_size'] ),
								'angle_border_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Angle Size', 'cost-calculator-builder-pro' ), 'angle_border_size', $order_block['lines']['angle_border_size'] ),
							),
						),
						'design'  => array(
							'title' => 'Design',
							'key'   => 'design',
							'data'  => array(
								'title_font_size'    => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Heading Font Size', 'cost-calculator-builder-pro' ), 'title_font_size', $order_block['design']['title_font_size'] ),
								'heading_text'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Heading Text', 'cost-calculator-builder-pro' ), 'heading_text', $order_block['design']['heading_text'], $order_block['design']['heading_text_status'] ),
								'heading_background' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Heading Background', 'cost-calculator-builder-pro' ), 'heading_background', $order_block['design']['heading_background'], $order_block['design']['heading_background_status'] ),
								'font_size'          => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Table Font Size', 'cost-calculator-builder-pro' ), 'font_size', $order_block['design']['font_size'] ),
								'table_text'         => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Table Text', 'cost-calculator-builder-pro' ), 'table_text', $order_block['design']['table_text'], $order_block['design']['table_text_status'] ),
								'table_background'   => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Table Background', 'cost-calculator-builder-pro' ), 'table_background', $order_block['design']['table_background'], $order_block['design']['table_background_status'] ),
							),
						),
					),
				),
				'footer_text'           => array(
					'enable'  => $footer_text['enable'],
					'sort_id' => $footer_text['sort_id'],
					'is_body' => $footer_text['is_body'],
					'key'     => 'footer_text',
					'title'   => 'Footer Text',
					'tabs'    => array(
						'text'   => array(
							'title' => __( 'Text', 'cost-calculator-builder-pro' ),
							'key'   => 'text',
							'data'  => array(
								'description' => CCBPdfManagerHelper::get_pdf_text_area_data( '', 'description', $footer_text['text']['description'] ),
							),
						),
						'design' => array(
							'title' => __( 'Design', 'cost-calculator-builder-pro' ),
							'key'   => 'design',
							'data'  => array(
								'text_align'       => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'text_align', $footer_text['design']['text_align'] ),
								'font_size'        => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Font Size', 'cost-calculator-builder-pro' ), 'font_size', $footer_text['design']['font_size'] ),
								'text_color'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $footer_text['design']['text_color'], $footer_text['design']['text_color_status'] ),
								'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $footer_text['design']['background_color'], $footer_text['design']['background_color_status'] ),
							),
						),
					),
				),
				'brand_block'           => array(
					'enable'  => $brand_block['enable'],
					'sort_id' => $brand_block['sort_id'],
					'is_body' => $brand_block['is_body'],
					'title'   => __( 'Brand Block', 'cost-calculator-builder-pro' ),
					'key'     => 'brand_block',
					'tabs'    => array(
						'logo'   => array(
							'title' => __( 'Logo', 'cost-calculator-builder-pro' ),
							'key'   => 'logo',
							'data'  => array(
								'logo_image'       => CCBPdfManagerHelper::get_pdf_upload_image_data( __( 'Logo Image', 'cost-calculator-builder-pro' ), 'logo_image', $brand_block['logo']['logo_image'] ),
								'logo_width_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Logo Width', 'cost-calculator-builder-pro' ), 'logo_width_size', $brand_block['logo']['logo_width_size'] ),
								'logo_height_size' => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Logo Height', 'cost-calculator-builder-pro' ), 'logo_height_size', $brand_block['logo']['logo_height_size'] ),
							),
						),
						'name'   => array(
							'title' => __( 'Name', 'cost-calculator-builder-pro' ),
							'key'   => 'name',
							'data'  => array(
								'show_company_name' => CCBPdfManagerHelper::get_pdf_switch_with_text_data( '', 'show_company_name', $brand_block['name']['show_company_name_status'], $brand_block['name']['show_company_name'] ),
								'font_size'         => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Font Size', 'cost-calculator-builder-pro' ), 'font_size', $brand_block['name']['font_size'] ),
								'font_color'        => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Font Color', 'cost-calculator-builder-pro' ), 'font_color', $brand_block['name']['font_color'], $brand_block['name']['font_color_status'] ),
							),
						),
						'slogan' => array(
							'title' => __( 'Slogan', 'cost-calculator-builder-pro' ),
							'key'   => 'slogan',
							'data'  => array(
								'show_slogan' => CCBPdfManagerHelper::get_pdf_switch_with_text_data( '', 'show_slogan', $brand_block['slogan']['show_slogan_status'], $brand_block['slogan']['show_slogan'] ),
								'font_size'   => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Font Size', 'cost-calculator-builder-pro' ), 'font_size', $brand_block['slogan']['font_size'] ),
								'font_color'  => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Font Color', 'cost-calculator-builder-pro' ), 'font_color', $brand_block['slogan']['font_color'], $brand_block['slogan']['font_color_status'] ),
							),
						),
						'design' => array(
							'title' => __( 'Design', 'cost-calculator-builder-pro' ),
							'key'   => 'design',
							'data'  => array(
								'text_align'       => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'text_align', $brand_block['design']['text_align'] ),
								'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $brand_block['design']['background_color'], $brand_block['design']['background_color_status'] ),
							),
						),
					),
				),
				'image_block'           => array(
					'enable'  => $image_block['enable'],
					'sort_id' => $image_block['sort_id'],
					'is_body' => $image_block['is_body'],
					'title'   => __( 'Image Block', 'cost-calculator-builder-pro' ),
					'key'     => 'image_block',
					'tabs'    => array(
						'default' => array(
							'title' => '',
							'key'   => 'default',
							'data'  => array(
								'background_image' => CCBPdfManagerHelper::get_pdf_upload_image_data( __( 'Image', 'cost-calculator-builder-pro' ), 'background_image', $image_block['default']['background_image'] ),
								'image_height'     => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Image Height', 'cost-calculator-builder-pro' ), 'image_height', $image_block['default']['image_height'] ),
							),
						),
					),
				),
				'company_block'         => array(
					'enable'  => $company_block['enable'],
					'sort_id' => $company_block['sort_id'],
					'is_body' => $company_block['is_body'],
					'title'   => __( 'Company Block', 'cost-calculator-builder-pro' ),
					'key'     => 'company_block',
					'tabs'    => array(
						'text'     => array(
							'title' => __( 'Text', 'cost-calculator-builder-pro' ),
							'key'   => 'text',
							'data'  => array(
								'title'       => CCBPdfManagerHelper::get_pdf_input_text_with_label_data( 'Block Title', 'title', $company_block['text']['title'] ),
								'description' => CCBPdfManagerHelper::get_pdf_text_area_data( '', 'description', $company_block['text']['description'] ),
							),
						),
						'contacts' => array(
							'title' => __( 'Contacts', 'cost-calculator-builder-pro' ),
							'key'   => 'contacts',
							'data'  => array(
								'phone'       => CCBPdfManagerHelper::get_pdf_contact_info_data( $company_block['contacts']['phone_status'], $company_block['contacts']['phone_label'], $company_block['contacts']['phone'], 'phone' ),
								'email'       => CCBPdfManagerHelper::get_pdf_contact_info_data( $company_block['contacts']['email_status'], $company_block['contacts']['email_label'], $company_block['contacts']['email'], 'email', 'john@doe.com' ),
								'messenger'   => CCBPdfManagerHelper::get_pdf_contact_info_data( $company_block['contacts']['messenger_status'], $company_block['contacts']['messenger_label'], $company_block['contacts']['messenger'], 'messenger', '@name' ),
								'site_url'    => CCBPdfManagerHelper::get_pdf_contact_info_data( $company_block['contacts']['site_url_status'], $company_block['contacts']['site_url_label'], $company_block['contacts']['site_url'], 'site_url', 'site.com' ),
								'address'     => CCBPdfManagerHelper::get_pdf_switch_with_text_data( __( 'Show', 'cost-calculator-builder-pro' ), 'address', $company_block['contacts']['address_status'], $company_block['contacts']['address'] ),
								'description' => CCBPdfManagerHelper::get_pdf_text_area_data( '', 'description', $company_block['contacts']['description'] ),
							),
						),
						'design'   => array(
							'title' => __( 'Design', 'cost-calculator-builder-pro' ),
							'key'   => 'design',
							'data'  => array(
								'text_align'       => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'text_align', $company_block['design']['text_align'] ),
								'title_font_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Heading Font Size', 'cost-calculator-builder-pro' ), 'title_font_size', $company_block['design']['title_font_size'] ),
								'font_size'        => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Text Font Size', 'cost-calculator-builder-pro' ), 'font_size', $company_block['design']['font_size'] ),
								'title_color'      => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Heading Color', 'cost-calculator-builder-pro' ), 'title_color', $company_block['design']['title_color'], $company_block['design']['title_color_status'] ),
								'text_color'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $company_block['design']['text_color'], $company_block['design']['text_color_status'] ),
								'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $company_block['design']['background_color'], $company_block['design']['background_color_status'] ),
							),
						),
					),
				),
				'customer_block'        => array(
					'enable'  => $customer_block['enable'],
					'sort_id' => $customer_block['sort_id'],
					'is_body' => $customer_block['is_body'],
					'title'   => __( 'Customer Block', 'cost-calculator-builder-pro' ),
					'key'     => 'customer_block',
					'tabs'    => array(
						'text'   => array(
							'title' => __( 'Text', 'cost-calculator-builder-pro' ),
							'key'   => 'text',
							'data'  => array(
								'title' => CCBPdfManagerHelper::get_pdf_input_text_with_label_data( 'Block Title', 'title', $customer_block['text']['title'] ),
							),
						),
						'design' => array(
							'title' => __( 'Design', 'cost-calculator-builder-pro' ),
							'key'   => 'design',
							'data'  => array(
								'text_align'       => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'text_align', $customer_block['design']['text_align'] ),
								'title_font_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Heading Font Size', 'cost-calculator-builder-pro' ), 'title_font_size', $customer_block['design']['title_font_size'] ),
								'font_size'        => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Text Font Size', 'cost-calculator-builder-pro' ), 'font_size', $customer_block['design']['font_size'] ),
								'title_color'      => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Heading Color', 'cost-calculator-builder-pro' ), 'title_color', $customer_block['design']['title_color'], $customer_block['design']['title_color_status'] ),
								'text_color'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $customer_block['design']['text_color'], $customer_block['design']['text_color_status'] ),
								'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $customer_block['design']['background_color'], $customer_block['design']['background_color_status'] ),
							),
						),
					),
				),
				'additional_text_block' => array(
					'enable'  => $additional_text_block['enable'],
					'sort_id' => $additional_text_block['sort_id'],
					'is_body' => $additional_text_block['is_body'],
					'title'   => __( 'Additional Text Block', 'cost-calculator-builder-pro' ),
					'key'     => 'additional_text_block',
					'tabs'    => array(
						'text'   => array(
							'title' => __( 'Text', 'cost-calculator-builder-pro' ),
							'key'   => 'text',
							'data'  => array(
								'title'       => CCBPdfManagerHelper::get_pdf_input_text_with_label_data( 'Block Title', 'title', $additional_text_block['text']['title'] ),
								'description' => CCBPdfManagerHelper::get_pdf_text_area_data( '', 'description', $additional_text_block['text']['description'] ),
							),
						),
						'design' => array(
							'title' => __( 'Design', 'cost-calculator-builder-pro' ),
							'key'   => 'design',
							'data'  => array(
								'text_align'       => CCBPdfManagerHelper::get_pdf_tab_options_data( '', 'text_align', $additional_text_block['design']['text_align'] ),
								'title_font_size'  => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Heading Font Size', 'cost-calculator-builder-pro' ), 'title_font_size', $additional_text_block['design']['title_font_size'] ),
								'font_size'        => CCBPdfManagerHelper::get_pdf_counter_data( __( 'Text Font Size', 'cost-calculator-builder-pro' ), 'font_size', $additional_text_block['design']['font_size'] ),
								'title_color'      => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Heading Color', 'cost-calculator-builder-pro' ), 'title_color', $additional_text_block['design']['title_color'], $additional_text_block['design']['title_color_status'] ),
								'text_color'       => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Text Color', 'cost-calculator-builder-pro' ), 'text_color', $additional_text_block['design']['text_color'], $additional_text_block['design']['text_color_status'] ),
								'background_color' => CCBPdfManagerHelper::get_pdf_switch_with_color_data( __( 'Background Color', 'cost-calculator-builder-pro' ), 'background_color', $additional_text_block['design']['background_color'], $additional_text_block['design']['background_color_status'] ),
							),
						),
					),
				),
			),
		);
	}

	public static function get_template_by_key( $key = 'default' ) {
		$templates = self::ccb_get_templates();
		return $templates[ $key ];
	}

	public static function get_pdf_templates_from_db() {
		$templates_from_db = get_option( 'ccb_pdf_templates', array() );
		if ( empty( $templates_from_db ) ) {
			$templates_from_db = array();
		}

		return $templates_from_db;
	}

	public static function ccb_get_templates() {
		return array(
			'default'     => array(
				'key'      => 'default',
				'name'     => 'Default',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/default.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => '',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'left_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#222222',
						'background_color'        => '#FFFFFF',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#000000cc',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#000000cc',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#FFFFFF',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#000000cc',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#111111',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#111111',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#ffde4a',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_1'  => array(
				'key'      => 'template_1',
				'name'     => 'Burgundy sidebar',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_1.png',
				'document' => array(
					'enable'  => true,
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/bg-image-1.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'left_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#FFFFFF',
						'background_color'        => '#3F0226',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'right',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#000000cc',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#000000cc',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#F8F8F8',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => false,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_white.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#111111',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#111111',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#ffde4a',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_2'  => array(
				'key'      => 'template_2',
				'name'     => 'Colored blocks',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_2.png',
				'document' => array(
					'enable'  => true,
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/bg-image-2.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#111111',
						'background_color'        => '#111111',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#222222',
							'font_color_status'        => true,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#222222',
							'font_color_status'  => true,
						),
						'design'  => array(
							'text_align'              => 'left',
							'background_color'        => '#FFDE4A',
							'background_color_status' => true,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#000000cc',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#000000cc',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#F8F8F8',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => true,
							'background_color'        => '#8BD2C5',
							'background_color_status' => true,
						),
					),
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => false,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => true,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => true,
							'background_color'        => '#F67994',
							'background_color_status' => true,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#8BD2C5',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_3'  => array(
				'key'      => 'template_3',
				'name'     => 'Contrast pink',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_3.png',
				'document' => array(
					'enable'  => true,
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FAA7E8',
						'background_image' => '',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#FAA7E8',
						'background_color'        => '#262626',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_pink.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FAA7E8',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FAA7E8',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#262626',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#111111',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#111111',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#FAA7E8',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#262626',
							'table_text'                => '#111111',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FAA7E8',
						),
					),
					'footer_text'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#FAA7E8',
							'text_color_status'       => false,
							'background_color'        => '#262626',
							'background_color_status' => false,
						),
					),
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 6,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FAA7E8',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 7,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FAA7E8',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 2,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 3,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#FAA7E8',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#FAA7E8',
							'text_color_status'       => false,
							'background_color'        => '#262626',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#FAA7E8',
							'title_color_status'      => false,
							'text_color'              => '#FAA7E8',
							'text_color_status'       => false,
							'background_color'        => '#262626',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#FAA7E8',
							'title_color_status'      => false,
							'text_color'              => '#FAA7E8',
							'text_color_status'       => false,
							'background_color'        => '#262626',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_4'  => array(
				'key'      => 'template_4',
				'name'     => 'Mesh Gradient',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_4.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/mesh_background.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'left_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#FFFFFF',
						'background_color'        => '#733679',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#87588B',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#87588B',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#FFFFFF',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#733679',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_white.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#111111',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#111111',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#ffde4a',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_5'  => array(
				'key'      => 'template_5',
				'name'     => 'Yellow Paper',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_5.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFEEA',
						'background_image' => '',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'left_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#111111',
						'background_color'        => '#FFFEEA',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'right',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#EAE9D0',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#EAE9D0',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#EAE9D0',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#111111',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#111111',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#ffde4a',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_6'  => array(
				'key'      => 'template_6',
				'name'     => 'Dark Paper',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_6.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#FEFFD7',
						'background_color' => '#222222',
						'background_image' => '',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'left_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#FEFFD7',
						'background_color'        => '#161616',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'right',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#FEFFD7',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#FEFFD7',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#222222',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#FEFFD7',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_paper.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#111111',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#111111',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#ffde4a',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_7'  => array(
				'key'      => 'template_7',
				'name'     => 'Gray Geometry',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_7.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/geometry_bg.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#111111',
						'background_color'        => '#FFFFFF',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => false,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#1A5D7C',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#1A5D7C',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#EDFFFF',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#1A5D7C',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => false,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#111111',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#111111',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'left',
							'background_color'        => '#ffde4a',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => false,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_8'  => array(
				'key'      => 'template_8',
				'name'     => 'Autumn',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_8.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/autumn.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#111111',
						'background_color'        => '#FFFFFF',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#E44849',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#E44849',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#FFF4E4',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#E44849',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => false,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#111111',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#111111',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#ffde4a',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_9'  => array(
				'key'      => 'template_9',
				'name'     => '3D',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_9.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#E8D9FF',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/3d_bg.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#E8D9FF',
						'background_color'        => '#FFFFFF',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 5,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#C0BBE9',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#C0BBE9',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#E8D9FF',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#E5326F',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => false,
						'sort_id' => 4,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_3d.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#E8D9FF',
							'font_color_status'        => true,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#E8D9FF',
							'font_color_status'  => true,
						),
						'design'  => array(
							'text_align'              => 'left',
							'background_color'        => '#E5326F',
							'background_color_status' => true,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_10' => array(
				'key'      => 'template_10',
				'name'     => 'Abstract Mint',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_10.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/template_10_bg.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'left_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#FFFFFF',
						'background_color'        => '#48B6AC',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'right',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#EEEEEE',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#C0BBE9',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#F8F8F8',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_white.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FFFFFF',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FFFFFF',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_11' => array(
				'key'      => 'template_11',
				'name'     => 'Modern',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_11.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/template_11_bg.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => false,
						'text_color'              => '#111111',
						'background_color'        => '#FFFFFF',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#FFFFFF',
							'text_color_status'       => true,
							'background_color'        => '#257101',
							'background_color_status' => true,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => false,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#FFFFFF',
							'text_color_status'       => true,
							'background_color'        => '#257101',
							'background_color_status' => true,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#ACACAC',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#ACACAC',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#C8C715',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => true,
							'table_background'          => '#E1E1E1',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 6,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FFFFFF',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FFFFFF',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'left',
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_12' => array(
				'key'      => 'template_12',
				'name'     => 'Minimalistic',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_12.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/template_12_bg.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#111111',
						'background_color'        => '#FFFFFF',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#111111',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#111111',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#FFFFFF',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#111111',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => true,
							'table_background'          => '#E4E4E4',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 6,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FFFFFF',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FFFFFF',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#E4E4E4',
							'background_color_status' => true,
						),
					),
				),
			),
			'template_13' => array(
				'key'      => 'template_13',
				'name'     => 'Contrast Blue',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_13.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#FFFFFF',
						'background_color' => '#FFFFFF',
						'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/template_13_bg.png',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#FFFFFF',
						'background_color'        => '#FFFFFF',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#294C74',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#294C74',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#FFFFFF',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#07264C',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => true,
							'table_background'          => '#07264C',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 6,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_white.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FFFFFF',
							'font_color_status'        => true,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FFFFFF',
							'font_color_status'  => true,
						),
						'design'  => array(
							'text_align'              => 'left',
							'background_color'        => '#3FA5BC',
							'background_color_status' => true,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#F6AF55',
							'background_color_status' => true,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_14' => array(
				'key'      => 'template_14',
				'name'     => 'Purple Paper',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_14.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#E7D6FF',
						'background_image' => '',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'left_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#111111',
						'background_color'        => '#CAB2EB',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'right',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#CAB2EB',
							'background_color_status' => true,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#CAB2EB',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#CAB2EB',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#CAB2EB',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#CAB2EB',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => false,
						'sort_id' => 6,
						'is_body' => false,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FFFFFF',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FFFFFF',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => true,
							'background_color'        => '#CAB2EB',
							'background_color_status' => true,
						),
					),
				),
			),
			'template_15' => array(
				'key'      => 'template_15',
				'name'     => 'Green Sidebar',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_15.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => '',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => true,
						'text_color'              => '#FFFFFF',
						'background_color'        => '#00B163',
						'background_color_status' => true,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => true,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => true,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#EEEEEE',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#C0BBE9',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#F8F8F8',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 6,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => false,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo_bg_white.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FFFFFF',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FFFFFF',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'center',
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
			'template_16' => array(
				'key'      => 'template_16',
				'name'     => 'Highlighted Blocks',
				'image'    => CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/template_bg_16.png',
				'document' => array(
					'body'    => array(
						'text_color'       => '#111111',
						'background_color' => '#FFFFFF',
						'background_image' => '',
						'side_paddings'    => 0,
					),
					'sidebar' => array(
						'layout'                  => 'right_sidebar',
						'text_color_status'       => false,
						'text_color'              => '#111111',
						'background_color'        => '#FFFFFF',
						'background_color_status' => false,
					),
					'border'  => array(
						'show_border'  => false,
						'border_type'  => 'solid',
						'border_color' => '#111111',
						'border_size'  => 2,
						'border_style' => 'solid',
					),
				),
				'sections' => array(
					'top_text_block'        => array(
						'enable'  => true,
						'sort_id' => 1,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Thank You!',
							'description' => 'We are happy to receive your order and our company will ensure the success of your business.',
						),
						'design'  => array(
							'text_align'              => 'right',
							'title_font_size'         => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_id_and_date'     => array(
						'enable'  => true,
						'sort_id' => 2,
						'is_body' => false,
						'id'      => array(
							'show_order_id'        => 'Order ID',
							'show_order_id_status' => true,
						),
						'date'    => array(
							'show_date'        => 'Order Date',
							'show_date_status' => true,
							'date_format'      => 'dd.mm.yyyy',
							'time_format'      => 'hh:mm',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'order_block'           => array(
						'enable'  => true,
						'sort_id' => 4,
						'is_body' => true,
						'content' => array(
							'show_payment_method' => true,
							'show_grand_total'    => true,
							'show_heading'        => true,
							'heading_name'        => 'Name',
							'heading_unit'        => 'Option/Unit',
							'heading_value'       => 'Total',
							'show_qr_code'        => true,
							'qr_code_link'        => '',
						),
						'images'  => array(
							'stamp_image'     => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/stamp.png',
							'stamp_size'      => 100,
							'signature_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/signature-sign.png',
							'signature_size'  => 80,
						),
						'lines'   => array(
							'show_lines'         => true,
							'line_border_color'  => '#EEEEEE',
							'line_border_size'   => 1,
							'line_border_style'  => 'solid',
							'show_border'        => false,
							'border_color'       => '#C0BBE9',
							'border_style'       => 'solid',
							'border_border_size' => 1,
							'angle_border_size'  => 2,
						),
						'design'  => array(
							'title_font_size'           => 8,
							'font_size'                 => 8,
							'heading_text'              => '#111111',
							'heading_text_status'       => true,
							'heading_background_status' => true,
							'heading_background'        => '#F8F8F8',
							'table_text'                => '#222222',
							'table_text_status'         => false,
							'table_background_status'   => false,
							'table_background'          => '#FFFFFF',
						),
					),
					'footer_text'           => array(
						'enable'  => true,
						'sort_id' => 6,
						'is_body' => true,
						'text'    => array(
							'description' => 'It is important to note that you can request a refund only if the website has not started working.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'font_size'               => 8,
							'text_color'              => '#FFFFFF',
							'text_color_status'       => true,
							'background_color'        => '#111111',
							'background_color_status' => true,
						),
					),
					'brand_block'           => array(
						'enable'  => true,
						'sort_id' => 3,
						'is_body' => true,
						'logo'    => array(
							'logo_image'       => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/company_logo.png',
							'logo_width_size'  => 50,
							'logo_height_size' => 50,
						),
						'name'    => array(
							'show_company_name'        => 'Company Name',
							'show_company_name_status' => true,
							'font_size'                => 18,
							'font_color'               => '#FFFFFF',
							'font_color_status'        => false,
						),
						'slogan'  => array(
							'show_slogan'        => 'Company Slogan',
							'show_slogan_status' => true,
							'font_size'          => 10,
							'font_color'         => '#FFFFFF',
							'font_color_status'  => false,
						),
						'design'  => array(
							'text_align'              => 'left',
							'background_color'        => '#FFDE4A',
							'background_color_status' => true,
						),
					),
					'image_block'           => array(
						'enable'  => false,
						'sort_id' => 5,
						'is_body' => false,
						'default' => array(
							'background_image' => CALC_URL . '/frontend/dist/img/pdf-tool-manager/partials/image-block-bg.png',
							'image_height'     => 125,
						),
					),
					'company_block'         => array(
						'enable'   => true,
						'sort_id'  => 7,
						'is_body'  => false,
						'text'     => array(
							'title'       => 'Company Details',
							'description' => 'Our assistants are ready to consult you from 9 am to 5 pm on weekdays. You can contact us through email or phone number that is mentioned below.',
						),
						'contacts' => array(
							'phone'            => '11111',
							'phone_status'     => true,
							'phone_label'      => 'Phone',
							'email'            => 'john@doe.com',
							'email_status'     => true,
							'email_label'      => 'Email',
							'messenger'        => '@name',
							'messenger_status' => false,
							'messenger_label'  => 'Messenger',
							'site_url'         => 'site.com',
							'site_url_status'  => true,
							'site_url_label'   => 'Site',
							'address'          => 'Address',
							'address_status'   => true,
							'description'      => 'The best place on the Earth Planet',
						),
						'design'   => array(
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_align'              => 'left',
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
					'customer_block'        => array(
						'enable'  => true,
						'sort_id' => 9,
						'is_body' => false,
						'text'    => array(
							'title' => 'Customer Details',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FF7452',
							'background_color_status' => true,
						),
					),
					'additional_text_block' => array(
						'enable'  => true,
						'sort_id' => 8,
						'is_body' => false,
						'text'    => array(
							'title'       => 'Explore more',
							'description' => 'If you are interested in expanding your business, there are other WordPress services and tools which will help to boost your sales. You can explore them on our website.',
						),
						'design'  => array(
							'text_align'              => 'left',
							'title_font_size'         => 12,
							'font_size'               => 8,
							'title_color'             => '#111111',
							'title_color_status'      => false,
							'text_color'              => '#111111',
							'text_color_status'       => false,
							'background_color'        => '#FFFFFF',
							'background_color_status' => false,
						),
					),
				),
			),
		);
	}

	public static function ccb_extract_template_data( $data, $key = 'default' ) {
		$template = self::get_template_by_key();
		$new_key  = ! empty( $key ) && 'default' !== $key ? $key : self::ccb_get_new_id_for_pdf_template();

		$static_templates = self::ccb_get_templates();
		if ( empty( $data['key'] ) || ( ! empty( $data['key'] ) && isset( $static_templates[ $data['key'] ] ) ) ) {
			$data['key'] = $new_key;
		}

		$template['key']   = $data['key'];
		$template['name']  = $data['name'] ?? '';
		$template['image'] = CALC_URL . '/frontend/dist/img/pdf-tool-manager/templates/saved.png';

		if ( ! empty( $data['document']['tabs'] ) ) {
			$body    = $data['document']['tabs']['body'];
			$sidebar = $data['document']['tabs']['sidebar'];
			$border  = $data['document']['tabs']['border'];

			$template['document']['body']['text_color']       = $body['data']['text_color']['value'];
			$template['document']['body']['background_color'] = $body['data']['background_color']['value'];
			$template['document']['body']['background_image'] = $body['data']['background_image']['value'];
			$template['document']['body']['side_paddings']    = $body['data']['side_paddings']['value'];

			$template['document']['sidebar']['layout']                  = $sidebar['data']['layout']['value'];
			$template['document']['sidebar']['text_color_status']       = $sidebar['data']['text_color']['data']['status'];
			$template['document']['sidebar']['text_color']              = $sidebar['data']['text_color']['value'];
			$template['document']['sidebar']['background_color']        = $sidebar['data']['background_color']['value'];
			$template['document']['sidebar']['background_color_status'] = $sidebar['data']['background_color']['data']['status'];

			$template['document']['border']['show_border']  = $border['data']['show_border']['value'];
			$template['document']['border']['border_type']  = $border['data']['border_type']['value'];
			$template['document']['border']['border_color'] = $border['data']['border_color']['value'];
			$template['document']['border']['border_size']  = $border['data']['border_size']['value'];
			$template['document']['border']['border_style'] = $border['data']['border_style']['value'];
		}

		if ( ! empty( $data['sections']['top_text_block']['tabs'] ) ) {
			$text   = $data['sections']['top_text_block']['tabs']['text'];
			$design = $data['sections']['top_text_block']['tabs']['design'];

			$template['sections']['top_text_block']['enable']  = $data['sections']['top_text_block']['enable'];
			$template['sections']['top_text_block']['is_body'] = $data['sections']['top_text_block']['is_body'];
			$template['sections']['top_text_block']['sort_id'] = $data['sections']['top_text_block']['sort_id'];

			$template['sections']['top_text_block']['text']['title']       = $text['data']['title']['value'];
			$template['sections']['top_text_block']['text']['description'] = $text['data']['description']['value'];

			$template['sections']['top_text_block']['design']['text_align']              = $design['data']['text_align']['value'];
			$template['sections']['top_text_block']['design']['title_font_size']         = $design['data']['title_font_size']['value'];
			$template['sections']['top_text_block']['design']['font_size']               = $design['data']['font_size']['value'];
			$template['sections']['top_text_block']['design']['text_color']              = $design['data']['text_color']['value'];
			$template['sections']['top_text_block']['design']['text_color_status']       = $design['data']['text_color']['data']['status'];
			$template['sections']['top_text_block']['design']['background_color']        = $design['data']['background_color']['value'];
			$template['sections']['top_text_block']['design']['background_color_status'] = $design['data']['background_color']['data']['status'];
		}

		if ( ! empty( $data['sections']['order_id_and_date']['tabs'] ) ) {
			$id     = $data['sections']['order_id_and_date']['tabs']['id'];
			$date   = $data['sections']['order_id_and_date']['tabs']['date'];
			$design = $data['sections']['order_id_and_date']['tabs']['design'];

			$template['sections']['order_id_and_date']['enable']  = $data['sections']['order_id_and_date']['enable'];
			$template['sections']['order_id_and_date']['is_body'] = $data['sections']['order_id_and_date']['is_body'];
			$template['sections']['order_id_and_date']['sort_id'] = $data['sections']['order_id_and_date']['sort_id'];

			$template['sections']['order_id_and_date']['id']['show_order_id']        = $id['data']['show_order_id']['value'];
			$template['sections']['order_id_and_date']['id']['show_order_id_status'] = $id['data']['show_order_id']['data']['status'];

			$template['sections']['order_id_and_date']['date']['show_date']        = $date['data']['show_date']['value'];
			$template['sections']['order_id_and_date']['date']['show_date_status'] = $date['data']['show_date']['data']['status'];
			$template['sections']['order_id_and_date']['date']['date_format']      = $date['data']['format']['data']['date_format']['value'];
			$template['sections']['order_id_and_date']['date']['time_format']      = $date['data']['format']['data']['time_format']['value'];

			$template['sections']['order_id_and_date']['design']['text_align']              = $design['data']['text_align']['value'];
			$template['sections']['order_id_and_date']['design']['font_size']               = $design['data']['font_size']['value'];
			$template['sections']['order_id_and_date']['design']['text_color']              = $design['data']['text_color']['value'];
			$template['sections']['order_id_and_date']['design']['text_color_status']       = $design['data']['text_color']['data']['status'];
			$template['sections']['order_id_and_date']['design']['background_color']        = $design['data']['background_color']['value'];
			$template['sections']['order_id_and_date']['design']['background_color_status'] = $design['data']['background_color']['data']['status'];
		}

		if ( ! empty( $data['sections']['order_block']['tabs'] ) ) {
			$content = $data['sections']['order_block']['tabs']['content'];
			$images  = $data['sections']['order_block']['tabs']['images'];
			$lines   = $data['sections']['order_block']['tabs']['lines'];
			$design  = $data['sections']['order_block']['tabs']['design'];

			$template['sections']['order_block']['enable']  = $data['sections']['order_block']['enable'];
			$template['sections']['order_block']['is_body'] = $data['sections']['order_block']['is_body'];
			$template['sections']['order_block']['sort_id'] = $data['sections']['order_block']['sort_id'];

			$template['sections']['order_block']['content']['show_payment_method'] = $content['data']['show_payment_method']['value'];
			$template['sections']['order_block']['content']['show_grand_total']    = $content['data']['show_grand_total']['value'];
			$template['sections']['order_block']['content']['show_heading']        = $content['data']['show_heading']['value'];
			$template['sections']['order_block']['content']['heading_name']        = $content['data']['heading_name']['value'];
			$template['sections']['order_block']['content']['heading_unit']        = $content['data']['heading_unit']['value'];
			$template['sections']['order_block']['content']['heading_value']       = $content['data']['heading_value']['value'];
			$template['sections']['order_block']['content']['show_qr_code']        = $content['data']['show_qr_code']['value'];
			$template['sections']['order_block']['content']['qr_code_link']        = $content['data']['qr_code_link']['value'];

			$template['sections']['order_block']['images']['stamp_image']     = $images['data']['stamp_image']['value'];
			$template['sections']['order_block']['images']['stamp_size']      = $images['data']['stamp_size']['value'];
			$template['sections']['order_block']['images']['signature_image'] = $images['data']['signature_image']['value'];
			$template['sections']['order_block']['images']['signature_size']  = $images['data']['signature_size']['value'];

			$template['sections']['order_block']['lines']['show_lines']         = $lines['data']['show_lines']['value'];
			$template['sections']['order_block']['lines']['line_border_color']  = $lines['data']['line_border_color']['value'];
			$template['sections']['order_block']['lines']['line_border_size']   = $lines['data']['line_border_size']['value'];
			$template['sections']['order_block']['lines']['line_border_style']  = $lines['data']['line_border_style']['value'];
			$template['sections']['order_block']['lines']['show_border']        = $lines['data']['show_border']['value'];
			$template['sections']['order_block']['lines']['border_color']       = $lines['data']['border_color']['value'];
			$template['sections']['order_block']['lines']['border_style']       = $lines['data']['border_style']['value'];
			$template['sections']['order_block']['lines']['border_border_size'] = $lines['data']['border_border_size']['value'];
			$template['sections']['order_block']['lines']['angle_border_size']  = $lines['data']['angle_border_size']['value'];

			$template['sections']['order_block']['design']['title_font_size']           = $design['data']['title_font_size']['value'];
			$template['sections']['order_block']['design']['heading_text']              = $design['data']['heading_text']['value'];
			$template['sections']['order_block']['design']['heading_text_status']       = $design['data']['heading_text']['data']['status'];
			$template['sections']['order_block']['design']['heading_background']        = $design['data']['heading_background']['value'];
			$template['sections']['order_block']['design']['heading_background_status'] = $design['data']['heading_background']['data']['status'];
			$template['sections']['order_block']['design']['font_size']                 = $design['data']['font_size']['value'];
			$template['sections']['order_block']['design']['table_text']                = $design['data']['table_text']['value'];
			$template['sections']['order_block']['design']['table_text_status']         = $design['data']['table_text']['data']['status'];
			$template['sections']['order_block']['design']['table_background_status']   = $design['data']['table_background']['data']['status'];
			$template['sections']['order_block']['design']['table_background']          = $design['data']['table_background']['value'];
		}

		if ( ! empty( $data['sections']['footer_text']['tabs'] ) ) {
			$text   = $data['sections']['footer_text']['tabs']['text'];
			$design = $data['sections']['footer_text']['tabs']['design'];

			$template['sections']['footer_text']['enable']  = $data['sections']['footer_text']['enable'];
			$template['sections']['footer_text']['is_body'] = $data['sections']['footer_text']['is_body'];
			$template['sections']['footer_text']['sort_id'] = $data['sections']['footer_text']['sort_id'];

			$template['sections']['footer_text']['text']['description'] = $text['data']['description']['value'];

			$template['sections']['footer_text']['design']['text_align']              = $design['data']['text_align']['value'];
			$template['sections']['footer_text']['design']['font_size']               = $design['data']['font_size']['value'];
			$template['sections']['footer_text']['design']['text_color']              = $design['data']['text_color']['value'];
			$template['sections']['footer_text']['design']['text_color_status']       = $design['data']['text_color']['data']['status'];
			$template['sections']['footer_text']['design']['background_color']        = $design['data']['background_color']['value'];
			$template['sections']['footer_text']['design']['background_color_status'] = $design['data']['background_color']['data']['status'];
		}

		if ( ! empty( $data['sections']['brand_block']['tabs'] ) ) {
			$logo   = $data['sections']['brand_block']['tabs']['logo'];
			$name   = $data['sections']['brand_block']['tabs']['name'];
			$slogan = $data['sections']['brand_block']['tabs']['slogan'];
			$design = $data['sections']['brand_block']['tabs']['design'];

			$template['sections']['brand_block']['enable']  = $data['sections']['brand_block']['enable'];
			$template['sections']['brand_block']['is_body'] = $data['sections']['brand_block']['is_body'];
			$template['sections']['brand_block']['sort_id'] = $data['sections']['brand_block']['sort_id'];

			$template['sections']['brand_block']['logo']['logo_image']       = $logo['data']['logo_image']['value'];
			$template['sections']['brand_block']['logo']['logo_width_size']  = $logo['data']['logo_width_size']['value'];
			$template['sections']['brand_block']['logo']['logo_height_size'] = $logo['data']['logo_height_size']['value'];

			$template['sections']['brand_block']['name']['show_company_name']        = $name['data']['show_company_name']['value'];
			$template['sections']['brand_block']['name']['show_company_name_status'] = $name['data']['show_company_name']['data']['status'];
			$template['sections']['brand_block']['name']['font_size']                = $name['data']['font_size']['value'];
			$template['sections']['brand_block']['name']['font_color']               = $name['data']['font_color']['value'];
			$template['sections']['brand_block']['name']['font_color_status']        = $name['data']['font_color']['data']['status'];

			$template['sections']['brand_block']['slogan']['show_slogan']        = $slogan['data']['show_slogan']['value'];
			$template['sections']['brand_block']['slogan']['show_slogan_status'] = $slogan['data']['show_slogan']['data']['status'];
			$template['sections']['brand_block']['slogan']['font_size']          = $slogan['data']['font_size']['value'];
			$template['sections']['brand_block']['slogan']['font_color']         = $slogan['data']['font_color']['value'];
			$template['sections']['brand_block']['slogan']['font_color_status']  = $slogan['data']['font_color']['data']['status'];

			$template['sections']['brand_block']['design']['text_align']              = $design['data']['text_align']['value'];
			$template['sections']['brand_block']['design']['background_color']        = $design['data']['background_color']['value'];
			$template['sections']['brand_block']['design']['background_color_status'] = $design['data']['background_color']['data']['status'];
		}

		if ( ! empty( $data['sections']['image_block']['tabs'] ) ) {
			$default = $data['sections']['image_block']['tabs']['default'];

			$template['sections']['image_block']['enable']  = $data['sections']['image_block']['enable'];
			$template['sections']['image_block']['is_body'] = $data['sections']['image_block']['is_body'];
			$template['sections']['image_block']['sort_id'] = $data['sections']['image_block']['sort_id'];

			$template['sections']['image_block']['default']['background_image'] = $default['data']['background_image']['value'];
			$template['sections']['image_block']['default']['image_height']     = $default['data']['image_height']['value'];
		}

		if ( ! empty( $data['sections']['company_block']['tabs'] ) ) {
			$text     = $data['sections']['company_block']['tabs']['text'];
			$contacts = $data['sections']['company_block']['tabs']['contacts'];
			$design   = $data['sections']['company_block']['tabs']['design'];

			$template['sections']['company_block']['enable']  = $data['sections']['company_block']['enable'];
			$template['sections']['company_block']['is_body'] = $data['sections']['company_block']['is_body'];
			$template['sections']['company_block']['sort_id'] = $data['sections']['company_block']['sort_id'];

			$template['sections']['company_block']['text']['title']       = $text['data']['title']['value'];
			$template['sections']['company_block']['text']['description'] = $text['data']['description']['value'];

			$template['sections']['company_block']['contacts']['phone']            = $contacts['data']['phone']['value'];
			$template['sections']['company_block']['contacts']['phone_status']     = $contacts['data']['phone']['data']['status'];
			$template['sections']['company_block']['contacts']['phone_label']      = $contacts['data']['phone']['data']['label'];
			$template['sections']['company_block']['contacts']['email']            = $contacts['data']['email']['value'];
			$template['sections']['company_block']['contacts']['email_status']     = $contacts['data']['email']['data']['status'];
			$template['sections']['company_block']['contacts']['email_label']      = $contacts['data']['email']['data']['label'];
			$template['sections']['company_block']['contacts']['messenger']        = $contacts['data']['messenger']['value'];
			$template['sections']['company_block']['contacts']['messenger_status'] = $contacts['data']['messenger']['data']['status'];
			$template['sections']['company_block']['contacts']['messenger_label']  = $contacts['data']['messenger']['data']['label'];
			$template['sections']['company_block']['contacts']['site_url']         = $contacts['data']['site_url']['value'];
			$template['sections']['company_block']['contacts']['site_url_status']  = $contacts['data']['site_url']['data']['status'];
			$template['sections']['company_block']['contacts']['site_url_label']   = $contacts['data']['site_url']['data']['label'];
			$template['sections']['company_block']['contacts']['address']          = $contacts['data']['address']['value'];
			$template['sections']['company_block']['contacts']['address_status']   = $contacts['data']['address']['data']['status'];
			$template['sections']['company_block']['contacts']['description']      = $contacts['data']['description']['value'];

			$template['sections']['company_block']['design']['text_align']              = $design['data']['text_align']['value'];
			$template['sections']['company_block']['design']['title_font_size']         = $design['data']['title_font_size']['value'];
			$template['sections']['company_block']['design']['title_color']             = $design['data']['title_color']['value'];
			$template['sections']['company_block']['design']['title_color_status']      = $design['data']['title_color']['data']['status'];
			$template['sections']['company_block']['design']['font_size']               = $design['data']['font_size']['value'];
			$template['sections']['company_block']['design']['text_color']              = $design['data']['text_color']['value'];
			$template['sections']['company_block']['design']['text_color_status']       = $design['data']['text_color']['data']['status'];
			$template['sections']['company_block']['design']['background_color']        = $design['data']['background_color']['value'];
			$template['sections']['company_block']['design']['background_color_status'] = $design['data']['background_color']['data']['status'];
		}

		if ( ! empty( $data['sections']['customer_block']['tabs'] ) ) {
			$text   = $data['sections']['customer_block']['tabs']['text'];
			$design = $data['sections']['customer_block']['tabs']['design'];

			$template['sections']['customer_block']['enable']  = $data['sections']['customer_block']['enable'];
			$template['sections']['customer_block']['is_body'] = $data['sections']['customer_block']['is_body'];
			$template['sections']['customer_block']['sort_id'] = $data['sections']['customer_block']['sort_id'];

			$template['sections']['customer_block']['text']['title'] = $text['data']['title']['value'];

			$template['sections']['customer_block']['design']['text_align']              = $design['data']['text_align']['value'];
			$template['sections']['customer_block']['design']['title_font_size']         = $design['data']['title_font_size']['value'];
			$template['sections']['customer_block']['design']['title_color']             = $design['data']['title_color']['value'];
			$template['sections']['customer_block']['design']['title_color_status']      = $design['data']['title_color']['data']['status'];
			$template['sections']['customer_block']['design']['font_size']               = $design['data']['font_size']['value'];
			$template['sections']['customer_block']['design']['text_color']              = $design['data']['text_color']['value'];
			$template['sections']['customer_block']['design']['text_color_status']       = $design['data']['text_color']['data']['status'];
			$template['sections']['customer_block']['design']['background_color']        = $design['data']['background_color']['value'];
			$template['sections']['customer_block']['design']['background_color_status'] = $design['data']['background_color']['data']['status'];
		}

		if ( ! empty( $data['sections']['additional_text_block']['tabs'] ) ) {
			$text   = $data['sections']['additional_text_block']['tabs']['text'];
			$design = $data['sections']['additional_text_block']['tabs']['design'];

			$template['sections']['additional_text_block']['enable']  = $data['sections']['additional_text_block']['enable'];
			$template['sections']['additional_text_block']['is_body'] = $data['sections']['additional_text_block']['is_body'];
			$template['sections']['additional_text_block']['sort_id'] = $data['sections']['additional_text_block']['sort_id'];

			$template['sections']['additional_text_block']['text']['title']       = $text['data']['title']['value'];
			$template['sections']['additional_text_block']['text']['description'] = $text['data']['description']['value'];

			$template['sections']['additional_text_block']['design']['text_align']              = $design['data']['text_align']['value'];
			$template['sections']['additional_text_block']['design']['title_font_size']         = $design['data']['title_font_size']['value'];
			$template['sections']['additional_text_block']['design']['title_color']             = $design['data']['title_color']['value'];
			$template['sections']['additional_text_block']['design']['title_color_status']      = $design['data']['title_color']['data']['status'];
			$template['sections']['additional_text_block']['design']['font_size']               = $design['data']['font_size']['value'];
			$template['sections']['additional_text_block']['design']['text_color']              = $design['data']['text_color']['value'];
			$template['sections']['additional_text_block']['design']['text_color_status']       = $design['data']['text_color']['data']['status'];
			$template['sections']['additional_text_block']['design']['background_color']        = $design['data']['background_color']['value'];
			$template['sections']['additional_text_block']['design']['background_color_status'] = $design['data']['background_color']['data']['status'];
		}

		return $template;
	}

	public static function ccb_get_new_id_for_pdf_template() {
		$idx               = 0;
		$templates_from_db = self::get_pdf_templates_from_db();
		$count             = count( $templates_from_db );
		if ( $count > 0 ) {
			$keys    = array_keys( $templates_from_db );
			$lat_key = $keys[ $count - 1 ];
			$idx     = intval( str_replace( 'custom_template_', '', $lat_key ) );
		}

		return 'custom_template_' . ( $idx + 1 );
	}

	public static function ccb_get_template_by_key( $key = 'default' ) {
		$templates = self::ccb_get_templates();
		if ( $templates[ $key ] ) {
			return $templates[ $key ];
		}

		return $templates['default'];
	}
}
