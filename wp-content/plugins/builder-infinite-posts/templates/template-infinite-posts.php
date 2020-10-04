<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Infinite Posts
 * 
 * Access original fields: $mod_settings
 */

$fields_default = array(
    'mod_title' => '',
    'post_type_post' => 'post',
    'type_query_post' => 'category',
    'category_post' => '',
    'query_slug_post' => '',
    'post_per_page_post' => '',
    'offset_post' => '',
    'order_post' => 'desc',
    'orderby_post' => 'date',
	'background_style' => 'builder-parallax-scrolling',
	'pagination' => 'infinite-scroll',
	'layout' => 'parallax',
	'post_layout' => 'grid-1',
	'masonry' => 'enabled',
	'gutter' => 'default',
	'permalink' => 'default',
	'image_size' => '',
	'img_width' => '',
	'img_height' => '',
	'read_more_text' => __( 'Read More', 'builder-infinite-posts' ),
	'color_button' => 'red',
	'row_height' => 'height-default',
	'overlay_color' => '000000_0.30',
	'text_color' => 'ffffff',
	'animation_effect' => '',
	'display_content' => 'excerpt',
	'hide_post_title' => 'no',
	'hide_post_date' => 'yes',
	'read_more_size' => 'small',
	'unlink_image' => 'yes',
	'unlink_post_title' => 'no',
	'hide_post_meta' => 'yes',
	'buttons_style' => 'colored',
	'hide_read_more_button' => 'no',
	'css_post' => ''
);

if (isset($mod_settings['category_post']))
    $mod_settings['category_post'] = $this->get_param_value($mod_settings['category_post']);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

$container_class = array( 'module', 'module-' . $mod_name, $module_ID, $css_post, 'pagination-' . $pagination, 'layout-' . $layout );
if( $layout == 'parallax' ) {
	$container_class[] = $row_height;
} else {
	if( $layout == 'grid' || $layout == 'overlay' ) {
		$container_class[] = $post_layout;
		// disable masonry when using grid-1
		if( $post_layout != 'grid-1' ) {
			$container_class[] = 'gutter-' . $gutter;
			$container_class[] = 'masonry-' . $masonry;
		}
	}
}
$container_class = implode( ' ', apply_filters('themify_builder_module_classes', $container_class, $mod_name, $module_ID, $fields_args) );

global $wp, $post;
/**
 * Do not use the global $paged variable, some pages (404 for example) don't support this
 * which breaks the pagination; the $_GET[tb_infinite] is used instead
 */
$paged = 1;
if( isset( $_GET[ 'tb_infinite' ] ) && ! empty( $_GET[ 'tb_infinite' ] ) ) {
	$paged = $_GET[ 'tb_infinite' ];
}

// The Query

$order = $order_post;
$orderby = $orderby_post;
$limit = $post_per_page_post;
$terms = isset($fields_args["{$type_query_post}_post"]) ? $fields_args["{$type_query_post}_post"] : $category_post;
// deal with how category fields are saved
$terms = preg_replace('/\|[multiple|single]*$/', '', $terms);

$temp_terms = explode(',', $terms);
$new_terms = array();
$is_string = false;
foreach ($temp_terms as $t) {
	if (!is_numeric($t))
		$is_string = true;
	if ('' != $t) {
		array_push($new_terms, trim($t));
	}
}
$tax_field = ( $is_string ) ? 'slug' : 'id';

$args = array(
	'post_status' => 'publish',
	'posts_per_page' => $limit,
	'order' => $order,
	'orderby' => $orderby,
	'suppress_filters' => false,
	'paged' => $paged,
	'post_type' => $post_type_post
);

if (count($new_terms) > 0 && !in_array('0', $new_terms) && 'post_slug' !== $type_query_post) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => $type_query_post,
			'field' => $tax_field,
			'terms' => $new_terms,
			'operator' => ( '-' == substr($terms, 0, 1) ) ? 'NOT IN' : 'IN',
		)
	);
}

if (!empty($query_slug_post) && 'post_slug' == $type_query_post) {
	$args['post__in'] = $this->parse_slug_to_ids( $query_slug_post, $post_type_post );
}

// add offset posts
if ($offset_post != '') {
	if (empty($limit))
		$limit = get_option('posts_per_page');

	$args['offset'] = ( ( $paged - 1 ) * $limit ) + $offset_post;
}

$query = new WP_Query( $args );

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $module_ID,
	'class' => $container_class,
	'data-url' => add_query_arg( 'tb_infinite', 0, remove_query_arg( 'tb_infinite' ) ),
	'data-current-page' => $paged
), $fields_args, $mod_name, $module_ID );
?>

<div <?php echo $this->get_element_attributes( $container_props ); ?>>

	<?php if ($mod_title != ''): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title, $fields_args)) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<div class="builder-infinite-posts-wrap clearfix">

		<?php
		$template = $this->locate_template( "infinite-posts-{$post_type_post}.php" );
		if( "infinite-posts-{$post_type_post}.php" == $template ) {
			// use default template for Post post type to render
			$template = $this->locate_template( "infinite-posts-post.php" );
		}
		include( $template );
		?>
	</div><!-- .builder-infinite-posts-wrap -->

	<?php if( $pagination == 'infinite-scroll' || $pagination == 'links' ) : ?>

		<?php echo TB_Infinite_Posts_Module::get_pagenav( '', '', $query, $offset_post ); ?>

	<?php elseif( $pagination == 'load-more' ) : ?>

		<?php
		$total_pages  = $query->max_num_pages;
		$current_page = $paged;
		if ( $total_pages > $current_page ) : ?>
			<div class="infinite-posts-load-more-wrap">
				<a class="ui builder_button rounded glossy white infinite-posts-load-more" href="<?php echo add_query_arg( 'tb_infinite', $paged + 1 ) ?>">
					<i class="fa fa-cog fa-spin"></i>
					<?php _e( 'Load More', 'builder-infinite-posts' ); ?>
				</a>
			</div><!-- .infinite-posts-load-more-wrap -->
		<?php endif; ?>

	<?php endif; ?>

</div>