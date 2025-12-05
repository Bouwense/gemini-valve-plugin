<?php

namespace cBuilder\Classes;

use cBuilder\Classes\Appearance\Presets\CCBPresetGenerator;
use cBuilder\Classes\Database\Condition;
use cBuilder\Classes\Database\Discounts;
use cBuilder\Classes\Database\Orders;
use cBuilder\Classes\Database\Payments;
use cBuilder\Classes\Database\Forms;
use cBuilder\Classes\Database\FormCalcs;
use cBuilder\Classes\Database\FormFields;
use cBuilder\Classes\Database\FormFieldsAttributes;
use cBuilder\Classes\Database\Promocodes;
use cBuilder\Classes\pdfManager\CCBPdfManager;
use cBuilder\Classes\pdfManager\CCBPdfManagerTemplates;
use cBuilder\Classes\Database\AnalyticsViews;
use cBuilder\Classes\Database\AnalyticsInteractions;

class CCBUpdatesCallbacks {

	public static function calculator_add_container_blur() {
		$presets = CCBPresetGenerator::get_static_preset_from_db();
		foreach ( $presets as $idx => $preset ) {
			if ( isset( $preset['desktop']['colors'] ) ) {
				$colors = $preset['desktop']['colors'];
				if ( isset( $colors['container_color'] ) ) {
					$container_bg = $colors['container_color'];

					unset( $colors['container_color'] );
					$colors['container']         = CCBPresetGenerator::get_container_default( $container_bg );
					$preset['desktop']['colors'] = $colors;
				}
			}
			$presets[ $idx ] = $preset;
		}

		CCBPresetGenerator::update_presets( $presets );
	}


	/**
	 *  Add 'Summary header font' options to Typography block
	 */
	public static function ccb_add_summary_header_appearance() {
		$presets = CCBPresetGenerator::get_static_preset_from_db();

		foreach ( $presets as $idx => $preset ) {
			if ( isset( $preset['data'] ) && ! isset( $preset['data']['desktop']['typography']['summary_header_size'] ) ) {
				$preset_data = $preset['data'];
				if ( ! isset( $preset_data['desktop']['typography']['summary_header_size'] ) ) {
					$preset_data['desktop']['typography']['summary_header_size'] = 14;
				}
				if ( ! isset( $preset_data['mobile']['typography']['summary_header_size'] ) ) {
					$preset_data['mobile']['typography']['summary_header_size'] = 14;
				}

				if ( ! isset( $preset_data['desktop']['typography']['summary_header_font_weight'] ) ) {
					$preset_data['desktop']['typography']['summary_header_font_weight'] = 700;
				}
				if ( ! isset( $preset_data['mobile']['typography']['summary_header_font_weight'] ) ) {
					$preset_data['mobile']['typography']['summary_header_font_weight'] = 700;
				}

				$preset['data']  = $preset_data;
				$presets[ $idx ] = $preset;
			}
		}

		CCBPresetGenerator::update_presets( $presets );
	}

	/**
	 * 3.1.51
	 */
	public static function ccb_update_min_date_info_to_unselectable() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) && preg_replace( '/_field_id.*/', '', $field['alias'] ) === 'datePicker' ) {
					if ( empty( $field['not_allowed_dates'] ) ) {
						$field['not_allowed_dates'] = array(
							'all_past' => false,
							'current'  => false,
							'period'   => array(
								array(
									'start' => null,
									'end'   => null,
								),
							),
						);
					}

					if ( ! empty( $field['min_date'] ) ) {
						$field['is_have_unselectable']          = true;
						$field['not_allowed_dates']['all_past'] = true;

						if ( $field['min_date_days'] > 0 ) {
							$field['not_allowed_dates']['current'] = true;
							$field['days_from_current']            = $field['min_date_days'] - 1;
						}
					}

					$fields[ $key ] = $field;
				}
			}
			update_post_meta( $calculator->ID, 'stm-fields', (array) $fields );
		}
	}

	/**
	 * 3.1.34
	 * Add 'Total field text transform' option to Typography block
	 */
	public static function ccb_add_text_transform_appearance() {
		$presets = CCBPresetGenerator::get_static_preset_from_db();

		$default_text_transform = 'capitalize';
		foreach ( $presets as $idx => $preset ) {
			if ( isset( $preset['data'] ) ) {
				$preset_data = $preset['data'];

				if ( ! isset( $preset_data['desktop']['typography']['total_text_transform'] ) ) {
					$preset_data['desktop']['typography']['total_text_transform'] = $default_text_transform;
				}

				if ( ! isset( $preset_data['mobile']['typography']['total_text_transform'] ) ) {
					$preset_data['mobile']['typography']['total_text_transform'] = $default_text_transform;
				}

				$preset['data']  = $preset_data;
				$presets[ $idx ] = $preset;
			}
		}

		CCBPresetGenerator::update_presets( $presets );
	}

	/**
	 * 3.1.32
	 * Add default webhooks settings
	 */
	public static function ccb_add_default_webhook_settings() {
		$calculators = self::get_calculators();
		foreach ( $calculators as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );
			if ( empty( $calc_settings['webhooks'] ) ) {
				$calc_settings['webhooks']['enableSendForms']  = false;
				$calc_settings['webhooks']['enableEmailQuote'] = false;
				$calc_settings['webhooks']['enablePaymentBtn'] = false;

				update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $calc_settings ) );
			}
		}
	}

	/**
	 * 3.1.31
	 * Update "Add svg color to Appearance
	 */
	public static function calculator_add_svg_color_appearance() {
		$presets = CCBPresetGenerator::get_static_preset_from_db();

		foreach ( $presets as $idx => $preset ) {
			if ( isset( $preset['data'] ) ) {
				$preset_data = $preset['data'];

				if ( ! isset( $preset_data['desktop']['colors']['svg_color'] ) ) {
					$preset_data['desktop']['colors']['svg_color'] = 0;
				}

				$preset['data']  = $preset_data;
				$presets[ $idx ] = $preset;
			}
		}

		CCBPresetGenerator::update_presets( $presets );
	}

	/**
	 * 3.1.29
	 * Update "Deliver Service" template in wp posts
	 * change "Type of Service" field from drop down to radio
	 */
	public static function ccb_update_template_delivery_service_field() {
		$templateName = 'Delivery Service';

		$args = array(
			'post_type'   => 'cost-calc',
			'post_status' => array( 'draft' ),
			'title'       => $templateName,
		);

		if ( class_exists( 'Polylang' ) ) {
			$args['lang'] = '';
		}

		$calcTemplates = get_posts( $args );

		if ( count( $calcTemplates ) === 0 ) {
			return;
		}

		$newTemplateData = CCBCalculatorTemplates::get_template_by_name( $templateName );
		if ( ! isset( $newTemplateData ) ) {
			return;
		}

		if ( ! isset( $newTemplateData['ccb_fields'] ) || count( $newTemplateData['ccb_fields'] ) === 0 ) {
			return;
		}

		update_post_meta( $calcTemplates[0]->ID, 'stm-formula', (array) $newTemplateData['ccb_formula'] );
		update_post_meta( $calcTemplates[0]->ID, 'stm-fields', (array) $newTemplateData['ccb_fields'] );
	}

	/**
	 * 3.1.23
	 * Update woo_products settings
	 * create category_ids option
	 * add all category ids if woo_products enabled empty value ( cause by default is "All categories" )
	 * add to array chosen value ( category_id )  if exist
	 */
	public static function ccb_make_woo_product_category_id_multiple() {
		$calculators    = self::get_calculators();
		$all_categories = ccb_woo_categories();
		$category_ids   = array();
		if ( ! ( $all_categories instanceof \WP_Error ) ) {
			$category_ids = array_column( $all_categories, 'term_id' );
		}

		foreach ( $calculators as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );
			$woo_products  = $calc_settings['woo_products'];

			/** create new option for list of category ids */
			$woo_products['category_ids'] = array();
			if ( null !== $woo_products['category_id'] ) {
				array_push( $woo_products['category_ids'], $woo_products['category_id'] );
			}

			if ( $woo_products['enable'] ) {
				$woo_products['category_ids'] = $category_ids;
			}

			$calc_settings['woo_products'] = $woo_products;
			update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $calc_settings ) );
		}
	}

	public static function ccb_appearance_totals( $totals, $descriptions ) {
		$formulas = array();
		foreach ( $totals as $idx => $total ) {
			$ccbDesc = strpos( $descriptions, '[ccb-total-' . $idx . ']' );
			if ( false !== $ccbDesc ) {
				$formulas[] = array(
					'idx'   => $idx,
					'title' => $total['label'],
					'alias' => $total['alias'] ?? '',
				);
			}
		}

		return $formulas;
	}

	public static function get_total_fields( $calc_id ) {
		$fields = get_post_meta( $calc_id, 'stm-fields', true );
		$totals = array();
		foreach ( $fields as $field ) {
			if ( isset( $field['_tag'] ) && 'cost-total' === $field['_tag'] ) {
				$totals[] = $field;
			}
		}

		return $totals;
	}

	public static function preset_exist( $presets, $colors ) {
		$exist = false;
		foreach ( $presets as $key => $preset ) {
			if ( isset( $preset['data'] ) ) {
				if ( $preset['data']['desktop']['colors'] == $colors && ! is_numeric( $exist ) ) { // phpcs:ignore
					$exist = $key;
				}
			}
		}

		return $exist;
	}

	public static function theme_exist( $themes, $colors ) {
		$exist = false;

		foreach ( $themes as $key => $theme ) {
			if ( isset( $theme['data'] ) ) {
				$theme_colors = $theme['data']['desktop']['colors'];

				$colors_lower = array();
				foreach ( $colors as $color_key => $color ) {
					if ( 'container' === $color_key ) {
						$colors_lower[ $color_key ]          = $color;
						$colors_lower[ $color_key ]['color'] = strtolower( $color['color'] );
					} elseif ( ! empty( $color ) ) {
						$colors_lower[ $color_key ] = strtolower( $color );
					}
				}

				$theme_colors_lower = array();
				foreach ( $theme_colors as $color_key => $color ) {
					if ( 'container' === $color_key ) {
						$theme_colors_lower[ $color_key ]          = $color;
						$theme_colors_lower[ $color_key ]['color'] = strtolower( $color['color'] );
					} else {
						$theme_colors_lower[ $color_key ] = strtolower( $color );
					}
				}

				if ( ! $exist && $theme_colors_lower == $colors_lower ) { // phpcs:ignore
					$exist = $key;
				}
			}
		}

		return $exist;
	}

	public static function get_calculators() {
		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'cost-calc',
			'post_status'    => array( 'publish' ),
		);

		if ( class_exists( 'Polylang' ) ) {
			$args['lang'] = '';
		}

		$calculators = new \WP_Query( $args );

		return $calculators->posts;
	}

	public static function calculator_add_templates() {
		CCBCalculatorTemplates::render_templates();
		CCBCalculators::create_sample_calculator();
		ccb_set_admin_url();
	}

	public static function calculator_email_templates_footer_toggle() {
		$general_settings = CCBSettingsData::get_calc_global_settings();

		if ( isset( $general_settings['email_templates'] ) ) {
			if ( ! isset( $general_settings['email_templates']['footer'] ) ) {
				$general_settings['email_templates']['footer'] = true;

				update_option( 'ccb_general_settings', apply_filters( 'calc_update_options', $general_settings ) );
			}
		}
	}

	public static function calculator_add_styles() {
		$general_settings = get_option( 'ccb_general_settings', \cBuilder\Classes\CCBSettingsData::general_settings_data() );
		if ( isset( $general_settings['general'] ) && empty( $general_settings['general']['styles'] ) ) {
			$general_settings['styles'] = array(
				'radio'             => '',
				'checkbox'          => '',
				'toggle'            => '',
				'radio_with_img'    => '',
				'checkbox_with_img' => '',
			);
		}
	}

	public static function ccb_add_thank_you_page_settings() {
		$settings    = \cBuilder\Classes\CCBSettingsData::settings_data();
		$calculators = self::get_calculators();
		foreach ( $calculators as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );
			if ( ! isset( $calc_settings['thankYouPage'] ) ) {
				$calc_settings['thankYouPage'] = $settings['thankYouPage'];
			}
			update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $calc_settings ) );
		}
	}

	public static function ccb_sync_calc_settings() {
		$settings    = \cBuilder\Classes\CCBSettingsData::settings_data();
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );
			$sync_settings = ccb_array_merge_recursive_left_source( $settings, $calc_settings ); // phpcs:ignore
			update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $sync_settings ) );
		}
	}

	public static function ccb_sync_general_settings() {
		$calc_options_settings = CCBSettingsData::get_calc_global_settings();
		$calc_static_settings  = \cBuilder\Classes\CCBSettingsData::general_settings_data();
		$sync_settings         = ccb_array_merge_recursive_left_source( $calc_static_settings, $calc_options_settings ); // phpcs:ignore

		update_option( 'ccb_general_settings', $sync_settings );
	}

	public static function ccb_update_checkbox_conditions() {
		$calculators = self::get_calculators();
		foreach ( $calculators as $calculator ) {
			$conditions = get_post_meta( $calculator->ID, 'stm-conditions', true );

			if ( ! empty( $conditions['links'] ) ) {
				foreach ( $conditions['links'] as $index => $link ) {

					$options_from = $link['options_from'] ?? '';
					$condition    = $link['condition'] ?? array();

					if ( ( str_contains( $options_from, 'checkbox' ) || str_contains( $options_from, 'toggle' ) ) ) {
						foreach ( $condition as $condition_key => $condition_item ) {
							foreach ( $condition_item['conditions'] as $inner_key => $inner_condition ) {
								if ( in_array( $inner_condition['condition'], array( '==', '!=' ), true ) && ! isset( $inner_condition['checkedValues'] ) ) {
									$inner_condition['checkedValues'] = array( $inner_condition['key'] );
									$inner_condition['key']           = '';
									$inner_condition['condition']     = '==' === $inner_condition['condition'] ? 'in' : 'not in';

									$link['condition'][ $condition_key ]['conditions'][ $inner_key ] = $inner_condition;
								}
							}
						}
					}

					$conditions['links'][ $index ] = $link;
				}
			}

			update_post_meta( $calculator->ID, 'stm-conditions', apply_filters( 'stm_ccb_sanitize_array', $conditions ) );
		}
	}

	public static function ccb_add_invoice_success_btn() {
		$general_settings = CCBSettingsData::get_calc_global_settings();

		if ( isset( $general_settings['invoice'] ) ) {
			if ( ! isset( $general_settings['invoice']['successText'] ) ) {
				// don't change domain 'cost-calculator-builder' because if user already change this text into another will extend automatically
				$general_settings['invoice']['successBtn'] = __( 'Email Quote Successfully Sent!', 'cost-calculator-builder' );
			}

			if ( ! isset( $general_settings['invoice']['errorText'] ) ) {
				// don't change domain 'cost-calculator-builder-pro' because if user already change this text into another will extend automatically
				$general_settings['invoice']['errorText'] = __( 'Fill in the required fields correctly.', 'cost-calculator-builder-pro' );
			}
		}

		update_option( 'ccb_general_settings', $general_settings );
	}

	public static function fix_payment_totals( $formulas, $totals ) {
		foreach ( $formulas as $key => $formula ) {
			if ( ! isset( $formula['alias'] ) && isset( $formula['idx'] ) && isset( $totals[ $formula['idx'] ] ) ) {
				if ( isset( $totals[ $formula['idx'] ]['alias'] ) ) {
					$formulas[ $key ]['alias'] = $totals[ $formula['idx'] ]['alias'];
				}
			}
		}

		return $formulas;
	}

	public static function ccb_update_payment_totals() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$totals        = self::get_total_fields( $calculator->ID );
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );

			if ( isset( $calc_settings['formFields']['formulas'] ) ) {
				$calc_settings['formFields']['formulas'] = self::fix_payment_totals( $calc_settings['formFields']['formulas'], $totals );
			}

			if ( isset( $calc_settings['woo_checkout']['formulas'] ) ) {
				$calc_settings['woo_checkout']['formulas'] = self::fix_payment_totals( $calc_settings['woo_checkout']['formulas'], $totals );
			}

			update_option( 'stm_ccb_form_settings_' . $calculator->ID, $calc_settings );
		}
	}

	public static function ccb_update_legacy_totals() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );
			foreach ( $fields as $key => $field ) {
				if ( 'cost-total' === $field['_tag'] && ! isset( $field['legacyFormula'] ) ) {
					if ( ! isset( $field['formulaView'] ) ) {
						$fields[ $key ]['formulaView'] = false;
					}
					$fields[ $key ]['legacyFormula'] = $field['costCalcFormula'];
				}
			}

			update_post_meta( $calculator->ID, 'stm-fields', $fields );

			$formulas = get_post_meta( $calculator->ID, 'stm-formula', true );
			foreach ( $formulas as $key => $formula ) {
				foreach ( $fields as $field ) {
					if ( isset( $field['formulaView'] ) && isset( $field['alias'] ) && isset( $formula['alias'] ) && $field['alias'] === $formula['alias'] ) {
						$formulas[ $key ]['formula'] = $field['formulaView'] ? $field['legacyFormula'] : $formulas[ $key ]['formula'];
					}
				}
			}

			update_post_meta( $calculator->ID, 'stm-formula', $formulas );
		}
	}

	public static function ccb_add_show_value_option() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields        = get_post_meta( $calculator->ID, 'stm-fields', true );
			$change_fields = array( 'dropDown', 'dropDown_with_img', 'radio_with_img', 'checkbox_with_img' );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) ) {
					$field_name = preg_replace( '/_field_id.*/', '', $field['alias'] );
					if ( in_array( $field_name, $change_fields, true ) && ! isset( $field['show_value_in_option'] ) ) {
						if ( 'radio_with_img' === $field_name && isset( $field['summary_view'] ) && 'show_value' !== $field['summary_view'] ) {
							$field['show_value_in_option'] = false;
						} elseif ( in_array( $field_name, array( 'dropDown', 'dropDown_with_img' ), true ) && ! $field['allowCurrency'] ) {
							$field['show_value_in_option'] = false;
						} else {
							$field['show_value_in_option'] = true;
						}
					}
				}
				$fields[ $key ] = $field;
			}

			update_post_meta( $calculator->ID, 'stm-fields', (array) $fields );
		}
	}

	public static function ccb_add_price_for_file() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) && str_contains( $field['alias'], 'file' ) && ! isset( $field['allowPrice'] ) ) {
					$field['allowPrice']       = true;
					$field['calculatePerEach'] = false;

					$fields[ $key ] = $field;
				}
			}

			update_post_meta( $calculator->ID, 'stm-fields', $fields );
		}
	}

	public static function ccb_checkbox_box_style() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) && str_contains( $field['alias'], 'radio_with_img' ) && 'default' === $field['styles']['style'] ) {
					$field['styles']['box_style'] = 'horizontal';
					$fields[ $key ]               = $field;
				}
			}

			update_post_meta( $calculator->ID, 'stm-fields', $fields );
		}
	}

	public static function ccb_convert_presets_into_theme() {
		$presets_idxes = array();
		$calculators   = self::get_calculators();
		$presets       = CCBPresetGenerator::get_static_preset_from_db();
		$themes        = CCBPresetGenerator::default_presets();
		$all_themes    = array_merge( $themes, $presets );

		$calc_preset_store = array();

		foreach ( $calculators as $calculator ) {
			$idx = get_post_meta( $calculator->ID, 'ccb_calc_preset_idx', true );

			if ( ! CCBPresetGenerator::preset_exist( $idx ) && isset( $presets[ $idx ]['desktop'] ) ) {
				$preset = $presets[ $idx ];
				$colors = $preset['desktop']['colors'];

				$key = self::theme_exist( $all_themes, $colors );
				if ( $key ) {
					unset( $presets[ $idx ] );
					CCBPresetGenerator::update_preset_key( $calculator->ID, sanitize_text_field( $key ) );
				} else {
					if ( ! in_array( intval( $idx ), $presets_idxes, true ) ) {
						$presets_idxes[] = intval( $idx );
					}

					if ( isset( $calc_preset_store[ $idx ] ) && is_array( $calc_preset_store[ $idx ] ) ) {
						$calc_preset_store[ $idx ][] = $calculator->ID;
					} else {
						$calc_preset_store[ $idx ] = array( $calculator->ID );
					}
				}
			} elseif ( CCBPresetGenerator::preset_exist( $idx ) ) {
				CCBPresetGenerator::update_preset_key( $calculator->ID, sanitize_text_field( $idx ) );
			} else {
				CCBPresetGenerator::update_preset_key( $calculator->ID );
			}
		}

		$new_themes  = CCBPresetGenerator::get_static_preset_from_db( true );
		$theme_count = count( $new_themes );

		if ( count( $presets ) > 0 ) {
			foreach ( $presets as $idx => $preset ) {
				if ( ! in_array( $idx, $presets_idxes, true ) ) {
					unset( $presets[ $idx ] );
				} elseif ( isset( $preset['desktop'] ) && isset( $calc_preset_store[ $idx ] ) ) {
					$new_theme                       = CCBPresetGenerator::generate_new_preset( $theme_count + 1, $preset );
					$new_themes[ $new_theme['key'] ] = $new_theme;
					$ids                             = $calc_preset_store[ $idx ];

					foreach ( $ids as $id ) {
						CCBPresetGenerator::update_preset_key( $id, $new_theme['key'] );
					}
					$theme_count++;
				}
			}
		}

		CCBPresetGenerator::update_presets( $new_themes );
	}

	public static function ccb_move_box_style_from_settings() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$presets       = CCBPresetGenerator::get_static_preset_from_db();
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );

			if ( isset( $calc_settings['general']['boxStyle'] ) ) {
				$box_style  = $calc_settings['general']['boxStyle'];
				$preset_key = get_post_meta( $calculator->ID, 'ccb_calc_preset_idx', true );

				if ( str_contains( $preset_key, 'saved' ) && isset( $presets[ $preset_key ] ) ) {
					$theme_box_style = $box_style;

					if ( isset( $presets[ $preset_key ]['data']['desktop']['layout']['box_style'] ) ) {
						$theme_box_style = $presets[ $preset_key ]['data']['desktop']['layout']['box_style'];
					}

					if ( $theme_box_style !== $box_style ) {
						$preset = $presets[ $preset_key ];

						$preset['data']['desktop']['layout']['box_style'] = $box_style;
						$new_theme                                        = CCBPresetGenerator::extend_preset( count( $presets ) + 1, $preset['data'] );
						$presets[ $new_theme['key'] ]                     = $new_theme;

						CCBPresetGenerator::update_preset_key( $calculator->ID, $new_theme['key'] );
					}
				}

				unset( $calc_settings['general']['boxStyle'] );
				update_option( 'stm_ccb_form_settings_' . $calculator->ID, $calc_settings );
			}

			CCBPresetGenerator::update_presets( $presets );
		}
	}

	public static function calculator_add_invoice_close_btn() {
		$general_settings = CCBSettingsData::get_calc_global_settings();
		if ( empty( $general_settings['invoice']['closeBtn'] ) ) {
			$general_settings['invoice']['closeBtn'] = 'Close';
		}

		update_option( 'ccb_general_settings', apply_filters( 'calc_update_options', $general_settings ) );
	}

	public static function ccb_change_font_weight_options() {
		$presets = CCBPresetGenerator::get_static_preset_from_db();
		$replace = array(
			'bold'   => 700,
			'bolder' => 700,
			'normal' => 500,
		);

		$valid   = array( 400, 500, 600, 700 );
		$devices = array( 'desktop', 'mobile' );
		$props   = array( 'header_font_weight', 'summary_header_font_weight', 'label_font_weight', 'description_font_weight', 'total_field_font_weight', 'total_font_weight', 'fields_btn_font_weight' );

		foreach ( $presets as $key => $preset ) {
			if ( isset( $preset['data']['desktop']['typography'] ) ) {
				foreach ( $devices as $device ) {
					$typography = $preset['data'][ $device ]['typography'];
					foreach ( $props as $prop ) {
						if ( ! empty( $typography[ $prop ] ) && isset( $replace[ $typography[ $prop ] ] ) ) {
							$typography[ $prop ] = $replace[ $typography[ $prop ] ];
						}

						if ( ! ( 'inherit' !== $typography[ $prop ] && is_numeric( $typography[ $prop ] ) && in_array( intval( $typography[ $prop ] ), $valid, true ) ) ) {
							$typography[ $prop ] = 500;
						}
					}

					$preset['data'][ $device ]['typography'] = $typography;
					$presets[ $key ]                         = $preset;
				}
			}
		}

		CCBPresetGenerator::update_presets( $presets );
	}

	public static function calculator_woo_products_by_product() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );

			if ( isset( $calc_settings['woo_products'] ) && ! isset( $calc_settings['woo_products']['by_category'] ) ) {
				$calc_settings['woo_products']['by_category'] = true;
				$calc_settings['woo_products']['by_product']  = false;
				$calc_settings['woo_products']['product_ids'] = array();

				update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $calc_settings ) );
			}
		}
	}

	public static function ccb_set_saved() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			update_post_meta( $calculator->ID, 'calc_saved', true );
		}
	}


	public static function ccb_added_payment_gateways() {
		$default_general_settings = CCBSettingsData::general_settings_data();

		if ( ! empty( $default_general_settings ) ) {
			$general_settings = CCBSettingsData::get_calc_global_settings();
			$update_data      = self::set_payment_gateway( $general_settings, $default_general_settings );
			update_option( 'ccb_general_settings', $update_data );
		}

		$calculators      = self::get_calculators();
		$default_settings = CCBSettingsData::settings_data();
		foreach ( $calculators as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );
			if ( ! empty( $calc_settings ) ) {
				if ( isset( $calc_settings['stripe']['use_in_all'] ) ) {
					unset( $calc_settings['stripe']['use_in_all'] );
				}
				$inner_update_data = self::set_payment_gateway( $calc_settings, $default_settings );

				update_option( 'stm_ccb_form_settings_' . $calculator->ID, $inner_update_data );
			}
		}
	}

	private static function set_payment_gateway( $settings, $default_settings ) {
		if ( ! isset( $settings['payment_gateway'] ) ) {
			$paypal_settings = $settings['paypal'];
			$stripe_settings = $settings['stripe'];
			$payment_gateway = $default_settings['payment_gateway'];

			$payment_gateway['paypal'] = $paypal_settings;

			if ( isset( $stripe_settings['enable'] ) ) {
				$payment_gateway['cards']['enable']                            = $stripe_settings['enable'];
				$payment_gateway['cards']['card_payments']['stripe']['enable'] = $stripe_settings['enable'];
			}

			if ( isset( $stripe_settings['use_in_all'] ) ) {
				$payment_gateway['cards']['use_in_all']                        = $stripe_settings['use_in_all'];
				$payment_gateway['cards']['card_payments']['stripe']['enable'] = $stripe_settings['use_in_all'];
			}

			$payment_gateway['cards']['card_payments']['stripe']['secretKey']  = $stripe_settings['secretKey'];
			$payment_gateway['cards']['card_payments']['stripe']['publishKey'] = $stripe_settings['publishKey'];
			$payment_gateway['cards']['card_payments']['stripe']['currency']   = $stripe_settings['currency'];

			if ( isset( $paypal_settings['formulas'] ) ) {
				$payment_gateway['formulas'] = $paypal_settings['formulas'];
			}

			if ( isset( $stripe_settings['formulas'] ) ) {
				$payment_gateway['formulas'] = $stripe_settings['formulas'];
			}

			$settings['payment_gateway'] = $payment_gateway;
			return $settings;
		}

		return $settings;
	}

	public static function ccb_update_payment_type_enum() {
		global $wpdb;
		$payment_table = Payments::_table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `%1s` LIKE %s;', $payment_table, 'type' ) ) ) { // phpcs:ignore
			$wpdb->query(
				$wpdb->prepare(
					"ALTER TABLE `%1s` MODIFY COLUMN `type` ENUM('paypal', 'stripe', 'woocommerce', 'cash_payment', 'twoCheckout', 'razorpay', 'no_payments') NOT NULL DEFAULT 'no_payments';", // phpcs:ignore
					$payment_table
				)
			);
		}
	}

	/**
	 * 3.1.82
	 * Add Terms and Conditions default settings to general settings
	 */
	public static function ccb_general_settings_terms_and_conditions_update() {
		$general_settings = CCBSettingsData::get_calc_global_settings();

		if ( ! isset( $general_settings['form_fields']['terms_and_conditions'] ) ) {
			$general_settings['form_fields']['terms_use_in_all']                  = false;
			$general_settings['form_fields']['terms_and_conditions']['checkbox']  = false;
			$general_settings['form_fields']['terms_and_conditions']['text']      = 'By clicking this box, I agree to your';
			$general_settings['form_fields']['terms_and_conditions']['link']      = '';
			$general_settings['form_fields']['terms_and_conditions']['link_text'] = '';
			$general_settings['form_fields']['terms_and_conditions']['page_id']   = '';

			update_option( 'ccb_general_settings', apply_filters( 'calc_update_options', $general_settings ) );
		}
	}

	/**
	 * 3.1.82
	 * Add Terms and Conditions default settings to calculator settings
	 */
	public static function ccb_calculator_settings_terms_and_conditions_update() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );

			if ( ! isset( $calc_settings['formFields']['terms_and_conditions'] ) ) {
				$calc_settings['formFields']['accessTermsEmail']                  = false;
				$calc_settings['formFields']['terms_and_conditions']['checkbox']  = false;
				$calc_settings['formFields']['terms_and_conditions']['text']      = 'By clicking this box, I agree to your';
				$calc_settings['formFields']['terms_and_conditions']['link']      = '';
				$calc_settings['formFields']['terms_and_conditions']['link_text'] = '';
				$calc_settings['formFields']['terms_and_conditions']['page_id']   = '';

				$calc_settins['texts']['form_fields']['terms_and_conditions_field'] = 'Please, check out our terms and click on the checkbox';

				update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $calc_settings ) );
			}
		}
	}

	public static function ccb_update_total_sign_to_unit_measure() {
		$calculators      = self::get_calculators();
		$general_settings = CCBSettingsData::get_calc_global_settings();

		foreach ( $calculators as $calculator ) {
			$calc_settings     = CCBSettingsData::get_calc_single_settings( $calculator->ID );
			$currency_settings = $general_settings['currency']['use_in_all'] ? $general_settings['currency'] : $calc_settings['currency'];

			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );
			foreach ( $fields as $key => $field ) {
				if ( 'cost-total' === $field['_tag'] && isset( $field['totalSymbol'] ) && true === $field['totalSymbol'] ) {
					$fields[ $key ]['currency']                                     = $field['totalSymbolSign'];
					$fields[ $key ]['fieldCurrencySettings']['currency']            = $field['totalSymbolSign'];
					$fields[ $key ]['fieldCurrencySettings']['num_after_integer']   = $currency_settings['num_after_integer'];
					$fields[ $key ]['fieldCurrencySettings']['decimal_separator']   = $currency_settings['decimal_separator'];
					$fields[ $key ]['fieldCurrencySettings']['thousands_separator'] = $currency_settings['thousands_separator'];
					$fields[ $key ]['fieldCurrencySettings']['currencyPosition']    = $currency_settings['currencyPosition'];
					$fields[ $key ]['fieldCurrency']                                = true;
				}
			}

			update_post_meta( $calculator->ID, 'stm-fields', $fields );
		}
	}

	public static function ccb_add_discount() {
		global $wpdb;
		$order_table = Orders::_table();
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `%1s` LIKE %s;', $order_table, 'promocodes' ) ) ) { // phpcs:ignore
			$wpdb->query(
				$wpdb->prepare(
					"ALTER TABLE `%1s` ADD COLUMN promocodes longtext DEFAULT NULL;", // phpcs:ignore
					$order_table
				)
			);
		}

		\cBuilder\Classes\Database\Discounts::create_table();
		\cBuilder\Classes\Database\Promocodes::create_table();
		\cBuilder\Classes\Database\Condition::create_table();
	}

	public static function ccb_add_summary_view_to_image_checkbox_field() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields        = get_post_meta( $calculator->ID, 'stm-fields', true );
			$change_fields = array( 'checkbox_with_img' );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) ) {
					$field_name = preg_replace( '/_field_id.*/', '', $field['alias'] );
					if ( in_array( $field_name, $change_fields, true ) && ! isset( $field['summary_view'] ) ) {
						$field['summary_view'] = 'show_value';
					}
				}
				$fields[ $key ] = $field;
			}

			update_post_meta( $calculator->ID, 'stm-fields', (array) $fields );
		}
	}

	public static function ccb_add_summary_display() {
		$calculators     = self::get_calculators();
		$settings        = CCBSettingsData::settings_data();
		$summary_display = $settings['formFields']['summary_display'];

		foreach ( $calculators as $calculator ) {
			$calc_settings = get_option( 'stm_ccb_form_settings_' . $calculator->ID );
			if ( empty( $calc_settings['formFields']['summary_display'] ) ) {
				$calc_settings['formFields']['summary_display'] = $summary_display;
				update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $calc_settings ) );
			}
		}

		$static_general_data = CCBSettingsData::general_settings_data();
		$general_settings    = get_option( 'ccb_general_settings' );
		if ( empty( $general_settings['form_fields']['summary_display'] ) ) {
			$general_settings['form_fields']['summary_display'] = $static_general_data['form_fields']['summary_display'];
			update_option( 'ccb_general_settings', apply_filters( 'calc_update_options', $general_settings ) );
		}
	}

	public static function ccb_date_picker_multi_period() {
		$calculators = self::get_calculators();
		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) && preg_replace( '/_field_id.*/', '', $field['alias'] ) === 'datePicker' ) {
					if ( ! isset( $field['not_allowed_dates']['period'][0] ) ) {
						$field['not_allowed_dates']['period'] = array(
							$field['not_allowed_dates']['period'],
						);
					}

					$fields[ $key ] = $field;
				}
			}

			update_post_meta( $calculator->ID, 'stm-fields', (array) $fields );
		}
	}

	public static function ccb_total_field_hidden_calculate() {
		$calculators = self::get_calculators();
		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) && preg_replace( '/_field_id.*/', '', $field['alias'] ) === 'total' ) {
					if ( isset( $field['hidden'] ) && true === $field['hidden'] ) {
						$field['calculateHidden'] = true;
					}

					$fields[ $key ] = $field;
				}
			}

			update_post_meta( $calculator->ID, 'stm-fields', (array) $fields );
		}
	}

	public static function ccb_order_form_fields_database_tables_create() {
		global $wpdb;
		$orders_table = Orders::_table();

		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `%1s` LIKE %s;', $orders_table, 'form_id' ) ) ) { // phpcs:ignore
			Forms::create_table();
			FormFields::create_table();
			FormFieldsAttributes::create_table();

			// phpcs:disable
			$wpdb->query(
				$wpdb->prepare(
					"ALTER TABLE `%1s`
				ADD COLUMN form_id INT UNSIGNED NULL AFTER form_details;",
					$orders_table
				)
			);
			// phpcs:enable

			Forms::create_default_forms();
		}
	}

	public static function ccb_update_paypal_data() {
		$calculators = self::get_calculators();
		foreach ( $calculators as $calculator ) {
			$calc_settings = get_option( 'stm_ccb_form_settings_' . $calculator->ID );

			if ( empty( $calc_settings['payment_gateway']['paypal']['integration_type'] ) ) {
				$calc_settings['payment_gateway']['paypal']['integration_type'] = 'legacy';
				$calc_settings['payment_gateway']['paypal']['client_id']        = '';
				$calc_settings['payment_gateway']['paypal']['client_secret']    = '';

				if ( empty( $calc_settings['payment_gateway']['paypal']['currency_code'] ) ) {
					$calc_settings['payment_gateway']['paypal']['currency_code'] = 'USD';
				}

				if ( empty( $calc_settings['payment_gateway']['paypal']['paypal_mode'] ) ) {
					$calc_settings['payment_gateway']['paypal']['paypal_mode'] = 'sandbox';
				}

				if ( ! empty( $calc_settings['paypal'] ) ) {
					unset( $calc_settings['paypal'] );
				}

				if ( ! empty( $calc_settings['stripe'] ) ) {
					unset( $calc_settings['stripe'] );
				}

				update_option( 'stm_ccb_form_settings_' . sanitize_text_field( $calculator->ID ), apply_filters( 'stm_ccb_sanitize_array', $calc_settings ) );
			}
		}

		$general_settings = get_option( 'ccb_general_settings' );
		if ( empty( $general_settings['payment_gateway']['paypal']['integration_type'] ) ) {
			$general_settings['payment_gateway']['paypal']['integration_type'] = 'legacy';
			$general_settings['payment_gateway']['paypal']['client_id']        = '';
			$general_settings['payment_gateway']['paypal']['client_secret']    = '';

			if ( empty( $general_settings['payment_gateway']['paypal']['currency_code'] ) ) {
				$general_settings['payment_gateway']['paypal']['currency_code'] = 'USD';
			}

			if ( empty( $general_settings['payment_gateway']['paypal']['paypal_mode'] ) ) {
				$general_settings['payment_gateway']['paypal']['paypal_mode'] = 'sandbox';
			}

			if ( ! empty( $general_settings['paypal'] ) ) {
				unset( $general_settings['paypal'] );
			}

			if ( ! empty( $general_settings['stripe'] ) ) {
				unset( $general_settings['stripe'] );
			}

			update_option( 'ccb_general_settings', apply_filters( 'calc_update_options', $general_settings ) );
		}
	}

	public static function ccb_order_off_autoload_for_meta_values() {
		global $wpdb;

		$options = $wpdb->get_results( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'calc_meta_data_order_%' AND autoload != 'off'" );

		if ( 0 < count( $options ) ) {
			foreach ( $options as $option ) {
				$wpdb->update(
					$wpdb->options,
					array( 'autoload' => 'off' ),
					array( 'option_name' => $option->option_name )
				);
			}
		}
	}

	public static function ccb_new_pdf_manager_tool() {
		$general_settings = CCBSettingsData::get_calc_global_settings();

		if ( isset( $general_settings['invoice']['companyName'] ) ) {
			if ( ! empty( $general_settings['invoice']['use_in_all'] ) && isset( $invoice['companyLogo'] ) ) {
				$invoice  = $general_settings['invoice'];
				$template = CCBPdfManagerTemplates::ccb_get_template_by_key();

				$template['document']['sidebar']['layout'] = 'no_sidebar';

				$blocks  = array( 'brand_block', 'order_id_and_date', 'order_block' );
				$sort_id = 3;
				foreach ( $template['sections'] as $key => $section ) {
					if ( in_array( $key, $blocks, true ) ) {
						$block_sort_id = 1;
						$status        = true;
						if ( 'order_id_and_date' === $key ) {
							$block_sort_id                                        = 2;
							$template['sections'][ $key ]['design']['text_align'] = 'right';
						} elseif ( 'order_block' === $key ) {
							$block_sort_id = 3;
						}

						if ( 'brand_block' === $key ) {
							$status = false;
							if ( ! empty( $invoice['companyLogo'] ) ) {
								$status = true;

								$template['sections'][ $key ]['logo']['logo_image'] = $invoice['companyLogo'];
							}

							if ( ! empty( $invoice['companyName'] ) ) {
								$status = true;

								$template['sections'][ $key ]['name']['show_company_name'] = $invoice['companyName'];
							}

							if ( ! empty( $invoice['companyInfo'] ) ) {
								$status = true;

								$template['sections'][ $key ]['slogan']['show_slogan'] = $invoice['companyInfo'];
							}

							$template['sections'][ $key ]['design']['text_align'] = 'left';
						}

						$template['sections'][ $key ]['enable']  = $status;
						$template['sections'][ $key ]['sort_id'] = $block_sort_id;
					} else {
						$template['sections'][ $key ]['enable']   = false;
						$template['sections'][ $key ][ $sort_id ] = $sort_id;

						$sort_id++;
					}
				}

				$key             = CCBPdfManagerTemplates::ccb_get_new_id_for_pdf_template();
				$template['key'] = $key;
				CCBPdfManager::ccb_add_or_update_pdf_tempalte( $key, $template );
				CCBPdfManager::update_template_key( $key );
			}

			unset( $general_settings['invoice']['companyName'] );
			unset( $general_settings['invoice']['companyInfo'] );
			unset( $general_settings['invoice']['companyLogo'] );
			unset( $general_settings['invoice']['dateFormat'] );
		}
	}

	public static function ccb_update_pdf_data() {
		$pdf_templates = CCBPdfManager::ccb_get_pdf_template();

		foreach ( $pdf_templates as $key => $template ) {
			if ( isset( $template['sections']['company_block']['contacts'] ) && ! isset( $template['sections']['company_block']['contacts']['phone_label'] ) ) {
				$template['sections']['company_block']['contacts']['phone_label']     = 'Phone';
				$template['sections']['company_block']['contacts']['email_label']     = 'Email';
				$template['sections']['company_block']['contacts']['messenger_label'] = 'Messenger';
				$template['sections']['company_block']['contacts']['site_url_label']  = 'Site';
			}

			if ( ! isset( $template['sections']['order_block']['content']['show_grand_total'] ) ) {
				$template['sections']['order_block']['content']['show_grand_total'] = true;
			}

			if ( isset( $template['sections']['order_block']['content'] ) && ! isset( $template['sections']['order_block']['content']['show_heading'] ) ) {
				$template['sections']['order_block']['content']['show_heading']  = true;
				$template['sections']['order_block']['content']['heading_name']  = 'Name';
				$template['sections']['order_block']['content']['heading_unit']  = 'Option/Unit';
				$template['sections']['order_block']['content']['heading_value'] = 'Total';
			}

			$pdf_templates[ $key ] = $template;
		}

		CCBPdfManager::update_tempaltes( $pdf_templates );
	}

	public static function ccb_remove_foreign_keys() {
		global $wpdb;

		$tables = array( Discounts::_table(), Forms::_table(), FormFields::_table() );
		foreach ( $tables as $table_name ) {
			// phpcs:disable
			$constraints = $wpdb->get_results(
				"SELECT TABLE_NAME, CONSTRAINT_NAME
						 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
						 WHERE REFERENCED_TABLE_NAME = '$table_name'
						 AND TABLE_SCHEMA = DATABASE();"
			);
			// phpcs:enable

			if ( ! is_array( $constraints ) ) {
				$constraints = array();
			}

			foreach ( $constraints as $constraint ) {
				$table           = $constraint->TABLE_NAME;
				$constraint_name = $constraint->CONSTRAINT_NAME;
				$wpdb->query("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint_name}"); // phpcs:ignore
			}
		}

		Promocodes::maybe_create_trigger();
		Condition::maybe_create_trigger();
		FormFields::maybe_create_trigger();
		FormFieldsAttributes::maybe_create_trigger();
	}

	public static function add_default_value_to_fields() {
		global $wpdb;

		$fields_table     = esc_sql( $wpdb->prefix . 'cc_form_fields' );
		$attributes_table = esc_sql( $wpdb->prefix . 'cc_form_fields_attributes' );

		$field_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM `$fields_table` WHERE type IN (%s, %s, %s)", //phpcs:ignore
				'checkbox',
				'dropdown',
				'radio'
			)
		);

		if ( empty( $field_ids ) ) {
			return;
		}

		foreach ( $field_ids as $field_id ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `$attributes_table` WHERE field_id = %d AND type = %s", //phpcs:ignore
					$field_id,
					'default_value'
				)
			);

			if ( ! $exists ) {
				$wpdb->insert(
					$attributes_table,
					array(
						'field_id'   => $field_id,
						'type'       => 'default_value',
						'text_data'  => '',
						'created_at' => current_time( 'mysql' ),
						'updated_at' => current_time( 'mysql' ),
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}
		}
	}

	public static function ccb_add_date_picker_field() {
		global $wpdb;
		$form_fields_table = FormFields::_table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `%1s` LIKE %s;', $form_fields_table, 'type' ) ) ) { // phpcs:ignore
			$wpdb->query(
				$wpdb->prepare(
					"ALTER TABLE `%1s` MODIFY COLUMN `type` ENUM('name', 'email', 'phone', 'input-textbox', 'textarea', 'number', 'dropdown', 'radio', 'checkbox', 'formatted-text', 'space', 'button', 'date-picker', 'time-picker') NOT NULL", // phpcs:ignore
					$form_fields_table
				)
			);
		}
	}

	public static function ccb_update_pdf_data_font_controls() {
		$pdf_templates = CCBPdfManager::ccb_get_pdf_template();

		foreach ( $pdf_templates as $key => $template ) {
			if ( isset( $template['sections']['top_text_block']['design'] ) && ! isset( $template['sections']['top_text_block']['design']['title_font_size'] ) ) {
				$template['sections']['top_text_block']['design']['title_font_size']    = 8;
				$template['sections']['top_text_block']['design']['title_color']        = '#111111';
				$template['sections']['top_text_block']['design']['title_color_status'] = false;
				$template['sections']['top_text_block']['design']['font_size']          = 8;
			}

			if ( isset( $template['sections']['order_id_and_date']['design'] ) && ! isset( $template['sections']['order_id_and_date']['design']['font_size'] ) ) {
				$template['sections']['order_id_and_date']['design']['font_size'] = 8;
			}

			if ( isset( $template['sections']['order_block']['design'] ) && ! isset( $template['sections']['order_block']['design']['title_font_size'] ) ) {
				$template['sections']['order_block']['design']['title_font_size'] = 8;
				$template['sections']['order_block']['design']['font_size']       = 8;
			}

			if ( isset( $template['sections']['footer_text']['design'] ) && ! isset( $template['sections']['footer_text']['design']['font_size'] ) ) {
				$template['sections']['footer_text']['design']['font_size'] = 8;
			}

			if ( isset( $template['sections']['company_block']['design'] ) && ! isset( $template['sections']['company_block']['design']['title_font_size'] ) ) {
				$template['sections']['company_block']['design']['title_font_size']    = 12;
				$template['sections']['company_block']['design']['title_color']        = '#111111';
				$template['sections']['company_block']['design']['title_color_status'] = false;
				$template['sections']['company_block']['design']['font_size']          = 8;
			}

			if ( isset( $template['sections']['customer_block']['design'] ) && ! isset( $template['sections']['customer_block']['design']['title_font_size'] ) ) {
				$template['sections']['customer_block']['design']['title_font_size']    = 12;
				$template['sections']['customer_block']['design']['title_color']        = '#111111';
				$template['sections']['customer_block']['design']['title_color_status'] = false;
				$template['sections']['customer_block']['design']['font_size']          = 8;
			}

			if ( isset( $template['sections']['additional_text_block']['design'] ) && ! isset( $template['sections']['additional_text_block']['design']['title_font_size'] ) ) {
				$template['sections']['additional_text_block']['design']['title_font_size']    = 12;
				$template['sections']['additional_text_block']['design']['title_color']        = '#111111';
				$template['sections']['additional_text_block']['design']['title_color_status'] = false;
				$template['sections']['additional_text_block']['design']['font_size']          = 8;
			}

			$pdf_templates[ $key ] = $template;
		}

		CCBPdfManager::update_tempaltes( $pdf_templates );
	}

	public static function ccb_delete_payments_table_constraints() {
		global $wpdb;
		$payment_table = Payments::_table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `%1s` LIKE %s;', $payment_table, 'currency' ) ) ) { // phpcs:ignore
			$wpdb->query(
				$wpdb->prepare(
					"ALTER TABLE `%1s` MODIFY COLUMN `currency` CHAR(20) DEFAULT '';", // phpcs:ignore
					$payment_table
				)
			);
		}
	}

	public static function ccb_add_pdf_border_style() {
		$pdf_templates = CCBPdfManager::ccb_get_pdf_template();

		foreach ( $pdf_templates as $key => $template ) {
			if ( ! isset( $template['document']['border']['border_style'] ) ) {
				$template['document']['border']['border_style'] = 'solid';
			}

			if ( ! isset( $template['sections']['order_block']['lines']['border_style'] ) ) {
				$template['sections']['order_block']['lines']['border_style'] = 'solid';
			}

			if ( ! isset( $template['sections']['order_block']['lines']['line_border_style'] ) ) {
				$template['sections']['order_block']['lines']['line_border_style'] = 'solid';
			}

			$pdf_templates[ $key ] = $template;
		}

		CCBPdfManager::update_tempaltes( $pdf_templates );
	}

	public static function ccb_maybe_create_orders_table() {
		global $wpdb;
		$orders_table = Orders::_table();
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `%1s` LIKE %s;', $orders_table, 'id' ) ) ) { // phpcs:ignore
			Orders::create_table();
		}
	}

	public static function ccb_get_old_header_color() {
		$global_settings  = get_option( 'ccb_general_settings', '' );
		$content_bg_value = $global_settings['email_templates']['content_bg']['value'];

		if ( ! isset( $global_settings['email_templates']['header_bg']['value'] ) ) {
			$global_settings['email_templates']['header_bg']['value'] = $content_bg_value;
			update_option( 'ccb_general_settings', $global_settings );
		}
	}

	public static function ccb_make_option_upload_from_url() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['alias'] ) && 'file_upload' === preg_replace( '/_field_id.*/', '', $field['alias'] ) ) {
					if ( ! isset( $field['uploadFromUrl'] ) ) {
						$field['uploadFromUrl'] = true;
					}

					$fields[ $key ] = $field;
				}
			}
			update_post_meta( $calculator->ID, 'stm-fields', (array) $fields );
		}
	}

	public static function ccb_add_field_disable_options() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			$field_names_with_options = array( 'radio', 'checkbox', 'toggle', 'dropDown', 'dropDown_with_img', 'radio_with_img', 'checkbox_with_img' );
			foreach ( $fields as $key => $field ) {
				$field_name = preg_replace( '/_field_id.*/', '', $field['alias'] );
				if ( in_array( $field_name, $field_names_with_options, true ) && ! isset( $field['disableOptions'] ) ) {
					$field['disableOptions'] = array();
					$field['hasNextTick']    = true;
					$field['nextTickCount']  = 0;
					$fields[ $key ]          = $field;
				}
			}

			update_post_meta( $calculator->ID, 'stm-fields', (array) $fields );
		}
	}

	/**
	 * 3.1.61
	 * Update "Loan Calculator" template in wp posts
	 * change "changed fields"
	 */
	public static function ccb_update_template_loan() {
		$templateName = 'Loan Calculator';

		$args = array(
			'post_type'   => 'cost-calc',
			'post_status' => array( 'draft' ),
			'title'       => $templateName,
		);

		if ( class_exists( 'Polylang' ) ) {
			$args['lang'] = '';
		}

		$calcTemplates = get_posts( $args );

		if ( count( $calcTemplates ) === 0 ) {
			return;
		}

		$newTemplateData = CCBCalculatorTemplates::get_template_by_name( $templateName );
		if ( ! isset( $newTemplateData ) ) {
			return;
		}

		if ( ! isset( $newTemplateData['ccb_fields'] ) || count( $newTemplateData['ccb_fields'] ) === 0 ) {
			return;
		}

		update_post_meta( $calcTemplates[0]->ID, 'stm-formula', (array) $newTemplateData['ccb_formula'] );
		update_post_meta( $calcTemplates[0]->ID, 'stm-fields', (array) $newTemplateData['ccb_fields'] );
		update_post_meta( $calcTemplates[0]->ID, 'stm-conditions', (array) $newTemplateData['ccb_conditions'] );
	}

	/**
	 * 3.1.64
	 * Update "Number of Decimals" in all single calculators
	 * change "changed fields"
	 */
	public static function ccb_update_total_number_of_decimals() {
		$calculators = self::get_calculators();

		foreach ( $calculators as $calculator ) {
			$fields = get_post_meta( $calculator->ID, 'stm-fields', true );

			foreach ( $fields as $key => $field ) {
				if ( 'cost-total' === $field['_tag'] && isset( $field['fieldCurrency'] ) && $field['fieldCurrency'] ) {
					$fields[ $key ]['fieldCurrencySettings']['num_after_integer'] = '' === $fields[ $key ]['fieldCurrencySettings']['num_after_integer'] ? '2' : min( 8, $fields[ $key ]['fieldCurrencySettings']['num_after_integer'] );
				}
			}
			update_post_meta( $calculator->ID, 'stm-fields', $fields );

			$formula = get_post_meta( $calculator->ID, 'stm-formula', true );
			foreach ( $formula as $index => $total ) {
				if ( isset( $total['fieldCurrency'] ) && $total['fieldCurrency'] ) {
					$formula[ $index ]['fieldCurrencySettings']['num_after_integer'] = '' === $formula[ $index ]['fieldCurrencySettings']['num_after_integer'] ? '2' : min( 8, $formula[ $index ]['fieldCurrencySettings']['num_after_integer'] );
				}
			}
			update_post_meta( $calculator->ID, 'stm-formula', $formula );
		}
	}

	public static function ccb_add_analytics_tables() {
		global $wpdb;
		$views_table = AnalyticsViews::_table();
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $views_table ) ) ) { // phpcs:ignore
			AnalyticsViews::create_table();
		}

		$interactions_table = AnalyticsInteractions::_table();
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $interactions_table ) ) ) { // phpcs:ignore
			AnalyticsInteractions::create_table();
		}
	}
}
