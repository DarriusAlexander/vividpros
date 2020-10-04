<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Progress Bar
 * 
 * Access original fields: $mod_settings
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
    $GLOBALS['ThemifyBuilder']->load_templates_js_css();
}

$fields_default = array(
	'mod_title_progressbar' => '',
	'progress_bars' => array(),
	'hide_percentage_text' => 'no',
	'add_css_progressbar' => '',
	'animation_effect' => ''
);

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $add_css_progressbar, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>
<!-- module progress bar -->
<div <?php echo $this->get_element_attributes( $container_props ); ?>>

	<?php if ( $mod_title_progressbar != '' ): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_progressbar, $fields_args ) ) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<div class="tf-progress-bar-wrap">
		<?php foreach ( $progress_bars as $key => $bar ) : ?>
			<div class="tf-progress-bar">

				<i class="tf-progress-bar-label"><?php echo $bar['bar_label']; ?></i>
				<span class="tf-progress-bar-bg" data-percent="<?php echo $bar['bar_percentage'] ?>" style="width: 0; background-color: <?php echo $this->stylesheet->get_rgba_color( $bar['bar_color'] ); ?>">

					<?php if( 'no' == $hide_percentage_text ) : ?>
						<span class="tf-progress-tooltip" id="<?php echo $module_ID . $key; ?>-progress-tooltip" data-to="<?php echo $bar['bar_percentage']; ?>" data-suffix="%" data-decimals="0"></span>
					<?php endif; ?>

				</span>

			</div><!-- .tf-progress-bar -->
		<?php endforeach; ?>
	</div><!-- .tf-progress-bar-wrap -->

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module progress bar -->