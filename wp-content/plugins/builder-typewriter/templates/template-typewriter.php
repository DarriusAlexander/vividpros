<?php
/* Exit if accessed directly */
defined( 'ABSPATH' ) or die( '-1' );

/**
 * Template Typewriter
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
	'mod_title_typewriter' => '',

	'builder_typewriter_tag' => 'p',
	'builder_typewriter_text_before' => '',
	'builder_typewriter_text_after' => '',
	'builder_typewriter_terms' => array(),

	'builder_typewriter_highlight_speed' => '50',
	'builder_typewriter_type_speed' => '60',
	'builder_typewriter_clear_delay' => '1.5',
	'builder_typewriter_type_delay' => '0.2',
	'builder_typewriter_typer_interval' => '1.5',

	'add_css_text' => '',
	'animation_effect' => '',

	'span_background_color' => '000000_1',
	'span_font_color' => 'ffffff_1',

	'span_padding_top' => '',
	'span_padding_top_unit' => '',

	'span_padding_right' => '',
	'span_padding_right_unit' => '',

	'span_padding_bottom' => '',
	'span_padding_bottom_unit' => '',

	'span_padding_left' => '',
	'span_padding_left_unit' => '',

	'span_border_top_color' => '',
	'span_border_top_width' => '',
	'span_border_top_style' => '',

	'span_border_right_color' => '',
	'span_border_right_width' => '',
	'span_border_right_style' => '',

	'span_border_bottom_color' => '',
	'span_border_bottom_width' => '',
	'span_border_bottom_style' => '',

	'span_border_left_color' => '',
	'span_border_left_width' => '',
	'span_border_left_style' => '',
);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

$container_class = implode(
	' ',
	apply_filters(
		'themify_builder_module_classes',
		array(
			'module',
			'module-'.$mod_name,
			$module_ID,
			esc_attr( $add_css_text ),
			$animation_effect
		),
		$mod_name,
		$module_ID,
		$fields_args
	)
);

// Terms to change
if(!wp_style_is('builder-typewriter')){
	wp_enqueue_style('builder-typewriter');
	wp_enqueue_script('tb_typewriter_frontend-scripts');
}
$typewriter_terms = array( 'targets' => array() );
foreach ( $builder_typewriter_terms as $term ) {
	$typewriter_terms['targets'][] = esc_attr( $term['builder_typewriter_term'] );
}
$typewriter_terms = json_encode( $typewriter_terms, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

// Highlight colors

if ( ! function_exists( 'hex2rgb' ) ) {
	function hex2rgb( $hex ) {
		$hex = str_replace("#", "", $hex);

		if (strlen($hex) == 3) {
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		} else {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}
		return implode(',', array($r, $g, $b));
	}
}

if ( ! function_exists( 'complexcolor2simplecolor' ) ) {
	function complexcolor2simplecolor( $complexcolor, $sep = '_' ) {
		if ( FALSE !== stripos( $complexcolor, $sep ) ) {
			$all = explode( $sep, $complexcolor );
			$complexcolor_hex = isset( $all[0] ) ? $all[0] : '';
			$complexcolor_opacity = isset( $all[1] ) ? $all[1] : '1';
		} else {
			$complexcolor_hex = $complexcolor;
			$complexcolor_opacity = '1';
		}
		$complexcolor_rgb = hex2rgb( $complexcolor_hex );
		$simplecolor = "rgba({$complexcolor_rgb},{$complexcolor_opacity})";
		return $simplecolor;
	}
}

$span_background_color = complexcolor2simplecolor( $span_background_color );
$span_font_color = complexcolor2simplecolor( $span_font_color );

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module typewriter -->
<div <?php echo $this->get_element_attributes( $container_props ); ?>>

	<?php
		// DYNAMIC STYLE

		$id = esc_attr( $module_ID );

		$margin_top = (int) $span_border_top_width;
		if ( $span_padding_top_unit == 'px' ) {
			$margin_top += (int) $span_padding_top;
		}

		$margin_right = (int) $span_border_right_width;
		if ( $span_padding_top_unit == 'px' ) {
			$margin_right += (int) $span_padding_right;
		}

		$margin_bottom = (int) $span_border_bottom_width;
		if ( $span_padding_top_unit == 'px' ) {
			$margin_bottom += (int) $span_padding_bottom;
		}

		$margin_left = (int) $span_border_left_width;
		if ( $span_padding_top_unit == 'px' ) {
			$margin_left += (int) $span_padding_left;
		}
		$style = '';
		
		
		if ( $margin_top > 0 || $margin_right > 0 || $margin_bottom > 0 || $margin_left > 0 ) {

			if ( $margin_top > 0 ) {
				$style .= "margin-top: -{$margin_top}px;\n";
			}
			if ( $margin_right > 0 ) {
				$style .= "margin-right: -{$margin_right}px;\n";
			}
			if ( $margin_bottom > 0 ) {
				$style .= "margin-bottom: -{$margin_bottom}px;\n";
			}
			if ( $margin_left > 0 ) {
				$style .= "margin-left: -{$margin_left}px;\n";
			}
		}

		if($style){
			$output= "<style type='text/css' scoped>\n";
			$output .= ".{$id} .typewriter-main-tag .typewriter-span span {\n";
			$output .=$style;
			$output .= "}\n";
			$output .= "</style>";
			echo $output;
		}
		
	?>

	<?php if ( $mod_title_typewriter != '' ) : ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_typewriter, $fields_args ) ) . $mod_settings['after_title'] ?>
	<?php endif ?>

	<?php do_action( 'themify_builder_before_template_content_render' ) ?>

	<?php
		$builder_typewriter_tag = esc_attr( $builder_typewriter_tag );
		$builder_typewriter_text_before = wp_kses_post( $builder_typewriter_text_before );
		$builder_typewriter_text_after = wp_kses_post( $builder_typewriter_text_after );
		
		$builder_typewriter_highlight_speed = esc_attr( $builder_typewriter_highlight_speed );
		$builder_typewriter_type_speed = esc_attr( $builder_typewriter_type_speed );
		$builder_typewriter_clear_delay = esc_attr( $builder_typewriter_clear_delay );
		$builder_typewriter_type_delay = esc_attr( $builder_typewriter_type_delay );
		$builder_typewriter_typer_interval = esc_attr( $builder_typewriter_typer_interval );

		$builder_typewriter_text_before = trim( $builder_typewriter_text_before ) . ' ';
		$builder_typewriter_text_after = ' '. trim( $builder_typewriter_text_after );

		echo "<{$builder_typewriter_tag} class='typewriter-main-tag'>";
		echo $builder_typewriter_text_before;
		echo "<span class='typewriter-span'".
				" data-typer-targets='{$typewriter_terms}'".
				" data-typer-highlight-speed='{$builder_typewriter_highlight_speed}'".
				" data-typer-type-speed='{$builder_typewriter_type_speed}'".
				" data-typer-clear-delay='{$builder_typewriter_clear_delay}'".
				" data-typer-type-delay='{$builder_typewriter_type_delay}'".
				" data-typer-interval='{$builder_typewriter_typer_interval}'".
				"></span>";
		echo $builder_typewriter_text_after;
		echo "</{$builder_typewriter_tag}>";
	?>

	<?php do_action( 'themify_builder_after_template_content_render' ) ?>
</div>
<!-- /module typewriter -->
