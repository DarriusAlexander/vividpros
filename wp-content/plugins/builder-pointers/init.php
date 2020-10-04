<?php
/*
Plugin Name:  Builder Pointers
Plugin URI:   http://themify.me/addons/pointers
Version:      1.1.1
Author:       Themify
Description:  Create tooltip-like pointers on any image. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:  builder-pointers
Domain Path:  /languages
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Pointers {

	private static $instance = null;
	private $url;
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
		$this->constants();
		$this->i18n();
		add_action( 'themify_builder_setup_modules', array( $this, 'register_module' ) );
		add_action( 'themify_builder_admin_enqueue', array( $this, 'admin_enqueue' ), 15 );
                add_filter('themify_builder_addons_assets',array($this,'assets'),10,1);
		add_action( 'init', array( $this, 'updater' ) );
		if( is_admin() ) {
			add_action( 'wp_ajax_builder_pointers_get_image', array( $this, 'ajax_get_image' ) );
		}
	}

	public function constants() {
		$data = get_file_data( __FILE__, array( 'Version' ) );
		$this->version = $data[0];
		$this->url = trailingslashit( plugin_dir_url( __FILE__ ) );
		$this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function i18n() {
		load_plugin_textdomain( 'builder-pointers', false, '/languages' );
	}

	public function admin_enqueue() {
		wp_enqueue_script( 'builder-pointers-admin', $this->url . 'assets/admin.js', array( 'jquery', 'jquery-ui-draggable' ), $this->version, true );
		wp_enqueue_style( 'builder-pointers-admin', $this->url . 'assets/admin.css' );
	}

	public function ajax_get_image() {
		if( isset( $_POST['pointers_image'] ) && '' != trim( $_POST['pointers_image'] ) ) {
			$url = trim( $_POST['pointers_image'] );
			$width = trim( $_POST['pointers_width'] );
			$height = trim( $_POST['pointers_height'] );
			$image = themify_do_img( $url, $width, $height );
			echo '<img src="' . $image['url'] .'" width="'. $image['width'] .'" height="'. $image['height'] .'" alt="" />';
		}
		die;
	}

	public function register_module( $ThemifyBuilder ) {
		$ThemifyBuilder->register_directory( 'templates', $this->dir . 'templates' );
		$ThemifyBuilder->register_directory( 'modules', $this->dir . 'modules' );
	}
        
        public function assets($assets){
            $assets['builder-pointers']=array(
                                    'selector'=>'.module-pointers',
                                    'css'=>$this->url.'assets/style.css',
                                    'js'=>$this->url.'assets/scripts.js',
                                    'ver'=>$this->version,
                                    'external'=>Themify_Builder_Model::localize_js('BuilderPointers', apply_filters( 'builder_pointers_script_vars', array(
                                                    'trigger' => 'hover',
                                        ) ))
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
add_action( 'themify_builder_before_init', array( 'Builder_Pointers', 'get_instance' ) );