<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;

class OrdersCalculatorFields extends DataBaseModel {
	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id INT UNSIGNED NOT NULL,
			is_basic TINYINT(1) NOT NULL DEFAULT 0,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_order_id (order_id),
			INDEX idx_is_basic (is_basic)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_calculator_field( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) || ! isset( $data['sort_id'] ) || empty( $data['alias'] ) ) {
			return false;
		}

		$field_name = preg_replace( '/_field_id.*/', '', $data['alias'] );
		$is_basic   = 1;

		if ( in_array( $field_name, array( 'checkbox', 'checkbox_with_img', 'toggle', 'file_upload' ), true ) ) {
			$is_basic = 0;
		}

		if ( in_array( $field_name, array( 'geolocation' ), true ) && count( $data['options'] ) > 0 ) {
			$is_basic = 0;
		}

		$option_unit = ! empty( $data['option_unit'] ) ? $data['option_unit'] : '';
		if ( in_array( $field_name, array( 'dropDown', 'dropDown_with_img', 'radio', 'radio_with_img' ), true ) && ! empty( $data['summary_view'] ) && 'show_value' === $data['summary_view'] ) {
			$option = ! empty( $data['options'][0] ) ? $data['options'][0] : array();
			if ( ! empty( $option['label'] ) ) {
				$option_unit = $option['label'];
			}
		}

		$field_data = array(
			'order_id' => $order_id,
			'is_basic' => $is_basic,
		);

		$field_id = self::insert( $field_data );

		if ( ! empty( $field_id ) ) {
			$converted = $data['converted'];

			if ( 'file_upload' === $field_name && isset( $data['value'] ) ) {
				$converted = $data['value'];
			}

			$attrs_data = array(
				'field_id'       => $field_id,
				'sort_id'        => $data['sort_id'],
				'alias'          => $data['alias'],
				'label'          => $data['label'],
				'converted'      => $converted,
				'option_unit'    => $option_unit,
				'add_to_summary' => $data['add_to_summary'],
			);
			OrdersCalcFieldsAttrs::create_calc_field_attrs( $field_id, $attrs_data );

			if ( empty( $is_basic ) ) {
				$value = null;
				if ( ! empty( $data['temps'] ) || empty( $data['value'] ) ) {
					$value = json_encode( $data['temps'], JSON_UNESCAPED_UNICODE ); // phpcs:ignore
				}

				if ( empty( $data['temps'] ) && ! empty( $data['value'] ) && 'file_upload' !== $field_name ) {
					$options = $data['options'];
					$option  = null;

					foreach ( $options as $option ) {
						if ( ! empty( $option['optionValue'] ) ) {
							$d = explode( ',', $option['optionValue'][0] );

							if ( intval( $d[0] ) === intval( $data['value'] ) ) {
								$value = json_encode( array( $option['optionValue'] ), JSON_UNESCAPED_UNICODE ); // phpcs:ignore
							}
						}
					}
				}

				if ( 'file_upload' === $field_name && ! is_array( $value ) && '[]' === $value ) {
					$value = null;
				}

				$option_field_data = array(
					'field_id'     => $field_id,
					'options'      => is_string( $data['options'] ) ? $data['options'] : json_encode( $data['options'], JSON_UNESCAPED_UNICODE ), // phpcs:ignore
					'value'        => $value,
					'summary_view' => $data['summary_view'],
				);

				OrdersCalcFieldsMultiOptions::create_calc_field_multi_options( $field_id, $option_field_data );
			} else {
				$value = $data['value'];
				if ( is_array( $value ) ) {
					$value = json_encode( $value, JSON_UNESCAPED_UNICODE ); // phpcs:ignore
				}

				$basic_field_data = array(
					'field_id' => $field_id,
					'value'    => $value,
				);
				OrdersCalcBasicFields::create_calc_basic_field( $field_id, $basic_field_data );
			}
		}

		return $field_id;
	}

	public static function update_calculator_field( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		return self::update( $data, array( 'order_id' => $order_id ) );
	}

	public static function delete_calculator_field( $order_id ) {
		global $wpdb;

		if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'DELETE FROM %1$s WHERE order_id = %2$d',
			self::_table(),
			$order_id
		);
		// phpcs:enable
		return $wpdb->query( $sql ); // phpcs:ignore
	}

	public static function delete_calculator_field_by_id( $id ) {
		global $wpdb;

		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'DELETE FROM %1$s WHERE id = %2$d',
			self::_table(),
			$id
		);
		// phpcs:enable
		return $wpdb->query( $sql ); // phpcs:ignore
	}

	public static function get_calculator_field( $order_id ) {
		global $wpdb;
		if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT * FROM %1$s WHERE order_id = %2$d',
			self::_table(),
			$order_id
		);
		// phpcs:enable
		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function remove_fields( $order_id ) {
		global $wpdb;
		if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT id FROM %1$s WHERE order_id = %2$d',
			self::_table(),
			$order_id
		);
		// phpcs:enable
		$fields = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		if ( empty( $fields ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			OrdersCalcFieldsAttrs::delete_calc_field_attrs( $field['id'] );
			OrdersCalcBasicFields::delete_calc_basic_field( $field['id'] );
			OrdersCalcFieldsMultiOptions::delete_calc_field_multi_options( $field['id'] );
			self::delete_calculator_field_by_id( $field['id'] );
		}
	}
}
