<?php

/**
 * Plugin Name: LoginPress - Limit Login Attempts
 * Plugin URI: http://www.WPBrigade.com/wordpress/plugins/limit-login-attempts/
 * Description: LoginPress - Limit Login Attempts is the best for <code>wp-login</code> Login Attemps plugin by <a href="https://wpbrigade.com/">WPBrigade</a> which allows you to restrict user attempts.
 * Version: 1.1.0
 * Author: WPBrigade
 * Author URI: http://www.WPBrigade.com/
 * Text Domain: loginpress-limit-login-attempts
 * Domain Path: /languages
 *
 * @package loginpress
 * @category Core
 * @author WPBrigade
 */

define( 'LOGINPRESS_LIMIT_LOGIN_ROOT_PATH', dirname( __FILE__ ) );
define( 'LOGINPRESS_LIMIT_LOGIN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'LOGINPRESS_LIMIT_LOGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'LOGINPRESS_LIMIT_LOGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'LOGINPRESS_LIMIT_LOGIN_ROOT_FILE', __FILE__ );

define( 'LOGINPRESS_LIMIT_LOGIN_STORE_URL', 'https://WPBrigade.com' );
define( 'LOGINPRESS_LIMIT_LOGIN_VERSION', '1.1.0' );

add_action( 'plugins_loaded', 'loginpress_limit_login_instance', 25 );
register_activation_hook( __FILE__ , 'loginpress_limit_login_activation' );
add_action( 'wpmu_new_blog', 'loginpress_limit_login_activation' );

function loginpress_limit_login_instance() {

   if ( ! file_exists( WP_PLUGIN_DIR . '/loginpress-pro/loginpress-pro.php' ) ) {
   add_action( 'admin_notices' , 'loginpress_limit_login_install_pro' );
   return;
 }

 if ( ! class_exists( 'LoginPress_Pro' ) ) {
   add_action( 'admin_notices', 'loginpress_limit_login_activate_pro' );
   return;
 }

 // Call the function
 loginPress_limit_login_loader();
}


/**
 * Returns the main instance of WP to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object LoginPress_Limit_Login_Attempts_Main
 */
function loginPress_limit_login_loader() {
  include_once LOGINPRESS_LIMIT_LOGIN_ROOT_PATH . '/classes/class-loginpress-limit-login-attempts.php';
  return LoginPress_Limit_Login_Attempts_Main::instance();
}

/**
* Notice if LoginPress Pro is not installed.
*
* @since 1.0.0
*/
function loginpress_limit_login_install_pro() {
  $class = 'notice notice-error is-dismissible';
  $message = __( 'Please Install LoginPress Pro to use "LoginPress - Limit Login Attempts" add-on.', 'loginpress-limit-login-attempts' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

/**
* Notice if LoginPress Pro is not activated.
*
* @since 1.0.0
*/
function loginpress_limit_login_activate_pro() {

  $action = 'activate';
  $slug   = 'loginpress-pro/loginpress-pro.php';
  $link   = wp_nonce_url( add_query_arg( array( 'action' => $action, 'plugin' => $slug ), admin_url( 'plugins.php' ) ), $action . '-plugin_' . $slug );

  printf( '<div class="notice notice-error is-dismissible">
  <p>%1$s<a href="%2$s" style="text-decoration:none">%3$s</a></p></div>' , esc_html__( 'LoginPress - Limit Login Attempts required LoginPress Pro activation &mdash; ', 'loginpress-limit-login-attempts' ), $link, esc_html__( 'Click here to activate LoginPress Pro', 'loginpress-limit-login-attempts' ) );
}



/**
 * Run some custom tasks on plugin activation
 * @since 1.0.1
 */
function loginpress_limit_login_activation( $network_wide ) {

  if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
    global $wpdb;
      // Get this so we can switch back to it later
      $current_blog = $wpdb->blogid;
      // Get all blogs in the network and activate plugin on each one
      $blog_ids = $wpdb->get_col(  "SELECT blog_id FROM $wpdb->blogs" );
    foreach ( $blog_ids as $blog_id ) {
      switch_to_blog( $blog_id );
      loginpress_limit_create_table();
    }
      switch_to_blog( $current_blog );
      return;
  } else {
    loginpress_limit_create_table(); // normal acticvation
  }

}
/**
 * Create Db table on plugin activation.
 *
 * @since 1.0.0
 * @version 1.0.1
 */
function loginpress_limit_create_table() {

  global $wpdb;
  // create user details table
  $table_name = "{$wpdb->prefix}loginpress_limit_login_details";

  $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
    id int(11) NOT NULL AUTO_INCREMENT,
    ip varchar(255) NOT NULL,
    username varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    datentime varchar(255) NOT NULL,
    gateway varchar(255) NOT NULL,
    whitelist int(11) NOT NULL,
    blacklist int(11) NOT NULL,
    UNIQUE KEY id (id)
  )";
  $wpdb->query( $sql );

  // Set default settings.
  if ( ! get_option( 'loginpress_limit_login_attempts' ) ) {
    update_option( 'loginpress_limit_login_attempts', array(
      'attempts_allowed' => 4,
      'minutes_lockout'  => 20
    ) );
  }

}
