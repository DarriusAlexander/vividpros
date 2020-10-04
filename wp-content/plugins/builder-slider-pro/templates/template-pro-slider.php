<?php
if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * Template Field Types
 * 
 * Access original fields: $mod_settings
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
	$GLOBALS['ThemifyBuilder']->load_templates_js_css();
}
wp_enqueue_script( 'builder-slider-pro' );

/* set the default options for the module */
$fields_default = array(
	'mod_title_slider' => '',
	'builder_ps_triggers_position' => 'standard',
	'builder_ps_triggers_type' => 'circle',
	'builder_ps_aa' => 'off',
	'builder_ps_hover_pause' => 'pause',
	'builder_ps_timer' => 'no',
	'builder_ps_width' => '',
	'builder_ps_height' => '',
	'builder_ps_thumb_width' => 30,
	'builder_ps_thumb_height' => 30,
	'builder_slider_pro_slides' => array(),
	'my_text_option' => '',
);
$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args );

$container_class = implode( ' ',
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, 'pager-' . $builder_ps_triggers_position, 'pager-type-' . $builder_ps_triggers_type
	), $mod_name, $module_ID, $fields_args )
);
if( empty( $builder_slider_pro_slides ) ) {
	return;
}

/* default options for each slide */
$slide_defaults = array(
	'builder_ps_slide_type' => '',
	'builder-ps-bg-image' => '',
	'builder_ps_tranzition' => 'slideTop',
	'builder_ps_layout' => 'bsp-slide-content-left',
	'builder_ps_tranzition_duration' => 'normal',
	'builder-ps-bg-color' => '',
	'builder-ps-slide-image' => '',
	'builder_ps_heading' => '',
	'builder_ps_text' => '',
	'builder_ps_text_color' => '',
	'builder_ps_text_link_color' => '',
	'builder_ps_button_text' => '',
	'builder_ps_button_link' => '',
	'builder_ps_button_icon' => '',
	'builder_ps_h3s_timer' => 'shortTop',
	'builder_ps_h3s_tranzition_duration' => 'normal',
	'builder_ps_h3e_timer' => 'shortTop',
	'builder_ps_h3e_tranzition_duration' => 'normal',
	'builder_ps_ps_timer' => 'shortTop',
	'builder_ps_ps_tranzition_duration' => 'normal',
	'builder_ps_pe_timer' => 'shortTop',
	'builder_ps_pe_tranzition_duration' => 'normal',
	'builder_ps_as_timer' => 'shortTop',
	'builder_ps_as_tranzition_duration' => 'normal',
	'builder_ps_ae_timer' => 'shortTop',
	'builder_ps_ae_tranzition_duration' => 'normal',
	'builder_ps_imgs_timer' => 'shortTop',
	'builder_ps_imgs_tranzition_duration' => 'normal',
	'builder_ps_imge_timer' => 'shortTop',
	'builder_ps_imge_tranzition_duration' => 'normal',
	'builder_ps_button_color' => '',
	'builder_ps_button_bg' => '',
);

/* setup element transition fallbacks */
$timer_translation = array(
	'shortTop' => 'up',
	'shortTopOut' => 'up',
	'longTop' => 'up',
	'longTopOut' => 'up',
	'shortLeft' => 'left',
	'shortLeftOut' => 'left',
	'longLeft' => 'left',
	'longLeftOut' => 'left',
	'skewShortLeft' => 'left',
	'skewShortLeftOut' => 'left',
	'skewLongLeft' => 'left',
	'skewLongLeftOut' => 'left',
	'shortBottom' => 'down',
	'shortBottomOut' => 'down',
	'longBottom' => 'down',
	'longBottomOut' => 'down',
	'shortRight' => 'right',
	'shortRightOut' => 'right',
	'longRight' => 'right',
	'longRightOut' => 'right',
	'skewShortRight' => 'right',
	'skewShortRightOut' => 'right',
	'skewLongRight' => 'right',
	'skewLongRightOut' => 'right',
	/* fallbacks: replace all non-existent effects with up */
	'fade' => 'up', 'fadeOut' => 'up'
);

$styles = array();

?>
<!-- Slider Pro module -->
<div id="<?php echo $module_ID; ?>" class="<?php echo esc_attr( $container_class ); ?>" data-thumbnail-width="<?php echo $builder_ps_thumb_width; ?>" data-thumbnail-height="<?php echo $builder_ps_thumb_height; ?>" data-autoplay="<?php echo $builder_ps_aa; ?>" data-hover-pause="<?php echo $builder_ps_hover_pause; ?>" data-timer-bar="<?php echo $builder_ps_timer; ?>" data-slider-width="<?php echo $builder_ps_width; ?>" data-slider-height="<?php echo $builder_ps_height; ?>">
	
		<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<?php if ( $mod_title_slider != '' ): ?>
		<?php echo $mod_settings['before_title'] . apply_filters( 'themify_builder_module_title', $mod_title_slider, $fields_args ). $mod_settings['after_title']; ?>
	<?php endif; ?>
	<div class="themify_builder_slider_loader"></div>
	<div class="slider-pro" style="visibility: hidden;">
		<div class="sp-slides">
			<?php foreach( $builder_slider_pro_slides as $i => $slide ) : ?>
				<?php
				$slide = wp_parse_args( $slide, $slide_defaults );
				$is_empty_slide = ( '' == $slide['builder_ps_slide_type'] || ( $slide['builder_ps_slide_type'] === 'Image' && empty( $slide['builder-ps-bg-image'] ) ) || ( $slide['builder_ps_slide_type'] === 'Video' && empty( $slide['builder_ps_vbg_option'] ) ) ) ? true : false;
				$slide_background = '';
				if( ! $is_empty_slide && $slide['builder_ps_slide_type'] === 'Image' ) {
					$image = themify_do_img( $slide['builder-ps-bg-image'], $builder_ps_width, $builder_ps_height );
					$slide_background = !empty($image['url'])?sprintf( ' style="background-image: url(%s);"', $image['url'] ):'';
				}

				// slide styles
				if( ! empty( $slide['builder-ps-bg-color'] ) )
					$styles[] = sprintf( '.sp-slide-%s { background-color: %s; }', $i, $this->stylesheet->get_rgba_color( $slide['builder-ps-bg-color'] ) );
				if( '' != $slide['builder_ps_text_color'] )
					$styles[] = sprintf( '.module-pro-slider .sp-slide-%1$s .bsp-slide-excerpt, .module-pro-slider .sp-slide-%1$s .bsp-slide-excerpt p, .module-pro-slider .sp-slide-%1$s .bsp-slide-post-title { color: %2$s; }', $i, $this->stylesheet->get_rgba_color( $slide['builder_ps_text_color'] ) );
				if( '' != $slide['builder_ps_text_link_color'] )
					$styles[] = sprintf( '.sp-slide-%1$s .bsp-slide-excerpt a, .sp-slide-%1$s .bsp-slide-excerpt p a { color: %2$s; }', $i, $this->stylesheet->get_rgba_color( $slide['builder_ps_text_link_color'] ) );
				if( '' != $slide['builder_ps_button_color'] )
					$styles[] = sprintf( '.sp-slide-%1$s a.bsp-slide-button { color: %2$s; }', $i, $this->stylesheet->get_rgba_color( $slide['builder_ps_button_color'] ) );
				if( '' != $slide['builder_ps_button_bg'] )
					$styles[] = sprintf( '.sp-slide-%1$s a.bsp-slide-button { background-color: %2$s; }', $i, $this->stylesheet->get_rgba_color( $slide['builder_ps_button_bg'] ) );

				?>
				<div class="sp-slide sp-slide-<?php echo $i; ?> sp-slide-type-<?php echo $slide['builder_ps_slide_type']; ?> <?php echo $slide['builder_ps_layout']; ?> <?php if( $is_empty_slide ) echo 'bsp-no-background'; ?>" data-transition="<?php echo $slide['builder_ps_tranzition']; ?>" data-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_tranzition_duration'] ); ?>" <?php echo $slide_background; ?>>
					<?php
					if( ! $is_empty_slide ) {

						/* slider thumbnail */
						if( $builder_ps_triggers_type === 'thumb' ) {
							$image = themify_do_img( $slide['builder-ps-bg-image'], $builder_ps_thumb_width, $builder_ps_thumb_height );
							echo sprintf( '<img class="sp-thumbnail" src="%s" width="%s" height="%s" />', $image['url'], $image['width'], $image['height'] );
						}

						if( $slide['builder_ps_slide_type'] == 'Image' ) {
						} elseif( $slide['builder_ps_slide_type'] == 'Video' ) {
							$video_output = themify_parse_video_embed_vars( wp_oembed_get( esc_url( $slide['builder_ps_vbg_option'] ) ), esc_url( $slide['builder_ps_vbg_option'] ) );
							if( $video_output == '<div class="post-embed"></div>' ) { // is it a local video? check the result of themify_parse_video_embed_vars function
								// $video_output = do_shortcode( sprintf( '[video src="%s"]', $slide['builder_ps_vbg_option'] ) );
								echo '<div class="bsp-video" data-src="'. $slide['builder_ps_vbg_option'] .'"></div><iframe class="bsp-video-iframe" src="" width="100%" height="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
							}
							
							echo $video_output;
						}
					}
					?>

					<?php ob_start(); ?>
					<?php if( ! empty( $slide['builder-ps-slide-image'] ) ) : ?>
						<div class="sp-layer sp-slide-image" data-show-transition="<?php echo $timer_translation[ $slide['builder_ps_imgs_timer'] ]; ?>" data-show-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_imgs_tranzition_duration'] ) * 1000 ?>" data-hide-transition="<?php echo $timer_translation[ $slide['builder_ps_imge_timer'] ]; ?>" data-hide-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_imge_tranzition_duration'] ) * 1000 ?>">
							<img class="bsp-content-img" src="<?php echo $slide['builder-ps-slide-image']; ?>" alt="" />
						</div>
					<?php endif; ?>
					<?php $img = ob_get_clean(); ?>

					<?php ob_start(); ?>
					<div class="sp-slide-text">
						<?php if( ! empty( $slide['builder_ps_heading'] ) ) : ?>
							<h3 class="sp-layer bsp-slide-post-title" data-show-transition="<?php echo $timer_translation[ $slide['builder_ps_h3s_timer'] ]; ?>" data-show-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_h3s_tranzition_duration'] ) * 1000 ?>" data-hide-transition="<?php echo $timer_translation[ $slide['builder_ps_h3e_timer'] ]; ?>" data-hide-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_h3e_tranzition_duration'] ) * 1000 ?>"><?php echo $slide['builder_ps_heading']; ?></h3>
						<?php endif; ?>
	
						<?php if( ! empty( $slide['builder_ps_text'] ) ) : ?>
							<div class="sp-layer bsp-slide-excerpt" data-show-transition="<?php echo $timer_translation[ $slide['builder_ps_ps_timer'] ]; ?>" data-show-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_ps_tranzition_duration'] ) * 1000 ?>" data-hide-transition="<?php echo $timer_translation[ $slide['builder_ps_pe_timer'] ]; ?>" data-hide-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_pe_tranzition_duration'] ) * 1000 ?>">
								<?php echo apply_filters( 'themify_builder_module_content', $slide['builder_ps_text'] ); ?>
							</div>
						<?php endif; ?>
	
						<?php if( '' != $slide['builder_ps_button_text'] && '' != $slide['builder_ps_button_link'] ) : ?>
							<a class="sp-layer bsp-slide-button" href="<?php echo esc_url( $slide['builder_ps_button_link'] ); ?>" data-show-transition="<?php echo $timer_translation[ $slide['builder_ps_as_timer'] ]; ?>" data-show-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_as_tranzition_duration'] ) * 1000 ?>" data-hide-transition="<?php echo $timer_translation[ $slide['builder_ps_ae_timer'] ]; ?>" data-hide-duration="<?php echo Builder_Pro_Slider::get_instance()->get_speed( $slide['builder_ps_ae_tranzition_duration'] ) * 1000 ?>">
								<?php if( '' != $slide['builder_ps_button_icon'] ) echo sprintf( '<i class="fa %s"></i>', themify_get_fa_icon_classname( $slide['builder_ps_button_icon'] ) ); ?> 
								<?php echo $slide['builder_ps_button_text']; ?>
							</a>
						<?php endif; ?>
					</div>
					<!-- /sp-slide-text -->

					<?php
					$text = ob_get_clean();
					$text = $img . $text;
					if( trim( $text ) ) : ?>
						<div class="bsp-layers-overlay">
							<div class="sp-slide-wrap">
								<?php echo $text?>
							</div><!-- .sp-slide-wrap -->
						</div><!-- .bsp-layers-overlay -->
					<?php endif;?>

				</div><!-- .sp-slide -->
			<?php endforeach; ?>
		</div><!-- .sp-slides -->
	</div><!-- .slider-pro -->

	<?php do_action( 'themify_builder_after_template_content_render' );
	
	// add styles
	if( ! empty( $styles ) ) {
		echo "<style type='text/css'>\n";
		foreach( $styles as $style ) {
			echo '#' . $module_ID . ' ' . $style . "\n";
		}
		echo '</style>';
	} ?>
</div>
<!-- /Slider Pro module -->