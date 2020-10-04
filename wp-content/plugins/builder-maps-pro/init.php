<?php
/*
Plugin Name:  Builder Maps Pro
Plugin URI:   http://themify.me/addons/maps-pro
Version:      1.2.1
Author:       Themify
Description:  Maps Pro module allows you to insert Google Maps with multiple location markers with custom icons, tooltip text, and various map styles. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:  builder-maps-pro
Domain Path:  /languages
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Maps_Pro {

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
		add_action( 'themify_builder_admin_enqueue', array( $this, 'admin_enqueue' ), 1 );
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
		load_plugin_textdomain( 'builder-maps-pro', false, '/languages' );
	}

	public function admin_enqueue() {
                $map_key = method_exists('Themify_Builder','getMapKey')?Themify_Builder::getMapKey():'';
		wp_enqueue_script( 'builder-maps-pro-admin', $this->url . 'assets/admin.js', array( 'jquery', 'jquery-ui-draggable' ), $this->version, false );
		wp_enqueue_style( 'builder-maps-pro-admin', $this->url . 'assets/admin.css' );

		$map_styles = array();
		foreach( $this->get_map_styles() as $key => $value ) {
			$name = str_replace( '.json', '', $key );
			$map_styles[$name] = $this->get_map_style( $name );
		}
		wp_localize_script( 'builder-maps-pro-admin', 'builderMapsPro', array(
                        'key'=>$map_key,
			'styles' => $map_styles,
			'labels' => array(
				'add_marker' => __( 'Add Location Marker', 'builder-maps-pro' ),
			)
		) );
	}

	public function register_module( $ThemifyBuilder ) {
		$ThemifyBuilder->register_directory( 'templates', $this->dir . 'templates' );
		$ThemifyBuilder->register_directory( 'modules', $this->dir . 'modules' );
	}
        
        public function assets($assets){
            $assets['builder-maps-pro']=array(
                                    'selector'=>'.module-maps-pro, .module-type-maps-pro',
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

	public function get_map_styles() {
		$theme_styles = is_dir( get_stylesheet_directory() . '/builder-maps-pro/styles/' ) ? $this->list_dir( get_stylesheet_directory() . '/builder-maps-pro/styles/' ) : array();

		return array_merge( $this->list_dir( $this->dir . 'styles/' ), $theme_styles );
	}

	public function list_dir( $path ) {
		$dh = opendir( $path );
		$files = array();
		while ( false !== ( $filename = readdir( $dh ) ) ) {
			if( $filename != '.' && $filename != '..' ) {
				$files[$filename] = $filename;
			}
		}

		return $files;
	}

	public function get_map_style( $name ) {
		$file = $this->dir . 'styles/' . $name . '.json';
		if( file_exists( get_stylesheet_directory() . '/builder-maps-pro/styles/' . $name . '.json' ) ) {
			$file = get_stylesheet_directory() . '/builder-maps-pro/styles/' . $name . '.json';
		} elseif( ! file_exists( $file ) ) {
			return '';
		}

		ob_start();
		include $file;
		return json_decode( ob_get_clean() );
	}
}
add_action( 'themify_builder_before_init', array( 'Builder_Maps_Pro', 'get_instance' ), 0 );