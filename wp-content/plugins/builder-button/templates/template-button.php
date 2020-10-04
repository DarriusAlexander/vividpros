<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Button Pro
 * 
 * Access original fields: $mod_settings
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
	$GLOBALS['ThemifyBuilder']->load_templates_js_css();
}

$fields_default = array(
	'type_button' => 'external',
	'link_label' => '',
	'icon_button' => '',
	'color_button' => '',
	'color_button_hover'=>'',
	'appearance_button' => '',
	'link_button' => '#',
	'param_button' => '',
	'content_modal_button' => '',
	'modules_reveal_behavior_button' => 'hide',
	'show_less_label_button' => __( 'Show less', 'builder-button' ),
	'add_css_button' => '',
	'animation_effect' => ''
);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );
$param_button = array_values( explode( '|', $fields_args['param_button'] ) );

if ( isset( $fields_args['appearance_button'] ) ) 
	$fields_args['appearance_button'] = $this->get_checkbox_data( $fields_args['appearance_button'] );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, 'button-link-' . $type_button, $add_css_button, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);

$atts = array(
	'class' => ( ( 'default' == $color_button ) ? '' : 'ui builder_button ' . $color_button ) . ' ' . $fields_args['appearance_button'],
	'href' => $link_button
);
if( 'modal' == $type_button ) {
	$atts['href'] = '#modal-' . $module_ID;
	$atts['class'] .= ' themify_lightbox';
} elseif( 'row_scroll' == $type_button ) {
	$atts['href'] = '#';
	$atts['class'] .= ' scroll-next-row';
} elseif( 'modules_reveal' == $type_button ) {
	$atts['href'] = '#';
	$atts['class'] .= ' modules-reveal';
	$atts['data-behavior'] = $modules_reveal_behavior_button;
	$atts['data-label'] = $link_label;
	$atts['data-lesslabel'] = $show_less_label_button;
} else {
	if( in_array( 'lightbox', $param_button ) ) {
		$atts['class'] .= ' themify_lightbox';
	}
	if( in_array( 'newtab', $param_button ) ) {
		$atts['target'] = '_blank';
	}
}
if($color_button_hover){
    $atts['data-hover'] = $color_button_hover;
    $atts['data-remove'] = $color_button;
}
$attributes = '';
foreach ( $atts as $attr => $value ) {
	$attributes .= ' ' . $attr . '="' . $value . '"';
}

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module button pro -->
<div <?php echo $this->get_element_attributes( $container_props ); ?>>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<a <?php echo $attributes; ?>>
		<?php if( '' != $icon_button ) : ?><i class="<?php echo themify_get_icon( $icon_button ); ?>"></i> <?php endif; ?>
		<span><?php echo $link_label; ?></span>
	</a>

	<?php if( 'modal' == $type_button ) : ?>
		<div id="modal-<?php echo $module_ID ?>" class="mfp-hide">
			<?php echo apply_filters( 'themify_builder_module_content', $content_modal_button ); ?>
		</div>
	<?php endif; ?>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module button pro -->
