<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Counter
 * 
 * Access original fields: $mod_settings
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
	$GLOBALS['ThemifyBuilder']->load_templates_js_css();
}

$chart_vars = apply_filters('themify_chart_init_vars', array(
	'trackColor' => 'rgba(0,0,0,.1)',
	'scaleColor' => 0,
	'scaleLength' => 0,
	'lineCap' => 'butt',
	'rotate' => 0,
	'size' => 150,
	'lineWidth' => 3,
	'animate' => 2000
));

$fields_default = array(
	'mod_title_counter' => '',
	'number_grouping' => '',
	'number_counter' => '',
	'label_counter' => '',
	'circle_percentage_counter' => '',
	'circle_stroke_counter' => '2',
	'circle_color_counter' => 'cccccc',
	'circle_background_counter' => '',
	'size_counter' => 'medium',
	'add_css_counter' => '',
	'animation_effect' => ''
);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

/* configure the chart size based on the option */
if( $size_counter == 'large' ) {
	$chart_vars['size'] = 200;
} elseif( $size_counter == 'small' ) {
	$chart_vars['size'] = 100;
}
$chart_class = ( $circle_percentage_counter == '' ) ? 'no-chart' : 'with-chart';

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $chart_class, 'size-' .$size_counter, $add_css_counter, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);

preg_match( '/([\D]*)([\d\.]*)([\D]*)/', (string)$number_counter, $parts );
$prefix = $parts[1];
$number = $parts[2];
$suffix = $parts[3];
$counter = explode( '.', (string)$number );
$decimals = isset( $counter[1] ) ? strlen( $counter[1] ) : 0;

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module counter -->
<div <?php echo $this->get_element_attributes( $container_props ); ?>>

	<?php if ( $mod_title_counter != '' ): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_counter, $fields_args ) ) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<?php if( '' != $circle_percentage_counter ) : ?>
		<div class="counter-chart" data-percent="<?php echo esc_attr( $circle_percentage_counter ); ?>" data-color="<?php echo $this->stylesheet->get_rgba_color( $circle_color_counter ); ?>" data-trackcolor="<?php echo $chart_vars['trackColor']; ?>" data-linecap="<?php echo $chart_vars['lineCap']; ?>" data-scalelength="<?php echo $chart_vars['scaleLength']; ?>" data-rotate="<?php echo $chart_vars['rotate']; ?>" data-size="<?php echo $chart_vars['size']; ?>" data-linewidth="<?php echo $circle_stroke_counter; ?>" data-animate="<?php echo $chart_vars['animate']; ?>">
	<?php endif; ?>

	<?php if( '' != $circle_background_counter ) : ?><div class="module-counter-background" style="background: <?php echo $this->stylesheet->get_rgba_color( $circle_background_counter ); ?>"></div><?php endif; ?>

	<div class="number">
		<span class="bc-timer" id="<?php echo $module_ID; ?>-bc-timer" data-from="0" data-to="<?php echo $number; ?>" data-suffix="<?php echo $suffix; ?>" data-prefix="<?php echo $prefix; ?>" data-decimals="<?php echo $decimals; ?>" data-grouping="<?php echo $number_grouping; ?>"></span>
	</div>

	<?php if( '' != $circle_percentage_counter ) : ?>
		</div><!-- .chart -->
	<?php endif; ?>

	<div class="counter-text"><?php echo $label_counter; ?></div>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module counter -->
