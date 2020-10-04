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

		echo implode( ' | ', $suborder_names ) . ' - ' . sprintf( get_woocommerce_price_format(), '', $suborder->get_total() ). "\n";
		echo __( 'Payment url:', 'yith-woocommerce-deposits-and-down-payments' ) . " " . $suborder->get_checkout_payment_url() . "\n\n";
	endforeach;
endif;
?>