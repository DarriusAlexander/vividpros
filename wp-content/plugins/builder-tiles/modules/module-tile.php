<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Tile
 */
class TB_Tile_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Tile', 'builder-tiles'),
			'slug' => 'tile'
		));
	}

	public function get_options() {
		$tile_size_options = array();
		foreach( Builder_Tiles::get_instance()->get_tile_sizes() as $key => $size ) {
			$tile_size_options[] = array( 'img' => $size['image'], 'value' => $key, 'label' => $size['label'] );
		}

		return array(
			array(
				'id' => 'size',
				'type' => 'layout',
				'label' => __('Size', 'builder-tiles'),
				'options' => $tile_size_options,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'type' => 'tabs',
				'id' => 'tile',
				'tabs' => array(
					'front' => array(
						'label' => __( 'Front', 'builder-tiles' ),
						'fields' => array(
							array(
								'id' => 'type_front',
								'type' => 'layout',
								'label' => __('Type', 'builder-tiles'),
								'options' => array(
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-text.png', 'value' => 'text', 'label' => __('Text', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-button.png', 'value' => 'button', 'label' => __('Button', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-gallery.png', 'value' => 'gallery', 'label' => __('Gallery', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-map.png', 'value' => 'map', 'label' => __('Map', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-blank.png', 'value' => 'blank', 'label' => __('Blank', 'builder-tiles')),
								),
								'option_js' => true,
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'color_front',
								'type' => 'layout',
								'label' => __('Tile Color', 'builder-tiles'),
								'options' => apply_filters( 'builder_tile_colors', array(
									array('img' => 'color-default.png', 'value' => 'default', 'label' => __('default', 'builder-tiles')),
									array('img' => 'color-black.png', 'value' => 'black', 'label' => __('black', 'builder-tiles')),
									array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('gray', 'builder-tiles')),
									array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('blue', 'builder-tiles')),
									array('img' => 'color-light-blue.png', 'value' => 'light-blue', 'label' => __('light-blue', 'builder-tiles')),
									array('img' => 'color-green.png', 'value' => 'green', 'label' => __('green', 'builder-tiles')),
									array('img' => 'color-light-green.png', 'value' => 'light-green', 'label' => __('light-green', 'builder-tiles')),
									array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('purple', 'builder-tiles')),
									array('img' => 'color-light-purple.png', 'value' => 'light-purple', 'label' => __('light-purple', 'builder-tiles')),
									array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('brown', 'builder-tiles')),
									array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('orange', 'builder-tiles')),
									array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('yellow', 'builder-tiles')),
									array('img' => 'color-red.png', 'value' => 'red', 'label' => __('red', 'builder-tiles')),
									array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('pink', 'builder-tiles'))
								) ),
								'bottom' => true,
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'title_front',
								'type' => 'text',
								'label' => __('Title', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							/* Text Tile options */
							array(
								'id' => 'text_front',
								'type' => 'wp_editor',
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text',
								'render_callback' => array(
									'binding' => 'live'
								)
							),

							/* Button Tile options */
							array(
								'id' => 'button_link_front',
								'type' => 'text',
								'label' => __('Title Link', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'button_link_params_front',
								'type' => 'checkbox',
								'label' => false,
								'pushed' => 'pushed',
								'options' => array(
									array( 'name' => 'lightbox', 'value' => __('Open link in lightbox', 'builder-tiles')),
									array( 'name' => 'newtab', 'value' => __('Open link in new tab', 'builder-tiles'))
								),
								'new_line' => false,
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'button_icon_front',
								'type' => 'group',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
								'fields' => array(
									array(
										'id' => 'icon_type_front',
										'type' => 'radio',
										'label' => __('Icon Type', 'builder-tiles'),
										'options' => array(
											'icon' => __('Icon', 'builder-tiles'),
											'image' => __('Image', 'builder-tiles'),
										),
										'default' => 'icon',
										'option_js' => true,
										'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'icon_front',
										'type' => 'icon',
										'label' => __('Icon', 'builder-tiles'),
										'class' => 'small',
										'wrap_with_class' => 'tf-group-element tf-group-element-icon tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'image_front',
										'type' => 'image',
										'label' => __('Image URL', 'builder-tiles'),
										'class' => 'xlarge',
										'wrap_with_class' => 'tf-group-element tf-group-element-image tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'icon_color_front',
										'type' => 'text',
										'colorpicker' => true,
										'label' => __('Icon Color', 'builder-tiles'),
										'class' => 'small',
										'wrap_with_class' => 'tf-group-element tf-group-element-icon tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
								)
							),

							/* Gallery Tile options */
							array(
								'id' => 'gallery_front',
								'type' => 'textarea',
								'class' => 'fullwidth tf-shortcode-input',
								'label' => __('Gallery Slider', 'builder-tiles'),
								'help' => sprintf('<a href="#" class="builder_button tf-gallery-btn">%s</a>', __('Insert Gallery', 'builder-tiles')),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live',
									'control_type' => 'textonchange'
								)
							),
							array(
								'id' => 'gallery_autoplay_front',
								'type' => 'select',
								'label' => __('Auto play', 'builder-tiles'),
								'options' => array(
									'off' => __( 'Off', 'builder-tiles' ),
									'1000' => __( '1 Second', 'builder-tiles' ),
									'2000' => __( '2 Seconds', 'builder-tiles' ),
									'3000' => __( '3 Seconds', 'builder-tiles' ),
									'4000' => __( '4 Seconds', 'builder-tiles' ),
									'5000' => __( '5 Seconds', 'builder-tiles' ),
									'6000' => __( '6 Seconds', 'builder-tiles' ),
									'7000' => __( '7 Seconds', 'builder-tiles' ),
									'8000' => __( '8 Seconds', 'builder-tiles' ),
									'9000' => __( '9 Seconds', 'builder-tiles' ),
									'10000' => __( '10 Seconds', 'builder-tiles' ),
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'gallery_hide_timer_front',
								'type' => 'select',
								'label' => __('Hide Timer', 'builder-tiles'),
								'options' => array(
									'no' => __( 'No', 'builder-tiles' ),
									'yes' => __( 'Yes', 'builder-tiles' ),
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),

							/* Map Tile options */
							array(
								'id' => 'address_map_front',
								'type' => 'textarea',
								'value' => '',
								'class' => 'fullwidth',
								'label' => __('Address', 'builder-tiles'),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'type_map_front',
								'type' => 'select',
								'label' => __('Type', 'builder-tiles'),
								'options' => array(
									'ROADMAP' => 'Road Map',
									'SATELLITE' => 'Satellite',
									'HYBRID' => 'Hybrid',
									'TERRAIN' => 'Terrain'
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'zoom_map_front',
								'type' => 'select',
								'label' => __('Zoom', 'builder-tiles'),
								'options' => range( 1, 18 ),
								'default' => 8,
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'scrollwheel_map_front',
								'type' => 'select',
								'label' => __( 'Scrollwheel', 'builder-tiles' ),
								'options' => array(
									'enable' => __( 'Enable', 'builder-tiles' ),
									'disable' => __( 'Disable', 'builder-tiles' )
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'draggable_map_front',
								'type' => 'select',
								'label' => __( 'Draggable', 'builder-tiles' ),
								'options' => array(
									'enable' => __( 'Enable', 'builder-tiles' ),
									'disable' => __( 'Disable', 'builder-tiles' )
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),

							array(
								'id' => 'action_text_front',
								'type' => 'text',
								'label' => __('Action Button Text', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'action_link_front',
								'type' => 'text',
								'label' => __('Action Button Link', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'action_param_front',
								'type' => 'checkbox',
								'label' => false,
								'pushed' => 'pushed',
								'options' => array(
									array( 'name' => 'lightbox', 'value' => __('Open link in lightbox', 'builder-tiles')),
									array( 'name' => 'newtab', 'value' => __('Open link in new tab', 'builder-tiles'))
								),
								'new_line' => false,
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'tile_front_style',
								'label' => __( 'Custom Style', 'builder-tiles' ),
								'type' => 'multi',
								'fields' => array(
									array(
										'id' => 'background_color_front',
										'type' => 'text',
										'colorpicker' => true,
										'class' => 'large',
										'label' => __('Background Color', 'builder-tiles'),
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'text_color_front',
										'type' => 'text',
										'colorpicker' => true,
										'class' => 'large',
										'label' => __('Text Color', 'builder-tiles'),
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'link_color_front',
										'type' => 'text',
										'colorpicker' => true,
										'class' => 'large',
										'label' => __('Link Color', 'builder-tiles'),
										'render_callback' => array(
											'binding' => 'live'
										)
									),
								),
								'separated' => 'top',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text',
							),
							array(
								'id' => 'background_image_front',
								'type' => 'image',
								'label' => __('Background Image URL', 'builder-tiles'),
								'class' => 'xlarge',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
						)
					),
					'back' => array(
						'label' => __( 'Back', 'builder-tiles' ),
						'fields' => array(
							array(
								'id' => 'type_back',
								'type' => 'layout',
								'label' => __('Type', 'builder-tiles'),
								'options' => array(
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-text.png', 'value' => 'text', 'label' => __('Text', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-button.png', 'value' => 'button', 'label' => __('Button', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-gallery.png', 'value' => 'gallery', 'label' => __('Gallery', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-map.png', 'value' => 'map', 'label' => __('Map', 'builder-tiles')),
									array('img' => Builder_Tiles::get_instance()->url . 'assets/tile-type-blank.png', 'value' => 'blank', 'label' => __('Blank', 'builder-tiles')),
								),
								'option_js' => true,
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'color_back',
								'type' => 'layout',
								'label' => __('Tile Color', 'builder-tiles'),
								'options' => apply_filters( 'builder_tile_colors', array(
									array('img' => 'color-default.png', 'value' => 'default', 'label' => __('default', 'builder-tiles')),
									array('img' => 'color-black.png', 'value' => 'black', 'label' => __('black', 'builder-tiles')),
									array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('gray', 'builder-tiles')),
									array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('blue', 'builder-tiles')),
									array('img' => 'color-light-blue.png', 'value' => 'light-blue', 'label' => __('light-blue', 'builder-tiles')),
									array('img' => 'color-green.png', 'value' => 'green', 'label' => __('green', 'builder-tiles')),
									array('img' => 'color-light-green.png', 'value' => 'light-green', 'label' => __('light-green', 'builder-tiles')),
									array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('purple', 'builder-tiles')),
									array('img' => 'color-light-purple.png', 'value' => 'light-purple', 'label' => __('light-purple', 'builder-tiles')),
									array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('brown', 'builder-tiles')),
									array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('orange', 'builder-tiles')),
									array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('yellow', 'builder-tiles')),
									array('img' => 'color-red.png', 'value' => 'red', 'label' => __('red', 'builder-tiles')),
									array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('pink', 'builder-tiles'))
								) ),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text tf-tile-options-gallery',
								'bottom' => true,
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'title_back',
								'type' => 'text',
								'label' => __('Title', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							/* Text Tile options */
							array(
								'id' => 'text_back',
								'type' => 'wp_editor',
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text',
								'render_callback' => array(
									'binding' => 'live'
								)
							),

							/* Button Tile options */
							array(
								'id' => 'button_link_back',
								'type' => 'text',
								'label' => __('Title Link', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'button_link_params_back',
								'type' => 'checkbox',
								'label' => false,
								'pushed' => 'pushed',
								'options' => array(
									array( 'name' => 'lightbox', 'value' => __('Open link in lightbox', 'builder-tiles')),
									array( 'name' => 'newtab', 'value' => __('Open link in new tab', 'builder-tiles'))
								),
								'new_line' => false,
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'button_icon_back',
								'type' => 'group',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
								'fields' => array(
									array(
										'id' => 'icon_type_back',
										'type' => 'radio',
										'label' => __('Icon Type', 'builder-tiles'),
										'options' => array(
											'icon' => __('Icon', 'builder-tiles'),
											'image' => __('Image', 'builder-tiles'),
										),
										'default' => 'icon',
										'option_js' => true,
										'wrap_with_class' => 'tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'icon_back',
										'type' => 'icon',
										'label' => __('Icon', 'builder-tiles'),
										'class' => 'small',
										'wrap_with_class' => 'tf-group-element tf-group-element-icon tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'image_back',
										'type' => 'image',
										'label' => __('Image URL', 'builder-tiles'),
										'class' => 'xlarge',
										'wrap_with_class' => 'tf-group-element tf-group-element-image tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'icon_color_back',
										'type' => 'text',
										'colorpicker' => true,
										'label' => __('Icon Color', 'builder-tiles'),
										'class' => 'small',
										'wrap_with_class' => 'tf-group-element tf-group-element-icon tf-tile-options tf-tile-options-button',
										'render_callback' => array(
											'binding' => 'live'
										)
									),
								)
							),

							/* Gallery Tile options */
							array(
								'id' => 'gallery_back',
								'type' => 'textarea',
								'class' => 'fullwidth tf-shortcode-input',
								'label' => __('Gallery Slider', 'builder-tiles'),
								'help' => sprintf('<a href="#" class="builder_button tf-gallery-btn">%s</a>', __('Insert Gallery', 'builder-tiles')),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'gallery_autoplay_back',
								'type' => 'select',
								'label' => __('Auto play', 'builder-tiles'),
								'options' => array(
									'off' => __( 'Off', 'builder-tiles' ),
									'1000' => __( '1 Second', 'builder-tiles' ),
									'2000' => __( '2 Seconds', 'builder-tiles' ),
									'3000' => __( '3 Seconds', 'builder-tiles' ),
									'4000' => __( '4 Seconds', 'builder-tiles' ),
									'5000' => __( '5 Seconds', 'builder-tiles' ),
									'6000' => __( '6 Seconds', 'builder-tiles' ),
									'7000' => __( '7 Seconds', 'builder-tiles' ),
									'8000' => __( '8 Seconds', 'builder-tiles' ),
									'9000' => __( '9 Seconds', 'builder-tiles' ),
									'10000' => __( '10 Seconds', 'builder-tiles' ),
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'gallery_hide_timer_back',
								'type' => 'select',
								'label' => __('Hide Timer', 'builder-tiles'),
								'options' => array(
									'no' => __( 'No', 'builder-tiles' ),
									'yes' => __( 'Yes', 'builder-tiles' ),
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),

							/* Map Tile options */
							array(
								'id' => 'address_map_back',
								'type' => 'textarea',
								'value' => '',
								'class' => 'fullwidth',
								'label' => __('Address', 'builder-tiles'),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'type_map_back',
								'type' => 'select',
								'label' => __('Type', 'builder-tiles'),
								'options' => array(
									'ROADMAP' => 'Road Map',
									'SATELLITE' => 'Satellite',
									'HYBRID' => 'Hybrid',
									'TERRAIN' => 'Terrain'
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'zoom_map_back',
								'type' => 'select',
								'label' => __('Zoom', 'builder-tiles'),
								'options' => range( 1, 18 ),
								'default' => 8,
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'scrollwheel_map_back',
								'type' => 'select',
								'label' => __( 'Scrollwheel', 'builder-tiles' ),
								'options' => array(
									'enable' => __( 'Enable', 'builder-tiles' ),
									'disable' => __( 'Disable', 'builder-tiles' )
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'draggable_map_back',
								'type' => 'select',
								'label' => __( 'Draggable', 'builder-tiles' ),
								'options' => array(
									'enable' => __( 'Enable', 'builder-tiles' ),
									'disable' => __( 'Disable', 'builder-tiles' )
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),

							array(
								'id' => 'action_text_back',
								'type' => 'text',
								'label' => __('Action Button Text', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'action_link_back',
								'type' => 'text',
								'label' => __('Action Button Link', 'builder-tiles'),
								'class' => 'fullwidth',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'action_param_back',
								'type' => 'checkbox',
								'label' => false,
								'pushed' => 'pushed',
								'options' => array(
									array( 'name' => 'lightbox', 'value' => __('Open link in lightbox', 'builder-tiles')),
									array( 'name' => 'newtab', 'value' => __('Open link in new tab', 'builder-tiles'))
								),
								'new_line' => false,
								'wrap_with_class' => 'tf-tile-options tf-tile-options-text tf-tile-options-gallery',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'tile_autoflip',
								'type' => 'select',
								'label' => __( 'Auto Flip', 'builder-tiles' ),
								'options' => array(
									'0' => __( 'Disabled', 'builder-tiles' ),
									'1' => __( '1 Second', 'builder-tiles' ),
									'2' => __( '2 Second', 'builder-tiles' ),
									'3' => __( '3 Second', 'builder-tiles' ),
									'4' => __( '4 Second', 'builder-tiles' ),
									'5' => __( '5 Second', 'builder-tiles' ),
									'6' => __( '6 Second', 'builder-tiles' ),
									'7' => __( '7 Second', 'builder-tiles' ),
									'8' => __( '8 Second', 'builder-tiles' ),
									'9' => __( '9 Second', 'builder-tiles' ),
									'10' => __( '10 Second', 'builder-tiles' ),
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text tf-tile-options-gallery tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'flip_effect',
								'type' => 'select',
								'label' => __( 'Flip Effect', 'builder-tiles' ),
								'options' => array(
									'flip-horizontal' => __( 'Horizontal Flip', 'builder-tiles' ),
									'flip-vertical' => __( 'Vertical Flip', 'builder-tiles' ),
									'fadeIn' => __( 'Fade In', 'builder-tiles' ),
									'fadeInUp' => __( 'fadeInUp', 'builder-tiles' ),
									'fadeInLeft' => __( 'fadeInLeft', 'builder-tiles' ),
									'fadeInRight' => __( 'fadeInRight', 'builder-tiles' ),
									'fadeInDown' => __( 'fadeInDown', 'builder-tiles' ),
									'zoomInUp' => __( 'zoomInUp', 'builder-tiles' ),
									'zoomInLeft' => __( 'zoomInLeft', 'builder-tiles' ),
									'zoomInRight' => __( 'zoomInRight', 'builder-tiles' ),
									'zoomInDown' => __( 'zoomInDown', 'builder-tiles' ),
								),
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text tf-tile-options-gallery tf-tile-options-map',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
							array(
								'id' => 'tile_back_style',
								'label' => __( 'Custom Style', 'builder-tiles' ),
								'type' => 'multi',
								'fields' => array(
									array(
										'id' => 'background_color_back',
										'type' => 'text',
										'colorpicker' => true,
										'class' => 'large',
										'label' => __('Background Color', 'builder-tiles'),
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'text_color_back',
										'type' => 'text',
										'colorpicker' => true,
										'class' => 'large',
										'label' => __('Text Color', 'builder-tiles'),
										'render_callback' => array(
											'binding' => 'live'
										)
									),
									array(
										'id' => 'link_color_back',
										'type' => 'text',
										'colorpicker' => true,
										'class' => 'large',
										'label' => __('Link Color', 'builder-tiles'),
										'render_callback' => array(
											'binding' => 'live'
										)
									),
								),
								'separated' => 'top',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text',
							),
							array(
								'id' => 'background_image_back',
								'type' => 'image',
								'label' => __('Background Image URL', 'builder-tiles'),
								'class' => 'xlarge',
								'wrap_with_class' => 'tf-tile-options tf-tile-options-button tf-tile-options-text',
								'render_callback' => array(
									'binding' => 'live'
								)
							),
						)
					)
				)
			)
                        ,
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_class',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-tiles'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'builder-tiles') ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'size' => 'square-large',
			'title_front' => esc_html__( 'Tile Title', 'builder-tiles' ),
			'text_front' => esc_html__( 'Tile content', 'builder-tiles' ),
			'type_front' => 'text'
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'builder-tiles' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-tiles'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'label' => __( 'Effect', 'builder-tiles' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'label' => __( 'Delay', 'builder-tiles' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-tiles' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'label' => __( 'Repeat', 'builder-tiles' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-tiles' ),
					),
				)
			)
		);

		return $animation;
	}

	public function get_styling() {
		return array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-tiles').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-tiles'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-tile'
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-tiles'),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-tile'
					),
					array(
						'id' => 'font_size_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-tiles')),
							array('value' => 'em', 'name' => __('em', 'builder-tiles')),
							array('value' => '%', 'name' => __('%', 'builder-tiles')),
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-tiles'),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-tile'
					),
					array(
						'id' => 'line_height_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => '', 'name' => ''),
							array('value' => 'px', 'name' => __('px', 'builder-tiles')),
							array('value' => 'em', 'name' => __('em', 'builder-tiles')),
							array('value' => '%', 'name' => __('%', 'builder-tiles'))
						)
					)
				)
			),
			array(
				'id' => 'text_align',
				'label' => __( 'Text Align', 'builder-tiles' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'builder-tiles' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'builder-tiles' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'builder-tiles' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'builder-tiles' ) ),
				),
				'prop' => 'text-align',
				'selector' => '.module-tile'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-tiles').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-tiles'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-tile .tile-flip-box-wrap'
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-tiles'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-tiles')),
							array('value' => '%', 'name' => __('%', 'builder-tiles'))
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
						'selector' => '.module-tile .tile-flip-box-wrap'
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-tiles'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-tiles')),
							array('value' => '%', 'name' => __('%', 'builder-tiles'))
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
						'selector' => '.module-tile .tile-flip-box-wrap'
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-tiles'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-tiles')),
							array('value' => '%', 'name' => __('%', 'builder-tiles'))
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
						'selector' => '.module-tile .tile-flip-box-wrap'
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-tiles'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-tiles')),
							array('value' => '%', 'name' => __('%', 'builder-tiles'))
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
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-tiles' ) )
				)
			),
		);
	}

	protected function _visual_template() {
		$module_args = $this->get_module_args();?>

		<#
			_.defaults( data, {
				title_front: '', title_back: '',
				type_front: 'text', type_back: 'text',
				text_front: '', text_back: '',
				flip_effect: 'flip-left',
				address_map_front: '', address_map_back: '',
				type_map_front: 'ROADMAP', type_map_back: 'ROADMAP',
				zoom_map_front: 8, zoom_map_back: 8,
				scrollwheel_map_front: 'enable', scrollwheel_map_back: 'enable',
				draggable_map_front: 'enable', draggable_map_back: 'enable',
				background_image_front: '', background_image_back: '',
				icon_front: '', icon_back: '',
				image_front: '', image_back: ''
			});

			var outEffect = {
				'flip-horizontal': '',
				'flip-vertical': '',
				'fadeInUp': 'fadeOutDown',
				'fadeIn': 'fadeOut',
				'fadeInLeft': 'fadeOutLeft',
				'fadeInRight': 'fadeOutRight',
				'fadeInDown': 'fadeOutUp',
				'zoomInUp': 'zoomOutDown',
				'zoomInLeft': 'zoomOutLeft',
				'zoomInRight': 'zoomOutRight',
				'zoomInDown': 'zoomOutUp'
			};

			var module_classes = 'has-flip';
			if( ( data.type_back == 'blank' )
				|| ( data.type_back == 'text' && data.text_back == '' && data.background_image_back == '' && data.title_back == '' )
				|| ( data.type_back == 'button' && data.title_back == '' && data.image_back == '' && data.icon_back == '' )
				|| ( data.type_back == 'gallery' && data.gallery_back == '' )
				|| ( data.type_back == 'map' && data.address_map_back == '' )
			) {
				module_classes = 'no-flip';
			}
		#>

		<div id="module-tile-preview-{{ data.cid }}" class="module module-<?php echo esc_attr( $this->slug ); ?> size-{{ data.size }} {{ data.css_class }} {{ module_classes }}" data-auto-flip="{{ data.tile_autoflip }}" data-in-effect="{{ data.flip_effect }}" data-out-effect="{{ outEffect[ data.flip_effect ] }}">

			<?php do_action( 'themify_builder_before_template_content_render' ); ?>

			<div class="tile-flip-box-wrap"><div class="tile-flip-box">
				<# _.each( ['front', 'back'], function( side ) { 

					if( data[ 'type_' + side ] == 'blank' ) return;

					var tileStyle = '<st'+'yle>';
					tileStyle += '#module-tile-preview-' + data.cid + ' .tile-' + side + ' {';
					if ( data['background_color_' + side ] ) tileStyle += 'background-color:'+ themifybuilderapp.Utils.toRGBA(data['background_color_' + side ]) +';';
					if ( data['text_color_' + side ] ) tileStyle += 'color:'+ themifybuilderapp.Utils.toRGBA(data['text_color_' + side ]) +';';
					if ( data['background_image_' + side ] ) tileStyle += 'background-image:url("'+ data['background_image_' + side ] +'");';
					tileStyle += '}';
					tileStyle += '#module-tile-preview-' + data.cid + ' .tile-' + side + ' a {';
					if ( data['link_color_' + side ] ) tileStyle += 'color:'+ themifybuilderapp.Utils.toRGBA(data['link_color_' + side ]) +';';
					tileStyle += '}';
					tileStyle += '</st'+'yle>';

					print(tileStyle); #>

					<div class="tile-{{ side }} tile-type-{{ data[ 'type_' + side ] }} ui {{ data[ 'color_' + side ] }}">
						<div class="tile-inner">

							<# if( data[ 'type_' + side ] == 'text' ) { 
								if( data[ 'title_' + side ] ) { #>
									<h4 class="tile-title">{{{ data[ 'title_' + side ] }}}</h4>
								<# } #>
								<div class="tile-content">
									{{{ data[ 'text_' + side ] }}}
								</div>
							<# } else if( data[ 'type_' + side ] == 'button' ) { 
								var iconStyle = data[ 'icon_color_' + side ] ? 'color:' + themifybuilderapp.Utils.toRGBA( data[ 'icon_color_' + side ] ) : '';

								if( data[ 'button_link_' + side ] ) { #>
									<a href="{{ data[ 'button_link_' + side ] }}">
									<# if( data[ 'title_' + side ] ) { #>
										<h4 class="tile-title">{{{ data[ 'title_' + side ] }}}</h4>
									<# } 
									if( data[ 'icon_type_' + side ] == 'icon' && data[ 'icon_' + side ] ) { #>
										<span class="tile-icon fa {{ data[ 'icon_' + side ] }}" <# iconStyle && print( 'style="' + iconStyle + '"' ) #>></span>
									<# } else if( data[ 'icon_type_' + side ] == 'image' && data[ 'image_' + side ] ) { #>
										<img src="{{ data[ 'image_' + side ] }}" alt="{{ data[ 'title_' + side ] }}" class="tile-icon" />
									<# } #>
					
								<# } #>

								<# if( data[ 'button_link_' + side ] ) { #>
									</a>
								<# } #>

							<# } else if( data[ 'type_' + side ] == 'gallery' ) {
								themifybuilderapp.activeModel.trigger('custom:preview:reload'); // force reload
							} else if( data[ 'type_' + side ] == 'map' ) {
								var mapData = {
									address: data[ 'address_map_' + side ].trim().replace( /\s+/, '' ),
									zoom: data[ 'zoom_map_' + side ],
									type: data[ 'type_map_' + side ],
									scroll: data[ 'scrollwheel_map_' + side ] == 'enable',
									drag: data[ 'draggable_map_' + side ] == 'enable'
								}, num = _.random( 0, 10000 ); #>

								<div id="themify_map_canvas_{{ num }}" data-map="{{ btoa( JSON.stringify( mapData ) ) }}" class="themify_map map-container"></div>
							<# } 

							if( [ 'text', 'gallery' ].includes( data[ 'type_' + side ] ) && data[ 'action_link_' + side ] ) { #>
								<a href="{{ data[ 'action_link_' + side ] }}" class="action-button">
									<span <# data[ 'link_color_' + side ] && print( 'style="border-color:' + themifybuilderapp.Utils.toRGBA( data[ 'link_color_' + side ] ) + '"' ) #>></span>
									{{{ data[ 'action_text_' + side ] }}}
								</a>
							<# } #>

						</div><!-- .tile-inner -->

					</div><!-- .tile-{{ side }} -->

				<# } ); #>
			</div><!-- .tile-flip-box --></div><!-- .tile-flip-box-wrap -->

			<?php do_action( 'themify_builder_after_template_content_render' ); ?>

		</div>
	<?php
	}
}

Themify_Builder_Model::register_module( 'TB_Tile_Module' );