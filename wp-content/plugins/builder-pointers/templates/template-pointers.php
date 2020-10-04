<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Pointers
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
    $GLOBALS['ThemifyBuilder']->load_templates_js_css( array( 'waypoints' => true ) );
}

$fields_default = array(
	'mod_title' => '',
	'image_url' => '',
	'blobs_showcase' => array(),
	'title_image' => '',
	'image_width' => '',
	'image_height' => '',
	'css_class' => '',
	'animation_effect' => ''
);
$blob_default = array(
	'title' => '', 'direction' => 'bottom', 'pointer_color' => '', 'tooltip_background' => '', 'tooltip_color' => '', 'left' => '', 'top' => '', 'link' => '', 'auto_visible' => 'no','pointer_hide'=>'no'
);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $css_class, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);

$image = themify_do_img( $image_url, $image_width, $image_height );

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module pointers -->
<div <?php echo $this->get_element_attributes( $container_props ); ?>>
	
	<?php if ( $mod_title != '' ): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title, $fields_args ) ) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<div class="showcase-image">
		<img src="<?php echo $image['url']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" alt="<?php echo esc_attr( $title_image ); ?>" />

		<?php foreach( $blobs_showcase as $key => $blob ) :
			$blob = wp_parse_args( $blob, $blob_default );
			if( $blob['left'] == '' ) continue;
                        $pointer_hide = isset($blob['pointer_hide']) && $blob['pointer_hide']=='yes';
			$pointer_color = ( '' != $blob['pointer_color'] ) ? 'background-color: ' . $this->stylesheet->get_rgba_color( $blob['pointer_color'] ) . ';' : '';
			$direction = ( '' != $blob['direction'] ) ? $blob['direction'] : 'top';
			
			$style = ( '' != $blob['tooltip_background'] ) ? 'background-color: ' . $this->stylesheet->get_rgba_color( $blob['tooltip_background'] ) . ';' : '';
			$style .= ( '' != $blob['tooltip_color'] ) ? 'color: ' . $this->stylesheet->get_rgba_color( $blob['tooltip_color'] ) . ';' : '';
			if( '' != $style ) echo sprintf( '<style>body .tooltip-%s-%s { %s } </style>', $module_ID, $key, $style );

			?><div class="blob blob-<?php echo $key; ?><?php echo $pointer_hide?' tooltipster-fade':''?>" style="top: <?php echo $blob['top']; ?>%; left: <?php echo $blob['left']; ?>%;" data-direction="<?php echo $direction; ?>" data-theme="tooltipster-default <?php echo 'tooltip-' . $module_ID . '-' . $key ?>" data-visible="<?php echo $blob['auto_visible']; ?>" aria-describedby="<?php echo 'blob-tooltip-' . $module_ID . '-' . $key; ?>">

				<?php if( '' != $blob['title'] ) : ?>
					<span class="blob-tooltip" id="<?php echo 'blob-tooltip-' . $module_ID . '-' . $key; ?>" style="display: none; visibility: hidden;" role="tooltip"><?php echo $blob['title']; ?></span>
				<?php endif; ?>

				<?php if( '' != $blob['link'] ) : ?>
					<a
						href="<?php echo ( isset($blob['open']) && $blob['open']=='lightbox' ) ? themify_get_lightbox_iframe_link( $blob['link'] ) : $blob['link']; ?>" 
						<?php if( isset($blob['open']) && $blob['open']=='lightbox' ): ?>class="themify_lightbox"<?php endif; ?>
						<?php if(isset($blob['open']) && $blob['open']=='blank'):?>target="_blank"<?php endif;?> 
					>
				<?php endif; ?>

				<div class="blob-icon" style="<?php echo $pointer_color; ?>">
					<span style="<?php echo $pointer_color; ?>"></span>
				</div>

				<?php if( '' != $blob['link'] ) : ?></a><?php endif; ?>

			</div>

		<?php endforeach; ?>
	</div>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module pointers -->