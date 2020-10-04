<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border: none;" border="1">
	<tbody>
	<?php
	$items = $parent_order->get_items( 'line_item' );
	$total_paid = 0;
	$total_to_pay = 0;

	if( ! empty( $items ) ):
		foreach( $items as $item_id => $item ):
			if( ! isset( $item['deposit'] ) || ! $item['deposit'] ) {
				continue;
			}

			$product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : $parent_order->get_product_from_item( $item );
			$suborder = wc_get_order( $item['full_payment_id'] );
			$suborder_items = $suborder->get_items( 'line_item' );
			$suborder_names = array();

			if( ! $product || ! $suborder || ! $suborder_items || in_array( $suborder->get_status(), array( 'completed', 'processing', 'cancelled' ) ) ){
				continue;
			}

			foreach( $suborder_items as $suborder_item ){
				$suborder_names[] = $suborder_item['name'];
			}

			$paid = $parent_order->get_item_total( $item, true );
			$paid += in_array( $suborder->get_status(), array( 'processing', 'completed' ) ) ? $suborder->get_total() : 0;
			$to_pay = in_array( $suborder->get_status(), array( 'processing', 'completed' ) ) ? 0 : $suborder->get_total();

			$total_paid += $paid;
			$total_to_pay += $to_pay;
			?>
			<tr>
				<td class="td" style="width: 60%; text-align:left; vertical-align:middle; border: none; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word; font-size: 30px; line-height: 1.2;">
					<?php echo implode( ' | ', $suborder_names ) ?>
					<small style="font-size: 17px;"> - <?php echo wc_price( $suborder->get_total() ) ?></small>
				</td>
				<td class="td" style="width: 40%; vertical-align:middle; border: none; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word; text-align: right;">
					<a href="<?php echo $suborder->get_checkout_payment_url() ?>" style="display: inline-block; background-color: #ebe9eb; color: #515151; white-space: nowrap; padding: .618em 1em; border-radius: 3px; text-decoration: none;"><?php _e( 'Pay now!', 'yith-woocommerce-deposits-and-down-payments' ) ?></a>
				</td>
			</tr>
			<?php
		endforeach;
	endif;
	?>
	</tbody>
</table>