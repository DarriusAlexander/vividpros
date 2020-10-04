<?php
/**
* Plugin Name: LoginPress - Social Login
* Plugin URI: https://www.WPBrigade.com/wordpress/plugins/loginpress/
* Description: This is a premium add-on of LoginPress WordPress plugin by <a href="https://wpbrigade.com/">WPBrigade</a> which allows you to login using social media accounts like Facebook, Twitter and Google/G+ etc
* Version: 1.0.5
* Author: WPBrigade
* Author URI: https://www.WPBrigade.com/
* Text Domain: loginpress-social-login
* Domain Path: /languages
*
* @package loginpress
* @category Core
* @author WPBrigade
*/

if ( ! class_exists( 'LoginPress_Social' ) ) :

  final class LoginPress_Social {

    /**
    * @var string
    */
    public $version = '1.0.5';

    /**
    * @var The single instance of the class
    * @since 1.0.0
    */
    protected static $_instance = null;

    /*
    * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
    public function __construct() {
      $this->settings = get_option( 'loginpress_social_logins' );
      $this->define_constants();
      $this->_hooks();

    }

    public $settings;
    /**
    * Define LoginPress Constants
    */
    private function define_constants() {

      $this->define( 'LOGINPRESS_SOCIAL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
      $this->define( 'LOGINPRESS_SOCIAL_DIR_PATH', plugin_dir_path( __FILE__ ) );
      $this->define( 'LOGINPRESS_SOCIAL_DIR_URL', plugin_dir_url( __FILE__ ) );
      $this->define( 'LOGINPRESS_SOCIAL_ROOT_PATH', dirname( __FILE__ ) . '/' );
      $this->define( 'LOGINPRESS_SOCIAL_VERSION', $this->version );
      $this->define( 'LOGINPRESS_SOCIAL_FEEDBACK_SERVER', 'https://wpbrigade.com/' );
    }

    /**
    * Hook into actions and filters
    *
    * @since  1.0.0
    */
    private function _hooks() {

      $enable   = isset( $this->settings['enable_social_login_links'] ) ? $this->settings['enable_social_login_links'] : '';
      $login    = isset( $enable['login'] ) ? 'login' : '';
      $register = isset( $enable['register'] ) ? 'register' : '';

      if ( 'login' == $login ) {
        add_action( 'login_form', array( $this, 'loginpress_social_login' ) );
      }
      if ( 'register' == $register ) {
        add_action( 'register_form', array( $this, 'loginpress_social_login' ) );
      }
      add_action( 'plugins_loaded', array( $this, 'textdomain' ) );
      add_filter( 'plugin_row_meta', array( $this, '_row_meta' ), 10, 2 );
      add_action( 'init', array( $this, 'session_init' ) );
      add_action( 'admin_init', array( $this, 'init_addon_updater' ), 0 );
      add_filter( 'loginpress_settings_tab', array( $this, 'settings_tab' ), 15 );
      add_filter( 'loginpress_settings_fields', array( $this, 'settings_field' ), 10 );
      add_action( 'loginpress_social_login_help_tab_script', array( $this, 'loginpress_social_login_help_tab_callback' ) );
      add_action( 'delete_user', array( $this, 'delete_user_row' ) );

      add_action( 'admin_enqueue_scripts', array( $this, 'loginpress_social_login_admin_action_scripts' ) );
      add_action( 'login_enqueue_scripts', array( $this, 'load_login_assets' ) );

    }

    /**
    * LoginPress Addon updater
    */
    public function init_addon_updater() {
      if ( class_exists( 'LoginPress_AddOn_Updater' ) ) {
        // echo 'Exists';
        $updater = new LoginPress_AddOn_Updater( 2335, __FILE__, $this->version );
      }
    }

    public function settings_field( $setting_array ) {

      $_new_tabs = array(
        array(
          'name'  => 'facebook',
          'label' => __( 'Facebook Login', 'loginpress-social-login' ),
          'desc'  => __( 'Enable Facebook Login', 'loginpress-social-login' ),
          'type'  => 'checkbox',
        ),
        array(
          'name'  => 'facebook_app_id',
          'label' => __( 'Facebook App ID', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter your facebook App ID.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'facebook_app_secret',
          'label' => __( 'Facebook App Secret', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter your facebook App Secret.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'twitter',
          'label' => __( 'Twitter Login', 'loginpress-social-login' ),
          'desc'  => __( 'Enable Twitter Login', 'loginpress-social-login' ),
          'type'  => 'checkbox',
        ),
        array(
          'name'  => 'twitter_oauth_token',
          'label' => __( 'Twitter API key', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter Your Consumer API key.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'twitter_token_secret',
          'label' => __( 'Twitter API secret key', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter Your Consumer API secret key.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'twitter_callback_url',
          'label' => __( 'Twitter Callback URL', 'loginpress-social-login' ),
          'desc'  => __( 'Enter Your Callback URL ' . wp_login_url(), 'loginpress-social-login' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'gplus',
          'label' => __( 'Google Login', 'loginpress-social-login' ),
          'desc'  => __( 'Enable Google Login', 'loginpress-social-login' ),
          'type'  => 'checkbox',
        ),
        array(
          'name'  => 'gplus_client_id',
          'label' => __( 'Client ID', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter Your Client ID.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'gplus_client_secret',
          'label' => __( 'Client Secret', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter Your Client Secret.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'gplus_redirect_uri',
          'label' => __( 'Redirect URI', 'loginpress-social-login' ),
          'desc'  => __( 'Enter Your Redirect URI:' . wp_login_url() . '?lpsl_login_id=gplus_login', 'loginpress-social-login' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'linkedin',
          'label' => __( 'LinkedIn Login', 'loginpress-social-login' ),
          'desc'  => __( 'Enable LinkedIn Login', 'loginpress-social-login' ),
          'type'  => 'checkbox',
        ),
        array(
          'name'  => 'linkedin_client_id',
          'label' => __( 'Client ID', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter Your Client ID.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'linkedin_client_secret',
          'label' => __( 'Client Secret', 'loginpress-social-login' ),
          'desc'  => sprintf( __( 'Enter Your Client Secret.', 'loginpress-social-login' ), '<a href="https://wpbrigade.com/">', '</a>' ),
          'type'  => 'text',
        ),
        array(
          'name'  => 'linkedin_redirect_uri',
          'label' => __( 'Redirect URI', 'loginpress-social-login' ),
          'desc'  => __( 'Enter Your Redirect URI: ' . wp_login_url() . '?lpsl_login_id=linkedin_login', 'loginpress-social-login' ),
          'type'  => 'text',
        ),
        array(
          'name'    => 'enable_social_login_links',
          'label'   => __( 'Enable Social Login on', 'loginpress-social-login' ),
          'desc'    => __( 'Enable Social Login on Login and Register form', 'loginpress-social-login' ),
          'type'    => 'multicheck',
          'default' => array( 'login' => 'login' ),
          'options' => array(
            'login'    => 'Login Form',
            'register' => 'Register Form',
          ),
        ),
        array(
          'name'  => 'delete_user_data',
          'label' => __( 'Remove Record On Uninstall', 'loginpress-social-login' ),
          'desc'  => __( 'This tool will remove all LoginPress - Social Logins record upon uninstall.', 'loginpress-social-login' ),
          'type'  => 'checkbox',
        ),
      );

      $_new_tabs = array( 'loginpress_social_logins' => $_new_tabs );
      return( array_merge( $_new_tabs, $setting_array ) );
    }

    function loginpress_social_login_admin_action_scripts( $hook ) {

      if ( 'toplevel_page_loginpress-settings' == $hook ) {

        wp_enqueue_style( 'loginpress-admin-social-login', plugins_url( 'assets/css/style.css', __FILE__ ), array(), LOGINPRESS_SOCIAL_VERSION );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'loginpress-admin-social-login', plugins_url( 'assets/js/main.js', __FILE__ ), false, LOGINPRESS_SOCIAL_VERSION );
      }
    }

    function settings_tab( $loginpress_tabs ) {
      $new_tab = array(
        array(
          'id'    => 'loginpress_social_logins',
          'title' => __( 'Social Login', 'loginpress' ),
          'desc'  => sprintf( __( '%1$s%3$sSettings%4$s %5$sHelp%4$s%2$s', 'loginpress-social-login' ), '<div class="loginpress-social-login-tab-wrapper">', '</div>', '<a href="#loginpress_social_login_settings" class="loginpress-social-login-tab loginpress-social-login-active">', '</a>', '<a href="#loginpress_social_login_help" class="loginpress-social-login-tab">' ),
        ),
      );
      return array_merge( $loginpress_tabs, $new_tab );

    }

    function loginpress_social_login_help_tab_callback() {

      $html  = '<div id="loginpress_social_login_help" class="display">';
      $html .= '<div class="loginpress-social-accordions">';
      $html .= '<a href="#loginpress-facebook-login" class="loginpress-accordions">Facebook Login <span class="dashicons dashicons-arrow-down loginpress-arrow"></span></a>';
      $html .= '<div class="loginpress-social-tabs" id="loginpress-facebook-login">
      <h2>Let\'s integrate Facebook login with LoginPress Social Login.</h2>
      <p>Following are the steps to Create an app on Facebook to use Facebook Login in a web application.</p>
      <h4>Step 1:</h4>
      <ul>
      <li>1.1 Go to <a href="https://developers.facebook.com/" target="_blank">Facebook Developers</a> section and login to your Facebook account, if you are not logged in already. This should not be your business account.</li>
      <li>1.2 If you are here (at Facebook Developer section) first time, you will require to “Register as a Developer” (If you have already done this, skip this step and move to step 2). Click “Register Now” button.</li>
      <li>1.3 Fill the form. After completing the form New App will be generated.</li>
      </ul>
      <h4>Step 2:</h4>
      <ul>
      <li>2.1 Click Add a new App from My app and fill out the required informational fields if you already Register.</li>
      <li>2.2 After you have created the App ID please select a product type here. In our case, we use Facebook login.</li>
      <li>2.3 Select the platform for this app: Here we use "web".</li>
      <li>2.4 Enter your web URL <strong>' . esc_html( site_url() ) . '</strong> and save the settings.</li>
      </ul>
      <h4>Step 3:</h4>
      <ul>
      <li>3.1 Go to settings &gt; Basic from the left side menu.</li>
      <li>3.2 Here you find the App ID and App Secret.</li>
      <li>3.3 Copy that ID and Secret and use it in plugin settings.</li>
      <li>3.4 Enter your contact email and privacy policy URL(Required).</li>
      <li>3.5 Save the settings.</li>
      </ul>
      <h4>Step 4:</h4>
      <ul>
      <li>4.1 Go to Facebook Login &gt; Settings from left side menu.</li>
      <li>4.2 There Please set the <b>Use Strict Mode for Redirect URIs</b> as Yes.</li>
      <li>4.3 Add valid OAuth redirect URIs here:
      <li>&nbsp;&nbsp;&nbsp;&nbsp;4.3.1 <strong>' . esc_html( wp_login_url() . '?lpsl_login_id=facebook_check' ) . '</strong></li>
      <li>&nbsp;&nbsp;&nbsp;&nbsp;4.3.2 <strong>' . esc_html( site_url( ) . '/admin.php?lpsl_login_id=facebook_check' ) . '</strong></li>
      </li>
      <li>4.4 Save the settings.</li>
      </ul>
      <h4>Step 5:</h4>
      <ul>
      <li>5.1 Final Step make this App public. For this, You just need to slide the checkbox that you see on topbar.</li>
      <li>5.2 After that select the category and press confirm button.</li>
      <li>5.3 Save the settings and enjoy it.</li>
      </ul>
      </div></div>';
      $html .= '<div class="loginpress-social-accordions">';
      $html .= '<a href="#loginpress-facebook-login" class="loginpress-accordions">Twitter Login <span class="dashicons dashicons-arrow-down loginpress-arrow"></span></a>';
      $html .= '<div class="loginpress-social-tabs" id="loginpress-twitter-login">
      <h2>Let\'s integrate Twitter login with LoginPress Social Login.</h2>
      <p>Following are the steps to create an app on Twitter to use Twitter Login in a web application.</p>
      <h4>Step 1:</h4>
      <ul>
      <li>1.1 You must register your website with Twitter at <a href="https://apps.twitter.com/" target="_blank">https://apps.twitter.com/</a>.</li>
      <li>1.2 Click on Create New App Button and fill out the required informational fields.</li>
      <li>1.3 Website URL: <strong>' . esc_html( site_url() ) . '</strong></li>
      <li>1.4 Callback URL: <strong>' . esc_html( wp_login_url() ) . '</strong></li>
      <li>1.4 Click on "Create your twitter application".</li>
      </ul>
      <h4>Step 2:</h4>
      <ul>
      <li>2.1 Go to “Keys and access token” tab.</li>
      <li>2.2 Copy that Key and Token and use it in plugin settings.</li>
      <li>2.3 Save the settings and enjoy.</li>
      </ul>
      </div></div>';
      $html .= '<div class="loginpress-social-accordions">';
      $html .= '<a href="#loginpress-facebook-login" class="loginpress-accordions">Google login <span class="dashicons dashicons-arrow-down loginpress-arrow"></span></a>';
      $html .= '<div class="loginpress-social-tabs" id="loginpress-gplus-login">
      <h2>Let\'s integrate Google login with LoginPress Social Login.</h2>
      <p>Following are the steps to Create an app on Google to use Google Login in a web application.</p>
      <h4>Step 1:</h4>
      <ul>
      <li>1.1 You must register your website with Google at <a href="https://console.developers.google.com/" target="_blank">https://console.developers.google.com/</a>.</li>
      <li>1.2 Click on Create Project and fill out the required informational fields.</li>
      <li>1.3 Click on credentials from the left side menu and create credentials here.</li>
      </ul>
      <h4>Step 2:</h4>
      <ul>
      <li>2.1 Go to OAuth consent screen tab</li>
      <li>2.2 Fill out the required informational fields save the settings.</li>
      <li>2.3 Go to Credentials tabs and click on OAuth Client ID from Create Credential dropdown.</li>
      <li>2.4 Select Application Type: in our case, we use "Web Application" and fill out the required informational fields.</li>
      <li>2.5 Authorized redirect URIs: <strong>' . esc_html( wp_login_url() . '?lpsl_login_id=gplus_login' ) . '</strong></li>
      <li>2.6 Save the settings.</li>
      </ul>
      <h4>Step 3:</h4>
      <ul>
      <li>3.1 After saving the settings a popup will occur with OAuth Client Copy the Client ID and Client Secret from here and use it in plugin setting.</li>
      <li>3.2 Save the settings and enjoy.</li>
      </ul>
      </div></div>';
      $html .= '<div class="loginpress-social-accordions">';
      $html .= '<a href="#loginpress-facebook-login" class="loginpress-accordions">Linkedin login <span class="dashicons dashicons-arrow-down loginpress-arrow"></span></a>';
      $html .= '<div class="loginpress-social-tabs" id="loginpress-linkedin-login">
      <h2>Let\'s integrate Linkedin login with LoginPress Social Login.</h2>
      <p>Following are the steps to create an app on Linkedin to use Linkedin Login in a web application.</p>
      <h4>Step 1:</h4>
      <ul>
      <li>1.1 You must register your website with Linkedin at <a href="https://developer.linkedin.com/" target="_blank">https://developer.linkedin.com/</a>.</li>
      <li>1.2 Click on My Apps then Create Application and fill out the required informational fields.</li>
      <li>1.3 Authorized Redirect URLs: <strong>' . esc_html( wp_login_url() . '?lpsl_login_id=linkedin_login' ) . '</strong></li>
      </ul>
      <h4>Step 2:</h4>
      <ul>
      <li>2.1 Copy that Client ID &amp; Client Secret and use it in plugin settings.</li>
      <li>2.2 Save the settings and enjoy.</li>
      </ul>
      </div></div>';
      $html .= '</div>';
      echo $html;
    }


    /**
    * Main Instance
    *
    * @since 1.0.0
    * @static
    * @see loginPress_social_loader()
    * @return Main instance
    */
    public static function instance() {
      if ( is_null( self::$_instance ) ) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }


    /**
    * Load Languages
    *
    * @since 1.0.0
    */
    public function textdomain() {

      $plugin_dir = dirname( plugin_basename( __FILE__ ) );
      load_plugin_textdomain( 'loginpress-social-login', false, $plugin_dir . '/languages/' );
    }

    // starts the session with the call of init hook
    function session_init() {

      if ( ! session_id() && ! headers_sent() ) {
        session_start();
      }
      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'classes/loginpress-social-check.php';
    }

    public function loginpress_social_login() {

      $redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

      $encoded_url = urlencode( $redirect_to );
      ?>

      <div class='social-networks block'>
        <span class="social-sep"><span><?php _e( 'or', 'loginpress-social-login' ); ?></span></span>

        <?php if ( isset( $this->settings['gplus'] ) && $this->settings['gplus'] == 'on' && ! empty( $this->settings['gplus_client_id'] ) && ! empty( $this->settings['gplus_client_secret'] ) ) : ?>
          <a href="<?php echo wp_login_url(); ?>?lpsl_login_id=gplus_login
            <?php
            if ( $encoded_url ) {
              echo '&state=' . base64_encode( "redirect_to=$encoded_url" );
            }
            ?>
            " title='
            <?php
            _e( 'Login with Google', 'loginpress-social-login' );
            ?>
            ' >
            <div class="lpsl-icon-block icon-google-plus clearfix">

              <span class="lpsl-login-text"><?php _e( 'Login with Google', 'loginpress-social-login' ); ?></span>
              <i class="fa fa-google"></i>
            </div>
          </a>
        <?php endif; ?>

        <?php if ( isset( $this->settings['facebook'] ) && $this->settings['facebook'] == 'on' && ! empty( $this->settings['facebook_app_id'] ) && ! empty( $this->settings['facebook_app_secret'] ) ) : ?>
          <a href="<?php echo wp_login_url(); ?>?lpsl_login_id=facebook_login
            <?php
            if ( $encoded_url ) {
              echo '&state=' . base64_encode( "redirect_to=$encoded_url" );
            }
            ?>
            " title='
            <?php
            _e( 'Login with Facebook', 'loginpress-social-login' );
            ?>
            ' >
            <div class="lpsl-icon-block icon-facebook clearfix">

              <span class="lpsl-login-text"><?php _e( 'Login with Facebook', 'loginpress-social-login' ); ?></span>
              <i class="fa fa-facebook"></i>
            </div>
          </a>
        <?php endif; ?>

        <?php if ( isset( $this->settings['twitter'] ) && $this->settings['twitter'] == 'on' && ! empty( $this->settings['twitter_oauth_token'] ) && ! empty( $this->settings['twitter_token_secret'] ) ) : ?>
          <a href="<?php echo wp_login_url(); ?>?lpsl_login_id=twitter_login
            <?php
            if ( $encoded_url ) {
              echo '&state=' . base64_encode( "redirect_to=$encoded_url" );
            }
            ?>
            " title='
            <?php
            _e( 'Login with Twitter', 'loginpress-social-login' );
            ?>
            ' >
            <div class="lpsl-icon-block icon-twitter clearfix">

              <span class="lpsl-login-text"><?php _e( 'Login with Twitter', 'loginpress-social-login' ); ?></span>
              <i class="fa fa-twitter"></i>
            </div>
          </a>
        <?php endif; ?>

        <?php if ( isset( $this->settings['linkedin'] ) && $this->settings['linkedin'] == 'on' && ! empty( $this->settings['linkedin_client_id'] ) && ! empty( $this->settings['linkedin_client_secret'] ) ) : ?>
          <a href="<?php echo wp_login_url(); ?>?lpsl_login_id=linkedin_login
            <?php
            if ( $encoded_url ) {
              echo '&state=' . base64_encode( "redirect_to=$encoded_url" );
            }
            ?>
            " title='
            <?php
            _e( 'Login with LinkedIn', 'loginpress-social-login' );
            ?>
            ' >
            <div class="lpsl-icon-block icon-linkdin clearfix">

              <span class="lpsl-login-text"><?php _e( 'Login with LinkedIn', 'loginpress-social-login' ); ?></span>
              <i class="fa fa-linkedin"></i>
            </div>
          </a>
        <?php endif; ?>
      </div>
      <?php
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
    * Define constant if not already set
    *
    * @param  array       $links
    * @param  string|bool $file
    */
    public function _row_meta( $links, $file ) {

      if ( strpos( $file, 'loginpress-social-login.php' ) !== false ) {

        // Set link for Reviews.
        $new_links = array(
          '<a href="https://wordpress.org/support/plugin/loginpress/reviews/?filter=5" target="_blank"><span class="dashicons dashicons-thumbs-up"></span> ' . __( 'Vote!', 'loginpress-social-login' ) . '</a>',
        );

        $links = array_merge( $links, $new_links );
      }

      return $links;
    }

    /**
    * Delete user row form the table.
    *
    * @since 1.0.0
    */
    function delete_user_row( $user_id ) {
      global $wpdb;

      $sql = "DELETE FROM `{$wpdb->prefix}loginpress_social_login_details` WHERE `user_id` = '$user_id'";
      $wpdb->query( $sql );
    }


    /**
    * Plugin activation for check multi site activation
    *
    * @since 1.0.5
    */
    static function loginpress_social_activation( $network_wide ) {
      if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
        global $wpdb;
        // Get this so we can switch back to it later
        $current_blog = $wpdb->blogid;
        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col(  "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {
          switch_to_blog( $blog_id );
          self::loginpress_social_create_table();
        }
        switch_to_blog( $current_blog );
        return;
      } else {
        self::loginpress_social_create_table(); // normal acticvation
      }
    }

    /**
    * Create DB table on plugin activation.
    *
    * @since 1.0.0
    * @version 1.0.5
    */
    static function loginpress_social_create_table() {

      global $wpdb;
      // create user details table
      $table_name = "{$wpdb->prefix}loginpress_social_login_details";

      $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        provider_name varchar(50) NOT NULL,
        identifier varchar(255) NOT NULL,
        sha_verifier varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        email_verified varchar(255) NOT NULL,
        first_name varchar(150) NOT NULL,
        last_name varchar(150) NOT NULL,
        profile_url varchar(255) NOT NULL,
        website_url varchar(255) NOT NULL,
        photo_url varchar(255) NOT NULL,
        display_name varchar(150) NOT NULL,
        description varchar(255) NOT NULL,
        gender varchar(10) NOT NULL,
        language varchar(20) NOT NULL,
        age varchar(10) NOT NULL,
        birthday int(11) NOT NULL,
        birthmonth int(11) NOT NULL,
        birthyear int(11) NOT NULL,
        phone varchar(75) NOT NULL,
        address varchar(255) NOT NULL,
        country varchar(75) NOT NULL,
        region varchar(50) NOT NULL,
        city varchar(50) NOT NULL,
        zip varchar(25) NOT NULL,
        UNIQUE KEY id (id),
        KEY user_id (user_id),
        KEY provider_name (provider_name)
      )";
      $wpdb->query( $sql );
    }

    /**
    * Load assets on login screen.
    *
    * @since 1.0.0
    * @version 1.0.3
    */
    function load_login_assets() {

      wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
      wp_enqueue_style( 'loginpress-social-login', plugins_url( 'assets/css/login.css', __FILE__ ), array(), LOGINPRESS_SOCIAL_VERSION );
      wp_enqueue_script( 'loginpress-social-login', plugins_url( 'assets/js/custom.js', __FILE__ ), false, LOGINPRESS_SOCIAL_VERSION );
    }

  }
endif;

/**
* Returns the main instance of WP to prevent the need to use globals.
*
* @since  1.0.0
* @return LoginPress
*/
function loginPress_social_loader() {
  return LoginPress_Social::instance();
}


register_activation_hook( __FILE__, array( 'LoginPress_Social', 'loginpress_social_activation' ) );
add_action( 'wpmu_new_blog', array( 'LoginPress_Social', 'loginpress_social_activation' ) );

add_action( 'plugins_loaded', 'lp_sl_instance', 25 );

/**
* Check if LoginPress Pro is install and active.
*
* @since 1.0.0
*/
function lp_sl_instance() {

  if ( ! file_exists( WP_PLUGIN_DIR . '/loginpress-pro/loginpress-pro.php' ) ) {
    add_action( 'admin_notices', 'lp_sl_install_pro' );
    return;
  }

  if ( ! class_exists( 'LoginPress_Pro' ) ) {
    add_action( 'admin_notices', 'lp_sl_activate_pro' );
    return;
  }
  // Call the function
  loginPress_social_loader();
}

/**
* Notice if LoginPress Pro is not install.
*
* @since 1.0.0
*/
function lp_sl_install_pro() {
  $class   = 'notice notice-error is-dismissible';
  $message = __( 'Please Install LoginPress Pro to use "LoginPress Social Login" add-on.', 'loginpress-social-login' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

/**
* Notice if LoginPress Pro is not activate.
*
* @since 1.0.0
*/
function lp_sl_activate_pro() {

  $action = 'activate';
  $slug   = 'loginpress-pro/loginpress-pro.php';
  $link   = wp_nonce_url(
    add_query_arg(
      array(
        'action' => $action,
        'plugin' => $slug,
      ),
      admin_url( 'plugins.php' )
    ),
    $action . '-plugin_' . $slug
  );

  printf(
    '<div class="notice notice-error is-dismissible">
    <p>%1$s<a href="%2$s" style="text-decoration:none">%3$s</a></p></div>',
    esc_html__( 'The following required plugin is currently inactive &mdash; ', 'loginpress-social-login' ),
    $link,
    esc_html__( 'Click here to activate LoginPress Pro', 'loginpress-social-login' )
  );
}
