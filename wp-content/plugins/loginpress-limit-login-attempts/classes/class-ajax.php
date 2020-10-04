<?php
if ( ! defined( 'ABSPATH' ) ) {
  // Exit if accessed directly.
  exit;
}

/**
* Handling all the AJAX calls in LoginPress - Limit Login Attempts.
*
* @since 1.0.0
* @version 1.0.1
* @class LoginPress_Attempts_AJAX
*/

if ( ! class_exists( 'LoginPress_Attempts_AJAX' ) ) :

  class LoginPress_Attempts_AJAX {

    /* * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
    public function __construct() {

      $this::init();
    }
    public static function init() {

      $ajax_calls = array(
        'attempts_whitelist' => false,
        'attempts_blacklist' => false,
        'attempts_unlock'    => false,
        'whitelist_clear'    => false,
        'blacklist_clear'    => false,
      );

      foreach ( $ajax_calls as $ajax_call => $no_priv ) {
        // code...
        add_action( 'wp_ajax_loginpress_' . $ajax_call, array( __CLASS__, $ajax_call ) );

        if ( $no_priv ) {
          add_action( 'wp_ajax_nopriv_loginpress_' . $ajax_call, array( __CLASS__, $ajax_call ) );
        }
      }
    }

    public function attempts_whitelist() {

      check_ajax_referer( 'loginpress-user-llla-nonce' , 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No cheating, huh!' );
      }

      global $wpdb;
      $table = "{$wpdb->prefix}loginpress_limit_login_details";
      $id    = $_POST['id'];
      $ip    = $_POST['ip'];

      $wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET whitelist = '1' WHERE ip = %s", $ip ) );

      wp_die();

    }

    public function attempts_blacklist() {

      check_ajax_referer( 'loginpress-user-llla-nonce' , 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No cheating, huh!' );
      }

      global $wpdb;
      $table = "{$wpdb->prefix}loginpress_limit_login_details";
      $id    = $_POST['id'];
      $ip    = $_POST['ip'];

      $wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET blacklist = '1' WHERE ip = %s", $ip ) );

      wp_die();
    }

    public function attempts_unlock() {

      check_ajax_referer( 'loginpress-user-llla-nonce' , 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No cheating, huh!' );
      }

      global $wpdb;
      $table = "{$wpdb->prefix}loginpress_limit_login_details";
      $id = $_POST['id'];
      $ip = $_POST['ip'];

      $wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE `ip` = %s", $ip ) );
      
      wp_die();
    }

    public function whitelist_clear() {

      check_ajax_referer( 'loginpress-user-llla-nonce' , 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No cheating, huh!' );
      }

      global $wpdb;
      $table = "{$wpdb->prefix}loginpress_limit_login_details";
      $id = $_POST['id'];
      $ip = $_POST['ip'];


      $wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE `ip` = %s", $ip ) );
      echo "Whitelist User Deleted";

      wp_die();
    }

    /**
     *  Blacklist clear button Delete matched ip rows.
     * @return [type] [description]
     */
    public function blacklist_clear() {

      check_ajax_referer( 'loginpress-user-llla-nonce' , 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No cheating, huh!' );
      }

      global $wpdb;
      $table = "{$wpdb->prefix}loginpress_limit_login_details";
      $id = $_POST['id'];
      $ip = $_POST['ip'];

      $wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE `ip` = %s", $ip ) );

      echo "Blacklist User is Deleted";

      wp_die();
    }

  }

endif;
new LoginPress_Attempts_AJAX();
