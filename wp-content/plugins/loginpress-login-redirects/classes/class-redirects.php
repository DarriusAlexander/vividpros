<?php

/**
 *   Redirects in process
 *
 */
if ( ! class_exists( 'LoginPress_Set_Login_Redirect' ) ) :

	class LoginPress_Set_Login_Redirect {

		public function __construct() {

			add_filter( 'login_redirect', array( $this, 'loginpress_redirects_after_login' ), 10, 3 );
      add_action( 'wp_logout', array( $this, 'loginpress_redirects_after_logout' ), 10 );
			add_action( 'loginpress_redirect_autologin', array( $this, '_autologin_redirects' ), 10, 1 );
		}


    // This function wraps around the main redirect function to determine whether or not to bypass the WordPress local URL limitation
    function loginpress_redirects_after_login( $redirect_to, $requested_redirect_to, $user )
    {
      if ( isset( $user->ID ) ) {
        $user_redirects_url = $this->loginpress_redirect_url( $user->ID, 'loginpress_login_redirects_url' );
				$role_redirects_url = get_option( 'loginpress_redirects_role' );

      	if ( isset( $user->roles ) && is_array( $user->roles ) ) {

      		// if ( in_array( 'administrator', $user->roles ) ) { //check for admins.
      		// 	// redirect them to the default place
      		// 	return $redirect_to;
          //
      		// } else

					if ( ! empty( $user_redirects_url ) ) { // check for specific user.

      			$this->_redirects( $user->ID, $user->name, $user, $user_redirects_url );

      		} elseif ( ! empty( $role_redirects_url ) ) { // check for specific role.

	      		foreach ( $role_redirects_url as $key => $value ) {
	      			if ( in_array( $key, $user->roles ) ) {

								$this->_redirects( $user->ID, $user->name, $user, $value['login'] );

	      			}
	      		}
	      	}
      	} else {
      		return $redirect_to;
      	}
      }
      return $redirect_to;
    }

    function loginpress_redirects_after_logout() {

      $user_id = get_current_user_id();
			$user_info = get_userdata( $user_id );
			$user_role = $user_info->roles;
			$role_redirects_url = get_option( 'loginpress_redirects_role' );

      if ( isset( $user_id ) ) {
        $user_redirects_url = $this->loginpress_redirect_url( $user_id, 'loginpress_logout_redirects_url' );
        if ( isset( $user_redirects_url ) && ! empty( $user_redirects_url ) ) {
          wp_redirect( $user_redirects_url );
          exit;
        } elseif ( ! empty( $role_redirects_url ) ) {
        	foreach ( $role_redirects_url as $key => $value ) {
						if ( in_array( $key, $user_role ) ) {
							wp_redirect( $value['logout'] );
							exit;
						} // endif;
					} // endforeach;
        } // endelseif;
      } // endif;
    } //end loginpress_redirects_after_logout();

		private function _redirects( $user_id, $username, $user, $redirect ) {

			wp_set_auth_cookie( $user_id, false );
			do_action( 'wp_login', $username, $user );
			wp_redirect( $redirect );
			exit;
		}

		/**
		 * _autologin_redirects redirect a user to a custom URL
		 * @param  object $user
		 * @return string URL
		 * @since 1.0.1
		 */
		public function _autologin_redirects( $user ){
			if ( isset( $user->ID ) ) {
        $user_redirects_url = $this->loginpress_redirect_url( $user->ID, 'loginpress_login_redirects_url' );
				$role_redirects_url = get_option( 'loginpress_redirects_role' );

      	if ( isset( $user->roles ) && is_array( $user->roles ) ) {

					if ( ! empty( $user_redirects_url ) ) { // check for specific user.

      			$this->_redirects( $user->ID, $user->name, $user, $user_redirects_url );

      		} elseif ( ! empty( $role_redirects_url ) ) { // check for specific role.

	      		foreach ( $role_redirects_url as $key => $value ) {
	      			if ( in_array( $key, $user->roles ) ) {

								$this->_redirects( $user->ID, $user->name, $user, $value['login'] );

	      			}
	      		}
	      	}
      	} else {
      		return $redirect_to;
      	}
      }
		}

		/**
		 * Get user meta.
		 * @param  int $user_id [ID of the use]
		 * @param string $option [user meta key]
		 * @since 1.0.1
		 */
		public function loginpress_redirect_url( $user_id, $option ) {

			if ( ! is_multisite() ) {
				$redirect_url = get_user_meta( $user_id, $option, true );
			} else {
				$redirect_url = get_user_option( $option, $user_id );
			}

			return $redirect_url;
		}

	}

	endif;
	new LoginPress_Set_Login_Redirect();
