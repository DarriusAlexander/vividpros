<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Maps Pro
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
    $GLOBALS['ThemifyBuilder']->load_templates_js_css();
}

$fields_default = array(
	'mod_title' => '',
	'map_link' => '',
	'map_center' => '',
	'zoom_map' => 4,
	'w_map' => '', 'unit_w' => '%',
	'h_map' => '', 'unit_h' => 'px',
	'type_map' => 'ROADMAP',
	'scrollwheel_map' => 'enable',
	'draggable_map' => 'enable',
	'disable_map_ui' => 'no',
	'markers' => array(),
	'map_display_type' => 'dynamic',
	'w_map_static' => 500,
	'animation_effect' => '',
	'style_map' => '',
	'css_class' => '',
);
$marker_defaults = array(
	'title' => '', 'address' => '', 'image' => ''
);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $css_class, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);
if( '' != $style_map && $map_display_type == 'dynamic' ) {
	echo '
	<script>
		map_pro_styles = window.map_pro_styles || [];
		map_pro_styles["'. $style_map .'"] = '. json_encode( Builder_Maps_Pro::get_instance()->get_map_style( $style_map ) ) . ';
	</script>';
}

$map_options = array(
	'zoom' => $zoom_map,
	'type' => $type_map,
	'address' => $map_center,
	'width' => $w_map,
	'height' => $h_map,
	'style_map' => $style_map,
	'scrollwheel' => $scrollwheel_map,
	'draggable' => ( 'enable' == $draggable_map || ( 'desktop_only' == $draggable_map && ! themify_is_touch() ) ) ? 'enable' : 'disable',
	'disable_map_ui' => $disable_map_ui
);

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module maps pro -->
<div <?php echo $this->get_element_attributes( $container_props ); ?> data-config='<?php echo esc_attr( json_encode( $map_options ) ); ?>'>
	
	<?php if ( $mod_title != '' ): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title, $fields_args ) ) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<?php if( $map_display_type == 'dynamic' ) : ?>

		<div class="maps-pro-canvas-container">
			<div class="maps-pro-canvas map-container" style="width: <?php echo $w_map . $unit_w; ?>%; height: <?php echo $h_map . $unit_h ?>;">
			</div>
		</div>

		<div class="maps-pro-markers" style="display: none;">

			<?php
				foreach( $markers as $marker ) :
				$marker = wp_parse_args( $marker, $marker_defaults );
				?>
				<div class="maps-pro-marker" data-address="<?php echo isset( $marker['latlng'] ) && ! empty( $marker['latlng'] ) ? $marker['latlng'] : $marker['address'] ?>" data-image="<?php echo $marker['image']; ?>">
					<?php echo TB_Maps_Pro_Module::sanitize_text( $marker['title'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>

	<?php else :

		$args = '';
		if( ! empty( $map_center ) ) {
			$args .= 'center=' . $map_center;
		}
		$args .= '&zoom=' . $zoom_map;
		$args .= '&maptype=' . strtolower( $type_map );
		$args .= '&size=' . preg_replace( "/[^0-9]/", "", $w_map_static ) . 'x' . preg_replace( "/[^0-9]/", "", $h_map );
		$args .= method_exists( 'Themify_Builder', 'getMapKey' ) ? '&key=' . Themify_Builder::getMapKey() : '';

		/* markers */
		if( ! empty( $markers ) ) {
			foreach( $markers as $marker ) {
				$marker = wp_parse_args( $marker, $marker_defaults );
				if( empty( $marker['image'] ) ) {
					$args .= '&markers=' . urlencode( $marker['address'] );
				} else {
					$args .= '&markers=icon:' . urlencode( $marker['image'] ) . '%7C' . urlencode( $marker['address'] );
				}
			}
		}

		/* Map style */
		if( '' != $style_map ) {
			$style = Builder_Maps_Pro::get_instance()->get_map_style( $style_map );
			foreach( $style as $rule ) {
				$args .= '&style=';
				if( isset( $rule->featureType ) ) {
					$args .= 'feature:' . $rule->featureType . '%7C';
				}
				if( isset( $rule->elementType ) ) {
					$args .= 'element:' . $rule->featureType . '%7C';
				}
				if( isset( $rule->stylers ) ) {
					foreach( $rule->stylers as $styler ) {
						foreach( $styler as $prop => $value ) {
							$value = str_replace( '#', '0x', $value );
							$args .= $prop . ':' . $value . '%7C';
						}
					}
				}
			}
		}

		if ( 'gmaps' == $map_link && ! empty( $map_center ) ) echo '<a href="http://maps.google.com/?q='. esc_attr( $map_center ) .'" target="_blank" rel="nofollow" title="Google Maps">';
		?>
		<img src="//maps.googleapis.com/maps/api/staticmap?<?php echo $args; ?>" />
		<?php
		if ( 'gmaps' == $map_link && ! empty( $map_center ) ) echo '</a>';
		?>

	<?php endif; ?>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module maps pro -->