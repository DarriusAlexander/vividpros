<?php

if (!defined('ABSPATH')) die('Error!');

error_reporting(0);

global $current_user, $dfiles;

//Check for blocked IPs
if(wpdm_ip_blocked()) {
    $_ipblockedmsg =  __('Your IP address is blocked!', 'download-manager');
    $ipblockedmsg = get_option('__wpdm_blocked_ips_msg', '');
    $ipblockedmsg = $ipblockedmsg == ''?$_ipblockedmsg:$ipblockedmsg;
    WPDM_Messages::Error($ipblockedmsg, 1);
}

//Check for blocked users by email
if(is_user_logged_in() && !wpdm_verify_email($current_user->user_email)) {
    WPDM_Messages::fullPage('Error!', __('Your email address is blocked!', 'download-manager'), 'error');
}

do_action("wpdm_onstart_download", $package);

$speed = 1024; //in KB - default 1 MB
$speed = apply_filters('wpdm_download_speed', $speed);
$user = get_user_by('id', $package['post_author']);
$user_upload_dir = UPLOAD_DIR . $user->user_login . '/';


$_content_dir = str_replace('\\','/',WP_CONTENT_DIR);
$_old_up_dir = $_content_dir.'/uploads/download-manager-files/';


if(in_array($package['post_status'], array('draft','inherit','trash','pending'))) wp_die(__( "Package is not available!" , "download-manager" ));

$limit_msg = stripslashes(get_option("__wpdm_download_limit_exceeded"));
$limit_msg = trim($limit_msg)?$limit_msg:__( "Download limit exceeded!" , "download-manager" );
if (wpdm_is_download_limit_exceed($package['ID'])) WPDM_Messages::fullPage("Error!", $limit_msg, 'error');
$files = \WPDM\Package::getFiles($package['ID']);
$fileCount = count($files);

$log = new \WPDM\libs\DownloadStats();
$oid = isset($_GET['oid']) ? esc_attr($_GET['oid']) : '';
$indsc = 1;
$indsc = isset($_GET['ind']) && get_option('__wpdm_ind_stats') == 0 ? 0 : 1;

if (($indsc && !isset($_GET['nostat'])) || $oid != '')
    $log->newStat($package['ID'], $current_user->ID, $oid);

if ($fileCount == 0) {
    if (isset($package['sourceurl']) && $package['sourceurl'] != '') {

        if (!isset($package['url_protect']) || $package['url_protect'] == 0 && strpos($package['sourceurl'], '://')) {
            header('location: ' . $package['sourceurl']);
            die();
        }

        $r_filename = basename($package['sourceurl']);
        $r_filename = explode("?", $r_filename);
        $r_filename = $r_filename[0];
        wpdm_download_file($package['sourceurl'], $r_filename, $speed, 1, $package);
        die();
    }

    wpdm_download_data('download-not-available.txt', __( "Sorry! Download is not available yet." , "download-manager" ));
    die();

}

$idvdl = ( \WPDM\Package::isSingleFileDownloadAllowed($package['ID']) || isset($_GET['oid']) ) && isset($_GET['ind']); //isset($package['individual_file_download']) && isset($_GET['ind']) ? 1 : 0;

$parallel_download = (int)get_option('__wpdm_parallel_download', 1);

if($parallel_download === 0 && (int)\WPDM\TempStorage::get("download.".wpdm_get_client_ip()) === 1)
    WPDM_Messages::error(get_option('__wpdm_parallel_download_msg', "Another download is in progress from your IP, please wait until finished."), 1);

if ($fileCount > 1 && !$idvdl) {

    $zipped = get_post_meta($package['ID'], "__wpdm_zipped_file", true);
    $cache_zip = get_option('__wpdm_cache_zip', 0);
    $cache_zip_po = get_post_meta($package['ID'], "__wpdm_cache_zip", true);
    //dd($cache_zip_po);
    $cache_zip = $cache_zip_po == -1 || !$cache_zip_po ? $cache_zip : $cache_zip_po;
    if ($zipped == '' || !file_exists($zipped) || $cache_zip == 0) {
        if(class_exists('ZipArchive'))
        $zip = new ZipArchive();
        else wp_die(__( "Please activate \"zlib\" in your server" , "download-manager" ));
        $zipped = UPLOAD_DIR . sanitize_file_name($package['post_title']) . '-' . $package['ID'] . '.zip';

        if ($zip->open($zipped, ZIPARCHIVE::CREATE) !== TRUE) {
            wpdm_download_data('error.txt', 'Failed to create file. Please make "' . UPLOAD_DIR . '" dir writable..');
            die();
        }

        foreach ($files as $file) {
            $file = trim($file);
            if (file_exists(UPLOAD_DIR . $file)) {
                $fpath = UPLOAD_DIR . $file;
                $zip->addFile($fpath, wpdm_basename($file));
            }
            else if (file_exists($user_upload_dir.$file))
                $zip->addFile($user_upload_dir.$file, wpdm_basename($file));
            else if (file_exists($file))
                $zip->addFile($file, wpdm_basename($file));
            else if (file_exists(WP_CONTENT_DIR . end($tmp = explode("wp-content", $file)))) {//path fix on site move
                $tmpf = explode("wp-content", $file);
                $zip->addFile(WP_CONTENT_DIR . end($tmpf), wpdm_basename($file));
            }
            else if (file_exists($_old_up_dir . $file))
                $zip->addFile($_old_up_dir . $file);
        }
        if ($dfiles) {
            foreach ($dfiles as $file) {
                $zip->addFile($file, str_replace($dir, '', $file));
            }
        }

        $zip->close();
        update_post_meta($package['ID'], "__wpdm_zipped_file", $zipped);
    }

    wpdm_download_file($zipped, sanitize_file_name($package['post_title']) . '.zip', $speed, 1, $package);
    //@unlink($zipped);
} else {

    //Individual file or single file download section

    $indfile = '';

    if (isset($_GET['ind'])) {
        $indfile = isset($files[esc_attr($_GET['ind'])])?$files[esc_attr($_GET['ind'])]:\WPDM\libs\Crypt::Decrypt(esc_attr($_GET['ind']));

    } else if ($fileCount == 1) {
        $indfile = array_shift($files);
    }

    //URL Download
    if ($indfile != '' && strpos($indfile, '://')) {

        if (!isset($package['url_protect']) || $package['url_protect'] == 0) {
            $indfile = wpdm_escs(htmlspecialchars_decode($indfile));
            header('location: ' . $indfile);

        } else {
            $r_filename = wpdm_basename($indfile);
            $r_filename = explode("?", $r_filename);
            $r_filename = $r_filename[0];
            wpdm_download_file($indfile, $r_filename, $speed, 1, $package);

        }

        die();
    }

    if ($indfile != '' && file_exists(UPLOAD_DIR . $indfile))
        $filepath = UPLOAD_DIR . $indfile;
    else if ($indfile != '' && file_exists($user_upload_dir.$indfile))
        $filepath = $user_upload_dir.$indfile;
    else if ($indfile != '' && file_exists($indfile))
        $filepath = $indfile;
    else if ($indfile != '' && file_exists(WP_CONTENT_DIR . end($tmp = explode("wp-content", $indfile)))) //path fix on site move
        $filepath = WP_CONTENT_DIR . end($tmp = explode("wp-content", $indfile));
    else if ($indfile != '' && file_exists($_old_up_dir . $indfile)) //path fix on site move
        $filepath = $_old_up_dir . $indfile;

    else {
        do_action("wpdm_file_not_found", $package);
        wpdm_download_data('file-not-found.txt', __( "File not found or deleted from server" , "download-manager" ));
        die();
    }

    //$plock = get_wpdm_meta($file['id'],'password_lock',true);
    //$fileinfo = get_wpdm_meta($package['id'],'fileinfo');

    $filepath = str_replace('\\','/', $filepath );
    $filename = wpdm_basename($filepath);
    $filename = preg_replace("/([0-9]+)[wpdm]+_/", "", $filename);

    wpdm_download_file($filepath, $filename, $speed, 1, $package);
    //@unlink($filepath);

}

\WPDM\TempStorage::kill("download.".wpdm_get_client_ip());

do_action("after_download", $package);

die();

