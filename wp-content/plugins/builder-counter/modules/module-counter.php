<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Counter
 */
class TB_Counter_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Counter', 'builder-counter'),
			'slug' => 'counter'
		));
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_counter',
				'type' => 'text',
				'label' => __('Module Title', 'builder-counter'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'multi_number_counter',
				'type' => 'multi',
				'label' => __('Number', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'number_counter',
						'type' => 'text',
						'class' => 'fullwidth',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'number_grouping',
						'type' => 'text',
						'after' => __('Thousand Separator', 'builder-counter'),
						'class' => 'xsmall',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
				)
			),
			array(
				'id' => 'label_counter',
				'type' => 'text',
				'label' => __('Label', 'builder-counter'),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'multi_circle_counter',
				'type' => 'multi',
				'label' => __('Circle', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'circle_percentage_counter',
						'type' => 'text',
						'label' => __('Percentage', 'builder-counter'),
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'circle_stroke_counter',
						'type' => 'text',
						'label' => __('Stroke', 'builder-counter'),
						'class' => 'large',
						'after' => 'px',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'circle_color_counter',
						'type' => 'text',
						'colorpicker' => true,
						'class' => 'large',
						'label' => __('Color', 'builder-counter'),
						'render_callback' => array(
							'binding' => 'live',
							'control_type' => 'color'
						)
					),
					array(
						'id' => 'circle_background_counter',
						'type' => 'text',
						'colorpicker' => true,
						'class' => 'large',
						'label' => __('Background', 'builder-counter'),
						'render_callback' => array(
							'binding' => 'live',
							'control_type' => 'color'
						)
					),
				)
			),
			array(
				'id' => 'size_counter',
				'type' => 'select',
				'label' => __('Size', 'builder-counter'),
				'options' => array(
					'large' => __('Large', 'builder-counter'),
					'medium' => __('Medium', 'builder-counter'),
					'small' => __('Small', 'builder-counter'),
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'add_css_counter',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-counter'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'builder-counter') ),
				'render_callback' => array(
					'binding' => 'live'
				)				
			)
		);
	}

	public function get_default_settings() {
		return array(
			'number_counter' => '50k',
			'label_counter' => esc_html__( 'Followers', 'builder-counter' ),
			'circle_percentage_counter' => 50,
			'circle_stroke_counter' => 2,
			'circle_color_counter' => '47cbff',
			'circle_background_counter' => 'd8eaed',
			'size_counter' => 'medium',
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'builder-counter' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'title' => __( 'Effect', 'builder-counter' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'title' => __( 'Delay', 'builder-counter' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-counter' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'title' => __( 'Repeat', 'builder-counter' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-counter' ),
					),
				)
			)
		);

		return $animation;
	}

	public function get_styling() {
		return array(
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-counter').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-counter'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-counter'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-counter').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-counter'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-counter' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-counter'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-counter' )
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'font_size_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => 'em', 'name' => __('em', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter')),
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'line_height_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => 'em', 'name' => __('em', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
						)
					)
				)
			),
			array(
				'id' => 'text_align',
				'label' => __( 'Text Align', 'builder-counter' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'builder-counter' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'builder-counter' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'builder-counter' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'builder-counter' ) ),
					array( 'value' => 'justify', 'name' => __( 'Justify', 'builder-counter' ) )
				),
				'prop' => 'text-align',
				'selector' => '.module-counter'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-counter').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-counter' ) )
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
				'meta' => array('html'=>'<h4>'.__('Margin', 'builder-counter').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-counter'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-counter')),
							array('value' => '%', 'name' => __('%', 'builder-counter'))
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
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'builder-counter' ) )
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
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-counter').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-counter'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-counter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-counter'
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_right_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-right-width',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_right_style',
						'type' => 'select',
						'description' => __('right', 'builder-counter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-counter'
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-counter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-counter'
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
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-counter'
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-counter'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-counter'
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
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-counter' ) )
				)
			),
		);
	}

	protected function _visual_template() {
		$module_args = $this->get_module_args();?>
		<# var parts = data.number_counter 
			? data.number_counter.match( /([\D]*)([\d\.]*)([\D]*)/ )
			: ['','','',''],
			counterSize = { small: 100, medium: 150, large: 200 }; #>

		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.add_css_counter }}">
			<# if( data.mod_title_counter ) { #>
				<?php echo $module_args['before_title']; ?>
				{{{ data.mod_title_counter }}}
				<?php echo $module_args['after_title']; ?>
			<# } #>

			<?php do_action( 'themify_builder_before_template_content_render' ); ?>

			<# if( data.circle_percentage_counter ) { #>
				<div class="counter-chart" data-percent="{{ data.circle_percentage_counter }}" data-color="#{{ data.circle_color_counter.substr( 0, 6 ) }}" data-trackcolor="rgba(0,0,0,.1)" data-linecap="butt" data-scalelength="0" data-rotate="0" data-size="<# data.size_counter && print( counterSize[ data.size_counter ] ) #>" data-linewidth="{{ data.circle_stroke_counter }}" data-animate="2000">
			<# }

			if( data.circle_background_counter ) { #>
				<div class="module-counter-background" style="background:#{{ data.circle_background_counter.substr( 0, 6 ) }}"></div>
			<# } #>

			<div class="number">
				<span class="bc-timer" id="{{ Date.now() }}-bc-timer" data-from="0" data-to="{{ parts[2] }}" data-suffix="{{ parts[3] }}" data-prefix="{{ parts[1] }}" data-decimals="<# print( parts[2].split('.')[1] || 0 ) #>" data-grouping="{{ data.number_grouping }}"></span>
			</div>
			
			<# if( data.circle_percentage_counter ) { #>
				</div><!-- .chart -->
			<# } #>

			<div class="counter-text">{{{ data.label_counter }}}</div>

			<?php do_action( 'themify_builder_after_template_content_render' ); ?>
		</div>
	<?php
	}
}

Themify_Builder_Model::register_module( 'TB_Counter_Module' );