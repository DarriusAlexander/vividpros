<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Tile
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
    $GLOBALS['ThemifyBuilder']->load_templates_js_css( array( 'carousel' => true, 'module-plugins' => true ) );
}

$fields_default = array(
	'size' => 'square-large',
	'flip_effect' => 'flip-left',
	'color_front' => '', 'color_back' => '',
	'title_front' => '', 'title_back' => '',
	'type_front' => 'text', 'type_back' => 'text',
	'text_front' => '', 'text_back' => '',
	'button_link_front' => '', 'button_link_back' => '',
	'button_param_front' => array(), 'button_param_back' => array(),
	'icon_type_front' => 'icon', 'icon_type_back' => 'icon',
	'icon_front' => '', 'icon_back' => '',
	'image_front' => '', 'image_back' => '',
	'icon_color_front' => '', 'icon_color_back' => '',
	'background_repeat_front' => '', 'background_repeat_back' => '',
	'gallery_front' => '', 'gallery_back' => '',
	'gallery_autoplay_front' => 'off', 'gallery_autoplay_back' => 'off',
	'gallery_hide_timer_front' => 'no', 'gallery_hide_timer_back' => 'no',
	'address_map_front' => '', 'address_map_back' => '',
	'type_map_front' => 'ROADMAP', 'type_map_back' => 'ROADMAP',
	'zoom_map_front' => 8, 'zoom_map_back' => 8,
	'scrollwheel_map_front' => 'enable', 'scrollwheel_map_back' => 'enable',
	'draggable_map_front' => 'enable', 'draggable_map_back' => 'enable',
	'action_text_front' => '', 'action_text_back' => '',
	'action_link_front' => '', 'action_link_back' => '',
	'action_param_front' => array(), 'action_param_back' => array(),
	'tile_autoflip' => '0',
	'background_color_front' => '', 'background_color_back' => '',
	'text_color_front' => '', 'text_color_back' => '',
	'link_color_front' => '', 'link_color_back' => '',
	'background_image_front' => '', 'background_image_back' => '',
	'animation_effect' => '',
	'css_class' => ''
);

foreach( array( 'front', 'back' ) as $side ) {
	if( isset( $mod_settings["button_link_params_{$side}"] ) )
		$mod_settings["button_link_params_{$side}"] = explode( '|', $mod_settings["button_link_params_{$side}"] );
	if( isset( $mod_settings["action_param_{$side}"] ) )
		$mod_settings["action_param_{$side}"] = explode( '|', $mod_settings["action_param_{$side}"] );
}

$args = wp_parse_args( $mod_settings, $fields_default );
extract( $args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect($animation_effect, $args);

$tile_sizes = Builder_Tiles::get_instance()->get_tile_sizes();
$tile_size = $tile_sizes[$size];

$class = 'has-flip';
if( ( $type_back == 'blank' )
	|| ( $type_back == 'text' && $text_back == '' && $background_image_back == '' && $title_back == '' ) // enable flip on back text tiles with no text but have background image
	|| ( $type_back == 'button' && $title_back == '' && $image_back == '' && $icon_back == '' )
	|| ( $type_back == 'gallery' && $gallery_back == '' )
	|| ( $type_back == 'map' && $address_map_back == '' )
) {
	$class = 'no-flip';
}

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, 'size-' . $size, $flip_effect, $class,$css_class, 'tile-type-front-' . $type_front, $animation_effect
	), $mod_name, $module_ID, $args )
);

$out_effect = array(
	'flip-horizontal' => '',
	'flip-vertical' => '',
	'fadeInUp' => 'fadeOutDown',
	'fadeIn' => 'fadeOut',
	'fadeInLeft' => 'fadeOutLeft',
	'fadeInRight' => 'fadeOutRight',
	'fadeInDown' => 'fadeOutUp',
	'zoomInUp' => 'zoomOutDown',
	'zoomInLeft' => 'zoomOutLeft',
	'zoomInRight' => 'zoomOutRight',
	'zoomInDown' => 'zoomOutUp',
);

$flip_button_enabled = apply_filters( 'builder_tiles_enable_flip_button', themify_is_touch() );
$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $args, $mod_name, $module_ID );
?>
<!-- module tile -->
<div<?php echo $this->get_element_attributes( $container_props ); ?> data-auto-flip="<?php echo esc_attr( $tile_autoflip ); ?>" data-in-effect="<?php echo esc_attr( $flip_effect ); ?>" data-out-effect="<?php echo esc_attr( $out_effect[$flip_effect] ); ?>">

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<div class="tile-flip-box-wrap"><div class="tile-flip-box">

	<?php foreach( array( 'front', 'back' ) as $side ) : ?>

		<?php if( $args['type_' . $side] == 'blank' ) continue; ?>

		<style>
		#<?php echo esc_attr( $module_ID ); ?> .tile-<?php echo esc_attr( $side ); ?> {
			<?php
			echo ( '' != $args['background_color_' . $side] ) ? 'background-color: ' . $this->stylesheet->get_rgba_color( $args['background_color_' . $side] ) . '; ' : '';
			echo ( '' != $args['text_color_' . $side] ) ? 'color: ' . $this->stylesheet->get_rgba_color( $args['text_color_' . $side] ) . '; ' : '';
			if( '' != $args['background_image_' . $side] ) {
				$image = themify_do_img( $args['background_image_' . $side], $tile_size['width'], $tile_size['height'] );
				echo 'background-image: url("' . esc_url( $image['url'] ) . '"); ';
			}
			?>
		}
		#<?php echo esc_attr( $module_ID ); ?> .tile-<?php echo esc_attr( $side ); ?> a {
			<?php echo ( '' != $args['link_color_' . $side] ) ? 'color: ' . $this->stylesheet->get_rgba_color( $args['link_color_' . $side] ) . '; ' : ''; ?>
		}
		</style>

		<div class="tile-<?php echo esc_attr( $side ); ?> tile-type-<?php echo esc_attr( $args['type_' . $side] ); ?> ui <?php echo esc_attr( $args['color_' . $side] ); ?>">
			<div class="tile-inner">

				<?php
				/**
				 * Text tile type
				 */
				if( $args['type_' . $side] == 'text' ) : ?>

					<?php if( $args['title_' . $side] != '' ) : ?><h4 class="tile-title"><?php echo wp_kses_post( $args['title_' . $side] ); ?></h4><?php endif; ?>
					<div class="tile-content">
						<?php echo apply_filters( 'themify_builder_module_content', $args['text_' . $side] ); ?>
					</div><!-- .tile-content -->

				<?php
				/**
				 * Button tile type
				 */
				elseif( $args['type_' . $side] == 'button' ) : ?>
					<?php
						$icon_style = ( '' != $args["icon_color_{$side}"] ) ? 'color: ' . $this->stylesheet->get_rgba_color( $args["icon_color_{$side}"] ) : '';
					?>
					<?php if( $args["button_link_{$side}"] != '' ) printf( '<a href="%s" class="%s" %s>', in_array( 'lightbox', $args["button_link_params_{$side}"] ) ? esc_url( themify_get_lightbox_iframe_link( $args["button_link_{$side}"] ) ) : esc_attr( $args["button_link_{$side}"] ), in_array( 'lightbox', $args["button_link_params_{$side}"] ) ? ' themify_lightbox' : '', in_array( 'newtab', $args["button_link_params_{$side}"] ) ? ' target="_blank"' : '' ); ?>
						<?php if( '' != $args["title_{$side}"] ) : ?><h4 class="tile-title"><?php echo wp_kses_post( $args["title_{$side}"] ); ?></h4><?php endif; ?>

						<?php if( $args["icon_type_{$side}"] == 'icon' && $args["icon_{$side}"] != '' ) : ?>
							<span class="tile-icon fa <?php echo esc_attr( themify_get_fa_icon_classname( $args["icon_{$side}"] ) ); ?>" style="<?php echo esc_attr( $icon_style ); ?>"></span>
						<?php elseif( $args["icon_type_{$side}"] == 'image' && $args["image_{$side}"] != '' ) : ?>
							<img src="<?php echo esc_url( $args["image_{$side}"] ); ?>" alt="<?php echo esc_attr( $args['title_' . $side] ); ?>" class="tile-icon" />
						<?php endif; ?>
					<?php if( $args["button_link_{$side}"] != '' ) echo '</a>'; ?>

				<?php
				/**
				 * Gallery tile type
				 */
				elseif( $args['type_' . $side] == 'gallery' ) : ?>

					<?php $images = $this->get_images_from_gallery_shortcode( $args["gallery_{$side}"] );
					$returned_items = count( $images );
					if( $images ) : ?>
						<div class="gallery-shortcode-wrap twg-wrap twg-gallery-shortcode <?php echo ( 'yes' == $args["gallery_hide_timer_{$side}"] ) ? 'no-timer' : 'with-timer'; ?>" data-bgmode="cover">
							<div class="gallery-image-holder twg-holder">

								<div class="twg-loading themify-loading"></div>

								<div class="gallery-info twg-info">
									<div class="gallery-caption twg-caption">

									</div>
									<!-- /gallery-caption -->
								</div>
							</div>

							<div id="gallery-shortcode-slider-<?php echo esc_attr( $module_ID ); ?>" class="gallery-slider-wrap twg-controls">

								<div class="gallery-slider-timer">
									<div class="timer-bar"></div>
								</div>
								<!-- /gallery-slider-timer -->

								<ul class="gallery-slider-thumbs slideshow twg-list" data-id="gallery-shortcode-slider-<?php echo esc_attr( $module_ID ); ?>" data-autoplay="<?php echo esc_attr( $args["gallery_autoplay_{$side}"] ); ?>" data-effect="scroll" data-speed="1000" data-visible="<?php echo 12 >= $returned_items ? $returned_items - 1: '12' ?>" data-width="33" data-wrap="yes" data-slidernav="yes" data-pager="no">
								<?php foreach ( $images as $image ) :
									$full = wp_get_attachment_image_src( $image->ID, apply_filters( 'themify_gallery_shortcode_full_size', 'large' ) );
									$caption = $image->post_excerpt == '' ? $image->post_content : $image->post_excerpt;
									$description = $image->post_content == '' ? $image->post_excerpt : $image->post_content;
									$alt = ( $alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) ) ? $alt_text : $image->post_name;
									?>
									<li class="twg-item">
										<a href="#" data-image="<?php echo esc_url( $full[0] ); ?>" data-caption="<?php echo esc_attr( $caption ); ?>" data-description="<?php echo esc_attr( $description ); ?>" class="twg-link">
											<?php echo themify_get_image( 'src=' . esc_url( $full[0] ) . '&w=40&h=33&alt=' . $alt . '&ignore=true' ); ?>
										</a>
									</li>
								<?php endforeach; // images as image ?>
								</ul>
							</div>
						</div>
					<?php endif; ?>

				<?php
				/**
				 * Map tile type
				 */
				elseif( $args['type_' . $side] == 'map' ) : ?>
					<?php
					$args["address_map_{$side}"] = preg_replace( '/\s+/', ' ', trim( $args["address_map_{$side}"] ) );
					$data = array();
					$data['address'] = $args["address_map_{$side}"];
					$data['zoom'] = $args["zoom_map_{$side}"];
					$data['type'] = $args["type_map_{$side}"];
					$data['scroll'] = 'enable' != $args["scrollwheel_map_{$side}"] ? 'false' : 'true';
					$data['drag'] = 'enable' != $args["draggable_map_{$side}"] ? 'false' : 'true';
					?>

					<?php $num = rand( 0,10000 ); ?>
						<div id="themify_map_canvas_<?php echo esc_attr( $num ); ?>" data-map="<?php esc_attr_e( base64_encode( json_encode( $data ) ))?>" class="themify_map map-container"></div>
				<?php endif; ?>

				<?php if( ( $args["type_{$side}"] == 'text' || $args["type_{$side}"] == 'gallery' ) && '' != $args["action_link_{$side}"] ) : ?>
					<a href="<?php echo in_array( 'lightbox', $args["action_param_{$side}"] ) ? esc_url( themify_get_lightbox_iframe_link( $args["action_link_{$side}"] ) ) : esc_attr( $args["action_link_{$side}"] ); ?>" class="action-button <?php if ( in_array( 'lightbox', $args["action_param_{$side}"] ) ) : echo 'themify_lightbox'; endif; ?>" <?php if ( in_array( 'newtab', $args["action_param_{$side}"] ) ) : echo 'target="_blank"'; endif; ?>><span></span> <?php echo wp_kses_post( $args["action_text_{$side}"] ); ?></a>
					<?php if( isset( $args["link_color_{$side}"] ) ) printf( '<style> #%s .action-button span { border-color: %s; } </style>', $module_ID, $this->stylesheet->get_rgba_color( $args["link_color_{$side}"] ) ); ?>
				<?php endif; // action button check ?>

				<?php if( $flip_button_enabled ) : ?>
					<a href="#" class="tile-flip-back-button"></a>
				<?php endif; ?>

			</div><!-- .tile-inner -->

		</div><!-- .tile-<?php echo esc_html( $side ); ?> -->

	<?php endforeach; ?>

	</div><!-- .tile-flip-box --></div><!-- .tile-flip-box-wrap -->

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module tile -->