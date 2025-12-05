<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;
use cBuilder\Classes\Database\OrdersPayments;
use cBuilder\Classes\Database\CalcOrders;

class OrdersStatuses extends DataBaseModel {
	/**
	 * Create Table
	 */

	public static $default_colors = array(
		'pending'   => 'yellow',
		'rejected'  => 'red',
		'complete'  => 'green',
		'cancelled' => 'gray',
	);

	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			status_name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
			sort_id INT UNSIGNED NOT NULL,
			is_default TINYINT(1) NOT NULL DEFAULT 0,
			is_completed TINYINT(1) NOT NULL DEFAULT 0,
			color VARCHAR(255) NOT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_status( $status_name, $slug, $color, $sort_id, $args = array() ) {
		if ( empty( $status_name ) || empty( $slug ) ) {
			return false;
		}

		$data = array(
			'status_name' => $status_name,
			'slug'        => $slug,
			'color'       => $color,
			'sort_id'     => $sort_id,
		);

		if ( ! empty( $args['is_default'] ) ) {
			$data['is_default'] = $args['is_default'];
		}

		if ( ! empty( $args['is_completed'] ) ) {
			$data['is_completed'] = $args['is_completed'];
		}

		return self::insert( $data );
	}

	public static function update_status( $id, $data ) {
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return false;
		}

		$update_data = array();

		if ( ! empty( $data['status_name'] ) ) {
			$update_data['status_name'] = $data['status_name'];
		}

		if ( ! empty( $data['slug'] ) ) {
			$update_data['slug'] = $data['slug'];
		}

		if ( ! empty( $data['color'] ) ) {
			$update_data['color'] = $data['color'];
		} elseif ( ! empty( $data['slug'] ) && isset( self::$default_colors[ $data['slug'] ] ) ) {
			$update_data['color'] = self::$default_colors[ $data['slug'] ];
		} else {
			$update_data['color'] = 'gray';
		}

		if ( ! empty( $data['sort_id'] ) ) {
			$update_data['sort_id'] = $data['sort_id'];
		}

		return self::update( $update_data, array( 'id' => $id ) );
	}

	public static function get_status_by_id( $id ) {
		global $wpdb;

		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return array();
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT *
					FROM %1$s
					WHERE id = %2$s
			',
			self::_table(),
			intval( $id )
		);
		// phpcs:enable
		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function get_status_by_slug( $slug ) {
		global $wpdb;

		if ( empty( $slug ) ) {
			return array();
		}

		$table = self::_table();
		$sql   = $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s", $slug ); // phpcs:ignore

		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function get_all_statuses() {
		global $wpdb;

		$sql = "SELECT id, status_name, slug, color, is_default, is_completed, sort_id FROM " . self::_table(); // phpcs:ignore

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function delete_status_by_id( $id ) {
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return false;
		}

		$orders_status_ids = OrdersPayments::get_all_orders_payment_status_ids();
		if ( ! empty( $orders_status_ids ) && in_array( $id, $orders_status_ids, true ) ) {
			return false;
		}

		return self::delete( $id );
	}

	public static function get_completed_status() {
		global $wpdb;
		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT id FROM %1$s WHERE is_completed = 1',
			self::_table()
		);
		// phpcs:enable
		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function make_completed( $id ) {
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return false;
		}

		$completed_status = self::get_completed_status();
		if ( ! empty( $completed_status['id'] ) ) {
			self::update( array( 'is_completed' => 0 ), array( 'id' => $completed_status['id'] ) );
		}

		return self::update( array( 'is_completed' => 1 ), array( 'id' => $id ) );
	}

	public static function get_default_status() {
		global $wpdb;
		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT id FROM %1$s WHERE is_default = 1',
			self::_table()
		);
		// phpcs:enable
		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function make_default( $id ) {
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return false;
		}

		$default_status = self::get_default_status();
		if ( ! empty( $default_status['id'] ) ) {
			self::update( array( 'is_default' => 0 ), array( 'id' => $default_status['id'] ) );
		}

		return self::update( array( 'is_default' => 1 ), array( 'id' => $id ) );
	}

	public static function create_default_statuses() {
		if ( self::orders_statuses_count() > 0 ) {
			return;
		}

		// phpcs:disable
		$statuses = array(
			array( 'status_name' => 'Pending', 'slug' => 'pending', 'color' => 'yellow', 'sort_id' => 1 ),
			array( 'status_name' => 'Completed', 'slug' => 'complete', 'color' => 'green', 'sort_id' => 2 ),
			array( 'status_name' => 'Cancelled', 'slug' => 'cancelled', 'color' => 'gray', 'sort_id' => 3 ),
			array( 'status_name' => 'Rejected', 'slug' => 'rejected', 'color' => 'red', 'sort_id' => 4 ),
		);
		// phpcs:enable

		foreach ( $statuses as $status ) {
			self::create_status( $status['status_name'], $status['slug'], $status['color'], $status['sort_id'] );
		}

		$pending_status = self::get_status_by_slug( 'pending' );
		if ( ! empty( $pending_status['id'] ) ) {
			self::make_default( $pending_status['id'] );
		}

		$completed_status = self::get_status_by_slug( 'complete' );
		if ( ! empty( $completed_status['id'] ) ) {
			self::make_completed( $completed_status['id'] );
		}
	}

	public static function orders_statuses_count() {
		global $wpdb;

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM %1$s',
			self::_table()
		);
		// phpcs:enable
		return $wpdb->get_var( $sql ); // phpcs:ignore
	}

	public static function get_all_statuses_with_count( $search = null, $filter = null ) {
		global $wpdb;

		$filter_data = array();

		if ( ! empty( $filter ) ) {
			foreach ( $filter as $key => $value ) {
				if ( ! empty( $value ) ) {
					$filter_data[ $key ] = $value;
				}
			}
		}

		if ( ! empty( $search ) ) {
			foreach ( $search as $key => $value ) {
				if ( ! empty( $value ) ) {
					$filter_data[ $key ] = $value;
				}
			}
		}

		$where = array();
		$args  = array();

		if ( ! empty( $filter_data['calculator'] ) ) {
			$where[] = "LOWER(o.calc_title) LIKE %s"; // phpcs:ignore
			$args[]  = '%' . strtolower( $filter_data['calculator'] ) . '%';
		}

		if ( ! empty( $filter_data['email'] ) ) {
			$where[] = "LOWER(oef.value) LIKE %s"; // phpcs:ignore
			$args[]  = '%' . strtolower( $filter_data['email'] ) . '%';
		}

		if ( ! empty( $filter_data['name'] ) ) {
			$where[] = "LOWER(onf.value) LIKE %s"; // phpcs:ignore
			$args[]  = '%' . strtolower( $filter_data['name'] ) . '%';
		}

		if ( ! empty( $filter_data['id'] ) ) {
			$where[] = "p.order_id = %d"; // phpcs:ignore
			$args[]  = (int) $filter_data['id'];
		}

		if ( ! empty( $filter_data['payment'] ) ) {
			$where[] = "p.payment_type = %s"; // phpcs:ignore
			$args[]  = strtolower( $filter_data['payment'] );
		}

		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where );
		}

		// phpcs:disable
		$sql = "
		    SELECT s.slug, COALESCE(COUNT(*), 0) AS count 
			FROM ". OrdersPayments::_table() . " AS p 
			LEFT JOIN ". CalcOrders::_table() . " o ON p.order_id = o.id
			LEFT JOIN ". self::_table() . " s ON p.payment_status = s.id
			LEFT JOIN LATERAL (
				select
				od.value
				from " . OrdersFormsDetails::_table() . " od
				where p.order_id = od.order_id AND od.type = 'email'
				order by od.sort_id asc limit 1
			) oef ON 1=1
			LEFT JOIN LATERAL (
				select
				od.value
				from " . OrdersFormsDetails::_table() . " od
				where p.order_id = od.order_id AND od.type = 'name'
				order by od.sort_id asc limit 1
			) onf ON 1=1
			{$where_sql}
			GROUP BY s.slug
		";
		// phpcs:enable

		if ( ! empty( $args ) ) {
			$sql = $wpdb->prepare( $sql, ...$args ); // phpcs:ignore
		}

		$statues = self::get_all_statuses();
		$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

		foreach ( $statues as $idx => $status ) {
			$status['count'] = 0;
			foreach ( $results as $result ) {
				if ( $status['slug'] === $result['slug'] ) {
					$status['count'] = intval( $result['count'] );
					break;
				}
			}

			$statues[ $idx ] = $status;
		}

		return $statues;
	}

	public static function delete_status( $id ) {
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return false;
		}

		return self::delete( $id );
	}
}
