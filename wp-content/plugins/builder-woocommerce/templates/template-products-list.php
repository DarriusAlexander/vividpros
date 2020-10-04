<?php
/**
 * @var $query_args the query parameters set by the module
 * @var $settings module config
 */

extract( $settings );

$animation_effect = $this->parse_animation_effect( $animation_effect, $settings );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $css_products
	), $mod_name, $module_ID, $settings )
);

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $settings, $mod_name, $module_ID );

$this->add_post_class( $animation_effect );
?>

<!-- module products -->
<div <?php echo $this->get_element_attributes( $container_props ); ?>>
	<div class="woocommerce">

		<?php if ( $mod_title_products != '' ): ?>
			<?php echo $settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_products, $fields_args ) ) . $settings['after_title']; ?>
		<?php endif; ?>

		<?php do_action( 'themify_builder_before_template_content_render' ); ?>

		<?php
		global $post;
		$builder_wc_temp_post = $post;
		$query = new WP_Query( $query_args );
		if( $query->have_posts() ) : ?>

			<div class="wc-products <?php echo $layout_products; ?>">

			<?php while( $query->have_posts() ) : $query->the_post(); ?>

				<div id="product-<?php the_ID(); ?>" <?php post_class("post product clearfix"); ?>>

					<?php
					if ( $hide_feat_img_products != 'yes' ) {
						$width = $img_width_products;
						$height = $img_height_products;
						$param_image = 'w='.$width .'&h='.$height.'&ignore=true';
						if ( $this->is_img_php_disabled() ) 
							$param_image .= $image_size_products != '' ? '&image_size=' . $image_size_products : '';

						if( $post_image = themify_get_image( $param_image ) ) {
						?>
							
							<figure class="post-image">
								<?php if ( $unlink_feat_img_products == 'yes' ): ?>
									<?php echo $post_image; ?>
								<?php else: ?>
									<a href="<?php echo the_permalink(); ?>"><?php echo $post_image; ?></a>
								<?php endif; ?>
							</figure>
						<?php } 
					}
					?>

					<div class="post-content">

						<?php if( $hide_sales_badge != 'yes' ) : ?>
							<?php woocommerce_show_product_loop_sale_flash(); ?>
						<?php endif; ?>

						<?php if ( $hide_post_title_products != 'yes' ) : ?>
							<?php if ( $unlink_post_title_products == 'yes' ) : ?>
								<h3><?php the_title(); ?></h3>
							<?php else: ?>
								<h3><a href="<?php echo the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
							<?php endif; //unlink product title ?>
						<?php endif; //product title ?>    

						<?php
						if( $hide_rating_products != 'yes' ) {
							woocommerce_template_loop_rating();
						} // product rating

						if( $hide_price_products != 'yes' ) {
							woocommerce_template_loop_price();
						} // product price

						if( 'none' != $description_products ) {
							if( $description_products == 'short' ) {
								woocommerce_template_single_excerpt();
							} else {
								the_content();
							}
						} // product description

						if( $hide_add_to_cart_products != 'yes' ) {
							echo '<p class="add-to-cart-button">';
							woocommerce_template_loop_add_to_cart();
							echo '</p>';
						} // product add to cart
						?>

						<?php edit_post_link(__('Edit', 'themify'), '[', ']'); ?>

					</div><!-- /.post-content -->
					
				</div><!-- product-<?php the_ID(); ?> -->

			<?php endwhile; wp_reset_postdata(); $post = $builder_wc_temp_post; unset( $builder_wc_temp_post ); ?>

			</div>

		<?php endif; ?>

		<?php if( 'no' == $hide_page_nav_products ) {
			echo $this->get_pagenav( '', '', $query, $offset_products );
		} ?>

		<?php do_action( 'themify_builder_after_template_content_render' ); $this->remove_post_class( $animation_effect ); ?>
	</div><!-- .woocommerce -->
</div>
<!-- /module products -->