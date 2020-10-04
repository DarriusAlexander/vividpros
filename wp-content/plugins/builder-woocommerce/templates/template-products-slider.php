<?php
/**
 * @var $query_args the query parameters set by the module
 * @var $settings module config
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
    $GLOBALS['ThemifyBuilder']->load_templates_js_css( array( 'carousel' => true, 'module-plugins' => true ) );
}

extract( $settings );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, 'module-slider', $module_ID, 'themify_builder_slider_wrap', 'clearfix', $layout_slider, $css_products, $animation_effect
	), $mod_name, $module_ID, $settings )
);

$visible = $visible_opt_slider;
$scroll = $scroll_opt_slider;
$auto_scroll = $auto_scroll_opt_slider;
$arrow = $show_arrow_slider;
$pagination = $show_nav_slider;
$slide_margins = array();
$slide_margins[] = !empty( $left_margin_slider ) ? sprintf( 'margin-left:%spx;', $left_margin_slider ) : '';
$slide_margins[] = !empty( $right_margin_slider ) ? sprintf( 'margin-right:%spx;', $right_margin_slider ) : '';
$wrapper = $wrap_slider;
$effect = $effect_slider;

switch ( $speed_opt_slider ) {
	case 'slow':
		$speed = 4;
	break;
	
	case 'fast':
		$speed = '.5';
	break;

	default:
	 $speed = 1;
	break;
}
?>

<!-- module products slider -->
<div id="<?php echo $module_ID; ?>-loader" class="themify_builder_slider_loader" style="<?php echo !empty($img_height_products) ? 'height:'.$img_height_products.'px;' : 'height:50px;'; ?>"></div>
<div id="<?php echo $module_ID; ?>" class="<?php echo esc_attr( $container_class ); ?>">
	<div class="woocommerce">

		<?php if ( $mod_title_products != '' ): ?>
			<?php echo $settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_products, $fields_args ) ) . $settings['after_title']; ?>
		<?php endif; ?>

		<ul class="themify_builder_slider" 
			data-id="<?php echo esc_attr( $module_ID ); ?>" 
			data-visible="<?php echo esc_attr( $visible ); ?>" 
			data-scroll="<?php echo esc_attr( $scroll ); ?>" 
			data-auto-scroll="<?php echo esc_attr( $auto_scroll ); ?>"
			data-speed="<?php echo esc_attr( $speed ); ?>"
			data-wrapper="<?php echo esc_attr( $wrapper ); ?>"
			data-arrow="<?php echo esc_attr( $arrow ); ?>"
			data-pagination="<?php echo esc_attr( $pagination ); ?>"
			data-effect="<?php echo esc_attr( $effect ); ?>" 
			data-pause-on-hover="<?php echo esc_attr( $pause_on_hover_slider ); ?>" >

		<?php do_action( 'themify_builder_before_template_content_render' ); ?>

		<?php
		global $post;
		$builder_wc_temp_post = $post;
		$query = new WP_Query( $query_args );
		if( $query->have_posts() ) : while( $query->have_posts() ) : $query->the_post(); ?>

			<li>
				<div class="slide-inner-wrap" style="<?php echo implode( '', $slide_margins ); ?>">
					<?php
						$unlink_feat = $unlink_feat_img_products == 'yes' ? true : false;
						$param_image = 'w='.$img_width_products .'&h='.$img_height_products.'&ignore=true';
						$attr_link_target = false;
						// $attr_link_target = 'yes' == $open_link_new_tab_slider ? ' target="_blank"' : '';
						if( $this->is_img_php_disabled() ) 
							$param_image .= $image_size_products != '' ? '&image_size=' . $image_size_products : '';

						if( $hide_feat_img_products != 'yes' && $post_image = themify_get_image( $param_image ) ) { ?>
							<figure class="slide-image">
								<?php if( $unlink_feat_img_products == 'yes' ): ?>
									<?php echo $post_image; ?>
								<?php else: ?>
									<a href="<?php the_permalink(); ?>" title="<?php echo the_title_attribute('echo=0'); ?>"<?php echo $attr_link_target; ?>><?php echo $post_image; ?></a>
								<?php endif; ?>
							</figure>
						<?php } // product image ?>

					<div class="slide-content">

						<?php if( $hide_sales_badge != 'yes' ) : ?>
							<?php woocommerce_show_product_loop_sale_flash(); ?>
						<?php endif; ?>

						<?php if( $hide_post_title_products != 'yes' ): ?>
							<?php if( $unlink_post_title_products == 'yes' ): ?>
								<h3><?php the_title(); ?></h3>
							<?php else: ?>
								<h3><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"<?php echo $attr_link_target; ?>><?php the_title(); ?></a></h3>
							<?php endif; //unlink post title ?>
						<?php endif; // hide post title ?>

						<?php
						if( $hide_rating_products != 'yes' ) {
							woocommerce_template_loop_rating();
						} // product rating

						if( $hide_price_products != 'yes' ) {
							woocommerce_template_loop_price();
						} // product price

						if( $hide_add_to_cart_products != 'yes' ) {
							echo '<p class="add-to-cart-button">';
							woocommerce_template_loop_add_to_cart();
							echo '</p>';
						} // product add to cart
						?>

						<?php if( $description_products == 'short' ) {
							woocommerce_template_single_excerpt();
						} elseif( $description_products == 'full' ) {
							the_content();
						}
						?>
					</div><!-- /slide-content -->
				</div>
			</li>

		<?php endwhile; wp_reset_postdata(); $post = $builder_wc_temp_post; unset( $builder_wc_temp_post ); ?>
		<?php endif; ?>

		<?php do_action( 'themify_builder_after_template_content_render' ); ?>

		</ul>
	</div><!-- .woocommerce -->
</div>
<!-- /module products slider -->