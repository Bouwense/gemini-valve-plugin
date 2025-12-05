<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;
use cBuilder\Classes\Database\OrdersFormsDetails;
use cBuilder\Classes\Database\OrdersCurrency;
use cBuilder\Classes\Database\OrdersDiscounts;
use cBuilder\Classes\Database\OrdersPayments;
use cBuilder\Classes\Database\OrdersPromocodes;
use cBuilder\Classes\Database\OrdersStatuses;
use cBuilder\Classes\Database\OrdersCalculatorFields;
use cBuilder\Classes\Database\OrdersCalcFieldsAttrs;
use cBuilder\Classes\Database\OrdersCalcBasicFields;
use cBuilder\Classes\Database\OrdersCalcFieldsMultiOptions;
use cBuilder\Classes\Database\OrdersTotals;
use cBuilder\Classes\Database\OrdersNotes;

class CalcOrders extends DataBaseModel {
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
			old_order_id INT UNSIGNED DEFAULT NULL,
			calc_id  INT UNSIGNED NOT NULL,
			calc_title TEXT DEFAULT NULL,
			total_amount DECIMAL(10, 2) NOT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key}),
			INDEX idx_calc_id (calc_id),
			INDEX idx_created_at (created_at),
			INDEX idx_total_amount (total_amount)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	/**
	 * Create Order with payment
	 */
	public static function create_order( $data ) {
		$order_data = $data['order_data'];

		if ( ! empty( $data['created_at'] ) ) {
			$order_data['created_at'] = wp_date( 'Y-m-d H:i:s', strtotime( $data['created_at'] ) );
		}

		self::insert( $order_data );
		$order_id = self::insert_id();

		if ( ! empty( $data['payment_details'] ) ) {
			if ( ! empty( $data['payment_details']['order_id'] ) ) {
				$data['payment_details']['order_id'] = $order_id;

				if ( ! empty( $data['payment_details']['created_at'] ) ) {
					$data['payment_details']['created_at'] = wp_date( 'Y-m-d H:i:s', strtotime( $data['payment_details']['created_at'] ) );
				}
			}
			OrdersPayments::create_payment( $order_id, $data['payment_details'] );
		}

		if ( ! empty( $data['form_details'] ) ) {
			$has_primary  = false;
			$form_details = array();
			foreach ( $data['form_details'] as $key => $form_detail ) {
				$form_details[] = array(
					'type'       => ! empty( $form_detail['type'] ) ? $form_detail['type'] : '',
					'sort_id'    => ! empty( $form_detail['sortId'] ) ? $form_detail['sortId'] : $key,
					'detail_id'  => ! empty( $form_detail['id'] ) ? $form_detail['id'] : $key,
					'label'      => ! empty( $form_detail['label'] ) ? $form_detail['label'] : '',
					'value'      => ! empty( $form_detail['value'] ) ? $form_detail['value'] : '',
					'is_primary' => ! empty( $form_detail['attributes']['primary'] ) ? 1 : 0,
				);

				if ( ! empty( $form_detail['attributes']['primary'] ) ) {
					$has_primary = true;
				}
			}

			if ( ! $has_primary ) {
				foreach ( $form_details as $key => $form_detail ) {
					if ( empty( $form_details[ $key ]['is_primary'] ) && ! empty( $form_detail['type'] ) && false !== strpos( $form_detail['type'], 'email' ) ) {
						$form_details[ $key ]['is_primary'] = 1;
						break;
					}
				}
			}

			foreach ( $form_details as $form_detail ) {
				if ( empty( $form_detail['type'] ) && ! empty( $form_detail['label'] ) ) {
					$form_detail['type']  = $form_detail['label'];
					$form_detail['label'] = ccb_slug_to_label( $form_detail['label'] );
				}

				if ( is_array( $form_detail['value'] ) ) {
					$form_detail['value'] = json_encode( $form_detail['value'], JSON_UNESCAPED_UNICODE ); // phpcs:ignore
				}
				OrdersFormsDetails::create_form_details( $order_id, $form_detail );
			}
		}

		if ( ! empty( $data['calculator_fields'] ) ) {
			foreach ( $data['calculator_fields'] as $idx => $calculator_field ) {
				$title = ! empty( $calculator_field['title'] ) ? $calculator_field['title'] : '';

				if ( ! empty( $calculator_field['groupTitle'] ) && ! empty( $calculator_field['alias'] ) && false !== strpos( $calculator_field['alias'], 'repeater' ) ) {
					$title = $calculator_field['groupTitle'];
				}

				$calculator_field_data = array(
					'sort_id'        => ! empty( $calculator_field['sort_id'] ) ? $calculator_field['idx'] : $idx,
					'alias'          => $calculator_field['alias'],
					'label'          => $title,
					'value'          => ! empty( $calculator_field['value'] ) ? $calculator_field['value'] : '0',
					'summary_view'   => ! empty( $calculator_field['summary_view'] ) ? $calculator_field['summary_view'] : '0',
					'options'        => ! empty( $calculator_field['options'] ) ? $calculator_field['options'] : array(),
					'temps'          => ! empty( $calculator_field['temps'] ) ? $calculator_field['temps'] : array(),
					'converted'      => ! empty( $calculator_field['originalValue'] ) ? $calculator_field['originalValue'] : '',
					'option_unit'    => ! empty( $calculator_field['option_unit'] ) ? $calculator_field['option_unit'] : '',
					'add_to_summary' => ! empty( $calculator_field['addToSummary'] ) ? $calculator_field['addToSummary'] : 0,
				);
				OrdersCalculatorFields::create_calculator_field( $order_id, $calculator_field_data );
			}
		}

		if ( ! empty( $data['currency'] ) ) {
			OrdersCurrency::create_currency( $order_id, $data['currency'] );
		}

		if ( ! empty( $data['promocodes'] ) ) {
			foreach ( $data['promocodes'] as $promocode ) {
				OrdersPromocodes::create_promo( $order_id, $promocode );
			}
		}

		if ( ! empty( $data['totals'] ) ) {
			foreach ( $data['totals'] as $idx => $total ) {
				$create_total_data = array(
					'order_id'       => $order_id,
					'sort_id'        => ! empty( $total['sort_id'] ) ? $total['sort_id'] : $idx,
					'alias'          => ! empty( $total['alias'] ) ? $total['alias'] : '',
					'label'          => ! empty( $total['label'] ) ? $total['label'] : '',
					'original_value' => ! empty( $total['originalValue'] ) ? $total['originalValue'] : '',
					'value'          => ! empty( $total['value'] ) ? $total['value'] : '',
					'is_primary'     => 1,
				);
				$field_id          = OrdersTotals::create_totals_field( $order_id, $create_total_data );

				if ( ! empty( $data['discounts'][ $total['alias'] ] ) ) {
					foreach ( $data['discounts'][ $total['alias'] ] as $discount ) {
						$discount['field_id'] = $field_id;
						OrdersDiscounts::create_discount( $discount );
					}
				}
			}
		}

		if ( ! empty( $data['other_totals'] ) ) {
			foreach ( $data['other_totals'] as $idx => $other_total ) {
				$create_total_data = array(
					'order_id'       => $order_id,
					'sort_id'        => ! empty( $other_total['sort_id'] ) ? $other_total['sort_id'] : $idx,
					'alias'          => ! empty( $other_total['alias'] ) ? $other_total['alias'] : '',
					'label'          => ! empty( $other_total['label'] ) ? $other_total['label'] : '',
					'value'          => ! empty( $other_total['value'] ) ? $other_total['value'] : '',
					'original_value' => ! empty( $other_total['originalValue'] ) ? $other_total['originalValue'] : '',
				);

				$field_id = OrdersTotals::create_totals_field( $order_id, $create_total_data );

				if ( ! empty( $data['discounts'][ $other_total['alias'] ] ) ) {
					foreach ( $data['discounts'][ $other_total['alias'] ] as $discount ) {
						$discount['field_id'] = $field_id;
						OrdersDiscounts::create_discount( $discount );
					}
				}
			}
		}

		return $order_id;
	}

	public static function get_order_full_data_by_ids( $ids ) {
		global $wpdb;

		if ( empty( $ids ) ) {
			return array();
		}

		// phpcs:disable
		$sql = "
			SELECT 
				o.id AS order_id,
				o.old_order_id,
				o.calc_id,
				o.total_amount,
				o.calc_title,
				o.created_at,

				ofd.id AS form_detail_id,
				ofd.sort_id AS form_detail_sort_id,
				ofd.type AS form_detail_type,
				ofd.label AS form_detail_label,
				ofd.detail_id AS form_detail_i_id,
				ofd.value AS form_detail_value,
				ofd.is_primary AS form_detail_is_primary,
				
				ofc.id AS calculator_field_id,
				ofc.is_basic AS calculator_field_is_basic,
				ofa.sort_id AS calculator_field_sort_id,
				ofa.alias AS calculator_field_alias,
				ofa.label AS calculator_field_label,
				ofa.converted AS calculator_field_converted,
				ofa.add_to_summary AS calculator_field_add_to_summary,
				ofa.option_unit AS calculator_field_option_unit,
				ofm.options AS calculator_field_options,
				ofm.summary_view AS calculator_field_summary_view,
				COALESCE(CASE WHEN ofc.is_basic = 1 THEN ofb.value ELSE ofm.value END, 0) AS calculator_field_value,
				
				oc.number_of_decimals,
				oc.thousand_separator,
				oc.decimal_separator,
				oc.currency_position,
				oc.currency_sign,

				ot.id AS totals_id,
				ot.sort_id AS totals_sort_id,
				ot.alias AS totals_alias,
				ot.label AS totals_label,
				ot.value AS totals_value,
				ot.is_primary AS totals_is_primary,
				ot.original_value AS totals_original_value,

				od.id AS discount_id,
				od.discount_view,
				od.discount_title,
				od.discount_type,
				od.discount_amount,
				od.discount_value,

				op.id AS promo_id,
				op.promo_code,

				p.id AS payment_id,
				p.payment_type,
            	p.tax AS payment_tax,
            	p.transaction,

				os.status_name AS payment_status,
				os.slug AS payment_status_slug,
				os.color AS status_color,

				no.id AS note_id,
				no.content AS note_content,
				no.created_at AS note_created_at
			FROM " . self::_table() . " o
			LEFT JOIN " . OrdersFormsDetails::_table() . " ofd ON o.id = ofd.order_id
			LEFT JOIN " . OrdersCalculatorFields::_table() . " ofc ON o.id = ofc.order_id
			LEFT JOIN " . OrdersCalcFieldsAttrs::_table() . " ofa ON ofa.field_id = ofc.id
			LEFT JOIN " . OrdersCalcBasicFields::_table() . " ofb ON ofb.field_id = ofc.id
			LEFT JOIN " . OrdersCalcFieldsMultiOptions::_table() . " ofm ON ofm.field_id = ofc.id
			LEFT JOIN " . OrdersCurrency::_table() . " oc ON o.id = oc.order_id
			LEFT JOIN " . OrdersPromocodes::_table() . " op ON o.id = op.order_id
			LEFT JOIN " . OrdersPayments::_table() . " p ON o.id = p.order_id
			LEFT JOIN " . OrdersStatuses::_table() . " os ON p.payment_status = os.id
			LEFT JOIN " . OrdersTotals::_table() . " ot ON o.id = ot.order_id
			LEFT JOIN " . OrdersDiscounts::_table() . " od ON ot.id = od.field_id
			LEFT JOIN " . OrdersNotes::_table() . " no ON o.id = no.order_id
			WHERE o.id IN (" . implode( ',', $ids ) . ")
		";
		// phpcs:enable
		$result = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		return self::parse_order_data( $result ) ?? array();
	}

	public static function get_order_full_data_by_id( $order_id, $limit = 10, $offset = 0 ) {
		global $wpdb;

		if ( empty( $order_id ) ) {
			return array();
		}

		// phpcs:disable
		$sql = "
			SELECT 
				o.id AS order_id,
				o.old_order_id,
				o.calc_id,
				o.total_amount,
				o.calc_title,
				o.created_at,

				ofd.id AS form_detail_id,
				ofd.sort_id AS form_detail_sort_id,
				ofd.type AS form_detail_type,
				ofd.label AS form_detail_label,
				ofd.detail_id AS form_detail_i_id,
				ofd.value AS form_detail_value,
				ofd.is_primary AS form_detail_is_primary,
				
				ofc.id AS calculator_field_id,
				ofc.is_basic AS calculator_field_is_basic,
				ofa.sort_id AS calculator_field_sort_id,
				ofa.alias AS calculator_field_alias,
				ofa.label AS calculator_field_label,
				ofa.converted AS calculator_field_converted,
				ofa.add_to_summary AS calculator_field_add_to_summary,
				ofa.option_unit AS calculator_field_option_unit,
				ofm.options AS calculator_field_options,
				ofm.summary_view AS calculator_field_summary_view,
				COALESCE(CASE WHEN ofc.is_basic = 1 THEN ofb.value ELSE ofm.value END, 0) AS calculator_field_value,
				
				oc.number_of_decimals,
				oc.thousand_separator,
				oc.decimal_separator,
				oc.currency_position,
				oc.currency_sign,

				ot.id AS totals_id,
				ot.sort_id AS totals_sort_id,
				ot.alias AS totals_alias,
				ot.label AS totals_label,
				ot.value AS totals_value,
				ot.is_primary AS totals_is_primary,
				ot.original_value AS totals_original_value,

				od.id AS discount_id,
				od.discount_view,
				od.discount_title,
				od.discount_type,
				od.discount_amount,
				od.discount_value,

				op.id AS promo_id,
				op.promo_code,

				p.id AS payment_id,
				p.payment_type,
            	p.tax AS payment_tax,
            	p.transaction,

				os.status_name AS payment_status,
				os.slug AS payment_status_slug,
				os.color AS status_color,

				no.id AS note_id,
				no.content AS note_content,
				no.created_at AS note_created_at
			FROM " . self::_table() . " o
			LEFT JOIN " . OrdersFormsDetails::_table() . " ofd ON o.id = ofd.order_id
			LEFT JOIN " . OrdersCalculatorFields::_table() . " ofc ON o.id = ofc.order_id
			LEFT JOIN " . OrdersCalcFieldsAttrs::_table() . " ofa ON ofa.field_id = ofc.id
			LEFT JOIN " . OrdersCalcBasicFields::_table() . " ofb ON ofb.field_id = ofc.id
			LEFT JOIN " . OrdersCalcFieldsMultiOptions::_table() . " ofm ON ofm.field_id = ofc.id
			LEFT JOIN " . OrdersCurrency::_table() . " oc ON o.id = oc.order_id
			LEFT JOIN " . OrdersPromocodes::_table() . " op ON o.id = op.order_id
			LEFT JOIN " . OrdersPayments::_table() . " p ON o.id = p.order_id
			LEFT JOIN " . OrdersStatuses::_table() . " os ON p.payment_status = os.id
			LEFT JOIN " . OrdersTotals::_table() . " ot ON o.id = ot.order_id
			LEFT JOIN " . OrdersDiscounts::_table() . " od ON ot.id = od.field_id
			LEFT JOIN " . OrdersNotes::_table() . " no ON o.id = no.order_id
			WHERE o.id = %d
		";
		// phpcs:enable
		$result = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ), ARRAY_A ); // phpcs:ignore
		$result = self::parse_order_data( $result );

		if ( is_array( $result ) && ! empty( $result ) ) {
			return $result[0];
		}

		return array();
	}

	public static function orders_count( $search = array(), $filter = null ) {
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

		if ( ! empty( $filter_data['status'] ) && ! is_array( $filter_data['status'] ) ) {
			$where[] = 'p.payment_status = %d';
			$args[]  = (int) $filter_data['status'];
		}

		if ( ! empty( $filter_data['calculator'] ) ) {
			$where[] = 'LOWER(o.calc_title) LIKE %s';
			$args[]  = '%' . strtolower( $filter_data['calculator'] ) . '%';
		}

		if ( ! empty( $filter_data['email'] ) ) {
			$where[] = 'LOWER(oef.value) LIKE %s';
			$args[]  = '%' . strtolower( $filter_data['email'] ) . '%';
		}

		if ( ! empty( $filter_data['name'] ) ) {
			$where[] = 'LOWER(onf.value) LIKE %s';
			$args[]  = '%' . strtolower( $filter_data['name'] ) . '%';
		}

		if ( ! empty( $filter_data['payment'] ) ) {
			$where[] = 'p.payment_type = %s';
			$args[]  = strtolower( $filter_data['payment'] );
		}

		if ( ! empty( $filter_data['id'] ) ) {
			$where[] = 'o.id = %d';
			$args[]  = (int) $filter_data['id'];
		}

		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where );
		}

		if ( ! empty( $filter_data['status'] ) && is_array( $filter_data['status'] ) ) {
			if ( empty( $where_sql ) ) {
				$where_sql = 'WHERE ';
			} else {
				$where_sql .= ' AND ';
			}
			$where_sql .= 'p.payment_status IN ( ' . implode( ',', $filter_data['status'] ) . ' )';
		}

		// phpcs:disable
		$sql = "
			SELECT COUNT(DISTINCT o.id) FROM " . self::_table() . " o
			LEFT JOIN " . OrdersPayments::_table() . " p ON o.id = p.order_id
			LEFT JOIN (
				SELECT 
					order_id,
					MAX(CASE WHEN type LIKE '%email%' THEN value END) as value
				FROM " . OrdersFormsDetails::_table() . "
				WHERE type LIKE '%email%'
				GROUP BY order_id
			) oef ON o.id = oef.order_id
			LEFT JOIN (
				SELECT 
					order_id,
					MAX(CASE WHEN type LIKE '%name%' THEN value END) as value
				FROM " . OrdersFormsDetails::_table() . "
				WHERE type LIKE '%name%'
				GROUP BY order_id
			) onf ON o.id = onf.order_id
			{$where_sql}
		";
		// phpcs:enable

		if ( ! empty( $args ) ) {
			$sql = $wpdb->prepare( $sql, ...$args ); // phpcs:ignore
		}

		return $wpdb->get_var( $sql ); // phpcs:ignore
	}

	public static function get_order_full_data( $limit = 10, $offset = 0, $search = array(), $sort = array(), $filter = null ) {
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

		if ( ! empty( $limit ) ) {
			$limit = (int) $limit;
		}

		if ( ! empty( $offset ) ) {
			$offset = (int) $offset;
		}

		$where = array();
		$args  = array();

		if ( ! empty( $filter_data['status'] ) && ! is_array( $filter_data['status'] ) ) {
			$where[] = 'p.payment_status = %d';
			$args[]  = (int) $filter_data['status'];
		}

		if ( ! empty( $filter_data['calculator'] ) ) {
			$where[] = 'LOWER(o.calc_title) LIKE %s';
			$args[]  = '%' . strtolower( $filter_data['calculator'] ) . '%';
		}

		if ( ! empty( $filter_data['email'] ) ) {
			$where[] = 'LOWER(oef.value) LIKE %s';
			$args[]  = '%' . strtolower( $filter_data['email'] ) . '%';
		}

		if ( ! empty( $filter_data['name'] ) ) {
			$where[] = 'LOWER(onf.value) LIKE %s';
			$args[]  = '%' . strtolower( $filter_data['name'] ) . '%';
		}

		if ( ! empty( $filter_data['payment'] ) ) {
			$where[] = 'p.payment_type = %s';
			$args[]  = strtolower( $filter_data['payment'] );
		}

		if ( ! empty( $filter_data['id'] ) ) {
			$where[] = 'o.id = %d';
			$args[]  = (int) $filter_data['id'];
		}

		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where );
		}

		if ( ! empty( $filter_data['status'] ) && is_array( $filter_data['status'] ) ) {
			if ( empty( $where_sql ) ) {
				$where_sql = 'WHERE ';
			} else {
				$where_sql .= ' AND ';
			}
			$where_sql .= 'p.payment_status IN ( ' . implode( ',', $filter_data['status'] ) . ' )';
		}

		$args[] = (int) $limit;
		$args[] = (int) $offset;

		$sort_value = '';

		if ( ! empty( $sort['id'] ) && in_array( strtolower( $sort['id'] ), array( 'asc', 'desc' ), true ) ) {
			$sort_value = ' o.id ' . $sort['id'];
		} elseif ( ! empty( $sort['amount'] ) && in_array( strtolower( $sort['amount'] ), array( 'asc', 'desc' ), true ) ) {
			$sort_value = ' o.total_amount ' . $sort['amount'];
		}

		// phpcs:disable
		$sql = "
			SELECT DISTINCT o.id
			FROM " . self::_table() . " o
			INNER JOIN " . OrdersPayments::_table() . " p ON o.id = p.order_id
			LEFT JOIN (
				SELECT 
					order_id,
					MAX(CASE WHEN type LIKE '%email%' THEN value END) as value
				FROM " . OrdersFormsDetails::_table() . "
				WHERE type LIKE '%email%'
				GROUP BY order_id
			) oef ON o.id = oef.order_id
			LEFT JOIN (
				SELECT 
					order_id,
					MAX(CASE WHEN type LIKE '%name%' THEN value END) as value
				FROM " . OrdersFormsDetails::_table() . "
				WHERE type LIKE '%name%'
				GROUP BY order_id
			) onf ON o.id = onf.order_id
			{$where_sql}
			ORDER BY {$sort_value}
			LIMIT %d OFFSET %d
		";

		$order_ids = $wpdb->get_col( $wpdb->prepare( $sql, ...$args ) );
		// phpcs:enable

		if ( empty( $order_ids ) ) {
			return array();
		}

		// phpcs:disable
		$sql = "
			SELECT 
				o.id AS order_id,
				o.old_order_id,
				o.calc_id,
				o.total_amount,
				o.calc_title,
				
				ofd.id AS form_detail_id,
				ofd.sort_id AS form_detail_sort_id,
				ofd.type AS form_detail_type,
				ofd.label AS form_detail_label,
				ofd.detail_id AS form_detail_i_id,
				ofd.value AS form_detail_value,
				ofd.is_primary AS form_detail_is_primary,
				
				ofc.id AS calculator_field_id,
				ofc.is_basic AS calculator_field_is_basic,
				ofa.sort_id AS calculator_field_sort_id,
				ofa.alias AS calculator_field_alias,
				ofa.label AS calculator_field_label,
				ofa.converted AS calculator_field_converted,
				ofa.add_to_summary AS calculator_field_add_to_summary,
				ofa.option_unit AS calculator_field_option_unit,
				ofm.options AS calculator_field_options,
				ofm.summary_view AS calculator_field_summary_view,
				CASE WHEN ofc.is_basic = 1 THEN ofb.value ELSE ofm.value END AS calculator_field_value,
				
				oc.number_of_decimals,
				oc.thousand_separator,
				oc.decimal_separator,
				oc.currency_position,
				oc.currency_sign,

				ot.id AS totals_id,
				ot.sort_id AS totals_sort_id,
				ot.alias AS totals_alias,
				ot.label AS totals_label,
				ot.value AS totals_value,
				ot.is_primary AS totals_is_primary,
				ot.original_value AS totals_original_value,

				od.id AS discount_id,
				od.discount_view,
				od.discount_title,
				od.discount_type,
				od.discount_amount,
				od.discount_value,

				op.id AS promo_id,
				op.promo_code,

				p.id AS payment_id,
				p.payment_type,

				os.status_name AS payment_status,
				os.slug AS payment_status_slug,
				os.color AS status_color,

				o.created_at,

				no.id AS note_id,
				no.content AS note_content,
				no.created_at AS note_created_at
			FROM " . self::_table() . " o
			LEFT JOIN " . OrdersFormsDetails::_table() . " ofd ON o.id = ofd.order_id
			LEFT JOIN " . OrdersCalculatorFields::_table() . " ofc ON o.id = ofc.order_id
			LEFT JOIN " . OrdersCalcFieldsAttrs::_table() . " ofa ON ofa.field_id = ofc.id
			LEFT JOIN " . OrdersCalcBasicFields::_table() . " ofb ON ofb.field_id = ofc.id
			LEFT JOIN " . OrdersCalcFieldsMultiOptions::_table() . " ofm ON ofm.field_id = ofc.id
			LEFT JOIN " . OrdersCurrency::_table() . " oc ON o.id = oc.order_id
			LEFT JOIN " . OrdersPromocodes::_table() . " op ON o.id = op.order_id
			LEFT JOIN " . OrdersPayments::_table() . " p ON o.id = p.order_id
			LEFT JOIN " . OrdersStatuses::_table() . " os ON p.payment_status = os.id
			LEFT JOIN " . OrdersTotals::_table() . " ot ON o.id = ot.order_id
			LEFT JOIN " . OrdersDiscounts::_table() . " od ON ot.id = od.field_id
			LEFT JOIN " . OrdersNotes::_table() . " no ON o.id = no.order_id
			WHERE o.id IN (" . implode( ',', $order_ids ) . ")
		";
		// phpcs:enable

		if ( ! empty( $filter_data['status'] ) ) {
			if ( ! is_array( $filter_data['status'] ) ) {
				$sql .= ' AND os.id = ' . $filter_data['status'];
			} else {
				$sql .= ' AND os.id IN ( ' . implode( ',', $filter_data['status'] ) . ' )';
			}
		}

		if ( ! empty( $sort_value ) ) {
			$sql .= ' ORDER BY ' . $sort_value;
		}

		$result = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		return self::parse_order_data( $result );
	}

	private static function parse_order_data( $orders ) {
		$order_data = array();

		$payment_labels = array(
			'woocommerce'  => __( 'Woocommerce', 'cost-calculator-builder' ),
			'razorpay'     => __( 'Razorpay', 'cost-calculator-builder' ),
			'no_payments'  => __( 'No Payment', 'cost-calculator-builder' ),
			'cash_payment' => __( 'Cash Payment', 'cost-calculator-builder' ),
			'paypal'       => __( 'PayPal', 'cost-calculator-builder' ),
			'stripe'       => __( 'Stripe', 'cost-calculator-builder' ),
		);

		foreach ( $orders as $order ) {
			$order_id = $order['order_id'];

			if ( ! isset( $order_data[ $order_id ] ) ) {
				$order_data[ $order_id ] = array(
					'order_id'            => $order_id,
					'calc_id'             => $order['calc_id'],
					'calc_title'          => $order['calc_title'],
					'total_amount'        => $order['total_amount'],
					'order_form_details'  => array(),
					'calculator_fields'   => array(),
					'currency'            => array(),
					'discounts'           => array(),
					'promocodes'          => array(),
					'notes'               => array(),
					'payment_status'      => $order['payment_status'],
					'payment_status_slug' => $order['payment_status_slug'],
					'status_color'        => $order['status_color'],
					'payment_type'        => $order['payment_type'],
					'payment_type_label'  => ! empty( $payment_labels[ $order['payment_type'] ] ) ? $payment_labels[ $order['payment_type'] ] : __( 'No Payment', 'cost-calculator-builder' ),
					'client_email'        => null,
					'client_name'         => null,
					'created_at'          => date_i18n( 'd.m.Y H:i', strtotime( $order['created_at'] ) ),
					'formatted_date'      => date_i18n( 'd M, h:i a', strtotime( $order['created_at'] ) ),
					'totals'              => array(),
					'other_totals'        => array(),
				);
			}

			if ( ! empty( $order['note_id'] ) && empty( $order_data[ $order_id ]['notes'][ $order['note_id'] ] ) ) {
				$order_data[ $order_id ]['notes'][ $order['note_id'] ] = array(
					'id'         => $order['note_id'],
					'content'    => wp_kses_post( $order['note_content'] ),
					'created_at' => date_i18n( 'd.m.Y H:i', strtotime( $order['note_created_at'] ) ),
				);
			}

			if ( ! empty( $order['form_detail_id'] ) ) {
				if ( empty( $order_data[ $order_id ]['order_form_details'][ $order['form_detail_id'] ] ) ) {
					$order_data[ $order_id ]['order_form_details'][ $order['form_detail_id'] ] = array(
						'id'         => $order['form_detail_id'],
						'sort_id'    => $order['form_detail_sort_id'],
						'type'       => $order['form_detail_type'],
						'label'      => $order['form_detail_label'],
						'value'      => $order['form_detail_value'],
						'is_primary' => $order['form_detail_is_primary'],
					);

					if ( empty( $order['client_email'] ) && ! empty( $order['form_detail_type'] ) && false !== strpos( $order['form_detail_type'], 'email' ) ) {
						$order_data[ $order_id ]['client_email'] = $order['form_detail_value'];
					}

					if ( empty( $order['client_name'] ) && ! empty( $order['form_detail_type'] ) && false !== strpos( $order['form_detail_type'], 'name' ) ) {
						$order_data[ $order_id ]['client_name'] = $order['form_detail_value'];
					}
				}
			}

			if ( ! empty( $order['calculator_field_id'] ) ) {
				if ( empty( $order_data[ $order_id ]['calculator_fields'][ $order['calculator_field_id'] ] ) ) {
					$value = $order['calculator_field_value'];
					if ( ! empty( $value ) && 0 === intval( $order['calculator_field_is_basic'] ) ) {
						$value = json_decode( $value, true );
					}

					$order_data[ $order_id ]['calculator_fields'][ $order['calculator_field_id'] ] = array(
						'id'             => $order['calculator_field_id'],
						'sort_id'        => $order['calculator_field_sort_id'],
						'label'          => $order['calculator_field_label'],
						'value'          => $value,
						'alias'          => $order['calculator_field_alias'],
						'add_to_summary' => $order['calculator_field_add_to_summary'],
						'option_unit'    => $order['calculator_field_option_unit'],
						'options'        => ! empty( $order['calculator_field_options'] ) ? json_decode( $order['calculator_field_options'], true ) : '',
						'summary_view'   => $order['calculator_field_summary_view'],
						'converted'      => $order['calculator_field_converted'],
						'is_basic'       => $order['calculator_field_is_basic'],
					);
				}
			}

			if ( empty( $order_data[ $order_id ]['currency'] ) ) {
				$order_data[ $order_id ]['currency'] = array(
					'number_of_decimals' => $order['number_of_decimals'],
					'thousand_separator' => $order['thousand_separator'],
					'decimal_separator'  => $order['decimal_separator'],
					'currency_position'  => $order['currency_position'],
					'currency_sign'      => $order['currency_sign'],
				);
			}

			if ( ! empty( $order['totals_id'] ) && ! empty( $order['totals_is_primary'] ) ) {
				if ( empty( $order_data[ $order_id ]['totals'][ $order['totals_id'] ] ) ) {
					$order_data[ $order_id ]['totals'][ $order['totals_id'] ] = array(
						'id'             => $order['totals_id'],
						'sort_id'        => $order['totals_sort_id'],
						'alias'          => $order['totals_alias'],
						'label'          => $order['totals_label'],
						'value'          => $order['totals_original_value'],
						'original_value' => $order['totals_original_value'],
						'is_primary'     => $order['totals_is_primary'],
						'converted'      => $order['totals_value'],
					);
				}
			}

			if ( ! empty( $order['totals_id'] ) && empty( $order['totals_is_primary'] ) ) {
				if ( empty( $order_data[ $order_id ]['other_totals'][ $order['totals_id'] ] ) ) {
					$order_data[ $order_id ]['other_totals'][ $order['totals_id'] ] = array(
						'id'             => $order['totals_id'],
						'sort_id'        => $order['totals_sort_id'],
						'alias'          => $order['totals_alias'],
						'label'          => $order['totals_label'],
						'value'          => $order['totals_original_value'],
						'original_value' => $order['totals_original_value'],
						'is_primary'     => $order['totals_is_primary'],
						'converted'      => $order['totals_value'],
					);
				}
			}

			if ( ! empty( $order['discount_id'] ) ) {
				if ( empty( $order_data[ $order_id ]['discounts'][ $order['discount_id'] ] ) ) {
					$order_data[ $order_id ]['discounts'][ $order['discount_id'] ] = array(
						'discount_view'   => $order['discount_view'],
						'discount_title'  => $order['discount_title'],
						'discount_type'   => $order['discount_type'],
						'discount_amount' => $order['discount_amount'],
						'discount_value'  => $order['discount_value'],
					);
				}
			}

			if ( ! empty( $order['promo_id'] ) ) {
				if ( empty( $order_data[ $order_id ]['promocodes'][ $order['promo_id'] ] ) ) {
					$order_data[ $order_id ]['promocodes'][ $order['promo_id'] ] = array(
						'promo_id'   => $order['promo_id'],
						'promo_code' => $order['promo_code'],
					);
				}
			}
		}

		foreach ( $order_data as $order_id => $order ) {
			$order_data[ $order_id ]['calculator_fields']  = array_values( $order_data[ $order_id ]['calculator_fields'] );
			$order_data[ $order_id ]['order_form_details'] = array_values( $order_data[ $order_id ]['order_form_details'] );
			$order_data[ $order_id ]['totals']             = array_values( $order_data[ $order_id ]['totals'] );
			$order_data[ $order_id ]['other_totals']       = array_values( $order_data[ $order_id ]['other_totals'] );
			$order_data[ $order_id ]['notes']              = array_values( $order_data[ $order_id ]['notes'] );
		}

		return array_values( $order_data );
	}

	public static function get_by_ids( $ids = array() ) {
		if ( empty( $ids ) ) {
			return array();
		}

		global $wpdb;
		$sql = sprintf(
			'SELECT *
					FROM %1$s
					WHERE id IN ( %3$s )
					ORDER BY %2$s DESC',
			self::_table(),
			static::$primary_key,
			implode( ',', $ids )
		);

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function delete_orders( $d, $ids ) {
		global $wpdb;
		$table_name = self::_table();
		$sql        = $wpdb->prepare( "DELETE FROM $table_name WHERE id IN ($d)", $ids ); // phpcs:ignore
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	public static function existing_calcs() {
		global $wpdb;
		$table_name = self::_table();
		return $wpdb->get_results( "SELECT DISTINCT calc_id FROM $table_name", ARRAY_A ); // phpcs:ignore
	}

	public static function get_unique_old_orders_ids() {
		global $wpdb;
		$table_name = self::_table();
		$sql = "SELECT DISTINCT old_order_id FROM $table_name WHERE old_order_id IS NOT NULL"; // phpcs:ignore
		return $wpdb->get_col( $sql ); // phpcs:ignore
	}

	public static function get_order_details_settings( $get_default = false ) {
		$default_settings = array(
			'form_details'       => array(
				'value'   => true,
				'key'     => 'form_details',
				'sort_id' => 1,
				'label'   => __( 'Form details', 'cost-calculator-builder' ),
			),
			'calculator_details' => array(
				'value'   => true,
				'sort_id' => 2,
				'key'     => 'calculator_details',
				'label'   => __( 'Calculator details', 'cost-calculator-builder' ),
			),
			'attachments'        => array(
				'value'   => true,
				'sort_id' => 3,
				'key'     => 'attachments',
				'label'   => __( 'Attachments', 'cost-calculator-builder' ),
			),
			'notes'              => array(
				'value'   => true,
				'sort_id' => 4,
				'key'     => 'notes',
				'label'   => __( 'Notes', 'cost-calculator-builder' ),
			),
		);

		if ( $get_default ) {
			return $default_settings;
		}

		return get_option( 'ccb_orders_details_settings', $default_settings );
	}

	public static function get_table_settings( $get_default = false ) {
		$default_settings = array(
			'order_id'   => array(
				'value'   => true,
				'sort_id' => 1,
				'key'     => 'order_id',
				'label'   => __( 'Order ID', 'cost-calculator-builder' ),
			),
			'type'       => array(
				'value'   => true,
				'sort_id' => 2,
				'key'     => 'type',
				'label'   => __( 'Name', 'cost-calculator-builder' ),
			),
			'email'      => array(
				'value'   => true,
				'sort_id' => 3,
				'key'     => 'email',
				'label'   => __( 'Email', 'cost-calculator-builder' ),
			),
			'date'       => array(
				'value'   => true,
				'sort_id' => 4,
				'key'     => 'date',
				'label'   => __( 'Date', 'cost-calculator-builder' ),
			),
			'calculator' => array(
				'value'   => true,
				'sort_id' => 5,
				'key'     => 'calculator',
				'label'   => __( 'Calculator', 'cost-calculator-builder' ),
			),
			'payment'    => array(
				'value'   => true,
				'sort_id' => 6,
				'key'     => 'payment',
				'label'   => __( 'Payment', 'cost-calculator-builder' ),
			),
			'status'     => array(
				'value'   => true,
				'sort_id' => 7,
				'key'     => 'status',
				'label'   => __( 'Status', 'cost-calculator-builder' ),
			),
			'amount'     => array(
				'value'   => true,
				'sort_id' => 8,
				'key'     => 'amount',
				'label'   => __( 'Amount', 'cost-calculator-builder' ),
			),
		);

		if ( $get_default ) {
			return $default_settings;
		}

		return get_option( 'ccb_orders_table_settings', $default_settings );
	}

	public static function update_order_details_settings( $settings ) {
		update_option( 'ccb_orders_details_settings', $settings );
	}

	public static function update_table_settings( $settings ) {
		update_option( 'ccb_orders_table_settings', $settings );
	}

	public static function update_order( $order_id, $data ) {
		if ( empty( $order_id ) || empty( $data ) ) {
			return false;
		}

		$data['updated_at'] = wp_date( 'Y-m-d H:i:s' );
		return self::update( $data, array( 'id' => $order_id ) );
	}

	public static function get_order_by_id( $order_id ) {
		if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return false;
		}

		return self::get( static::$primary_key, $order_id );
	}

	public static function delete_order( $order_id ) {
		if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return false;
		}

		OrdersFormsDetails::delete_form_details( $order_id );
		OrdersCalculatorFields::remove_fields( $order_id );
		OrdersCurrency::delete_currency( $order_id );
		OrdersPromocodes::delete_promocodes( $order_id );
		OrdersPayments::delete_payments( $order_id );
		OrdersTotals::delete_totals( $order_id );
		OrdersNotes::delete_note_by_order_id( $order_id );

		return self::delete( array( 'id' => $order_id ) );
	}

	/**
	 * Analytics Methods
	 */

	/**
	 * Build WHERE clause for analytics queries based on period, calc_id and status
	 */
	private static function build_analytics_where_clause( $period, $calc_id = null, $status = 'all', $custom_date = null ) {
		global $wpdb;

		$periods = ccb_get_date_range_for_period_pro( $period, $custom_date );
		$from    = $periods['from'];
		$to      = $periods['to'];

		$where = array();

		if ( $from && $to ) {
			$where[] = $wpdb->prepare(
				'o.created_at BETWEEN %s AND %s + INTERVAL 1 DAY',
				$from->format( 'Y-m-d H:i:s' ),
				$to->format( 'Y-m-d H:i:s' )
			);
		}

		if ( ! is_null( $calc_id ) ) {
			$where[] = $wpdb->prepare( 'o.calc_id = %d', $calc_id );
		}

		if ( 'all' !== $status ) {
			$status_value = 'completed' === $status ? 'complete' : $status;
			$where[]      = $wpdb->prepare( 'os.slug = %s', $status_value );
		}

		return ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';
	}

	/**
	 * Get orders with totals for analytics (Revenue calculation)
	 */
	public static function get_orders_for_analytics( $period, $calc_id = null, $status = 'all' ) {
		global $wpdb;

		$custom_date = null;
		if ( 'custom' === $period && isset( $_GET['extra'] ) ) {
			$custom_date = sanitize_text_field( $_GET['extra'] );
		}

		$where_clause = self::build_analytics_where_clause( $period, $calc_id, $status, $custom_date );

		// phpcs:disable
		$sql = "
			SELECT 
				o.id,
				o.calc_id,
				o.calc_title,
				o.created_at,
				ot.original_value as total_value,
				os.slug as status
			FROM " . self::_table() . " o
			INNER JOIN " . OrdersPayments::_table() . " p ON o.id = p.order_id
			INNER JOIN " . OrdersStatuses::_table() . " os ON p.payment_status = os.id
			LEFT JOIN " . OrdersTotals::_table() . " ot ON o.id = ot.order_id AND ot.is_primary = 1
			{$where_clause}
			ORDER BY o.created_at
		";

		return $wpdb->get_results( $sql );
		// phpcs:enable
	}

	/**
	 * Get calculator fields data for analytics
	 */
	public static function get_calculator_fields_for_analytics( $period, $calc_id, $status = 'all' ) {
		global $wpdb;

		$custom_date = null;
		if ( 'custom' === $period && isset( $_GET['extra'] ) ) {
			$custom_date = sanitize_text_field( $_GET['extra'] );
		}

		$where_clause = self::build_analytics_where_clause( $period, $calc_id, $status, $custom_date );

		// phpcs:disable
		$sql = "
			SELECT 
				o.id as order_id,
				ofa.alias as field_alias,
				CASE WHEN ofc.is_basic = 1 
					THEN ofb.value 
					ELSE ofm.value 
				END as field_value,
				ofm.options as field_options,
				ofm.summary_view as field_summary_view
			FROM " . self::_table() . " o
			INNER JOIN " . OrdersPayments::_table() . " p ON o.id = p.order_id
			INNER JOIN " . OrdersStatuses::_table() . " os ON p.payment_status = os.id
			LEFT JOIN " . OrdersCalculatorFields::_table() . " ofc ON o.id = ofc.order_id
			LEFT JOIN " . OrdersCalcFieldsAttrs::_table() . " ofa ON ofc.id = ofa.field_id
			LEFT JOIN " . OrdersCalcBasicFields::_table() . " ofb ON ofc.id = ofb.field_id AND ofc.is_basic = 1
			LEFT JOIN " . OrdersCalcFieldsMultiOptions::_table() . " ofm ON ofc.id = ofm.field_id AND ofc.is_basic = 0
			{$where_clause}
			ORDER BY o.id, ofa.sort_id
		";

		return $wpdb->get_results( $sql );
		// phpcs:enable
	}
}
