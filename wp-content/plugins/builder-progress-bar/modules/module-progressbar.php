<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Progress Bar
 */
class TB_ProgressBar_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Progress Bar', 'builder-progressbar'),
			'slug' => 'progressbar'
		));
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_progressbar',
				'type' => 'text',
				'label' => __('Module Title', 'builder-progressbar'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'progress_bars',
				'type' => 'builder',
				'options' => array(
					array(
						'id' => 'bar_label',
						'type' => 'text',
						'label' => __('Label', 'builder-progressbar'),
						'class' => 'large',
						'render_callback' => array(
							'binding' => 'live',
							'repeater' => 'progress_bars'
						)
					),
					array(
						'id' => 'bar_percentage',
						'type' => 'text',
						'label' => __('Percentage', 'builder-progressbar'),
						'after' => '%',
						'class' => 'small',
						'render_callback' => array(
							'binding' => 'live',
							'repeater' => 'progress_bars'
						)
					),
					array(
						'id' => 'bar_color',
						'type' => 'text',
						'colorpicker' => true,
						'label' => __('Color', 'builder-progressbar'),
						'class' => 'small',
						'render_callback' => array(
							'binding' => 'live',
							'control_type' => 'color',
							'repeater' => 'progress_bars'
						)
					),
				),
				'render_callback' => array(
					'binding' => 'live',
					'control_type' => 'repetear'
				)
			),
			array(
				'id' => 'hide_percentage_text',
				'type' => 'select',
				'label' => __('Hide Percentage Text', 'builder-progressbar'),
				'options' => array(
					'no' => __('No', 'builder-progressbar'),
					'yes' => __('Yes', 'builder-progressbar'),
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
				'id' => 'add_css_progressbar',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-progressbar'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'builder-progressbar') ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'hide_percentage_text' => 'no',
			'progress_bars' => array( array(
				'bar_label' => esc_html__( 'Label', 'builder-progressbar' ),
				'bar_percentage' => 80,
				'bar_color' => '4a54e6_1'
			) )
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'builder-progressbar' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-progressbar'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'title' => __( 'Effect', 'builder-progressbar' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'title' => __( 'Delay', 'builder-progressbar' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-progressbar' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'title' => __( 'Repeat', 'builder-progressbar' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-progressbar' ),
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
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-progressbar').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-progressbar'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-progressbar'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-progressbar').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-progressbar'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-progressbar' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-progressbar'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-progressbar' )
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-progressbar'),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'font_size_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => 'em', 'name' => __('em', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar')),
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-progressbar'),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'line_height_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => 'em', 'name' => __('em', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
						)
					)
				)
			),
			array(
				'id' => 'text_align',
				'label' => __( 'Text Align', 'builder-progressbar' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'builder-progressbar' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'builder-progressbar' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'builder-progressbar' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'builder-progressbar' ) ),
					array( 'value' => 'justify', 'name' => __( 'Justify', 'builder-progressbar' ) )
				),
				'prop' => 'text-align',
				'selector' => '.module-progressbar'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-progressbar').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-progressbar'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-progressbar' ) )
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
				'meta' => array('html'=>'<h4>'.__('Margin', 'builder-progressbar').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-progressbar'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-progressbar'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-progressbar')),
							array('value' => '%', 'name' => __('%', 'builder-progressbar'))
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
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'builder-progressbar' ) )
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
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-progressbar').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-progressbar'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-progressbar'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-progressbar'
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
						'selector' => '.module-progressbar'
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
						'description' => __('right', 'builder-progressbar'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-progressbar'
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-progressbar'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-progressbar'
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
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-progressbar'
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-progressbar'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-progressbar'
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
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-progressbar' ) )
				)
			),
		);
	}

	protected function _visual_template() {
		$module_args = $this->get_module_args();?>

		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.add_css_progressbar }}">
			<# if( data.mod_title_progressbar ) { #>
				<?php echo $module_args['before_title']; ?>
				{{{ data.mod_title_progressbar }}}
				<?php echo $module_args['after_title']; ?>
			<# } #>

			<?php do_action( 'themify_builder_before_template_content_render' ); ?>
			
			<div class="tf-progress-bar-wrap">
				<# _.each( data.progress_bars, function( bar, index ) { #>
					<div class="tf-progress-bar">

						<i class="tf-progress-bar-label">{{{ bar.bar_label }}}</i>
						<span class="tf-progress-bar-bg" data-percent="{{ bar.bar_percentage }}" style="width: 0; background-color: #<# bar.bar_color && print( bar.bar_color.substr( 0, 6 ) ) #>">

							<# if( data.hide_percentage_text == 'no' ) { #>
								<span class="tf-progress-tooltip" id="{{ index }}-progress-tooltip" data-to="{{ bar.bar_percentage }}" data-suffix="%" data-decimals="0"></span>
							<# } #>

						</span>

					</div><!-- .tf-progress-bar -->
				<# } ); #>
			</div><!-- .tf-progress-bar-wrap -->

			<?php do_action( 'themify_builder_after_template_content_render' ); ?>
		</div>
	<?php
	}
}

Themify_Builder_Model::register_module( 'TB_ProgressBar_Module' );