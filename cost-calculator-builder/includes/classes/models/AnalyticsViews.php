<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;

class AnalyticsViews extends DataBaseModel {
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			{$primary_key} INT UNSIGNED NOT NULL AUTO_INCREMENT,
            calc_id INT UNSIGNED NOT NULL,
            ip_address VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );

	}

	public static function create_calculator_views( $calc_id, $ip_address ) {
		if ( ! is_numeric( $calc_id ) || ! ccb_is_valid_ip( $ip_address ) ) {
			return false;
		}

		$data = array(
			'calc_id'    => $calc_id,
			'ip_address' => $ip_address,
		);

		self::insert( $data );

		return true;
	}
}
