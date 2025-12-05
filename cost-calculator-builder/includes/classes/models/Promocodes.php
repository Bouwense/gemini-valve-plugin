<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class Promocodes extends DataBaseModel {
	public static $primary_key  = 'promocode_id';
	public static $trigger_name = 'cascade_delete_promocodes';
	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            {$primary_key} INT UNSIGNED NOT NULL AUTO_INCREMENT,
            discount_id INT UNSIGNED NOT NULL,
            promocode_count INT NOT NULL,
            promocode TEXT NOT NULL,
            promocode_used INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL,
			updated_at TIMESTAMP NOT NULL,
            PRIMARY KEY ({$primary_key}),
            INDEX `idx_promocode_count` (`promocode_count`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_poromo( $promo_data ) {
		if ( ! empty( $promo_data['promocode_count'] ) && ! empty( $promo_data['promocode'] ) ) {
			self::insert( $promo_data );
			return self::insert_id();
		}
	}

	public static function delete_poromo( $discount_id ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %1$s WHERE discount_id = %2$s', self::_table(), $discount_id );
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	public static function delete_all_poromo() {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %1$s', self::_table() );
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	public static function update_discount_condition( $data, $id ) {
		global $wpdb;
		$sql       = sprintf( 'SELECT %1$s.* FROM %1$s WHERE %1$s.promocode_id = %%d', self::_table() );
		$promocode = $wpdb->get_row( $wpdb->prepare( $sql, intval( $id ) ), ARRAY_A ); // phpcs:ignore

		$new_promocode               = array_replace( $promocode, array_intersect_key( $data, $promocode ) );
		$new_promocode['updated_at'] = wp_date( 'Y-m-d H:i:s' );
		self::update( $new_promocode, array( 'promocode_id' => $id ) );
	}

	private static function trigger_exists() {
		global $wpdb;
		$trigger_name = self::$trigger_name;
        // phpcs:disable
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
						FROM INFORMATION_SCHEMA.TRIGGERS 
						WHERE TRIGGER_NAME = %s
						AND TRIGGER_SCHEMA = DATABASE();",
				$trigger_name
			)
		);
        // phpcs:enable
	}

	private static function create_trigger() {
		global $wpdb;
		$trigger_name    = self::$trigger_name;
		$table_name      = self::_table();
		$discounts_table = Discounts::_table();

		// phpcs:disable
		$wpdb->query(
			"CREATE TRIGGER $trigger_name
					AFTER DELETE ON $discounts_table
					FOR EACH ROW
					BEGIN
						DELETE FROM $table_name WHERE discount_id = OLD.discount_id;
					END;"
		);
		// phpcs:enable
	}

	public static function maybe_create_trigger() {
		if ( ! self::trigger_exists() ) {
			self::create_trigger();
		}
	}
}
