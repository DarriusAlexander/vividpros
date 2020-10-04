<?php

class Builder_Timeline_Post_Source {

	public function get_id() {
		return 'posts';
	}

	public function get_name() {
		return __( 'Posts', 'builder-timeline' );
	}

	public function get_items( $args ) {
		global $ThemifyBuilder, $paged, $post;
		$items = array();
		$args = wp_parse_args( $args, array(
			'category_post_timeline' => '',
			'post_per_page_post_timeline' => '',
			'offset_post_timeline' => 0,
			'order_post_timeline' => '',
			'orderby_post_timeline' => '',
			'display_post_timeline' => '',
			'hide_feat_img_post_timeline' => '',
			'image_size_post_timeline' => '',
			'img_width_post_timeline' => '',
			'img_height_post_timeline' => ''
		) );
		if( isset( $args['category_post_timeline'] ) )
			$args['category_post_timeline'] = $ThemifyBuilder->get_param_value( $args['category_post_timeline'] );
		extract( $args, EXTR_SKIP );
		$paged = $ThemifyBuilder->get_paged_query();

		$query = array(
			'post_type' => 'post',
			'posts_per_page' => $post_per_page_post_timeline,
			'order' => $order_post_timeline,
			'orderby' => $orderby_post_timeline,
			'paged' => $paged,
		);
		if( $offset_post_timeline != '' ) {
			$query['offset'] = ( ( $paged - 1 ) * $post_per_page_post_timeline ) + $offset_post_timeline;
		}

		$temp_terms = explode( ',', $category_post_timeline );
		$terms = array();
		$is_string = false;
		foreach ( $temp_terms as $t ) {
			if ( ! is_numeric( $t ) )
				$is_string = true;
			if ( '' != $t ) {
				array_push( $terms, trim( $t ) );
			}
		}
		$tax_field = ( $is_string ) ? 'slug' : 'id';

		if ( count( $terms ) > 0 && ! in_array( '0', $terms ) ) {
			$query['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field' => $tax_field,
					'terms' => $terms
				)
			);
		}

		$query = new WP_Query( apply_filters( 'builder_timeline_source_post_query', $query ) );
		if( $query->have_posts() ) : while( $query->have_posts() ) : $query->the_post();
			$item = array(
				'id' => get_the_ID(),
				'title' => get_the_title(),
				'icon' => '',
				'icon_color' => '',
				'link' => get_permalink(),
				'date' => mysql2date( 'Y-m-d G:i:s', $post->post_date ), /* do not use get_the_date to avoid translation of the date which will break strtotime */
				'date_formatted' => date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ),
				'hide_featured_image' => 'yes' == $hide_feat_img_post_timeline || ! has_post_thumbnail(),
				'image' => themify_get_image( 'ignore=true&w='. $img_width_post_timeline .'&h=' . $img_height_post_timeline ),
				'hide_content' => 'none' == $display_post_timeline,
				'content' => ( 'content' == $display_post_timeline ) ? get_the_content() : get_the_excerpt(),
			);
			$items[] = $item;
		endwhile; endif; wp_reset_postdata();

		return apply_filters( 'builder_timeline_source_post_items', $items );
	}

	public function get_options() {
		global $ThemifyBuilder;

		$image_sizes = themify_get_image_sizes_list( false );

		return array(
			array(
				'id' => 'category_post_timeline',
				'type' => 'query_category',
				'label' => __('Category', 'builder-timeline'),
				'options' => array(),
				'help' => sprintf(__('Add more <a href="%s" target="_blank">blog posts</a>', 'builder-timeline'), admin_url('post-new.php')),
			),
			array(
				'id' => 'post_per_page_post_timeline',
				'type' => 'text',
				'label' => __('Limit', 'builder-timeline'),
				'class' => 'xsmall',
				'help' => __('number of posts to show', 'builder-timeline')
			),
			array(
				'id' => 'offset_post_timeline',
				'type' => 'text',
				'label' => __('Offset', 'builder-timeline'),
				'class' => 'xsmall',
				'help' => __('number of post to displace or pass over', 'builder-timeline')
			),
			array(
				'id' => 'order_post_timeline',
				'type' => 'select',
				'label' => __('Order', 'builder-timeline'),
				'help' => __('Descending = show newer posts first', 'builder-timeline'),
				'options' => array(
					'desc' => __('Descending', 'builder-timeline'),
					'asc' => __('Ascending', 'builder-timeline')
				)
			),
			array(
				'id' => 'orderby_post_timeline',
				'type' => 'select',
				'label' => __('Order By', 'builder-timeline'),
				'options' => array(
					'date' => __('Date', 'builder-timeline'),
					'id' => __('Id', 'builder-timeline'),
					'author' => __('Author', 'builder-timeline'),
					'title' => __('Title', 'builder-timeline'),
					'name' => __('Name', 'builder-timeline'),
					'modified' => __('Modified', 'builder-timeline'),
					'rand' => __('Rand', 'builder-timeline'),
					'comment_count' => __('Comment Count', 'builder-timeline')
				)
			),
			array(
				'id' => 'display_post_timeline',
				'type' => 'select',
				'label' => __('Display', 'builder-timeline'),
				'options' => array(
					'excerpt' => __('Excerpt', 'builder-timeline'),
					'content' => __('Content', 'builder-timeline'),
					'none' => __('None', 'builder-timeline')
				)
			),
			array(
				'id' => 'hide_feat_img_post_timeline',
				'type' => 'select',
				'label' => __('Hide Featured Image', 'builder-timeline'),
				'options' => array(
					'no' => __('No', 'builder-timeline'),
					'yes' => __('Yes', 'builder-timeline'),
				)
			),
			// array(
				// 'id' => 'ulink_feat_img_post_timeline',
				// 'type' => 'select',
				// 'label' => __('Unlink Featured Image', 'builder-timeline'),
				// 'options' => array(
					// 'no' => __('No', 'builder-timeline'),
					// 'yes' => __('Yes', 'builder-timeline'),
				// )
			// ),
			array(
				'id' => 'image_size_post_timeline',
				'type' => 'select',
				'label' => $ThemifyBuilder->is_img_php_disabled() ? __('Image Size', 'builder-timeline') : false,
				'hide' => $ThemifyBuilder->is_img_php_disabled() ? false : true,
				'options' => $image_sizes
			),
			array(
				'id' => 'img_width_post_timeline',
				'type' => 'text',
				'label' => __('Image Width', 'builder-timeline'),
				'class' => 'xsmall'
			),
			array(
				'id' => 'img_height_post_timeline',
				'type' => 'text',
				'label' => __('Image Height', 'builder-timeline'),
				'class' => 'xsmall'
			)
		);
	}

}