<?php
namespace WPDM;

class AuthorDashboard
{
    function __construct(){

        add_shortcode("wpdm_frontend", array($this, 'Dashboard'));
        add_shortcode("wpdm_package_list", array($this, 'packageList'));
        add_shortcode("wpdm_package_form", array($this, 'packageForm'));
        add_shortcode("wpdm_profile_info", array($this, 'editProfile'));
        add_shortcode("wpdm_author_settings", array($this, 'Settings'));
        add_action('wp_ajax_delete_package_frontend', array($this, 'deletePackage'));
        add_action('wp_ajax_wpdm_frontend_file_upload', array($this, 'uploadFile'));
        add_action('wp_ajax_wpdm_update_public_profile', array($this, 'updateProfile'));
        add_action('wp_ajax_wpdm_author_settings', array($this, 'saveSettings'));        
    }

    /**
     * @usage Short-code function for front-end UI
     * @return string
     */
    function Dashboard($params = array())
    {

        global $current_user;

        if(!is_user_logged_in()) {
            ob_start();
            if(isset($params['signup']) && $params['signup'] == 1)
                include \WPDM\Template::Locate('wpdm-be-member.php');
            else
                include  \WPDM\Template::Locate('wpdm-login-form.php');
            return ob_get_clean();
        }

        $msg = get_option('__wpdm_front_end_access_blocked', __( "Sorry, Your Are Not Allowed!" , "download-manager" ));
        $msg = $msg != ''?$msg:__( "Sorry, Your Are Not Allowed!" , "download-manager" );

        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));
        $urlfix = isset($params['flaturl']) && $params['flaturl'] == 0?1:0;
        update_post_meta(get_the_ID(), '__urlfix', $urlfix);
        $task = get_query_var('adb_page');
        if($urlfix == 1) $task = wpdm_query_var('adb_page');
        $task = explode("/", $task);

        if($task[0] == 'edit-package') $pid = $task[1];
        if($task[0] == 'page') { $task[0] = ''; set_query_var('paged', $task[1]); }
        $task = $task[0];

        if($task == 'edit-package' && get_post_type($pid) !== 'wpdmpro') return \WPDM_Messages::error(array('message' =>  __( "Package Not Found" , "download-manager" ), 'title' =>  __( "ERROR!" , "download-manager" )), -1);

        //if($task == 'edit-package' && get_current_user_id() == get_post_au) return \WPDM_Messages::error(array('message' =>  __( "Your are not authorized to edit this package" , "download-manager" ), 'title' =>  __( "ERROR!" , "download-manager" )), -1);

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        $id = wpdm_query_var('ID');


        $tabs = array(
            'manage-packs' => array('label' => __( "All Items" , "download-manager" ), 'shortcode' => "[wpdm_package_list view=table]"),
            'add-new' => array('label' => __( "Add New" , "download-manager" ), 'shortcode' => "[wpdm_package_form]")
        );
        $tabs = apply_filters('wpdm_frontend', $tabs);
        $tasks = array_keys($tabs);
        $task = $task == ''?$tasks[0]:$task;
        $burl = get_permalink();
        $sap = strpos($burl, '?') ? '&' : '?';

        $default_icons['seller-dashboard'] = 'fa fa-tachometer-alt';
        $default_icons['manage-packs'] = 'far fa-arrow-alt-circle-down';
        $default_icons['add-new'] = 'fa fa-file-upload';
        $default_icons['categories'] = 'fa fa-sitemap';
        $default_icons['sales'] = 'fa fa-chart-line';
        $default_icons['file-manager'] = 'far fa-images';

        ob_start();
        include \WPDM\Template::locate('author-dashboard.php');
        $data = ob_get_clean();
        wp_reset_query();
        return $data;
    }

    /**
     * @usage Delete package from front-end
     */
    function deletePackage()
    {
        global $wpdb, $current_user;
        if (isset($_GET['ID']) && intval($_GET['ID'])>0) {
            $id = (int)$_GET['ID'];
            $uid = $current_user->ID;
            if ($uid == '') die('Error! You are not logged in.');
            $post = get_post($id);
            if($post->post_author == $uid)
                wp_delete_post($id, true);
            echo "deleted";
            die();
        }
    }

    /**
     * @usage Upload files
     */
    function uploadFile(){

        global $current_user;

        $currentAccess = maybe_unserialize(get_option( '__wpdm_front_end_access', array()));
        // Check if user is authorized to upload file from front-end
        if(!is_user_logged_in() || !array_intersect($currentAccess, $current_user->roles) ) die(__( "Error! You are not allowed to upload files." , "download-manager" ));

        $upload_dir = current_user_can('manage_options')?UPLOAD_DIR:UPLOAD_DIR.$current_user->user_login.'/';

        check_ajax_referer(NONCE_KEY);

        $name = isset($_FILES['attach_file']['name']) && !isset($_REQUEST["chunks"])?$_FILES['attach_file']['name']:$_REQUEST['name'];

        if(file_exists($upload_dir.$name) && get_option('__wpdm_overwrite_file_frontend',0)==1 && !isset($_REQUEST["chunks"])){
            @unlink($upload_dir.$name);
        }
        if(file_exists($upload_dir.$name) && !isset($_REQUEST["chunks"]))
            $filename = time().'wpdm_'.$name;
        else
            $filename = $name;

        $filename = esc_html($filename);

        //move_uploaded_file($_FILES['attach_file']['tmp_name'],UPLOAD_DIR.$filename);
        //echo $filename;

        if(!file_exists($upload_dir)){
            mkdir($upload_dir);
            \WPDM\libs\FileSystem::blockHTTPAccess($upload_dir);
        }
        if (isset($_POST['current_path']) && $_POST['current_path'] != ''){
            $user_upload_dir = $upload_dir;
            $upload_dir  = realpath($upload_dir.'/'.$_POST['current_path']).'/';
            if(!strstr($upload_dir, $user_upload_dir)) die('Error! '. $upload_dir);
        }

        if(get_option('__wpdm_sanitize_filename', 0) == 1)
            $filename = sanitize_file_name($filename);

        if(isset($_REQUEST["chunks"])) $this->chunkUploadFile($upload_dir.$filename);
        else {
            $ret = move_uploaded_file($_FILES['attach_file']['tmp_name'], $upload_dir . $filename);
            if(!$ret) debug_print_backtrace();
        }
        echo "|||".str_replace(UPLOAD_DIR, '', $upload_dir).$filename."|||";

        exit;
    }

    function chunkUploadFile($destFilePath){

        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $out = @fopen("{$destFilePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
            // Read binary input stream and append it to temp file
            $in = @fopen($_FILES['attach_file']['tmp_name'], "rb");

            if ($in) {
                while ($buff = fread($in, 4096))
                    fwrite($out, $buff);
            } else
                die('-3');

            @fclose($in);
            @fclose($out);

            @unlink($_FILES['package_file']['tmp_name']);
        } else
            die('-3');

        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$destFilePath}.part", $destFilePath);
        }
    }

    function packageList($params = array()){

        global $current_user;

        if(!is_user_logged_in()) {
            return wpdm_login_form($params);
        }

        $msg = get_option('__wpdm_front_end_access_blocked', __( "Sorry, Your Are Not Allowed!" , "download-manager" ));
        $msg = $msg != ''?$msg:__( "Sorry, Your Are Not Allowed!" , "download-manager" );

        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        ob_start();
        echo "<div class='w3eden'>";
        include wpdm_tpl_path("wpdm-ad-package-list.php");
        echo "</div>";
        return ob_get_clean();
    }

    function packageForm($params = array()){
        global $current_user;
        if(!is_user_logged_in()) {
            return wpdm_login_form($params);
        }

        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        $msg = get_option('__wpdm_front_end_access_blocked', __( "Sorry, Your Are Not Allowed!" , "download-manager" ));
        $msg = $msg != ''?$msg:__( "Sorry, Your Are Not Allowed!" , "download-manager" );

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        $post = get_post(wpdm_query_var('id'));

        if(wpdm_query_var('id') > 0 && $current_user->ID != $post->post_author && !current_user_can('manage_options'))
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        ob_start();
        include wpdm_tpl_path("new-package-form.php");
        wp_reset_postdata();
        return ob_get_clean();
    }

    public static function hasAccess($uid = null){
        global $current_user;
        if(!$uid) $uid = $current_user->ID;
        if(current_user_can('manage_options')) return true;
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));
        return array_intersect($currentAccess, $current_user->roles) && is_user_logged_in()?true:false;
    }

    function updateProfile(){
        if(!is_user_logged_in()) die('Error!');
        $profile = wpdm_query_var('__wpdm_public_profile');
        update_user_meta(get_current_user_id(), '__wpdm_public_profile', $profile);
        if(isset($_POST['__wpdm_public_profile']['paypal']) && $_POST['__wpdm_public_profile']['paypal'] != '') {
            update_user_meta(get_current_user_id(), 'payment_account', esc_attr($_POST['__wpdm_public_profile']['paypal']));
        }
        die('OK');
    }

    function editProfile(){
        ob_start();
        include(wpdm_tpl_path('wpdm-edit-user-profile.php'));
        return ob_get_clean();
    }


    function Settings(){
        ob_start();
        $settings = get_user_meta(get_current_user_id(), '__wpdm_author_settings', true);
        include wpdm_tpl_path("author-settings.php");
        return ob_get_clean();
    }
    function saveSettings(){
        if(!self::hasAccess()) die('Error!');
        if(isset($_POST['__saveas']) && wp_verify_nonce($_POST['__saveas'], NONCE_KEY)){
            update_user_meta(get_current_user_id(), '__wpdm_author_settings', wpdm_query_var('__wpdm_author_settings'));
            do_action("wpdm_after_save_author_settings", $_POST['__wpdm_author_settings']);
            die('OK');

        }
        die('Error');
    }



}