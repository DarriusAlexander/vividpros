<?php

namespace WPDM\libs;

use WPDM\libs\FileSystem;
use WPDM\Package;
use WPDM\AuthorDashboard;
use WPDM\Session;
use WPDM\TempStorage;

class Apply {

    function __construct(){

        add_filter('wpdm_custom_data', array( $this, 'SR_CheckPackageAccess' ), 10, 2);

        //add_action('save_post', array( $this, 'SavePackage' ));
        add_action('publish_wpdmpro', array( $this, 'customPings' ));

        add_action( 'after_switch_theme', array($this, 'flashRules') );

        $this->adminActions();
        $this->frontendActions();

    }

    function frontendActions(){

        add_action("wp_ajax_nopriv_updatePassword", array($this, 'updatePassword'));
        add_action("wp_ajax_nopriv_resetPassword", array($this, 'resetPassword'));
        add_action("wp_ajax_nopriv_showLockOptions", array($this, 'showLockOptions'));
        add_action("wp_ajax_showLockOptions", array($this, 'showLockOptions'));
        add_action("wp_ajax_wpdm_addtofav", array($this, 'addToFav'));

        add_action('wp_ajax_wpdm_verify_file_pass', array($this, 'checkFilePassword'));
        add_action('wp_ajax_nopriv_wpdm_verify_file_pass', array($this, 'checkFilePassword'));


        add_filter("login_url", array($this, 'loginURL'), 999999, 3);
        add_filter("logout_url", array($this, 'logoutURL'), 999999, 2);

        add_filter("init", array($this, 'loginURLRedirect'));
        add_action('wp', array($this, 'wpdmIframe'));
        add_action("wp", array($this, 'shortcodeIframe'), 0);

        add_filter("template_include", array($this, 'interimLogin'), 9999);

        if(is_admin()) return;

        add_action("init", array($this, 'playMedia'), 8);
        add_action("init", array($this, 'triggerDownload'), 9);
        add_action('init', array($this, 'addWriteRules' ), 999999 );
        add_action('init', array($this, 'login'));
        add_action('init', array($this, 'register'));
        add_action('init', array($this, 'logout'));
        add_action('init', array($this, 'sfbAccess'));

        add_action('wp', array( $this, 'savePackage' ));
        add_action('wp', array($this, 'updateProfile'));

        add_filter('the_content', array($this, 'validateLoginPage'));
        add_filter('widget_text', 'do_shortcode');

        add_action('query_vars', array( $this, 'dashboardPageVars' ));
        add_action('request', array($this, 'rssFeed'));
        add_filter('pre_get_posts', array($this, 'queryTag'));

        add_action('wp_ajax_update_profile', array($this, 'updateProfile'));
        add_filter( 'ajax_query_attachments_args', array($this, 'usersMediaQuery') );


        add_action( 'wp_head', array($this, 'addGenerator'), 9999);
        add_filter( 'post_comments_feed_link', array($this, 'removeCommentFeed'));

        add_filter('the_excerpt_embed', array($this, 'oEmbed'));
        add_action('registration_errors', array($this, 'verifyEmail'), 10, 3);

        //add_filter( 'get_avatar_url', array($this, 'shopLogo'), 10, 3 );
        add_filter( 'get_avatar_data', array($this, 'shopLogo'), 10, 3 );

        add_action( 'wp_head', array($this, 'googleFont'), 999999);

    }


    function adminActions(){
        if(!is_admin()) return;
        add_action('save_post', array( $this, 'dashboardPages' ));
        add_action( 'admin_init', array($this, 'sfbAccess'));
        add_action( 'wp_ajax_clear_cache', array($this, 'clearCache'));
        add_action( 'wp_ajax_clear_stats', array($this, 'clearStats'));
        add_action( 'wp_ajax_generate_tdl', array($this, 'generateTDL'));
        add_action( 'admin_head', array($this, 'uiColors'));

    }

    function SR_CheckPackageAccess($data, $id){
        global $current_user;
        $skiplocks = maybe_unserialize(get_option('__wpdm_skip_locks', array()));
        if( is_user_logged_in() ){
            foreach($skiplocks as $lock){
                unset($data[$lock."_lock"]); // = 0;
            }
        }

        return $data;
    }

    function docStream(){
        if(strstr($_SERVER['REQUEST_URI'], 'wpdm-doc-preview')){
            preg_match("/wpdm\-doc\-preview\/([0-9]+)/", $_SERVER['REQUEST_URI'], $mat );
            $file_id = $mat[1];
            $files = Package::getFiles($file_id);
            if(count($files) ==0) die('No file found!');
            $sfile = '';
            foreach($files as $i=>$sfile){
                $ifile = $sfile;
                $sfile = explode(".", $sfile);
                $fext = end($sfile);
                if(in_array(end($sfile),array('pdf','doc','docx','xls','xlsx','ppt','pptx'))) { $sfile = $ifile; break; }
            }
            if($sfile =='') die('No supported document found!');
            if(file_exists(UPLOAD_DIR.$sfile)) $sfile =  UPLOAD_DIR.$sfile;
            if(!file_exists($sfile)) die('No supported document found!');
             
            if(strstr($sfile, '://')) header("location: {$sfile}");
            else
            FileSystem::downloadFile($sfile, basename($sfile));
            die();
        }
    }

    function addWriteRules(){
        global $wp_rewrite;
        $udb_page_id = get_option('__wpdm_user_dashboard', 0);
        if($udb_page_id) {
            $page_name = get_post_field("post_name", $udb_page_id);
            add_rewrite_rule('^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $udb_page_id . '&udb_page=$matches[1]', 'top');
            //dd($wp_rewrite);
        }
        $adb_page_id = get_option('__wpdm_author_dashboard', 0);

        if($adb_page_id) {
            $page_name = get_post_field("post_name", $adb_page_id);
            add_rewrite_rule('^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $adb_page_id . '&adb_page=$matches[1]', 'top');
        }

        $ap_page_id = get_option('__wpdm_author_profile', 0);

        if($ap_page_id) {
            $page_name = get_post_field("post_name", $ap_page_id);
            add_rewrite_rule('^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $ap_page_id . '&profile=$matches[1]', 'top');
        }

        //add_rewrite_rule('^wpdmdl/([0-9]+)/?', 'index.php?wpdmdl=$matches[1]', 'top');
        //add_rewrite_rule('^wpdmdl/([0-9]+)/ind/([^\/]+)/?', 'index.php?wpdmdl=$matches[1]&ind=$matches[2]', 'top');
        //if(is_404()) dd('404');
        //$wp_rewrite->flush_rules();
        //dd($wp_rewrite);
    }

    function flashRules(){
        $this->addWriteRules();
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    function dashboardPages($post_id){
        if ( wp_is_post_revision( $post_id ) )  return;
        $page_id = get_option('__wpdm_user_dashboard', 0);
        $post = get_post($post_id);
        $flush = 0;
        if((int)$page_id > 0 && has_shortcode($post->post_content, "wpdm_user_dashboard")) {
            update_option('__wpdm_user_dashboard', $post_id);
            $flush = 1;
        }

        if(has_shortcode($post->post_content, "wpdm_package_form")) {
            update_option('__wpdm_package_form', $post_id);
        }

        $page_id = get_option('__wpdm_author_dashboard', 0);
        $post = get_post($post_id);

        if((int)$page_id > 0 && has_shortcode($post->post_content, "wpdm_frontend")) {
            update_option('__wpdm_author_dashboard', $post_id);
            $flush = 1;
        }

        $page_id = get_option('__wpdm_author_profile', 0);
        $post = get_post($post_id);

        if((int)$page_id > 0 && has_shortcode($post->post_content, "wpdm_user_profile")) {
            update_option('__wpdm_author_profile', $post_id);
            $flush = 1;
        }

        if($flush == 1) {
            $this->addWriteRules();
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }

    }

    function dashboardPageVars( $vars ){
        array_push($vars, 'udb_page', 'adb_page','page_id', 'wpdmdl', 'ind', 'profile');
        return $vars;
    }

    /**
     * @usage Save Package Data
     */

    function savePackage()
    {
        global  $current_user, $wpdb;

        if(!is_user_logged_in()) return;
        $allowed_roles = get_option('__wpdm_front_end_access');
        $allowed_roles = maybe_unserialize($allowed_roles);
        $allowed_roles = is_array($allowed_roles)?$allowed_roles:array();
        $allowed =  array_intersect($allowed_roles, $current_user->roles);
        if (isset($_REQUEST['act']) && in_array($_REQUEST['act'], array('_ap_wpdm', '_ep_wpdm')) && count($allowed) > 0) {
            if(!wp_verify_nonce($_REQUEST['__wpdmepnonce'], NONCE_KEY)){
                $data = array('error' => __( "Invalid Request! Refresh the page and try again" , "download-manager" ));
                header('Content-type: application/json');
                echo json_encode($data);
                die();
            }

                $pack = $_POST['pack'];
            $pack['post_type'] = 'wpdmpro';

            if ($_POST['act'] == '_ep_wpdm') {

                $p = get_post($_POST['id']);
                if($current_user->ID != $p->post_author && !current_user_can('manage_options')) return;

                $hook = "edit_package_frontend";
                $pack['ID'] = (int)$_POST['id'];
                unset($pack['post_status']);
                unset($pack['post_author']);
                $post = get_post($pack['ID']);

                $ostatus = $post->post_status=='publish'?'publish':get_option('__wpdm_ips_frontend','publish');
                $status = isset($_POST['status']) && in_array($_POST['status'], array('draft','auto-draft'))?'draft': $ostatus;
                $pack['post_status'] = $status;
                $id = wp_update_post($pack);
                if(isset($_POST['cats'])) {
                    $ret = wp_set_post_terms($pack['ID'], $_POST['cats'], 'wpdmcategory');
                }
                if(isset($_POST['tags'])) {
                    wp_set_post_tags($pack['ID'], $_POST['tags']);
                }

            }
            if ($_POST['act'] == '_ap_wpdm'){
                $hook = "create_package_frontend";
                //$status = isset($_POST['status']) && $_POST['status'] == 'draft'?'draft': get_option('__wpdm_ips_frontend','publish');
                $status = isset($_POST['status']) && in_array($_POST['status'], array('draft','auto-draft'))?$_POST['status']: $ostatus;
                $pack['post_status'] = $status;
                $pack['post_author'] = $current_user->ID;
                $id = wp_insert_post($pack);
                if(isset($_POST['cats'])) {
                    wp_set_post_terms($id, $_POST['cats'], 'wpdmcategory');
                }
                if(isset($_POST['tags'])) {
                    wp_set_post_tags($pack['ID'], $_POST['tags']);
                }
            }

            // Save Package Meta
            $cdata = get_post_custom($id);
            foreach ($cdata as $k => $v) {
                $tk = str_replace("__wpdm_", "", $k);
                if (!isset($_POST['file'][$tk]) && $tk != $k)
                    delete_post_meta($id, $k);

            }

            if(isset($_POST['file']['preview'])){
                $preview = $_POST['file']['preview'];
                $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid='%s';", $preview ));
                set_post_thumbnail($id, $attachment_id);
                unset($_POST['file']['preview']);
            } else {
                delete_post_thumbnail($id);
            }

            if(isset($_POST['file']) && is_array($_POST['file'])) {
                foreach ($_POST['file'] as $meta_key => $meta_value) {
                    if ($meta_key == 'package_size' && doubleval($meta_value) == 0) $meta_value = "";
                    $key_name = "__wpdm_" . $meta_key;
                    update_post_meta($id, $key_name, $meta_value);
                }
            }

            update_post_meta($id, '__wpdm_masterkey', uniqid());

            if (isset($_POST['reset_key']) && $_POST['reset_key'] == 1)
                update_post_meta($id, '__wpdm_masterkey', uniqid());

            if(get_option('__wpdm_disable_new_package_email') == 0)
            \WPDM\Email::send("new-package-frontend", array( 'package_name' => $pack['post_title'], 'name' => $current_user->user_nicename, 'author' => "<a href='".admin_url('user-edit.php?user_id='.$current_user->ID)."'>{$current_user->user_nicename}</a>", 'edit_url' => admin_url('post.php?action=edit&post='.$id)));

            do_action($hook, $id, get_post($id));

            $data = array('result' => $_POST['act'], 'id' => $id);

            header('Content-type: application/json');
            echo json_encode($data);
            die();


        }
    }

    function login()
    {

        global $wp_query, $post, $wpdb;
        if (!isset($_POST['wpdm_login'])) return;

        $login_try = (int)Session::get('login_try');
        $login_try++;
        Session::set('login_try', $login_try);

        if($login_try > 30) wp_die("Slow Down!");

        Session::clear('login_error');
        $creds = array();
        $creds['user_login'] = $_POST['wpdm_login']['log'];
        $creds['user_password'] = $_POST['wpdm_login']['pwd'];
        $creds['remember'] = isset($_POST['rememberme']) ? $_POST['rememberme'] : false;
        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            Session::set('login_error', $user->get_error_message());

            if(wpdm_is_ajax()) die('failed');

            header("location: " . $_SERVER['HTTP_REFERER']);
            die();
        } else {
            wp_set_auth_cookie($user->ID);
            Session::set('login_try', 0);
            do_action('wp_login', $creds['user_login'], $user);
            if(wpdm_is_ajax()) die('success');

            header("location: " . $_POST['redirect_to']);
            die();
        }
    }

    /**
     * @usage Logout an user
     */

    function logout()
    {

        if (isset($_REQUEST['logout']) && wp_verify_nonce(wpdm_query_var('logout'), NONCE_KEY)) {
            wp_logout();
            //wp_loginout();
            header("location: " . wpdm_login_url());
            die();
        }
    }

    /**
     * @usage Register an user
     */
    function register()
    {
        global $wp_query, $wpdb;
        if (!isset($_POST['wpdm_reg'])) return;

        $shortcode_params = \WPDM\libs\Crypt::Decrypt($_REQUEST['phash']);

        if(!isset($_REQUEST['__reg_nonce']) || !wp_verify_nonce($_REQUEST['__reg_nonce'], NONCE_KEY) || wpdm_query_var('user_email_confirm') != ''){
            Session::set('reg_error',  __( "Something is Wrong! Please refresh the page and try again" , "download-manager" ));
            if (wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . Session::get('reg_error'))); die(); }
            header("location: " . $_POST['permalink']);
            die();
        }

        if(!get_option('users_can_register') && isset($_POST['wpdm_reg'])){
            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => __( "Error: User registration is disabled!" , "download-manager" ))); die(); }
            else Session::set('reg_error', __( "Error: User registration is disabled!" , "download-manager" ));
            header("location: " . $_POST['permalink']);
            die();
        }


        $_POST['wpdm_reg']['full_name'] = $_POST['wpdm_reg']['first_name']." ".$_POST['wpdm_reg']['last_name'];
        extract($_POST['wpdm_reg']);
        $display_name = $first_name." ".$last_name;

        Session::set('tmp_reg_info', $_POST['wpdm_reg']);
        $user_id = username_exists($user_login);
        $loginurl = $_POST['permalink'];
        if ($user_login == '') {

            Session::set('reg_error',  __( "Username is Empty!" , "download-manager" ));

            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . Session::get('reg_error'))); die(); }

            header("location: " . $_POST['permalink']);
            die();
        }
        if (!isset($user_email) || !is_email($user_email)) {

            Session::set('reg_error',  __( "Invalid Email Address!" , "download-manager" ));

            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . Session::get('reg_error'))); die(); }

            header("location: " . $_POST['permalink']);
            die();
        }

        if (!$user_id) {
            $user_id = email_exists($user_email);
            if (!$user_id) {
                
                $auto_login = 0;
                if(isset($shortcode_params['autologin']) && $shortcode_params['autologin'] == 'true')
                $auto_login = 1;

                $user_pass = (isset($shortcode_params['verifyemail']) && $shortcode_params['verifyemail'] == 'true') || !isset($user_pass) || $user_pass == ''?wp_generate_password(12, false):$user_pass;
                $emlpass = (isset($shortcode_params['verifyemail']) && $shortcode_params['verifyemail'] == 'true')?__( "Password: " , "download-manager" ).$user_pass."<br/>":"";

                $errors = new \WP_Error();

                do_action( 'register_post', $user_login, $user_email, $errors );

                $errors = apply_filters( 'registration_errors', $errors, $user_login, $user_email );

                if ( $errors->get_error_code() ) {
                    if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . $errors->get_error_message())); die(); }
                    else Session::set('reg_error',  'Error: ' . $errors->get_error_message());
                    header("location: " . $_POST['permalink']);
                    die();
                }

                $user_id = wp_create_user($user_login, $user_pass, $user_email);

                wp_update_user(array('ID' => $user_id, 'display_name' => $display_name, 'first_name' => $first_name, 'last_name' => $last_name));

                //To User
                $usparams = array('to_email' => $user_email, 'name' => $display_name, 'first_name' => $first_name, 'last_name' => $last_name, 'user_email' => $user_email, 'username' => $user_login, 'password' => $user_pass);
                \WPDM\Email::send("user-signup", $usparams);

                //To Admin
                $ip = wpdm_get_client_ip();
                $data = array(
                            array('Name', $display_name),
                            array('Username', $user_login),
                            array('Email', $user_email),
                            array('IP', $ip)
                );
                $css = array('col' => array('background: #edf0f2 !important'), 'td' => 'border-bottom:1px solid #e6e7e8');
                $table = MailUI::table(null, $data, $css);
                $edit_user_btn = "<a class='button' style='display:block;margin:10px 0 0;text-decoration: none;text-align:center;' href='".admin_url('user-edit.php?user_id='.$user_id)."'> ".__( "Edit User" , "download-manager" )." </a>";
                $message = __( "New user registration on your site WordPress Download Manager:" , "download-manager" )."<br/>".$table.$edit_user_btn; //.__( "Username" , "download-manager" ).": {$user_login}<br/>".__( "Email" , "download-manager" ).": {$user_email}<br/>".__( "IP" , "download-manager" ).": {$ip}<br/><strong><a style='text-decoration: none;' href='".admin_url('user-edit.php?user_id='.$user_id)."'>&mdash; ".__( "Edit User" , "download-manager" )." &mdash;</a></strong>";
                $params = array('subject' => sprintf(__("[%s] New User Registration"), get_bloginfo( 'name' ), 'wpdmpro'), 'to_email' => get_option('admin_email'), 'message' => $message);
                \WPDM\Email::send("default", $params);

                Session::clear('guest_order');
                Session::clear('login_error');
                Session::clear('tmp_reg_info');

                $creds['user_login'] = $user_login;
                $creds['user_password'] = $user_pass;
                $creds['remember'] = true;
                Session::set('sccs_msg', __( "Your account has been created successfully and login info sent to your mail address." , "download-manager" ));

                if($auto_login==1) {
                    Session::set('sccs_msg', __( "Your account has been created successfully" , "download-manager" ));
                    wp_signon($creds);
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    $loginurl = wpdm_user_dashboard_url();

                }

                if(wpdm_is_ajax()) {  wp_send_json(array('success' => true)); die(); }

                header("location: " . $loginurl);
                die();
            } else {
                Session::set('reg_error', __( "Email already exists." , "download-manager" ));
                $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];

                if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . Session::get('reg_error'))); die(); }

                header("location: " . $loginurl);
                die();
            }
        } else {
            Session::set('reg_error', __( "Username already exists." , "download-manager" ));
            $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];

            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . Session::get('reg_error'))); die(); }

            header("location: " . $loginurl);
            die();
        }

    }

    function updateProfile()
    {
        global $current_user;

        if (isset($_POST['wpdm_profile']) && is_user_logged_in() && wp_verify_nonce(wpdm_query_var('__wpdm_epnonce'), NONCE_KEY)) {

            $error = 0;

            $pfile_data['display_name'] = $_POST['wpdm_profile']['display_name'];
            $pfile_data['description'] = $_POST['wpdm_profile']['description'];
            $pfile_data['user_email'] = $_POST['wpdm_profile']['user_email'];


            if ($_POST['password'] != $_POST['cpassword']) {
                Session::set('member_error', 'Password not matched');
                $error = 1;
            }
            if (!$error) {
                $pfile_data['ID'] = $current_user->ID;
                if ($_POST['password'] != '')
                    $pfile_data['user_pass'] = $_POST['password'];

                wp_update_user($pfile_data);

                update_user_meta($current_user->ID, 'payment_account', $_POST['payment_account']);
                Session::set( 'member_success' , 'Profile data updated successfully.' );
            }

            do_action("wpdm_update_profile");

            if(wpdm_is_ajax()){
                ob_clean();
                if($error == 1){
                    $msg['type'] = 'danger';
                    $msg['title'] = 'ERROR!';
                    $msg['msg'] = Session::get( 'member_error' );
                    Session::clear('member_error');
                    wp_send_json($msg);
                    die();
                } else {
                    $msg['type'] = 'success';
                    $msg['title'] = 'DONE!';
                    $msg['msg'] = Session::get( 'member_success' );
                    Session::clear('member_success');
                    wp_send_json($msg);
                    die();
                }
            }
            header("location: " . $_SERVER['HTTP_REFERER']);
            die();
        }
    }

    function playMedia(){

        if(strstr("!{$_SERVER['REQUEST_URI']}", "/wpdm-media/")){
            $media = explode("wpdm-media/", $_SERVER['REQUEST_URI']);
            $media = explode("/", $media[1]);
            list($ID, $file, $name) = $media;
            $key = wpdm_query_var('_wpdmkey');

            if ( isset($_SERVER['HTTP_RANGE']) ) {
                $partialContent = true;
                preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                $offset = intval($matches[1]);
                $length = intval($matches[2]) - $offset;
            } else {
                $partialContent = false;
            }

            $keyValid = is_wpdmkey_valid($ID, $key, true);
            if(!$partialContent) {
                if ($key == '' || !$keyValid)
                    \WPDM_Messages::Error(stripslashes(get_option('__wpdm_permission_denied_msg')), 1);
            }
            $files = \WPDM\Package::getFiles($ID);
            $file = $files[$file];
            $file = \WPDM\libs\FileSystem::fullPath($file, $ID);
            $stream = new \WPDM\libs\StreamMedia($file);
            $stream->start();
            die();
        }
    }


    /**
     * @usage Process Download Request from lock options
     */
    function triggerDownload()
    {

        global $wpdb, $current_user, $wp_query;
        if(preg_match("/\/wpdmdl\//", $_SERVER['REQUEST_URI'])){
            $uri = trim($_SERVER['REQUEST_URI'], '/');
            $download_url_base = get_option('__wpdm_download_url_base', 'wpdmdl');
            $uri = explode("/".$download_url_base."/", $uri);
            $parts = explode("/", $uri[1]);
            $_REQUEST['wpdmdl'] = $_GET['wpdmdl'] = (int)$parts[0];
            $wp_query->query_vars['wpdmdl'] = (int)$parts[0];
            if(isset($parts[1])) $_REQUEST['_wpdmkey'] = $_GET['_wpdmkey'] = $parts[1];

        }

        //Instant download link processing
        if (isset($_GET['wpdmidl'])) {
            $file = TempStorage::get("__wpdm_instant_download_{$_GET['wpdmidl']}");
            if(!$file)
                \WPDM_Messages::error(__("Download ID not found or expired", "download-manager"), 1);
            if(!file_exists($file))
                \WPDM_Messages::error(__("The file is already removed from the server!", "download-manager"), 1);

            FileSystem::downloadFile($file, "Packages-Export-".date("Y-m-d").".csv");
            die();
        }

        //Regular download processing
        if (!isset($wp_query->query_vars['wpdmdl']) && !isset($_GET['wpdmdl'])) return;


        $id = isset($_GET['wpdmdl']) ? (int)$_GET['wpdmdl'] : (int)$wp_query->query_vars['wpdmdl'];
        if ($id <= 0) return;

        $key = wpdm_query_var('_wpdmkey');
        $key = $key == '' && array_key_exists('_wpdmkey', $wp_query->query_vars) ? $wp_query->query_vars['_wpdmkey'] : $key;
        $key = preg_replace("/[^_a-z|A-Z|0-9]/i", "", $key);

        if($key) {
            $keyValid = is_wpdmkey_valid($id, $key, true);

            if ((int)$keyValid !== 1) {
                \WPDM_Messages::error(__("&mdash; Invalid download link &mdash;", "download-manager"), 1);
            }
        }

        $package = get_post($id, ARRAY_A);

        $package = array_merge($package, wpdm_custom_data($package['ID']));
        if (isset($package['files']))
            $package['files'] = maybe_unserialize($package['files']);
        else
            $package['files'] = array();
        //$package = wpdm_setup_package_data($package);

        $package['access'] = wpdm_allowed_roles($id);

        if (is_array($package)) {
            $role = @array_shift(@array_keys($current_user->caps));
            $cpackage = apply_filters('before_download', $package);
            $lock = '';
            $package = $cpackage ? $cpackage : $package;
            if (isset($package['email_lock']) && $package['email_lock'] == 1) $lock = 'locked';
            if (isset($package['password_lock']) && $package['password_lock'] == 1) $lock = 'locked';
            if (isset($package['gplusone_lock']) && $package['gplusone_lock'] == 1) $lock = 'locked';
            if (isset($package['twitterfollow_lock']) && $package['twitterfollow_lock'] == 1) $lock = 'locked';
            if (isset($package['facebooklike_lock']) && $package['facebooklike_lock'] == 1) $lock = 'locked';
            if (isset($package['tweet_lock']) && $package['tweet_lock'] == 1) $lock = 'locked';
            if (isset($package['captcha_lock']) && $package['captcha_lock'] == 1) $lock = 'locked';

            if ($lock !== 'locked')
                $lock = apply_filters('wpdm_check_lock', $lock, $id);

            if (isset($_GET['masterkey']) && esc_attr($_GET['masterkey']) == $package['masterkey']) {
                $lock = 0;
            }



            $matched = (is_array(@maybe_unserialize($package['access'])) && is_user_logged_in())?array_intersect($current_user->roles, @maybe_unserialize($package['access'])):array();

            
            if ((($id != '' && is_user_logged_in() && count($matched) < 1 && !@in_array('guest', $package['access'])) || (!is_user_logged_in() && !@in_array('guest', $package['access']) && $id != ''))) {
                do_action("wpdm_download_permission_denied", $id);
                wpdm_download_data("permission-denied.txt", __( "You don't have permission to download this file" , "download-manager" ));
                die();
            } else {

                if ($package['ID'] > 0) {

                    if((int)$package['quota'] == 0 || $package['quota'] > $package['download_count']) {
                        $package['force_download'] = wpdm_query_var('_wpdmkey');
                        include(WPDM_BASE_DIR . "wpdm-start-download.php");
                    }
                    else
                        wpdm_download_data("stock-limit-reached.txt", __( "Stock Limit Reached" , "download-manager" ));

                }

            }
        } else
            \WPDM_Messages::error(__( "Invalid download link." , "download-manager" ), 1);
    }


    /**
     * @usage Add with main RSS feed
     * @param $query
     * @return mixed
     */
    function rssFeed($query) {
        if ( isset($query['feed'])  && !isset($query['post_type']) &&  get_option('__wpdm_rss_feed_main', 0) == 1 ){
            $query['post_type'] = array('post','wpdmpro');
        }
        return $query;
    }

    /**
     * @usage Schedule custom ping
     * @param $post_id
     */
    function customPings($post_id){
        wp_schedule_single_event(time()+5000, 'do_pings', array($post_id));
    }

    /**
     * @usage Allow access to server file browser for selected user roles
     */
    function sfbAccess(){

        global $wp_roles;
        if(!is_array($wp_roles->roles)) return;
        $roleids = array_keys($wp_roles->roles);
        $roles = get_option('_wpdm_file_browser_access',array('administrator'));
        $naroles = array_diff($roleids, $roles);
        foreach($roles as $role) {
            $role = get_role($role);
            if(is_object($role) && !is_wp_error($role))
                $role->add_cap('access_server_browser');
        }

        foreach($naroles as $role) {
            $role = get_role($role);
            if(is_object($role) && !is_wp_error($role))
                $role->remove_cap('access_server_browser');
        }

    }

    /**
     * @usage Validate individual file password
     */
    function checkFilePassword(){
        if (isset($_POST['actioninddlpvr'], $_POST['wpdmfileid']) && $_POST['actioninddlpvr'] != '') {

            $fileid = intval($_POST['wpdmfileid']);
            $data = get_post_meta($_POST['wpdmfileid'], '__wpdm_fileinfo', true);
            $data = $data ? $data : array();
            $package = get_post($fileid);
            $packagemeta = wpdm_custom_data($fileid);
            $password = isset($data[$_POST['wpdmfile']]['password']) && $data[$_POST['wpdmfile']]['password'] != "" ? $data[$_POST['wpdmfile']]['password'] : $packagemeta['password'];
            $pu = isset($packagemeta['password_usage']) && is_array($packagemeta['password_usage'])?$packagemeta['password_usage']:array();
            if ($password == $_POST['filepass'] || strpos($password, "[" . $_POST['filepass'] . "]") !== FALSE) {
                $pul = $packagemeta['password_usage_limit'];
                if (is_array($pu) && isset($pu[$password]) && $pu[$password] >= $pul && $pul > 0) {
                    $data['error'] = __( "Password usages limit exceeded" , "download-manager" );
                    die('|error|');
                }
                else {
                    if(!is_array($pu)) $pu = array();
                    $pu[$password] = isset($pu[$password])?$pu[$password]+1:1;
                    update_post_meta($fileid, '__wpdm_password_usage', $pu);
                }

                $id = uniqid();
                Session::set( '__wpdm_unlocked_'.$_POST['wpdmfileid'] , 1 );
                update_post_meta($fileid, "__wpdmkey_".$id, 8);

                die("|ok|$id|");
            } else
                die('|error|');
        }
    }

    /**
     * @usage Allow front-end users to access their own files only
     * @param $query_params
     * @return string
     */
    function usersMediaQuery( $query_params ){
        global $current_user;

        if(current_user_can('edit_posts')) return $query_params;

        if( is_user_logged_in() ){
            $query_params['author'] = $current_user->ID;
        }
        return $query_params;
    }

    /**
     * @usage Add packages wth tag query
     * @param $query
     * @return mixed
     */
    function queryTag($query)
    {

        if (is_tag() && $query->is_main_query()) {
            $post_type = get_query_var('post_type');
            if (!is_array($post_type))
                $post_type = array('post', 'page', 'wpdmpro', 'nav_menu_item');
            else
                $post_type = array_merge($post_type, array('post', 'wpdmpro', 'nav_menu_item'));
            $query->set('post_type', $post_type);
        }
        return $query;
    }

    /**
     * Empty cache dir
     */
    function clearCache(){
        if(!current_user_can('manage_options')) return;
        \WPDM\libs\FileSystem::deleteFiles(WPDM_CACHE_DIR, false);
        \WPDM\libs\FileSystem::deleteFiles(WPDM_CACHE_DIR.'pdfthumbs/', false);
        die('ok');
    }

    /**
     * Delete all download hostory
     */
    function clearStats(){
        if(!current_user_can('manage_options')) return;
        global $wpdb;
        $wpdb->query('truncate table '.$wpdb->prefix.'ahm_download_stats');
        die('ok');
    }


    /**
     * @usage Add generator tag
     */
    function addGenerator(){
        echo '<meta name="generator" content="WordPress Download Manager '.WPDM_Version.'" />'."\r\n";
    }

    function oEmbed($content){
        if(get_post_type(get_the_ID()) != 'wpdmpro') return $content;
        if(function_exists('wpdmpp_effective_price') && wpdmpp_effective_price(get_the_ID()) > 0)
        $template = '<table class="table table-bordered"><tbody><tr><td colspan="2">[excerpt_200]</td></tr><tr><td>Price</td><td>[currency][effective_price]</td></tr><tr><td>Version</td><td>[version]</td></tr><tr><td>Total Files</td><td>[file_count]</td></tr><tr><td>File Size</td><td>[file_size]</td></tr><tr><td>Create Date</td><td>[create_date]</td></tr><tr><td>Last Updated</td><td>[update_date]</td><tr><td colspan="2" style="text-align: right;border-bottom: 0"><a class="wpdmdlbtn" href="[page_url]" target="_parent">&#x1F4B3; &nbsp; Buy Now</a></td></tr></tbody></table><br/><style> .wpdmdlbtn {-moz-box-shadow:inset 0px 1px 0px 0px #9acc85;-webkit-box-shadow:inset 0px 1px 0px 0px #9acc85;box-shadow:inset 0px 1px 0px 0px #9acc85;background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #74ad5a), color-stop(1, #68a54b));background:-moz-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-webkit-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-o-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-ms-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:linear-gradient(to bottom, #74ad5a 5%, #68a54b 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#74ad5a\', endColorstr=\'#68a54b\',GradientType=0);background-color:#74ad5a;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;border:1px solid #3b6e22;display:inline-block;cursor:pointer;color:#ffffff !important; font-size:12px;font-weight:bold;padding:10px 20px;text-transform: uppercase;text-decoration:none !important;}.wpdmdlbtn:hover {background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #68a54b), color-stop(1, #74ad5a));background:-moz-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-webkit-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-o-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-ms-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:linear-gradient(to bottom, #68a54b 5%, #74ad5a 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#68a54b\', endColorstr=\'#74ad5a\',GradientType=0);background-color:#68a54b;}.wpdmdlbtn:active {position:relative;top:1px;} .table{width:100%;border: 1px solid #eeeeee;} .table td{ padding:10px;border-bottom:1px solid #eee;}</style>'; else $template = '<table class="table table-bordered"><tbody><tr><td colspan="2">[excerpt_200]</td></tr><tr><td>Version</td><td>[version]</td></tr><tr><td>Total Files</td><td>[file_count]</td></tr><tr><td>File Size</td><td>[file_size]</td></tr><tr><td>Create Date</td><td>[create_date]</td></tr><tr><td>Last Updated</td><td>[update_date]</td><tr><td colspan="2" style="text-align: right;border-bottom: 0"><a class="wpdmdlbtn" href="[page_url]" target="_parent">&#x2b07; &nbsp; Download</a></td></tr></tbody></table><br/><style> .wpdmdlbtn {-moz-box-shadow:inset 0px 1px 0px 0px #9acc85;-webkit-box-shadow:inset 0px 1px 0px 0px #9acc85;box-shadow:inset 0px 1px 0px 0px #9acc85;background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #74ad5a), color-stop(1, #68a54b));background:-moz-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-webkit-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-o-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-ms-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:linear-gradient(to bottom, #74ad5a 5%, #68a54b 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#74ad5a\', endColorstr=\'#68a54b\',GradientType=0);background-color:#74ad5a;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;border:1px solid #3b6e22;display:inline-block;cursor:pointer;color:#ffffff !important; font-size:12px;font-weight:bold;padding:10px 20px;text-transform: uppercase;text-decoration:none !important;}.wpdmdlbtn:hover {background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #68a54b), color-stop(1, #74ad5a));background:-moz-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-webkit-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-o-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-ms-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:linear-gradient(to bottom, #68a54b 5%, #74ad5a 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#68a54b\', endColorstr=\'#74ad5a\',GradientType=0);background-color:#68a54b;}.wpdmdlbtn:active {position:relative;top:1px;} .table{width:100%; border: 1px solid #eeeeee; } .table td{ padding:10px;border-bottom:1px solid #eee;}</style>';
        return \WPDM\Package::fetchTemplate($template, get_the_ID());
    }

    function showLockOptions(){
        if(!isset($_REQUEST['id'])) die('ID Missing!');
        echo \WPDM\Package::downloadLink((int)$_REQUEST['id'], 1);
        die();
    }

    function addToFav(){
        if(!is_user_logged_in()) die('error');
        $pid = (int)$_REQUEST['pid'];
        $ufavs = maybe_unserialize(get_post_meta($pid, '__wpdm_favs', true));
        $myfavs = maybe_unserialize(get_user_meta(get_current_user_id(), '__wpdm_favs', true));
        if(!is_array($myfavs)) $myfavs = array();
        if(!is_array($ufavs)) $ufavs = array();

        if(is_array($myfavs) && ($key = array_search($_REQUEST['pid'], $myfavs)) !== false) {
            unset($myfavs[$key]);
            if(isset($ufavs[get_current_user_id()])) unset($ufavs[get_current_user_id()]);
        }
        else {
            $myfavs[] = $_REQUEST['pid'];
            $ufavs[get_current_user_id()] = time();
        }
        update_user_meta(get_current_user_id(), '__wpdm_favs', $myfavs);
        update_post_meta($pid, '__wpdm_favs', $ufavs);
        die('ok');
    }


    function loginURL($login_url, $redirect, $force_reauth){
        $id = get_option('__wpdm_login_url', 0);
        if($id > 0) {
            $page = get_post($id);
            if($page->post_status == 'publish') {
                $url = get_permalink($id);
                if ($redirect != '')
                    $url = add_query_arg(array('redirect_to' => $redirect), $url);
            }
            else $url = $login_url;
        }
        else $url = $login_url;
        return $url;
    }

    function logoutURL($logout_url, $redirect){
        $logout_url = wpdm_logout_url($redirect);
        return $logout_url;
    }

    function loginURLRedirect(){
        if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && strstr($_SERVER['REQUEST_URI'], 'wp-login.php') && !wpdm_query_var('skipwpdm')) {
            $id = get_option('__wpdm_login_url', 0);
            if($id > 0)
            {
                $page = get_post($id);
                if($page->post_status == 'publish') {
                    if (is_user_logged_in()) {
                        wp_redirect(wpdm_user_dashboard_url());
                    } else {
                        wp_redirect(add_query_arg($_GET, wpdm_login_url()));
                    }
                    die();
                }
            }
        }
    }

    function resetPassword(){
        if(wpdm_query_var('__reset_pass')){

            if ( empty( $_POST['user_login'] ) ) {
                die('error');
            } elseif ( strpos( $_POST['user_login'], '@' ) ) {
                $user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
                if ( empty( $user_data ) )
                    die('error');
            } else {
                $login = trim($_POST['user_login']);
                $user_data = get_user_by('login', $login);
            }
            if(Session::get( '__reset_time' ) && time() - Session::get( '__reset_time' ) < 60){
                echo "toosoon";
                exit;
            }
            if(!is_object($user_data) || !isset($user_data->user_login)) die('error');
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;
            $key = get_password_reset_key( $user_data );



            $reseturl = add_query_arg(array('action' => 'rp', 'key' => $key, 'login' => rawurlencode($user_login)), wpdm_login_url());

            $params = array('reset_password' => $reseturl, 'to_email' => $user_email);

            \WPDM\Email::send('password-reset', $params);
            Session::set( '__reset_time' , time() );
            echo 'ok';
            exit;

        }
    }

    function updatePassword(){
        if(wpdm_query_var('__update_pass')){

            if(wp_verify_nonce(wpdm_query_var('__update_pass'), NONCE_KEY)){
                $pass = wpdm_query_var('password');
                if($pass == '') die('error');
                $user = Session::get('__up_user');
                $user = maybe_unserialize($user);
                if(is_object($user) && isset($user->ID)) {
                    wp_set_current_user($user->ID, $user->user_login);
                    wp_set_auth_cookie($user->ID);
                    //do_action('wp_login', $user->user_login);
                    wp_set_password($pass, $user->ID);
                    //print_r($user);
                    wp_send_json(array('success' => true, 'message' => ''));
                } else wp_send_json(array('success' => false, 'message' => __('Session Expired! Please try again.', 'download-manager')));
            }
            else
                wp_send_json(array('success' => false, 'message' => __('Session Expired! Please try again.', 'download-manager')));

        }
    }

    function interimLogin($template){
        if(isset($_REQUEST['interim-login']) && !isset($_POST['wpdm_login'])){
            $template = WPDM_BASE_DIR.'tpls/clean.php';
        }
        return $template;
    }


    function verifyEmail($errors, $sanitized_user_login, $user_email){
        if(!$errors) $errors = new \WP_Error();
        if(!wpdm_verify_email($user_email))
            $errors->add( 'blocked_email', __( "The email address is blocked." , "download-manager" ) );
        return $errors;
    }

    function generateTDL(){
        if(is_user_logged_in() && AuthorDashboard::hasAccess(get_current_user_id()) && wp_verify_nonce($_REQUEST['__tdlnonce'], NONCE_KEY)){
            $pid = (int)$_REQUEST['pid'];
            $pack = get_post($pid);
            if(current_user_can(WPDM_ADMIN_CAP) || $pack->post_author == get_current_user_id()) {
                Session::set( '__wpdm_unlocked_' . $pid , 1 );
                $key = uniqid();
                $expire = time() + ((int)$_REQUEST['exmisd'] * 60);
                update_post_meta($pid, "__wpdmkey_" . $key, array('use' => (int)$_REQUEST['ulimit'], 'expire' => $expire));
                $download_url = wpdm_download_url($pid, "_wpdmkey={$key}");
                echo $download_url;
                die();
            }
        }
        die('Error! Unauthorized Access');
    }

    function wpdmIframe(){
        if(isset($_REQUEST['__wpdmlo'])){
            include wpdm_tpl_path("lock-options-iframe.php");
            die();
        }
    }

    function shortcodeIframe(){
        if(isset($_REQUEST['__wpdmxp'])){
            include wpdm_tpl_path("shortcode-iframe.php");
            die();
        }
    }

    function validateLoginPage($content){
        if(is_singular('page')) {
            $id = get_option('__wpdm_login_url', 0);
            if($id == get_the_ID()) {
                if(!has_shortcode($content, 'wpdm_login_form') && !has_shortcode($content, 'wpdm_user_dashboard') && !has_shortcode($content, 'wpdm_author_dashboard')) {
                     $content = wpdm_login_form();
                }
            }
        }
        return $content;

    }

    function removeCommentFeed($feed){
        if(get_post_type() == 'wpdmpro' && get_option('__wpdm_has_archive', false) == false)
            $feed =  false;
        return $feed;
    }


    function shopLogo1($url, $id, $args = array() ) {
        if(is_object($id)) $id = $id->user_id;
        if(is_email($id)) {
            $user = get_user_by('email', $id);
            $id = $user->ID;
        }

        $store = get_user_meta($id, '__wpdm_public_profile', true);
        return isset($store['logo']) && $store['logo'] != ''?$store['logo']:$url;
    }

    function shopLogo($args, $id) {
        if(is_object($id)) $id = $id->user_id;
        if(is_email($id)) {
            $user = get_user_by('email', $id);
            $id = $user->ID;
        }

        $store = get_user_meta($id, '__wpdm_public_profile', true);
        if(isset($store['logo']) && $store['logo'] != '')
            $args['url'] = $store['logo'];
        return $args;
    }


    function googleFont(){
        $wpdmss = maybe_unserialize(get_option('__wpdm_disable_scripts', array()));
        $uicolors = maybe_unserialize(get_option('__wpdm_ui_colors', array()));
        $ltemplates = maybe_unserialize(get_option("_fm_link_templates",true));
        $ptemplates = maybe_unserialize(get_option("_fm_page_templates",true));
        $font = get_option('__wpdm_google_font', 'Rubik');
        $font = $font?$font.',':'';
        $css = "";
        if(is_array($ltemplates)){
            $tplstatus = maybe_unserialize(get_option("_fm_link_template_status"));
            foreach ($ltemplates as $id => $template){
                if(!isset($tplstatus[$id]) || (int)$tplstatus[$id] === 1)
                    $css .= isset($template['css']) ? $template['css'] : '';
            }
        }
        if(is_array($ptemplates)){
            $tplstatus = maybe_unserialize(get_option("_fm_page_template_status"));
            foreach ($ptemplates as $id => $template){
                if(!isset($tplstatus[$id]) || (int)$tplstatus[$id] === 1)
                    $css .= isset($template['css']) ? $template['css'] : '';
            }
        }

        ?>
        <?php if(get_option('__wpdm_google_font', 'Rubik') !== '') { ?>
            <link href="https://fonts.googleapis.com/css?family=<?php echo get_option('__wpdm_google_font', 'Rubik'); ?>" rel="stylesheet">
        <style>
            .w3eden .fetfont,
            .w3eden .btn,
            .w3eden .btn.wpdm-front h3.title,
            .w3eden .wpdm-social-lock-box .IN-widget a span:last-child,
            .w3eden #xfilelist .panel-heading,
            .w3eden .wpdm-frontend-tabs a,
            .w3eden .alert:before,
            .w3eden .panel .panel-heading,
            .w3eden .discount-msg,
            .w3eden .panel.dashboard-panel h3,
            .w3eden #wpdm-dashboard-sidebar .list-group-item,
            .w3eden #package-description .wp-switch-editor,
            .w3eden .w3eden.author-dashbboard .nav.nav-tabs li a,
            .w3eden .wpdm_cart thead th,
            .w3eden #csp .list-group-item,
            .w3eden .modal-title {
                font-family: <?php echo $font; ?> -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
                text-transform: uppercase;
                font-weight: 700;
            }
            .w3eden #csp .list-group-item{
                text-transform: unset;
            }

            <?php
            echo '/* WPDM Link/Page Template Styles */';
            echo $css;
            ?>
        </style>
        <?php } ?>
        <?php
        $this->uiColors();

    }

    static function uiColors(){
        $uicolors = maybe_unserialize(get_option('__wpdm_ui_colors', array()));
        $primary = isset($uicolors['primary'])?$uicolors['primary']:'#4a8eff';
        $secondary = isset($uicolors['secondary'])?$uicolors['secondary']:'#4a8eff';
        $success = isset($uicolors['success'])?$uicolors['success']:'#18ce0f';
        $info = isset($uicolors['info'])?$uicolors['info']:'#2CA8FF';
        $warning = isset($uicolors['warning'])?$uicolors['warning']:'#f29e0f';
        $danger = isset($uicolors['danger'])?$uicolors['danger']:'#ff5062';
        $font = get_option('__wpdm_google_font', 'Rubik');
        $font = $font?$font.',':'';
        if(is_singular('wpdmpro'))
            $ui_button = get_option('__wpdm_ui_download_button');
        else
            $ui_button = get_option('__wpdm_ui_download_button_sc');
        $class =  ".btn.".(isset($ui_button['color'])?$ui_button['color']:'btn-primary').(isset($ui_button['size']) && $ui_button['size'] != ''?".".$ui_button['size']:'');

        ?>
        <style>

            :root{
                --color-primary: <?php echo $primary; ?>;
                --color-primary-rgb: <?php echo wpdm_hex2rgb($primary); ?>;
                --color-primary-hover: <?php echo isset($uicolors['primary'])?$uicolors['primary_hover']:'#4a8eff'; ?>;
                --color-primary-active: <?php echo isset($uicolors['primary'])?$uicolors['primary_active']:'#4a8eff'; ?>;
                --color-secondary: <?php echo $secondary; ?>;
                --color-secondary-rgb: <?php echo wpdm_hex2rgb($secondary); ?>;
                --color-secondary-hover: <?php echo isset($uicolors['secondary'])?$uicolors['secondary_hover']:'#4a8eff'; ?>;
                --color-secondary-active: <?php echo isset($uicolors['secondary'])?$uicolors['secondary_active']:'#4a8eff'; ?>;
                --color-success: <?php echo $success; ?>;
                --color-success-rgb: <?php echo wpdm_hex2rgb($success); ?>;
                --color-success-hover: <?php echo isset($uicolors['success_hover'])?$uicolors['success_hover']:'#4a8eff'; ?>;
                --color-success-active: <?php echo isset($uicolors['success_active'])?$uicolors['success_active']:'#4a8eff'; ?>;
                --color-info: <?php echo $info; ?>;
                --color-info-rgb: <?php echo wpdm_hex2rgb($info); ?>;
                --color-info-hover: <?php echo isset($uicolors['info_hover'])?$uicolors['info_hover']:'#2CA8FF'; ?>;
                --color-info-active: <?php echo isset($uicolors['info_active'])?$uicolors['info_active']:'#2CA8FF'; ?>;
                --color-warning: <?php echo $warning; ?>;
                --color-warning-rgb: <?php echo wpdm_hex2rgb($warning); ?>;
                --color-warning-hover: <?php echo isset($uicolors['warning_hover'])?$uicolors['warning_hover']:'orange'; ?>;
                --color-warning-active: <?php echo isset($uicolors['warning_active'])?$uicolors['warning_active']:'orange'; ?>;
                --color-danger: <?php echo $danger; ?>;
                --color-danger-rgb: <?php echo wpdm_hex2rgb($danger); ?>;
                --color-danger-hover: <?php echo isset($uicolors['danger_hover'])?$uicolors['danger_hover']:'#ff5062'; ?>;
                --color-danger-active: <?php echo isset($uicolors['danger_active'])?$uicolors['danger_active']:'#ff5062'; ?>;
                --color-green: <?php echo isset($uicolors['green'])?$uicolors['green']:'#30b570'; ?>;
                --color-blue: <?php echo isset($uicolors['blue'])?$uicolors['blue']:'#0073ff'; ?>;
                --color-purple: <?php echo isset($uicolors['purple'])?$uicolors['purple']:'#8557D3'; ?>;
                --color-red: <?php echo isset($uicolors['red'])?$uicolors['red']:'#ff5062'; ?>;
                --color-muted: rgba(69, 89, 122, 0.6);
                --wpdm-font: <?php echo $font; ?> -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            }
            .wpdm-download-link<?php echo $class; ?>{
                border-radius: <?php echo (isset($ui_button['borderradius'])?$ui_button['borderradius']:4); ?>px;
            }


        </style>
        <?php

    }




}
