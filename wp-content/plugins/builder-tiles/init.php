<?php
/*
Plugin Name:  Builder Tiles
Plugin URI:   http://themify.me/addons/tiles
Version:      1.2.7
Author:       Themify
Description:  A Builder addon to make flippable Tiles like Windows 8 Metro layouts. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:  builder-tiles
Domain Path:  /languages
*/

defined( 'ABSPATH' ) or die( '-1' );

if( ! class_exists( 'Builder_Tiles' ) ) {
	class Builder_Tiles {

		private static $instance = null;
		public $url;
		private $dir;
		private $version;
		private $options;

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
			include( $this->dir . 'includes/admin.php' );
			add_action( 'init', array( $this, 'i18n' ), 5 );
			add_action( 'themify_builder_setup_modules', array( $this, 'register_module' ) );
			add_action( 'themify_builder_admin_enqueue', array( $this, 'admin_enqueue' ), 15 );
			add_action( 'wp_head', array( $this, 'dynamic_css' ) );
			add_filter( 'themify_builder_script_vars', array( $this, 'themify_builder_script_vars' ) );
			add_action( 'themify_do_metaboxes', array( $this, 'themify_do_metaboxes' ) );
                        add_filter('themify_builder_addons_assets',array($this,'assets'),10,1);
			add_action( 'init', array( $this, 'updater' ) );
		}

		public function constants() {
			$data = get_file_data( __FILE__, array( 'Version' ) );
			$this->version = $data[0];
			$this->url = defined( 'BUILDER_TILES_URL' ) ? BUILDER_TILES_URL : trailingslashit( plugin_dir_url( __FILE__ ) );
			$this->dir = defined( 'BUILDER_TILES_DIR' ) ? BUILDER_TILES_DIR : trailingslashit( plugin_dir_path( __FILE__ ) );
		}

		public function i18n() {
			load_plugin_textdomain( 'builder-tiles', false, '/languages' );
		}

                
                public function assets($assets){
                        $fluid_tiles = array(
				array( 'query' => 'screen and (max-width: 600px)', 'size' => '2' ),
			);
			$fluid_tiles_enabled = $this->get_option( 'fluid_tiles', 0 );
			if( $fluid_tiles_enabled == 1 || $fluid_tiles_enabled === 'yes' ) {
				$fluid_tiles[] = array(
					'query' => 'screen and (min-width: 601px) and (max-width: 1001px)',
					'size' => '4'
				);
				$fluid_tiles[] = array(
					'query' => 'screen and (min-width: 1001px)',
					'size' => '5'
				);
				$fluid_tiles[] = array(
					'query' => 'screen and (min-width: 1501px)',
					'size' => '6'
				);
			}
                    $options = apply_filters( 'builder_tiles_script_vars', array(
                                                'ajax_nonce'	=> wp_create_nonce('ajax_nonce'),
                                                'ajax_url'	=> admin_url( 'admin-ajax.php' ),
                                                'networkError'	=> __('Unknown network error. Please try again later.', 'builder-tiles'),
                                                'termSeparator'	=> ', ',
                                                'galleryFadeSpeed' => '300',
                                                'galleryEvent' => 'click',
                                                'transition_duration' => 750,
                                                'isOriginLeft' => is_rtl() ? 0 : 1,
                                                'tiles_selector' => '.tb-column-inner:has(".module-tile"), .themify_builder_sub_row .sub_column:has(".module-tile")',
                                                'fluid_tiles' => 'yes',
                                                'fluid_tile_rules' => $fluid_tiles,
                                                'url'=>$this->url . 'assets/',
                                                'include_url' =>  includes_url('js/'),
                                                'ver'=>$this->version,
                                            ));
                    $assets['builder-tiles']=array(
                                            'selector'=>$options['tiles_selector'],
                                            'css'=>$this->url.'assets/style.css',
                                            'js'=>$this->url.'assets/script.js',
                                            'ver'=>$this->version,
                                            'external'=>Themify_Builder_Model::localize_js('BuilderTiles',$options)
                                    );
                    return $assets;
                }
		/**
		 * Load animate.css library when Tiles module is used in the page
		 *
		 * @since 1.1.3
		 */
		public function themify_builder_script_vars( $vars ) {
			$vars['animationInviewSelectors'][] = '.module.module-tile';
			return $vars;
		}

		public function admin_enqueue() {
			wp_enqueue_style( 'builder-tiles-admin', $this->url . 'assets/admin.css' );
			wp_enqueue_script( 'builder-tiles-admin', $this->url . 'assets/admin.js', array( 'jquery' ), $this->version, true );
		}

		public function register_module( $ThemifyBuilder ) {
			$ThemifyBuilder->register_directory( 'templates', $this->dir . 'templates' );
			$ThemifyBuilder->register_directory( 'modules', $this->dir . 'modules' );
		}

		public function get_tile_sizes() {
			return apply_filters( 'builder_tiles_sizes', array(
				'square-large' => array( 'label' => __( 'Square Large', 'builder-tiles' ), 'width' => 480, 'height' => 480, 'mobile_width' => 280, 'mobile_height' => 280, 'image' => $this->url . 'assets/size-sl.png' ),
				'square-small' => array( 'label' => __( 'Square Small', 'builder-tiles' ), 'width' => 240, 'height' => 240, 'mobile_width' => 140, 'mobile_height' => 140, 'image' => $this->url . 'assets/size-ss.png' ),
				'landscape' => array( 'label' => __( 'Landscape', 'builder-tiles' ), 'width' => 480, 'height' => 240, 'mobile_width' => 280, 'mobile_height' => 140, 'image' => $this->url . 'assets/size-l.png' ),
				'portrait' => array( 'label' => __( 'Portrait', 'builder-tiles' ), 'width' => 240, 'height' => 480, 'mobile_width' => 140, 'mobile_height' => 280, 'image' => $this->url . 'assets/size-p.png' ),
			) );
		}

		public function dynamic_css() {
			$css = '';
			foreach( $this->get_tile_sizes() as $key => $size ) {
				$css .= sprintf( '
			.module-tile.size-%1$s,
			.module-tile.size-%1$s .tile-background img,
			.module-tile.size-%1$s .map-container {
				width: %2$spx;
				height: %3$spx;
				max-width: 100%%;
			}',
					$key,
					$size['width'],
					$size['height'],
					$size['mobile_width'],
					$size['mobile_height']
				);
			}

			$gutter = (int) $this->get_option( 'gutter', 0 );
			if( $gutter ) {
				$css .= '.module-tile .tile-flip-box-wrap { padding: ' . $gutter . 'px; } .tiles-wrap { width: calc( 100% + ' . $gutter * 2 . 'px ); margin-left: -' . $gutter . 'px; }';
			}

			echo sprintf( '<style>%s</style>', $css );
		}

		function themify_do_metaboxes( $panels ) {
			$options = array(
				array(
				"name" => "builder_tiles_fluid_tiles",
				"title" => __('Fluid Tiles', 'builder-tiles'),
				"description" => __( "If enabled, tiles will display fluid in % width (eg. small tile will be 25% width)", 'builder-tiles' ),
				"type" => "dropdown",
				"meta" => array(
					array("value" => "", "name" => __('Default', 'builder-tiles'), "selected" => true),
					array("value" => 'yes', 'name' => __('Enable', 'themify')),
					array("value" => 'no', 'name' => __('Disable', 'themify'))
				)
				),
				array(
					"name" => "builder_tiles_gutter",
					"title" => __('Tile Spacing', 'builder-tiles'),
					"description" => "",
					"type" => "textbox",
					"meta" => array( "size"=>"small"),
					'after' => ' px'
				),
			);
			$panels[] = array(
				'name' => __( 'Builder Tiles', 'builder-tiles' ),
				'id' => 'builder-tiles',
				'options' => $options,
				'pages' => 'page'
			);

			return $panels;
		}

		public function get_option( $name, $default = null ) {
			if( ! isset( $this->options ) ) {
				$options = get_option( 'builder_tiles', array() );
				$this->options = wp_parse_args( $options, $this->get_defaults() );
			}

			$value = null;
			if( isset( $this->options[$name] ) ) {
				$value = $this->options[$name];
			}
			if( is_page() ) {
				if( themify_check( "builder_tiles_{$name}" ) ) {
					$value = themify_get( "builder_tiles_{$name}" );
				}
			}

			if( $value == null ) {
				$value = $default;
			}

			return $value;
		}

		function get_defaults() {
			return array(
				'fluid_tiles' => 1,
				'gutter' => 0,
			);
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
	Builder_Tiles::get_instance();
}