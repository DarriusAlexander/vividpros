<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Products
 * 
 * Access original fields: $mod_settings
 */
global $paged;

$fields_default = array(
	'mod_title_products' => '',
	'query_products' => 'all',
	'template_products' => 'list',
	'hide_free_products' => 'no',
	'layout_products' => 'list-post',
	'category_products' => '',
	'hide_child_products'=>false,
	'post_per_page_products' => 6,
	'offset_products' => 0,
	'order_products' => 'ASC',
	'orderby_products' => 'title',
	'description_products' => 'none',
	'hide_feat_img_products' => 'no',
	'image_size_products' => '',
	'img_width_products' => '',
	'img_height_products' => '',
	'unlink_feat_img_products' => 'no',
	'hide_post_title_products' => 'no',
	'unlink_post_title_products' => 'no',
	'hide_price_products' => 'no',
	'hide_add_to_cart_products' => 'no',
	'hide_rating_products' => 'no',
	'hide_sales_badge' => 'no',
	// slider settings
	'layout_slider' => '',
	'visible_opt_slider' => '',
	'auto_scroll_opt_slider' => 0,
	'scroll_opt_slider' => '',
	'speed_opt_slider' => '',
	'effect_slider' => 'scroll',
	'pause_on_hover_slider' => 'resume',
	'wrap_slider' => 'yes',
	'show_nav_slider' => 'yes',
	'show_arrow_slider' => 'yes',
	'left_margin_slider' => '',
	'right_margin_slider' => '',
	'hide_page_nav_products' => 'yes',
	'animation_effect' => '',
	'css_products' => '',
);

if ( isset( $mod_settings['category_products'] ) )	
	$mod_settings['category_products'] = $this->get_param_value( $mod_settings['category_products'] );

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );

$temp_terms = explode( ',', $category_products );
$terms = array();
$is_string = false;
foreach ( $temp_terms as $t ) {
	if ( ! is_numeric( $t ) )
		$is_string = true;
	if ( '' != $t ) {
		array_push( $terms, trim( $t ) );
	}
}
$tax_field = ( $is_string ) ? 'slug' : 'id';

$query_args = array(
	'post_type' => 'product',
	'posts_per_page' => $post_per_page_products,
	'order' => $order_products,
);
$paged = $this->get_paged_query();
$query_args['offset'] = ( ( $paged - 1 ) * $post_per_page_products ) + $offset_products;

$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
$query_args['meta_query']   = array_filter( $query_args['meta_query'] );

if ( count( $terms ) > 0 && ! in_array( '0', $terms ) ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field' => $tax_field,
			'terms' => $terms,
			'include_children'=>$hide_child_products=='yes'?false:true
		)
	);
}

if( $query_products == 'onsale' ) {
	$product_ids_on_sale = wc_get_product_ids_on_sale();
	$product_ids_on_sale[] = 0;
	$query_args['post__in'] = $product_ids_on_sale;
} elseif( $query_products == 'featured' ) {
	$query_args['meta_query'][] = array(
		'key'   => '_featured',
		'value' => 'yes'
	);
}

switch ( $orderby_products ) {
	case 'price' :
		$query_args['meta_key'] = '_price';
		$query_args['orderby']  = 'meta_value_num';
		break;
	case 'sales' :
		$query_args['meta_key'] = 'total_sales';
		$query_args['orderby']  = 'meta_value_num';
		break;
	default :
		$query_args['orderby']  = $orderby_products;
}

if ( $hide_free_products == 'yes' ) {
	$query_args['meta_query'][] = array(
		'key'     => '_price',
		'value'   => 0,
		'compare' => '>',
		'type'    => 'DECIMAL',
	);
}

$is_theme_template = false;
if( $template_products == 'list' && $this->is_loop_template_exist( 'query-product.php', 'includes' ) ) {
	$theme_layouts = apply_filters( 'builder_woocommerce_theme_layouts', array() );
	if( in_array( $layout_products, $theme_layouts ) ) { // check if the chosen layout is supported by the theme
		$is_theme_template = true;
	}
}

if( $is_theme_template ) {

	global $themify;
	$themify_save = clone $themify;

	// $themify->page_navigation = $hide_page_nav_products;
	$themify->page_navigation = 'yes'; // hide navigation links
	$themify->query_products = $query_args;
	$themify->post_layout = $layout_products;
	$themify->product_archive_show_short = $description_products;
	$themify->unlink_product_title = $unlink_post_title_products;
	$themify->hide_product_title = $hide_post_title_products;
	$themify->hide_product_image = $hide_feat_img_products;
	$themify->unlink_product_image = $unlink_feat_img_products;
	$themify->width = $img_width_products;
	$themify->height = $img_height_products;
	if ($this->is_img_php_disabled()){
            $themify->image_setting .= $image_size_products != '' ? 'image_size=' . $image_size_products . '&' : '';
	}
	

	if( 'yes' == $hide_add_to_cart_products ) {
		add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string' );
	}
	if( 'yes' == $hide_rating_products ) {
		add_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_no' );
	} else {
		// enable ratings despite the option configured in WooCommerce > Settings
		add_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_yes' );
	}
	if( 'yes' == $hide_sales_badge ) {
		add_filter( 'woocommerce_sale_flash', '__return_empty_string' );
	}
	if( 'yes' == $hide_price_products ) {
		add_filter( 'woocommerce_get_price_html', '__return_empty_string' );
	}
	$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );
	$container_class = implode(' ', 
		apply_filters( 'themify_builder_module_classes', array(
			'module', 'module-' . $mod_name, $module_ID, $css_products
		), $mod_name, $module_ID, $fields_args )
	);

	$container_props = apply_filters( 'themify_builder_module_container_props', array(
		'id' => $module_ID,
		'class' => $container_class
	), $fields_args, $mod_name, $module_ID );
	
	$this->add_post_class( $animation_effect );
	?>
	<div <?php echo $this->get_element_attributes( $container_props ); ?>>

		<?php if ( $mod_title_products != '' ): ?>
			<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_products, $fields_args ) ) . $mod_settings['after_title']; ?>
		<?php endif; ?>

		<?php do_action( 'themify_builder_before_template_content_render' ); ?>

		<?php get_template_part( 'includes/query-product' ); ?>
		
		<?php do_action( 'themify_builder_after_template_content_render' ); ?>
	</div>
	<?php
	// reset config
	$themify = clone $themify_save;

	remove_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string' );
	remove_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_no' );
	remove_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_yes' );
	remove_filter( 'woocommerce_sale_flash', '__return_empty_string' );
	remove_filter( 'woocommerce_get_price_html', '__return_empty_string' );

} else {
	// render the template
	$this->retrieve_template( 'template-'.$mod_name.'-'.$template_products.'.php', array(
		'module_ID' => $module_ID,
		'mod_name' => $mod_name,
		'query_args' => $query_args,
		'settings' => ( isset( $fields_args ) ? $fields_args : array() )
	), '', '', true );
}