<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template FitText
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
	$GLOBALS['ThemifyBuilder']->load_templates_js_css();
}
wp_enqueue_script( 'builder-fittext' );

$fields_default = array(
	'fittext_text' => '',
	'fittext_link' => '',
	'fittext_params' => '',
	'font_family' => '',
	'add_css_fittext' => '',
	'js_params' => array(),
	'animation_effect' => ''
);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );
$fittext_params = array_values( explode( '|', $fields_args['fittext_params'] ) );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $add_css_fittext, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);

$link_class = '';
$link_target = '';
if( in_array( 'lightbox', $fittext_params ) ) {
	$fittext_link = themify_get_lightbox_iframe_link( $fittext_link );
	$link_class = 'themify_lightbox';
} elseif( in_array( 'newtab', $fittext_params ) ) {
	$link_target = '_blank';
}

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module fittext -->
<div <?php echo $this->get_element_attributes( $container_props ); ?> data-font-family="<?php echo $font_family; ?>">

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<?php if( '' != $fittext_link ) : ?>
		<a href="<?php echo $fittext_link; ?>" class="<?php echo $link_class; ?>" target="<?php echo $link_target; ?>">
	<?php endif; ?>

	<span><?php echo $fittext_text; ?></span>

	<?php if( '' != $fittext_link ) : ?>
		</a>
	<?php endif; ?>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module fittext -->