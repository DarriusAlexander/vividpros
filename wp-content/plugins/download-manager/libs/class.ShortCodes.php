<?php
namespace WPDM;

static $shortcode_count = array();

class ShortCodes
{

    function __construct()
    {


        // Total Package Count
        add_shortcode('wpdm_package_count', array($this, 'TotalPackages'));

        // Total Package Count
        add_shortcode('wpdm_download_count', array($this, 'TotalDownloads'));

        // Login/Register Form
        add_shortcode('wpdm_login_form', array($this, 'loginForm'));

        // Logout URL
        add_shortcode('wpdm_logout_url', array($this, 'logoutURL'));

         // Register form
        add_shortcode('wpdm_reg_form', array($this, 'registerForm'));

        // Edit Profile
        //add_shortcode('wpdm_edit_profile', array($this, 'EditProfile'));

        // Show all packages
        add_shortcode('wpdm_packages', array($this, 'packages'));

        // Show a package by id
        add_shortcode('wpdm_package', array($this, 'package'));

        // Show a category link
        add_shortcode('wpdm_category_link', array($this, 'categoryLink'));

        // Generate direct download link
        add_shortcode('wpdm_direct_link', array($this, 'directLink'));

        // Show all packages in a responsive table
        add_shortcode('wpdm_all_packages', array($this, 'allPackages'));
        add_shortcode('wpdm-all-packages', array($this, 'allPackages'));

        //Packages by tag
        add_shortcode("wpdm_tag", array($this, 'packagesByTag'));

        //Search downloads
        add_shortcode( 'wpdm_search_result', array($this, 'searchResult'));

        //All Users
        add_shortcode( 'wpdm_authors', array($this, 'authors'));

        //User Favourites
        add_shortcode( 'wpdm_user_favourites', array($this, 'userFavourites'));

    }

    /**
     * @usage Short-code function for total download count
     * @param array $params
     * @return mixed
     */
    function totalDownloads($params = array()){
        global $wpdb;
        $download_count = $wpdb->get_var("select sum(meta_value) from {$wpdb->prefix}postmeta where meta_key='__wpdm_download_count'");
        return $download_count;
    }

    /**
     * @usage Short-code function for total package count
     * @param array $params
     * @return mixed
     */
    function totalPackages($params = array()){
        $count_posts = wp_count_posts('wpdmpro');
        $status = isset($params['status'])?$params['status']:'publish';
        if($status=='draft') return $count_posts->draft;
        if($status=='pending') return $count_posts->pending;
        return $count_posts->publish;
    }

    /**
     * @usage Short-code callback function for login/register form
     * @return string
     */
    function loginForm($params = array()){
        if(isset($params) && is_array($params))
            extract($params);
        if(!isset($redirect)) $redirect = get_permalink(get_option('__wpdm_user_dashboard'));
        ob_start();
        include(wpdm_tpl_path('wpdm-login-form.php'));
        return ob_get_clean();
    }

    /**
     * @usage Short-code callback function for logout url
     * @param $params
     * @return string|void
     */
    function logoutURL($params){
        $redirect = isset($params['r'])?$params['r']:'';
        return wpdm_logout_url($redirect);
    }



    /**
     * @usage Edit profile
     * @return string
     */
    public function editProfile()
    {
        global $wpdb, $current_user, $wp_query;
        //wp_reset_query();
        wp_reset_postdata();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return \WPDM_Messages::Error(wpautop(stripslashes(get_option('__wpdm_front_end_access_blocked'))), -1);


        $id = wpdm_query_var('ID');

        ob_start();
        echo "<div class='w3eden'>";
        if (is_user_logged_in()) {
            include(wpdm_tpl_path('wpdm-edit-user-profile.php'));
        } else {
            include(wpdm_tpl_path('wpdm-login-form.php'));
        }
        echo "</div>";

        $data = ob_get_clean();
        return $data;
    }

    function registerForm($params = array()){
        if(!get_option('users_can_register')) return __( "User registration is disabled" , "download-manager" );
        if(isset($params['role'])) update_post_meta(get_the_ID(),'__wpdm_role', $params['role']);
        ob_start();
        $regparams = \WPDM\libs\Crypt::Encrypt($params);

        include(wpdm_tpl_path('wpdm-reg-form.php'));

        $data = ob_get_clean();
        return $data;
    }

    /**
     * @param array $params
     * @return string
     */
    function packages($params = array('items_per_page' => 10, 'title' => false, 'desc' => false, 'order_by' => 'date', 'order' => 'DESC', 'paging' => false, 'page_numbers' => true, 'toolbar' => 1, 'template' => '','cols'=>3, 'colspad'=>2, 'colsphone' => 1, 'tags' => '', 'categories' => '', 'year' => '', 'month' => '', 's' => '', 'css_class' => 'wpdm_packages', 'e_id' => '', 'async' => 0))
    {

        static $wpdm_packages = 0;

        $wpdm_packages++;

        //$params['order_by']  = isset($params['order_field']) && $params['order_field'] != '' && !isset($params['order_by'])?$params['order_field']:$params['order_by'];
        $scparams = $params;
        $defaults = array('author' => '', 'author_name' => '', 'items_per_page' => 10, 'title' => false, 'desc' => false, 'order_by' => 'date', 'order' => 'DESC', 'paging' => false, 'page_numbers' => true, 'toolbar' => 1, 'template' => 'link-template-panel','cols'=>3, 'colspad'=>2, 'colsphone' => 1, 'css_class' => 'wpdm_packages', 'e_id' => 'wpdm_packages_'.$wpdm_packages, 'async' => 0);
        $params = shortcode_atts($defaults, $params, 'wpdm_packages' );

        if(is_array($params))
            extract($params);

        if(!isset($items_per_page) || $items_per_page < 1) $items_per_page = 10;

        $cwd_class = "col-md-".(int)(12/$cols);
        $cwdsm_class = "col-sm-".(int)(12/$colspad);
        $cwdxs_class = "col-xs-".(int)(12/$colsphone);

        if(isset($id)) {
            $id = trim($id, ", ");
            $cids = explode(",", $id);
        }

        global $wpdb, $current_user, $post, $wp_query;

        if(isset($order_by) && !isset($order_field)) $order_field = $order_by;
        $order_field = isset($order_field) ? $order_field : 'date';
        $order_field = isset($_GET['orderby']) ? esc_attr($_GET['orderby']) : $order_field;
        $order = isset($order) ? $order : 'desc';
        $order = isset($_GET['order']) ? esc_attr($_GET['order']) : $order;
        $cp = wpdm_query_var('cp','num');
        if(!$cp) $cp = 1;

        $params = array(
            'post_type' => 'wpdmpro',
            'paged' => $cp,
            'posts_per_page' => $items_per_page,
           // 'include_children' => false,
        );

        if(isset($scparams['s']) && $scparams['s'] != '') $params['s'] = $scparams['s'];
        if(isset($scparams['author']) && $scparams['author'] != '') $params['author'] = $scparams['author'];
        if(isset($scparams['author_name']) && $scparams['author_name'] != '') $params['author_name'] = $scparams['author_name'];
        if(isset($scparams['author__not_in']) && $scparams['author__not_in'] != '') $params['author__not_in'] = explode(",",$scparams['author__not_in']);
        if(isset($scparams['search']) && $scparams['search'] != '') $params['s'] = $scparams['search'];
        if(isset($scparams['tag']) && $scparams['tag'] != '') $params['tag'] = $scparams['tag'];
        if(isset($scparams['tag_id']) && $scparams['tag_id'] != '') $params['tag_id'] = $scparams['tag_id'];
        if(isset($scparams['tag__and']) && $scparams['tag__and'] != '') $params['tag__and'] = explode(",",$scparams['tag__and']);
        if(isset($scparams['tag__in']) && $scparams['tag__in'] != '') $params['tag__in'] = explode(",",$scparams['tag__in']);
        if(isset($scparams['tag__not_in']) && $scparams['tag__not_in'] != '') {
            $params['tag__not_in'] = explode(",",$scparams['tag__not_in']);
            foreach ($params['tag__not_in'] as &$tg){
                if(!is_numeric($tg)){
                    $tgg = get_term_by('slug', $tg, 'post_tag');
                    $tg = $tgg->term_id;
                }
            }
        }

        if(isset($scparams['tag_slug__and']) && $scparams['tag_slug__and'] != '') $params['tag_slug__and'] = explode(",",$scparams['tag_slug__and']);
        if(isset($scparams['tag_slug__in']) && $scparams['tag_slug__in'] != '') $params['tag_slug__in'] = explode(",",$scparams['tag_slug__in']);
        if(isset($scparams['categories']) && $scparams['categories'] != '') {
            $operator = isset($scparams['operator'])?$scparams['operator']:'OR';
            $params['tax_query'] = array(array(
                'taxonomy' => 'wpdmcategory',
                'field' => 'slug',
                'terms' => explode(",",$scparams['categories']),
                'include_children' => ( isset($scparams['include_children']) && $scparams['include_children'] != '' )?$scparams['include_children']: false,
                'operator' => $operator
            ));
        }

        if(isset($scparams['xcats']) && $scparams['xcats'] != '') {
            $xcats = explode(",",$scparams['xcats']);
            foreach ($xcats as &$xcat){
                if(!is_numeric($xcat)){
                    $xct = get_term_by('slug', $xcat, 'wpdmcategory');
                    $xcat = $xct->term_id;
                }
            }
            $params['tax_query'][] = array(
                'taxonomy' => 'wpdmcategory',
                'field'    => 'term_id',
                'terms'    => $xcats,
			    'operator' => 'NOT IN',
            );
        }



        if (get_option('_wpdm_hide_all', 0) == 1) {
            $params['meta_query'] = array(
                array(
                    'key' => '__wpdm_access',
                    'value' => '"guest"',
                    'compare' => 'LIKE'
                )
            );
            if(is_user_logged_in()){
                global $current_user;
                $params['meta_query'][] = array(
                    'key' => '__wpdm_access',
                    'value' => $current_user->roles[0],
                    'compare' => 'LIKE'
                );
                $params['meta_query']['relation'] = 'OR';
            }
        }

        if(isset($scparams['year']) ||isset($scparams['month']) || isset($scparams['day'])){
            $date_query = array();

            if(isset($scparams['day']) && $scparams['day'] == 'today') $scparams['day'] = date('d');
            if(isset($scparams['year']) && $scparams['year'] == 'this') $scparams['year'] = date('Y');
            if(isset($scparams['month']) && $scparams['month'] == 'this') $scparams['month'] = date('m');
            if(isset($scparams['week']) && $scparams['week'] == 'this') $scparams['week'] = date('W');

            if(isset($scparams['year']))  $date_query['year'] = $scparams['year'];
            if(isset($scparams['month']))  $date_query['month'] = $scparams['month'];
            if(isset($scparams['week']))  $date_query['week'] = $scparams['week'];
            if(isset($scparams['day']))  $date_query['day'] = $scparams['day'];
            $params['date_query'][] = $date_query;
        }

        $order_fields = array('__wpdm_download_count','__wpdm_view_count','__wpdm_package_size_b');
        if(!in_array( "__wpdm_".$order_field, $order_fields)) {
            $params['orderby'] = $order_field;
            $params['order'] = $order;
        } else {
            $params['orderby'] = 'meta_value_num';
            $params['meta_key'] = "__wpdm_".$order_field;
            $params['order'] = $order;
        }

        $params = apply_filters("wpdm_packages_query_params", $params);

        $packs = new \WP_Query($params);

        $total = $packs->found_posts;

        $pages = ceil($total / $items_per_page);
        $page = isset($_GET['cp']) ? (int)$_GET['cp'] : 1;
        $start = ($page - 1) * $items_per_page;

        if (!isset($paging) || intval($paging) == 1) {
            $pag = new \WPDM\libs\Pagination();
            $pag->items($total);
            $pag->showPageNumbers($page_numbers);
            $pag->nextLabel(' <i class="fa fa-arrow-right"></i> ');
            $pag->prevLabel(' <i class="fa fa-arrow-left"></i> ');
            if(isset($async) && $async == 1) {
                $pag->async(1);
                $pag->container('#'.$e_id);
            }
            $pag->limit($items_per_page);
            $pag->currentPage($page);
        }

        $burl = get_permalink();
        $url = $_SERVER['REQUEST_URI']; //get_permalink();
        $url = strpos($url, '?') ? $url . '&' : $url . '?';
        $url = preg_replace("/[\&]*cp=[0-9]+[\&]*/", "", $url);
        $url = strpos($url, '?') ? $url . '&' : $url . '?';
        if (!isset($paging) || intval($paging) == 1) {
            $url = str_replace("?&", "?", $url);
            $pag->urlTemplate($url . "cp=[%PAGENO%]");
        }


        $html = '';
        $templates = maybe_unserialize(get_option("_fm_link_templates", true));

        if(isset($template) && isset($templates[$template])) $template = $templates[$template]['content'];

        //global $post;
        while($packs->have_posts()) { $packs->the_post();

            $pack = (array)$post;
            $repeater = "<div class='{$cwd_class} {$cwdsm_class} {$cwdxs_class}'>".\WPDM\Package::fetchTemplate($template, $pack)."</div>";
            $html .=  $repeater;

        }
        wp_reset_postdata();

        $html = "<div class='row'>{$html}</div>";


        if (!isset($paging) || intval($paging) == 1)
            $pgn = "<div style='clear:both'></div>" . $pag->show() . "<div style='clear:both'></div>";
        else
            $pgn = "";
        global $post;

        $sap = get_option('permalink_structure') ? '?' : '&';
        $burl = $burl . $sap;
        if (isset($_GET['p']) && $_GET['p'] != '') $burl .= 'p=' . esc_attr($_GET['p']) . '&';
        if (isset($_GET['src']) && $_GET['src'] != '') $burl .= 'src=' . esc_attr($_GET['src']) . '&';
        $orderby = isset($_GET['orderby']) ? esc_attr($_GET['orderby']) : 'date';
        $order = ucfirst($order);

        $order_field = " " . __(ucwords(str_replace("_", " ", $order_field)),"wpdmpro");
        $ttitle = __( "Title" , "download-manager" );
        $tdls = __( "Downloads" , "download-manager" );
        $tcdate = __( "Publish Date" , "download-manager" );
        $tudate = __( "Update Date" , "download-manager" );
        $tasc = __( "Asc" , "download-manager" );
        $tdsc = __( "Desc" , "download-manager" );
        $tsrc = __( "Search" , "download-manager" );
        $ord = __( "Order" , "download-manager" );
        $order_by_label = __( "Order By" , "download-manager" );

        $css_class = isset($scparams['css_class'])?sanitize_text_field($scparams['css_class']):'';
        $desc = isset($scparams['desc'])?sanitize_text_field($scparams['desc']):'';

        $title = isset($title) && $title !=''?"<h3>$title</h3>":"";



        $toolbar = isset($toolbar)?(int)$toolbar:0;

        if ($toolbar) {
            $toolbar = <<<TBR
                
                   <div class="wpdm-toolbar" style="margin-bottom: 15px;">
                   
                   <div class="btn-group btn-group-sm pull-right"><button type="button" class="btn btn-primary" disabled="disabled">{$ord} &nbsp;</button><a class="btn btn-primary" href="{$burl}orderby={$orderby}&order=asc">{$tasc}</a><a class="btn btn-primary" href="{$burl}orderby={$orderby}&order=desc">{$tdsc}</a></div>                         
                   <div class="btn-group btn-group-sm"><button type="button" class="btn btn-info" disabled="disabled">{$order_by_label} &nbsp;</button><a class="btn btn-info" href="{$burl}orderby=title&order=asc">{$ttitle}</a><a class="btn btn-info" href="{$burl}orderby=publish_date&order=desc">{$tcdate}</a></div>                         
                    
                   </div>
                   
                    
                  
TBR;
        }
        else
            $toolbar = '';

        return "<div class='w3eden {$css_class}' id='{$e_id}' style='position: relative'>" . $title . $desc  . $toolbar . $html  . $pgn . "<div style='clear:both'></div></div>";
    }


    /**
     * @param array $params
     * @return array|null|WP_Post
     * @usage Shortcode callback function for [wpdm_search_result]
     */
    function searchResult( $params = array() ){
        global $wpdb;

        if(is_array($params))
            @extract($params);
        $template = isset($template) && $template != '' ? $template : 'link-template-calltoaction3';
        $async = isset($async) ? $async : 0;
        $items_per_page = isset($items_per_page) ? $items_per_page : 0;
        update_post_meta(get_the_ID(), "__wpdm_link_template", $template);
        update_post_meta(get_the_ID(), "__wpdm_items_per_page", $items_per_page);
        $strm = wpdm_query_var('search', 'txt');
        if($strm === '') $strm = wpdm_query_var('s', 'txt');
        $html = '';
        $cols = isset($cols)?$cols:1;
        $colspad = isset($colspad)?$colspad:1;
        $colsphone = isset($colsphone)?$colsphone:1;
        if(($strm == '' && isset($init) && $init == 1) || $strm != '')
        $html = $this->Packages(array('items_per_page' => $items_per_page, 'template' => $template, 's' => $strm, 'paging' => true, 'toolbar' => 0,'cols'=>$cols, 'colsphone'=>$colsphone, 'colspad'=>$colspad, 'async' => $async));
        $html = "<div class='w3eden'><form id='wpdm-search-form' style='margin-bottom: 20px'><div class='input-group input-group-lg'><div class='input-group-addon input-group-prepend'><span class=\"input-group-text\"><i class='fas fa-search'></i></span></div><input type='text' name='search' value='".$strm."' class='form-control input-lg' /></div></form>{$html}</div>";
        return str_replace(array("\r","\n"),"",$html);
    }

    /**
     * @usage Callback function for shortcode [wpdm_package id=PID]
     * @param mixed $params
     * @return mixed
     */
    function package($params)
    {
        extract($params);

        if(!isset($id)) return '';
        $id = (int)$id;
        if(get_post_type($id) != 'wpdmpro') return '';
        $postlink = site_url('/');
        if (isset($pagetemplate) && $pagetemplate == 1) {
            $template = get_post_meta($id,'__wpdm_page_template', true);
            $wpdm_package['page_template'] = stripcslashes($template);
            $data = wpdm_fetch_template($template, $id, 'page');
            $siteurl = site_url('/');
            return  "<div class='w3eden'>{$data}</div>";
        }

        $template = isset($params['template'])?$params['template']:get_post_meta($id,'__wpdm_template', true);
        if($template == '') $template = 'link-template-calltoaction3.php';
        $html = "<div class='w3eden'>" . \WPDM\Package::fetchTemplate($template, $id, 'link') . "</div>";
        //wp_reset_query();
        wp_reset_postdata();
        return $html;
    }


    /**
     * @usage Generate direct link to download
     * @param $params
     * @param string $content
     * @return string
     */
    function directLink($params, $content = "")
    {
        extract($params);
        global $wpdb;
        if(\WPDM\Package::isLocked($params['id']))
            $linkURL = get_permalink($params['id']);
        else
            $linkURL = home_url("/?wpdmdl=".$params['id']);
        $target = isset($params['target'])?"target='".sanitize_text_field($params['target'])."'":"";
        $class = isset($params['class'])?"class='".sanitize_text_field($params['class'])."'":"";
        $style = isset($params['style'])?"style='".sanitize_text_field($params['style'])."'":"";
        $eid = isset($params['eid'])?"id='".sanitize_text_field($params['eid'])."'":"";
        $linkLabel = isset($params['label']) && !empty($params['label'])?$params['label']:get_post_meta($params['id'], '__wpdm_link_label', true);
        $linkLabel = empty($linkLabel)?'Download '.get_the_title($params['id']):$linkLabel;
        return  "<a {$target} {$class} {$eid} {$style} href='$linkURL'>$linkLabel</a>";

    }

    /**
     * @usage Short-code [wpdm_all_packages] to list all packages in tabular format
     * @param array $params
     * @return string
     */
    function allPackages($params = array())
    {
        global $wpdb, $current_user, $wp_query;
        $items = isset($params['items_per_page']) && $params['items_per_page'] > 0 ? $params['items_per_page'] : 20;
        if(isset($params['jstable']) && $params['jstable']==1) $items = 2000;
        $cp = isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] : 1;
        $terms = isset($params['categories']) ? explode(",", $params['categories']) : array();
        $tag = isset($params['tag']) ? $params['tag'] : '';
        if (isset($_GET['wpdmc'])) $terms = array(esc_attr($_GET['wpdmc']));
        $offset = ($cp - 1) * $items;
        $total_files = wp_count_posts('wpdmpro')->publish;
        if (count($terms) > 0) {
            $tax_query = array(array(
                'taxonomy' => 'wpdmcategory',
                'field' => 'slug',
                'terms' => $terms,
                'operator' => 'IN',
                'include_children' => false
            ));
        }
        if($tag != ''){
            $params['tag'] = $tag;
        }
        if(isset($params['login']) && $params['login'] == 1 && !is_user_logged_in())
            return do_shortcode("[wpdm_login_form simple=1]");
        else {
            ob_start();
            include(wpdm_tpl_path("wpdm-all-downloads.php"));
            $data = ob_get_clean();
            return $data;
        }
    }

    /**
     * @usage Show packages by tag
     * @param $params
     * @return string
     */
    function packagesByTag($params)
    {
        $params['order_field'] = isset($params['order_by'])?$params['order_by']:'publish_date';
        $params['tag'] = 1;
        unset($params['order_by']);
        if (isset($params['item_per_page']) && !isset($params['items_per_page'])) $params['items_per_page'] = $params['item_per_page'];
        unset($params['item_per_page']);
        return wpdm_embed_category($params);

    }

    function categoryLink($params){

        $term = (array)get_term($params['id'], 'wpdmcategory');
        $icon = \WPDM\libs\CategoryHandler::icon($params['id']);
        $term['icon'] = $icon?"<img src='$icon' alt='{$term['name']}' />":"";
        $params['template'] = isset($params['template']) && $params['template'] != ''?$params['template']:'link-template-category-link.php';
        return \WPDM\Template::output($params['template'], $term);


    }


    function userFavourites($params = array()){
        global $wpdb, $current_user;
        if(!isset($params['user']) && !is_user_logged_in()) return $this->loginForm();
        ob_start();
        include wpdm_tpl_path('user-favourites.php');
        return ob_get_clean();
    }

    function authors($params = array()){
        $sid = isset($params['sid'])?$params['sid']:'';
        update_post_meta(get_the_ID(), '__wpdm_users_params'.$sid, $params);
        ob_start();
        include wpdm_tpl_path("list-authors.php");
        return ob_get_clean();
    }

    function listAuthors($params = null){

        if(!$params) $params = get_post_meta(wpdm_query_var('_pid', 'int'), '__wpdm_users_params'.wpdm_query_var('_sid'), true);
        $page = isset($_REQUEST['cp']) && $_REQUEST['cp'] > 0?(int)$_REQUEST['cp']:1;
        $items_per_page = isset($params['items_per_page'])?$params['items_per_page']:12;
        //$offset = $page * $items_per_page;
        $cols = isset($params['cols']) && in_array($params['cols'], array(1,2,3,4,6))?$params['cols']:0;
        if($cols > 0) $cols_class = "col-md-".(12/$cols);

        $args = array(
            'role'         => isset($params['role'])?$params['role']:'',
            'role__in'     => isset($params['role__in'])?explode(",", $params['role__in']):array(),
            'role__not_in' => isset($params['role__not_in'])?explode(",", $params['role__not_in']):array(),
            'meta_key'     => isset($params['meta_key'])?$params['meta_key']:'',
            'meta_value'   => isset($params['meta_value'])?$params['meta_value']:'',
            'meta_compare' => isset($params['meta_compare'])?$params['meta_compare']:'',
            //'meta_query'   => array(),
            //'date_query'   => array(),
            'include'      => isset($params['include'])?explode(",", $params['include']):array(),
            'exclude'      => isset($params['exclude'])?explode(",", $params['exclude']):array(),
            'orderby'      => isset($params['orderby'])?$params['orderby']:'login',
            'order'        => isset($params['order'])?$params['order']:'DESC',
            //'offset'       => $offset,
            'search'       => isset($params['search'])?$params['search']:'',
            'number'       => $items_per_page,
            'paged'       => $page,
            'count_total'  => true,
        );
        $users = new \WP_User_Query( $args );
        if($cols > 0) echo "<div class='row'>";
        foreach ($users->get_results() as $user) {
            if(isset($cols_class)) echo "<div class='$cols_class'>";
            include wpdm_tpl_path("author.php");
            if(isset($cols_class)) echo "</div>";
        }
        if($cols > 0) echo "</div>";
        $total = $users->get_total();
        $contid = isset($params['sid'])?"-{$params['sid']}":'';
        if(isset($params['paging']) && (int)$params['paging'] == 1)
            echo wpdm_paginate_links($total, $items_per_page, $page, 'cp', array('async' => 1, 'container' => "#wpdm-authors{$contid}"));
    }



}
