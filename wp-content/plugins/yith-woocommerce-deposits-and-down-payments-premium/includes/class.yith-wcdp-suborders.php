<?php
/**
 * Suborder class
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

if ( ! class_exists( 'YITH_WCDP_Suborders' ) ) {
	/**
	 * WooCommerce Deposits and Down Payments
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Suborders {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_Suborders
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Temp storage where to store real cart during plugin elaboration that requires a custom cart
		 *
		 * @var \WC_Cart
		 * @since 1.0.0
		 */
		protected $_cart;

		/**
		 * Temp storage where to store real applied coupon during plugin elaboration that requires a custom cart
		 *
		 * @var mixed
		 * @since 1.0.0
		 */
		protected $_coupons;

		/**
		 * Constructor.
		 *
		 * @return \YITH_WCDP_Suborders
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'create_balance_suborder' ), 10, 2 );
			add_filter( 'woocommerce_get_product_from_item', array( $this, 'woocommerce_get_product_from_item' ), 10, 3 );
			add_action( 'trashed_post', array( $this, 'trash_suborders' ) );
			add_action( 'untrashed_post', array( $this, 'untrash_suborders' ) );
			add_filter( 'yith_wcmv_get_suborder_ids', array( $this, 'remove_deposit_suborder_from_multi_vendor' ), 10, 2 );

			// avoid payment gateway to reduce stock order for suborders
			add_filter( 'woocommerce_can_reduce_order_stock', array( $this, 'skip_reduce_stock_on_suborders' ), 10, 2 );

			// avoid WooCommerce to block suborder processing because of products out of stock (stock was already processed during deposit checkout)
			add_filter( 'woocommerce_order_item_product', array( $this, 'set_suborder_items_as_in_stock' ), 10, 2 );
		}

		/* === SUBORDER METHODS === */

		/**
		 * Create suborders during process checkout, to let user finalize all his/her deposit in a separate balance order
		 *
		 * @param $order_id int Processing order id
		 * @return bool Status of the operation
		 * @since 1.0.0
		 */
		public function create_balance_suborder( $order_id, $posted_data ) {
			
			if( ! defined( 'YITH_WCDP_PROCESS_SUBORDERS' ) ){
				define( 'YITH_WCDP_PROCESS_SUBORDERS', true );
			}
			
			// retrieve order
			$parent_order = wc_get_order( $order_id );
			$suborders = array();

			// if no order found, exit
			if( ! $parent_order ){
				return false;
			}

			// if order already process, exit
			$suborders_meta = yit_get_prop( $parent_order, '_full_payment_orders' );

			if( $suborders_meta ){
				return false;
			}

			// retrieve order items
			$items = $parent_order->get_items( 'line_item' );

			// if no items found, exit
			if( empty( $items ) ){
				return false;
			}

			// create support cart
			$this->create_support_cart();

			// retrieve deposit payment balance
			$deposit_shipping_preference = get_option( 'yith_wcdp_general_deposit_shipping', 'let_user_choose' );
			$deposit_admin_shipping_preference = get_option( 'yith_wcdp_general_deposit_shipping_admin_selection' );

			// cycle over order items
			foreach( $items as $item_id => $item ){

				$deposit = wc_get_order_item_meta( $item_id, '_deposit', true );
				$deposit_balance = wc_get_order_item_meta( $item_id, '_deposit_balance', true );
				$deposit_shipping_method = wc_get_order_item_meta( $item_id, '_deposit_shipping_method', true );
				$product = is_object( $item ) ? $item->get_product() : $parent_order->get_product_from_item( $item );

				// if not a deposit, continue
				if( ! $deposit ){
					continue;
				}

				// set order item meta with deposit-related full payment order
				wc_add_order_item_meta( $item_id, '_full_payment_id', false );

				// skip processing for other reason
				if( apply_filters( 'yith_wcdp_skip_suborder_creation', false, $item_id, $item, $order_id, $parent_order, $product ) ){
					continue;
				}

				// set has_deposit meta
				yit_save_prop( $parent_order, '_has_deposit', true );

				// if deposit, add elem to support cart (filters change price of the product to be added to the cart)
				add_filter( 'woocommerce_add_cart_item', array( $this, 'set_item_full_amount_price' ) );

				$product_id = $product->is_type( 'variation' ) ? yit_get_prop( $product, 'parent_id' ) : $product->get_id();
				$variation_id = $product->is_type( 'variation' ) ? $product->get_id() : '';
				$variation_attributes = $product->is_type( 'variation' ) ? $product->get_variation_attributes() : array();

				WC()->cart->add_to_cart( $product_id, $item['qty'], $variation_id, $variation_attributes, apply_filters( 'yith_wcdp_suborder_add_cart_item_data', array(
					'_deposit_balance' => $deposit_balance
				), $item, $product ) );
				remove_filter( 'woocommerce_add_cart_item', array( $this, 'set_item_full_amount_price' ) );

				// apply coupons (when required and possible) to suborder
				if( apply_filters( 'yith_wcdp_propagate_coupons', false ) && ! empty( $this->_coupons ) ){
					foreach( $this->_coupons as $coupon ){
						if( apply_filters( 'yith_wcdp_propagate_coupon', true, $coupon) ) {
							WC()->cart->add_discount( $coupon );
						}
					}
					wc_clear_notices();
				}

				// set shipping method for suborder
				if( $deposit_shipping_preference == 'let_user_choose' && $deposit_shipping_method && apply_filters( 'yith_wcdp_virtual_on_deposit', true ) ){
					WC()->checkout()->shipping_methods = $deposit_shipping_method;
				}
				elseif( $deposit_shipping_preference == 'admin_choose' && $deposit_admin_shipping_preference && apply_filters( 'yith_wcdp_virtual_on_deposit', true ) ){
					WC()->checkout()->shipping_methods = (array) $deposit_admin_shipping_preference;
				}
				elseif( ! apply_filters( 'yith_wcdp_virtual_on_deposit', true ) ){
					WC()->checkout()->shipping_methods = array();
				}

				// create suborder
				$new_suborder_id = WC()->checkout()->create_order( $posted_data );

				if( ! $new_suborder_id || is_wp_error( $new_suborder_id ) ){
					continue;
				}

				// set new suborder post parent
				$new_suborder = wc_get_order( $new_suborder_id );

				yit_save_prop( $new_suborder, array(
					'post_parent' => $order_id,
					'post_status' => apply_filters( 'yith_wcdp_suborder_status', 'pending', $new_suborder_id, $order_id, $item_id, $item, $product ),

					// disable stock management for brand new order
					'_has_full_payment' => true,

					// disable stock management for brand new order
					'_order_stock_reduced' => true,

					// update created_via meta
					'_created_via' => 'yith_wcdp_balance_order',

					// add plugin version
					'_yith_wcdp_version' => YITH_WCDP::YITH_WCDP_VERSION
				) );

				// update new suborder totals
				$new_suborder->calculate_shipping();
				$new_suborder->calculate_taxes();
				$new_suborder->calculate_totals();

				// set suborder customer note (remove email notification for this action only during this call)
				add_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );
				$new_suborder->add_order_note( sprintf( '%s <a href="%s">#%d</a>', __( 'This order has been created to allow payment of the balance', 'yith-woocommerce-deposits-and-down-payments' ), $parent_order->get_view_order_url(), $order_id ), apply_filters( 'yith_wcdp_suborder_note_is_customer_note', true ) );
				remove_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );

				// update new suborder items
				$new_suborder_items = $new_suborder->get_items( 'line_item' );
				if( ! empty( $new_suborder_items ) ){
					foreach( $new_suborder_items as $suborder_item_id => $suborder_item ){
						wc_add_order_item_meta( $suborder_item_id, '_deposit_id', $order_id );
						wc_add_order_item_meta( $suborder_item_id, '_full_payment', true );
					}
				}

				// register suborder just created
				$suborders[] = $new_suborder_id;

				// set order item meta with deposit-related full payment order
				wc_update_order_item_meta( $item_id, '_full_payment_id', $new_suborder_id );

				// empty support cart, for next suborder
				WC()->cart->empty_cart();
			}

			// restore original cart
			$this->restore_original_cart();

			yit_save_prop( $parent_order, '_full_payment_orders', $suborders );

			return true;
		}

		/**
		 * Change item price, when adding it to temp cart, to let user pay only order balance
		 *
		 * @param $cart_item_data mixed Array of items added to temp cart
		 * @return mixed Filtered cart item data
		 * @since 1.0.0
		 */
		public function set_item_full_amount_price( $cart_item_data ) {
			if( ! isset( $cart_item_data['_deposit_balance'] ) ){
				return $cart_item_data;
			}

			yit_set_prop( $cart_item_data['data'], 'price', $cart_item_data['_deposit_balance'] );
			yit_set_prop( $cart_item_data['data'], 'yith_wcdp_balance', true );

			return $cart_item_data;
		}

		/**
		 * Filter item product, when retrieving it from order item
		 *
		 * @param $product \WC_Product Product found
		 * @param $item mixed Order item
		 * @param $order \WC_Order Order object
		 * @return \WC_Product Filtered product
		 * @since 1.0.0
		 */
		public function woocommerce_get_product_from_item( $product, $item, $order ) {
			if( isset( $item['deposit'] ) && $item['deposit'] ){
				if( ! $product ){
					return $product;
				}

				yit_set_prop( $product, 'price', $item['deposit_value'] );
				
				if( apply_filters( 'yith_wcdp_virtual_on_deposit', true ) ) {
					yit_set_prop( $product, 'virtual', 'yes' );
				}

				if( apply_filters( 'yith_wcdp_not_downloadable_on_deposit', true ) ) {
					yit_set_prop( $product, 'downloadable', 'no' );
				}
			}

			return $product;
		}

		/**
		 * Trash suborders on parent order trashing
		 *
		 * @param $post_id int Trashed post id
		 * @return void
		 * @since 1.0.0
		 */
		public function trash_suborders( $post_id ) {
			$order = wc_get_order( $post_id );

			if( ! $order ){
				return;
			}

			$suborders = $this->get_suborder( $post_id );

			if( ! $suborders ){
				return;
			}

			foreach( $suborders as $suborder ){
				( method_exists( $suborder, 'delete' ) ) ? $suborder->delete() : wp_trash_post( $suborder );
			}
		}

		/**
		 * Restore suborders on parent order restoring
		 *
		 * @param $post_id int Restore post id
		 * @return void
		 * @since 1.0.0
		 */
		public function untrash_suborders( $post_id ) {
			$order = wc_get_order( $post_id );

			if( ! $order ){
				return;
			}

			$suborders = $this->get_suborder( $post_id );

			if( ! $suborders ){
				return;
			}

			foreach( $suborders as $suborder ){
				wp_untrash_post( $suborder );
			}
		}

		/**
		 * Let WooCommerce skip stock decreasing for suborders
		 *
		 * @param $skip bool Whether to perform or not stock decreasing
		 * @param $order \WC_Order Current order
		 * @return bool Filtered \$skip value
		 */
		public function skip_reduce_stock_on_suborders( $skip, $order ) {
			if( $this->is_suborder( yit_get_prop( $order, 'id' ) ) ){
				return false;
			}

			return $skip;
		}

		/**
		 * Set products as in stock if they're retrieved for a balance payment
		 *
		 * @param $product \WC_Product Currently retrieved product
		 * @param $item \WC_Order_Item_Product Current order item
		 * @return \WC_Product filtered product
		 */
		public function set_suborder_items_as_in_stock( $product, $item ) {
			$order_id = method_exists( $item, 'get_order_id' ) ? $item->get_order_id() : false;

			if( ( $order_id && $this->is_suborder( $order_id ) ) || isset( $item['full_payment_id'] ) ){
				$product->set_stock_status( 'instock' );
			}

			return $product;
		}

		/* === SUPPORT CART METHODS === */

		/**
		 * Create a support cart, used to temporarily replace actual cart and make shipping/tax calculation, suborders checkout
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function create_support_cart() {
			// save current cart
			$this->_cart = WC()->session->get( 'cart' );
			$this->_coupons = WC()->session->get( 'applied_coupons' );

			WC()->cart->empty_cart( true );
			WC()->cart->remove_coupons();
		}

		/**
		 * Restore original cart, saved in \YITH_WCDP_Suborders::_cart property
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function restore_original_cart() {
			// delete current cart
			WC()->cart->empty_cart( true );
			WC()->cart->remove_coupons();

			// reload cart

			/**
			 * Depending on where \YITH_WCDP_Suborders::create_support_cart() was called, \YITH_WCDP_Suborders::_cart property may be
			 * an instance of WC_Cart class, or an array of cart contents (results of a previous WC_Cart::get_cart_for_session() )
			 *
			 * instanceof prevents Fatal Error: method called on a non-object on single product pages, while
			 * WC_Cart::get_cart_for_session() avoid cart remaining empty after restore on process checkout
			 *
			 * @since 1.0.5
			 */
			WC()->session->set( 'cart', $this->_cart instanceof WC_Cart ? $this->_cart->get_cart_for_session() : $this->_cart );
			WC()->session->set( 'applied_coupons', $this->_coupons );

			WC()->cart->get_cart_from_session();

			/**
			 * Since we're sure cart has changed, let's force calculate_totals()
			 * Under some circumstances, not calculating totals at this point could effect WC()->cart->needs_payment() later,
			 * causing checkout process to redirect directly to Thank You page, instead of processing payment
			 *
			 * This was possibly caused by change in check performed at the end of get_cart_from_session() with WC 3.2
			 * Now conditions to recalculate totals after getting it from session are different then before
			 *
			 * @since 1.1.1
			 */
			WC()->cart->calculate_totals();
		}

		/* === HELPER METHODS === */

		/**
		 * Check if order identified by $order_id has suborders, and eventually returns them
		 *
		 * @param $order_id int Id of the order to check
		 * @return mixed Array of suborders, if any
		 * @since 1.0.0
		 */
		public function get_suborder( $order_id ) {
			global $wpdb;

			$suborder_ids = array();
			$parent_ids = (array) absint( $order_id );

			while ( ! empty( $parent_ids ) ){

				// todo: review code once WC switches to custom tables
				$parents_list = implode( ', ', $parent_ids );
				$parent_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} AS p 
                     LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
                     WHERE post_parent IN ({$parents_list}) 
                     AND post_type=%s 
                     AND meta_key=%s 
                     AND meta_value=%s ",
					'shop_order',
					'_created_via',
					'yith_wcdp_balance_order'
				) );

				$suborder_ids = array_merge( $suborder_ids, $parent_ids );
			}

			return $suborder_ids;
		}
		
		/**
		 * Returns post parent of a Full payment order
		 * If order is not a full payment order, it will return false
		 * 
		 * @param $order_id int Order id
		 * @return int|bool If order is full payment, and has post parent, returns parent ID; false otherwise
		 */
		public function get_parent_order( $order_id ) {
			$order = wc_get_order( $order_id );
			$has_full_payment = yit_get_prop( $order, '_has_full_payment' );

			if( ! $has_full_payment ){
				return false;
			}
			
			return yit_get_prop( $order, 'parent_id' );

		}

		/**
		 * Check if order identified by $order_id is a suborder (has post_parent)
		 *
		 * @param $order_id int Id of the order to check
		 * @return bool Whether order is a suborder or no
		 * @since 1.0.0
		 */
		public function is_suborder( $order_id ) {
			$order = wc_get_order( absint( $order_id ) );

			if( ! $order ){
				return false;
			}
			
			$post_parent = yit_get_prop( $order, 'parent_id', true );
			$created_via = yit_get_prop( $order, '_created_via', true );

			return $post_parent && 'yith_wcdp_balance_order' == $created_via;
		}

		/**
		 * Get parent orders
		 *
		 * @return \WP_Post[] Array of found orders
		 * @since 1.0.0
		 */
		public function get_parent_orders() {
			$customer_orders = yit_get_orders( apply_filters( 'yith_wcdp_add_parent_orders', array(
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => '_customer_user',
						'value' => get_current_user_id()
					),
					array(
						'key' => '_has_deposit'
					)
				),
				'post_type'   => wc_get_order_types( 'view-orders' ),
				'post_status' => array_keys( wc_get_order_statuses() ),
				'post_parent' => 0
			) ) );

			return $customer_orders;
		}

		/**
		 * Get child orders
		 *
		 * @return \WP_Post[] Array of found orders
		 * @since 1.0.0
		 */
		public function get_child_orders() {
			$customer_orders = get_posts( apply_filters( 'yith_wcdp_add_child_orders', array(
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => '_customer_user',
						'value' => get_current_user_id()
					),
					array(
						'key' => '_has_full_payment'
					)
				),
				'post_type'   => wc_get_order_types( 'view-orders' ),
				'post_status' => array_keys( wc_get_order_statuses() )
			) ) );

			return $customer_orders;
		}

		/* === MULTI VENDOR COMPATIBILITY === */

		/**
		 * Remove deposit ssuborders from Multi Vendor suborders list
		 *
		 * @param $suborder_ids mixed Multi Vendor suborders
		 * @param $parent_order_id int Parent order id
		 * @return mixed Array diff between Multi Vendor suborders and deposit suborders
		 * @since 1.0.4
		 */
		public function remove_deposit_suborder_from_multi_vendor( $suborder_ids, $parent_order_id ) {
			if( $parent_order_id && $suborder_ids ) {
				$deposit_suborder_ids = $this->get_suborder( $parent_order_id );
				if ( $deposit_suborder_ids ) {
					$suborder_ids = array_diff( $suborder_ids, $deposit_suborder_ids );
				}
			}

			return $suborder_ids;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_Suborders
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
 * Unique access to instance of YITH_WCDP_suborders class
 *
 * @return \YITH_WCDP_Suborders
 * @since 1.0.0
 */
function YITH_WCDP_Suborders(){
	return YITH_WCDP_Suborders::get_instance();
}