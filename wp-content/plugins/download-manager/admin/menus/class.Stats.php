<?php
/**
 * User: shahnuralam
 * Date: 11/9/15
 * Time: 7:44 PM
 */

namespace WPDM\admin\menus;


use WPDM\libs\FileSystem;

class Stats
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'Menu'));
        add_action('admin_init', array($this, 'Export'));
    }

    function Menu()
    {
        add_submenu_page('edit.php?post_type=wpdmpro', __( "Stats &lsaquo; Download Manager" , "download-manager" ), __("Stats" , "download-manager" ), WPDM_MENU_ACCESS_CAP, 'wpdm-stats', array($this, 'UI'));
    }

    function UI()
    {
        include(WPDM_BASE_DIR."admin/tpls/stats.php");
    }

    function Export(){
        if(wpdm_query_var('page') == 'wpdm-stats' && wpdm_query_var('task') == 'export'){
            global $wpdb;
            $data = $wpdb->get_results("select s.*, p.post_title as file from {$wpdb->prefix}ahm_download_stats s, {$wpdb->prefix}posts p where p.ID = s.pid order by id DESC");
            FileSystem::downloadHeaders("download-stats.csv");
            echo "Package,User ID,User Name,User Email,Order ID,Date,Timestamp\r\n";
            foreach ($data as $d){
                if($d->uid > 0) {
                    $u = get_user_by('ID', $d->uid);
                    echo "\"{$d->file}\",{$d->uid},\"{$u->display_name}\",\"{$u->user_email}\",{$d->oid},{$d->year}-{$d->month}-{$d->day},{$d->timestamp}\r\n";
                } else
                    echo "\"{$d->file}\",-,\"-\",\"-\",{$d->oid},{$d->year}-{$d->month}-{$d->day},{$d->timestamp}\r\n";
            }
            die();
        }
    }


}