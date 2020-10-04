<?php

/**
 * Plugin Name: LoginPress - Hide Login
 * Plugin URI: http://www.WPBrigade.com/wordpress/plugins/hidelogin/
 * Description: LoginPress is the best <code>wp-login</code> Hide Login plugin by <a href="https://wpbrigade.com/">WPBrigade</a> which allows you to hide the wp-login.php.
 * Version: 1.1.0
 * Author: WPBrigade
 * Author URI: http://www.WPBrigade.com/
 * Text Domain: loginpress-hide-login
 * Domain Path: /languages
 *
 * @package loginpress
 * @category Core
 * @author WPBrigade
 */

define( 'LOGINPRESS_HIDE_ROOT_PATH', dirname( __FILE__ ) );
define( 'LOGINPRESS_HIDE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'LOGINPRESS_HIDE_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'LOGINPRESS_HIDE_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'LOGINPRESS_HIDE_ROOT_FILE', __FILE__ );

define( 'LOGINPRESS_HIDE_STORE_URL', 'https://WPBrigade.com' );
define( 'LOGINPRESS_HIDE_VERSION', '1.1.0' );

add_action( 'plugins_loaded', 'loginpress_hidelogin_instance', 25 );

function loginpress_hidelogin_instance() {

		if ( ! file_exists( WP_PLUGIN_DIR . '/loginpress-pro/loginpress-pro.php' ) ) {
		add_action( 'admin_notices' , 'lp_hl_install_pro' );
		return;
	}

	if ( ! class_exists( 'LoginPress_Pro' ) ) {
		add_action( 'admin_notices', 'lp_hl_activate_pro' );
		return;
	}


	// Makes sure the plugin is defined before trying to use it
	if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'is_plugin_active' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}


	if ( is_plugin_active_for_network( 'rename-wp-login/rename-wp-login.php' ) || is_plugin_active_for_network( 'wps-hide-login/wps-hide-login.php' ) ) {
		deactivate_plugins( LOGINPRESS_HIDE_PLUGIN_BASENAME );
		add_action( 'network_admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		return;
	}


	if ( is_plugin_active( 'rename-wp-login/rename-wp-login.php' ) || is_plugin_active( 'wps-hide-login/wps-hide-login.php' ) ) {
		deactivate_plugins( LOGINPRESS_HIDE_PLUGIN_BASENAME );
		add_action( 'admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		return;
	}

	// Call the function
	loginPress_hidelogin_loader();
}


/**
 * Returns the main instance of WP to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object LoginPress_HideLogin_Main
 */
function loginPress_hidelogin_loader() {
	 include_once LOGINPRESS_HIDE_ROOT_PATH . '/classes/class-loginpress-hidelogin.php';
	 return LoginPress_HideLogin_Main::instance();
}


function admin_notices_plugin_conflict() {

	echo '<div class="error notice is-dismissible"><p>' . __( 'LoginPress Hide Login could not be activated because you already have active . Please uninstall Login Rename Plugin to use LoginPress Hide Login', 'loginpress-hide-login' ) . '</p></div>';

}

/**
* Notice if LoginPress Pro is not install.
*
* @since 1.0.0
*/
function lp_hl_install_pro() {
  $class = 'notice notice-error is-dismissible';
  $message = __( 'Please Install LoginPress Pro to use "LoginPress Hide Login" add-on.', 'loginpress-hide-login' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

/**
* Notice if LoginPress Pro is not activate.
*
* @since 1.0.0
*/
function lp_hl_activate_pro() {

  $action = 'activate';
  $slug   = 'loginpress-pro/loginpress-pro.php';
  $link   = wp_nonce_url( add_query_arg( array( 'action' => $action, 'plugin' => $slug ), admin_url( 'plugins.php' ) ), $action . '-plugin_' . $slug );

  printf('<div class="notice notice-error is-dismissible">
  <p>%1$s<a href="%2$s" style="text-decoration:none">%3$s</a></p></div>' , esc_html__( 'LoginPress Hide Login required LoginPress Pro activation &mdash; ', 'loginpress-hide-login' ), $link, esc_html__( 'Click here to activate LoginPress Pro', 'loginpress-hide-login' ) );
}
