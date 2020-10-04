<?php
/**
 * Suborder Premium class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Deposits and Down Payments
 * @version 1.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Suborders_Premium' ) ) {
	/**
	 * WooCommerce Deposits and Down Payments Suborders
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Suborders_Premium extends YITH_WCDP_Suborders {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_Suborders_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Constructor.
		 *
		 * @return \YITH_WCDP_Suborders_Premium
		 * @since 1.0.0
		 */
		public function __construct() {
			// change suborder default status, when balance order cannot be completed online
			add_filter( 'yith_wcdp_suborder_status', array( $this, 'change_suborder_status' ), 10, 6 );

			// handle automatic expired suborders cancel
			add_action( 'wp', array( $this, 'cancel_expired_suborders_setup_schedule' ) );
			add_action( 'cancel_expired_suborders_action_schedule', array( $this, 'delete_expired_suborders_do_schedule' ) );

			// handle automatic expiring suborders notification
			add_action( 'wp', array( $this, 'notify_expiring_suborders_setup_schedule' ) );
			add_action( 'notify_expiring_suborders_action_schedule', array( $this, 'notify_expiring_suborders_do_schedule' ) );
			
			// handle suborders admin actions
			add_action( 'admin_action_yith_wcdp_refund_item', array( $this, 'create_refund_for_item' ) );
			add_action( 'admin_action_yith_wcdp_delete_refund_notice', array( $this, 'delete_refund_notice' ) );

			parent::__construct();
		}

		/* === TO-REFUND ORDER METHODS === */

		/**
		 * Create manual refund for item, after admin action
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function create_refund_for_item() {
			if(  ! isset( $_GET['order_id'] ) || ! isset( $_GET['item_id'] ) ){
				wp_redirect( esc_url_raw( add_query_arg( 'post_type', 'shop_order', admin_url( 'edit.php' ) ) ) );
				die();
			}

			$order_id = intval( $_GET['order_id'] );
			$item_id = intval( $_GET['item_id'] );

			$order = wc_get_order( $order_id );

			$refund_amount = 0;
			$to_refund = array();

			if( $order ){
				$order_items = $order->get_items( 'line_item' );

				if( isset( $order_items[ $item_id ] ) ){
					$item = $order_items[ $item_id ];

					$to_refund[ $item_id ] = array(
						'qty' => $item['qty'],
						'refund_total' => $order->get_item_total( $item, true ),
						'type' => 'line_item'
					);
					$refund_amount += $order->get_item_total( $item, true, false );
				}

				if ( WC()->payment_gateways() ) {
					$payment_gateways = WC()->payment_gateways->payment_gateways();
				}

				$order_payment_gateway = yit_get_prop( $order, 'payment_method' );

				if ( isset( $payment_gateways[ $order_payment_gateway ] ) ) {
					$refund_reason = __( 'Item refunded manually for deposit expiration', 'yith-woocommerce-deposits-and-down-payments' );

					// Create the refund object
					$refund = wc_create_refund( array(
						'amount'     => $refund_amount,
						'reason'     => $refund_reason,
						'order_id'   => $order_id,
						'line_items' => $to_refund
					) );

					if( $refund ) {
						wc_update_order_item_meta( $item_id, '_deposit_refunded_after_expiration', yit_get_prop( $refund, 'id' ) );
						wc_delete_order_item_meta( $item_id, '_deposit_needs_manual_refund' );
					}
				}
			}

			$redirect_url = str_replace( '&amp;', '&', get_edit_post_link( $order_id ) );
			wp_redirect( $redirect_url );
			die();
		}

		/**
		 * Delete notice to refund order after deposit expiration
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function delete_refund_notice() {
			if(  ! isset( $_GET['order_id'] ) || ! isset( $_GET['item_id'] ) ){
				wp_redirect( esc_url_raw( add_query_arg( 'post_type', 'shop_order', admin_url( 'edit.php' ) ) ) );
				die();
			}

			$order_id = intval( $_GET['order_id'] );
			$item_id = intval( $_GET['item_id'] );

			wc_delete_order_item_meta( $item_id, '_deposit_needs_manual_refund' );

			$redirect_url = str_replace( '&amp;', '&', get_edit_post_link( $order_id ) );
			wp_redirect( $redirect_url );
			die();
		}

		/**
		 * Count orders with an expired deposit, that requires manual refund
		 *
		 * @return int Number of orders with deposit to manually refund
		 * @since 1.0.0
		 */
		public function count_deposit_to_refund() {
			global $wpdb;

			//TODO: review query when WC switches to custom tables
			$query = "SELECT
                       COUNT( DISTINCT( ID ) )
                      FROM {$wpdb->posts} AS p
                      LEFT JOIN {$wpdb->prefix}woocommerce_order_items as i ON p.ID = i.order_id
                      LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
                      WHERE p.post_type = %s
                      AND p.post_status IN (%s, %s)
                      AND im.meta_key = %s
                      AND im.meta_value = %d";

			$query_arg = array(
				'shop_order',
				'wc-completed',
				'wc-processing',
				'_deposit_needs_manual_refund',
				1
			);

			$count = $wpdb->get_var( $wpdb->prepare( $query, $query_arg ) );

			return $count;
		}

		/* === SUBORDER METHODS === */

		/**
		 * Change suborder status when orders cannot be completed online
		 *
		 * @param $default_status string Original suborder status
		 * @param $new_order \WC_Order New order
		 * @param $parent_order \WC_Order Parent order
		 * @param $item_id int Current item id
		 * @param $item mixed Current item
		 * @param $product \WC_Product Current product
		 * @return string Filtered suborder status
		 * @since 1.0.0
		 */
		public function change_suborder_status( $default_status, $new_order_id, $parent_order_id, $item_id, $item, $product ) {
			$create_balance_orders = yit_get_prop( $product, '_create_balance_orders' );
			$create_balance_orders = $create_balance_orders ? $create_balance_orders : 'default';

			if( $create_balance_orders == 'no' || ( $create_balance_orders == 'default' && 'yes' != get_option( 'yith_wcdp_general_create_balance_orders', 'yes' ) ) ){
				$default_status = 'on-hold';

				yit_save_prop( wc_get_order( $new_order_id ), '_full_payment_needs_manual_payment', true );
			}

			return $default_status;
		}

		/* === CRON HANDLING === */

		/**
		 * Setup schedule for cancel expired balance orders
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function cancel_expired_suborders_setup_schedule() {
			$schedule = get_option( 'yith_wcdp_deposit_expiration_enable', 'no' );
			$is_scheduled = $schedule == 'yes';

			if( ! $is_scheduled ) {
				wp_clear_scheduled_hook( 'cancel_expired_suborders_action_schedule' );
			}
			elseif ( ! wp_next_scheduled( 'cancel_expired_suborders_action_schedule' ) ) {
				wp_schedule_event( time(), 'daily', 'cancel_expired_suborders_action_schedule' );
			}
		}

		/**
		 * Delete expired balance orders with scheduled events
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function delete_expired_suborders_do_schedule() {
			global $wpdb;

			$schedule = get_option( 'yith_wcdp_deposit_expiration_enable', 'no' );
			$expiration = get_option( 'yith_wcdp_deposits_expiration_duration', 30 );
			$fallback = get_option( 'yith_wcdp_deposit_expiration_fallback', 'none' );

			if( $schedule == 'no' ){
				return;
			}

			$time = sprintf( '-%d day', $expiration );

			//TODO: review query when WC switches to custom tables
			$query = "SELECT p.ID
                      FROM {$wpdb->posts} AS p
                      LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
                      WHERE p.post_type = %s
                      AND p.post_parent <> %d
                      AND p.post_status NOT IN ( %s, %s, %s )
                      AND p.post_date < %s
                      AND pm.meta_key = %s
                      AND pm.meta_value = %s";

			$query_args = array(
				'shop_order',
				0,
				'wc-completed',
				'wc-processing',
				'wc-cancelled',
				date( 'Y-m-d H:i:s', strtotime( $time ) ),
				'_has_full_payment',
				1
			);

			$order_ids = $wpdb->get_col( $wpdb->prepare( $query, $query_args ) );

			// remove customer note notification
			add_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );

			if( ! empty( $order_ids ) ){
				foreach( $order_ids as $order_id ){
					$order = wc_get_order( $order_id );

					if( ! $order ){
						continue;
					}

					$items = $order->get_items( 'line_item' );

					if( ! empty( $items ) ){
						foreach( $items as $item_id => $item ){
							// refund correct line item in parent order
							switch( $fallback ){
								case 'refund':
									$parent_order_id = wc_get_order_item_meta( $item_id, '_deposit_id', true );
									$parent_order = wc_get_order( $parent_order_id );

									if( ! $parent_order ){
										break;
									}

									if( ! in_array( $parent_order->get_status(), array( 'completed', 'processing', 'partially-paid' ) ) ){
										break;
									}

									$to_refund = array();
									$to_refund_name = array();
									$parent_order_items = $parent_order->get_items( 'line_item' );
									$refund_amount = 0;

									if( ! empty( $parent_order_items ) ){
										foreach( $parent_order_items as $parent_item_id => $parent_item ){
											$full_payment = wc_get_order_item_meta( $parent_item_id, '_full_payment_id', true );
											$already_refunded = wc_get_order_item_meta( $item_id, '_deposit_refunded_after_expiration', true );

											if( $full_payment && $full_payment == $order_id && ! $already_refunded ){
												$to_refund[ $parent_item_id ] = array(
													'qty' => $parent_item['qty'],
													'refund_total' => $parent_order->get_item_total( $parent_item, true ),
													'type' => 'line_item'
												);
												$refund_amount += $parent_order->get_item_total( $parent_item, true, false );

												$to_refund_name[] = $parent_item['name'];
											}
										}
									}

									if ( WC()->payment_gateways() ) {
										$payment_gateways = WC()->payment_gateways->payment_gateways();
									}
									
									$parent_order_payment_method = yit_get_prop( $parent_order, 'payment_method' );

									if ( isset( $payment_gateways[ $parent_order_payment_method ] ) && $payment_gateways[ $parent_order_payment_method ]->supports( 'refunds' ) ) {
										$refund_reason = __( 'Item refunded automatically for deposit expiration', 'yith-woocommerce-deposits-and-down-payments' );

										// Create the refund object
										$refund = wc_create_refund( array(
											'amount'     => $refund_amount,
											'reason'     => $refund_reason,
											'order_id'   => $parent_order_id,
											'line_items' => $to_refund
										) );

										$result = $payment_gateways[ $parent_order->payment_method ]->process_refund( $parent_order_id, $refund_amount, $refund_reason );

										do_action( 'woocommerce_refund_processed', $refund, $result );

										// if correctly refunded, mark deposit items
										if( $refund ){
											foreach( $to_refund as $refund_item_id => $refund ){
												wc_update_order_item_meta( $refund_item_id, '_deposit_refunded_after_expiration', $refund->id );
											}
										}

										$parent_order->add_order_note( apply_filters( 'yith_wcdp_expired_order_notice', sprintf( _n( 'Item %s has been automatically refunded, because %d days allowed to complete payment have passed', 'Items %s have been automatically refunded, because %d days allowed to complete payment have passed', count( $to_refund_name ),  'yith-woocommerce-deposits-and-down-payments' ), implode( ', ', $to_refund_name ), $expiration ) ), true );
									}
									else{
										foreach( $to_refund as $refund_item_id => $refund ){
											wc_update_order_item_meta( $refund_item_id, '_deposit_needs_manual_refund', true );
										}
									}

									break;

								case 'none':
								default:
									break;
							}

							// increment stock for item
							$_product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : $order->get_product_from_item( $item );

							if ( $_product && $_product->exists() && $_product->managing_stock() ) {
								$old_stock    = wc_stock_amount( $_product->stock );
								$new_quantity = yit_update_product_stock( $_product, $item['qty'], 'increase' );

								$order->add_order_note( sprintf( __( 'Item #%s stock increased from %s to %s.', 'woocommerce' ), $item['product_id'], $old_stock, $new_quantity ) );

								do_action( 'woocommerce_restock_refunded_item', $_product->get_id(), $old_stock, $new_quantity, $order );
							}
						}
					}

					// set child orders as cancelled
					$order->update_status( 'cancelled' );
					$order->add_order_note( apply_filters( 'yith_wcdp_expired_order_notice', sprintf( __( 'The %d days granted to complete this order have passed. For this reason, it has been switched to cancelled, and it cannot be completed anymore', 'yith-woocommerce-deposits-and-down-payments' ), $expiration ) ), true );

					// set meta to mark expired orders
					yit_save_prop( $order, '_has_deposit_expired', 1 );
				}
			}

			// re-enable customer notification (just in case)
			remove_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );
		}

		/**
		 * Setup schedule for expiring suborder notification
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function notify_expiring_suborders_setup_schedule() {
			$enable_expiration = get_option( 'yith_wcdp_deposit_expiration_enable', 'no' );
			$enable_notification = get_option( 'yith_wcdp_notify_customer_deposit_expiring', 'no' );
			$notification_days = get_option( 'yith_wcdp_notify_customer_deposit_expiring_days_limit', 15 );
			$is_scheduled = $enable_expiration == 'yes' && $enable_notification == 'yes' && $notification_days;

			if( ! $is_scheduled ) {
				wp_clear_scheduled_hook( 'notify_expiring_suborders_action_schedule' );
			}
			elseif ( ! wp_next_scheduled( 'notify_expiring_suborders_action_schedule' ) ) {
				wp_schedule_event( time(), 'daily', 'notify_expiring_suborders_action_schedule' );
			}
		}

		/**
		 * Send email to customer when their deposit is about to expire
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function notify_expiring_suborders_do_schedule() {
			global $wpdb;

			$enable_expiration = get_option( 'yith_wcdp_deposit_expiration_enable', 'no' );
			$expiration = get_option( 'yith_wcdp_deposits_expiration_duration', 30 );
			$enable_notification = get_option( 'yith_wcdp_notify_customer_deposit_expiring', 'no' );
			$notification_days = get_option( 'yith_wcdp_notify_customer_deposit_expiring_days_limit', 15 );

			if( $enable_expiration == 'no' || $enable_notification == 'no' || $expiration - $notification_days < 0 ){
				return;
			}

			$time = strtotime( sprintf( '-%d day', $expiration - $notification_days ) );
			$start = date( 'Y-m-d 00:00:00', $time );
			$end = date( 'Y-m-d 23:59:59', $time );

			//TODO: review query when WC switches to custom tables
			$query = "SELECT DISTINCT( p.post_parent )
                      FROM {$wpdb->posts} AS p
                      LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
                      WHERE p.post_type = %s
                      AND p.post_parent <> %d
                      AND p.post_status NOT IN ( %s, %s, %s, %s )
                      AND p.post_date < %s
                      AND p.post_date > %s
                      AND pm.meta_key = %s
                      AND pm.meta_value = %s";

			$query_args = array(
				'shop_order',
				0,
				'wc-completed',
				'wc-processing',
				'wc-cancelled',
				'trash',
				$end,
				$start,
				'_has_full_payment',
				1
			);

			$order_ids = $wpdb->get_col( $wpdb->prepare( $query, $query_args ) );

			if( ! empty( $order_ids ) ){
				foreach( $order_ids as $order_id ){
					$order = wc_get_order( $order_id );

					if( ! $order ){
						continue;
					}

					do_action( 'yith_wcdp_deposits_expiring', yit_get_prop( $order, 'id' ) );

					yit_save_prop( $order, '_has_expired', true );

					$order->add_order_note( __( 'Expiring deposit notification sent', 'yith-woocommerce-deposits-and-down-payments' ) );
				}


			}
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_Suborders_Premium
		 * @since 1.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of YITH_WCDP_Suborders_Premium class
 *
 * @return \YITH_WCDP_Suborders_Premium
 * @since 1.0.0
 */
function YITH_WCDP_Suborders_Premium(){
	return YITH_WCDP_Suborders_Premium::get_instance();
}