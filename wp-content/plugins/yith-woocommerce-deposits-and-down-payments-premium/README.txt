=== YITH WooCommerce Deposits and Down Payments ===

Contributors: yithemes
Tags:  deposits, deposits and down payments, down payments, down payment, deposit, woocommerce deposits, woocommerce down payments, rate, amount, full payment, balance, backorder, sales, woocommerce, wp e-commerce
Requires at least: 4.0.0
Tested up to: 4.9.2
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Documentation: http://yithemes.com/docs-plugins/yith-woocommerce-deposits-and-down-payments

== Changelog ==

= 1.2.0 - Released: Jan, 31 - 2018 =

* New: WooCommerce 3.3.0 support
* New: Updated plugin-fw
* New: added nl_NL translation
* New: integration with YITH WooCommerce Composite Products
* New: added label with order total including balance in the cart
* Tweak: do not hide "Add deposit to Cart" form in variable product when variation handling is not available
* Tweak: moved Balances email sending to order completed action
* Tweak: filter order status on My Account page only
* Tweak: fixed Sold Individually behaviour when Deposit gets added to cart
* Fix: preventing Fatal Error: Called method get_order_id on a non-object for WC < 3.0
* Fix: notice when sending deposit email
* Fix: issue with Added to Cart message not appearing
* Fix: added an additional check to avoid js errors on single product page
* Dev: added yith_wcdp_disable_deposit_variation_option filter in order to disable per-variation handling when not required, and drastically improve performance for variable products

= 1.1.2 - Released: Nov, 06 - 2017 =

* New: added WC 3.2.1 compatibility
* Tweak: recalculate totals after restoring original cart (avoid checkout skipping the payment)
* Tweak: added procedure to disable deposit when removing category rule
* Tweak: plugin now shows prices including taxes when required
* Tweak: added checks over product before adding it to temporary cart
* Fix: error when retrieving products to enable for category deposit rule
* Fix: customer can now pay balance orders even if products are out of stock (stock handling is processed during deposit)
* Dev: added yith_wcdp_is_deposit_enabled_on_product filter, to let third party plugin filter is_deposit_enabled_on_product() return value
* Dev: added yith_wcdp_skip_support_cart filter, to let third party plugin avoid support cart processing
* Dev: added yith_wcdp_suborder_add_cart_item_data filter, to let third party plugin add cart item data during cart processing for suborders creation

= 1.1.1 - Released: Apr, 21 - 2017 =

* Tweak: update plugin-fw
* Tweak: optimized meta saving
* Tweak: avoid double "Deposit" or "Full Payment" label before order item name
* Fix: problem with duplicated meta
* Fix: variation rate when category rate is set
* Fix: problem with product's select on Deposit tab
* Dev: added yith_wcdp_disable_email_notification filter, to let disable balance email notifications

= 1.1.0 - Released: Apr, 03 - 2017 =

* New: WordPress 4.7.3 compatibility
* New: WooCommerce 3.0-RC2 compatibility
* New: option to change Deposit label on the frontend
* New: compatibility with YITH Dynamic Pricing and Discounts
* New: compatibility with YITH Event Tickets for WooCommerce
* New: Compatibility with YITH WooCommerce Product Addon
* New: Compatibility with YITH Pre Order for WooCommerce
* New: "Reset Data" handling for variation form on single product page
* New: deposit ID on "New Order" email
* New: improved wpml config to let admin correctly localize plugin labels
* Tweak: new text-domain
* Tweak: fixed downloads not appearing for "partially-paid" orders
* Tweak: fixed plugin when product has more then 30 variations
* Tweak: added check for product on deposit table, to avoid possible fatal errors when removing products from the store
* Tweak: added check over product when filtering get_product_from_item
* Tweak: added balance total to "Suborder" column in order page
* Fix: js error that was repeating #yith-wcdp-add-deposit-to-cart at each found_variation
* Fix: preventing warning on setting panel, when no shipping method is set
* Fix: possible notice due to undefined global $post
* Fix: possible notice when global $post is not an object
* Fix: WooCommerce decreasing stock both on Deposit and Balance orders
* Fix: problem with get_cart_from_session when using YITH Stripe and YITH Subscription
* Fix: js handling for "Shipping Calculator" on variable products
* Fix: Wrong deposits amount in admin email
* Fix: heading string for "My Deposits" section
* Dev: added yith_wcdp_not_downloadable_on_deposit filter to make deposit downloadable, when needed
* Dev: fixed yith_wcdp_deposit_value and yith_wcdp_deposit_balance filters (now they send variation_id and product_id as additional parameters to filter)

= 1.0.4 - Released: Oct, 10 - 2016 =

* Added: compatibility with variable products
* Added: filter yith_wcdp_skip_cart_item_processing to let dev skip add deposit to cart programmatically
* Added: YITH_WCDP_PROCESS_SUBORDERS constant to avoid suborder with deposit when deposit is forced
* Added: compatibility for shipping zones
* Added: compatibility with YITH WooCommerce PDF Invoice premium
* Added: compatibility with YITH WooCommerce Booking
* Added: option to choose whether deposit should be checked as default or not
* Added: get_deposit method, to get deposit value for a specific product/variation/user/price
* Tweak: changed plugin text domain to yith-woocommerce-deposits-and-down-payments
* Tweak: made plugin work with [product_page] woocommerce shortcode

= 1.0.3 - Released: Jun, 13 - 2016 =

* Added: WooCommerce 2.6-RC1 compatibility
* Added: yith_wcdp_deposit_label filter to change deposit label
* Added: yith_wcdp_full_payment_label filter to change full amount label
* Added: yith_wcdp_process_deposit to let third party plugin to prevent plugin from processing deposits for some products
* Added: yith_wcdp_propagate_coupons to let coupons be applied to suborders
* Added: yith_wcdp_virtual_on_deposit to let third party plugin make deposits product not virtual
* Added: function yith_wcdp_get_order_subtotal

= 1.0.2 - Released: May, 02 - 2016 =

* Added: support for WordPress 4.5.1
* Added: support for WooCommerce 2.5.5
* Added: capability for the user to regenerate shipping methods basing on shipping address in single product page
* Added: compatibility with YITH WooCommerce Bulk Product Editing premium
* Added: Quick / Bulk deposit options edit for products
* Added: handling for custom product type
* Added: global option for "Create Suborders"
* Tweak: Passed product variable to templates, avoiding global variable usage
* Tweak: added qty calculation on "Full Amount" / "Down payment"
* Fixed: email templates for WooCommerce 2.5
* Fixed: plugin changing internal pointer of item array in backend order page
* Fixed: YITH Plugins view id (preventing assets to load on admin plugin settings page)

= 1.0.1 - Released: Dec, 01 - 2015 =

* Initial release