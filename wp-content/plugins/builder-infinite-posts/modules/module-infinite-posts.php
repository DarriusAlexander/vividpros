<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Infinite Posts
 */
class TB_Infinite_Posts_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Infinite Posts', 'builder-infinite-posts'),
			'slug' => 'infinite-posts'
		));
	}

	public function get_options() {
		global $ThemifyBuilder;

		$url = Builder_Infinite_Posts::get_instance()->url;
		$image_sizes = themify_get_image_sizes_list( false );

		$taxonomies = $ThemifyBuilder->get_public_taxonomies();
		$term_options = array();
		
		foreach( $taxonomies as $key => $label ) {
			$term_options[] = array(
				'id' => "{$key}_post",
				'label' => $label,
				'type' => 'query_category',
				'options' => array( 'taxonomy' => $key ),
				'wrap_with_class' => "tf-group-element tf-group-element-{$key}"
			);
		}

		/* allow query posts by slug */
		$taxonomies['post_slug'] = __('Slug', 'builder-infinite-posts');

		$options = array(
			array(
				'id' => 'mod_title',
				'type' => 'text',
				'label' => __('Module Title', 'builder-infinite-posts'),
				'class' => 'large',
			),
			array(
				'id' => 'post_type_post',
				'type' => 'select',
				'label' => __('Post Type', 'builder-infinite-posts'),
				'options' => $ThemifyBuilder->get_public_post_types()
			),
			array(
				'id' => 'type_query_post',
				'type' => 'radio',
				'label' => __('Query by', 'builder-infinite-posts'),
				'options' => $taxonomies,
				'default' => 'category',
				'option_js' => true
			),
			array(
				'type' => 'group',
				'fields' => $term_options
			),
			array(
				'id' => 'query_slug_post',
				'type' => 'text',
				'label' => __('Post Slugs', 'builder-infinite-posts'),
				'class' => 'large',
				'wrap_with_class' => 'tf-group-element tf-group-element-post_slug',
				'help' => '<br/>' . __( 'Insert post slug. Multiple slug should be separated by comma (,)', 'builder-infinite-posts'),
			),
			array(
				'id' => 'post_per_page_post',
				'type' => 'text',
				'label' => __('Limit', 'builder-infinite-posts'),
				'class' => 'xsmall',
				'help' => __('number of posts to show', 'builder-infinite-posts'),
			),
			array(
				'id' => 'offset_post',
				'type' => 'text',
				'label' => __('Offset', 'builder-infinite-posts'),
				'class' => 'xsmall',
				'help' => __('number of post to displace or pass over', 'builder-infinite-posts'),
			),
			array(
				'id' => 'order_post',
				'type' => 'select',
				'label' => __('Order', 'builder-infinite-posts'),
				'help' => __('Descending = show newer posts first', 'builder-infinite-posts'),
				'options' => array(
					'desc' => __('Descending', 'builder-infinite-posts'),
					'asc' => __('Ascending', 'builder-infinite-posts')
				)
			),
			array(
				'id' => 'orderby_post',
				'type' => 'select',
				'label' => __('Order By', 'builder-infinite-posts'),
				'options' => array(
					'date' => __('Date', 'builder-infinite-posts'),
					'id' => __('Id', 'builder-infinite-posts'),
					'author' => __('Author', 'builder-infinite-posts'),
					'title' => __('Title', 'builder-infinite-posts'),
					'name' => __('Name', 'builder-infinite-posts'),
					'modified' => __('Modified', 'builder-infinite-posts'),
					'rand' => __('Random', 'builder-infinite-posts'),
					'comment_count' => __('Comment Count', 'builder-infinite-posts')
				)
			),
			array(
				'id' => 'layout',
				'type' => 'radio',
				'label' => __('Layout', 'builder-infinite-posts'),
				'options' => array(
					'parallax' => __( 'Parallax', 'builder-infinite-posts' ),
					'list' => __( 'List', 'builder-infinite-posts' ),
					'grid' => __( 'Grid', 'builder-infinite-posts' ),
					'overlay' => __( 'Overlay', 'builder-infinite-posts' ),
				),
				'default' => 'parallax',
				'option_js' => true
			),
			array(
				'id' => 'post_layout',
				'type' => 'layout',
				'label' => __('Post Layout', 'builder-infinite-posts'),
				'options' => array(
					array('img' => 'grid2.png', 'value' => 'grid-2', 'label' => __('Grid 2', 'builder-infinite-posts')),
					array('img' => 'grid3.png', 'value' => 'grid-3', 'label' => __('Grid 3', 'builder-infinite-posts')),
					array('img' => 'grid4.png', 'value' => 'grid-4', 'label' => __('Grid 4', 'builder-infinite-posts')),
				),
				'default' => '',
				'wrap_with_class' => 'tf-group-element tf-group-element-grid tf-group-element-overlay'
			),
			array(
				'id' => 'image_size',
				'type' => 'select',
				'label' => Themify_Builder_Model::is_img_php_disabled() ? __('Image Size', 'builder-infinite-posts') : false,
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'hide' => Themify_Builder_Model::is_img_php_disabled() ? false : true,
				'options' => $image_sizes,
				'wrap_with_class' => 'tf-group-element tf-group-element-grid tf-group-element-list tf-group-element-overlay'
			),
			array(
				'id' => 'img_width',
				'type' => 'text',
				'label' => __('Image Width', 'builder-infinite-posts'),
				'class' => 'xsmall',
				'wrap_with_class' => 'tf-group-element tf-group-element-list tf-group-element-grid tf-group-element-overlay',
			),
			array(
				'id' => 'img_height',
				'type' => 'text',
				'label' => __('Image Height', 'builder-infinite-posts'),
				'class' => 'xsmall',
				'wrap_with_class' => 'tf-group-element tf-group-element-list tf-group-element-grid tf-group-element-overlay',
			),
			array(
				'id' => 'row_height',
				'type' => 'select',
				'label' => __('Post Height', 'builder-infinite-posts'),
				'options' => array(
					'height-default' => __('Default', 'builder-infinite-posts'),
					'fullheight' => __('Fullheight', 'builder-infinite-posts'),
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-parallax'
			),
			array(
				'id' => 'background_style',
				'type' => 'select',
				'label' => __('Background Style', 'builder-infinite-posts'),
				'options' => array(
					'builder-parallax-scrolling' => __('Parallax Scrolling', 'builder-infinite-posts'),
					'fullcover' => __('Full Cover', 'builder-infinite-posts'),
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-parallax'
			),
			array(
				'id' => 'overlay_color',
				'type' => 'text',
				'colorpicker' => true,
				'label' => __('Overlay Color', 'builder-infinite-posts'),
				'class' => 'small',
				'wrap_with_class' => 'tf-group-element tf-group-element-parallax',
			),
			array(
				'id' => 'masonry',
				'type' => 'select',
				'label' => __('Masonry Layout', 'builder-infinite-posts'),
				'options' => array(
					'enabled' => __('Enabled', 'builder-infinite-posts'),
					'disabled' => __('Disabled', 'builder-infinite-posts'),
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid tf-group-element-overlay'
			),
			array(
				'id' => 'gutter',
				'type' => 'select',
				'label' => __('Gutter Spacing', 'builder-infinite-posts'),
				'options' => array(
					'default' => __('Default', 'builder-infinite-posts'),
					'narrow' => __('Narrow', 'builder-infinite-posts'),
					'none' => __('None', 'builder-infinite-posts'),
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid tf-group-element-overlay'
			),
			array(
				'id' => 'pagination',
				'type' => 'select',
				'label' => __('Pagination', 'builder-infinite-posts'),
				'options' => array(
					'infinite-scroll' => __('Infinite Scroll', 'builder-infinite-posts'),
					'links' => __('Pagination Links', 'builder-infinite-posts'),
					'load-more' => __('Load More Button', 'builder-infinite-posts'),
					'disabled' => __('No Pagination', 'builder-infinite-posts'),
				)
			),
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'display_content',
				'type' => 'select',
				'label' => __('Display', 'builder-infinite-posts'),
				'options' => array(
					'excerpt' => __('Excerpt', 'builder-infinite-posts'),
					'content' => __('Content', 'builder-infinite-posts'),
					'none' => __('None', 'builder-infinite-posts'),
				)
			),
			array(
				'id' => 'unlink_image',
				'type' => 'select',
				'label' => __('Unlink Featured Image', 'builder-infinite-posts'),
				'options' => array(
					'yes' => __('Yes', 'builder-infinite-posts'),
					'no' => __('No', 'builder-infinite-posts'),
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid tf-group-element-list'
			),
			array(
				'id' => 'hide_post_title',
				'type' => 'select',
				'label' => __('Hide Post Title', 'builder-infinite-posts'),
				'options' => array(
					'no' => __('No', 'builder-infinite-posts'),
					'yes' => __('Yes', 'builder-infinite-posts'),
				)
			),
			array(
				'id' => 'unlink_post_title',
				'type' => 'select',
				'label' => __('Unlink Post Title', 'builder-infinite-posts'),
				'options' => array(
					'no' => __('No', 'builder-infinite-posts'),
					'yes' => __('Yes', 'builder-infinite-posts'),
				)
			),
			array(
				'id' => 'hide_post_date',
				'type' => 'select',
				'label' => __('Hide Post Date', 'builder-infinite-posts'),
				'options' => array(
					'yes' => __('Yes', 'builder-infinite-posts'),
					'no' => __('No', 'builder-infinite-posts'),
				)
			),
			array(
				'id' => 'hide_post_meta',
				'type' => 'select',
				'label' => __('Hide Post Meta', 'builder-infinite-posts'),
				'options' => array(
					'yes' => __('Yes', 'builder-infinite-posts'),
					'no' => __('No', 'builder-infinite-posts'),
				)
			),
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr /><h4>' . __( 'Read More Button', 'builder-infinite-posts' ) . '</h4>' )
			),
			array(
				'id' => 'hide_read_more_button',
				'type' => 'select',
				'label' => __('Hide Read More Button', 'builder-infinite-posts'),
				'options' => array(
					'no' => __('No', 'builder-infinite-posts'),
					'yes' => __('Yes', 'builder-infinite-posts'),
				)
			),
			array(
				'id' => 'read_more_text',
				'type' => 'text',
				'label' => __('Button Text', 'builder-infinite-posts'),
				'class' => '',
				'value' => __( 'Read More', 'builder-infinite-posts' ),
			),
			array(
				'id' => 'permalink',
				'type' => 'select',
				'label' => __('Open Link In', 'builder-infinite-posts'),
				'options' => array(
					'default' => __('Same Window', 'builder-infinite-posts'),
					'lightboxed' => __('Lightbox', 'builder-infinite-posts'),
					'newwindow' => __('New Window', 'builder-infinite-posts'),
				)
			),
			array(
				'id' => 'buttons_style',
				'type' => 'radio',
				'label' => __( 'Button Style', 'builder-infinite-posts' ),
				'options' => array(
					'colored' => __( 'Colored', 'builder-infinite-posts' ),
					'outline' => __( 'Outlined', 'builder-infinite-posts' ),
				),
				'default' => 'colored'
			),
			array(
				'id' => 'color_button',
				'type' => 'layout',
				'label' => __('Button Color', 'builder-infinite-posts'),
				'options' => array(
					array('img' => 'color-black.png', 'value' => 'black', 'label' => __('black', 'builder-infinite-posts')),
					array('img' => 'color-white.png', 'value' => 'white', 'label' => __('white', 'builder-infinite-posts')),
					array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('gray', 'builder-infinite-posts')),
					array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('blue', 'builder-infinite-posts')),
					array('img' => 'color-light-blue.png', 'value' => 'light-blue', 'label' => __('light-blue', 'builder-infinite-posts')),
					array('img' => 'color-green.png', 'value' => 'green', 'label' => __('green', 'builder-infinite-posts')),
					array('img' => 'color-light-green.png', 'value' => 'light-green', 'label' => __('light-green', 'builder-infinite-posts')),
					array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('purple', 'builder-infinite-posts')),
					array('img' => 'color-light-purple.png', 'value' => 'light-purple', 'label' => __('light-purple', 'builder-infinite-posts')),
					array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('brown', 'builder-infinite-posts')),
					array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('orange', 'builder-infinite-posts')),
					array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('yellow', 'builder-infinite-posts')),
					array('img' => 'color-red.png', 'value' => 'red', 'label' => __('red', 'builder-infinite-posts')),
					array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('pink', 'builder-infinite-posts')),
					array('img' => 'color-transparent.png', 'value' => 'default', 'label' => __('Default', 'builder-infinite-posts'))
				)
			),
			array(
				'id' => 'read_more_size',
				'type' => 'radio',
				'label' => __('Button Size', 'builder-infinite-posts'),
				'options' => array(
					'small' => __('Small', 'builder-infinite-posts'),
					'normal' => __('Normal', 'builder-infinite-posts'),
					'large' => __('Large', 'builder-infinite-posts'),
					'xlarge' => __('xLarge', 'builder-infinite-posts'),
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_class_contact',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'builder-infinite-posts'),
				'class' => 'large exclude-from-reset-field',
				'description' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'builder-infinite-posts') ),
			)
		);

		return $options;
	}

	public function get_default_settings() {
		return array(
			'post_per_page_post' => 3,
			'overlay_color' => '000000_0.30'
		);
	}

	public function get_animation() {
		$animation = array(
			array(
				'id' => 'animation_effect',
				'type' => 'animation_select',
				'label' => __( 'Effect', 'builder-image-pro' )
			),
			array(
				'id' => 'animation_effect_delay',
				'type' => 'text',
				'label' => __( 'Delay', 'builder-image-pro' ),
				'class' => 'xsmall',
				'description' => __( 'Delay (s)', 'builder-image-pro' ),
			),
			array(
				'id' => 'animation_effect_repeat',
				'type' => 'text',
				'label' => __( 'Repeat', 'builder-image-pro' ),
				'class' => 'xsmall',
				'description' => __( 'Repeat (x)', 'builder-image-pro' ),
			),
		);

		return $animation;
	}

	public function get_styling() {
		$general = array(
			// Background
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Background', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-infinite-posts'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module .infinite-post-inner' ),
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'font_size_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'line_height_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'text_align',
				'label' => __( 'Text Align', 'builder-infinite-posts' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'builder-infinite-posts' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'builder-infinite-posts' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'builder-infinite-posts' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'builder-infinite-posts' ) ),
					array( 'value' => 'justify', 'name' => __( 'Justify', 'builder-infinite-posts' ) )
				),
				'prop' => 'text-align',
				'selector' => '.module-infinite-posts'
			),
			// Link
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_link',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Link', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'link_color',
				'type' => 'color',
				'label' => __('Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module a:not(.builder_button)'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'builder-infinite-posts' ),
				'meta'	=> array(
					array('value' => '',   'name' => '', 'selected' => true),
					array('value' => 'underline',   'name' => __('Underline', 'builder-infinite-posts')),
					array('value' => 'overline', 'name' => __('Overline', 'builder-infinite-posts')),
					array('value' => 'line-through',  'name' => __('Line through', 'builder-infinite-posts')),
					array('value' => 'none',  'name' => __('None', 'builder-infinite-posts'))
				),
				'prop' => 'text-decoration',
				'selector' => '.module a:not(.builder_button)'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'padding_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'padding_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'padding_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'padding_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
					array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'builder-infinite-posts' ) )
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
				'meta' => array('html'=>'<h4>'.__('Margin', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'margin_top_unit',
						'type' => 'select',
						'description' => __('top', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'margin_right_unit',
						'type' => 'select',
						'description' => __('right', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'margin_bottom_unit',
						'type' => 'select',
						'description' => __('bottom', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'margin_left_unit',
						'type' => 'select',
						'description' => __('left', 'builder-infinite-posts'),
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
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
					array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'builder-infinite-posts' ) )
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
				'meta' => array('html'=>'<h4>'.__('Border', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'builder-infinite-posts'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-infinite-posts'
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
						'selector' => '.module-infinite-posts'
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
						'description' => __('right', 'builder-infinite-posts'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-infinite-posts'
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'builder-infinite-posts'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-infinite-posts'
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
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-infinite-posts'
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'builder-infinite-posts'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-infinite-posts'
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
					array( 'name' => 'border', 'value' => __( 'Apply to all border', 'builder-infinite-posts' ) )
				)
			)
		);

		$post_title = array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family_post_title',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts .post-title' )
			),
			array(
				'id' => 'font_color_post_title',
				'type' => 'color',
				'label' => __('Font Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-infinite-posts .post-title', '.module-infinite-posts .post-title a' ),
			),
			array(
				'id' => 'multi_font_size_post_title',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size_post_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts .post-title'
					),
					array(
						'id' => 'font_size_post_title_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts')),
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height_post_title',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'line_height_post_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-infinite-posts .post-title'
					),
					array(
						'id' => 'line_height_post_title_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
						)
					)
				)
			),
		);

		$post_date = array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family_post_date',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts .post-date' )
			),
			array(
				'id' => 'font_color_post_date',
				'type' => 'color',
				'label' => __('Font Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-infinite-posts .post-date', '.module-infinite-posts .post-date a' ),
			),
			array(
				'id' => 'multi_font_size_post_date',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size_post_date',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts .post-date'
					),
					array(
						'id' => 'font_size_post_date_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height_post_date',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'line_height_post_date',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-infinite-posts .post-date'
					),
					array(
						'id' => 'line_height_post_date_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
						)
					)
				)
			),
		);

		$post_meta = array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family_post_meta',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts .post-meta' )
			),
			array(
				'id' => 'font_color_post_meta',
				'type' => 'color',
				'label' => __('Font Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-infinite-posts .post-meta', '.module-infinite-posts .post-meta a' ),
			),
			array(
				'id' => 'multi_font_size_post_meta',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size_post_meta',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts .post-meta'
					),
					array(
						'id' => 'font_size_post_meta_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height_post_meta',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'line_height_post_meta',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-infinite-posts .post-meta'
					),
					array(
						'id' => 'line_height_post_meta_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
						)
					)
				)
			),
		);

		$post_content = array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family_post_content',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts .bip-post-content' )
			),
			array(
				'id' => 'font_color_post_content',
				'type' => 'color',
				'label' => __('Font Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-infinite-posts .bip-post-content' ),
			),
			array(
				'id' => 'multi_font_size_post_content',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size_post_meta',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts .bip-post-content'
					),
					array(
						'id' => 'font_size_post_content_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height_post_content',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'line_height_post_content',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-infinite-posts .bip-post-content'
					),
					array(
						'id' => 'line_height_post_content_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
						)
					)
				)
			),
		);

		$post_title = array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family_post_title',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts .post-title' )
			),
			array(
				'id' => 'font_color_post_title',
				'type' => 'color',
				'label' => __('Font Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-infinite-posts .post-title', '.module-infinite-posts .post-title a' ),
			),
			array(
				'id' => 'multi_font_size_post_title',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size_post_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts .post-title'
					),
					array(
						'id' => 'font_size_post_title_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height_post_title',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'line_height_post_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-infinite-posts .post-title'
					),
					array(
						'id' => 'line_height_post_title_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
						)
					)
				)
			),
		);

		$post_date = array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family_post_date',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts .post-date' )
			),
			array(
				'id' => 'font_color_post_date',
				'type' => 'color',
				'label' => __('Font Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-infinite-posts .post-date', '.module-infinite-posts .post-date a' ),
			),
			array(
				'id' => 'multi_font_size_post_date',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size_post_date',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts .post-date'
					),
					array(
						'id' => 'font_size_post_date_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'multi_line_height_post_date',
				'type' => 'multi',
				'label' => __('Line Height', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'line_height_post_date',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-infinite-posts .post-date'
					),
					array(
						'id' => 'line_height_post_date_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts')),
							array('value' => '%', 'name' => __('%', 'builder-infinite-posts'))
						)
					)
				)
			),
		);

		$read_more_button = array(
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'builder-infinite-posts').'</h4>'),
			),
			array(
				'id' => 'font_family_read_more',
				'type' => 'font_select',
				'label' => __('Font Family', 'builder-infinite-posts'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-infinite-posts a.read-more-button' )
			),
			array(
				'id' => 'background_color_read_more',
				'type' => 'color',
				'label' => __('Background Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-infinite-posts a.read-more-button'
			),
			array(
				'id' => 'font_color_read_more',
				'type' => 'color',
				'label' => __('Color', 'builder-infinite-posts'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-infinite-posts a.read-more-button' ),
			),
			array(
				'id' => 'multi_font_size_read_more',
				'type' => 'multi',
				'label' => __('Font Size', 'builder-infinite-posts'),
				'fields' => array(
					array(
						'id' => 'font_size_read_more',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-infinite-posts a.read-more-button'
					),
					array(
						'id' => 'font_size_read_more_unit',
						'type' => 'select',
						'meta' => array(
							array('value' => 'px', 'name' => __('px', 'builder-infinite-posts')),
							array('value' => 'em', 'name' => __('em', 'builder-infinite-posts'))
						)
					)
				)
			),
			array(
				'id' => 'text_align_read_more',
				'label' => __( 'Alignment', 'builder-infinite-posts' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'builder-infinite-posts' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'builder-infinite-posts' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'builder-infinite-posts' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'builder-infinite-posts' ) ),
				),
				'prop' => 'text-align',
				'selector' => '.module-infinite-posts .read-more-button-wrap'
			),
		);

		return array(
			array(
				'type' => 'tabs',
				'id' => 'module-styling',
				'tabs' => array(
					'general' => array(
						'label' => __('General', 'builder-infinite-posts'),
						'fields' => $general
					),
					'title' => array(
						'label' => __('Post Title', 'builder-infinite-posts'),
						'fields' => $post_title
					),
					'meta' => array(
						'label' => __('Post Meta', 'builder-infinite-posts'),
						'fields' => $post_meta
					),
					'date' => array(
						'label' => __('Post Date', 'builder-infinite-posts'),
						'fields' => $post_date
					),
					'content' => array(
						'label' => __('Post Content', 'builder-infinite-posts'),
						'fields' => $post_content
					),
					'read_more_button' => array(
						'label' => __('Read More Button', 'builder-infinite-posts'),
						'fields' => $read_more_button
					),
				)
			),
		);
	}

	public static function get_pagenav( $before = '', $after = '', $query = false, $original_offset = 0 ) {
		global $wpdb, $wp_query;

		if (false == $query) {
			$query = $wp_query;
		}

		$request = $query->request;
		$posts_per_page = intval(get_query_var('posts_per_page'));
		$paged = 1;
		if( isset( $_GET['tb_infinite'] ) && ! empty( $_GET['tb_infinite'] ) ) {
			$paged = $_GET['tb_infinite'];
		}
		$numposts = $query->found_posts;

		// $query->found_posts does not take offset into account, we need to manually adjust that
		if( (int) $original_offset ) {
			$numposts = $numposts - (int) $original_offset;
		}

		$max_page = ceil( $numposts / $query->query_vars['posts_per_page'] );
		$out = '';

		if (empty($paged) || $paged == 0) {
			$paged = 1;
		}
		$pages_to_show = apply_filters('themify_filter_pages_to_show', 5);
		$pages_to_show_minus_1 = $pages_to_show - 1;
		$half_page_start = floor($pages_to_show_minus_1 / 2);
		$half_page_end = ceil($pages_to_show_minus_1 / 2);
		$start_page = $paged - $half_page_start;
		if ($start_page <= 0) {
			$start_page = 1;
		}
		$end_page = $paged + $half_page_end;
		if (($end_page - $start_page) != $pages_to_show_minus_1) {
			$end_page = $start_page + $pages_to_show_minus_1;
		}
		if ($end_page > $max_page) {
			$start_page = $max_page - $pages_to_show_minus_1;
			$end_page = $max_page;
		}
		if ($start_page <= 0) {
			$start_page = 1;
		}

		if ($max_page > 1) {
			$out .= $before . '<div class="pagenav clearfix">';
			for ($i = $start_page; $i <= $end_page; $i++) {
				if ($i == $paged) {
					$out .= ' <span class="number current">' . $i . '</span> ';
				} else {
					$out .= ' <a href="' . esc_url( add_query_arg( array( 'tb_infinite' => $i ) ) ) . '" class="number">' . $i . '</a> ';
				}
			}
			$out .= '</div>' . $after;
		}

		return $out;
	}
}
Themify_Builder_Model::register_module( 'TB_Infinite_Posts_Module' );