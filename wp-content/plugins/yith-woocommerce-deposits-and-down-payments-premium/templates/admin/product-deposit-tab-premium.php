<?php
/**
 * Product deposit option tab
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
?>

<div id="yith_wcdp_deposit_tab" class="panel woocommerce_options_panel">
	<div class="options_group">
		<p class="form-field _enable_deposit">
			<label for="_enable_deposit"><?php _e( 'Enable deposit', 'yith-woocommerce-deposits-and-down-payments' ) ?></label>
			<input type="radio" class="enable_deposit" name="_enable_deposit" value="default" <?php checked( ( $enable_deposit == 'default' ) || empty( $enable_deposit ) ) ?> /> <?php _e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ) ?> <img class="help_tip" data-tip="<?php _e( 'Check this option to enable payment of deposit for this product', 'yith-woocommerce-deposits-and-down-payments' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16"><br/>
			<input type="radio" class="enable_deposit" name="_enable_deposit" value="yes" <?php checked( $enable_deposit, 'yes' ) ?> /> <?php _e( 'Yes', 'yith-woocommerce-deposits-and-down-payments' ) ?><br/>
			<input type="radio" class="enable_deposit" name="_enable_deposit" value="no" <?php checked( $enable_deposit, 'no' ) ?> /> <?php _e( 'No', 'yith-woocommerce-deposits-and-down-payments' ) ?><br/>
		</p>
	</div>
	<div class="options_group">
		<p class="form-field _deposit_default">
			<label for="_deposit_default"><?php _e( 'Deposit checked?', 'yith-woocommerce-deposits-and-down-payments' ) ?></label>
			<span><input type="radio" class="deposit_default" name="_deposit_default" value="default" <?php checked( ( $deposit_default == 'default' ) || empty( $force_deposit ) ) ?>/> <?php _e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ) ?></span><br/>
			<span><input type="radio" class="deposit_default" name="_deposit_default" value="yes" <?php checked( $deposit_default, 'yes' ) ?>/> <?php _e( 'Yes', 'yith-woocommerce-deposits-and-down-payments' ) ?></span><br/>
			<span><input type="radio" class="deposit_default" name="_deposit_default" value="no" <?php checked( $deposit_default, 'no' ) ?>/> <?php _e( 'No', 'yith-woocommerce-deposits-and-down-payments' ) ?></span>
		</p>
		<p class="form-field _force_deposit">
			<label for="_force_deposit"><?php _e( 'Accept or force deposit', 'yith-woocommerce-deposits-and-down-payments' ) ?></label>
			<span><input type="radio" class="force_deposit" name="_force_deposit" value="default" <?php checked( ( $force_deposit == 'default' ) || empty( $force_deposit ) ) ?>/> <?php _e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ) ?></span><br/>
			<span><input type="radio" class="force_deposit" name="_force_deposit" value="yes" <?php checked( $force_deposit, 'yes' ) ?>/> <?php _e( 'Force deposit', 'yith-woocommerce-deposits-and-down-payments' ) ?></span><br/>
			<span><input type="radio" class="force_deposit" name="_force_deposit" value="no" <?php checked( $force_deposit, 'no' ) ?>/> <?php _e( 'Allow deposit', 'yith-woocommerce-deposits-and-down-payments' ) ?></span>
		</p>
		<p class="form-field _create_balance_orders">
			<label for="_enable_full_payment"><?php _e( 'Let users pay balances online', 'yith-woocommerce-deposits-and-down-payments' ) ?></label>
			<span><input type="radio" class="create_balance_orders" name="_create_balance_orders" value="default" <?php checked( ( $create_balance_orders == 'default' ) || empty( $create_balance_orders ) ) ?>/> <?php _e( 'Default', 'yith-woocommerce-deposits-and-down-payments' ) ?></span><br/>
			<span><input type="radio" class="create_balance_orders" name="_create_balance_orders" value="yes" <?php checked( $create_balance_orders, 'yes' ) ?>/> <?php _e( 'Let users pay the balance online (pending payment)', 'yith-woocommerce-deposits-and-down-payments' ) ?></span><br/>
			<span><input type="radio" class="create_balance_orders" name="_create_balance_orders" value="no" <?php checked( $create_balance_orders, 'no' ) ?>/> <?php _e( 'Customers will pay the balance using other means (on hold)', 'yith-woocommerce-deposits-and-down-payments' ) ?></span>
		</p>
		<p class="form-field _product_note">
			<label for="_product_note"><?php _e( 'Additional product notes', 'yith-woocommerce-deposits-and-down-payments' ) ?></label>
			<textarea name="_product_note" id="_product_note" cols="30" rows="10"><?php echo esc_html( $product_note ) ?></textarea>
			<img class="help_tip" data-tip="<?php _e( 'This option overrides general option set in deposit panel; note location can be selected on plugin panel', 'yith-woocommerce-deposits-and-down-payments' ) ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
		</p>
	</div>
</div>