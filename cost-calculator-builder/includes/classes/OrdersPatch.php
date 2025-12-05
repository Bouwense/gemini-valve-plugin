<?php

namespace cBuilder\Classes;

use cBuilder\Classes\Database\OrdersFormsDetails;
use cBuilder\Classes\Database\OrdersCurrency;
use cBuilder\Classes\Database\OrdersDiscounts;
use cBuilder\Classes\Database\OrdersPayments;
use cBuilder\Classes\Database\OrdersPromocodes;
use cBuilder\Classes\Database\OrdersStatuses;
use cBuilder\Classes\Database\OrdersCalculatorFields;
use cBuilder\Classes\Database\Orders;
use cBuilder\Classes\Database\CalcOrders;
use cBuilder\Classes\Database\OrdersTotals;
use cBuilder\Classes\Database\OrdersCalcFieldsAttrs;
use cBuilder\Classes\Database\OrdersCalcFieldsMultiOptions;
use cBuilder\Classes\Database\OrdersCalcBasicFields;
use cBuilder\Classes\Database\Payments;
use cBuilder\Classes\Database\OrdersNotes;

class OrdersPatch {
	public static function ccb_patch_maybe_create_orders_table() {
		global $wpdb;
		$orders_table = CalcOrders::_table();
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $orders_table ) ) ) { // phpcs:ignore
			CalcOrders::create_table();
			OrdersStatuses::create_table();
			OrdersPayments::create_table();
			OrdersFormsDetails::create_table();
			OrdersCalculatorFields::create_table();
			OrdersCalcFieldsAttrs::create_table();
			OrdersCalcFieldsMultiOptions::create_table();
			OrdersCalcBasicFields::create_table();
			OrdersCurrency::create_table();
			OrdersDiscounts::create_table();
			OrdersPromocodes::create_table();
			OrdersTotals::create_table();
			OrdersNotes::create_table();
		}
	}

	public static function ccb_patch_maybe_create_orders_statuses() {
		global $wpdb;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', OrdersStatuses::_table() ) ) ) { // phpcs:ignore
			OrdersStatuses::create_default_statuses();
		}
	}

	public static function ccb_patch_maybe_move_orders_data() {
		global $wpdb;
		$orders_table = CalcOrders::_table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $orders_table ) ) ) { // phpcs:ignore
			$new_orders_count = CalcOrders::orders_count();
			$old_orders_count = Orders::orders_count();

			if ( empty( $new_orders_count ) && ! empty( $old_orders_count ) ) {
				$batch_size     = 20;
				$offset         = 0;
				$total_migrated = 0;
				$errors_count   = 0;

				while ( $offset < $old_orders_count ) {
					$old_orders = Orders::get_order_full_data_batch( $batch_size, $offset );

					if ( empty( $old_orders ) ) {
						break;
					}

					foreach ( $old_orders as $old_order ) {
						try {
							$order_data = array(
								'calc_id'      => $old_order['calc_id'],
								'total_amount' => isset( $old_order['payment_total'] ) ? $old_order['payment_total'] : 0,
								'calc_title'   => $old_order['calc_title'],
								'old_order_id' => $old_order['order_id'],
							);

							$form_data = array();
							if ( ! empty( $old_order['form_details']['fields'] ) ) {
								$form_data = $old_order['form_details']['fields'];
							}

							$calc_fields = array();
							if ( ! empty( $old_order['order_details'] ) ) {
								foreach ( $old_order['order_details'] as $order_detail ) {
									$calc_fields[] = $order_detail;
								}
							}

							$default_status = OrdersStatuses::get_default_status();

							$old_payment_data = Payments::payment_by_order_id_exist( $old_order['order_id'] );
							$payment_data     = array(
								'order_id'       => $old_order['order_id'],
								'payment_type'   => 'no_payments',
								'payment_status' => $default_status['id'],
								'total'          => 0,
							);

							if ( ! empty( $old_payment_data ) ) {
								$status    = OrdersStatuses::get_status_by_slug( $old_payment_data->status );
								$status_id = $default_status['id'];
								if ( ! empty( $status['id'] ) ) {
									$status_id = $status['id'];
								}

								$total = 0;
								if ( ! empty( $old_order['totals'] ) && is_array( $old_order['totals'] ) ) {
									foreach ( $old_order['totals'] as $t ) {
										if ( isset( $t['total'] ) ) {
											$total += $t['total'];
										}
									}
								}

								$payment_data = array(
									'order_id'       => $old_order['order_id'],
									'payment_type'   => $old_payment_data->type,
									'payment_status' => $status_id,
									'total'          => ! empty( $old_payment_data->total ) ? $old_payment_data->total : $total,
									'tax'            => isset( $old_payment_data->tax ) ? $old_payment_data->tax : 0,
									'transaction'    => isset( $old_payment_data->transaction ) ? $old_payment_data->transaction : '',
									'paid_at'        => isset( $old_payment_data->paid_at ) ? $old_payment_data->paid_at : null,
									'created_at'     => isset( $old_payment_data->created_at ) ? $old_payment_data->created_at : wp_date( 'Y-m-d H:i:s' ),
								);
							}

							$data = array(
								'order_data'        => $order_data,
								'form_details'      => $form_data,
								'calculator_fields' => $calc_fields,
								'currency'          => isset( $old_order['order_currency'] ) ? $old_order['order_currency'] : array(),
								'discounts'         => isset( $old_order['discounts'] ) ? $old_order['discounts'] : array(),
								'promocodes'        => isset( $old_order['promocodes'] ) ? $old_order['promocodes'] : array(),
								'totals'            => isset( $old_order['totals'] ) ? $old_order['totals'] : array(),
								'other_totals'      => isset( $old_order['other_totals'] ) ? $old_order['other_totals'] : array(),
								'payment_details'   => $payment_data,
								'created_at'        => isset( $old_order['created_at'] ) ? $old_order['created_at'] : wp_date( 'Y-m-d H:i:s' ),
							);

							CalcOrders::create_order( $data );
							$total_migrated++;

							unset( $order_data, $form_data, $calc_fields, $payment_data, $data, $old_payment_data, $default_status );

						} catch ( \Exception $e ) {
							$errors_count++;
							error_log( 'CCB Migration Error for order ' . $old_order['order_id'] . ': ' . $e->getMessage() ); // phpcs:ignore
						}
					}

					unset( $old_orders );

					global $wpdb;
					if ( isset( $wpdb->queries ) ) {
						$wpdb->queries = array();
					}
					$wpdb->flush();

					if ( function_exists( 'gc_collect_cycles' ) ) {
						gc_collect_cycles();
					}

					$offset += $batch_size;

					usleep( 200000 );
				}

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:disable
					error_log( sprintf( 
						'CCB Orders Migration Complete: %d migrated, %d errors out of %d total', 
						$total_migrated, 
						$errors_count, 
						$old_orders_count 
					) );
					// phpcs:enable
				}

				if ( function_exists( 'wp_cache_flush' ) ) {
					wp_cache_flush();
				}

				global $wpdb;
				if ( isset( $wpdb->queries ) ) {
					$wpdb->queries = array();
				}
				$wpdb->flush();

				if ( function_exists( 'gc_collect_cycles' ) ) {
					gc_collect_cycles();
				}
			}
		}
	}
}
