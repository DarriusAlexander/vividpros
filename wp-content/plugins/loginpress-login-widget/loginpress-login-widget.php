<?php
/**
 * Plugin Name: LoginPress - Login Widget
 * Plugin URI: http://www.WPBrigade.com/wordpress/plugins/loginpress/
 * Description: LoginPress -Login widget is the best Login plugin by <a href="https://wpbrigade.com/">WPBrigade</a> which allows you to login from front end.
 * Version: 1.0.1
 * Author: WPBrigade
 * Author URI: http://www.WPBrigade.com/
 * Text Domain: loginpress-login-widget
 * Domain Path: /languages
 *
 * @package loginpress
 * @category Core
 * @author WPBrigade
 */

if ( ! class_exists( 'LoginPress_Login_Widget' ) ) :

	final class LoginPress_Login_Widget {

		/**
		 * @var string
		 */
		public $version = '1.0.1';

		public function __construct() {
			$this->_hooks();
			$this->define_constants();

		}

		/**
		 * Hook into actions and filters.
		 * @since 1.0.0
		 * @version 1.0.1
		 */
		public function _hooks() {

			// Here we call `init` action instead of `plugins_loaded` for textdomain(); because plugins_loaded is triggered before the theme loads.
			add_action( 'init',         					array( $this, 'textdomain' ) );
			add_action( 'wp_enqueue_scripts',     array( $this, '_widget_script' ) );
			add_action( 'widgets_init',           array( $this, 'register_widget' ) );
			add_action( 'admin_enqueue_scripts',  array( $this, '_admin_scripts' ) );
		  add_action( 'admin_init',             array( $this, 'init_addon_updater' ), 0 );
			add_filter( 'login_form_bottom', 			array( $this, 'loginpress_social_login' ), 1 );

			// Ajax events
			add_action( 'wp_ajax_loginpress_widget_login_process', array( $this, 'loginpress_widget_ajax' ) );
			add_action( 'wp_ajax_nopriv_loginpress_widget_login_process', array( $this, 'loginpress_widget_ajax' ) );
		}

		public function loginpress_social_login(){

      $redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

      $encoded_url = urlencode( $redirect_to );

			$settings = get_option( 'loginpress_social_logins' );

			$html = '';
			$html .= "<div class='social-networks block'>";
			$html .= "<span class='social-sep'><span>" . __( 'or', 'loginpress-social-login' ) . "</span></span>";

			if ( isset( $settings['gplus'] ) && $settings['gplus'] == 'on' && ! empty( $settings['gplus_client_id'] ) && ! empty( $settings['gplus_client_secret'] )) :
				$html .= '<a href="' . wp_login_url() . '?lpsl_login_id=gplus_login';
				if ( $encoded_url ) {
						$html .= "&state=" . base64_encode( "redirect_to=$encoded_url" );
				}
				$html .= '" title="' . __( 'Login with Google Plus', 'loginpress-social-login' ) .'">';
				$html .= '<div class="lpsl-icon-block icon-google-plus clearfix">';
				$html .= '<span class="lpsl-login-text">' . __( 'Login with Google', 'loginpress-social-login' ) . '</span>';
				$html .= '<i class="fa fa-google-plus"></i>';
				$html .= '</div>';
				$html .= '</a>';
			endif;

			if ( isset( $settings['facebook'] ) && $settings['facebook'] == 'on' && ! empty( $settings['facebook_app_id'] ) && ! empty( $settings['facebook_app_secret'] ) ) :
				$html .= '<a href="' . wp_login_url() . '?lpsl_login_id=facebook_login';
				if ( $encoded_url ) {
						$html .= "&state=" . base64_encode( "redirect_to=$encoded_url" );
				}
				$html .= '" title="' . __( 'Login with Facebook', 'loginpress-social-login' ) .'">';
				$html .= '<div class="lpsl-icon-block icon-facebook clearfix">';
				$html .= '<span class="lpsl-login-text">' . __( 'Login with Facebook', 'loginpress-social-login' ) . '</span>';
				$html .= '<i class="fa fa-facebook"></i>';
				$html .= '</div>';
				$html .= '</a>';
			endif;

			if ( isset( $settings['twitter'] ) && $settings['twitter'] == 'on' && ! empty( $settings['twitter_oauth_token'] ) && ! empty( $settings['twitter_token_secret'] ) ) :
				$html .= '<a href="' . wp_login_url() . '?lpsl_login_id=twitter_login';
				if ( $encoded_url ) {
						$html .= "&state=" . base64_encode( "redirect_to=$encoded_url" );
				}
				$html .= '" title="' . __( 'Login with Twitter', 'loginpress-social-login' ) .'">';
				$html .= '<div class="lpsl-icon-block icon-twitter clearfix">';
				$html .= '<span class="lpsl-login-text">' . __( 'Login with Twitter', 'loginpress-social-login' ) . '</span>';
				$html .= '<i class="fa fa-twitter"></i>';
				$html .= '</div>';
				$html .= '</a>';
			endif;

			if ( isset( $settings['linkedin'] ) && $settings['linkedin'] == 'on' && ! empty( $settings['linkedin_client_id'] ) && ! empty( $settings['linkedin_client_secret'] )) :
				$html .= '<a href="' . wp_login_url() . '?lpsl_login_id=linkedin_login';
				if ( $encoded_url ) {
						$html .= "&state=" . base64_encode( "redirect_to=$encoded_url" );
				}
				$html .= '" title="' . __( 'Login with LinkedIn', 'loginpress-social-login' ) .'">';
				$html .= '<div class="lpsl-icon-block icon-linkdin clearfix">';
				$html .= '<span class="lpsl-login-text">' . __( 'Login with LinkedIn', 'loginpress-social-login' ) . '</span>';
				$html .= '<i class="fa fa-linkdin"></i>';
				$html .= '</div>';
				$html .= '</a>';
			endif;
			$html .= '</div>';

			return $html;
    }

		/**
	     * LoginPress Addon updater
	     *
	     */
	    public function init_addon_updater() {
	        if( class_exists( 'LoginPress_AddOn_Updater' ) ) {
	          //echo 'Exists';
	          $updater = new LoginPress_AddOn_Updater( 2333, __FILE__, $this->version );
	        }
	    }

		/**
		 * Load Languages
		 *
		 * @since 1.0.0
		 */
		public function textdomain() {

			$plugin_dir =  dirname( plugin_basename( __FILE__ ) ) ;
      load_plugin_textdomain( 'loginpress-login-widget', false, $plugin_dir . '/languages/' );
		}

		/**
		 * _widget_script function.
		 *
		 * @access public
		 * @return void
		 */
		public function _widget_script() {

			// Enqueue LoginPress Widget JS
			wp_enqueue_script( 'loginpress-login-widget-script', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), $this->version, false );

			// Enqueue Styles
			wp_enqueue_style( 'loginpress-login-widget-style', plugins_url( 'assets/css/style.css', __FILE__ ), '', $this->version );

			$loginpress_widget_option = get_option( 'widget_loginpress-login-widget' );
			$_loginpress_widget_option = isset( $loginpress_widget_option ) ? $loginpress_widget_option : false;
			if ( $_loginpress_widget_option ) {
				$error_bg_color = isset( $loginpress_widget_option[2]['error_bg_color'] ) ? $loginpress_widget_option[2]['error_bg_color'] : '#fbb1b7';

				$error_text_color = isset( $loginpress_widget_option[2]['error_text_color'] ) ? $loginpress_widget_option[2]['error_text_color'] : '#ae121e';// fbb1b7

				$_loginpress_widget_error_bg_clr = "
                .loginpress-login-widget .loginpress_widget_error{
                  background-color: {$error_bg_color};
                  color: {$error_text_color};
                }";
				wp_add_inline_style( 'loginpress-login-widget-style', $_loginpress_widget_error_bg_clr );
			}

			$loginpress_key = get_option( 'loginpress_customization' ) ?: array();

			$invalid_usrname = array_key_exists( 'incorrect_username', $loginpress_key ) && ! empty( $loginpress_key['incorrect_username'] ) ? $loginpress_key['incorrect_username'] : sprintf( __( '%1$sError:%2$s Invalid Username.', 'loginpress-login-widget' ), '<strong>', '</strong>' );

			$invalid_pasword = array_key_exists( 'incorrect_password', $loginpress_key ) && ! empty( $loginpress_key['incorrect_password'] ) ? $loginpress_key['incorrect_password'] : sprintf( __( '%1$sError:%2$s Invalid Password.', 'loginpress-login-widget' ), '<strong>', '</strong>' );

			$empty_username = array_key_exists( 'empty_username', $loginpress_key ) && ! empty( $loginpress_key['empty_username'] ) ? $loginpress_key['empty_username'] : sprintf( __( '%1$sError:%2$s The username field is empty.', 'loginpress-login-widget' ), '<strong>', '</strong>' );

			$empty_password = array_key_exists( 'empty_password', $loginpress_key ) && ! empty( $loginpress_key['empty_password'] ) ? $loginpress_key['empty_password'] : sprintf( __( '%1$sError:%2$s The password field is empty.', 'loginpress-login-widget' ), '<strong>', '</strong>' );

			$invalid_email   = array_key_exists( 'invalid_email', $loginpress_key ) && ! empty( $loginpress_key['invalid_email'] ) ? $loginpress_key['invalid_email'] : sprintf( __( '%1$sError:%2$s The email address isn\'t correct..', 'loginpress-login-widget' ), '<strong>', '</strong>' );

			// Pass variables
			$loginpress_widget_params = array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'force_ssl_admin'  => force_ssl_admin() ? 1 : 0,
				'is_ssl'           => is_ssl() ? 1 : 0,
				'empty_username'   => $empty_username,
				'empty_password'   => $empty_password,
				'invalid_username' => $invalid_usrname,
				'invalid_password' => $invalid_pasword,
				'invalid_email'    => $invalid_email,
			);

			wp_localize_script( 'loginpress-login-widget-script', 'loginpress_widget_params', $loginpress_widget_params );

			wp_enqueue_style( 'loginpress-social-login', plugins_url( 'loginpress-social-login/assets/css/login.css', __DIR__ ), array(), LOGINPRESS_SOCIAL_VERSION );
		}

		function register_widget() {
			include_once( LOGINPRESS_WIDGET_DIR_PATH . 'classes/class-loginpress-widget.php' );
		}

		/**
		 * Define LoginPress AutoLogin Constants
		 */
		private function define_constants() {

			$this->define( 'LOGINPRESS_WIDGET_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'LOGINPRESS_WIDGET_DIR_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'LOGINPRESS_WIDGET_DIR_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'LOGINPRESS_WIDGET_ROOT_PATH',  dirname( __FILE__ ) . '/' );
			$this->define( 'LOGINPRESS_WIDGET_ROOT_FILE', __FILE__ );
			$this->define( 'LOGINPRESS_WIDGET_VERSION', $this->version );
		}

		/**
		 * Load JS or CSS files at admin side and enqueue them
		 *
		 * @param  string tell you the Page ID
		 * @return void
		 */
		function _admin_scripts( $hook ) {

			wp_enqueue_style( 'loginpress_widget_stlye', plugins_url( 'assets/css/style.css', __FILE__ ), array(), LOGINPRESS_WIDGET_VERSION );

			wp_enqueue_script( 'loginpress_widget_js', plugins_url( 'assets/js/autologin.js', __FILE__ ), array( 'jquery', 'jquery-blockui' ), LOGINPRESS_WIDGET_VERSION );

		}

		/**
		 * Define constant if not already set
		 *
		 * @param  string      $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * loginpress_widget_ajax function.
		 *
		 * @access public
		 * @return void
		 */
		public function loginpress_widget_ajax() {

			$data                  = array();
			$data['user_login']    = stripslashes( trim( $_POST['user_login'] ) );
			$data['user_password'] = stripslashes( trim( $_POST['user_password'] ) );
			$data['remember']      = isset( $_POST['remember'] ) ? sanitize_text_field( $_POST['remember'] ) : '';
			$redirect_to            = esc_url_raw( $_POST['redirect_to'] );
			$secure_cookie          = null;

			// If the user wants ssl but the session is not ssl, force a secure cookie.
			if ( ! force_ssl_admin() ) {
				$user = is_email( $data['user_login'] ) ? get_user_by( 'email', $data['user_login'] ) : get_user_by( 'login', sanitize_user( $data['user_login'] ) );

				if ( $user && get_user_option( 'use_ssl', $user->ID ) ) {
					$secure_cookie = true;
					force_ssl_admin( true );
				}
			}

			if ( force_ssl_admin() ) {
				$secure_cookie = true;
			}

			if ( is_null( $secure_cookie ) && force_ssl_admin() ) {
				$secure_cookie = false;
			}

			// Login
			$user = wp_signon( $data, $secure_cookie );

			// Redirect filter
			if ( $secure_cookie && strstr( $redirect_to, 'wp-admin' ) ) {
				$redirect_to = str_replace( 'http:', 'https:', $redirect_to );
			}

			$response = array();

			if ( ! is_wp_error( $user ) ) {

				$response['success']  = 1;
				$response['redirect'] = $redirect_to;
			} else {

				$response['success'] = 0;
				if ( $user->errors ) {

					foreach ( $user->errors as $key => $error ) {

						$response[ $key ] = $error[0];
						break;
					}
				} else {

					$response['error'] = __( 'Please enter your username and password to login.', 'loginpress-login-widget' );
				}
			}

			echo json_encode( $response );

			wp_die();
		}
	}
endif;



/**
* Check if LoginPress Pro is install and active.
*
* @since 1.0.0
*/
function lp_lw_instance() {

  if ( ! file_exists( WP_PLUGIN_DIR . '/loginpress-pro/loginpress-pro.php' ) ) {
    add_action( 'admin_notices' , 'lp_lw_install_pro' );
    return;
  }

  if ( ! class_exists( 'LoginPress_Pro' ) ) {
    add_action( 'admin_notices', 'lp_lw_activate_pro' );
    return;
  }

  // Call the function
	new LoginPress_Login_Widget();
}

add_action( 'plugins_loaded', 'lp_lw_instance', 25 );


/**
* Notice if LoginPress Pro is not install.
*
* @since 1.0.0
*/
function lp_lw_install_pro() {
  $class = 'notice notice-error is-dismissible';
  $message = __( 'Please Install LoginPress Pro to use "LoginPress Login Widget" add-on.', 'loginpress-login-widget' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

/**
* Notice if LoginPress Pro is not activate.
*
* @since 1.0.0
*/
function lp_lw_activate_pro() {

  $action = 'activate';
  $slug   = 'loginpress-pro/loginpress-pro.php';
  $link   = wp_nonce_url( add_query_arg( array( 'action' => $action, 'plugin' => $slug ), admin_url( 'plugins.php' ) ), $action . '-plugin_' . $slug );

  printf('<div class="notice notice-error is-dismissible">
  <p>%1$s<a href="%2$s" style="text-decoration:none">%3$s</a></p></div>' , esc_html__( 'LoginPress Login Widget required LoginPress Pro activation &mdash; ', 'loginpress-login-widget' ), $link, esc_html__( 'Click here to activate LoginPress Pro', 'loginpress-login-widget' ) );
}
