<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Image Pro
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

$fields_default = array(
	'mod_title_image' => '',
	'style_image' => '',
	'url_image' => '',
	'appearance_image' => '',
	'appearance_image2' => '',
	'image_size_image' => '',
	'width_image' => '',
	'height_image' => '',
	'title_image' => '',
	'param_image' => array(),
	'caption_image' => '',
	'css_image' => '',
	'image_effect' => '',
	'image_filter' => '',
	'image_alignment'=>'',
	'overlay_color' => '',
	'overlay_image' => '',
	'overlay_effect' => 'fadeIn',
	'action_button' => '',
	'color_button' => '',
	'link_type' => 'external',
	'link_new_window' => 'no',
	'link_address' => '',
	'content_modal' => '',
	'link_image_type' => 'image_external',
	'link_image' => '',
	'link_image_new_window' => 'no',
	'image_content_modal' => '',
	'animation_effect' => ''
);

$link_image = isset( $mod_settings['link_image'] ) ? $mod_settings['link_image'] : '';

if (isset($mod_settings['appearance_image']))
	$mod_settings['appearance_image'] = $this->get_checkbox_data($mod_settings['appearance_image']);
if (isset($mod_settings['appearance_image2']))
	$mod_settings['appearance_image2'] = $this->get_checkbox_data($mod_settings['appearance_image2']);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );
$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, 'filter-' . $image_filter, 'effect-' . $image_effect, $appearance_image, $appearance_image2, $image_alignment, $style_image, $css_image, $animation_effect, 'entrance-effect-' . $overlay_effect
	), $mod_name, $module_ID, $fields_args )
);

$image_alt = '' != $title_image ? esc_attr( $title_image ) : wp_strip_all_tags( $caption_image );
$image_alt = '' != $image_alt ? $image_alt : esc_attr( $title_image );

$param_image_src = 'src='.esc_url($url_image).'&w='.$width_image .'&h='.$height_image.'&alt='.$image_alt.'&ignore=true';
if ( $this->is_img_php_disabled() ) {
	// get image preset
	$preset = $image_size_image != '' ? $image_size_image : themify_get('setting-global_feature_size');
	if ( isset( $_wp_additional_image_sizes[ $preset ]) && $image_size_image != '') {
		$width_image = intval( $_wp_additional_image_sizes[ $preset ]['width'] );
		$height_image = intval( $_wp_additional_image_sizes[ $preset ]['height'] );
	} else {
		$width_image = $width_image != '' ? $width_image : get_option($preset.'_size_w');
		$height_image = $height_image != '' ? $height_image : get_option($preset.'_size_h');
	}
	$image = '<img src="' . esc_url( $url_image ) . '" alt="' . esc_attr( $image_alt ) . '" width="' . esc_attr( $width_image ) . '" height="' . esc_attr( $height_image ) . '">';
} else {
	$image = themify_get_image( $param_image_src );
}

// check whether link is image or url
if ( ! empty( $link_address ) ) {
	$check_img = $this->is_img_link( $link_address );
	if ( ! $check_img && $link_type == 'lightbox_link' ) {
		$link_address = themify_get_lightbox_iframe_link( $link_address );
	}
}

$out_effect = array(
	'none' => '',
	'partial-overlay' => '',
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

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module image pro -->
<div <?php echo $this->get_element_attributes( $container_props ); ?> data-entrance-effect="<?php echo $overlay_effect; ?>" data-exit-effect="<?php echo $out_effect[$overlay_effect]; ?>">
	
	<?php if ( $mod_title_image != '' ): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_image, $fields_args ) ) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<div class="image-pro-wrap">
		<?php if(!empty($link_image) || 'image_modal' == $link_image_type):?>
			<a class="image-pro-external<?php if($link_image_type!='image_external'):?> themify_lightbox<?php endif;?>" href="<?php echo 'image_modal' !== $link_image_type?esc_url($link_image):'#modal-image-'.$module_ID?>" <?php if( $link_image_new_window == 'yes' ) : ?>target="_blank"<?php endif; ?>></a>
		<?php endif;?>
		<div class="image-pro-flip-box-wrap">
		<div class="image-pro-flip-box">

		<?php echo $image; ?>

		<div class="image-pro-overlay <?php echo ( 'none' == $overlay_effect ) ? 'none' : ''; ?>">

			<?php if( $overlay_color != '' ) : ?>
				<div class="image-pro-color-overlay" style="background-color: <?php echo $this->stylesheet->get_rgba_color( $overlay_color ); ?>"></div>
			<?php endif; ?>

			<div class="image-pro-overlay-inner">

				<?php if( $title_image != '' ) : ?>
					<h4 class="image-pro-title"><?php echo $title_image; ?></h4>
				<?php endif; ?>

				<?php if( $caption_image != '' ) : ?>
					<div class="image-pro-caption"><?php echo $caption_image; ?></div>
				<?php endif; ?>

				<?php if( $action_button != '' ) : ?>
					<a class="ui builder_button image-pro-action-button <?php echo $color_button; ?> <?php if( $link_type == 'lightbox_link' || $link_type == 'modal' ) echo 'themify_lightbox' ?>" href="<?php
					if( $link_type == 'modal' ) {
						echo '#modal-' . $module_ID;
					} else {
						echo $link_address;
					}
					?>" <?php if( $link_new_window == 'yes' ) : ?>target="_blank"<?php endif; ?>>
						<?php echo $action_button; ?>
					</a>
				<?php endif; ?>

			</div>
		</div><!-- .image-pro-overlay -->

		</div>
		</div>

	</div><!-- .image-pro-wrap -->

	<?php if( 'modal' == $link_type ) : ?>
		<div id="modal-<?php echo $module_ID ?>" class="mfp-hide">
			<?php echo apply_filters( 'themify_builder_module_content', $content_modal ); ?>
		</div>
	<?php endif; ?>
		<?php if( 'image_modal' == $link_image_type ) : ?>
		<div id="modal-image-<?php echo $module_ID ?>" class="mfp-hide">
			<?php echo apply_filters( 'themify_builder_module_content', $image_content_modal ); ?>
		</div>
	<?php endif; ?>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>

<style>
	<?php if( $overlay_image != '' ) echo '#' . $module_ID . ' .image-pro-overlay { background-image: url("'. $overlay_image .'"); }';?>
</style>

</div>
<!-- /module image pro -->