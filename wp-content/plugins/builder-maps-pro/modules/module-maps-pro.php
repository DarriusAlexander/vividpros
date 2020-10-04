<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Maps Pro
 */
class TB_Maps_Pro_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Maps Pro', 'builder-maps-pro' ),
			'slug' => 'maps-pro'
		));
	}

	/**
	 * Filter the marker texts
	 */
	public static function sanitize_text( $text ) {
		/* remove script tags */
		$text = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $text );

		return $text;
	}

	public function get_options() {
		$map_styles = array();
		foreach( Builder_Maps_Pro::get_instance()->get_map_styles() as $key => $style ) {
			$name = str_replace( '.json', '', $key );
			$map_styles[$name] = $name;
		}

		return array(
			array(
				'id' => 'mod_title',
				'type' => 'text',
				'label' => __('Module Title', 'builder-maps-pro'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'map_display_type',
				'type' => 'radio',
				'label' => __('Type', 'builder-maps-pro'),
				'options' => array(
					'dynamic' => __( 'Dynamic', 'builder-maps-pro' ),
					'static' => __( 'Static image', 'builder-maps-pro' ),
				),
				'default' => 'dynamic',
				'option_js' => true,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'w_map',
				'type' => 'text',
				'class' => 'xsmall',
				'label' => __('Width', 'builder-maps-pro'),
				'unit' => array(
					'id' => 'unit_w',
					'selected' => '%',
					'options' => array(
						array( 'id' => 'pixel_unit_w', 'value' => 'px'),
						array( 'id' => 'percent_unit_w', 'value' => '%')
					),
					'render_callback' => array(
						'binding' => 'live',
						'control_type' => 'select'
					)
				),
				'value' => 100,
				'wrap_with_class' => 'tf-group-element tf-group-element-dynamic',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'w_map_static',
				'type' => 'text',
				'class' => 'xsmall',
				'label' => __('Width', 'builder-maps-pro'),
				'value' => 500,
				'after' => 'px',
				'wrap_with_class' => 'tf-group-element tf-group-element-static',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'h_map',
				'type' => 'text',
				'label' => __('Height', 'builder-maps-pro'),
				'class' => 'xsmall',
				'unit' => array(
					'id' => 'unit_h',
					'options' => array(
						array( 'id' => 'pixel_unit_h', 'value' => 'px')
					)
				),
				'value' => 300,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'type_map',
				'type' => 'select',
				'label' => __('Type', 'builder-maps-pro'),
				'options' => array(
					'ROADMAP' => __( 'Road Map', 'builder-maps-pro' ),
					'SATELLITE' => __( 'Satellite', 'builder-maps-pro' ),
					'HYBRID' => __( 'Hybrid', 'builder-maps-pro' ),
					'TERRAIN' => __( 'Terrain', 'builder-maps-pro' )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'style_map',
				'type' => 'select',
				'label' => __('Style', 'builder-maps-pro'),
				'options' => array( '' => '' ) + $map_styles,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'scrollwheel_map',
				'type' => 'select',
				'label' => __( 'Scrollwheel', 'builder-maps-pro' ),
				'options' => array(
					'disable' => __( 'Disable', 'builder-maps-pro' ),
					'enable' => __( 'Enable', 'builder-maps-pro' ),
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-dynamic',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'draggable_map',
				'type' => 'select',
				'label' => __( 'Draggable', 'builder-maps-pro' ),
				'options' => array(
					'enable' => __( 'Enable', 'builder-maps-pro' ),
					'desktop_only' => __( 'Enable only on desktop', 'builder-maps-pro' ),
					'disable' => __( 'Disable', 'builder-maps-pro' )
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-dynamic',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'disable_map_ui',
				'type' => 'select',
				'label' => __( 'Disable Map Controls', 'builder-maps-pro' ),
				'options' => array(
					'no' => __( 'No', 'builder-maps-pro' ),
					'yes' => __( 'Yes', 'builder-maps-pro' ),
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-dynamic',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'map_link',
				'type' => 'checkbox',
				'label' => __( 'Map link', 'builder-maps-pro' ),
				'options' => array(
					array( 'name' => 'gmaps', 'value' => __('Open Google Maps', 'builder-maps-pro'))
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-static',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
					'id' => 'zoom_map',
					'type' => 'selectbasic',
					'label' => __('Zoom', 'builder-maps-pro'),
					'default' => 4,
					'options' => range( 1, 18 ),
					'render_callback' => array(
						'binding' => 'live',
						'control_type' => 'select'
					)
			),
			array(
					'id' => 'map_center',
					'type' => 'textarea',
					'value' => '',
					'class' => 'fullwidth',
					'label' => __('Base Map Address (Also accepts Lat/Lng values)', 'builder-maps-pro'),
					'render_callback' => array(
						'binding' => 'live'
					)
			),
			array(
				'type' => 'map_pro'
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_class',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-maps-pro'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'builder-maps-pro' ) ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'type_map' => 'ROADMAP',
			'scrollwheel_map' => 'enable',
			'draggable_map' => 'enable',
			'disable_map_ui' => 'no',
			'unit_w' => '%',
			'unit_h' => 'px',
			'w_map' => 100,
			'h_map' => 350,
			'zoom_map' => 4,
			'style_map' => 'pale-dawn',
			'map_center' => 'Toronto, ON, Canada',
			'map_display_type' => 'dynamic',
			'w_map_static' => 500,
			'markers' => array( array(
				'address' => '3 Bedford Road, Toronto, ON, Canada',
				'title' => 'Our Shop',
				'image' => 'https://themify.me/demo/themes/themes/wp-content/uploads/addon-samples/shop-map-marker.png'
			) )
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'builder-maps-pro' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-maps-pro'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'title' => __( 'Effect', 'builder-maps-pro' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'title' => __( 'Delay', 'builder-maps-pro' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-maps-pro' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'title' => __( 'Repeat', 'builder-maps-pro' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-maps-pro' ),
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
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-maps-pro').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-maps-pro'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module',
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-maps-pro').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-maps-pro'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'padding-top',
						'selector' => '.module',
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
						'selector' => '.module',
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
						'selector' => '.module',
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
						'selector' => '.module',
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'themify' ) )
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
				'meta' => array('html'=>'<h4>'.__('Margin', 'builder-maps-pro').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-maps-pro'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'margin-top',
						'selector' => '.module',
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
						'selector' => '.module',
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
						'selector' => '.module',
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
						'selector' => '.module',
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-maps-pro'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-maps-pro')),
							array('value' => '%', 'name' => __('%', 'builder-maps-pro'))
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
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'themify' ) )
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
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-maps-pro').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-maps-pro'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module',
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module',
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-maps-pro'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module',
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
						'selector' => '.module',
					),
					array(
						'id' => 'border_right_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-right-width',
						'selector' => '.module',
					),
					array(
						'id' => 'border_right_style',
						'type' => 'select',
						'description' => __('right', 'builder-maps-pro'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module',
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
						'selector' => '.module',
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module',
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-maps-pro'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module',
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
						'selector' => '.module',
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module',
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-maps-pro'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module',
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
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'themify' ) )
				)
			)
		);
	}

	protected function _visual_template() {
		$module_args = $this->get_module_args();?>

		<#
			var moduleSettings = {
				'zoom': data.zoom_map,
				'type': data.type_map,
				'address': data.map_center,
				'width': data.w_map,
				'height': data.h_map,
				'style_map': data.style_map,
				'scrollwheel': data.scrollwheel_map,
				'draggable': ( 'enable' == data.draggable_map || ( 'desktop_only' == data.draggable_map ) ) ? 'enable' : 'disable',
				'disable_map_ui': data.disable_map_ui
			};
		#>

		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.css_class }}" data-config="{{ JSON.stringify( moduleSettings ) }}">

			<# if( data.mod_title ) { #>
				<?php echo $module_args['before_title']; ?>
				{{{ data.mod_title }}}
				<?php echo $module_args['after_title']; ?>
			<# } #>

			<?php do_action( 'themify_builder_before_template_content_render' ); ?>
			
			<# if( data.map_display_type == 'dynamic' ) { #>

				<div class="maps-pro-canvas-container">
					<div class="maps-pro-canvas map-container" style="width: {{ data.w_map }}{{ data.unit_w }}; height: {{ data.h_map }}{{ data.unit_h }};">
					</div>
				</div>

				<div class="maps-pro-markers" style="display: none;">

					<# _.each( data.markers, function( marker ) { #>
						<div class="maps-pro-marker" data-address="{{{ marker.address }}}" data-image="{{ marker.image }}">
							<# marker.title && print( marker.title ) #>
						</div>
					<# } ) #>
				</div>

			<# } else {
				var args = '';
				args += data.map_center ? 'center=' + data.map_center : '';
				args += data.zoom_map ? '&zoom=' + data.zoom_map : '';
				args += data.type_map ? '&maptype=' + data.type_map.toLowerCase() : '';
				args += data.w_map_static ? '&size=' + data.w_map_static.replace( /[^0-9]/, '' ) + 'x' + data.h_map.replace( /[^0-9]/, '' ) : '';
				<?php if( method_exists( 'Themify_Builder', 'getMapKey' ) ) {
					echo "args += '&key=" . Themify_Builder::getMapKey() . '\';';
				} ?>

				if( data.markers ) {
					_.each( data.markers, function( marker ) {
						args += marker.image 
							? '&markers=icon:' + encodeURI( marker.image ) + '%7C' + encodeURI( marker.address )
							: '&markers=' + encodeURI( marker.address );
					} );
				}

				if( data.map_link == 'gmaps' && data.map_center ) { #>
					<a href="http://maps.google.com/?q={{ data.map_center }}" target="_blank" rel="nofollow" title="Google Maps">
				<# } #>
			
				<img src="//maps.googleapis.com/maps/api/staticmap?{{ args }}" />

				<# if( data.map_link == 'gmaps' && data.map_center ) { #>
					</a>
				<# } #>

			<# } #>

			<?php do_action( 'themify_builder_after_template_content_render' ); ?>
		</div>
	<?php
	}
}

function themify_builder_field_map_pro( $field, $module_name ) {
	echo '<div id="map-preview">';

	echo '<div id="map-canvas"></div>';

	themify_builder_module_settings_field( array(
		array(
			'id' => 'markers',
			'type' => 'builder',
			'options' => array(
				array(
					'id' => 'address',
					'type' => 'textarea',
					'label' => __('Address (or Lat/Lng)', 'builder-maps-pro'),
					'class' => '',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'markers'
					)
				),
				array(
					'id' => 'latlng',
					'type' => 'textarea',
					'label' => '',
					'class' => 'latlng',
				),
				array(
					'id' => 'title',
					'type' => 'textarea',
					'label' => __('Tooltip Text', 'builder-maps-pro'),
					'class' => '',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'markers'
					)
				),
				array(
					'id' => 'image',
					'type' => 'image',
					'label' => __('Icon', 'builder-maps-pro'),
					'class' => '',
					'render_callback' => array(
						'binding' => 'live',
						'repeater' => 'markers'
					)
				),
			),
			'render_callback' => array(
				'binding' => 'live',
				'control_type' => 'repeater'
			)
		),
	), $module_name );

	echo '<div class="themify_builder_field tf-group-element tf-group-element-static">';
		esc_html_e( 'In static mode, Google allows up to 5 custom icons, though each unique icons may be used multiple times. Icons are limited to sizes of 4096 pixels (64x64 for square images), and also the API does not support custom icon URLs that use HTTPS.', 'builder-maps-pro' );
	echo '</div>';

	echo '</div>';

}

Themify_Builder_Model::register_module( 'TB_Maps_Pro_Module' );