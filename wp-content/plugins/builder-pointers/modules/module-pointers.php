<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Pointers
 */
class TB_Pointers_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Pointers', 'builder-pointers'),
			'slug' => 'pointers'
		));
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title',
				'type' => 'text',
				'label' => __('Module Title', 'builder-pointers'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'image_url',
				'type' => 'image',
				'label' => __('Image URL', 'builder-pointers'),
				'class' => 'xlarge',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'title_image',
				'type' => 'text',
				'label' => __('Image Alt', 'builder-pointers'),
				'class' => 'fullwidth',
				'after' => '<small>' . __( 'Optional: Image alt is the image "alt" attribute. Primarily used for SEO describing the image.', 'builder-pointers' ) . '</small>',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'image_dimension',
				'type' => 'multi',
				'label' => __( 'Image Dimension', 'builder-pointers' ),
				'fields' => array(
					array(
						'id' => 'image_width',
						'type' => 'text',
						'label' => __('Width', 'builder-pointers'),
						'class' => 'medium',
						'help' => 'px',
						'value' => 300,
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'image_height',
						'type' => 'text',
						'label' => __('Height', 'builder-pointers'),
						'class' => 'medium',
						'help' => 'px',
						'value' => 200,
						'render_callback' => array(
							'binding' => 'live'
						)
					),
				)
			),
			array(
				'type' => 'pointers'
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_class',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-pointers'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'builder-pointers') ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'image_url' => 'https://themify.me/demo/themes/themes/wp-content/uploads/addon-samples/pointers-ipad-image.jpg',
			'image_width' => 400,
			'image_height' => 330
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'builder-pointers' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-pointers'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'title' => __( 'Effect', 'builder-pointers' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'title' => __( 'Delay', 'builder-pointers' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-pointers' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'title' => __( 'Repeat', 'builder-pointers' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-pointers' ),
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
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-pointers').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-pointers'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-pointers'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-pointers').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-pointers'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-pointers' ) )
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
				'meta' => array('html'=>'<h4>'.__('Margin', 'builder-pointers').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-pointers'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-pointers'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-pointers')),
							array('value' => '%', 'name' => __('%', 'builder-pointers'))
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
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'builder-pointers' ) )
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
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-pointers').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-pointers'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-pointers'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-pointers'
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
						'selector' => '.module-pointers'
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
						'description' => __('right', 'builder-pointers'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-pointers'
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-pointers'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-pointers'
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
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-pointers'
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-pointers'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-pointers'
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
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-pointers' ) )
				)
			),
		);
	}

	protected function _visual_template() {
		$module_args = $this->get_module_args();?>
		<#
		var blob_default = {
			title: '', 
			direction: 'bottom',
			pointer_color: '', 
			tooltip_background: '', 
			tooltip_color: '',
			left: '',
			top: '',
			link: '',
			auto_visible: 'no',
			pointer_hide: 'no'
		};
		#>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.css_class }}">
			<# if( data.mod_title ) { #>
				<?php echo $module_args['before_title']; ?>
				{{{ data.mod_title }}}
				<?php echo $module_args['after_title']; ?>
			<# } #>

			<?php do_action( 'themify_builder_before_template_content_render' ); ?>
			
			<div class="showcase-image">
				<img src="{{ data.image_url }}" width="{{ data.image_width }}" height="{{ data.image_height }}" alt="{{ data.title_image }}" />
				
				<# _.each( data.blobs_showcase, function( blob, index ) { 
					_.defaults( blob, blob_default );
					if ( _.isUndefined( blob['left'] ) || '' === blob['left'] ) return;
					var pointerHide = blob.pointer_hide && blob.pointer_hide == 'yes' ? 'tooltipster-fade' : '';
					var pointerColor = blob.pointer_color ? 'background-color: ' + themifybuilderapp.Utils.toRGBA( blob.pointer_color ) + ';' : '';
					var direction = blob.direction ? blob.direction : 'top';
					var style = blob.tooltip_background ? 'background-color: ' + themifybuilderapp.Utils.toRGBA( blob.tooltip_background ) + ';' : '';
					style += blob.tooltip_color ? 'color: ' + themifybuilderapp.Utils.toRGBA( blob.tooltip_color ) + ';' : '';
					
					if( style ) {
						print( '<style>body .tooltip-' + data.cid + '-' + index + ' { ' + style + ' }</style>' );
					} #>
					<div class="blob blob-{{ index }} {{ pointerHide }}" style="top: {{ blob.top }}%; left: {{ blob.left }}%;" data-direction="{{ direction }}" data-theme="tooltipster-default tooltip-{{ data.cid }}-{{ index }}" data-visible="{{ blob.auto_visible }}" aria-describedby="blob-tooltip-{{ data.cid }}-{{ index }}">

						<# if( blob.title ) { #>
							<span class="blob-tooltip" id="blob-tooltip-{{ data.cid }}-{{ index }}" style="display: none; visibility: hidden;" role="tooltip">{{{ blob.title }}}</span>
						<# }

						if( blob.link ) { #>
							<a href="{{ blob.link }}" >
						<# } #>

							<div class="blob-icon" style="{{ pointerColor }}">
								<span style="{{ pointerColor }}"></span>
							</div>

						<# if( blob.link ) { #>
							</a>
						<# } #>

					</div>

				<# } ); #>
			</div>

			<?php do_action( 'themify_builder_after_template_content_render' ); ?>
		</div>
	<?php
	}
}

function themify_builder_field_pointers( $field, $module_name ) {
	echo '<div id="pointers">';
	echo '<p class="description">'. __( 'Click on the image to add tooltips (click tooltip points to edit again).', 'builder-pointers' ) .'</p>';

	echo '<div id="pointers-preview"><div class="loading"><i class="fa fa-gear fa-spin"></i></div></div>';

	themify_builder_module_settings_field( array(
		array(
			'id' => 'blobs_showcase',
			'type' => 'builder',
			'options' => array(
				array(
					'id' => 'title',
					'type' => 'textarea',
					'label' => __('Text', 'builder-pointers'),
					'class' => 'large',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase'
					)
				),
				array(
					'id' => 'direction',
					'type' => 'select',
					'label' => __('Tooltip Direction', 'builder-pointers'),
					'options' => array(
						'bottom' => __('Bottom', 'builder-pointers'),
						'top' => __('Top', 'builder-pointers'),
						'left' => __('Left', 'builder-pointers'),
						'right' => __('Right', 'builder-pointers'),
					),
					'default' => 'bottom',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase'
					)
				),
                array(
					'id' => 'pointer_hide',
					'type' => 'select',
					'label' => __('Hide Pointer', 'builder-pointers'),
					'options' => array(
						'no' => __('No', 'builder-pointers'),
						'yes' => __('Yes', 'builder-pointers'),
					),
					'default' => 'no',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase'
					)
				),
				array(
					'id' => 'auto_visible',
					'type' => 'select',
					'label' => __('Always Visible', 'builder-pointers'),
					'options' => array(
						'no' => __('No', 'builder-pointers'),
						'yes' => __('Yes', 'builder-pointers'),
					),
					'default' => 'no',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase'
					)
				),
				array(
					'id' => 'pointer_color',
					'type' => 'text',
					'colorpicker' => true,
					'class' => 'large',
					'label' => __('Pointer Color', 'builder-pointers'),
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase',
						'control_type' => 'color'
					)
				),
				array(
					'id' => 'tooltip_background',
					'type' => 'text',
					'colorpicker' => true,
					'class' => 'large',
					'label' => __('Tooltip Background', 'builder-pointers'),
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase',
						'control_type' => 'color'
					)
				),
				array(
					'id' => 'tooltip_color',
					'type' => 'text',
					'colorpicker' => true,
					'class' => 'large',
					'label' => __('Tooltip Text Color', 'builder-pointers'),
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase',
						'control_type' => 'color'
					)
				),
                                array(
					'id' => 'open',
					'type' => 'select',
					'label' => __('Open link in', 'builder-pointers'),
					'options' => array(
						'blank' => __('New Window', 'builder-pointers'),
						'' => __('Same Window', 'builder-pointers'),
						'lightbox' => __('Lightbox', 'builder-pointers'),
					),
					'default' => 'blank',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase'
					)
				),
				array(
					'id' => 'link',
					'type' => 'text',
					'label' => __('Link To', 'builder-pointers'),
					'class' => 'large',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase'
					)
				),
				array(
					'id' => 'left',
					'type' => 'text',
					'label' => __('Left', 'builder-pointers'),
					'class' => 'large',
					'wrap_with_class' => 'hide-if-js',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase',
						'control_type' => 'textonchange'
					)
				),
				array(
					'id' => 'top',
					'type' => 'text',
					'label' => __('Top', 'builder-pointers'),
					'class' => 'large',
					'wrap_with_class' => 'hide-if-js',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'blobs_showcase',
						'control_type' => 'textonchange'
					)
				),
			),
			'render_callback' => array(
				'binding' => 'live',
				'control_type' => 'repeater'
			)
		),
	), $module_name );
	echo '</div>';
}

Themify_Builder_Model::register_module( 'TB_Pointers_Module' );