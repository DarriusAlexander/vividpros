<?php
/**
 * My deposit template
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Deposits and Down Payments
 * @version 1.0.1
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

?>

<div id="yith_wcdp_deposits_details" class="yith-wcdp-my-deposits">
	<h2><?php echo apply_filters( 'yith_wcdp_my_deposit_title', sprintf( __( '%s Details', 'yith-woocommerce-deposits-and-down-payments' ), apply_filters( 'yith_wcdp_deposit_label', __( 'Deposit', 'yith-woocommerce-deposits-and-down-payments' ) ) ) ); ?></h2>	<p>
		<?php echo apply_filters( 'yith_wcdp_my_deposit_text', sprintf( __(  'Some products in this order have been bought with %s. In order to complete the transaction and to ship the products, all remaining amounts have to be paid. Here follow details with owned balance for items in this order:', 'yith-woocommerce-deposits-and-down-payments' ), strtolower( apply_filters( 'yith_wcdp_deposit_label', __( 'Deposit', 'yith-woocommerce-deposits-and-down-payments' ) ) ) ) ) ?>	</p>
	<?php do_action( 'yith_wcdp_before_my_deposits_table', $order_id ) ?>
	<table class="shop_table order_details">
		<thead>
		<tr>
			<th class="product-name"><?php _e( 'Product', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="order-status"><?php _e( 'Status', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="order-paid"><?php _e( 'Paid', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="order-to-pay"><?php _e( 'To be paid', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="order-actions">&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<?php if( ! empty( $deposits ) ): ?>
			<?php foreach( $deposits as $deposit ): ?>
				<tr>
					<td class="product-name">
						<a href="<?php echo esc_url( $deposit['product_url'] ) ?>"><?php echo $deposit['product_name'] ?></a>
					</td>
					<td class="order-status"><?php echo wc_get_order_status_name( $deposit['order_status'] ) ?></td>
					<td class="order-paid">
						<?php echo wc_price( $deposit['order_paid'] ) ?>
						<?php if( apply_filters( 'yith_wcdp_print_paid_details', true, $order_id ) ): ?>
							<div class="details">
								<small><?php _e( 'Subtotal: ', 'yith-woocommerce-deposits-and-down-payments' ) ?> <?php echo wc_price( $deposit['order_subtotal'] ) ?></small>
								<br>
								<small><?php _e( 'Discount: ', 'yith-woocommerce-deposits-and-down-payments' ) ?> <?php echo wc_price( $deposit['order_discount'] ) ?></small>
							</div>
					<?php endif; ?>
					</td>
					<td class="order-to-pay"><?php echo wc_price( $deposit['order_to_pay'] ) ?></td>
					<td class="order-actions">
						<?php
						if ( $deposit['actions'] ) {
							foreach ( $deposit['actions'] as $key => $action ) {
								echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
							}
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
	<?php do_action( 'yith_wcdp_after_my_deposits_table', $order_id ) ?>
</div>