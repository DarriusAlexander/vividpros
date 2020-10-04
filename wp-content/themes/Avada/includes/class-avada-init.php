<?php

class Avada_Init {

	public function __construct() {

		add_action( 'after_setup_theme', array( $this, 'load_textdomain' ) );
		add_action( 'after_setup_theme', array( $this, 'set_builder_status' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'add_theme_supports' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'register_nav_menus' ) );
		add_action( 'after_setup_theme', array( $this, 'add_image_size' ) );
		add_action( 'after_setup_theme', array( $this, 'migrate' ) );

		add_action( 'wp', array( $this, 'set_theme_version' ) );

		add_action( 'widgets_init', array( $this, 'widget_init' ) );

		// Allow shortcodes in widget text
		add_filter( 'widget_text', 'do_shortcode' );

		add_filter( 'wp_nav_menu_args', array( $this, 'main_menu_args' ) );
		add_action( 'admin_init', array( $this, 'theme_activation' ) );

		// Term meta migration for WordPress 4.4
		// add_action( 'admin_init', array( $this, 'migrate_term_data' ) );

		add_action( 'avada_before_main', array( $this, 'youtube_flash_fix' ) );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

		// Remove post_format from previwe link
		add_filter( 'preview_post_link', array( $this, 'remove_post_format_from_link' ), 9999 );


		// Add home url link for navigation menus
		add_filter( 'wp_nav_menu_items', array( $this, 'set_home_path_for_menu_items' ) );

	}

	/**
	 * Load the theme textdomain
	 */
	public function load_textdomain(){

		// wp-content/theme/languages/en_US.mo
		// wp-content/languages/themes/Avada-en_US.mo
		$loaded = load_theme_textdomain( 'Avada', get_template_directory() . '/languages' );

		// wp-content/theme/languages/Avada-en_US.mo
		 if ( ! $loaded ) {
		 	add_filter( 'theme_locale', array( $this, 'change_locale' ), 10, 2 );
		 	$loaded = load_theme_textdomain( 'Avada', get_template_directory() . '/languages' );

		 	// wp-content/theme/languages/avada-en_US.mo
			// wp-content/languages/themes/avada-en_US.mo
		 	if ( ! $loaded ) {
		 		remove_filter( 'theme_locale', array( $this, 'change_locale' ) );
		 		add_filter( 'theme_locale', array( $this, 'change_locale_lowercase' ), 10, 2 );
		 		$loaded = load_theme_textdomain( 'Avada', get_template_directory() . '/languages' );

		 		// wp-content/languages/Avada-en_US.mo
		 		if ( ! $loaded ) {
					remove_filter( 'theme_locale', array( $this, 'change_locale_lowercase' ) );
					add_filter( 'theme_locale', array( $this, 'change_locale' ), 10, 2 );
					$loaded = load_theme_textdomain( 'Avada', dirname( dirname( get_template_directory() ) ) . '/languages' );

					// wp-content/languages/themes/avada/en_US.mo
					if ( ! $loaded ) {
						remove_filter( 'theme_locale', array( $this, 'change_locale' ) );
						$loaded = load_theme_textdomain( 'Avada', dirname( dirname( get_template_directory() ) ) . '/languages/themes/avada' );
					}
		 		}
		 	}
		}
	}

	public function change_locale( $locale, $domain ) {
		return $domain . '-' . $locale;
	}

	public function change_locale_lowercase( $locale, $domain ) {
		return strtolower( $domain ) . '-' . $locale;
	}

	/**
	 * Conditionally add theme_support for fusion_builder
	 */
	public function set_builder_status() {

		if ( ! Avada()->settings->get( 'disable_builder' ) ) {
			add_theme_support( 'fusion_builder' );
		}

	}

	/**
	 * Stores the theme version in the options table in the WordPress database.
	 */
	public function set_theme_version() {

		if ( function_exists( 'wp_get_theme' ) ) {
			$theme_obj = wp_get_theme();
			$theme_version = $theme_obj->get( 'Version' );

			if ( $theme_obj->parent_theme ) {
				$template_dir  = basename( get_template_directory() );
				$theme_obj     = wp_get_theme( $template_dir );
				$theme_version = $theme_obj->get( 'Version' );
			}

			update_option( 'avada_theme_version', $theme_version );
		}

	}

	/**
	 * Add theme_supports
	 */
	public function add_theme_supports() {

		// Default WP generated title support
		add_theme_support( 'title-tag' );
		// Default RSS feed links
		add_theme_support( 'automatic-feed-links' );
		// Default custom header
		add_theme_support( 'custom-header' );
		// Default custom backgrounds
		add_theme_support( 'custom-background' );
		// Woocommerce Support
		add_theme_support( 'woocommerce' );
		// Post Formats
		add_theme_support( 'post-formats', array( 'gallery', 'link', 'image', 'quote', 'video', 'audio', 'chat' ) );
		// Add post thumbnail functionality
		add_theme_support('post-thumbnails');

	}

	/**
	 * Add image sizes
	 */
	 public function add_image_size() {

		 add_image_size( 'blog-large', 669, 272, true );
		 add_image_size( 'blog-medium', 320, 202, true );
		 add_image_size( 'tabs-img', 52, 50, true );
		 add_image_size( 'related-img', 180, 138, true );
		 add_image_size( 'portfolio-full', 940, 400, true );
		 add_image_size( 'portfolio-one', 540, 272, true );
		 add_image_size( 'portfolio-two', 460, 295, true );
		 add_image_size( 'portfolio-three', 300, 214, true );
		 add_image_size( 'portfolio-four', 220, 161, true );
		 add_image_size( 'portfolio-five', 177, 142, true );
		 add_image_size( 'portfolio-six', 147, 118, true );
		 add_image_size( 'recent-posts', 700, 441, true );
		 add_image_size( 'recent-works-thumbnail', 66, 66, true );
	 }

	/**
	 * Migrate script to decode theme options
	 */
	public function migrate() {
		if ( get_option( 'avada_38_migrate' ) != 'done' ) {
			$theme_version = get_option( 'avada_theme_version' );

			if ( $theme_version == '1.0.0' ) { // child theme check failure
				$this->set_theme_version();
			}

			$theme_version = get_option( 'avada_theme_version' );

			if ( version_compare( $theme_version, '3.8', '>=' ) && version_compare( $theme_version, '3.8.5', '<' ) ) {
				$smof_data_to_decode = get_option( 'Avada_options' );

				$encoded_field_names = array( 'google_analytics', 'space_head', 'space_body', 'custom_css' );

				foreach ( $encoded_field_names as $field_name ) {
					$decoded_field_value = rawurldecode( $smof_data_to_decode[ $field_name ] );

					if ( $decoded_field_value ) {
						$smof_data_to_decode[ $field_name ] = $decoded_field_value;
					}
				}

				update_option( 'Avada_options', $smof_data_to_decode );
				update_option( 'avada_38_migrate', 'done' );
			}
		}
	}

	/**
	 * Register navigation menus
	 */
	public function register_nav_menus() {

		register_nav_menu( 'main_navigation', 'Main Navigation' );
		register_nav_menu( 'top_navigation', 'Top Navigation' );
		register_nav_menu( '404_pages', '404 Useful Pages' );
		register_nav_menu( 'sticky_navigation', 'Sticky Header Navigation' );

	}

	public function theme_activation() {

		global $pagenow;

		if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) ) {

			update_option( 'shop_catalog_image_size',   array( 'width' => 500, 'height' => '', 0 ) );
			update_option( 'shop_single_image_size',    array( 'width' => 500, 'height' => '', 0 ) );
			update_option( 'shop_thumbnail_image_size', array( 'width' => 120, 'height' => '', 0 ) );

		}

	}

	/*public function migrate_term_data() {
		$version = get_bloginfo( 'version' );
		$function_test = function_exists( 'add_term_meta' );

		if( version_compare( $version, '4.4', '>=' ) && ! $function_test ) {

		}
	}*/

	public function main_menu_args( $args ) {

		global $post;

		$c_pageID = Avada::c_pageID();

		if ( get_post_meta( $c_pageID, 'pyre_displayed_menu', true ) != '' && get_post_meta( $c_pageID, 'pyre_displayed_menu', true ) != 'default' && ( $args['theme_location'] == 'main_navigation' || $args['theme_location'] == 'sticky_navigation' ) ) {
			$menu = get_post_meta( $c_pageID, 'pyre_displayed_menu', true );
			$args['menu'] = $menu;
		}

		return $args;

	}

	public function youtube_flash_fix() {
		echo '<div class="fusion-youtube-flash-fix">&shy;<style type="text/css"> iframe { visibility: hidden; opacity: 0; } </style></div>';
	}

	public function widget_init() {

		register_widget( 'Fusion_Widget_Ad_125_125' );
		register_widget( 'Fusion_Widget_Contact_Info' );
		register_widget( 'Fusion_Widget_Tabs' );
		register_widget( 'Fusion_Widget_Recent_Works' );
		register_widget( 'Fusion_Widget_Tweets' );
		register_widget( 'Fusion_Widget_Flickr' );
		register_widget( 'Fusion_Widget_Social_Links' );
		register_widget( 'Fusion_Widget_Facebook_Page' );

	}

	public function remove_post_format_from_link( $url ) {
		$url = remove_query_arg( 'post_format', $url );
		return $url;
	}

	public function set_home_path_for_menu_items( $items ) {
		$url = get_home_url();
		$url = str_replace( 'http://', '', $url );
		$url = str_replace( 'https://', '', $url );

	    $items = str_replace( 'fusion_home_url', $url, $items );
	    return $items;
	}


}

// Omit closing PHP tag to avoid "Headers already sent" issues.
