<?php
/*
Plugin Name:  Builder Infinite Posts
Plugin URI:   http://themify.me/addons/infinite-posts
Version:      1.0.3
Author:       Themify
Description:  It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:  builder-infinite-posts
Domain Path:  /languages
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Infinite_Posts {

	private static $instance = null;
	public $url;
	private $dir;
	private $version;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 5 );
		add_action( 'themify_builder_setup_modules', array( $this, 'register_module' ) );
		add_action( 'themify_builder_admin_enqueue', array( $this, 'admin_enqueue' ), 15 );
                add_filter('themify_builder_addons_assets',array($this,'assets'),10,1);
		add_action( 'init', array( $this, 'updater' ) );
	}

	public function constants() {
		$data = get_file_data( __FILE__, array( 'Version' ) );
		$this->version = $data[0];
		$this->url = trailingslashit( plugin_dir_url( __FILE__ ) );
		$this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function i18n() {
		load_plugin_textdomain( 'builder-infinite-posts', false, '/languages' );
	}


	public function admin_enqueue() {
		wp_enqueue_style( 'builder-infinite-posts-admin', $this->url . 'assets/admin.css', null, $this->version );
		wp_enqueue_script( 'builder-infinite-posts' );
	}

	public function register_module( $ThemifyBuilder ) {
		$ThemifyBuilder->register_directory( 'templates', $this->dir . 'templates' );
		$ThemifyBuilder->register_directory( 'modules', $this->dir . 'modules' );
	}
        
        public function assets($assets){
            $assets['builder-infinite-posts']=array(
                                    'selector'=>'.module-infinite-posts',
                                    'css'=>$this->url.'assets/style.css',
                                    'js'=>$this->url.'assets/scripts.js',
                                    'ver'=>$this->version,
                                    'external'=>Themify_Builder_Model::localize_js('builderInfinitePosts', array(
                                        'url' => $this->url,
                                        'loading_image' => $this->url . 'assets/loading.gif',
                                        'bufferPx' => 10,
                                        'pixelsFromNavToBottom' => 30
                                    ))
                            );
            return $assets;
        }
	public function updater() {
		if( class_exists( 'Themify_Builder_Updater' ) ) {
			if ( ! function_exists( 'get_plugin_data') ) 
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
			$plugin_basename = plugin_basename( __FILE__ );
			$plugin_data = get_plugin_data( trailingslashit( plugin_dir_path( __FILE__ ) ) . basename( $plugin_basename ) );
			new Themify_Builder_Updater( array(
				'name' => trim( dirname( $plugin_basename ), '/' ),
				'nicename' => $plugin_data['Name'],
				'update_type' => 'addon',
			), $this->version, trim( $plugin_basename, '/' ) );
		}
	}
}
Builder_Infinite_Posts::get_instance();