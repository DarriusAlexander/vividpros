<?php

defined( 'ABSPATH' ) or die;

$GLOBALS['processed_terms'] = array();
$GLOBALS['processed_posts'] = array();

require_once ABSPATH . 'wp-admin/includes/post.php';
require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Add an Import Action, this data is stored for processing after import is done.
 *
 * Each action is sent as an Ajax request and is handled by themify-ajax.php file
 */ 
function themify_add_import_action( $action = '', $data = array() ) {
	global $import_actions;

	if ( ! isset( $import_actions[ $action ] ) ) {
		$import_actions[ $action ] = array();
	}

	$import_actions[ $action ][] = $data;
}

function themify_import_post( $post ) {
	global $processed_posts, $processed_terms;

	if ( ! post_type_exists( $post['post_type'] ) ) {
		return;
	}

	/* Menu items don't have reliable post_title, skip the post_exists check */
	if( $post['post_type'] !== 'nav_menu_item' ) {
		$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
		if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
			$processed_posts[ intval( $post['ID'] ) ] = intval( $post_exists );
			return;
		}
	}

	if( $post['post_type'] == 'nav_menu_item' ) {
		if( ! isset( $post['tax_input']['nav_menu'] ) || ! term_exists( $post['tax_input']['nav_menu'], 'nav_menu' ) ) {
			return;
		}
		$_menu_item_type = $post['meta_input']['_menu_item_type'];
		$_menu_item_object_id = $post['meta_input']['_menu_item_object_id'];

		if ( 'taxonomy' == $_menu_item_type && isset( $processed_terms[ intval( $_menu_item_object_id ) ] ) ) {
			$post['meta_input']['_menu_item_object_id'] = $processed_terms[ intval( $_menu_item_object_id ) ];
		} else if ( 'post_type' == $_menu_item_type && isset( $processed_posts[ intval( $_menu_item_object_id ) ] ) ) {
			$post['meta_input']['_menu_item_object_id'] = $processed_posts[ intval( $_menu_item_object_id ) ];
		} else if ( 'custom' != $_menu_item_type ) {
			// associated object is missing or not imported yet, we'll retry later
			// $missing_menu_items[] = $item;
			return;
		}
	}

	$post_parent = ( $post['post_type'] == 'nav_menu_item' ) ? $post['meta_input']['_menu_item_menu_item_parent'] : (int) $post['post_parent'];
	$post['post_parent'] = 0;
	if ( $post_parent ) {
		// if we already know the parent, map it to the new local ID
		if ( isset( $processed_posts[ $post_parent ] ) ) {
			if( $post['post_type'] == 'nav_menu_item' ) {
				$post['meta_input']['_menu_item_menu_item_parent'] = $processed_posts[ $post_parent ];
			} else {
				$post['post_parent'] = $processed_posts[ $post_parent ];
			}
		}
	}

	/**
	 * for hierarchical taxonomies, IDs must be used so wp_set_post_terms can function properly
	 * convert term slugs to IDs for hierarchical taxonomies
	 */
	if( ! empty( $post['tax_input'] ) ) {
		foreach( $post['tax_input'] as $tax => $terms ) {
			if( is_taxonomy_hierarchical( $tax ) ) {
				$terms = explode( ', ', $terms );
				$post['tax_input'][ $tax ] = array_map( 'themify_get_term_id_by_slug', $terms, array_fill( 0, count( $terms ), $tax ) );
			}
		}
	}

	$post['post_author'] = (int) get_current_user_id();
	$post['post_status'] = 'publish';

	$old_id = $post['ID'];

	unset( $post['ID'] );
	$post_id = wp_insert_post( $post, true );
	if( is_wp_error( $post_id ) ) {
		return false;
	} else {
		$processed_posts[ $old_id ] = $post_id;

		return $post_id;
	}
}

function themify_get_placeholder_image() {
	static $placeholder_image = null;

	if( $placeholder_image == null ) {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;
		$upload = wp_upload_bits( $post['post_name'] . '.jpg', null, $wp_filesystem->get_contents( THEMIFY_DIR . '/img/image-placeholder.jpg' ) );

		if ( $info = wp_check_filetype( $upload['file'] ) )
			$post['post_mime_type'] = $info['type'];
		else
			return new WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'themify' ) );

		$post['guid'] = $upload['url'];
		$post_id = wp_insert_attachment( $post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		$placeholder_image = $post_id;
	}

	return $placeholder_image;
}

function themify_import_term( $term ) {
	global $processed_terms;

	if( $term_id = term_exists( $term['slug'], $term['taxonomy'] ) ) {
		if ( is_array( $term_id ) ) $term_id = $term_id['term_id'];
		if ( isset( $term['term_id'] ) )
			$processed_terms[ intval( $term['term_id'] ) ] = (int) $term_id;
		return (int) $term_id;
	}

	if ( empty( $term['parent'] ) ) {
		$parent = 0;
	} else {
		$parent = term_exists( $processed_terms[ intval( $term['parent'] ) ], $term['taxonomy'] );
		if ( is_array( $parent ) ) $parent = $parent['term_id'];
	}

	$id = wp_insert_term( $term['name'], $term['taxonomy'], array(
		'parent' => $parent,
		'slug' => $term['slug'],
		'description' => $term['description'],
	) );
	if ( ! is_wp_error( $id ) ) {
		if ( isset( $term['term_id'] ) ) {
			// success!
			$processed_terms[ intval($term['term_id']) ] = $id['term_id'];
			if ( isset( $term['thumbnail'] ) ) {
				themify_add_import_action( 'term_thumb', array(
					'id' => $id['term_id'],
					'thumb' => $term['thumbnail'],
				) );
			}
			return $term['term_id'];
		}
	}

	return false;
}

function themify_get_term_id_by_slug( $slug, $tax ) {
	$term = get_term_by( 'slug', $slug, $tax );
	if( $term ) {
		return $term->term_id;
	}

	return false;
}

function themify_undo_import_term( $term ) {
	$term_id = term_exists( $term['slug'], $term['taxonomy'] );
	if ( $term_id ) {
		if ( is_array( $term_id ) ) $term_id = $term_id['term_id'];

		if ( $term_thumbnail = get_term_meta( $term['term_id'], 'thumbnail_id', true ) ) {
			wp_delete_attachment( $term_thumbnail, true );
		}

		if ( isset( $term_id ) ) {
			wp_delete_term( $term_id, $term['taxonomy'] );
		}
	}
}

/**
 * Determine if a post exists based on title, content, and date
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $args array of database parameters to check
 * @return int Post ID if post exists, 0 otherwise.
 */
function themify_post_exists( $args = array() ) {
	global $wpdb;

	$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
	$db_args = array();

	foreach ( $args as $key => $value ) {
		$value = wp_unslash( sanitize_post_field( $key, $value, 0, 'db' ) );
		if( ! empty( $value ) ) {
			$query .= ' AND ' . $key . ' = %s';
			$db_args[] = $value;
		}
	}

	if ( !empty ( $args ) )
		return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );

	return 0;
}

function themify_undo_import_post( $post ) {
	if( $post['post_type'] == 'nav_menu_item' ) {
		$post_exists = themify_post_exists( array(
			'post_name' => $post['post_name'],
			'post_modified' => $post['post_date'],
			'post_type' => 'nav_menu_item',
		) );
	} else {
		$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
	}
	if( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
		/**
		 * check if the post has been modified, if so leave it be
		 *
		 * NOTE: posts are imported using wp_insert_post() which modifies post_modified field
		 * to be the same as post_date, hence to check if the post has been modified,
		 * the post_modified field is compared against post_date in the original post.
		 */
		if( $post['post_date'] == get_post_field( 'post_modified', $post_exists ) ) {
			// find and remove all post attachments
			$attachments = get_posts( array(
				'post_type' => 'attachment',
				'posts_per_page' => -1,
				'post_parent' => $post_exists,
			) );
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
					wp_delete_attachment( $attachment->ID, true );
				}
			}
			wp_delete_post( $post_exists, true ); // true: bypass trash
		}
	}
}

function themify_process_post_import( $post ) {
	if( ERASEDEMO ) {
		themify_undo_import_post( $post );
	} else {
		if ( $id = themify_import_post( $post ) ) {
			if ( defined( 'IMPORT_IMAGES' ) && ! IMPORT_IMAGES ) {
				/* if importing images is disabled and post is supposed to have a thumbnail, create a placeholder image instead */
				if ( isset( $post['thumb'] ) ) { // the post is supposed to have featured image
					$placeholder = themify_get_placeholder_image();
					if( ! is_wp_error( $placeholder ) ) {
						set_post_thumbnail( $id, $placeholder );
					}
				}
			} else {
				if ( isset( $post["thumb"] ) ) {
					themify_add_import_action( 'post_thumb', array(
						'id' => $id,
						'thumb' => $post["thumb"],
					) );
				}
				if ( isset( $post["gallery_shortcode"] ) ) {
					themify_add_import_action( 'gallery_field', array(
						'id' => $id,
						'fields' => $post["gallery_shortcode"],
					) );
				}
				if ( isset( $post["_product_image_gallery"] ) ) {
					themify_add_import_action( 'product_gallery', array(
						'id' => $id,
						'images' => $post["_product_image_gallery"],
					) );
				}
			}
		}
	}
}
$thumbs = array();
function themify_do_demo_import() {
global $import_actions;

	if ( isset( $GLOBALS["ThemifyBuilder_Data_Manager"] ) ) {
		remove_action( "save_post", array( $GLOBALS["ThemifyBuilder_Data_Manager"], "save_builder_text_only"), 10, 3 );
	}
$term = array (
  'term_id' => 58,
  'name' => 'Bedroom',
  'slug' => 'bedroom',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 59,
  'name' => 'Chair',
  'slug' => 'chair',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 60,
  'name' => 'Cupboard',
  'slug' => 'cupboard',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 61,
  'name' => 'furniture',
  'slug' => 'furniture',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 62,
  'name' => 'Interior',
  'slug' => 'interior',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 63,
  'name' => 'Office',
  'slug' => 'office',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 64,
  'name' => 'Plant',
  'slug' => 'plant',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 65,
  'name' => 'Table',
  'slug' => 'table',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 68,
  'name' => 'classic',
  'slug' => 'classic',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 71,
  'name' => 'fur',
  'slug' => 'fur',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 72,
  'name' => 'furniture',
  'slug' => 'furniture',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 73,
  'name' => 'Lighting',
  'slug' => 'lighting',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 74,
  'name' => 'modern',
  'slug' => 'modern',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 75,
  'name' => 'pillow',
  'slug' => 'pillow',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 76,
  'name' => 'pots',
  'slug' => 'pots',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 77,
  'name' => 'sofa',
  'slug' => 'sofa',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 78,
  'name' => 'vase',
  'slug' => 'vase',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 79,
  'name' => 'wallpaper',
  'slug' => 'wallpaper',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 99,
  'name' => 'Bed',
  'slug' => 'bed',
  'term_group' => 0,
  'taxonomy' => 'product_cat',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 100,
  'name' => 'Chair',
  'slug' => 'chair',
  'term_group' => 0,
  'taxonomy' => 'product_cat',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 101,
  'name' => 'Pillow',
  'slug' => 'pillow',
  'term_group' => 0,
  'taxonomy' => 'product_cat',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 102,
  'name' => 'Table',
  'slug' => 'table',
  'term_group' => 0,
  'taxonomy' => 'product_cat',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 80,
  'name' => 'Bench',
  'slug' => 'bench',
  'term_group' => 0,
  'taxonomy' => 'product_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 83,
  'name' => 'Chair',
  'slug' => 'chair',
  'term_group' => 0,
  'taxonomy' => 'product_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 84,
  'name' => 'Cotton',
  'slug' => 'cotton',
  'term_group' => 0,
  'taxonomy' => 'product_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 93,
  'name' => 'Sofa',
  'slug' => 'sofa',
  'term_group' => 0,
  'taxonomy' => 'product_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 94,
  'name' => 'Table',
  'slug' => 'table',
  'term_group' => 0,
  'taxonomy' => 'product_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 97,
  'name' => 'Wood',
  'slug' => 'wood',
  'term_group' => 0,
  'taxonomy' => 'product_tag',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 103,
  'name' => 'Main Navigation',
  'slug' => 'main-navigation',
  'term_group' => 0,
  'taxonomy' => 'nav_menu',
  'description' => '',
  'parent' => 0,
);

if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$post = array (
  'ID' => 105,
  'post_date' => '2016-10-05 05:35:57',
  'post_date_gmt' => '2016-10-05 05:35:57',
  'post_content' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.

inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.',
  'post_title' => 'Modern Classic Chairs',
  'post_excerpt' => '',
  'post_name' => 'modern-classic-chairs',
  'post_modified' => '2016-11-16 01:53:44',
  'post_modified_gmt' => '2016-11-16 01:53:44',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=105',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"8khj006\\"}],\\"styling\\":[],\\"element_id\\":\\"ww34077\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, interior',
    'post_tag' => 'classic',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/10/furniture-802031_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 99,
  'post_date' => '2016-10-04 05:29:36',
  'post_date_gmt' => '2016-10-04 05:29:36',
  'post_content' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.

inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.',
  'post_title' => 'Ethnic Modern Chair',
  'post_excerpt' => '',
  'post_name' => 'ethnic-modern-chair',
  'post_modified' => '2016-11-16 01:53:54',
  'post_modified_gmt' => '2016-11-16 01:53:54',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=99',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"o8pb245\\"}],\\"styling\\":[],\\"element_id\\":\\"yd1n904\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, interior',
    'post_tag' => 'wallpaper',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/10/table-1031148_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 115,
  'post_date' => '2016-10-02 05:41:51',
  'post_date_gmt' => '2016-10-02 05:41:51',
  'post_content' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias at vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis.

quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

Aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus.',
  'post_title' => 'Italian Relax Chair',
  'post_excerpt' => '',
  'post_name' => 'italian-relax-chair',
  'post_modified' => '2016-11-16 01:54:00',
  'post_modified_gmt' => '2016-11-16 01:54:00',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=115',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"vok5083\\"}],\\"styling\\":[],\\"element_id\\":\\"3okv978\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, interior',
    'post_tag' => 'classic, sofa',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/10/eames-chairs-670286_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 117,
  'post_date' => '2016-10-01 05:42:02',
  'post_date_gmt' => '2016-10-01 05:42:02',
  'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.

Vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi.',
  'post_title' => 'Simple Dining Table Set',
  'post_excerpt' => '',
  'post_name' => 'simple-dining-table-set',
  'post_modified' => '2016-11-16 01:57:51',
  'post_modified_gmt' => '2016-11-16 01:57:51',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=117',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"a38q089\\"}],\\"styling\\":[],\\"element_id\\":\\"410y809\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, table',
    'post_tag' => 'furniture',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/10/table-629772.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 110,
  'post_date' => '2016-09-29 05:36:34',
  'post_date_gmt' => '2016-09-29 05:36:34',
  'post_content' => 'Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus. Et quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus omnis.

At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.',
  'post_title' => 'Bedroom Design Interior',
  'post_excerpt' => '',
  'post_name' => 'bedroom-design-interior',
  'post_modified' => '2016-11-16 01:57:41',
  'post_modified_gmt' => '2016-11-16 01:57:41',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=110',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"lpdr088\\"}],\\"styling\\":[],\\"element_id\\":\\"93rl720\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'bedroom',
    'post_tag' => 'furniture, pillow',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/09/hotel-room-1447201_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 108,
  'post_date' => '2016-09-25 05:36:12',
  'post_date_gmt' => '2016-09-25 05:36:12',
  'post_content' => 'Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.

Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae.',
  'post_title' => 'Guide to Choose L Shaped Couch',
  'post_excerpt' => '',
  'post_name' => 'guide-choose-l-shaped-couch',
  'post_modified' => '2016-11-16 01:57:30',
  'post_modified_gmt' => '2016-11-16 01:57:30',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=108',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"spk6101\\"}],\\"styling\\":[],\\"element_id\\":\\"4dpg788\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, interior',
    'post_tag' => 'sofa',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/09/sofa-184555_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 102,
  'post_date' => '2016-09-11 05:31:31',
  'post_date_gmt' => '2016-09-11 05:31:31',
  'post_content' => 'Labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.

At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.',
  'post_title' => 'Simple Wood Bench',
  'post_excerpt' => '',
  'post_name' => 'simple-wood-bench',
  'post_modified' => '2016-11-16 01:57:21',
  'post_modified_gmt' => '2016-11-16 01:57:21',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=102',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"27ll703\\"}],\\"styling\\":[],\\"element_id\\":\\"gij8777\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair',
    'post_tag' => 'fur',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/09/bench-1544926_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 96,
  'post_date' => '2016-09-03 05:20:55',
  'post_date_gmt' => '2016-09-03 05:20:55',
  'post_content' => 'Labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.

At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.
<div id="themify_builder_content-740" class="themify_builder_content themify_builder_content-740 themify_builder themify_builder_front" data-postid="740"></div>',
  'post_title' => 'Modern Egyptian Interior',
  'post_excerpt' => '',
  'post_name' => 'modern-egyptian-interior',
  'post_modified' => '2016-11-16 02:00:42',
  'post_modified_gmt' => '2016-11-16 02:00:42',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=96',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"gzmu170\\"}],\\"styling\\":[],\\"element_id\\":\\"mjws200\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'interior',
    'post_tag' => 'vase',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/09/room-1336497_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 91,
  'post_date' => '2016-09-02 04:39:44',
  'post_date_gmt' => '2016-09-02 04:39:44',
  'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.

Magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.

Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt neque porro quisquam.',
  'post_title' => 'Wood Chair and Table',
  'post_excerpt' => '',
  'post_name' => 'wood-chair-table',
  'post_modified' => '2016-11-16 02:00:45',
  'post_modified_gmt' => '2016-11-16 02:00:45',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=91',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"s3nz880\\"}],\\"styling\\":[],\\"element_id\\":\\"d23u344\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, interior, table',
    'post_tag' => 'furniture',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/09/la-molina-225145_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 88,
  'post_date' => '2016-09-01 04:35:32',
  'post_date_gmt' => '2016-09-01 04:35:32',
  'post_content' => 'Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates.

Magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?”  repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis.',
  'post_title' => 'Middle Age Furniture and Interior Design Set',
  'post_excerpt' => '',
  'post_name' => 'middle-age-furniture-interior-design-set',
  'post_modified' => '2016-11-16 02:00:47',
  'post_modified_gmt' => '2016-11-16 02:00:47',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=88',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"c79c046\\"}],\\"styling\\":[],\\"element_id\\":\\"mh3y560\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, interior, table',
    'post_tag' => 'furniture',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/09/farmhouse-358286_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 82,
  'post_date' => '2016-08-29 04:32:44',
  'post_date_gmt' => '2016-08-29 04:32:44',
  'post_content' => 'Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.

<span id="more-117"></span>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat epellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus. iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.

Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus.',
  'post_title' => 'Home Office Furniture',
  'post_excerpt' => '',
  'post_name' => 'home-office-furniture',
  'post_modified' => '2016-11-16 02:04:03',
  'post_modified_gmt' => '2016-11-16 02:04:03',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=82',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"43wt303\\"}],\\"styling\\":[],\\"element_id\\":\\"7h61300\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'interior, office',
    'post_tag' => 'furniture',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/office-1078869_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 67,
  'post_date' => '2016-08-28 03:56:43',
  'post_date_gmt' => '2016-08-28 03:56:43',
  'post_content' => 'Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.

<span id="more-117"></span>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat epellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus. iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.

. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus.',
  'post_title' => 'Vase Plant Gardening',
  'post_excerpt' => '',
  'post_name' => 'vase-plant-gardening',
  'post_modified' => '2016-11-16 02:04:08',
  'post_modified_gmt' => '2016-11-16 02:04:08',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=67',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"pj6m500\\"}],\\"styling\\":[],\\"element_id\\":\\"w7tg606\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'interior, plant',
    'post_tag' => 'pots, vase',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/pots-716579_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 79,
  'post_date' => '2016-08-27 04:27:11',
  'post_date_gmt' => '2016-08-27 04:27:11',
  'post_content' => 'Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat epellendus.  Ton provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.

<span id="more-133"></span>Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate.',
  'post_title' => 'Stripe Pillow Cover',
  'post_excerpt' => '',
  'post_name' => 'stripe-pillow-cover',
  'post_modified' => '2016-11-16 02:04:12',
  'post_modified_gmt' => '2016-11-16 02:04:12',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=79',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"86jz966\\"}],\\"styling\\":[],\\"element_id\\":\\"ob68967\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'bedroom',
    'post_tag' => 'pillow',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/pillows-1031079_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 73,
  'post_date' => '2016-08-20 04:22:51',
  'post_date_gmt' => '2016-08-20 04:22:51',
  'post_content' => 'Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.

<span id="more-117"></span>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat epellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus. iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.

. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus.',
  'post_title' => 'Modern Living Room Furniture',
  'post_excerpt' => '',
  'post_name' => 'modern-living-room-furniture',
  'post_modified' => '2016-11-16 02:04:16',
  'post_modified_gmt' => '2016-11-16 02:04:16',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=73',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"b9yg001\\"}],\\"styling\\":[],\\"element_id\\":\\"skgj051\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'interior',
    'post_tag' => 'furniture',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/furniture-998265.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 70,
  'post_date' => '2016-08-15 04:03:21',
  'post_date_gmt' => '2016-08-15 04:03:21',
  'post_content' => 'Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat epellendus.  Ton provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.

<span id="more-133"></span>Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate.',
  'post_title' => 'Sofa Maker, Custom Order and Services',
  'post_excerpt' => '',
  'post_name' => 'sofa-maker-custom-order-services',
  'post_modified' => '2016-11-16 02:04:19',
  'post_modified_gmt' => '2016-11-16 02:04:19',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=70',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"aoql507\\"}],\\"styling\\":[],\\"element_id\\":\\"adcq330\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair',
    'post_tag' => 'sofa',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/artisan-895670.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 54,
  'post_date' => '2016-08-12 03:05:49',
  'post_date_gmt' => '2016-08-12 03:05:49',
  'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.',
  'post_title' => 'Retro Modern Bedroom Interior',
  'post_excerpt' => '',
  'post_name' => 'retro-modern-bedroom-interior',
  'post_modified' => '2016-11-16 02:10:05',
  'post_modified_gmt' => '2016-11-16 02:10:05',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=54',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"otaz080\\"}],\\"styling\\":[],\\"element_id\\":\\"b5wg000\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'bedroom, interior',
    'post_tag' => 'classic, furniture',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/house-753271.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 64,
  'post_date' => '2016-08-11 03:45:29',
  'post_date_gmt' => '2016-08-11 03:45:29',
  'post_content' => 'Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit.recusandae. Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga.

At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores.',
  'post_title' => 'Striped Sofa',
  'post_excerpt' => '',
  'post_name' => 'striped-sofa',
  'post_modified' => '2016-11-16 02:10:09',
  'post_modified_gmt' => '2016-11-16 02:10:09',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=64',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"avkf555\\"}],\\"styling\\":[],\\"element_id\\":\\"n19o259\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair',
    'post_tag' => 'sofa',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/couch-480486_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 61,
  'post_date' => '2016-08-07 03:36:38',
  'post_date_gmt' => '2016-08-07 03:36:38',
  'post_content' => 'Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus. Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate.

At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.',
  'post_title' => 'Perfect Combination',
  'post_excerpt' => '',
  'post_name' => 'perfect-combination',
  'post_modified' => '2016-11-16 02:10:19',
  'post_modified_gmt' => '2016-11-16 02:10:19',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=61',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"8t6m650\\"}],\\"styling\\":[],\\"element_id\\":\\"4brx545\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, cupboard, interior',
    'post_tag' => 'furniture',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/bedroom-1078887_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 57,
  'post_date' => '2016-08-06 03:35:32',
  'post_date_gmt' => '2016-08-06 03:35:32',
  'post_content' => 'Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit.
<span id="more-122"></span>
Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio.',
  'post_title' => 'Modern Lighting',
  'post_excerpt' => '',
  'post_name' => 'modern-lighting',
  'post_modified' => '2016-11-16 02:10:25',
  'post_modified_gmt' => '2016-11-16 02:10:25',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=57',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"irhd090\\"}],\\"styling\\":[],\\"element_id\\":\\"cs4r059\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'interior',
    'post_tag' => 'lighting',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/ceiling-lamp-335975_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 126,
  'post_date' => '2016-08-02 05:51:41',
  'post_date_gmt' => '2016-08-02 05:51:41',
  'post_content' => 'Ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

Laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit ess. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.',
  'post_title' => 'Yellow Corner Chair',
  'post_excerpt' => '',
  'post_name' => 'yellow-corner-chair',
  'post_modified' => '2016-11-16 02:10:30',
  'post_modified_gmt' => '2016-11-16 02:10:30',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?p=126',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"je88300\\"}],\\"styling\\":[],\\"element_id\\":\\"tons103\\"}]',
  ),
  'tax_input' => 
  array (
    'category' => 'chair, furniture, interior',
    'post_tag' => 'modern',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/08/chaise-1575490_1920.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 116,
  'post_date' => '2016-10-06 05:45:17',
  'post_date_gmt' => '2016-10-06 05:45:17',
  'post_content' => '',
  'post_title' => 'Blog',
  'post_excerpt' => '',
  'post_name' => 'blog',
  'post_modified' => '2017-05-19 19:31:22',
  'post_modified_gmt' => '2017-05-19 19:31:22',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?page_id=116',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'builder_switch_frontend' => '0',
  ),
  'tax_input' => 
  array (
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 47,
  'post_date' => '2016-10-06 02:08:51',
  'post_date_gmt' => '2016-10-06 02:08:51',
  'post_content' => '[woocommerce_cart]',
  'post_title' => 'Cart',
  'post_excerpt' => '',
  'post_name' => 'cart',
  'post_modified' => '2016-10-06 02:08:51',
  'post_modified_gmt' => '2016-10-06 02:08:51',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/cart/',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"iwaz330\\"}],\\"styling\\":[],\\"element_id\\":\\"2ddz162\\"}]',
  ),
  'tax_input' => 
  array (
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 48,
  'post_date' => '2016-10-06 02:08:51',
  'post_date_gmt' => '2016-10-06 02:08:51',
  'post_content' => '[woocommerce_checkout]',
  'post_title' => 'Checkout',
  'post_excerpt' => '',
  'post_name' => 'checkout',
  'post_modified' => '2016-10-06 02:08:51',
  'post_modified_gmt' => '2016-10-06 02:08:51',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/checkout/',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"2cde191\\"}],\\"styling\\":[],\\"element_id\\":\\"han5900\\"}]',
  ),
  'tax_input' => 
  array (
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 18,
  'post_date' => '2016-10-05 17:06:02',
  'post_date_gmt' => '2016-10-05 17:06:02',
  'post_content' => '<!--themify_builder_static--><h1>New Collections<br/>Now available in store</h1>
<h4>Become a subscriber</h4><h2>Get $50</h2>
<a href="https://themify.me/" > Subscribe Now </a>
<h2>Special Offer</h2><h4>featured furniture 2016</h4>
<h4>Design by Gosolo</h4><h2>Lucky Chair</h2>
<a href="https://themify.me/" > View Details </a>
<h1>Featured Products<br/></h1>
<figure> <a href="https://themify.me/demo/themes/ultra-ecommerce/product/simple-wood-chair/"><img src="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-16.jpg" alt="product-16" srcset="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-16.jpg 306w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-16-295x300.jpg 295w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-16-47x48.jpg 47w" sizes="(max-width: 306px) 100vw, 306px" /></a> </figure> 
 Sale! 
 <h3><a href="https://themify.me/demo/themes/ultra-ecommerce/product/simple-wood-chair/" title="Simple Wood Chair">Simple Wood Chair</a></h3> 
 <del>&#36;125.00</del> <ins>&#36;100.00</ins> <p><a href="/demo/themes/ultra-ecommerce/wp-admin/admin-ajax.php?add-to-cart=43" data-quantity="1" data-product_id="43" data-product_sku="" aria-label="Add &ldquo;Simple Wood Chair&rdquo; to your cart" rel="nofollow">Add to cart</a></p> [<a href="https://themify.me/demo/themes/ultra-ecommerce/wp-admin/post.php?post=43&#038;action=edit">Edit</a>] 
 
 <figure> <a href="https://themify.me/demo/themes/ultra-ecommerce/product/classic-european-table/"><img src="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-20.jpg" alt="product-20" srcset="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-20.jpg 306w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-20-300x240.jpg 300w" sizes="(max-width: 306px) 100vw, 306px" /></a> </figure> 
 Sale! 
 <h3><a href="https://themify.me/demo/themes/ultra-ecommerce/product/classic-european-table/" title="Classic European Table">Classic European Table</a></h3> 
 <del>&#36;150.00</del> <ins>&#36;90.00</ins> <p><a href="/demo/themes/ultra-ecommerce/wp-admin/admin-ajax.php?add-to-cart=41" data-quantity="1" data-product_id="41" data-product_sku="" aria-label="Add &ldquo;Classic European Table&rdquo; to your cart" rel="nofollow">Add to cart</a></p> [<a href="https://themify.me/demo/themes/ultra-ecommerce/wp-admin/post.php?post=41&#038;action=edit">Edit</a>] 
 
 <figure> <a href="https://themify.me/demo/themes/ultra-ecommerce/product/single-armchair-sofa/"><img src="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-18.jpg" alt="product-18" srcset="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-18.jpg 306w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-18-300x240.jpg 300w" sizes="(max-width: 306px) 100vw, 306px" /></a> </figure> 
 
 <h3><a href="https://themify.me/demo/themes/ultra-ecommerce/product/single-armchair-sofa/" title="Single Armchair Sofa">Single Armchair Sofa</a></h3> 
 <p><a href="https://themify.me/demo/themes/ultra-ecommerce/product/single-armchair-sofa/" data-quantity="1" data-product_id="39" data-product_sku="" aria-label="Select options for &ldquo;Single Armchair Sofa&rdquo;" rel="nofollow">Read more</a></p> [<a href="https://themify.me/demo/themes/ultra-ecommerce/wp-admin/post.php?post=39&#038;action=edit">Edit</a>] 
 
 <figure> <a href="https://themify.me/demo/themes/ultra-ecommerce/product/modern-wood-chair/"><img src="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-17.jpg" alt="product-17" srcset="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-17.jpg 306w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-17-300x240.jpg 300w" sizes="(max-width: 306px) 100vw, 306px" /></a> </figure> 
 
 <h3><a href="https://themify.me/demo/themes/ultra-ecommerce/product/modern-wood-chair/" title="Modern Wood Chair">Modern Wood Chair</a></h3> 
 &#36;150.00 <p><a href="/demo/themes/ultra-ecommerce/wp-admin/admin-ajax.php?add-to-cart=37" data-quantity="1" data-product_id="37" data-product_sku="" aria-label="Add &ldquo;Modern Wood Chair&rdquo; to your cart" rel="nofollow">Add to cart</a></p> [<a href="https://themify.me/demo/themes/ultra-ecommerce/wp-admin/post.php?post=37&#038;action=edit">Edit</a>] 
 
 <figure> <a href="https://themify.me/demo/themes/ultra-ecommerce/product/dining-chair/"><img src="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-5.jpg" alt="product-5" srcset="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-5.jpg 1400w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-5-300x200.jpg 300w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-5-768x512.jpg 768w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-5-600x400.jpg 600w" sizes="(max-width: 1400px) 100vw, 1400px" /></a> </figure> 
 
 <h3><a href="https://themify.me/demo/themes/ultra-ecommerce/product/dining-chair/" title="Dining Chair">Dining Chair</a></h3> 
 &#36;250.00 <p><a href="/demo/themes/ultra-ecommerce/wp-admin/admin-ajax.php?add-to-cart=35" data-quantity="1" data-product_id="35" data-product_sku="" aria-label="Add &ldquo;Dining Chair&rdquo; to your cart" rel="nofollow">Add to cart</a></p> [<a href="https://themify.me/demo/themes/ultra-ecommerce/wp-admin/post.php?post=35&#038;action=edit">Edit</a>] 
 
 <figure> <a href="https://themify.me/demo/themes/ultra-ecommerce/product/blue-bed-pillow/"><img src="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-15.jpg" alt="product-15" srcset="https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-15.jpg 306w, https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-15-300x274.jpg 300w" sizes="(max-width: 306px) 100vw, 306px" /></a> </figure> 
 
 <h3><a href="https://themify.me/demo/themes/ultra-ecommerce/product/blue-bed-pillow/" title="Blue Bed Pillow">Blue Bed Pillow</a></h3> 
 &#36;50.00 <p><a href="/demo/themes/ultra-ecommerce/wp-admin/admin-ajax.php?add-to-cart=33" data-quantity="1" data-product_id="33" data-product_sku="" aria-label="Add &ldquo;Blue Bed Pillow&rdquo; to your cart" rel="nofollow">Add to cart</a></p> [<a href="https://themify.me/demo/themes/ultra-ecommerce/wp-admin/post.php?post=33&#038;action=edit">Edit</a>]

<img src="https://themify.me/demo/themes/ultra-ecommerce/files/2016/10/sale.png" width="482" height="212" alt="" />
<p>Lorem ipsum dolor sit amet, consectetur adipisicingsed do eiusmod tempor incididunt ut labore et dolor.</p>
<a href="https://themify.me" > Shop Now </a>
<h1>Must-Have Items</h1><p>Sriracha godard messenger bag, beard meditation dreamcatcher<br /> forage etsy next level semiotics.</p>
<a href="https://themify.me/" > Shop Sales Item </a>
<h3>We Accept</h3>
<img src="https://themify.me/demo/themes/ultra-ecommerce/files/2019/04/credit-card-299x41.png" width="299" alt="credit-card" />
<h3 style="margin: 0; margin-top: 15px; padding-right: 29px;">Subscribe</h3>[mc4wp_form id="30"]<!--/themify_builder_static-->',
  'post_title' => 'Home',
  'post_excerpt' => '',
  'post_name' => 'home',
  'post_modified' => '2019-07-07 01:14:00',
  'post_modified_gmt' => '2019-07-07 01:14:00',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?page_id=18',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'content_width' => 'full_width',
    'hide_page_title' => 'yes',
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"element_id\\":\\"167c406\\",\\"cols\\":[{\\"element_id\\":\\"77d07f6\\",\\"grid_class\\":\\"col-full\\",\\"modules\\":[{\\"mod_name\\":\\"fancy-heading\\",\\"element_id\\":\\"c669748\\",\\"mod_settings\\":{\\"sub_heading\\":\\"Now available in store\\",\\"heading\\":\\"New Collections\\",\\"heading_tag\\":\\"h1\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\",\\"text_alignment\\":\\"themify-text-center\\"}}]}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/top-feature-bg-1400x631.jpg\\",\\"background_repeat\\":\\"fullcover\\",\\"background_position\\":\\"center-bottom\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"15\\",\\"padding_top_unit\\":\\"%\\",\\"padding_bottom\\":\\"15\\",\\"padding_bottom_unit\\":\\"%\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\"}},{\\"element_id\\":\\"6260f0a\\",\\"cols\\":[{\\"element_id\\":\\"e24510a\\",\\"grid_class\\":\\"col3-1\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"be3dbf3\\",\\"mod_settings\\":{\\"content_text\\":\\"<h4>Become a subscriber<\\\\/h4><h2>Get $50<\\\\/h2>\\",\\"text_align\\":\\"center\\",\\"link_color\\":\\"ffffff_1.00\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}},{\\"mod_name\\":\\"buttons\\",\\"element_id\\":\\"25d1dcf\\",\\"mod_settings\\":{\\"buttons_size\\":\\"large\\",\\"buttons_style\\":\\"squared\\",\\"content_button\\":[{\\"label\\":\\"Subscribe Now\\",\\"link\\":\\"https:\\\\/\\\\/themify.me\\\\/\\",\\"link_options\\":\\"regular\\",\\"button_color_bg\\":\\"yellow\\"}],\\"text_align\\":\\"center\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"button_background_color\\":\\"e4c272\\",\\"link_color\\":\\"242847\\",\\"padding_top_link\\":\\"15\\",\\"padding_bottom_link\\":\\"15\\",\\"link_checkbox_margin_apply_all\\":\\"margin\\",\\"link_checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/become-subscriber.jpg\\",\\"background_repeat\\":\\"fullcover\\",\\"background_position\\":\\"center-center\\",\\"cover_color-type\\":\\"color\\",\\"cover_color\\":\\"242847_0.8\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"font_color\\":\\"ffffff_1.00\\",\\"padding_top\\":\\"150\\",\\"padding_bottom\\":\\"150\\",\\"checkbox_border_apply_all\\":\\"border\\"}},{\\"element_id\\":\\"6d4bcfe\\",\\"grid_class\\":\\"col3-1\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"4bece00\\",\\"mod_settings\\":{\\"content_text\\":\\"<h2>Special Offer<\\\\/h2><h4>featured furniture 2016<\\\\/h4>\\",\\"text_align\\":\\"center\\",\\"link_color\\":\\"ffffff_1.00\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/chair-bg.jpg\\",\\"background_repeat\\":\\"fullcover\\",\\"background_position\\":\\"center-center\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"150\\",\\"padding_bottom\\":\\"150\\",\\"checkbox_border_apply_all\\":\\"border\\"}},{\\"element_id\\":\\"66b87c8\\",\\"grid_class\\":\\"col3-1\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"eaee1f8\\",\\"mod_settings\\":{\\"content_text\\":\\"<h4>Design by Gosolo<\\\\/h4><h2>Lucky Chair<\\\\/h2>\\",\\"text_align\\":\\"center\\",\\"link_color\\":\\"ffffff_1.00\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}},{\\"mod_name\\":\\"buttons\\",\\"element_id\\":\\"efde34c\\",\\"mod_settings\\":{\\"buttons_size\\":\\"large\\",\\"buttons_style\\":\\"squared\\",\\"content_button\\":[{\\"label\\":\\"View Details\\",\\"link\\":\\"https:\\\\/\\\\/themify.me\\\\/\\",\\"link_options\\":\\"regular\\",\\"button_color_bg\\":\\"yellow\\"}],\\"text_align\\":\\"center\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"button_background_color\\":\\"e4c272\\",\\"link_color\\":\\"242847\\",\\"padding_top_link\\":\\"15\\",\\"padding_bottom_link\\":\\"15\\",\\"checkbox_link_padding_apply_all\\":\\"padding\\",\\"link_checkbox_margin_apply_all\\":\\"margin\\",\\"link_checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/lucky-chair.jpg\\",\\"background_repeat\\":\\"fullcover\\",\\"background_position\\":\\"center-center\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"150\\",\\"padding_bottom\\":\\"150\\",\\"checkbox_border_apply_all\\":\\"border\\"}}],\\"gutter\\":\\"gutter-none\\",\\"styling\\":{\\"row_width\\":\\"fullwidth-content\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\"}},{\\"element_id\\":\\"371d708\\",\\"cols\\":[{\\"element_id\\":\\"fd93d1f\\",\\"grid_class\\":\\"col-full\\",\\"modules\\":[{\\"mod_name\\":\\"fancy-heading\\",\\"element_id\\":\\"0384b42\\",\\"mod_settings\\":{\\"heading\\":\\"Featured Products\\",\\"heading_tag\\":\\"h1\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"margin_bottom\\":\\"50\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\",\\"text_alignment\\":\\"themify-text-center\\"}},{\\"mod_name\\":\\"products\\",\\"element_id\\":\\"ec999a2\\",\\"mod_settings\\":{\\"query_products\\":\\"all\\",\\"category_products\\":\\"0|single\\",\\"hide_child_products\\":\\"no\\",\\"hide_free_products\\":\\"no\\",\\"post_per_page_products\\":\\"6\\",\\"orderby_products\\":\\"date\\",\\"order_products\\":\\"desc\\",\\"template_products\\":\\"list\\",\\"layout_products\\":\\"grid3\\",\\"layout_slider\\":\\"slider-default\\",\\"visible_opt_slider\\":\\"1\\",\\"auto_scroll_opt_slider\\":\\"4\\",\\"scroll_opt_slider\\":\\"1\\",\\"speed_opt_slider\\":\\"normal\\",\\"effect_slider\\":\\"scroll\\",\\"pause_on_hover_slider\\":\\"resume\\",\\"wrap_slider\\":\\"yes\\",\\"show_nav_slider\\":\\"yes\\",\\"show_arrow_slider\\":\\"yes\\",\\"description_products\\":\\"none\\",\\"hide_feat_img_products\\":\\"no\\",\\"unlink_feat_img_products\\":\\"no\\",\\"hide_post_title_products\\":\\"no\\",\\"unlink_post_title_products\\":\\"no\\",\\"hide_price_products\\":\\"no\\",\\"hide_add_to_cart_products\\":\\"no\\",\\"hide_rating_products\\":\\"no\\",\\"hide_sales_badge\\":\\"no\\",\\"hide_page_nav_products\\":\\"yes\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\"}}]}],\\"styling\\":{\\"background_slider_size\\":\\"large\\",\\"background_slider_mode\\":\\"fullcover\\",\\"background_slider_speed\\":\\"2000\\",\\"background_repeat\\":\\"repeat\\",\\"background_attachment\\":\\"scroll\\",\\"background_position\\":\\"center-center\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"background_repeat_inner\\":\\"repeat\\",\\"background_attachment_inner\\":\\"scroll\\",\\"background_position_inner\\":\\"center-center\\",\\"checkbox_padding_inner_apply_all\\":\\"1\\",\\"border_inner-type\\":\\"top\\",\\"top-frame_type\\":\\"top-presets\\",\\"bottom-frame_type\\":\\"bottom-presets\\",\\"left-frame_type\\":\\"left-presets\\",\\"right-frame_type\\":\\"right-presets\\",\\"padding_top\\":\\"6\\",\\"padding_top_unit\\":\\"%\\",\\"border-type\\":\\"top\\",\\"breakpoint_mobile\\":{\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"100\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\"},\\"stick_at_position\\":\\"top\\"}},{\\"element_id\\":\\"f8de695\\",\\"cols\\":[{\\"element_id\\":\\"8c161ec\\",\\"grid_class\\":\\"col4-2\\"},{\\"element_id\\":\\"f156512\\",\\"grid_class\\":\\"col4-2\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"8228c7f\\",\\"mod_settings\\":{\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}},{\\"mod_name\\":\\"image\\",\\"element_id\\":\\"6b95b43\\",\\"mod_settings\\":{\\"style_image\\":\\"image-center\\",\\"url_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/sale.png\\",\\"width_image\\":\\"482\\",\\"height_image\\":\\"212\\",\\"param_image\\":\\"regular\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"margin_bottom\\":\\"50\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}},{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"b91edea\\",\\"mod_settings\\":{\\"content_text\\":\\"<p>Lorem ipsum dolor sit amet, consectetur adipisicingsed do eiusmod tempor incididunt ut labore et dolor.<\\\\/p>\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"margin_bottom\\":\\"35\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}},{\\"mod_name\\":\\"buttons\\",\\"element_id\\":\\"c36ae42\\",\\"mod_settings\\":{\\"buttons_size\\":\\"large\\",\\"buttons_style\\":\\"squared\\",\\"content_button\\":[{\\"label\\":\\"Shop Now\\",\\"link\\":\\"https:\\\\/\\\\/themify.me\\",\\"link_options\\":\\"regular\\"}],\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"button_background_color\\":\\"242847\\",\\"link_color\\":\\"ffffff_1.00\\",\\"padding_top_link\\":\\"15\\",\\"padding_bottom_link\\":\\"15\\",\\"link_checkbox_margin_apply_all\\":\\"margin\\",\\"link_checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}}],\\"styling\\":{\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"font_color\\":\\"242847\\",\\"text_align\\":\\"center\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_border_apply_all\\":\\"border\\"}}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/bg-sale-1400x608-1-1400x608.jpg\\",\\"background_repeat\\":\\"best-fit-image\\",\\"background_position\\":\\"center-bottom\\",\\"background_color\\":\\"abcfcb_1.00\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"10\\",\\"padding_top_unit\\":\\"%\\",\\"padding_bottom\\":\\"7\\",\\"padding_bottom_unit\\":\\"%\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"breakpoint_mobile\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2019\\\\/01\\\\/bg-sale-1400x608-1.jpg\\",\\"background_repeat\\":\\"best-fit-image\\",\\"background_attachment\\":\\"scroll\\",\\"background_position\\":\\"center-bottom\\",\\"background_color\\":\\"abcfcb\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"background_repeat_inner\\":\\"repeat\\",\\"background_attachment_inner\\":\\"scroll\\",\\"background_position_inner\\":\\"center-center\\",\\"checkbox_padding_inner_apply_all\\":\\"1\\",\\"border_inner-type\\":\\"top\\",\\"top-frame_type\\":\\"top-presets\\",\\"bottom-frame_type\\":\\"bottom-presets\\",\\"left-frame_type\\":\\"left-presets\\",\\"right-frame_type\\":\\"right-presets\\",\\"padding_top\\":\\"15\\",\\"padding_top_unit\\":\\"%\\",\\"padding_bottom\\":\\"75\\",\\"padding_bottom_unit\\":\\"%\\",\\"border-type\\":\\"top\\"},\\"stick_at_position\\":\\"top\\"}},{\\"element_id\\":\\"cbee4a0\\",\\"cols\\":[{\\"element_id\\":\\"ce9f876\\",\\"grid_class\\":\\"col-full\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"465a129\\",\\"mod_settings\\":{\\"content_text\\":\\"<h1>Must-Have Items<\\\\/h1><p>Sriracha godard messenger bag, beard meditation dreamcatcher<br \\\\/> forage etsy next level semiotics.<\\\\/p>\\",\\"text_align\\":\\"center\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}},{\\"mod_name\\":\\"buttons\\",\\"element_id\\":\\"2354c75\\",\\"mod_settings\\":{\\"buttons_size\\":\\"large\\",\\"buttons_style\\":\\"squared\\",\\"content_button\\":[{\\"label\\":\\"Shop Sales Item\\",\\"link\\":\\"https:\\\\/\\\\/themify.me\\\\/\\",\\"link_options\\":\\"regular\\"}],\\"text_align\\":\\"center\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"button_background_color\\":\\"e4c272\\",\\"link_color\\":\\"242847\\",\\"padding_top_link\\":\\"15\\",\\"padding_bottom_link\\":\\"15\\",\\"checkbox_link_padding_apply_all\\":\\"padding\\",\\"link_checkbox_margin_apply_all\\":\\"margin\\",\\"link_checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}}],\\"styling\\":{\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"font_color\\":\\"242847\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_border_apply_all\\":\\"border\\"}}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/sale-text-bg-4-1400x567.jpg\\",\\"background_repeat\\":\\"best-fit-image\\",\\"background_position\\":\\"center-center\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"7\\",\\"padding_top_unit\\":\\"%\\",\\"padding_bottom\\":\\"7\\",\\"padding_bottom_unit\\":\\"%\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"breakpoint_mobile\\":{\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"100\\",\\"padding_bottom\\":\\"100\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\"}}},{\\"element_id\\":\\"350a3a0\\",\\"cols\\":[{\\"element_id\\":\\"ab9e0d9\\",\\"grid_class\\":\\"col4-2\\",\\"modules\\":[{\\"element_id\\":\\"c4c9a66\\",\\"cols\\":[{\\"element_id\\":\\"b7a0717\\",\\"grid_class\\":\\"col2-1\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"a3b9125\\",\\"mod_settings\\":{\\"background_repeat\\":\\"repeat\\",\\"background_position\\":\\"left-top\\",\\"font_color_type\\":\\"font_color_solid\\",\\"font_color\\":\\"ffffff\\",\\"text_align\\":\\"right\\",\\"border-type\\":\\"top\\",\\"font_color_type_h1\\":\\"font_color_h1_solid\\",\\"font_color_type_h2\\":\\"font_color_h2_solid\\",\\"font_color_type_h3\\":\\"font_color_h3_solid\\",\\"font_color_h3\\":\\"ffffff\\",\\"font_color_type_h4\\":\\"font_color_h4_solid\\",\\"font_color_type_h5\\":\\"font_color_h5_solid\\",\\"font_color_type_h6\\":\\"font_color_h6_solid\\",\\"checkbox_dropcap_padding_apply_all\\":\\"1\\",\\"checkbox_dropcap_margin_apply_all\\":\\"1\\",\\"dropcap_border-type\\":\\"top\\",\\"content_text\\":\\"<h3>We Accept<\\\\/h3>\\",\\"add_css_text\\":\\"tf-subscribe-form\\",\\"stick_at_position\\":\\"top\\"}}]},{\\"element_id\\":\\"c6f2c36\\",\\"grid_class\\":\\"col2-1\\",\\"modules\\":[{\\"mod_name\\":\\"image\\",\\"element_id\\":\\"d539774\\",\\"mod_settings\\":{\\"background_repeat\\":\\"repeat\\",\\"background_position\\":\\"left-top\\",\\"font_color_type\\":\\"font_color_solid\\",\\"border-type\\":\\"top\\",\\"i_t_b-type\\":\\"top\\",\\"style_image\\":\\"image-top\\",\\"url_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2019\\\\/04\\\\/credit-card.png\\",\\"width_image\\":\\"299\\",\\"param_image\\":\\"regular\\",\\"stick_at_position\\":\\"top\\"}}]}]}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/card.png\\",\\"background_repeat\\":\\"repeat-none\\",\\"background_position\\":\\"left-bottom\\",\\"background_color\\":\\"414355_1.00\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"66\\",\\"padding_right\\":\\"61\\",\\"padding_bottom\\":\\"47\\",\\"padding_left\\":\\"61\\",\\"checkbox_border_apply_all\\":\\"border\\"}},{\\"element_id\\":\\"c16e339\\",\\"grid_class\\":\\"col4-2\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"element_id\\":\\"b84185b\\",\\"mod_settings\\":{\\"content_text\\":\\"<h3 style=\\\\\\\\\\\\\\"margin: 0; margin-top: 15px; padding-right: 29px;\\\\\\\\\\\\\\">Subscribe<\\\\/h3>[mc4wp_form id=\\\\\\\\\\\\\\"30\\\\\\\\\\\\\\"]\\",\\"add_css_text\\":\\"tf-subscribe-form\\",\\"padding_left\\":\\"45\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile_hide\\":\\"hide\\"}}],\\"styling\\":{\\"background_image\\":\\"https://themify.me/demo/themes/ultra-ecommerce\\\\/files\\\\/2016\\\\/10\\\\/mail.png\\",\\"background_repeat\\":\\"repeat-none\\",\\"background_position\\":\\"left-bottom\\",\\"background_color\\":\\"dddde2_1.00\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"padding_top\\":\\"54\\",\\"padding_right\\":\\"46\\",\\"padding_bottom\\":\\"47\\",\\"checkbox_border_apply_all\\":\\"border\\"}}],\\"gutter\\":\\"gutter-none\\",\\"styling\\":{\\"row_width\\":\\"fullwidth-content\\",\\"cover_color-type\\":\\"color\\",\\"cover_color_hover-type\\":\\"hover_color\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_border_apply_all\\":\\"border\\"}}]',
  ),
  'tax_input' => 
  array (
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 49,
  'post_date' => '2016-10-06 02:08:51',
  'post_date_gmt' => '2016-10-06 02:08:51',
  'post_content' => '[woocommerce_my_account]',
  'post_title' => 'My Account',
  'post_excerpt' => '',
  'post_name' => 'my-account',
  'post_modified' => '2016-10-06 02:08:51',
  'post_modified_gmt' => '2016-10-06 02:08:51',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/my-account/',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 46,
  'post_date' => '2016-10-06 02:08:51',
  'post_date_gmt' => '2016-10-06 02:08:51',
  'post_content' => '',
  'post_title' => 'Shop',
  'post_excerpt' => '',
  'post_name' => 'shop',
  'post_modified' => '2016-10-06 02:08:51',
  'post_modified_gmt' => '2016-10-06 02:08:51',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/shop/',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1 sidebar-left',
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"nlf8020\\"}],\\"styling\\":[],\\"element_id\\":\\"hfss204\\"}]',
  ),
  'tax_input' => 
  array (
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 43,
  'post_date' => '2016-10-03 17:59:23',
  'post_date_gmt' => '2016-10-03 17:59:23',
  'post_content' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.',
  'post_title' => 'Simple Wood Chair',
  'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
  'post_name' => 'simple-wood-chair',
  'post_modified' => '2019-07-11 05:19:17',
  'post_modified_gmt' => '2019-07-11 05:19:17',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=43',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-16.jpg',
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '358',
    'total_sales' => '4',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'yes',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '125',
    '_sale_price' => '100',
    '_price' => '100',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '3.6.5',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"wxrj195\\"}],\\"styling\\":[],\\"element_id\\":\\"gsm3300\\"}]',
    '_edit_lock' => '1550887241:90',
    '_edit_last' => '115',
    '_tax_status' => 'taxable',
    '_default_attributes' => 
    array (
    ),
    '_download_limit' => '-1',
    '_download_expiry' => '-1',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_visibility' => 'featured',
    'product_cat' => 'chair',
    'product_tag' => 'bench, wood',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-16.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 41,
  'post_date' => '2016-09-30 17:57:35',
  'post_date_gmt' => '2016-09-30 17:57:35',
  'post_content' => 'Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.',
  'post_title' => 'Classic European Table',
  'post_excerpt' => 'Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.',
  'post_name' => 'classic-european-table',
  'post_modified' => '2019-06-23 00:28:43',
  'post_modified_gmt' => '2019-06-23 00:28:43',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=41',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '362',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-20.jpg',
    'total_sales' => '8',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '150',
    '_sale_price' => '90',
    '_price' => '90',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '3.6.2',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"91jr853\\"}],\\"styling\\":[],\\"element_id\\":\\"s3gp358\\"}]',
    '_edit_lock' => '1479246609:115',
    '_edit_last' => '115',
    '_tax_status' => 'taxable',
    '_default_attributes' => 
    array (
    ),
    '_download_limit' => '-1',
    '_download_expiry' => '-1',
    'themify_used_global_styles' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_visibility' => 'featured',
    'product_cat' => 'table',
    'product_tag' => 'table, wood',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-20.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 39,
  'post_date' => '2016-09-28 17:55:22',
  'post_date_gmt' => '2016-09-28 17:55:22',
  'post_content' => 'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.',
  'post_title' => 'Single  Armchair Sofa',
  'post_excerpt' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.',
  'post_name' => 'single-armchair-sofa',
  'post_modified' => '2016-11-15 21:51:35',
  'post_modified_gmt' => '2016-11-15 21:51:35',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=39',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
      'pa_color' => 
      array (
        'name' => 'pa_color',
        'value' => '',
        'position' => '0',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
      'pa_material' => 
      array (
        'name' => 'pa_material',
        'value' => '',
        'position' => '1',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
    ),
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"mjc7977\\"}],\\"styling\\":[],\\"element_id\\":\\"zn3q770\\"}]',
    '_min_variation_price' => NULL,
    '_max_variation_price' => NULL,
    '_min_price_variation_id' => NULL,
    '_max_price_variation_id' => NULL,
    '_min_variation_regular_price' => NULL,
    '_max_variation_regular_price' => NULL,
    '_min_regular_price_variation_id' => NULL,
    '_max_regular_price_variation_id' => NULL,
    '_min_variation_sale_price' => NULL,
    '_max_variation_sale_price' => NULL,
    '_min_sale_price_variation_id' => NULL,
    '_max_sale_price_variation_id' => NULL,
    '_edit_lock' => '1479246561:115',
    '_edit_last' => '115',
    '_thumbnail_id' => '360',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-18.jpg',
    '_price' => NULL,
    'themify_used_global_styles' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'product_type' => 'variable',
    'product_cat' => 'chair',
    'product_tag' => 'sofa',
    'pa_color' => 'black, brown, grey, purple',
    'pa_material' => 'cotton, leather, linen, wool',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-18.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 37,
  'post_date' => '2016-09-27 17:52:17',
  'post_date_gmt' => '2016-09-27 17:52:17',
  'post_content' => 'Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem.',
  'post_title' => 'Modern Wood Chair',
  'post_excerpt' => 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem.',
  'post_name' => 'modern-wood-chair',
  'post_modified' => '2016-11-15 21:49:31',
  'post_modified_gmt' => '2016-11-15 21:49:31',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=37',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '359',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-17.jpg',
    'total_sales' => '1',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '150',
    '_price' => '150',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_review_count' => '0',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"00g1810\\"}],\\"styling\\":[],\\"element_id\\":\\"621r020\\"}]',
    '_edit_lock' => '1479246529:115',
    '_edit_last' => '115',
    'themify_used_global_styles' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'chair',
    'product_tag' => 'chair, wood',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-17.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 35,
  'post_date' => '2016-09-25 17:49:36',
  'post_date_gmt' => '2016-09-25 17:49:36',
  'post_content' => 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.',
  'post_title' => 'Dining Chair',
  'post_excerpt' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus.',
  'post_name' => 'dining-chair',
  'post_modified' => '2016-11-15 21:49:00',
  'post_modified_gmt' => '2016-11-15 21:49:00',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=35',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '347',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-5.jpg',
    'total_sales' => '6',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'yes',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '250',
    '_price' => '250',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '3.0.0',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_edit_lock' => '1479246525:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"461x010\\"}],\\"styling\\":[],\\"element_id\\":\\"r6v4666\\"}]',
    '_edit_last' => '115',
    '_tax_status' => 'taxable',
    '_default_attributes' => 
    array (
    ),
    '_download_limit' => '-1',
    '_download_expiry' => '-1',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_visibility' => 'featured, rated-5',
    'product_cat' => 'chair',
    'product_tag' => 'chair',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-5.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 33,
  'post_date' => '2016-09-10 17:46:50',
  'post_date_gmt' => '2016-09-10 17:46:50',
  'post_content' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur.',
  'post_title' => 'Blue Bed Pillow',
  'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
  'post_name' => 'blue-bed-pillow',
  'post_modified' => '2016-11-15 21:48:35',
  'post_modified_gmt' => '2016-11-15 21:48:35',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=33',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '357',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-15.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '50',
    '_price' => '50',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '3.0.0',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"yx3t999\\"}],\\"styling\\":[],\\"element_id\\":\\"meu5000\\"}]',
    '_edit_lock' => '1479246652:115',
    '_edit_last' => '115',
    '_tax_status' => 'taxable',
    '_default_attributes' => 
    array (
    ),
    '_download_limit' => '-1',
    '_download_expiry' => '-1',
    'themify_used_global_styles' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_visibility' => 'featured, rated-5',
    'product_cat' => 'bed, pillow',
    'product_tag' => 'cotton',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-15.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 179,
  'post_date' => '2016-09-03 04:10:42',
  'post_date_gmt' => '2016-09-03 04:10:42',
  'post_content' => '<div>

Yeleniti atque corrupti olores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga.

</div>',
  'post_title' => 'Red Single Bed Sofa',
  'post_excerpt' => 'Teleniti atque corrupti ero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum  quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga.',
  'post_name' => 'red-single-bed-sofa',
  'post_modified' => '2016-11-15 22:01:11',
  'post_modified_gmt' => '2016-11-15 22:01:11',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=179',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_wc_review_count' => '0',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '356',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-14.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'yes',
    '_product_attributes' => 
    array (
      'pa_color' => 
      array (
        'name' => 'pa_color',
        'value' => '',
        'position' => '0',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
      'pa_material' => 
      array (
        'name' => 'pa_material',
        'value' => '',
        'position' => '1',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
    ),
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"af6y030\\"}],\\"styling\\":[],\\"element_id\\":\\"3r1g103\\"}]',
    '_min_variation_price' => NULL,
    '_max_variation_price' => NULL,
    '_min_price_variation_id' => NULL,
    '_max_price_variation_id' => NULL,
    '_min_variation_regular_price' => NULL,
    '_max_variation_regular_price' => NULL,
    '_min_regular_price_variation_id' => NULL,
    '_max_regular_price_variation_id' => NULL,
    '_min_variation_sale_price' => NULL,
    '_max_variation_sale_price' => NULL,
    '_min_sale_price_variation_id' => NULL,
    '_max_sale_price_variation_id' => NULL,
    '_edit_lock' => '1479247155:115',
    '_edit_last' => '115',
    '_price' => NULL,
  ),
  'tax_input' => 
  array (
    'product_type' => 'variable',
    'product_cat' => 'bed, chair',
    'product_tag' => 'sofa',
    'pa_color' => 'black, brown, grey, purple',
    'pa_material' => 'cotton, leather, linen, wool',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-14.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 177,
  'post_date' => '2016-09-02 04:06:27',
  'post_date_gmt' => '2016-09-02 04:06:27',
  'post_content' => '<div>

Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam.

</div>',
  'post_title' => 'Modern Junction Desk',
  'post_excerpt' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit.',
  'post_name' => 'modern-junction-desk',
  'post_modified' => '2016-11-15 22:00:53',
  'post_modified_gmt' => '2016-11-15 22:00:53',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=177',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '355',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-13.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '250',
    '_price' => '250',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_edit_lock' => '1479247152:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"zc4v110\\"}],\\"styling\\":[],\\"element_id\\":\\"7760000\\"}]',
    '_edit_last' => '115',
    '_wc_review_count' => '0',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'table',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-13.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 175,
  'post_date' => '2016-09-02 04:04:40',
  'post_date_gmt' => '2016-09-02 04:04:40',
  'post_content' => '<div>

Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.

</div>',
  'post_title' => 'Orange Sofa',
  'post_excerpt' => 'Molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.',
  'post_name' => 'orange-sofa',
  'post_modified' => '2016-11-15 21:59:16',
  'post_modified_gmt' => '2016-11-15 21:59:16',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=175',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '354',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-12.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
      'pa_color' => 
      array (
        'name' => 'pa_color',
        'value' => '',
        'position' => '0',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
      'pa_material' => 
      array (
        'name' => 'pa_material',
        'value' => '',
        'position' => '1',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
    ),
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"hjfy005\\"}],\\"styling\\":[],\\"element_id\\":\\"o4hx101\\"}]',
    '_min_variation_price' => NULL,
    '_max_variation_price' => NULL,
    '_min_price_variation_id' => NULL,
    '_max_price_variation_id' => NULL,
    '_min_variation_regular_price' => NULL,
    '_max_variation_regular_price' => NULL,
    '_min_regular_price_variation_id' => NULL,
    '_max_regular_price_variation_id' => NULL,
    '_min_variation_sale_price' => NULL,
    '_max_variation_sale_price' => NULL,
    '_min_sale_price_variation_id' => NULL,
    '_max_sale_price_variation_id' => NULL,
    '_edit_lock' => '1479247151:115',
    '_edit_last' => '115',
    '_price' => NULL,
  ),
  'tax_input' => 
  array (
    'product_type' => 'variable',
    'product_cat' => 'chair',
    'pa_color' => 'black, brown, grey, purple',
    'pa_material' => 'cotton, leather, linen, wool',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-12.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 173,
  'post_date' => '2016-09-01 04:01:30',
  'post_date_gmt' => '2016-09-01 04:01:30',
  'post_content' => '<div>

Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

</div>',
  'post_title' => 'Aaddison Wood Table',
  'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
  'post_name' => 'aaddison-wood-table',
  'post_modified' => '2016-11-15 21:58:33',
  'post_modified_gmt' => '2016-11-15 21:58:33',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=173',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '353',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-11.jpg',
    'total_sales' => '1',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '200',
    '_price' => '200',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_edit_lock' => '1479247149:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"4jv9055\\"}],\\"styling\\":[],\\"element_id\\":\\"fmsp350\\"}]',
    '_edit_last' => '115',
    'themify_used_global_styles' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'table',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-11.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 171,
  'post_date' => '2016-08-30 03:59:01',
  'post_date_gmt' => '2016-08-30 03:59:01',
  'post_content' => '<div>

Ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt.

</div>',
  'post_title' => 'Lexington Office Table',
  'post_excerpt' => 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.',
  'post_name' => 'lexington-office-table',
  'post_modified' => '2016-11-15 21:58:13',
  'post_modified_gmt' => '2016-11-15 21:58:13',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=171',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_stock_status' => 'instock',
    '_visibility' => 'visible',
    '_product_version' => '2.6.4',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '352',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-10.jpg',
    'total_sales' => '2',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '150',
    '_price' => '150',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_edit_lock' => '1479247148:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"1jc3022\\"}],\\"styling\\":[],\\"element_id\\":\\"7zxl880\\"}]',
    '_edit_last' => '115',
    '_wc_review_count' => '0',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'table',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-10.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 166,
  'post_date' => '2016-08-27 03:52:25',
  'post_date_gmt' => '2016-08-27 03:52:25',
  'post_content' => '<div>

Omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum.

</div>',
  'post_title' => 'Cassette Pillow',
  'post_excerpt' => 'Similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum.',
  'post_name' => 'bird-cage-pillow-case',
  'post_modified' => '2016-11-15 21:57:53',
  'post_modified_gmt' => '2016-11-15 21:57:53',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=166',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '351',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-9.jpg',
    'total_sales' => '1',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'yes',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '25',
    '_price' => '25',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_edit_lock' => '1479247146:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"g9co562\\"}],\\"styling\\":[],\\"element_id\\":\\"fc7p030\\"}]',
    '_edit_last' => '115',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'bed, pillow',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-9.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 162,
  'post_date' => '2016-08-21 03:42:04',
  'post_date_gmt' => '2016-08-21 03:42:04',
  'post_content' => '<div>

Ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt.

</div>',
  'post_title' => 'Hi Lo Single Sofa',
  'post_excerpt' => 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.',
  'post_name' => 'hi-lo-single-sofa',
  'post_modified' => '2016-11-15 21:57:33',
  'post_modified_gmt' => '2016-11-15 21:57:33',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=162',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '349',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-7.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
      'pa_color' => 
      array (
        'name' => 'pa_color',
        'value' => '',
        'position' => '0',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
      'pa_material' => 
      array (
        'name' => 'pa_material',
        'value' => '',
        'position' => '1',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1,
      ),
    ),
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_min_variation_price' => NULL,
    '_max_variation_price' => NULL,
    '_min_price_variation_id' => NULL,
    '_max_price_variation_id' => NULL,
    '_min_variation_regular_price' => NULL,
    '_max_variation_regular_price' => NULL,
    '_min_regular_price_variation_id' => NULL,
    '_max_regular_price_variation_id' => NULL,
    '_min_variation_sale_price' => NULL,
    '_max_variation_sale_price' => NULL,
    '_min_sale_price_variation_id' => NULL,
    '_max_sale_price_variation_id' => NULL,
    '_default_attributes' => 
    array (
    ),
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"6n4s078\\"}],\\"styling\\":[],\\"element_id\\":\\"knie808\\"}]',
    '_edit_lock' => '1479247144:115',
    '_edit_last' => '115',
    '_price' => NULL,
    '_wc_review_count' => '0',
  ),
  'tax_input' => 
  array (
    'product_type' => 'variable',
    'product_cat' => 'chair',
    'pa_color' => 'black, brown, grey, purple',
    'pa_material' => 'cotton, leather, linen, wool',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-7.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 164,
  'post_date' => '2016-08-20 03:48:47',
  'post_date_gmt' => '2016-08-20 03:48:47',
  'post_content' => '<div>

Aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur.

</div>',
  'post_title' => 'Rustic coffee Table',
  'post_excerpt' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur.',
  'post_name' => 'rustic-coffee-table',
  'post_modified' => '2016-11-15 21:56:57',
  'post_modified_gmt' => '2016-11-15 21:56:57',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=164',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '350',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-8.jpg',
    'total_sales' => '1',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '170',
    '_price' => '170',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_edit_lock' => '1479247143:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"5191520\\"}],\\"styling\\":[],\\"element_id\\":\\"ljdh055\\"}]',
    '_edit_last' => '115',
    '_wc_review_count' => '0',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'table',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-8.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 160,
  'post_date' => '2016-08-15 03:35:33',
  'post_date_gmt' => '2016-08-15 03:35:33',
  'post_content' => '<div>

Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

</div>',
  'post_title' => 'Blue Leather Sofa',
  'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
  'post_name' => 'blue-leather-sofa',
  'post_modified' => '2016-11-15 21:56:36',
  'post_modified_gmt' => '2016-11-15 21:56:36',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=160',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'outofstock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '348',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-6.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"1j5h119\\"}],\\"styling\\":[],\\"element_id\\":\\"ex04003\\"}]',
    '_edit_lock' => '1479247140:115',
    '_edit_last' => '115',
    'themify_used_global_styles' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'product_type' => 'variable',
    'product_cat' => 'chair',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-6.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 156,
  'post_date' => '2016-08-12 03:29:15',
  'post_date_gmt' => '2016-08-12 03:29:15',
  'post_content' => '<div>

Magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur.

</div>',
  'post_title' => 'Round Table Landscape Ornament',
  'post_excerpt' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.',
  'post_name' => 'round-table-landscape-ornament',
  'post_modified' => '2016-11-15 21:55:54',
  'post_modified_gmt' => '2016-11-15 21:55:54',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=156',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '346',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-4.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '180',
    '_price' => '180',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_edit_lock' => '1479247138:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"rff6880\\"}],\\"styling\\":[],\\"element_id\\":\\"qqug404\\"}]',
    '_edit_last' => '115',
    '_wc_review_count' => '0',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'table',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-4.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 158,
  'post_date' => '2016-08-11 03:31:55',
  'post_date_gmt' => '2016-08-11 03:31:55',
  'post_content' => 'Voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est qui dolorem ipsum quia dolor sit amet consectetur adipisci velit.',
  'post_title' => 'Restaurant Chair',
  'post_excerpt' => 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.',
  'post_name' => 'restaurant-chair',
  'post_modified' => '2018-02-21 15:30:07',
  'post_modified_gmt' => '2018-02-21 15:30:07',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=158',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    '_thumbnail_id' => '363',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-21.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '400.44',
    '_price' => '400.44',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_stock' => NULL,
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '3.3.1',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_edit_lock' => '1519239837:151',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full\\",\\"element_id\\":\\"rwkx500\\"}],\\"element_id\\":\\"1s7m896\\"}]',
    '_edit_last' => '151',
    '_tax_status' => 'taxable',
    '_default_attributes' => 
    array (
    ),
    '_download_limit' => '-1',
    '_download_expiry' => '-1',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'chair',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-21.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 154,
  'post_date' => '2016-08-10 03:27:08',
  'post_date_gmt' => '2016-08-10 03:27:08',
  'post_content' => '<div>

Numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis.

</div>',
  'post_title' => 'Wood Relax Chair',
  'post_excerpt' => 'Voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.',
  'post_name' => 'wood-relax-chair',
  'post_modified' => '2016-11-15 21:55:10',
  'post_modified_gmt' => '2016-11-15 21:55:10',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=154',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '345',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-3.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'yes',
    '_product_attributes' => 
    array (
    ),
    '_regular_price' => '150',
    '_price' => '150',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_wc_review_count' => '0',
    '_edit_lock' => '1479247134:115',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"yha6009\\"}],\\"styling\\":[],\\"element_id\\":\\"qmtn064\\"}]',
    '_edit_last' => '115',
    'themify_used_global_styles' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_cat' => 'chair',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-3.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 152,
  'post_date' => '2016-08-08 03:24:04',
  'post_date_gmt' => '2016-08-08 03:24:04',
  'post_content' => '<div>

aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet.

</div>',
  'post_title' => 'Purple Sofa',
  'post_excerpt' => 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam.',
  'post_name' => 'purple-sofa',
  'post_modified' => '2016-11-15 21:54:39',
  'post_modified_gmt' => '2016-11-15 21:54:39',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=152',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '344',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-2.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_product_attributes' => 
    array (
      'color' => 
      array (
        'name' => 'Color',
        'value' => 'Purple, Brown, Grey, Black',
        'position' => '0',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 0,
      ),
      'material' => 
      array (
        'name' => 'Material',
        'value' => 'Leather, Cotton, Linen, Wool',
        'position' => '1',
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 0,
      ),
    ),
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '2.6.4',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"64eu777\\"}],\\"styling\\":[],\\"element_id\\":\\"8lky073\\"}]',
    '_min_variation_price' => NULL,
    '_max_variation_price' => NULL,
    '_min_price_variation_id' => NULL,
    '_max_price_variation_id' => NULL,
    '_min_variation_regular_price' => NULL,
    '_max_variation_regular_price' => NULL,
    '_min_regular_price_variation_id' => NULL,
    '_max_regular_price_variation_id' => NULL,
    '_min_variation_sale_price' => NULL,
    '_max_variation_sale_price' => NULL,
    '_min_sale_price_variation_id' => NULL,
    '_max_sale_price_variation_id' => NULL,
    '_edit_lock' => '1479247130:115',
    '_edit_last' => '115',
    '_price' => NULL,
    '_wc_review_count' => '0',
  ),
  'tax_input' => 
  array (
    'product_type' => 'variable',
    'product_cat' => 'chair',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-2.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 148,
  'post_date' => '2016-08-07 03:16:53',
  'post_date_gmt' => '2016-08-07 03:16:53',
  'post_content' => '<div>

Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.

</div>',
  'post_title' => 'Round Wood Glass Coffee Table',
  'post_excerpt' => 'Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.',
  'post_name' => 'round-wood-glass-coffee-table',
  'post_modified' => '2016-11-15 21:54:05',
  'post_modified_gmt' => '2016-11-15 21:54:05',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/?post_type=product&#038;p=148',
  'menu_order' => 0,
  'post_type' => 'product',
  'meta_input' => 
  array (
    '_product_attributes' => 
    array (
      'size' => 
      array (
        'name' => 'Size',
        'value' => '',
        'position' => '0',
        'is_visible' => 1,
        'is_variation' => 0,
        'is_taxonomy' => 0,
      ),
    ),
    '_regular_price' => '90',
    '_sale_price' => '80',
    '_price' => '80',
    '_manage_stock' => 'no',
    '_backorders' => 'no',
    '_upsell_ids' => 
    array (
    ),
    '_crosssell_ids' => 
    array (
    ),
    '_product_version' => '3.0.0',
    '_yoast_wpseo_content_score' => '30',
    '_wc_rating_count' => 
    array (
    ),
    '_wc_average_rating' => '0',
    '_visibility' => 'visible',
    '_stock_status' => 'instock',
    'builder_switch_frontend' => '0',
    '_thumbnail_id' => '343',
    'post_image' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-1.jpg',
    'total_sales' => '0',
    '_downloadable' => 'no',
    '_virtual' => 'no',
    '_featured' => 'no',
    '_wp_old_slug' => 'modern-minimalist-short-chair',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"gutter\\":\\"gutter-default\\",\\"equal_column_height\\":\\"\\",\\"column_alignment\\":\\"col_align_top\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"grid_width\\":\\"\\",\\"modules\\":[],\\"styling\\":[],\\"element_id\\":\\"8b5c000\\"}],\\"styling\\":[],\\"element_id\\":\\"vhoa200\\"}]',
    '_wc_review_count' => '0',
    '_edit_lock' => '1479246712:115',
    '_edit_last' => '115',
    '_tax_status' => 'taxable',
    '_default_attributes' => 
    array (
    ),
    '_download_limit' => '-1',
    '_download_expiry' => '-1',
  ),
  'tax_input' => 
  array (
    'product_type' => 'simple',
    'product_visibility' => 'featured, rated-5',
    'product_cat' => 'table',
  ),
  'thumb' => 'https://themify.me/demo/themes/ultra-ecommerce/files/2016/11/product-1.jpg',
);
themify_process_post_import( $post );


$post = array (
  'ID' => 392,
  'post_date' => '2016-11-15 21:36:48',
  'post_date_gmt' => '2016-11-15 21:36:48',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '392',
  'post_modified' => '2016-11-15 21:36:48',
  'post_modified_gmt' => '2016-11-15 21:36:48',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/2016/11/15/392/',
  'menu_order' => 1,
  'post_type' => 'nav_menu_item',
  'xfn' => '',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '0',
    '_menu_item_object_id' => '18',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-navigation',
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 391,
  'post_date' => '2016-11-15 21:36:47',
  'post_date_gmt' => '2016-11-15 21:36:47',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '391',
  'post_modified' => '2016-11-15 21:36:47',
  'post_modified_gmt' => '2016-11-15 21:36:47',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/2016/11/15/',
  'menu_order' => 2,
  'post_type' => 'nav_menu_item',
  'xfn' => '',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '0',
    '_menu_item_object_id' => '116',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-navigation',
  ),
);
themify_process_post_import( $post );


$post = array (
  'ID' => 393,
  'post_date' => '2016-11-15 21:36:48',
  'post_date_gmt' => '2016-11-15 21:36:48',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '393',
  'post_modified' => '2016-11-15 21:36:48',
  'post_modified_gmt' => '2016-11-15 21:36:48',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/ultra-ecommerce/2016/11/15/393/',
  'menu_order' => 3,
  'post_type' => 'nav_menu_item',
  'xfn' => '',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '0',
    '_menu_item_object_id' => '46',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-navigation',
  ),
);
themify_process_post_import( $post );



function themify_import_get_term_id_from_slug( $slug ) {
	$menu = get_term_by( "slug", $slug, "nav_menu" );
	return is_wp_error( $menu ) ? 0 : (int) $menu->term_id;
}

	$widgets = get_option( "widget_text" );
$widgets[1002] = array (
  'title' => '',
  'text' => '',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1003] = array (
  'title' => 'About',
  'text' => 'The Ultra theme is Themify\'s flagship theme. It\'s a WordPress designed to give you more control on the design of your theme. Built to work seamlessly with our drag & drop Builder plugin, it gives you the ability to customize the look and feel of your content. ',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1004] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1005] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1006] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1007] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1008] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1009] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1010] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1011] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1012] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1013] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1014] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1015] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1016] = array (
  'title' => 'Contact',
  'text' => '<b>Phone: </b> +1 916-875-2235<br>
<b>Fax:</b> +1 916-875-2235<br>
<b>Email:</b> info@domain.ltd <br> <br>
908 New Hampshire Avenue
Northwest #100, Washington, DC 
20037, United States',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1017] = array (
  'title' => 'Widget 1',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1018] = array (
  'title' => 'Widget 2',
  'text' => 'Display any widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1019] = array (
  'title' => 'Widget 3',
  'text' => 'For example, phone #: 123-333-4567',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1020] = array (
  'title' => 'About',
  'text' => 'The Ultra theme is Themify\'s flagship theme. It\'s a WordPress designed to give you more control on the design of your theme. Built to work seamlessly with our drag & drop Builder plugin, it gives you the ability to customize the look and feel of your content. ',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1021] = array (
  'title' => 'Widget 1',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1022] = array (
  'title' => 'Widget 2',
  'text' => 'Display any widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1023] = array (
  'title' => 'Widget 3',
  'text' => 'For example, phone #: 123-333-4567',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1024] = array (
  'title' => 'Widget 2',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1025] = array (
  'title' => 'Widget 3',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1026] = array (
  'title' => 'About Us',
  'text' => 'The eCommerce theme is Themify\'s flagship theme. It\'s a WordPress
designed to give you more control on the design of your theme. Built to work seamlessly with our drag & drop Builder plugin, it gives you the ability to customize the look and feel of your content.',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1027] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1028] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1029] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1030] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1031] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1032] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1033] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1034] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1035] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1036] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1037] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1038] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_nav_menu" );
$widgets[1039] = array (
);
update_option( "widget_nav_menu", $widgets );

$widgets = get_option( "widget_themify-feature-posts" );
$widgets[1040] = array (
  'title' => 'Recent Posts',
  'category' => '0',
  'show_count' => '3',
  'show_date' => 'on',
  'show_thumb' => 'on',
  'display' => 'none',
  'hide_title' => NULL,
  'thumb_width' => '50',
  'thumb_height' => '50',
  'excerpt_length' => '55',
  'orderby' => 'date',
  'order' => 'DESC',
);
update_option( "widget_themify-feature-posts", $widgets );

$widgets = get_option( "widget_themify-feature-posts" );
$widgets[1041] = array (
  'title' => 'Recent Posts',
  'category' => '0',
  'show_count' => '3',
  'show_date' => 'on',
  'show_thumb' => 'on',
  'display' => 'none',
  'hide_title' => NULL,
  'thumb_width' => '50',
  'thumb_height' => '50',
  'excerpt_length' => '55',
  'orderby' => 'date',
  'order' => 'DESC',
);
update_option( "widget_themify-feature-posts", $widgets );

$widgets = get_option( "widget_themify-feature-posts" );
$widgets[1042] = array (
  'title' => 'Recent Posts',
  'category' => '0',
  'show_count' => '2',
  'show_date' => 'on',
  'show_thumb' => 'on',
  'display' => 'none',
  'hide_title' => NULL,
  'thumb_width' => '50',
  'thumb_height' => '50',
  'excerpt_length' => '55',
  'orderby' => 'date',
  'order' => 'DESC',
);
update_option( "widget_themify-feature-posts", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1043] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1044] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1045] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1046] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1047] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1048] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1049] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-large',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1050] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1051] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '3',
  'hide_timestamp' => NULL,
  'show_follow' => 'on',
  'follow_text' => '→ Follow me',
  'include_retweets' => 'on',
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1052] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '2',
  'hide_timestamp' => NULL,
  'show_follow' => NULL,
  'follow_text' => '→ Follow me',
  'include_retweets' => NULL,
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1053] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '3',
  'hide_timestamp' => NULL,
  'show_follow' => 'on',
  'follow_text' => '→ Follow me',
  'include_retweets' => 'on',
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1054] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '2',
  'hide_timestamp' => NULL,
  'show_follow' => NULL,
  'follow_text' => '→ Follow me',
  'include_retweets' => NULL,
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1055] = array (
  'title' => 'Latest Tweets',
  'username' => '@themify',
  'show_count' => '2',
  'hide_timestamp' => NULL,
  'show_follow' => 'on',
  'follow_text' => '→ Follow me',
  'include_retweets' => 'on',
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_woocommerce_products" );
$widgets[1056] = array (
  'title' => 'Featured Products',
  'number' => 3,
  'show' => 'featured',
  'orderby' => 'date',
  'order' => 'desc',
  'hide_free' => 0,
  'show_hidden' => 0,
);
update_option( "widget_woocommerce_products", $widgets );

$widgets = get_option( "widget_nav_menu" );
$widgets[1057] = array (
);
update_option( "widget_nav_menu", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1058] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1059] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1060] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1061] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1062] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1063] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1064] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1065] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1066] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1067] = array (
  'title' => 'Widget 1',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1068] = array (
  'title' => 'Widget 2',
  'text' => 'Display any widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1069] = array (
  'title' => 'Widget 3',
  'text' => 'For example, phone #: 123-333-4567',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1070] = array (
  'title' => 'About',
  'text' => 'The Ultra theme is Themify\'s flagship theme. It\'s a WordPress designed to give you more control on the design of your theme. Built to work seamlessly with our drag & drop Builder plugin, it gives you the ability to customize the look and feel of your content. ',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1071] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1072] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1073] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_themify-feature-posts" );
$widgets[1074] = array (
  'title' => 'Recent Posts',
  'category' => '0',
  'show_count' => '3',
  'show_date' => 'on',
  'show_thumb' => 'on',
  'display' => 'none',
  'hide_title' => NULL,
  'thumb_width' => '50',
  'thumb_height' => '50',
  'excerpt_length' => '55',
  'orderby' => 'date',
  'order' => 'DESC',
);
update_option( "widget_themify-feature-posts", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1075] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1076] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1077] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1078] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1079] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '3',
  'hide_timestamp' => NULL,
  'show_follow' => 'on',
  'follow_text' => '→ Follow me',
  'include_retweets' => 'on',
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1080] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '2',
  'hide_timestamp' => NULL,
  'show_follow' => NULL,
  'follow_text' => '→ Follow me',
  'include_retweets' => NULL,
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1081] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1082] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1083] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1084] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1085] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1086] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_themify-feature-posts" );
$widgets[1087] = array (
  'title' => 'Recent Posts',
  'category' => '0',
  'show_count' => '3',
  'show_date' => 'on',
  'show_thumb' => 'on',
  'display' => 'none',
  'hide_title' => NULL,
  'thumb_width' => '50',
  'thumb_height' => '50',
  'excerpt_length' => '55',
  'orderby' => 'date',
  'order' => 'DESC',
);
update_option( "widget_themify-feature-posts", $widgets );

$widgets = get_option( "widget_themify-feature-posts" );
$widgets[1088] = array (
  'title' => 'Recent Posts',
  'category' => '0',
  'show_count' => '2',
  'show_date' => 'on',
  'show_thumb' => 'on',
  'display' => 'none',
  'hide_title' => NULL,
  'thumb_width' => '50',
  'thumb_height' => '50',
  'excerpt_length' => '55',
  'orderby' => 'date',
  'order' => 'DESC',
);
update_option( "widget_themify-feature-posts", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1089] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1090] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1091] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-large',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1092] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '3',
  'hide_timestamp' => NULL,
  'show_follow' => 'on',
  'follow_text' => '→ Follow me',
  'include_retweets' => 'on',
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1093] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '2',
  'hide_timestamp' => NULL,
  'show_follow' => NULL,
  'follow_text' => '→ Follow me',
  'include_retweets' => NULL,
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1094] = array (
  'title' => 'Widget 1',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1095] = array (
  'title' => 'Widget 2',
  'text' => 'Display any widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1096] = array (
  'title' => 'Widget 3',
  'text' => 'For example, phone #: 123-333-4567',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1097] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1098] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1099] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1100] = array (
  'title' => 'Widget 1',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1101] = array (
  'title' => 'Widget 2',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1102] = array (
  'title' => 'Widget 3',
  'text' => 'Optional widget here',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1103] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1104] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1105] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1106] = array (
  'title' => '',
  'text' => '[searchandfilter id="sidebar_product_filter"]',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1107] = array (
  'title' => 'Latest Tweets',
  'username' => '@themify',
  'show_count' => '2',
  'hide_timestamp' => NULL,
  'show_follow' => 'on',
  'follow_text' => '→ Follow me',
  'include_retweets' => 'on',
  'exclude_replies' => NULL,
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1108] = array (
  'title' => '',
  'show_link_name' => NULL,
  'open_new_window' => NULL,
  'icon_size' => 'icon-medium',
  'orientation' => 'horizontal',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1109] = array (
  'title' => 'Contact',
  'text' => '<b>Phone: </b> +1 916-875-2235<br>
<b>Fax:</b> +1 916-875-2235<br>
<b>Email:</b> info@domain.ltd <br> <br>
908 New Hampshire Avenue
Northwest #100, Washington, DC 
20037, United States',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_text" );
$widgets[1110] = array (
  'title' => 'About Us',
  'text' => 'The eCommerce theme is Themify\'s flagship theme. It\'s a WordPress
designed to give you more control on the design of your theme. Built to work seamlessly with our drag & drop Builder plugin, it gives you the ability to customize the look and feel of your content.',
  'filter' => false,
);
update_option( "widget_text", $widgets );

$widgets = get_option( "widget_woocommerce_products" );
$widgets[1111] = array (
  'title' => 'Featured Products',
  'number' => 3,
  'show' => 'featured',
  'orderby' => 'date',
  'order' => 'desc',
  'hide_free' => 0,
  'show_hidden' => 0,
);
update_option( "widget_woocommerce_products", $widgets );



$sidebars_widgets = array (
  'orphaned_widgets_1' => 
  array (
    0 => 'text-1002',
  ),
  'orphaned_widgets_5' => 
  array (
    0 => 'text-1003',
  ),
  'wp_inactive_widgets' => 
  array (
    0 => 'archives-1004',
    1 => 'archives-1005',
    2 => 'archives-1006',
    3 => 'archives-1007',
    4 => 'meta-1008',
    5 => 'meta-1009',
    6 => 'meta-1010',
    7 => 'meta-1011',
    8 => 'search-1012',
    9 => 'search-1013',
    10 => 'search-1014',
    11 => 'search-1015',
    12 => 'text-1016',
    13 => 'text-1017',
    14 => 'text-1018',
    15 => 'text-1019',
    16 => 'text-1020',
    17 => 'text-1021',
    18 => 'text-1022',
    19 => 'text-1023',
    20 => 'text-1024',
    21 => 'text-1025',
    22 => 'text-1026',
    23 => 'categories-1027',
    24 => 'categories-1028',
    25 => 'categories-1029',
    26 => 'categories-1030',
    27 => 'recent-posts-1031',
    28 => 'recent-posts-1032',
    29 => 'recent-posts-1033',
    30 => 'recent-posts-1034',
    31 => 'recent-comments-1035',
    32 => 'recent-comments-1036',
    33 => 'recent-comments-1037',
    34 => 'recent-comments-1038',
    35 => 'nav_menu-1039',
    36 => 'themify-feature-posts-1040',
    37 => 'themify-feature-posts-1041',
    38 => 'themify-feature-posts-1042',
    39 => 'themify-social-links-1043',
    40 => 'themify-social-links-1044',
    41 => 'themify-social-links-1045',
    42 => 'themify-social-links-1046',
    43 => 'themify-social-links-1047',
    44 => 'themify-social-links-1048',
    45 => 'themify-social-links-1049',
    46 => 'themify-social-links-1050',
    47 => 'themify-twitter-1051',
    48 => 'themify-twitter-1052',
    49 => 'themify-twitter-1053',
    50 => 'themify-twitter-1054',
    51 => 'themify-twitter-1055',
    52 => 'woocommerce_products-1056',
    53 => 'nav_menu-1057',
    54 => 'archives-1058',
    55 => 'meta-1059',
    56 => 'search-1060',
    57 => 'categories-1061',
    58 => 'recent-posts-1062',
    59 => 'recent-comments-1063',
    60 => 'archives-1064',
    61 => 'meta-1065',
    62 => 'search-1066',
    63 => 'text-1067',
    64 => 'text-1068',
    65 => 'text-1069',
    66 => 'text-1070',
    67 => 'categories-1071',
    68 => 'recent-posts-1072',
    69 => 'recent-comments-1073',
    70 => 'themify-feature-posts-1074',
    71 => 'themify-social-links-1075',
    72 => 'themify-social-links-1076',
    73 => 'themify-social-links-1077',
    74 => 'themify-social-links-1078',
    75 => 'themify-twitter-1079',
    76 => 'themify-twitter-1080',
    77 => 'archives-1081',
    78 => 'meta-1082',
    79 => 'search-1083',
    80 => 'categories-1084',
    81 => 'recent-posts-1085',
    82 => 'recent-comments-1086',
    83 => 'themify-feature-posts-1087',
    84 => 'themify-feature-posts-1088',
    85 => 'themify-social-links-1089',
    86 => 'themify-social-links-1090',
    87 => 'themify-social-links-1091',
    88 => 'themify-twitter-1092',
    89 => 'themify-twitter-1093',
    90 => 'text-1094',
    91 => 'text-1095',
    92 => 'text-1096',
    93 => 'archives-1097',
    94 => 'meta-1098',
    95 => 'search-1099',
    96 => 'text-1100',
    97 => 'text-1101',
    98 => 'text-1102',
    99 => 'categories-1103',
    100 => 'recent-posts-1104',
    101 => 'recent-comments-1105',
  ),
  'sidebar-main' => 
  array (
    0 => 'text-1106',
    1 => 'themify-twitter-1107',
  ),
  'footer-social-widget' => 
  array (
    0 => 'themify-social-links-1108',
  ),
  'footer-widget-1' => 
  array (
    0 => 'text-1109',
  ),
  'footer-widget-2' => 
  array (
    0 => 'text-1110',
  ),
  'footer-widget-3' => 
  array (
    0 => 'woocommerce_products-1111',
  ),
); 
update_option( "sidebars_widgets", $sidebars_widgets );

$menu_locations = array();
$menu = get_terms( "nav_menu", array( "slug" => "main-navigation" ) );
if( is_array( $menu ) && ! empty( $menu ) ) $menu_locations["main-nav"] = $menu[0]->term_id;
$menu = get_terms( "nav_menu", array( "slug" => "main-navigation" ) );
if( is_array( $menu ) && ! empty( $menu ) ) $menu_locations["footer-nav"] = $menu[0]->term_id;
set_theme_mod( "nav_menu_locations", $menu_locations );


$homepage = get_posts( array( 'name' => 'home', 'post_type' => 'page' ) );
			if( is_array( $homepage ) && ! empty( $homepage ) ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $homepage[0]->ID );
			}
			
	ob_start(); ?>a:110:{s:21:"setting-webfonts_list";s:11:"recommended";s:22:"setting-default_layout";s:12:"sidebar-none";s:27:"setting-default_post_layout";s:9:"list-post";s:19:"setting-post_filter";s:3:"yes";s:23:"setting-disable_masonry";s:3:"yes";s:19:"setting-post_gutter";s:6:"gutter";s:30:"setting-default_layout_display";s:7:"content";s:25:"setting-default_more_text";s:4:"More";s:21:"setting-index_orderby";s:4:"date";s:19:"setting-index_order";s:4:"DESC";s:32:"setting-default_post_meta_author";s:3:"yes";s:30:"setting-default_media_position";s:5:"above";s:31:"setting-image_post_feature_size";s:5:"blank";s:32:"setting-default_page_post_layout";s:8:"sidebar1";s:37:"setting-default_page_post_layout_type";s:7:"classic";s:30:"setting-default_page_post_meta";s:2:"no";s:37:"setting-default_page_post_meta_author";s:3:"yes";s:42:"setting-default_page_single_media_position";s:5:"above";s:38:"setting-image_post_single_feature_size";s:5:"blank";s:36:"setting-search-result_layout_display";s:7:"content";s:36:"setting-search-result_media_position";s:5:"above";s:27:"setting-default_page_layout";s:8:"sidebar1";s:40:"setting-custom_post_tglobal_style_single";s:8:"sidebar1";s:38:"setting-default_portfolio_index_layout";s:12:"sidebar-none";s:43:"setting-default_portfolio_index_post_layout";s:5:"grid3";s:29:"setting-portfolio_post_filter";s:3:"yes";s:33:"setting-portfolio_disable_masonry";s:3:"yes";s:24:"setting-portfolio_gutter";s:6:"gutter";s:39:"setting-default_portfolio_index_display";s:4:"none";s:50:"setting-default_portfolio_index_post_meta_category";s:3:"yes";s:49:"setting-default_portfolio_index_unlink_post_image";s:3:"yes";s:39:"setting-default_portfolio_single_layout";s:12:"sidebar-none";s:54:"setting-default_portfolio_single_portfolio_layout_type";s:9:"fullwidth";s:50:"setting-default_portfolio_single_unlink_post_image";s:3:"yes";s:22:"themify_portfolio_slug";s:7:"project";s:19:"setting-shop_layout";s:21:"sidebar1 sidebar-left";s:27:"setting-shop_archive_layout";s:21:"sidebar1 sidebar-left";s:23:"setting-products_layout";s:5:"grid4";s:31:"setting-product_disable_masonry";s:3:"yes";s:29:"setting-single_product_layout";s:12:"sidebar-none";s:30:"setting-related_products_limit";s:1:"3";s:53:"setting-customizer_responsive_design_tablet_landscape";s:4:"1024";s:43:"setting-customizer_responsive_design_tablet";s:3:"768";s:43:"setting-customizer_responsive_design_mobile";s:3:"480";s:33:"setting-mobile_menu_trigger_point";s:4:"1200";s:24:"setting-gallery_lightbox";s:8:"lightbox";s:26:"setting-page_builder_cache";s:2:"on";s:27:"setting-page_builder_expiry";s:1:"2";s:21:"setting-header_design";s:17:"header-horizontal";s:28:"setting-exclude_site_tagline";s:2:"on";s:19:"setting_search_form";s:11:"live_search";s:19:"setting-exclude_rss";s:2:"on";s:30:"setting-exclude_header_widgets";s:2:"on";s:22:"setting-header_widgets";s:4:"none";s:21:"setting-footer_design";s:15:"footer-left-col";s:38:"setting-exclude_footer_menu_navigation";s:2:"on";s:22:"setting-footer_widgets";s:17:"footerwidget-3col";s:23:"setting-mega_menu_posts";s:1:"5";s:29:"setting-mega_menu_image_width";s:3:"180";s:30:"setting-mega_menu_image_height";s:3:"120";s:27:"setting-imagefilter_applyto";s:12:"featuredonly";s:29:"setting-color_animation_speed";s:1:"5";s:29:"setting-relationship_taxonomy";s:8:"category";s:37:"setting-relationship_taxonomy_entries";s:1:"3";s:45:"setting-relationship_taxonomy_display_content";s:4:"none";s:30:"setting-single_slider_autoplay";s:3:"off";s:27:"setting-single_slider_speed";s:6:"normal";s:28:"setting-single_slider_effect";s:5:"slide";s:28:"setting-single_slider_height";s:4:"auto";s:18:"setting-more_posts";s:8:"infinite";s:19:"setting-entries_nav";s:8:"numbered";s:25:"setting-img_php_base_size";s:5:"large";s:27:"setting-global_feature_size";s:5:"blank";s:22:"setting-link_icon_type";s:9:"font-icon";s:32:"setting-link_type_themify-link-0";s:10:"image-icon";s:33:"setting-link_title_themify-link-0";s:7:"Twitter";s:31:"setting-link_img_themify-link-0";s:110:"https://themify.me/demo/themes/ultra-restaurant/wp-content/themes/themify-ultra/themify/img/social/twitter.png";s:32:"setting-link_type_themify-link-1";s:10:"image-icon";s:33:"setting-link_title_themify-link-1";s:8:"Facebook";s:31:"setting-link_img_themify-link-1";s:111:"https://themify.me/demo/themes/ultra-restaurant/wp-content/themes/themify-ultra/themify/img/social/facebook.png";s:32:"setting-link_type_themify-link-2";s:10:"image-icon";s:33:"setting-link_title_themify-link-2";s:7:"Google+";s:31:"setting-link_img_themify-link-2";s:114:"https://themify.me/demo/themes/ultra-restaurant/wp-content/themes/themify-ultra/themify/img/social/google-plus.png";s:32:"setting-link_type_themify-link-3";s:10:"image-icon";s:33:"setting-link_title_themify-link-3";s:7:"YouTube";s:31:"setting-link_img_themify-link-3";s:110:"https://themify.me/demo/themes/ultra-restaurant/wp-content/themes/themify-ultra/themify/img/social/youtube.png";s:32:"setting-link_type_themify-link-4";s:10:"image-icon";s:33:"setting-link_title_themify-link-4";s:9:"Pinterest";s:31:"setting-link_img_themify-link-4";s:112:"https://themify.me/demo/themes/ultra-restaurant/wp-content/themes/themify-ultra/themify/img/social/pinterest.png";s:33:"setting-link_type_themify-link-11";s:9:"font-icon";s:34:"setting-link_title_themify-link-11";s:7:"Twitter";s:33:"setting-link_link_themify-link-11";s:27:"https://twitter.com/themify";s:34:"setting-link_ficon_themify-link-11";s:10:"fa-twitter";s:32:"setting-link_type_themify-link-6";s:9:"font-icon";s:33:"setting-link_title_themify-link-6";s:8:"Facebook";s:32:"setting-link_link_themify-link-6";s:32:"https://www.facebook.com/themify";s:33:"setting-link_ficon_themify-link-6";s:11:"fa-facebook";s:32:"setting-link_type_themify-link-8";s:9:"font-icon";s:33:"setting-link_title_themify-link-8";s:7:"YouTube";s:32:"setting-link_link_themify-link-8";s:32:"https://youtube.com/user/themify";s:33:"setting-link_ficon_themify-link-8";s:10:"fa-youtube";s:22:"setting-link_field_ids";s:275:"{"themify-link-0":"themify-link-0","themify-link-1":"themify-link-1","themify-link-2":"themify-link-2","themify-link-3":"themify-link-3","themify-link-4":"themify-link-4","themify-link-11":"themify-link-11","themify-link-6":"themify-link-6","themify-link-8":"themify-link-8"}";s:23:"setting-link_field_hash";s:2:"12";s:30:"setting-page_builder_is_active";s:6:"enable";s:41:"setting-page_builder_animation_appearance";s:4:"none";s:42:"setting-page_builder_animation_parallax_bg";s:4:"none";s:46:"setting-page_builder_animation_parallax_scroll";s:6:"mobile";s:44:"setting-page_builder_animation_sticky_scroll";s:4:"none";s:4:"skin";s:9:"ecommerce";s:13:"import_images";s:2:"on";}<?php $themify_data = unserialize( ob_get_clean() );

	// Fix how "skin" option used to save
	if( isset( $themify_data['skin'] ) ) {
		if ( filter_var( $themify_data['skin'], FILTER_VALIDATE_URL ) ) {
			$parsed_skin = parse_url( $value, PHP_URL_PATH );
			$themify_data['skin'] = basename( dirname( $parsed_skin ) );
		}
	}

	themify_set_data( $themify_data );
	
}
themify_do_demo_import();