<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class OrdersPromocodes extends DataBaseModel {
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
			promo_code VARCHAR(255) NOT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_order_id (order_id)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_promo( $order_id, $promo_code ) {
		if ( empty( $order_id ) || empty( $promo_code ) ) {
			return array();
		}

		// phpcs:disable
		$data = array(
			'order_id'    => $order_id,
			'promo_code'  => $promo_code,
		);
		// phpcs:enable
		return self::insert( $data );
	}

	public static function delete_promo_by_order_id( $order_id ) {
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

	public static function get_all_orders_promo_codes() {
		global $wpdb;
		$table_name = self::_table();
		return $wpdb->get_results( "SELECT DISTINCT promo_code FROM $table_name", ARRAY_A ); // phpcs:ignore
	}

	public static function get_promo_by_order_id( $order_id ) {
		global $wpdb;
		if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return array();
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT * FROM %1$s WHERE order_id = %d',
			self::_table(),
			$order_id
		);
		// phpcs:enable
		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function delete_promocodes( $order_id ) {
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
}
