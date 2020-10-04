<?php
/**
 * Created by PhpStorm.
 * User: shahnuralam
 * Date: 6/24/18
 * Time: 10:39 PM
 */

namespace WPDM\libs;


if( ! class_exists( 'User' ) ):

    class User{

        function __construct()
        {
            if(is_admin()) {
                add_action('show_user_profile', array($this, 'shopProfile'));
                add_action('edit_user_profile', array($this, 'shopProfile'));
                add_action('personal_options_update', array($this, 'saveShopProfile'));
                add_action('edit_user_profile_update', array($this, 'saveShopProfile'));
            }

            if(!is_admin()) {
                //add_action('wp_footer', array($this, 'modalLoginForm'));
            }
        }

        function shopProfile($user){
            $store = get_user_meta($user->ID, '__wpdm_public_profile', true);
            echo "<div class='w3eden' style='width: 800px;max-width: 100%;'>";
            include WPDM_BASE_DIR."admin/tpls/edit-store-profile.php";
            echo "</div>";
        }

        function saveShopProfile($user_id){
            if ( !current_user_can( 'edit_user', $user_id ) ) return false;
            $codata = wpdm_sanitize_array($_POST['__wpdm_public_profile']);
            //wpdmdd($user_id);
            update_user_meta($user_id, '__wpdm_public_profile', $codata);

            if(isset($_POST['__wpdm_public_profile']['paypal']) && $_POST['__wpdm_public_profile']['paypal'] != '') {
                update_user_meta(get_current_user_id(), 'payment_account', $_POST['__wpdm_public_profile']['paypal']);
            }
        }

        function modalLoginForm(){
            include_once wpdm_tpl_path("modal-login-form.php");
        }

    }

endif;

