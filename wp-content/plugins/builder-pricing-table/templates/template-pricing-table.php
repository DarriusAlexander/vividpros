<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Template Pricing Table
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

if( method_exists( $GLOBALS['ThemifyBuilder'], 'load_templates_js_css' ) ) {
	$GLOBALS['ThemifyBuilder']->load_templates_js_css();
}

$fields_default = array(
	'mod_appearance_pricing_table' => '',
	'mod_color_pricing_table' => 'white',
	'mod_enlarge_pricing_table' => '',
	'mod_title_pricing_table' => '',
	'mod_title_icon_pricing_table' => '',
	'mod_price_pricing_table' => '',
	'mod_recurring_fee_pricing_table' => '',
	'mod_description_pricing_table' => '',
	'mod_feature_list_pricing_table' => '',
	'mod_feature_bg_color' => '',
	'mod_unavailable_feature_list_pricing_table' => '',
	'mod_button_text_pricing_table' => '',
	'mod_button_link_pricing_table' => '',
	'mod_pop_text_pricing_table' => '',
	'animation_effect' => ''
);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args);
$animation_effect_pricing = $this->parse_animation_effect( $animation_effect, $fields_args );

if ( '' != $mod_appearance_pricing_table )
	$mod_appearance_pricing_table = $this->get_checkbox_data( $mod_appearance_pricing_table );

$container_class = array( 'ui', $mod_color_pricing_table, 'module', 'module-' . $mod_name, $module_ID, $mod_appearance_pricing_table, $animation_effect_pricing );
if( $mod_enlarge_pricing_table == 'enlarge' ) {
	$container_class[] = "pricing-enlarge";
}
if( ! empty( $mod_pop_text_pricing_table ) ) {
	$container_class[] = "pricing-pop";
}

$container_class = implode( ' ',
	apply_filters( 'themify_builder_module_classes', $container_class, $mod_name, $module_ID, $fields_args )
);
$feature_list = explode("\n", $mod_feature_list_pricing_table);
$unavailable_feature_list = explode("\n", $mod_unavailable_feature_list_pricing_table);

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class
), $fields_args, $mod_name, $module_ID );
?>

<div <?php echo $this->get_element_attributes( $container_props ); ?>>

	<?php do_action('themify_builder_before_template_content_render'); ?>

	<?php if (!empty($mod_pop_text_pricing_table)) { ?>
		<span class="fa module-pricing-table-pop"><?php echo $mod_pop_text_pricing_table; ?></span>
	<?php } ?>

		<div class="module-pricing-table-header ui <?php echo $mod_color_pricing_table . ' '; echo $mod_settings['mod_appearance_pricing_table']; ?>" >
			<?php if (!empty($mod_title_pricing_table)) { ?>
			<span class="module-pricing-table-title">
				<?php if (!empty($mod_title_icon_pricing_table)) { ?>
					<i class="fa <?php echo $mod_title_icon_pricing_table; ?>"></i>
				<?php } ?>
				<span ><?php echo $mod_title_pricing_table; ?></span>
			</span>
			<?php } ?>
			<?php if (!empty($mod_price_pricing_table)) { ?>
				<span class="module-pricing-table-price"><?php echo $mod_price_pricing_table; ?></span>
			<?php } ?>
			<?php if (!empty($mod_recurring_fee_pricing_table)) { ?>
				<p class="module-pricing-table-reccuring-fee"><?php echo $mod_recurring_fee_pricing_table; ?></p>
			<?php } ?>
			<?php if (!empty($mod_description_pricing_table)) { ?>
				<p	class="module-pricing-table-description"><?php echo $mod_description_pricing_table; ?></p>
			<?php } ?>
		</div><!-- .module-pricing-table-header -->
		<div class="module-pricing-table-content" style="<?php printf( 'background-color:%s;', $mod_feature_bg_color ? '#' . substr($mod_feature_bg_color, 0, 6) : 'transparent' ); ?>">
			<?php
			if (!empty($mod_feature_list_pricing_table)) {
				foreach ($feature_list as $line) {
			?>
				<p class="module-pricing-table-features"><?php echo $line; ?></p>
			<?php
				}
			}
			if (!empty($mod_unavailable_feature_list_pricing_table)) {
				foreach ($unavailable_feature_list as $line) {
			?>
				<p class="module-pricing-table-features unavailable-features"><?php echo $line; ?></p>
			<?php
				}
			}
			?>
			<?php if (!empty($mod_button_text_pricing_table)) { ?>
				<a class="module-pricing-table-button <?php 
				if ($mod_pricing_blank_button == "modal") { 
					echo 'lightbox-builder themify_lightbox ';
				} ?> ui <?php echo $mod_color_pricing_table . ' '; echo $mod_settings['mod_appearance_pricing_table']; ?>" href="<?php
			   if ($mod_pricing_blank_button == "modal" && !empty($mod_button_link_pricing_table)) {
					echo themify_get_lightbox_iframe_link( $mod_button_link_pricing_table );
			   } else if (!empty($mod_button_link_pricing_table)) {
					echo $mod_button_link_pricing_table;
			   } else {
					echo "#";
			   }
			   ?>" <?php
			   if ($mod_pricing_blank_button == "external") {
				   echo "target='_blank'";
			   } ?>>
					<?php echo $mod_button_text_pricing_table; ?> 
				</a> 
			<?php } ?>
	</div><!-- .module-pricing-table-content -->

	<?php do_action('themify_builder_after_template_content_render'); ?>

</div><!-- /module pricing-table -->