<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;

class OrdersCalcFieldsAttrs extends DataBaseModel {
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
			sort_id INT UNSIGNED NOT NULL,
			alias TEXT DEFAULT '',
			label TEXT DEFAULT '',
			converted TEXT DEFAULT '',
			add_to_summary TINYINT(1) NOT NULL DEFAULT 0,
			option_unit TEXT DEFAULT '',
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_field_id (field_id)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_calc_field_attrs( $field_id, $data ) {
		if ( empty( $field_id ) || empty( $data ) ) {
			return false;
		}

		return self::insert( $data );
	}

	public static function delete_calc_field_attrs( $field_id ) {
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
}
