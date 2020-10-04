<?php
/**
 * WooCommerce Custom Hook
 * woocommerce-hooks.php
 */

// include plugin functions
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/* Single product actions */
add_action( 'themify_single_product_price', 'woocommerce_template_single_price', 10);
add_action( 'template_redirect', 'themify_single_product_related_products', 12);
add_action( 'template_redirect', 'themify_hide_shop_features', 12);
add_filter( 'woocommerce_product_tabs', 'themify_single_product_reviews' );
if(themify_check('setting-product_reviews')) {
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
}else if(themify_check('setting-product_reviews_empty')){
		remove_action('woocommerce_single_product_summary','woocommerce_template_single_rating', 10 );
		add_action('woocommerce_single_product_summary','themify_single_product_rating', 10 );
}

/* Single product on lightbox actions */
add_action( 'themify_single_product_image_ajax', 'woocommerce_show_product_sale_flash', 20);
add_action( 'themify_single_product_image_ajax', 'woocommerce_show_product_images', 20);
add_action( 'themify_single_product_ajax_content', 'woocommerce_template_single_add_to_cart', 10);
if(isset($_GET['ajax']) && $_GET['ajax']) {
	add_filter('woocommerce_single_product_image_html', 'themify_product_image_ajax', 10, 2);
	add_filter('woocommerce_single_product_image_thumbnail_html', create_function('', "return '';"));
}
add_filter('woocommerce_single_product_image_html', 'themify_product_image_single', 10, 2);

/* Sorting menu */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
add_action( 'woocommerce_before_shop_loop', 'themify_catalog_ordering', 8 );

// Remove breadcrumb for later insertion within Themify wrapper
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// Remove dock item hooks
add_action( 'init', 'themify_update_cart_action');

/* Content Wrapper */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action( 'woocommerce_before_main_content', 'themify_before_shop_content', 20);
add_action( 'woocommerce_after_main_content', 'themify_after_shop_content', 20);

/* Sidebar */
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
add_action('template_redirect', 'themify_woocommerce_sidebar_layout', 12);

// Show excerpt or content in product archive pages
add_action('woocommerce_after_shop_loop_item', 'themify_after_shop_loop_item');
// Set WC image sizes
add_action( 'switch_theme', 'themify_theme_delete_image_sizes_flag' );

// Add to cart link
add_filter('woocommerce_loop_add_to_cart_link', 'themify_loop_add_to_cart_link', 10, 3);
// No product title in product archive pages
add_filter('the_title', 'themify_no_product_title');
// No product price in product archive pages
add_filter('woocommerce_get_price_html', 'themify_no_price');
// Set number of products shown in product archive pages
add_filter('loop_shop_per_page', 'themify_products_per_page');
// Alter or remove success message after adding to cart with ajax.
$cart_message_hook = 'wc_add_to_cart_message';
$cart_message_hook .= version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ? '_html' : '';
add_filter( $cart_message_hook, 'themify_theme_wc_add_to_cart_message' );

// Hide Add to Cart Button
if( themify_get('setting-product_archive_hide_cart_button') == 'yes' ) {
	add_filter( 'woocommerce_loop_add_to_cart_link', '__return_false' );
}

if ( themify_check('setting-hide_product_rating_stars') ) {
	// Disable rating in archive pages
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
}else if(themify_check('setting-products_reviews_empty')){
	// Always show rating even for 0 rating
	add_filter( 'woocommerce_product_get_rating_html', 'themify_wc_product_get_rating_html', 10, 3 );
}

/**
 * Fragments
 * Adding cart total and shopdock markup to the fragments
 */
$cart_fragments_hook = version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' )
	? 'woocommerce_add_to_cart_fragments' : 'add_to_cart_fragments';
add_filter( $cart_fragments_hook, 'themify_theme_add_to_cart_fragments' );

/**
 * Theme delete cart hook
 * Note: for Add to cart using default WC function
 */
add_action( 'wp_ajax_theme_delete_cart', 'themify_theme_woocommerce_delete_cart' );
add_action( 'wp_ajax_nopriv_theme_delete_cart', 'themify_theme_woocommerce_delete_cart' );

/**
 * Theme adding cart hook
 * Adding cart ajax on single product page
 */
add_action( 'wp_ajax_theme_add_to_cart', 'themify_theme_woocommerce_add_to_cart' );
add_action( 'wp_ajax_nopriv_theme_add_to_cart', 'themify_theme_woocommerce_add_to_cart' );

/**
 * WC Plugins compliance 
 */
// Dynamic Gallery Plugin
if ( is_plugin_active( 'woocommerce-dynamic-gallery/wc_dynamic_gallery_woocommerce.php' ) ) {
	remove_action( 'themify_single_product_image', 'woocommerce_show_product_images', 20);
	remove_action( 'themify_single_product_image', 'woocommerce_show_product_thumbnails', 20);
}

if( ! is_admin() || !empty($_POST['wpf']) ) {
	add_filter( 'woocommerce_product_get_image', 'themify_woocommerce_product_get_image', 9, 3 );
}
if('custom' === themify_get( 'setting-product_single_image_size' )){
	add_filter( 'woocommerce_gallery_image_size', 'themify_wc_single_product_image_size' );
	add_filter( 'woocommerce_gallery_image_html_attachment_image_params', 'themify_wc_single_product_image_attrs' );
}

/**
 * Specific for infinite scroll themes
 */

if( 'infinite' === themify_get('setting-more_posts') ){
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
	remove_action( 'woocommerce_after_shop_loop', 'themify_theme_shop_pagination', 10 );
	function themify_shop_infinite_scroll() {
		$is_wc_shortcode = wc_get_loop_prop( 'is_shortcode' );
		if ( $is_wc_shortcode ) {
			woocommerce_pagination();
		} else {
			get_template_part( 'includes/pagination');
		}
	}
	add_action('woocommerce_after_shop_loop', 'themify_shop_infinite_scroll', 9 );
}

if(themify_check('setting-hide_shop_products')) {
	add_action( 'pre_get_posts', 'themify_hide_shop_products' );
}
