<?php
/* Exit if accessed directly */
defined( 'ABSPATH' ) or die( '-1' );

/**
 * Module Name: Typewriter
 * Description: Display Typewriter content
 */
class TB_Typewriter extends Themify_Builder_Module {
	
	public function __construct() {
		parent::__construct(
			array(
				'name' => __( 'Typewriter', 'builder-typewriter' ),
				'slug' => 'typewriter'
			)
		);
	}

	public function get_title( $module ) {
		$text = isset( $module['mod_settings']['mod_title_typewriter'] ) ? $module['mod_settings']['mod_title_typewriter'] : '';
		$return = wp_trim_words( $text, 100 );
		return $return;
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_typewriter',
				'type' => 'text',
				'label' => __( 'Module Title', 'builder-typewriter' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			// Typewriter
			array(
				'id' => 'separator_typewriter',
				'type' => 'separator',
				'meta' => array( 'html' => '<hr /><h4>'. __( 'Typewriter', 'builder-typewriter' ) .'</h4>' ),
			),
			array(
				'id' => 'builder_typewriter_tag',
				'type' => 'select',
				'label' => __( 'Text Tag', 'builder-typewriter' ),
				'options' => array(
					'p' => __( 'p', 'builder-typewriter' ),
					'h1' => __( 'h1', 'builder-typewriter' ),
					'h2' => __( 'h2', 'builder-typewriter' ),
					'h3' => __( 'h3', 'builder-typewriter' ),
					'h4' => __( 'h4', 'builder-typewriter' ),
					'h5' => __( 'h5', 'builder-typewriter' ),
					'h6' => __( 'h6', 'builder-typewriter' ),
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'builder_typewriter_text_before',
				'type' => 'text',
				'label' => __( 'Before Text', 'builder-typewriter' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'builder_typewriter_text_after',
				'type' => 'text',
				'label' => __( 'After Text', 'builder-typewriter' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'builder_typewriter_terms',
				'type' => 'builder',
				'options' => array(
					array(
						'id' => 'builder_typewriter_term',
						'type' => 'text',
						'label' => __( 'Text', 'builder-typewriter' ),
						'class' => 'large',
						'render_callback' => array(
							'binding' => 'live',
							'repeater' => 'builder_typewriter_terms'
						)
					)
				),
				'new_row_text' => __( 'Add New Text', 'builder-typewriter' ),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			// Effects
			array(
				'id' => 'separator_effect',
				'type' => 'separator',
				'meta' => array( 'html' => '<hr /><h4>'. __( 'Effects', 'builder-typewriter' ) .'</h4>' ),
			),
			array(
				'id' => 'builder_typewriter_highlight_speed',
				'type' => 'select',
				'label' => __( 'Highlight Speed', 'builder-typewriter' ),
				'default' => 'Normal',
				'options' => array(
					'50' => __( 'Normal', 'builder-typewriter' ),
					'100' => __( 'Slow', 'builder-typewriter' ),
					'25' => __( 'Fast', 'builder-typewriter' ),
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'builder_typewriter_type_speed',
				'type' => 'select',
				'label' => __( 'Type Speed', 'builder-typewriter' ),
				'default' => 'Normal',
				'options' => array(
					'60' => __( 'Normal', 'builder-typewriter' ),
					'120' => __( 'Slow', 'builder-typewriter' ),
					'35' => __( 'Fast', 'builder-typewriter' ),
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'builder_typewriter_clear_delay',
				'type' => 'text',
				'label' => __( 'Clear Delay', 'builder-typewriter' ),
				'class' => 'small',
				'after' => __( 'second(s)', 'builder-typewriter' ),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'builder_typewriter_type_delay',
				'type' => 'text',
				'label' => __( 'Type Delay', 'builder-typewriter' ),
				'class' => 'small',
				'after' => __( 'second(s)', 'builder-typewriter' ),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'builder_typewriter_typer_interval',
				'type' => 'text',
				'label' => __( 'Highlight Delay', 'builder-typewriter' ),
				'class' => 'small',
				'after' => __( 'second(s)', 'builder-typewriter' ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
                        ,
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'add_css_text',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-typewriter'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'builder-typewriter' ) ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);

		return $options;
	}

	public function get_default_settings() {
		return array(
			'builder_typewriter_tag' => 'h3',
			'builder_typewriter_text_before' => esc_html__( 'This is', 'builder-typewriter' ),
			'builder_typewriter_text_after' => esc_html__( 'addon', 'builder-typewriter' ),
			'builder_typewriter_terms' => array( array(
					'builder_typewriter_term' => esc_html__( 'Typewriter', 'builder-typewriter' )
				)
			),
			'span_background_color' => 'ffff00_1'
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'label' => __( 'Effect', 'builder-typewriter' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'label' => __( 'Delay', 'builder-typewriter' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-typewriter' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'label' => __( 'Repeat', 'builder-typewriter' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-typewriter' ),
					),
				)
			)
		);

		return $animation;
	}

	public function get_styling() {
		$general = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-typewriter').'</h4>'),
			),
			array(
				'id' => 'background_image',
				'type' => 'image',
				'label' => __('Background Image', 'builder-typewriter'),
				'class' => 'xlarge',
				'prop' => 'background-image',
				'selector' => '.module-typewriter'
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-typewriter'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-typewriter',
			),
			// Background repeat
			array(
				'id' 		=> 'background_repeat',
				'label'		=> __('Background Repeat', 'builder-typewriter'),
				'type' 		=> 'select',
				'default'	=> '',
				'meta'		=> array(
					array('value' => 'repeat', 'name' => __('Repeat All', 'builder-typewriter')),
					array('value' => 'repeat-x', 'name' => __('Repeat Horizontally', 'builder-typewriter')),
					array('value' => 'repeat-y', 'name' => __('Repeat Vertically', 'builder-typewriter')),
					array('value' => 'repeat-none', 'name' => __('Do not repeat', 'builder-typewriter')),
					array('value' => 'fullcover', 'name' => __('Fullcover', 'builder-typewriter'))
				),
				'prop' => 'background-repeat',
				'selector' => '.module-typewriter'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-typewriter').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-typewriter'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-typewriter', '.module-typewriter p', '.module-typewriter h1', '.module-typewriter h2', '.module-typewriter h3:not(.module-title)', '.module-typewriter h4', '.module-typewriter h5', '.module-typewriter h6' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-typewriter'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-typewriter', '.module-typewriter p', '.module-typewriter h1', '.module-typewriter h2', '.module-typewriter h3:not(.module-title)', '.module-typewriter h4', '.module-typewriter h5', '.module-typewriter h6' ),
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => array( '.module-typewriter', '.module-typewriter p', '.module-typewriter h1', '.module-typewriter h2', '.module-typewriter h3:not(.module-title)', '.module-typewriter h4', '.module-typewriter h5', '.module-typewriter h6' ),
					),
					array(
						'id' => 'font_size_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => 'em', 'name' => __('em', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter')),
						)
					)
				),
			),
			array(
				'id' => 'multi_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => array( '.module-typewriter', '.module-typewriter p', '.module-typewriter h1', '.module-typewriter h2', '.module-typewriter h3:not(.module-title)', '.module-typewriter h4', '.module-typewriter h5', '.module-typewriter h6' ),
					),
					array(
						'id' => 'line_height_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => 'em', 'name' => __('em', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					)
				)
			),
			array(
				'id' => 'text_align',
				'label' => __( 'Text Align', 'builder-typewriter' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'builder-typewriter' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'builder-typewriter' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'builder-typewriter' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'builder-typewriter' ) ),
					array( 'value' => 'justify', 'name' => __( 'Justify', 'builder-typewriter' ) )
				),
				'prop' => 'text-align',
				'selector' => '.module-typewriter'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-typewriter').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			array(
				'id' => 'multi_padding_right',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'padding_right',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-right',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			array(
				'id' => 'multi_padding_bottom',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'padding_bottom',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-bottom',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			array(
				'id' => 'multi_padding_left',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'padding_left',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-left',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			// "Apply all" // apply all padding
			array(
				'id' => 'checkbox_padding_apply_all',
				'class' => 'style_apply_all style_apply_all_padding',
				'type' => 'checkbox',
				'label' => false,
				'options' => array(
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-typewriter' ) )
				)
			),
			// Margin
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Margin', 'builder-typewriter').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
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
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-right',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
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
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-bottom',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
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
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-left',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
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
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'builder-typewriter' ) )
				)
			),
			// Border
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_border',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-typewriter').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-typewriter',
					),
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
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_right_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-right-width',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_right_style',
						'type' => 'select',
						'description' => __('right', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-typewriter',
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
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-typewriter',
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
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-typewriter',
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-typewriter',
					)
				)
			),
			// "Apply all" // apply all border
			array(
				'id' => 'checkbox_border_apply_all',
				'class' => 'style_apply_all style_apply_all_border',
				'type' => 'checkbox',
				'label' => false,
				'options' => array(
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-typewriter' ) )
				)
			)
		);

		$typewriter = array(
			// Background
			array(
				'id' => 'separator_image_background_span',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>'. __( 'Background', 'builder-typewriter' ). '</h4>' ),
			),
			array(
				'id' => 'span_background_color',
				'type' => 'color',
				'label' => __( 'Background Color (Highlighted)', 'builder-typewriter' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => ' .typewriter-main-tag .typewriter-span span'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_font_span',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>'. __( 'Font', 'builder-typewriter' ) .'</h4>' ),
			),
			array(
				'id' => 'span_font_color',
				'type' => 'color',
				'label' => __( 'Font Color (Highlighted)', 'builder-typewriter' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => ' .typewriter-main-tag .typewriter-span span'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-typewriter').'</h4>'),
			),
			array(
				'id' => 'span_multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding (Highlighted)', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'span_padding_top',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-top',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			array(
				'id' => 'span_multi_padding_right',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'span_padding_right',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-right',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			array(
				'id' => 'span_multi_padding_bottom',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'span_padding_bottom',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-bottom',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			array(
				'id' => 'span_multi_padding_left',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'span_padding_left',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-left',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-typewriter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-typewriter')),
							array('value' => '%', 'name' => __('%', 'builder-typewriter'))
						)
					),
				)
			),
			// "Apply all" // apply all padding
			array(
				'id' => 'checkbox_padding_apply_all_span',
				'class' => 'style_apply_all style_apply_all_padding',
				'type' => 'checkbox',
				'label' => false,
				'options' => array(
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-typewriter' ) )
				)
			),
			// Border
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_border_span',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-typewriter').'</h4>'),
			),
			array(
				'id' => 'span_multi_border_top',
				'type' => 'multi',
				'label' => __('Border (Highlighted)', 'builder-typewriter'),
				'fields' => array(
					array(
						'id' => 'span_border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
				)
			),
			array(
				'id' => 'span_multi_border_right',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'span_border_right_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-right-color',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_right_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-right-width',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_right_style',
						'type' => 'select',
						'description' => __('right', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					)
				)
			),
			array(
				'id' => 'span_multi_border_bottom',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'span_border_bottom_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-bottom-color',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					)
				)
			),
			array(
				'id' => 'span_multi_border_left',
				'type' => 'multi',
				'label' => '',
				'fields' => array(
					array(
						'id' => 'span_border_left_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-left-color',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					),
					array(
						'id' => 'span_border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-typewriter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => ' .typewriter-main-tag .typewriter-span span'
					)
				)
			),
			// "Apply all" // apply all border
			array(
				'id' => 'checkbox_border_apply_all_span',
				'class' => 'style_apply_all style_apply_all_border',
				'type' => 'checkbox',
				'label' => false,
				'options' => array(
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-typewriter' ) )
				)
			)
		);

		return array(
			array(
				'type' => 'tabs',
				'id' => 'module-styling',
				'tabs' => array(
					'general' => array(
		        	'label' => __( 'General', 'builder-typewriter' ),
					'fields' => $general
					),
					'typewriter' => array(
						'label' => __( 'Typewriter', 'builder-typewriter' ),
						'fields' => $typewriter
					)
				)
			),
		);
	}

	protected function _visual_template() {
		$module_args = $this->get_module_args();?>

		<#
			_.defaults( data, {
				builder_typewriter_highlight_speed: 50,
				builder_typewriter_type_speed: 60,
				builder_typewriter_clear_delay: 1.5,
				builder_typewriter_type_delay: 0.2,
				builder_typewriter_typer_interval: 1.5
			});

			var typewriterTerms = { targets: [] };
			if( data.builder_typewriter_terms ) {
				data.builder_typewriter_terms.forEach( function( el ) {
					typewriterTerms.targets.push( el.builder_typewriter_term );
				} );
			}

			typewriterTerms = JSON.stringify( typewriterTerms );
		#>

		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.add_css_text }}">
			<# if( data.mod_title_typewriter ) { #>
				<?php echo $module_args['before_title']; ?>
				{{{ data.mod_title_typewriter }}}
				<?php echo $module_args['after_title']; ?>
			<# } #>

			<?php do_action( 'themify_builder_before_template_content_render' ) ?>

			<{{{ data.builder_typewriter_tag }}} class="typewriter-main-tag">
				{{{ data.builder_typewriter_text_before }}} 
				<span class="typewriter-span"
					data-typer-targets="{{ typewriterTerms }}"
					data-typer-highlight-speed="{{ data.builder_typewriter_highlight_speed }}"
					data-typer-type-speed="{{ data.builder_typewriter_type_speed }}"
					data-typer-clear-delay="{{ data.builder_typewriter_clear_delay }}"
					data-typer-type-delay="{{ data.builder_typewriter_type_delay }}"
					data-typer-interval="{{ data.builder_typewriter_typer_interval }}"></span>
				 {{{ data.builder_typewriter_text_after }}}
			</{{{ data.builder_typewriter_tag }}}>

			<?php do_action( 'themify_builder_after_template_content_render' ) ?>
		</div>
	<?php
	}

}

Themify_Builder_Model::register_module( 'TB_Typewriter' );
