<?php
defined( 'ABSPATH' ) or die( "No script kiddies please!" );

if( !class_exists( 'LoginPress_Social_Login_Check' ) ) {

  class LoginPress_Social_Login_Check {
    //constructor
    function __construct() {
      $this->loginpress_check();
      // echo "Request ";
      // var_dump($_REQUEST);
      // echo "<br />session ";
      // var_dump(get_option('loginpress_twitter_oauth'));
      $lp_twitter_oauth = get_option('loginpress_twitter_oauth');
      if ( isset( $lp_twitter_oauth["oauth_token"] ) && isset( $_REQUEST['oauth_verifier'] ) ) {

        $this->onTwitterLogin();
      }

    }

    function loginpress_check() {
      if( isset( $_GET['lpsl_login_id'] ) ) {
        $exploder = explode( '_', $_GET['lpsl_login_id'] );

        if ( 'facebook' == $exploder[0] ) {
          if( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
            _e( 'The Facebook SDK requires PHP version 5.4 or higher. Please notify about this error to site admin.', 'loginpress-social-login' );
            die();
          }
          $this->onFacebookLogin();
        } elseif ( 'twitter' == $exploder[0] ) {
          $this->onTwitterLogin();
        } elseif ( 'gplus' == $exploder[0] ) {
          $this->onGPlusLogin();
        } elseif ( 'linkedin' == $exploder[0]  ) {
          $this->onLinkedIdLogin();
        }

      }
    }

    public function onLinkedIdLogin() {

      $_settings    = get_option('loginpress_social_logins');

      $clientId     = $_settings['linkedin_client_id']; // LinkedIn client ID
      $clientSecret = $_settings['linkedin_client_secret']; // LinkedIn client secret
      $redirectURL  = $_settings['linkedin_redirect_uri']; // Callback URL

      if ( ! isset( $_GET['code'] ) ) {
            wp_redirect( "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$clientId}&client_secret={$clientSecret}&redirect_uri={$redirectURL}&state=987654321&scope=r_basicprofile&r_emailaddress" );
      } else{


        $get_access_token = wp_remote_post( 'https://www.linkedin.com/oauth/v2/accessToken', array(
          'body' => array(
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code'],
            'redirect_uri'  => $redirectURL,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret
          ) ) );


        $_access_token = json_decode(  $get_access_token['body'] )->access_token;

        if ( ! $_access_token ) {
          $user_login_url = apply_filters( 'login_redirect', admin_url(), site_url(), wp_signon() );
          wp_safe_redirect( $user_login_url );
        }
        $get_user_details = wp_remote_get( 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,email-address,public-profile-url)', array(
          'body' => array(
            'format'              => 'json',
            'oauth2_access_token' => $_access_token
          ) ) );


        $linkedin_data = json_decode( $get_user_details['body'] );

        include_once LOGINPRESS_SOCIAL_DIR_PATH . 'classes/loginpress-utilities.php';
        $loginpress_utilities = new LoginPress_Social_Utilities;

        $_username =  end( explode( '/', $linkedin_data->publicProfileUrl ) );

        $result =  new stdClass();

        $result->status     = 'SUCCESS';
        $result->deuid      = $linkedin_data->id;
        $result->deutype    = 'linkedin';
        $result->first_name = $linkedin_data->firstName;
        $result->last_name  = $linkedin_data->lastName;
        $result->email      = $linkedin_data->email != '' ? $linkedin_data->email : $linkedin_data->id . '@linkedin.com' ;
        $result->username   = $_username;
        $result->url        = $linkedin_data->publicProfileUrl;

        global $wpdb;
        $sha_verifier = sha1( $result->deutype.$result->deuid );
        $identifier = $linkedin_data->id;
        $sql = "SELECT * FROM `{$wpdb->prefix}loginpress_social_login_details` WHERE `provider_name` LIKE '$result->deutype' AND `identifier` LIKE '$result->deuid' AND `sha_verifier` LIKE '$sha_verifier'";
        $row = $wpdb->get_results( $sql );

        $user_object = get_user_by( 'email', $result->email );
        if( ! $row ) {
          //check if there is already a user with the email address provided from social login already
          if( $user_object != false ) {
            //user already there so log him in
            $id = $user_object->ID;
            $sql = "SELECT * FROM  `{$wpdb->prefix}loginpress_social_login_details` WHERE `user_id` LIKE '$id'";
            $row = $wpdb->get_results($sql);

            if( ! $row ){
              $loginpress_utilities->link_user( $id, $result );
            }
            $loginpress_utilities->_home_url( $id );
            // add_filter( 'login_redirect', array($this,'my_login_redirect'), 10, 3 );
            die();
          }

          $loginpress_utilities->register_user( $result->username, $result->email );
          $user_object  = get_user_by( 'email', $result->email );
          $id           = $user_object->ID;
          $role         = 'subscriber';
          $loginpress_utilities->update_usermeta( $id, $result, $role );
          // add_filter( 'login_redirect', array($this,'my_login_redirect'), 10, 3 );
          $loginpress_utilities->_home_url( $id );
          exit();
        } else {

          if( ( $row[0]->provider_name == $result->deutype ) && ( $row[0]->identifier == $result->deuid ) ) {
            //echo "user found in our database";
            $user_object  = get_user_by( 'email', $result->email );
            $id           = $user_object->ID;
            $loginpress_utilities->_home_url( $id );

            exit();
          }else{
            // user not found in our database
            // need to handle an exception
          }
        }

      }

    }

    public function onGPlusLogin() {

      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'sdk/gplus/Google_Client.php';
      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'sdk/gplus/contrib/Google_Oauth2Service.php';
      // $clientId = '615932804747-uealq45gvhav6pdee1jksqolegla6qps.apps.googleusercontent.com'; //Google client ID
      // $clientSecret = 'bPgTEEdk3SJNMonoF3nrcqeP'; //Google client secret
      //
      // $redirectURL = 'http://localhost/wp-analytify/wp-login.php?lpsl_login_id=gplus_login'; //Callback URL

      $_settings    = get_option('loginpress_social_logins');

      $clientId     = $_settings['gplus_client_id']; //Google client ID
      $clientSecret = $_settings['gplus_client_secret']; //Google client secret
      $redirectURL  = $_settings['gplus_redirect_uri']; //Callback URL

      $gClient = new Google_Client();
      $gClient->setApplicationName( 'LoginPress Social Login' );
      $gClient->setClientId( $clientId );
      $gClient->setClientSecret( $clientSecret );
      $gClient->setRedirectUri( $redirectURL );

      $google_oauthV2 = new Google_Oauth2Service( $gClient );

      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'classes/loginpress-utilities.php';

      $loginpress_utilities = new LoginPress_Social_Utilities;


      if ( ! isset( $_GET['code'] ) ) {
        wp_redirect( $gClient->createAuthUrl() );
      }
      else {
        try {
          $gClient->authenticate($_GET['code']);
        } catch (Exception $e) {
          add_filter( 'authenticate', array( 'LoginPress_Social_Utilities', 'gplus_login_error' ), 40, 3 );
        }

        if ( $gClient->getAccessToken() ) {
          //Get user profile data from google
          $gpUserProfile = $google_oauthV2->userinfo->get();

          $result =  new stdClass();

          $result->status     = 'SUCCESS';
          $result->deuid      = $gpUserProfile['id'];
          $result->deutype    = 'glpus';
          $result->first_name = $gpUserProfile['given_name'];
          $result->last_name  = $gpUserProfile['family_name'];
          $result->email      = $gpUserProfile['email'];
          $result->username   = ( $gpUserProfile['given_name'] !='' ) ? strtolower( $gpUserProfile['given_name'] ) : $gpUserProfile['email'];
          $result->gender     = isset( $gpUserProfile['gender'] ) ? $gpUserProfile['gender'] : '';
          $result->url        = isset( $gpUserProfile['link'] ) ? $gpUserProfile['link'] : '';
          $result->about      = ''; //gplus doesn't return user about details.
          $result->deuimage   = $gpUserProfile['picture'];


          global $wpdb;
          $sha_verifier = sha1( $result->deutype.$result->deuid );
          $identifier = $gpUserProfile['id'];
          $sql = "SELECT * FROM `{$wpdb->prefix}loginpress_social_login_details` WHERE `provider_name` LIKE '$result->deutype' AND `identifier` LIKE '$result->deuid' AND `sha_verifier` LIKE '$sha_verifier'";
          $row = $wpdb->get_results( $sql );

          $user_object = get_user_by( 'email', $gpUserProfile['email'] );
          if( ! $row ) {
            //check if there is already a user with the email address provided from social login already
            if( $user_object != false ) {
              //user already there so log him in
              $id = $user_object->ID;
              $sql = "SELECT * FROM  `{$wpdb->prefix}loginpress_social_login_details` WHERE `user_id` LIKE '$id'";
              $row = $wpdb->get_results($sql);

              if( ! $row ){
                $loginpress_utilities->link_user( $id, $result );
              }
              $loginpress_utilities->_home_url( $id );
              // add_filter( 'login_redirect', array($this,'my_login_redirect'), 10, 3 );
              die();
            }

            $loginpress_utilities->register_user( $result->username, $result->email );
            $user_object  = get_user_by( 'email', $result->email );
            $id           = $user_object->ID;
            $role         = 'subscriber';
            $loginpress_utilities->update_usermeta( $id, $result, $role );
            // add_filter( 'login_redirect', array($this,'my_login_redirect'), 10, 3 );
            $loginpress_utilities->_home_url( $id );
            exit();
          } else {

            if( ( $row[0]->provider_name == $result->deutype ) && ( $row[0]->identifier == $result->deuid ) ) {
              //echo "user found in our database";
              $user_object  = get_user_by( 'email', $result->email );
              $id           = $user_object->ID;
              $loginpress_utilities->_home_url( $id );

              exit();
            }else{
              // user not found in our database
              // need to handle an exception
            }
          }
        }

        }
      }


    public function onFacebookLogin() {

      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'classes/loginpress-facebook.php';
      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'classes/loginpress-utilities.php';
      $response_class       = new stdClass();
      $facebook_login       = new LoginPress_Facebook;
      $loginpress_utilities = new LoginPress_Social_Utilities;
      $result               = $facebook_login->facebookLogin( $response_class );

      if( isset( $result->status ) && $result->status == 'SUCCESS' ) {

        global $wpdb;
        $sha_verifier = sha1( $result->deutype.$result->deuid );
        $sql = "SELECT * FROM `{$wpdb->prefix}loginpress_social_login_details` WHERE `provider_name` LIKE '$result->deutype' AND `identifier` LIKE '$result->deuid' AND `sha_verifier` LIKE '$sha_verifier'";
        $row = $wpdb->get_results( $sql );
        $user_object = get_user_by( 'email', $result->email );

        if( ! $row ) {
          //check if there is already a user with the email address provided from social login already
          if( $user_object != false ){
            //user already there so log him in
            $id = $user_object->ID;
            $sql = "SELECT * FROM  `{$wpdb->prefix}loginpress_social_login_details` WHERE `user_id` LIKE '$id'";
            $row = $wpdb->get_results($sql);
            if( ! $row ){
              $loginpress_utilities->link_user( $id, $result );
            }
            $loginpress_utilities->_home_url( $id );
            // add_filter( 'login_redirect', array($this,'my_login_redirect'), 10, 3 );
            die();
          }

          $loginpress_utilities->register_user( $result->username, $result->email );
          $user_object  = get_user_by( 'email', $result->email );
          $id           = $user_object->ID;
          $role         = 'subscriber';
          $loginpress_utilities->update_usermeta( $id, $result, $role );
          // add_filter( 'login_redirect', array($this,'my_login_redirect'), 10, 3 );
          $loginpress_utilities->_home_url( $id );
          exit();
        } else {
          if( ( $row[0]->provider_name == $result->deutype ) && ( $row[0]->identifier == $result->deuid ) ) {
            //echo "user found in our database";
            $user_object  = get_user_by( 'email', $result->email );
            $id           = $user_object->ID;
            $loginpress_utilities->_home_url( $id );

            exit();
          }else{
            // user not found in our database
            // need to handle an exception
          }
        }
      } else {
        if( isset( $_REQUEST['error'] ) ) {


          $redirect_url = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : site_url();
          $loginpress_utilities->redirect( $redirect_url );
        }
        die();
      }

    } //!onFacebookLogin()

    public function onTwitterLogin() {

      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'classes/loginpress-twitter.php';
      include_once LOGINPRESS_SOCIAL_DIR_PATH . 'classes/loginpress-utilities.php';

      $response_class       = new stdClass();
      $twitter_login        = new LoginPress_Twitter;
      $loginpress_utilities = new LoginPress_Social_Utilities;
      $result               = $twitter_login->twitterLogin( $response_class );


      if( isset( $result->status ) && $result->status == 'SUCCESS' ) {
        global $wpdb;
        $sha_verifier = sha1( $result->deutype.$result->deuid );
        $sql = "SELECT *  FROM  `{$wpdb->prefix}loginpress_social_login_details` WHERE  `provider_name` LIKE  '$result->deutype' AND  `identifier` LIKE  '$result->deuid' AND `sha_verifier` LIKE '$sha_verifier'";
        $row = $wpdb->get_results( $sql );

        if( ! $row ) {
          //check if there is already a user with the email address provided from social login already
          $user_object = get_user_by( 'email', $result->email );

          if( $user_object != false ){
            //user already there so log him in
            $id = $user_object->ID;
            $sql = "SELECT *  FROM  `{$wpdb->prefix}loginpress_social_login_details` WHERE  `user_id` LIKE  '$id'; ";
            $row = $wpdb->get_results($sql);

            // var_dump($row);
            if( ! $row ) {
              $loginpress_utilities->link_user( $id, $result );
            }
            $loginpress_utilities->_home_url( $id );
            die();
          }

          $loginpress_utilities->register_user( $result->username, $result->email );
          $user_object  = get_user_by( 'email', $result->email );
          $id           = $user_object->ID;
          $role         = 'subscriber';
          $loginpress_utilities->update_usermeta( $id, $result, $role );
          $loginpress_utilities->_home_url( $id );
          exit();
        }else{

          if( ( $row[0]->provider_name == $result->deutype ) && ( $row[0]->identifier == $result->deuid ) ){
            //echo "user found in our database";
            $user_object  = get_user_by( 'email', $result->email );
            $id           = $user_object->ID;
            $loginpress_utilities->_home_url( $id );
            exit();
          } else {
            // user not found in our database
            // need to handle an exception
          }
        }

      }else{
        if ( isset( $_REQUEST['denied'] ) ) {
          $redirect_url = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : site_url();
          $loginpress_utilities->redirect( $redirect_url );
        }
        die();
      }
    }

  }
}
$lpsl_login_check = new LoginPress_Social_Login_Check();
