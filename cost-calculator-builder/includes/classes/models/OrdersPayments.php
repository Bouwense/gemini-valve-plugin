<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;
use cBuilder\Classes\Database\OrdersStatuses;

class OrdersPayments extends DataBaseModel {

	public static $defaultType = 'no_payments';
	public static $typeList    = array( 'paypal', 'stripe', 'no_payments', 'twoCheckout', 'razorpay' );

	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS  {$table_name} (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id INT UNSIGNED NOT NULL,
			payment_type ENUM('paypal', 'stripe', 'woocommerce', 'cash_payment', 'twoCheckout', 'razorpay', 'no_payments') NOT NULL DEFAULT 'no_payments',
			payment_status INT UNSIGNED NOT NULL,
			total DECIMAL(10,2) DEFAULT 0.00,
			tax DECIMAL(10,2) DEFAULT 0.00,
			transaction VARCHAR(255) DEFAULT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			paid_at TIMESTAMP DEFAULT NULL,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_order_id (order_id),
			INDEX idx_payment_status (payment_status),
			INDEX idx_payment_type (payment_type)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_payment( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data['payment_status'] ) || empty( $data['payment_type'] ) ) {
			return array();
		}

		$data['order_id'] = $order_id;

		return self::insert( $data );
	}

	public static function update_payment( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		$data['updated_at'] = wp_date( 'Y-m-d H:i:s' );
		return self::update( $data, array( 'order_id' => $order_id ) );
	}

	public static function update_payment_status_by_order_id( $order_id, $status_id ) {
		if ( empty( $order_id ) || empty( $status_id ) ) {
			return false;
		}

		$data = array( 'payment_status' => $status_id );

		$completed_status = OrdersStatuses::get_completed_status();
		if ( ! empty( $completed_status ) && intval( $completed_status['id'] ) === intval( $status_id ) ) {
			$data['paid_at'] = wp_date( 'Y-m-d H:i:s' );
		}

		return self::update( $data, array( 'order_id' => $order_id ) );
	}

	public static function update_payment_status_by_order_ids( $order_ids, $payment_status ) {
		global $wpdb;
		if ( empty( $order_ids ) || ! is_array( $order_ids ) || empty( $payment_status ) ) {
			return false;
		}

		$data = array( 'payment_status' => $payment_status );

		$completed_status = OrdersStatuses::get_completed_status();
		if ( ! empty( $completed_status ) && is_numeric( $payment_status ) && intval( $completed_status['id'] ) === intval( $payment_status ) ) {
			$data['paid_at'] = wp_date( 'Y-m-d H:i:s' );
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'UPDATE %1$s SET payment_status = %2$d WHERE order_id IN (%3$s)',
			self::_table(),
			$payment_status,
			implode( ',', $order_ids )
		);
		// phpcs:enable

		return $wpdb->query( $sql ); // phpcs:ignore
	}

	public static function delete_payment_by_order_id( $order_id ) {
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

	public static function delete_payment_by_order_ids( $order_ids ) {
		if ( empty( $order_ids ) ) {
			return false;
		}

		return self::delete( array( 'order_id' => $order_ids ) );
	}

	public static function get_all_orders_payment_status_ids() {
		global $wpdb;
		$table_name = self::_table();
		return $wpdb->get_results( "SELECT DISTINCT payment_status FROM $table_name", ARRAY_A ); // phpcs:ignore
	}

	public static function get_all_orders_payment_status_ids_by_status_id( $status_id ) {
		global $wpdb;
		if ( empty( $status_id ) || ! is_numeric( $status_id ) ) {
			return false;
		}

		$table_name = self::_table();

		$sql = "SELECT DISTINCT order_id FROM $table_name WHERE payment_status = %d";
		return $wpdb->get_results( $wpdb->prepare( $sql, $status_id ), ARRAY_A ); // phpcs:ignore
	}

	public static function get_payment_by_order_id( $order_id ) {
		return self::get( 'order_id', $order_id );
	}

	public static function set_payment_default_status( $order_ids, $status_id ) {
		global $wpdb;
		$table_name = self::_table();

		if ( ! is_array( $order_ids ) || empty( $status_id ) || ! is_numeric( $status_id ) ) {
			return false;
		}

		$placeholders = implode( ',', $order_ids );
		return $wpdb->get_results( "UPDATE $table_name SET payment_status = $status_id WHERE order_id IN ($placeholders)", ARRAY_A ); // phpcs:ignore
	}

	public static function delete_payments( $order_id ) {
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
