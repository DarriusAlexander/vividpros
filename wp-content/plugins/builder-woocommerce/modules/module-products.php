<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: WooCommerce
 */
class TB_Products_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('WooCommerce', 'builder-wc'),
			'slug' => 'products'
		));
	}

	public function get_options() {
		$image_sizes = themify_get_image_sizes_list( false );
		return array(
			array(
				'id' => 'mod_title_products',
				'type' => 'text',
				'label' => __('Module Title', 'builder-wc'),
				'class' => 'large'
			),
			array(
				'id' => 'query_products',
				'type' => 'radio',
				'label' => __('Type', 'builder-wc'),
				'options' => array(
					'all' => __('All Products', 'builder-wc'),
					'featured' => __('Featured Products', 'builder-wc'),
					'onsale' => __('On Sale', 'builder-wc'),
					'toprated' => __('Top Rated', 'builder-wc'),
				),
				'default' => 'all',
			),
			array(
				'id' => 'category_products',
				'type' => 'query_category',
				'label' => __('Category', 'builder-wc'),
				'options' => array(
					'taxonomy' => 'product_cat',
				),
			),
                        array(
				'id' => 'hide_child_products',
				'type' => 'select',
				'label' => __('Show products only from parent category', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_free_products',
				'type' => 'select',
				'label' => __('Hide Free Products', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'post_per_page_products',
				'type' => 'text',
				'label' => __('Limit', 'builder-wc'),
				'class' => 'xsmall',
				'help' => __('number of posts to show', 'builder-wc')
			),
			array(
				'id' => 'offset_products',
				'type' => 'text',
				'label' => __('Offset', 'builder-wc'),
				'class' => 'xsmall',
				'help' => __('number of post to displace or pass over', 'builder-wc')
			),
			array(
				'id' => 'orderby_products',
				'type' => 'select',
				'label' => __('Order By', 'builder-wc'),
				'options' => array(
					'date' => __('Date', 'builder-wc'),
					'price' => __('Price', 'builder-wc'),
					'sales' => __('Sales', 'builder-wc'),
					'id' => __('Id', 'builder-wc'),
					'title' => __('Title', 'builder-wc'),
					'rand' => __('Random', 'builder-wc'),
				)
			),
			array(
				'id' => 'order_products',
				'type' => 'select',
				'label' => __('Order', 'builder-wc'),
				'help' => __('Descending = show newer posts first', 'builder-wc'),
				'options' => array(
					'desc' => __('Descending', 'builder-wc'),
					'asc' => __('Ascending', 'builder-wc')
				)
			),
			array(
				'id' => 'template_products',
				'type' => 'radio',
				'label' => __('Display as', 'builder-wc'),
				'options' => apply_filters( 'builder_products_templates', array(
					'list' => __('List', 'builder-wc'),
					'slider' => __('Slider', 'builder-wc'),
				) ),
				'default' => 'list',
				'option_js' => true
			),
			array(
				'id' => 'list',
				'type' => 'group',
				'fields' => array(
					array(
						'id' => 'layout_products',
						'type' => 'layout',
						'label' => __('Layout', 'builder-wc'),
						'options' => array(
							array('img' => 'list-post.png', 'value' => 'list-post', 'label' => __('List Post', 'builder-wc')),
							array('img' => 'grid3.png', 'value' => 'grid3', 'label' => __('Grid 3', 'builder-wc')),
							array('img' => 'grid2.png', 'value' => 'grid2', 'label' => __('Grid 2', 'builder-wc')),
							array('img' => 'grid4.png', 'value' => 'grid4', 'label' => __('Grid 4', 'builder-wc')),
							array('img' => 'list-thumb-image.png', 'value' => 'list-thumb-image', 'label' => __('List Thumb Image', 'builder-wc')),
							array('img' => 'grid2-thumb.png', 'value' => 'grid2-thumb', 'label' => __('Grid 2 Thumb', 'builder-wc'))
						)
					)
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-list',
				'render_callback' => array(
					'binding' => 'live',
					'selector' => '> .woocommerce'
				)
			),
			array(
				'id' => 'slider',
				'type' => 'group',
				'fields' => array(
					array(
						'id' => 'layout_slider',
						'type' => 'layout',
						'label' => __('Slider Layout', 'builder-wc'),
						'separated' => 'top',
						'options' => array(
							array('img' => 'slider-default.png', 'value' => 'slider-default', 'label' => __('Slider Default', 'builder-wc')),
							array('img' => 'slider-image-top.png', 'value' => 'slider-overlay', 'label' => __('Slider Overlay', 'builder-wc')),
							array('img' => 'slider-caption-overlay.png', 'value' => 'slider-caption-overlay', 'label' => __('Slider Caption Overlay', 'builder-wc')),
							array('img' => 'slider-agency.png', 'value' => 'slider-agency', 'label' => __('Agency', 'builder-wc'))
						)
					),
					array(
						'id' => 'slider_option_slider',
						'type' => 'slider',
						'label' => __('Slider Options', 'builder-wc'),
						'options' => array(
							array(
								'id' => 'visible_opt_slider',
								'type' => 'select',
								'default' => 1,
								'options' => apply_filters( 'builder_products_visible_opt_slider', array( 1 => 1, 2, 3, 4, 5, 6, 7 ) ),
								'help' => __('Visible', 'builder-wc')
							),
							array(
								'id' => 'auto_scroll_opt_slider',
								'type' => 'select',
								'default' => 4,
								'options' => apply_filters( 'builder_products_auto_scroll_opt_slider', array(
									'off' => __( 'Off', 'builder-wc' ),
									1 => __( '1 sec', 'builder-wc' ),
									2 => __( '2 sec', 'builder-wc' ),
									3 => __( '3 sec', 'builder-wc' ),
									4 => __( '4 sec', 'builder-wc' ),
									5 => __( '5 sec', 'builder-wc' ),
									6 => __( '6 sec', 'builder-wc' ),
									7 => __( '7 sec', 'builder-wc' ),
									8 => __( '8 sec', 'builder-wc' ),
									9 => __( '9 sec', 'builder-wc' ),
									10 => __( '10 sec', 'builder-wc' )
								) ),
								'help' => __('Auto Scroll', 'builder-wc')
							),
							array(
								'id' => 'scroll_opt_slider',
								'type' => 'select',
								'options' => apply_filters( 'builder_products_scroll_opt_slider', array( 1 => 1, 2, 3, 4, 5, 6, 7 ) ),
								'help' => __('Scroll', 'builder-wc')
							),
							array(
								'id' => 'speed_opt_slider',
								'type' => 'select',
								'options' => array(
									'normal' => __('Normal', 'builder-wc'),
									'fast' => __('Fast', 'builder-wc'),
									'slow' => __('Slow', 'builder-wc')
								),
								'help' => __('Speed', 'builder-wc')
							),
							array(
								'id' => 'effect_slider',
								'type' => 'select',
								'options' => array(
									'scroll' => __('Slide', 'builder-wc'),
									'fade' => __('Fade', 'builder-wc'),
									'crossfade' => __('Cross Fade', 'builder-wc'),
									'cover' => __('Cover', 'builder-wc'),
									'cover-fade' => __('Cover Fade', 'builder-wc'),
									'uncover' => __('Uncover', 'builder-wc'),
									'uncover-fade' => __('Uncover Fade', 'builder-wc'),
									'continuously' => __('Continuously', 'builder-wc')
								),
								'help' => __('Effect', 'builder-wc')
							),
							array(
								'id' => 'pause_on_hover_slider',
								'type' => 'select',
								'options' => array(
									'resume' => __('Resume', 'builder-wc'),
									'immediate' => __('Immediate', 'builder-wc'),
									'false' => __('Disable', 'builder-wc')
								),
								'help' => __('Pause On Hover', 'builder-wc')
							),
							array(
								'id' => 'wrap_slider',
								'type' => 'select',
								'help' => __('Wrap', 'builder-wc'),
								'options' => array(
									'yes' => __('Yes', 'builder-wc'),
									'no' => __('No', 'builder-wc')
								)
							),
							array(
								'id' => 'show_nav_slider',
								'type' => 'select',
								'help' => __('Show slider pagination', 'builder-wc'),
								'options' => array(
									'yes' => __('Yes', 'builder-wc'),
									'no' => __('No', 'builder-wc')
								)
							),
							array(
								'id' => 'show_arrow_slider',
								'type' => 'select',
								'help' => __('Show slider arrow buttons', 'builder-wc'),
								'options' => array(
									'yes' => __('Yes', 'builder-wc'),
									'no' => __('No', 'builder-wc')
								)
							),
							array(
								'id' => 'left_margin_slider',
								'type' => 'text',
								'class' => 'xsmall',
								'unit' => 'px',
								'help' => __('Left margin space between slides', 'builder-wc')
							),
							array(
								'id' => 'right_margin_slider',
								'type' => 'text',
								'class' => 'xsmall',
								'unit' => 'px',
								'help' => __('Right margin space between slides', 'builder-wc')
							)
						)
					)
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-slider'
			),
			array(
				'id' => 'description_products',
				'type' => 'select',
				'label' => __('Product Description', 'builder-wc'),
				'options' => array(
					'none' => __('None', 'builder-wc'),
					'short' => __('Short Description', 'builder-wc'),
					'full' => __('Full Description', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_feat_img_products',
				'type' => 'select',
				'label' => __('Hide Product Image', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'image_size_products',
				'type' => 'select',
				'label' => Themify_Builder_Model::is_img_php_disabled() ? __('Image Size', 'builder-wc') : false,
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'hide' => Themify_Builder_Model::is_img_php_disabled() ? false : true,
				'options' => $image_sizes
			),
			array(
				'id' => 'img_width_products',
				'type' => 'text',
				'label' => __('Image Width', 'builder-wc'),
				'class' => 'xsmall'
			),
			array(
				'id' => 'img_height_products',
				'type' => 'text',
				'label' => __('Image Height', 'builder-wc'),
				'class' => 'xsmall'
			),
			array(
				'id' => 'unlink_feat_img_products',
				'type' => 'select',
				'label' => __('Unlink Product Image', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_post_title_products',
				'type' => 'select',
				'label' => __('Hide Products Title', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'unlink_post_title_products',
				'type' => 'select',
				'label' => __('Unlink Products Title', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_price_products',
				'type' => 'select',
				'label' => __('Hide Price', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_add_to_cart_products',
				'type' => 'select',
				'label' => __('Hide Add To Cart Button', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_rating_products',
				'type' => 'select',
				'label' => __('Hide Rating', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_sales_badge',
				'type' => 'select',
				'label' => __('Hide Sales Badge', 'builder-wc'),
				'options' => array(
					'no' => __('No', 'builder-wc'),
					'yes' => __('Yes', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_page_nav_products',
				'type' => 'select',
				'label' => __('Hide Product Navigation', 'builder-wc'),
				'options' => array(
					'yes' => __('Yes', 'builder-wc'),
					'no' => __('No', 'builder-wc')
				),
				'default' => 'Yes',
				'wrap_with_class' => 'tf-group-element tf-group-element-list'
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
			'post_per_page_products' => 6,
			'layout_products' => 'grid3'
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'builder-wc' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'builder-wc'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'title' => __( 'Effect', 'builder-wc' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'title' => __( 'Delay', 'builder-wc' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'builder-wc' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'title' => __( 'Repeat', 'builder-wc' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'builder-wc' ),
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
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-wc').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-wc'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-products'
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
				'selector' => array( '.module-products', '.module-products .product-title a' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-wc'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-products', '.module-products .product-title a' )
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
				'selector' => '.module-products'
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
				'selector' => '.module-products a'
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
				'selector' => '.module-products a'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-products'
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-wc'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
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
						'selector' => '.module-products'
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-products'
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-wc'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-products'
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
						'selector' => '.module-products'
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-products'
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-wc'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-products'
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

Themify_Builder_Model::register_module( 'TB_Products_Module' );

function builder_woocommerce_return_no() {
	return 'no';
}

function builder_woocommerce_return_yes() {
	return 'yes';
}