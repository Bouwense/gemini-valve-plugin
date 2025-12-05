<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;

class OrdersCurrency extends DataBaseModel {
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
			number_of_decimals INT UNSIGNED NOT NULL,
			thousand_separator VARCHAR(10) NOT NULL,
			decimal_separator VARCHAR(10) NOT NULL,
			currency_position VARCHAR(255) NOT NULL,
			currency_sign VARCHAR(100) DEFAULT '',
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			UNIQUE INDEX idx_order_id (order_id)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_currency( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		$data['order_id'] = $order_id;
		return self::insert( $data );
	}

	public static function update_currency( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		return self::update( $data, array( 'order_id' => $order_id ) );
	}

	public static function delete_currency( $order_id ) {
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

	public static function get_order_currency( $order_id ) {
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
}
