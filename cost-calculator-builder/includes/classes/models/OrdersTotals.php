<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;

class OrdersTotals extends DataBaseModel {
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
			alias TEXT DEFAULT '',
			label TEXT DEFAULT '',
			original_value TEXT DEFAULT '',
			`value` TEXT DEFAULT '',
			is_primary TINYINT(1) NOT NULL DEFAULT 0,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_order_id (order_id),
			INDEX idx_is_primary (is_primary)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_totals_field( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		return self::insert( $data );
	}

	public static function update_totals_field( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		return self::update( $data, array( 'order_id' => $order_id ) );
	}

	public static function delete_totals_field( $order_id ) {
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

		return $wpdb->query( $sql ); // phpcs:ignore
		// phpcs:enable
	}

	public static function get_totals_field( $order_id ) {
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

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		// phpcs:enable
	}

	public static function delete_totals( $order_id ) {
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

	public static function delete_total_field_by_id( $id ) {
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

	public static function delete_totals_with_discounts( $order_id ) {
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

		$totals = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		if ( empty( $totals ) ) {
			return false;
		}

		foreach ( $totals as $total ) {
			OrdersDiscounts::delete_discount( $total['id'] );
			self::delete_total_field_by_id( $total['id'] );
		}

		return $wpdb->query( $sql ); // phpcs:ignore
	}
}
