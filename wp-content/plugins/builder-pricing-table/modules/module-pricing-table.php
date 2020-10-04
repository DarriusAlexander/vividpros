<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Module Name: Pricing table
 * Description:
 */

class TB_Pricing_Table_Module extends Themify_Builder_Module {

	function __construct() {
		parent::__construct(array(
			'name' => __('Pricing Table', 'builder-pricing-table'),
			'slug' => 'pricing-table'
		));
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_color_pricing_table',
				'type' => 'layout',
				'label' => __('Appearance', 'builder-pricing-table'),
				'options' => array(
					array('img' => 'color-white.png', 'value' => 'white', 'label' => __('white',
								'builder-pricing-table')),
					array('img' => 'color-black.png', 'value' => 'black', 'label' => __('black',
								'builder-pricing-table')),
					array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('gray',
								'builder-pricing-table')),
					array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('blue',
								'builder-pricing-table')),
					array('img' => 'color-light-blue.png', 'value' => 'light-blue',
						'label' => __('light-blue', 'builder-pricing-table')),
					array('img' => 'color-green.png', 'value' => 'green', 'label' => __('green',
								'builder-pricing-table')),
					array('img' => 'color-light-green.png', 'value' => 'light-green',
						'label' => __('light-green', 'builder-pricing-table')),
					array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('purple',
								'builder-pricing-table')),
					array('img' => 'color-light-purple.png', 'value' => 'light-purple',
						'label' => __('light-purple', 'builder-pricing-table')),
					array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('brown',
								'builder-pricing-table')),
					array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('orange',
								'builder-pricing-table')),
					array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('yellow',
								'builder-pricing-table')),
					array('img' => 'color-red.png', 'value' => 'red', 'label' => __('red',
								'builder-pricing-table')),
					array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('pink',
								'builder-pricing-table')),
					array('img' => 'color-transparent.png', 'value' => 'transparent',
						'label' => __('Transparent', 'builder-pricing-table'))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_appearance_pricing_table',
				'type' => 'checkbox',
				'label' => __(' ', 'builder-pricing-table'),
				'default' => array(),
				'options' => array(
					array('name' => 'rounded', 'value' => __('Rounded',
								'builder-pricing-table')),
					array('name' => 'gradient', 'value' => __('Gradient',
								'builder-pricing-table')),
					array('name' => 'glossy', 'value' => __('Glossy', 'builder-pricing-table')),
					array('name' => 'embossed', 'value' => __('Embossed',
								'builder-pricing-table')),
					array('name' => 'shadow', 'value' => __('Shadow', 'builder-pricing-table'))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_title_pricing_table',
				'type' => 'text',
				'label' => __('Title', 'builder-pricing-table'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_title_icon_pricing_table',
				'type' => 'icon',
				'label' => __('Title icon', 'builder-pricing-table'),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_price_pricing_table',
				'type' => 'text',
				'label' => __('Price', 'builder-pricing-table'),
				'class' => 'large',
				'after' => sprintf('<br/><small>%s</small>',
						__('(eg. $29)', 'builder-pricing-table')),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_recurring_fee_pricing_table',
				'type' => 'text',
				'label' => __('Recurring Fee', 'builder-pricing-table'),
				'class' => 'large',
				'after' => sprintf('<br/><small>%s</small>',
						__('(eg. per month)', 'builder-pricing-table')),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_description_pricing_table',
				'type' => 'text',
				'label' => __('Description', 'builder-pricing-table'),
				'class' => 'large',
				'after' => sprintf('<br/><small>%s</small>',
						__('(eg. For Basic Users)', 'builder-pricing-table')),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_feature_list_pricing_table',
				'type' => 'textarea',
				'label' => __('Feature List', 'builder-pricing-table'),
				'class' => 'large exclude-from-reset-field textarea-feature',
				'description' => sprintf('<br/><small>%s</small>',
						__('Enter one line per each feature', 'builder-pricing-table')),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_unavailable_feature_list_pricing_table',
				'type' => 'textarea',
				'label' => __('Unavailable Feature List', 'builder-pricing-table'),
				'class' => 'large exclude-from-reset-field textarea-unfeature',
				'description' => sprintf('<br/><small>%s</small>',
						__('Unavailable feature list will appear greyed-out',
								'builder-pricing-table')),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_button_text_pricing_table',
				'type' => 'text',
				'label' => __('Buy Button Text', 'builder-pricing-table'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'multi_link_pricing_table',
				'type' => 'multi',
				'label' => __('Buy Button Link', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'mod_button_link_pricing_table',
						'type' => 'text',
						'class' => 'large',
						'prop' => 'href',
						'selector' => '.module-pricing-table',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'mod_pricing_blank_button',
						'type' => 'radio',
						'class' => 'large',
						'options' => array(
							'default' => __('Default', 'builder-pricing-table'),
							'modal' => __('Open in lightbox', 'builder-pricing-table'),
							'external' => __('Open in external link', 'builder-pricing-table'),
						),
						'default' => 'default',
						'render_callback' => array(
							'binding' => 'live'
						)
					)
				)
			),
			array(
				'id' => 'mod_pop_text_pricing_table',
				'type' => 'text',
				'label' => __('Pop-out Text', 'builder-pricing-table'),
				'class' => 'large',
				'after' => sprintf('<br/><small>%s</small>',
						__('(eg. Popular)', 'builder-pricing-table')),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'mod_enlarge_pricing_table',
				'type' => 'checkbox',
				'label' => __('Enlarge this pricing box', 'builder-pricing-table'),
				'options' => array(
					array('name' => 'enlarge', 'value' => __('', 'builder-pricing-table'))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);

		return $options;
	}

	public function get_default_settings() {
		return array(
			'mod_color_pricing_table' => 'blue',
			'mod_title_pricing_table' => esc_html__( 'Package Title', 'builder-pricing-table' ),
			'mod_price_pricing_table' => '$200',
			'mod_description_pricing_table' => esc_html__( 'Description Here', 'builder-pricing-table' ),
			'mod_feature_list_pricing_table' => esc_html__( "Feature Item\r\nFeature Item 2", 'builder-pricing-table' ),
			'mod_button_text_pricing_table' => esc_html__( 'Buy Now', 'builder-pricing-table' ),
			'mod_button_link_pricing_table' => 'https://themify.me'
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'builder-pricing-table' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'title' => __( 'Effect', 'builder-pricing-table' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'title' => __( 'Delay', 'builder-pricing-table' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-pricing-table' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'title' => __( 'Repeat', 'builder-pricing-table' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-pricing-table' ),
					),
				)
			)
		);

		return $animation;
	}

	public function get_styling() {
		$table_header = array(
			array(
				'id' => 'mod_title_font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-pricing-table'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-pricing-table .module-pricing-table-header',
			),
			array(
				'id' => 'mod_title_font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-pricing-table .module-pricing-table-header'
			),
			array(
				'id' => 'multi_title_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'font_size_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-pricing-table .module-pricing-table-header',
					),
					array(
						'id' => 'font_size_title_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => 'em', 'name' => __('em', 'builder-pricing-table')),
							array('value' => '%', 'name' => __('%', 'builder-pricing-table')),
						)
					)
				)
			),
			array(
				'id' => 'multi_title_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'mod_line_height_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-pricing-table .module-pricing-table-header',
					),
					array(
						'id' => 'mod_line_height_title_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => 'em', 'name' => __('em', 'builder-pricing-table'))
						)
					)
				)
			),
			array(
				'id' => 'mod_text_align_title',
				'label' => __('Text Align', 'builder-pricing-table'),
				'type' => 'radio',
				'meta' => array(
					array('value' => '', 'name' => __('Default', 'builder-pricing-table'), 'selected' => true),
					array('value' => 'left', 'name' => __('Left', 'builder-pricing-table')),
					array('value' => 'center', 'name' => __('Center', 'builder-pricing-table')),
					array('value' => 'right', 'name' => __('Right', 'builder-pricing-table')),
					array('value' => 'justify', 'name' => __('Justify',
								'builder-pricing-table'))
				),
				'prop' => 'text-align',
				'selector' => '.module-pricing-table .module-pricing-table-header'
			),
			array(
				'id' => 'mod_title_background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-pricing-table div.module-pricing-table-header'
			),
		);
		// Features list
		$feature_list = array(
			array(
				'id' => 'mod_feature_font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-pricing-table'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-pricing-table .module-pricing-table-content',
			),
			array(
				'id' => 'mod_feature_font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-pricing-table .module-pricing-table-content'
			),
			array(
				'id' => 'multi_content_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'font_size_content',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-pricing-table .module-pricing-table-content',
					),
					array(
						'id' => 'font_size_content_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => 'em', 'name' => __('em', 'builder-pricing-table'))
						)
					)
				)
			),
			array(
				'id' => 'multi_content_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'mod_line_height_content',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-pricing-table .module-pricing-table-content',
					),
					array(
						'id' => 'mod_line_height_content_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => 'em', 'name' => __('em', 'builder-pricing-table'))
						)
					)
				)
			),
			array(
				'id' => 'mod_text_align_content',
				'label' => __('Text Align', 'builder-pricing-table'),
				'type' => 'radio',
				'meta' => array(
					array('value' => '', 'name' => __('Default', 'builder-pricing-table'), 'selected' => true),
					array('value' => 'left', 'name' => __('Left', 'builder-pricing-table')),
					array('value' => 'center', 'name' => __('Center', 'builder-pricing-table')),
					array('value' => 'right', 'name' => __('Right', 'builder-pricing-table')),
					array('value' => 'justify', 'name' => __('Justify',
								'builder-pricing-table'))
				),
				'prop' => 'text-align',
				'selector' => '.module-pricing-table .module-pricing-table-content'
			),
			array(
				'id' => 'mod_feature_bg_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-pricing-table .module-pricing-table-content'
			),
		);
		//Pop text
		$pop_text = array(
			array(
				'id' => 'mod_pop_font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-pricing-table'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-pricing-table .module-pricing-table-pop',
			),
			array(
				'id' => 'mod_pop_font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-pricing-table .module-pricing-table-pop'
			)
		);
		//Buy button
		$buy_button = array(
			array(
				'id' => 'mod_button_font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-pricing-table'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-pricing-table .module-pricing-table-button',
			),
			array(
				'id' => 'mod_button_font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-pricing-table .module-pricing-table-button'
			),
			array(
				'id' => 'multi_button_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'font_size_button',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-pricing-table .module-pricing-table-button',
					),
					array(
						'id' => 'font_size_button_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => 'em', 'name' => __('em', 'builder-pricing-table'))
						)
					)
				)
			),
			array(
				'id' => 'multi_button_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'mod_line_height_button',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-pricing-table .module-pricing-table-button',
					),
					array(
						'id' => 'mod_line_height_button_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => 'em', 'name' => __('em', 'builder-pricing-table'))
						)
					)
				)
			),
			array(
				'id' => 'mod_text_align_button',
				'label' => __('Text Align', 'builder-pricing-table'),
				'type' => 'radio',
				'meta' => array(
					array('value' => '', 'name' => __('Default', 'builder-pricing-table'), 'selected' => true),
					array('value' => 'left', 'name' => __('Left', 'builder-pricing-table')),
					array('value' => 'center', 'name' => __('Center', 'builder-pricing-table')),
					array('value' => 'right', 'name' => __('Right', 'builder-pricing-table')),
					array('value' => 'justify', 'name' => __('Justify',
								'builder-pricing-table'))
				),
				'prop' => 'text-align',
				'selector' => '.module-pricing-table .module-pricing-table-button'
			),
			array(
				'id' => 'mod_button_bg_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-pricing-table .module-pricing-table-button'
			),
		);
		$general = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html' => '<h4>' . __('Font', 'builder-pricing-table') . '</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-pricing-table'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.ui.module-pricing-table',
			),
			array(
				'id' => 'separator_background',
				'type' => 'separator',
				'meta' => array('html'=>'<hr /><h4>'.__('Background', 'builder-pricing-table').'</h4>'),
			),
			array(
				'id' => 'background_image',
				'type' => 'image_and_gradient',
				'label' => __('Background Image', 'builder-pricing-table'),
				'class' => 'xlarge',
				'prop' => 'background-image',
				'selector' => '.ui.module-pricing-table',
				'option_js' => true
			),
			array(
				'id' 		=> 'background_repeat',
				'label'		=> __('Background Repeat', 'builder-pricing-table'),
				'type' 		=> 'select',
				'default'	=> '',
				'meta'		=> array(
					array('value' => 'repeat', 'name' => __('Repeat All', 'builder-pricing-table')),
					array('value' => 'repeat-x', 'name' => __('Repeat Horizontally', 'builder-pricing-table')),
					array('value' => 'repeat-y', 'name' => __('Repeat Vertically', 'builder-pricing-table')),
					array('value' => 'no-repeat', 'name' => __('Do not repeat', 'builder-pricing-table')),
					array('value' => 'fullcover', 'name' => __('Fullcover', 'builder-pricing-table'))
				),
				'prop' => 'background-repeat',
				'selector' => '.ui.module-pricing-table',
				'wrap_with_class' => 'tf-group-element tf-group-element-image',
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-pricing-table'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.ui.module-pricing-table'
			),
			// Border
			array(
				'type' => 'separator',
				'meta' => array('html' => '<hr />')
			),
			array(
				'id' => 'separator_border',
				'type' => 'separator',
				'meta' => array('html' => '<h4>' . __('Border', 'builder-pricing-table') . '</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-pricing-table'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-pricing-table'
					)
				)
			),
			array(
				'id' => 'multi_border_right',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'border_right_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-right-color',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_right_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-right-width',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_right_style',
						'type' => 'select',
						'description' => __('right', 'builder-pricing-table'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-pricing-table'
					)
				)
			),
			array(
				'id' => 'multi_border_bottom',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'border_bottom_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-bottom-color',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-pricing-table'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-pricing-table'
					)
				)
			),
			array(
				'id' => 'multi_border_left',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'border_left_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-left-color',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-pricing-table'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-pricing-table'
					)
				)
			),
			// "Apply all" // apply all border
			array(
				'id' => 'checkbox_border_apply_all',
				'class' => 'style_apply_all style_apply_all_border',
				'type' => 'checkbox',
				'label' => false,
				'default'=>'border',
				'options' => array(
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-pricing-table' ) )
				)
			),
			// Margin
			array(
				'type' => 'separator',
				'meta' => array('html' => '<hr />')
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array('html' => '<h4>' . __('Margin', 'builder-pricing-table') . '</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-pricing-table'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-pricing-table'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => '%', 'name' => __('%', 'builder-pricing-table'))
						)
					),
				)
			),
			array(
				'id' => 'multi_margin_right',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'margin_right',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-right',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-pricing-table'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => '%', 'name' => __('%', 'builder-pricing-table'))
						)
					),
				)
			),
			array(
				'id' => 'multi_margin_bottom',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'margin_bottom',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-bottom',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-pricing-table'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => '%', 'name' => __('%', 'builder-pricing-table'))
						)
					),
				)
			),
			array(
				'id' => 'multi_margin_left',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'margin_left',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-left',
						'selector' => '.module-pricing-table'
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-pricing-table'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pricing-table')),
							array('value' => '%', 'name' => __('%', 'builder-pricing-table'))
						)
					),
				)
			),
			// "Apply all" // apply all margin
			array(
				'id' => 'checkbox_margin_apply_all',
				'class' => 'style_apply_all style_apply_all_margin',
				'type' => 'checkbox',
				'label' => false,
				'options' => array(
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'builder-pricing-table' ) )
				)
			),
		);

		return array(
			array(
				'type' => 'tabs',
				'id' => 'module-styling',
				'tabs' => array(
					'general' => array(
						'label' => __('General', 'builder-pricing-table'),
						'fields' => $general
					),
					'top-table-header' => array(
						'label' => __('Top Table Header', 'builder-pricing-table'),
						'fields' => $table_header
					),
					'feature-list' => array(
						'label' => __('Features list', 'builder-pricing-table'),
						'fields' => $feature_list
					),
					'buy-button' => array(
						'label' => __('Buy Button', 'builder-pricing-table'),
						'fields' => $buy_button
					),
					'pop-text' => array(
						'label' => __('Pop-out Text', 'builder-pricing-table'),
						'fields' => $pop_text
					),
				)
			),
		);
	}

	protected function _visual_template() {
		$module_args = $this->get_module_args();?>

		<div class="ui module module-<?php echo esc_attr( $this->slug ); ?> <# data.mod_pop_text_pricing_table && print( 'pricing-pop' ) #> <# data.mod_enlarge_pricing_table == 'enlarge' && print( 'pricing-enlarge' ) #> {{ data.mod_appearance_pricing_table }} {{ data.mod_color_pricing_table }}">
			<?php do_action('themify_builder_before_template_content_render'); ?>

			<# if( data.mod_pop_text_pricing_table ) { #>
				<span class="fa module-pricing-table-pop">{{ data.mod_pop_text_pricing_table }}</span>
			<# } #>

			<div class="module-pricing-table-header ui {{ data.mod_color_pricing_table }} {{ data.mod_appearance_pricing_table }}" >
				<# if( data.mod_title_pricing_table ) { #>
					<span class="module-pricing-table-title">
						<# if( data.mod_title_icon_pricing_table ) { #>
							<i class="fa {{ data.mod_title_icon_pricing_table }}"></i>
						<# } #>
						<span>{{ data.mod_title_pricing_table }}</span>
					</span>
				<# } #>

				<# if( data.mod_price_pricing_table ) { #>
					<span class="module-pricing-table-price">{{ data.mod_price_pricing_table }}</span>
				<# } #>

				<# if( data.mod_recurring_fee_pricing_table ) { #>
					<p class="module-pricing-table-reccuring-fee">{{ data.mod_recurring_fee_pricing_table }}</p>
				<# } #>

				<# if( data.mod_description_pricing_table ) { #>
					<p	class="module-pricing-table-description">{{ data.mod_description_pricing_table }}</p>
				<# } #>
			</div><!-- .module-pricing-table-header -->
			<div class="module-pricing-table-content" style="background-color: <# print( data.mod_feature_bg_color ? '#' + data.mod_feature_bg_color.substr( 0, 6 ) : 'transparent' ); #>">
				<# if( data.mod_feature_list_pricing_table ) { 
					_.each( data.mod_feature_list_pricing_table.split( "\n" ), function( line ) { #>
						<p class="module-pricing-table-features">{{ line }}</p>
					<# } );
				} 
				
				if( data.mod_unavailable_feature_list_pricing_table ) {
					_.each( data.mod_unavailable_feature_list_pricing_table.split( "\n" ), function( line ) { #>
						<p class="module-pricing-table-features unavailable-features">{{ line }}</p>
					<# } );
				}

				if( data.mod_button_text_pricing_table ) { #>
					<a class="module-pricing-table-button ui {{ data.mod_color_pricing_table }} {{ data.mod_appearance_pricing_table }}" href="{{ data.mod_button_link_pricing_table }}">
						{{{ data.mod_button_text_pricing_table }}}
					</a>
				<# } #>
				
			</div><!-- .module-pricing-table-content -->

			<?php do_action('themify_builder_after_template_content_render'); ?>
		</div>
	<?php
	}

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Pricing_Table_Module');
