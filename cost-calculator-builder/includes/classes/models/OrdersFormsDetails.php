<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class OrdersFormsDetails extends DataBaseModel {
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
			sort_id INT UNSIGNED NOT NULL,
			type VARCHAR(50) DEFAULT NULL,
			label TEXT DEFAULT '',
			detail_id INT UNSIGNED NOT NULL,
			`value` TEXT DEFAULT '',
			is_primary TINYINT(1) NOT NULL DEFAULT 0,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_order_id (order_id),
			INDEX idx_order_type_sort (order_id, type, sort_id),
			INDEX idx_type (type)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_form_details( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) || ! isset( $data['sort_id'] ) ) {
			return false;
		}

		$data['order_id'] = $order_id;
		return self::insert( $data );
	}

	public static function update_form_details( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		return self::update( $data, array( 'order_id' => $order_id ) );
	}

	public static function delete_form_details( $order_id ) {
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

	public static function get_form_details( $order_id ) {
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
