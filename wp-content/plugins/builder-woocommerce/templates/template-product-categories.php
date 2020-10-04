<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Product Categories
 * 
 * Access original fields: $mod_settings
 */
global $woocommerce_loop;

$fields_default = array(
	'mod_title' => '',
	'child_of' => 0,
	'columns' => 4,
	'orderby' => 'name',
	'order' => 'ASC',
	'hide_empty' => 'yes',
	'pad_counts' => 'yes',
	'latest_products' => 0,
	'animation_effect' => '',
	'css_products' => '',
);
$args = wp_parse_args( $mod_settings, $fields_default );
extract( $args );
$animation_effect = $this->parse_animation_effect( $animation_effect );

$hide_empty = ( $hide_empty == 'yes' ) ? 1 : 0;

// get terms and workaround WP bug with parents/pad counts
$args = array(
	'orderby'    => $orderby,
	'order'      => $order,
	'hide_empty' => $hide_empty,
	'pad_counts' => true,
);
if( 0 != $child_of ) {
	$args['child_of'] = $child_of;
} elseif( 'top-level' == $child_of ) {
	$args['parent'] = 0; /* show only top-level terms */
}

// check if we have to query the slug, instead of ID
// keep option to query by ID, for backward compatibility
if( 'top-level' !== $child_of && preg_match( '/\D/', $child_of ) ) {
	$term = get_term_by( 'slug', $child_of, 'product_cat' );
	if( ! is_wp_error( $term ) ) {
		$child_of = $args['child_of'] = $term->term_id;
	}
}

$product_categories = get_terms( 'product_cat', $args );

if(empty($product_categories) && 'top-level' !== $child_of &&  0 != $child_of ){
    $args['child_of'] = false;
    $args['term_taxonomy_id'] = $child_of;
    $product_categories = get_terms( 'product_cat', $args );
}

if ( $hide_empty ) {
	foreach ( $product_categories as $key => $category ) {
		if ( $category->count == 0 ) {
			unset( $product_categories[ $key ] );
		}
	}
}

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $css_products, $animation_effect
	), $mod_name, $module_ID, $args )
);

$woocommerce_loop['columns'] = $columns;
?>
<!-- module product categories -->
<div id="<?php echo $module_ID; ?>" class="<?php echo esc_attr( $container_class ); ?>">

	<?php if ( $mod_title != '' ): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title, $args ) ) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<div class="woocommerce columns-<?php echo $columns; ?>">

		<?php
		// Reset loop/columns globals when starting a new loop
		$woocommerce_loop['loop'] = $woocommerce_loop['column'] = '';

		if ( $product_categories ) {

			woocommerce_product_loop_start();

			foreach ( $product_categories as $category ) {

				// Store column count for displaying the grid
				if ( empty( $woocommerce_loop['columns'] ) )
					$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

				// Increase loop count
				$woocommerce_loop['loop']++;
				?>
				<li class="product-category product<?php
					if ( ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] == 0 || $woocommerce_loop['columns'] == 1 )
						echo ' first';
					if ( $woocommerce_loop['loop'] % $woocommerce_loop['columns'] == 0 )
						echo ' last';
					?>">

					<?php woocommerce_template_loop_category_link_open( $category ); ?>
						<?php woocommerce_subcategory_thumbnail( $category ); ?>
					<?php woocommerce_template_loop_category_link_close(); ?>

					<?php
					if( 0 != $latest_products ) {
						$query = get_posts( array( 'post_type' => 'product', 'posts_per_page' => $latest_products, 'product_cat' => $category->slug ) );
						if( ! empty( $query ) ) : ?>
							<div class="product-thumbs">
								<?php foreach( $query as $product ) : ?>
									<div class="post">
										<a href="<?php echo get_permalink( $product->ID ); ?>">
											<?php echo get_the_post_thumbnail( $product->ID, 'shop_catalog' ); ?>
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						<?php
						endif;
					}
					?>

					<?php woocommerce_template_loop_category_link_open( $category ); ?>
						<h3>
							<?php
								echo $category->name;

								if ( 'yes' == $pad_counts && $category->count > 0 )
									echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . $category->count . ')</mark>', $category );
							?>
						</h3>
					<?php woocommerce_template_loop_category_link_close(); ?>

				</li>
			<?php
			}

			woocommerce_product_loop_end();
		}

		woocommerce_reset_loop();
		?>

	</div>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- module product categories -->