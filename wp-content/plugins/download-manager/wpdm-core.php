<?php

/**
 * Warning!!!
 * Don't change any function from here
 *
 */

global $stabs, $package, $wpdm_package;


/**
 * @param $tablink
 * @param $newtab
 * @param $func
 * @deprecated Deprecated from v4.2, use filter hook 'add_wpdm_settings_tab'
 * @usage Deprecated: From v4.2, use filter hook 'add_wpdm_settings_tab'
 */
function add_wdm_settings_tab($tablink, $newtab, $func)
{
    global $stabs;
    $stabs["{$tablink}"] = array('id' => $tablink, 'icon' => 'fa fa-cog', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=' . $tablink, 'title' => $newtab, 'callback' => $func);
}

function wpdm_create_settings_tab($tabid, $tabtitle, $callback, $icon = 'fa fa-cog')
{
    return \WPDM\admin\menus\Settings::createMenu($tabid, $tabtitle, $callback, $icon);
}


/**
 * @usage Check user's download limit
 * @param $id
 * @return bool
 */
function wpdm_is_download_limit_exceed($id)
{
    return \WPDM\Package::userDownloadLimitExceeded($id);
}


/**
 * @param (int|array) $package Package ID (INT) or Complete Package Data (Array)
 * @param string $ext
 * @return string|void
 */
function wpdm_download_url($package, $ext = '')
{
    if (!is_array($package)) $package = intval($package);
    $id = is_int($package) ? $package : $package['ID'];
    return \WPDM\Package::getDownloadURL($id, $ext);
}


/**
 * @usage Check if a download manager category has child
 * @param $parent
 * @return bool
 */

function wpdm_cat_has_child($parent)
{
    $termchildren = get_term_children($parent, 'wpdmcategory');
    if (count($termchildren) > 0) return count($termchildren);
    return false;
}

/**
 * @usage Get category checkbox list
 * @param int $parent
 * @param int $level
 * @param array $sel
 */
function wpdm_cblist_categories($parent = 0, $level = 0, $sel = array())
{
    $cats = get_terms('wpdmcategory', array('hide_empty' => false, 'parent' => $parent));
    if (!$cats) $cats = array();
    if ($parent != '') echo "<ul>";
    foreach ($cats as $cat) {
        $id = $cat->slug;
        $pres = $level * 5;

        if (in_array($id, $sel))
            $checked = 'checked=checked';
        else
            $checked = '';
        echo "<li style='margin-left:{$pres}px;padding-left:0'><label><input id='c$id' type='checkbox' name='file[category][]' value='$id' $checked /> " . $cat->name . "</label></li>\n";
        wpdm_cblist_categories($cat->term_id, $level + 1, $sel);

    }
    if ($parent != '') echo "</ul>";
}

/**
 * @usage Get category dropdown list
 * @param string $name
 * @param string $selected
 * @param string $id
 * @param int $echo
 * @return string
 */
function wpdm_dropdown_categories($name = '', $selected = '', $id = '', $echo = 1)
{
    return wp_dropdown_categories(array('show_option_none' => __( "Select category" , "download-manager" ), 'hierarchical' => 1, 'show_count' => 0, 'orderby' => 'name', 'echo' => $echo, 'class' => 'form-control selectpicker', 'taxonomy' => 'wpdmcategory', 'hide_empty' => 0, 'name' => $name, 'id' => $id, 'selected' => $selected));

}


/**
 * @usage Post with cURL
 * @param $url
 * @param $data
 * @return bool|mixed|string
 */
function remote_post($url, $data)
{

    $response = wp_remote_post($url, array(
            'method' => 'POST',
            'sslverify' => false,
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => $data,
            'cookies' => array()
        )
    );
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return $error_message;
    } else {
        return $response['body'];
    }
}

/**
 * @usage Get with cURL
 * @param $url
 * @return bool|mixed|string
 */
function remote_get($url)
{
    $content = "";
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    if (is_array($response)) {
        $content = $response['body'];
    } else
        $content = WPDM_Messages::error($response->get_error_message(), -1);
    return $content;
}


function is_valid_license_key()
{
    $key = isset($_POST['_wpdm_license_key']) ? $_POST['_wpdm_license_key'] : get_option('_wpdm_license_key');
    update_option("__wpdm_nlc", strtotime('+7 days'));
    $domain = strtolower(str_replace("www.", "", $_SERVER['HTTP_HOST']));
    if (file_exists(WPDM_CACHE_DIR . "wpdm_{$domain}")) {
        $data = unserialize(base64_decode(file_get_contents(WPDM_CACHE_DIR . "wpdm_{$domain}")));
        if ($data[0] == md5($domain . $key) && $data[1] > time())
            return true;
        else
            @unlink(WPDM_CACHE_DIR . "wpdm_{$domain}");
    }
    //$res = remote_post('https://www.wpdownloadmanager.com/', array('action' => 'wpdm_pp_ajax_call', 'execute' => 'verifylicense', 'domain' => $domain, 'key' => $key, 'product' => 'wpdmpro'));
    $res = remote_post('https://www.wpdownloadmanager.com/', array('wpdmLicense' => 'validate', 'domain' => $domain, 'licenseKey' => $key, 'productId' => 'wpdmpro'));
    $res = json_decode($res);

    if ($res->status === 'VALID') {
        file_put_contents(WPDM_CACHE_DIR . "wpdm_{$domain}", base64_encode(serialize(array(md5($domain . $key), strtotime("+30 days")))));
        return true;
    }
    if (get_option('settings_ok') == '')
        update_option('settings_ok', strtotime('+30 days'));
    else {
        $page = isset($_GET['page']) ? $_GET['page'] : "";
        $time = (int)get_option('settings_ok');
        if ($time < time() && $page == 'settings' && (!isset($_GET['tab']) || $_GET['tab'] != 'license')) {
            die("<script>location.href='edit.php?post_type=wpdmpro&page=settings&tab=license';</script>");
        }
    }
    return false;
}


function wpdm_check_license()
{
    if ((int)get_option('__wpdm_nlc') > time()) return true;
    if ($_SERVER['HTTP_HOST'] == 'localhost') return true;
    if((int)\WPDM\Session::get('__key_valid') === 1) true;
    //if (isset($_SESSION['__key_valid']) && $_SESSION['__key_valid'] == 1) return true;
    //if (!isAjax()) {
    if (!is_valid_license_key()) {
        $time = (int)get_option('settings_ok');
        if ($time > time())
            wp_die("
        <div id=\"warning\" class=\"error fade\"><p>
        Please enter a valid <a href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for <b>Download Manager</b> 
        </div>
        ");
        else
            wp_die("
        <div id=\"warning\" class=\"error fade\"><p>
        Trial period for <b>Download Manager</b> is expired.<br/>
        Please enter a valid <a href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for <b>Download Manager</b> to reactivate it.<br/>
        <a href='https://www.wpdownloadmanager.com/'>Buy your copy now only at 59.00 usd</a>
        </div>
        ");
    } else {
        \WPDM\Session::set('__key_valid', 1);
        //$_SESSION['__key_valid'] = 1;
    }
    //}
}

function wpdm_license_notice()
{
    if ((int)get_option('__wpdm_nlc') > time()) return '';
    if ($_SERVER['HTTP_HOST'] == 'localhost') return '';
    //if (!isAjax()) {
    if (!is_valid_license_key()) {
        $time = (int)get_option('settings_ok');
        if ($time > time())
            return "
        <div class='w3eden'><div id=\"warning\" class=\"alert alert-danger\"><p>
        Please enter a valid <a href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for <b>Download Manager</b>
        </div></div>
        ";
        else
            return ("
        <div class='w3eden'><div id=\"warning\" class=\"alert alert-danger\"><p>
        Trial period for <b>Download Manager</b> is expired.<br/>
        Please enter a valid <a style='font-weight: 900;text-decoration: underline' href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for Download Manager to reactivate it.<br/>
        <a href='https://www.wpdownloadmanager.com/'>Buy your copy now only at 59.00 usd</a>
        </div></div>
        ");
    }
    //}
    return '';
}

function wpdm_access_token(){
    $at = get_option("__wpdm_access_token", false);
    if($at)
        return $at;
    if(get_option('__wpdm_suname') != '') {
        $auth = array('login' => get_option('__wpdm_suname'), 'pass' => get_option('__wpdm_supass'));
        $auth = base64_encode(serialize($auth));
        $access_token = remote_get('https://www.wpdownloadmanager.com/?wpdm_api_req=getAccessToken&__wpdm_auth=' . $auth);
        $access_token = json_decode($access_token);
        //wpdmdd($access_token);
        if (isset($access_token->access_token)) {
            update_option("__wpdm_access_token", $access_token->access_token);
            return $access_token->access_token;
        }
    }
    return '';
}


function wpdm_admin_license_notice()
{
    if (basename($_SERVER['REQUEST_URI']) != 'plugins.php' && basename($_SERVER['REQUEST_URI']) != 'index.php' && get_post_type() != 'wpdmpro') return '';
    if ((int)get_option('__wpdm_nlc') > time()) return '';
    if ($_SERVER['HTTP_HOST'] == 'localhost') return '';
    //if (!isAjax()) {
    if (!is_valid_license_key()) {
        $time = (int)get_option('settings_ok');
        if ($time > time())
            echo "
        <div id=\"error\" class=\"error\" style='border-left: 0 !important;border-top: 3px solid #dd3d36 !important;'><p>
        Please enter a valid <a href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for <b>Download Manager</b></p>
        </div>
        ";
        else
            echo("
        <div id=\"error\" class=\"error\" style='border-left: 0 !important;border-top: 3px solid #dd3d36 !important;'><p>
        Trial period for <b>Download Manager</b> is expired.<br/>
        Please enter a valid <a style='font-weight: 900;text-decoration: underline' href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for Download Manager to reactivate it.<br/>
        <a href='https://www.wpdownloadmanager.com/'>Buy your copy now only at 59.00 usd</a></p>
        </div>
        ");
    }
    //}
}


function wpdm_ajax_call_exec()
{
    if (isset($_POST['action']) && $_POST['action'] == 'wpdm_ajax_call') {
        if ($_POST['execute'] == 'wpdm_getlink')
            wpdm_getlink();
        die();
    }
}


function wpdm_plugin_data($dir)
{
    $plugins = get_plugins();
    foreach ($plugins as $plugin => $data) {
        $data['plugin_index_file'] = $plugin;
        $plugin = explode("/", $plugin);
        if ($plugin[0] == $dir) return $data;
    }
    return false;
}

function wpdm_plugin_update_email($plugin_name, $version, $update_url)
{

    $admin_email = get_option('admin_email');
    $hash = "__wpdm_" . md5($plugin_name . $version);
    $sent = get_option($hash, false);
    if (!$sent) {

        $message = 'New version available. Please update your copy.<br/><table class="email" style="width: 100%" cellpadding="5px"><tr><th>Plugin Name</th><th>Version</th></tr><tr><td>' . $plugin_name . '</td><td>' . $version . '</td></tr></table><div style="padding-top: 10px;"><a style="display: block;text-align: center" class="button" href="' . $update_url . '">Update Now</a></div>';

        $params = array(
                'subject' => sprintf(__("[%s] Update Available"), $plugin_name, 'download-manager'),
            'to_email' => get_option('admin_email'),
            'from_name' => 'WordPress Download Manager',
            'from_email' => 'support@wpdownloadmanager.com',
            'message' => $message
        );

        \WPDM\Email::send("default", $params);
        update_option($hash, 1);

    }
}

function wpdm_check_update()
{

    if (!current_user_can(WPDM_ADMIN_CAP)) die();

    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    $latest = get_option('wpdm_latest');
    $latest_check = get_option('wpdm_latest_check');
    $time = time() - intval($latest_check);
    $plugins = get_plugins();

    $latest_v_url = 'https://cdn.wpdownloadmanager.com/versions.php';

    if ($latest == '' || $time > 86400) {
        $latest = remote_get($latest_v_url);
        update_option('wpdm_latest', $latest);
        update_option('wpdm_latest_check', time());

    }
    $latest = maybe_unserialize($latest);


    $page = isset($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : '';
    $plugin_info_url = isset($_REQUEST['plugin_url']) ? $_REQUEST['plugin_url'] : 'https://www.wpdownloadmanager.com/purchases/';
    if (is_array($latest)) {
        foreach ($latest as $plugin_dir => $latestv) { 
            if ($plugin_dir != 'download-manager') {
                if (get_option('wpdm_update_notice') == 'disabled' || !($page == 'plugins' || get_post_type() == 'wpdmpro')) die('');
                $plugin_data = wpdm_plugin_data($plugin_dir);

                $plugin_name = $plugin_data['Name'];
                $plugin_info_url = $plugin_data['PluginURI'];
                $active = is_plugin_active($plugin_data['plugin_index_file']) ? 'active' : '';

                if (version_compare($plugin_data['Version'], $latestv, '<') == true) {
                    $trid = sanitize_title($plugin_name);
                    $plugin_update_url = admin_url('/edit.php?post_type=wpdmpro&page=settings&tab=plugin-update&plugin=' . $plugin_dir); //'https://www.wpdownloadmanager.com/purchases/?'; //
                    if ($trid != '') {
                        wpdm_plugin_update_email($plugin_name, $latestv, $plugin_update_url);
                        if ($page == 'plugins') {
                            echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        jQuery('tr:data[data-slug={$trid}]').addClass('update').after('<tr class="plugin-update-tr {$active} update"><td colspan=3 class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>There is a new version of <strong>{$plugin_name}</strong> available. <a href="{$plugin_update_url}&v={$latestv}" style="margin-left:10px" target=_blank>Update now ( v{$latestv} )</a></p></div></td></tr>');
      });
      </script>
NOTICE;
                        } else {
                            echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        jQuery('.wrap > h2').after('<div class="updated error" style="margin:10px 0px;padding:10px;border-left:2px solid #dd3d36;background: #ffffff"><div style="float:left;"><b style="color:#dd3d36;">Important!</b><br/>There is a new version of <u>{$plugin_name}</u> available.</div> <a style="border-radius:0; float:right;;color:#ffffff; background: #D54E21;padding:10px 15px;text-decoration: none;font-weight: bold;font-size: 9pt;letter-spacing:1px" href="{$plugin_update_url}&v={$latestv}"  target=_blank><i class="fa fa-sync"></i> update v{$latestv}</a><div style="clear:both"></div></div>');
         });
         </script>
NOTICE;
                        }
                    }
                }
            } else {
                $os = 'Completed';
                $license_key = get_option('_wpdm_license_key', '');
                if($license_key != '') {
                    $item = remote_get('https://www.wpdownloadmanager.com/?wpdmppaction=getlicensedetails&license=' . $license_key);
                    $item = json_decode($item);
                    $os = $item->order_status;
                    $oid = $item->order_id;
                    $valid = is_valid_license_key();
                } else {
                    $os = 'Invalid';
                    $oid = '0';
                    $valid = false;
                }
                $plugin_data = wpdm_plugin_data($plugin_dir);

                $plugin_name = $plugin_data['Name'];
                $plugin_info_url = $plugin_data['PluginURI'];
                $active = is_plugin_active($plugin_data['plugin_index_file']) ? 'active' : '';

                if (version_compare($plugin_data['Version'], $latestv, '<') == true) {

                    wpdm_plugin_update_email($plugin_name, $latestv, admin_url('plugins.php'));
                }
                $trid = 'download-manager-pro';

                $vlic = admin_url('edit.php?post_type=wpdmpro&page=settings&tab=license');
                $wpdmli = admin_url('edit.php?post_type=wpdmpro&page=settings&tab=plugin-update');
                $wpdmun = get_option('__wpdm_suname');
                if($page == 'plugins') {
                    if (!$valid || $wpdmun == '') {
                        $message = __( "Please login to activate automatic update <strong><a href=\"{$wpdmli}\" target=_blank>Login Now</a></strong>" , "download-manager" );
                        if(!$valid)
                            $message = "Please activate <strong>{$plugin_name}</strong> license for automatic update. <a href=\"{$vlic}\" target=_blank>Validate license key</a>";
                        echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        if(jQuery('#download-manager-pro-update').html() != undefined ) {       
            jQuery('#download-manager-pro-update td').append('<div class="update-message notice inline notice-error notice-alt"><p>{$message}</p></div>');
            }
        else {
       
            if(jQuery('tr:data[data-slug={$trid}]').html() != undefined)
                jQuery('tr:data[data-slug={$trid}]').addClass('update').after('<tr class="plugin-update-tr {$active} active update" id="{$trid}-expired"><td colspan=3 class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p>{$message}</p></div></td></tr>');
            else    
                jQuery('tr:data[data-slug=download-manager]').addClass('update').after('<tr class="plugin-update-tr {$active} active update" id="{$trid}-expired"><td colspan=3 class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p>{$message}</p></div></td></tr>');
           }
      });
      </script>
NOTICE;

                    } else if ($os == 'Expired') {
                        echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        if(jQuery('#download-manager-pro-update').html() !='' ) 
            jQuery('#download-manager-pro-update td').append('<div class="update-message notice inline notice-error notice-alt"><p><strong>{$plugin_name}</strong> support and update period is expired. <a href="https://www.wpdownloadmanager.com/user-dashboard/purchases/order/{$oid}/" target=_blank>Renew now</a></p></div>');
        else {
            jQuery('tr:data[data-slug={$trid}]:last-child').addClass('update').after('<tr class="plugin-update-tr {$active} update" id="{$trid}-expired"><td colspan=3 class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p><strong>{$plugin_name}</strong> support and update period is expired. <a href="https://www.wpdownloadmanager.com/user-dashboard/purchases/order/{$oid}/" target=_blank>Renew now ( v{$latestv} )</a></p></div></td></tr>');
            jQuery('tr:data[data-slug=download-manager]:last-child').addClass('update').after('<tr class="plugin-update-tr {$active} update" id="{$trid}-expired"><td colspan=3 class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p><strong>{$plugin_name}</strong> support and update period is expired. <a href="https://www.wpdownloadmanager.com/user-dashboard/purchases/order/{$oid}/" target=_blank>Renew now ( v{$latestv} )</a></p></div></td></tr>');
           }
      });
      </script>
NOTICE;

                    }
                }
            }
        }
    }
    if (wpdm_is_ajax())
        die('ok');
}

function wpdm_newversion_check()
{

    global $pagenow;
    if (!current_user_can(WPDM_ADMIN_CAP)) return;

    if(!in_array($pagenow, array('plugins.php'))) return;
    $tmpvar = explode("?", basename($_SERVER['REQUEST_URI']));
    $page = array_shift($tmpvar);
    $page = explode(".", $page);
    $page = array_shift($page);


    $page = $page == 'plugins' ? $page : get_post_type();

    ?>
    <script type="text/javascript">
        jQuery(function () {
            console.log('Checking WPDM Version!');
            jQuery.post(ajaxurl, {
                action: 'wpdm_check_update',
                page: '<?php echo $page; ?>'
            }, function (res) {
                jQuery('#wpfooter').after(res);
            });


        });
    </script>

    <?php
}

function wpdm_core_update_plugin($update_plugins){

    if ( ! is_object( $update_plugins ) )
        return $update_plugins;
    if ( ! isset( $update_plugins->response ) || ! is_array( $update_plugins->response ) )
        $update_plugins->response = array();

    $latest = get_option('wpdm_latest', '');
    $latest_check = get_option('wpdm_latest_check');
    $time = time() - intval($latest_check);

    if ($latest == '' || $time > 86400) {
        $latest_v_url = 'https://cdn.wpdownloadmanager.com/versions.php';
        $latest = remote_get($latest_v_url);
        update_option('wpdm_latest', $latest);
        update_option('wpdm_latest_check', time());
    }
    $latest = maybe_unserialize($latest);

    //$pluign = wpdm_plugin_data('download-manager');
    $wpdm_current = WPDM_Version;
    $wpdm_latest = is_array($latest) && isset($latest['download-manager'])?$latest['download-manager']:WPDM_Version;

    if(version_compare($wpdm_current, $wpdm_latest, '>=')) return $update_plugins;

    $upcheck = get_option('__wpdm_core_update_check', false);

    if(($upcheck && (time() - $upcheck) < 86400) && isset($update_plugins->response['download-manager/download-manager.php'])) return $update_plugins;

    $download_url = '';
    $valid = false;
    if(\WPDM\Session::get('__wpdm_download_url')) $download_url = \WPDM\Session::get('__wpdm_download_url');
    else {
        if(get_option('__wpdm_suname') != '')
            $valid = is_valid_license_key();
        if ($valid === true) {
            $item = remote_get('https://www.wpdownloadmanager.com/?wpdmppaction=getlicensedetails&license=' . get_option('_wpdm_license_key'));
            $item = json_decode($item);
            $download_url = is_object($item) && isset($item->download_url)?$item->download_url:'';
            if ($download_url != '' && get_option('__wpdm_suname') != '') $download_url .= '&wpdm_access_token=' . wpdm_access_token();
            else $download_url = '';
            update_option("__wpdm_core_update_check", time());
        }
    }

    $update_plugins->response['download-manager/download-manager.php'] = (object)array(
        'slug'         => 'download-manager-pro',
        'plugin'         => 'download-manager/download-manager.php',
        'new_version'  => $latest['download-manager'],
        'url'          => 'https://www.wpdownloadmanager.com/',
        'package'      => $download_url,
    );

    \WPDM\Session::set('__wpdm_download_url', $download_url);

    return $update_plugins;

}

add_action('admin_notices', 'wpdm_admin_license_notice');
add_filter( 'site_transient_update_plugins', 'wpdm_core_update_plugin' );
add_filter( 'transient_update_plugins', 'wpdm_core_update_plugin' );




if (!isset($_REQUEST['P3_NOCACHE'])) {

    include(dirname(__FILE__) . "/wpdm-hooks.php");

    $files = scandir(dirname(__FILE__) . '/modules/');
    foreach ($files as $file) {
        $tmpdata = explode(".", $file);
        if ($file != '.' && $file != '..' && !@is_dir($file) && end($tmpdata) == 'php')
            include(dirname(__FILE__) . '/modules/' . $file);
    }
}


