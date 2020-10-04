<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: WooCommerce Product Categories
 */
class TB_Product_Categories_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Product Categories', 'builder-wc'),
			'slug' => 'product-categories'
		));
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title',
				'type' => 'text',
				'label' => __('Module Title', 'builder-wc'),
				'class' => 'large'
			),
			array(
				'id' => 'columns',
				'type' => 'layout',
				'label' => __('Layout', 'builder-wc'),
				'options' => array(
					array('img' => 'list-post.png', 'value' => '1', 'label' => __('1 Column', 'builder-wc')),
					array('img' => 'grid2.png', 'value' => '2', 'label' => __('2 Columns', 'builder-wc')),
					array('img' => 'grid3.png', 'value' => '3', 'label' => __('3 Columns', 'builder-wc')),
					array('img' => 'grid4.png', 'value' => '4', 'label' => __('4 Columns', 'builder-wc')),
				)
			),
			array(
				'id' => 'child_of',
				'type' => 'product_categories',
				'label' => __('Categories', 'builder-wc'),
				'description' => __('Show all categories or sub-categories of a category.', 'builder-wc'),
			),
			array(
				'id' => 'orderby',
				'type' => 'select',
				'label' => __('Order By', 'builder-wc'),
				'options' => array(
					'name' => __('Name', 'builder-wc'),
					'id' => __('ID', 'builder-wc'),
					'count' => __('Product Count', 'builder-wc'),
				)
			),
			array(
				'id' => 'order',
				'type' => 'select',
				'label' => __('Order', 'builder-wc'),
				'help' => __('Descending = show newer posts first', 'builder-wc'),
				'options' => array(
					'desc' => __('Descending', 'builder-wc'),
					'asc' => __('Ascending', 'builder-wc')
				)
			),
			array(
				'id' => 'hide_empty',
				'type' => 'select',
				'label' => __('Hide Empty Categories', 'builder-wc'),
				'options' => array(
					'yes' => __('Yes', 'builder-wc'),
					'no' => __('No', 'builder-wc'),
				)
			),
			array(
				'id' => 'pad_counts',
				'type' => 'select',
				'label' => __('Show Product Counts', 'builder-wc'),
				'options' => array(
					'yes' => __('Yes', 'builder-wc'),
					'no' => __('No', 'builder-wc'),
				)
			),
			array(
				'id' => 'latest_products',
				'type' => 'select',
				'label' => __('Latest Products', 'builder-wc'),
				'options' => array(
					'0' => 0,
					'1' => 1,
					'2' => 2,
					'3' => 3,
					'4' => 4,
					'5' => 5,
					'6' => 6,
					'7' => 7,
					'8' => 8,
					'9' => 9,
					'10' => 10,
				),
				'help' => __('Number of latest products to show.', 'builder-wc'),
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_products',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-wc'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'builder-wc') )
			)
		);
	}

	public function get_default_settings() {
		return array(
			'latest_products' => 3,
			'columns' => 3
		);
	}

	public function get_animation() {
		return array(
			array(
				'id' => 'animation_effect',
				'type' => 'animation_select',
				'label' => __( 'Effect', 'builder-countdown' ),
				'class' => ''
			)
		);
	}

	public function get_styling() {
		return array(
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-wc').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-wc'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-product-categories'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-wc').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-wc'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-product-categories'
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-wc'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-product-categories'
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-wc'),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'font_size_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => 'em', 'name' => __('em', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc')),
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-wc'),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'line_height_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => 'em', 'name' => __('em', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
						)
					)
				)
			),
			array(
				'id' => 'text_align',
				'label' => __( 'Text Align', 'builder-wc' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'builder-wc' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'builder-wc' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'builder-wc' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'builder-wc' ) ),
					array( 'value' => 'justify', 'name' => __( 'Justify', 'builder-wc' ) )
				),
				'prop' => 'text-align',
				'selector' => '.module-product-categories'
			),
			// Link
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_link',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Link', 'builder-wc').'</h4>'),
			),
			array(
				'id' => 'link_color',
				'type' => 'color',
				'label' => __('Color', 'builder-wc'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-product-categories a'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'builder-wc' ),
				'meta'	=> array(
					array('value' => '',   'name' => '', 'selected' => true),
					array('value' => 'underline',   'name' => __('Underline', 'builder-wc')),
					array('value' => 'overline', 'name' => __('Overline', 'builder-wc')),
					array('value' => 'line-through',  'name' => __('Line through', 'builder-wc')),
					array('value' => 'none',  'name' => __('None', 'builder-wc'))
				),
				'prop' => 'text-decoration',
				'selector' => '.module-product-categories a'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-wc').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-wc'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
						'class' => 'xsmall',
						'prop' => 'padding-right',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
						'class' => 'xsmall',
						'prop' => 'padding-bottom',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
						'class' => 'xsmall',
						'prop' => 'padding-left',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-wc' ) )
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
				'meta' => array('html'=>'<h4>'.__('Margin', 'builder-wc').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-wc'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-wc'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-wc')),
							array('value' => '%', 'name' => __('%', 'builder-wc'))
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
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'builder-wc' ) )
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
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-wc').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-wc'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-wc'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-product-categories'
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
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'border_right_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
					),
					array(
						'id' => 'border_right_style',
						'type' => 'select',
						'description' => __('right', 'builder-wc'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-product-categories'
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
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-wc'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-product-categories'
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
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-product-categories'
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-wc'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-product-categories'
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
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-wc' ) )
				)
			),
		);
	}
}

function themify_builder_field_product_categories( $field, $module_name ) {
	echo '
	<div class="themify_builder_field ' . $field['id'] . '">
		<div class="themify_builder_label">'. $field['label'] .'</div>
		<div class="themify_builder_input">';

			wp_dropdown_categories( array(
				'taxonomy' => 'product_cat',
				'class' => 'tfb_lb_option',
				'show_option_all' => __( 'All Categories', 'builder-wc' ),
				'show_option_none'   => __( 'Only Top Level Categories', 'builder-wc' ),
				'option_none_value'  => 'top-level',
				'hide_empty' => 1,
				'echo' => true,
				'name' => $field['id'],
				'selected' => '',
				'value_field' => 'slug',
			) );
			if( isset( $field['description'] ) ) echo '<p class="description">' . $field['description'] . '</p>';

	echo '
		</div>
	</div>';
}

Themify_Builder_Model::register_module( 'TB_Product_Categories_Module' );