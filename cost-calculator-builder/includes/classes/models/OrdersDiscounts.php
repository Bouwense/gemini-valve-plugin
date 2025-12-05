<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class OrdersDiscounts extends DataBaseModel {
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
			field_id INT UNSIGNED NOT NULL,
			discount_view VARCHAR(255) NOT NULL,
			discount_title VARCHAR(255) NOT NULL,
			discount_type VARCHAR(255) NOT NULL,
			discount_amount DECIMAL(10,2) NOT NULL,
			discount_value VARCHAR(255) DEFAULT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_field_id (field_id)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_discount( $data ) {
		if ( empty( $data ) || empty( $data['field_id'] ) || empty( $data['discount_title'] ) || empty( $data['discount_type'] ) || empty( $data['discount_amount'] ) ) {
			return false;
		}

		return self::insert( $data );
	}

	public static function update_discount( $field_id, $data ) {
		if ( empty( $field_id ) || empty( $data ) ) {
			return false;
		}

		return self::update( $data, array( 'field_id' => $field_id ) );
	}

	public static function delete_discount( $field_id ) {
		global $wpdb;

		if ( empty( $field_id ) || ! is_numeric( $field_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'DELETE FROM %1$s WHERE field_id = %2$d',
			self::_table(),
			$field_id
		);
		// phpcs:enable
		return $wpdb->query( $sql ); // phpcs:ignore
	}

	public static function get_order_discounts( $field_id ) {
		global $wpdb;
		if ( empty( $field_id ) || ! is_numeric( $field_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT * FROM %1$s WHERE field_id = %2$d',
			self::_table(),
			$field_id
		);
		// phpcs:enable
		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}
}
