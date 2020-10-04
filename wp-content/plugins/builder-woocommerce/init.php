<?php
/*
Plugin Name:  Builder WooCommerce
Plugin URI:   http://themify.me/addons/woocommerce
Version:      1.1.6 
Author:       Themify
Description:  Show WooCommerce products anywhere with the Builder. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:  builder-wc
Domain Path:  /languages
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Woocommerce {

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
		add_action( 'plugins_loaded', array( $this, 'setup' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 5 );
		add_action( 'init', array( $this, 'updater' ) );
	}

	public function constants() {
		$data = get_file_data( __FILE__, array( 'Version' ) );
		$this->version = $data[0];
		$this->url = trailingslashit( plugin_dir_url( __FILE__ ) );
		$this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function setup() {
		if( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'themify_builder_setup_modules', array( $this, 'register_module' ) );
		add_action( 'themify_builder_admin_enqueue', array( $this, 'admin_enqueue' ), 15 );
		add_filter('themify_builder_addons_assets',array($this,'assets'),10,1);
	}

	public function i18n() {
		load_plugin_textdomain( 'builder-wc', false, '/languages' );
	}

	public function enqueue() {
		wp_enqueue_style( 'builder-woocommerce', $this->url . 'assets/style.css', null, $this->version );
	}
        
        public function assets($assets){
            $assets['builder-woocommerce']=array(
                                    'selector'=>'.module-products,.module-product-categories',
                                    'css'=>$this->url.'assets/style.css',
                                    'ver'=>$this->version
                            );
            return $assets;
        }
	public function admin_enqueue() {
		wp_enqueue_style( 'builder-woocommerce-admin', $this->url . 'assets/admin.css' );
	}

	public function register_module( $ThemifyBuilder ) {
		$ThemifyBuilder->register_directory( 'templates', $this->dir . 'templates' );
		$ThemifyBuilder->register_directory( 'modules', $this->dir . 'modules' );
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
Builder_Woocommerce::get_instance();