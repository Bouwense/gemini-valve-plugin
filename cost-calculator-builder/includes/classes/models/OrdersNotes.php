<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;

class OrdersNotes extends DataBaseModel {
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
			content TEXT NOT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_order_id (order_id),
			INDEX idx_created_at (created_at)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_note( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data['content'] ) ) {
			return false;
		}
		$data['order_id'] = $order_id;
		return self::insert( $data );
	}
	public static function update_note( $note_id, $data ) {
		if ( empty( $note_id ) || empty( $data['content'] ) ) {
			return false;
		}

		return self::update( $data, array( 'id' => $note_id ) );
	}

	public static function delete_note_by_id( $note_id ) {
		global $wpdb;

		if ( empty( $note_id ) || ! is_numeric( $note_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'DELETE FROM %1$s WHERE id = %2$d',
			self::_table(),
			$note_id
		);
		// phpcs:enable
		return $wpdb->query( $sql ); // phpcs:ignore
	}

	public static function delete_note_by_order_id( $order_id ) {
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

	public static function get_note_by_id( $note_id ) {
		global $wpdb;
		if ( empty( $note_id ) || ! is_numeric( $note_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT * FROM %1$s WHERE id = %2$d ORDER BY created_at DESC',
			self::_table(),
			$note_id
		);
		// phpcs:enable
		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function get_all_notes_by_order_id( $order_id ) {
		global $wpdb;
		if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return false;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT * FROM %1$s WHERE order_id = %2$d ORDER BY created_at DESC',
			self::_table(),
			$order_id
		);
		// phpcs:enable
		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}
}
