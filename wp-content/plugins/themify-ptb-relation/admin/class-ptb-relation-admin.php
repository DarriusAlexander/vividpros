<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    PTB
 * @subpackage PTB/public
 * @author     Themify <ptb@themify.me>
 */
class PTB_Relation_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;
    
    private $submission_values = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version, $options) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $options;
    }

    /**
     * Register the script/stylesheets for the adminpanel.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screens = $this->screens(array(), false);
        $screen = get_current_screen();
        $pluginurl = plugin_dir_url(dirname(__FILE__));
        if (in_array($screen->id, $screens)) {
            wp_enqueue_style($this->plugin_name, $pluginurl . 'admin/css/ptb-relation.css', array(), $this->version, 'all');
            wp_enqueue_script($this->plugin_name . '-cmb', $pluginurl . 'admin/js/ptb-cmb-relation.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name, $pluginurl . 'admin/js/ptb-relation.js', array('jquery'), $this->version, false);
        } elseif ($screen->id == 'post-type-builder_page_ptb-ptt') {
            wp_enqueue_script($this->plugin_name, $pluginurl . 'admin/js/ptb-relation.js', array('jquery'), $this->version, false);
        }
        unset($screen, $screens);
    }

    /**
     * Deactivate plugin if PTB is deactivated
     *
     * @since    1.0.0
     */
    public function deactivate() {
        deactivate_plugins(plugin_basename(__FILE__));
    }

    public function add_plugin_admin_menu($menu) {

        $menu[$this->plugin_name] = array(__('Relations Templates', 'ptb-relation'), __('Relations Templates', 'ptb-relation'), 'manage_options', array($this, 'display_list'));
        return $menu;
    }

    public function display_list() {
        $ptb_options = PTB::get_option();
        $ptb_options->add_template_styles();
        wp_enqueue_script('plupload-all');
        wp_enqueue_script($this->plugin_name . '-plupload', plugin_dir_url(dirname(__FILE__)) . 'admin/js/pluupload.js', array($this->plugin_name, 'plupload-all'), $this->version, TRUE);
        include_once 'partials/list.php';
    }

    public function add_template() {
        check_ajax_referer($this->plugin_name . '-add', '_nonce', true);
        if (current_user_can('manage_options')) {
            include_once 'partials/add.php';
        }
        wp_die();
    }

    public function edit_template() {
        if (isset($_GET['id']) && current_user_can('manage_options')) {
            global $cpt_id;
            $cpt_id = sanitize_key($_GET['id']);
            $ptb_options = PTB::get_option();
            $cpt = $ptb_options->get_post_type_template($cpt_id);
            if ($cpt) {
                include_once 'partials/edit.php';
            }
        }
        wp_die();
    }

    public function save_temlate() {
        check_ajax_referer($this->plugin_name . '-save', '_nonce', true);
        if (isset($_POST['post_type']) && current_user_can('manage_options')) {
            global $cpt_id;
            $post_types = explode('@', esc_attr($_POST['post_type']));
            $post_type1 = $post_types[0];
            $post_type2 = $post_types[1];
            $ptb_options = PTB::get_option();
            $options = PTB_Relation::get_option();
            if (($template = $options->get_relation_template($post_type2, $post_type1)) != false) {
                $cpt_id = $template['id'];
            } else {
                $cpt_id = $ptb_options->get_next_id('ptt', 'ptb_ptt_');
            }
            $ptb_options->option_post_type_templates[$cpt_id]['relation'] = array();
            $ptb_options->option_post_type_templates[$cpt_id]['post_type'] = $post_type2;
            $ptb_options->option_post_type_templates[$cpt_id]['rel_post_type'] = $post_type1;
            $ptb_options->update();
            $this->options = $ptb_options;
            global $add;
            $add = 1;
            include_once 'partials/edit.php';
        }
        wp_die();
    }

    public function ptb_template_save(array $post_data, array $data) {
        if (isset($data['ptb_type']) && $data['ptb_type'] == 'relation') {
            $post_data['relation']['data'] = $data['ptb_relation'];
        }
        return $post_data;
    }

    public function import_temlate() {
        global $post_type;
        $post_type = false;
        if (isset($_GET['post_type']) && current_user_can('manage_options')) {
            $post_type = esc_attr($_GET['post_type']);
            $ptb_options = PTB::get_option();
            $cpt = $ptb_options->get_custom_post_type($post_type);
            if (!$cpt) {
                wp_die();
            }
        }
        include_once 'partials/import.php';
        wp_die();
    }

    public function init() {
        //export template
        if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'export' && current_user_can('manage_options')) {
            $tid = sanitize_key($_GET['id']);
            $ptb_options = PTB::get_option();
            if (isset($ptb_options->option_post_type_templates[$tid]) && isset($ptb_options->option_post_type_templates[$tid]['relation'])) {
                $template = $ptb_options->option_post_type_templates[$tid];
                $rel_options = PTB_Relation::get_option();
                $data = array();
                $data['relation'] = $template['relation'];
                $data['post_type'] = $template['post_type'];
                $data['rel_post_type'] = $template['rel_post_type'];
                $data['metabox'] = $rel_options->get_relation_type_cmb($data['rel_post_type'], $data['post_type']);
                ignore_user_abort(true);
                nocache_headers();
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename=ptb-relation-' . $template['rel_post_type'] . '-' . $data['post_type'] . '-export-' . date('m-d-Y') . '.json');
                header("Expires: 0");
                header("Pragma: no-cache");
                echo wp_json_encode($data);
                exit;
            }
        }
    }

    public function set_import() {
        if (isset($_FILES['import']) && current_user_can('manage_options')) {
            $allow_extensions = array('json', 'zip');
            $file = $_FILES['import'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (in_array($ext, $allow_extensions)) {
                $result = array();
                WP_Filesystem();
                global $wp_filesystem;
                // Retrieve the settings from the file and convert the json object to an array.
                if ($ext == 'json') {
                    $result[] = json_decode($wp_filesystem->get_contents($file['tmp_name']), true);
                    $wp_filesystem->delete($file['tmp_name'], true);
                } else {
                    $path = sys_get_temp_dir() . '/ptb-relation/';
                    if (!$wp_filesystem->is_dir($path)) {
                        $wp_filesystem->mkdir($path, '777');
                    }
                    if (!unzip_file($file['tmp_name'], $path)) {
                        die(wp_json_encode(array('error' => sprintf(__("Couldn't unzip %s", 'ptb-relation'), $file['name']))));
                    } elseif ($dh = opendir($path)) {
                        while (($f = readdir($dh)) !== false) {
                            $ext = pathinfo($f, PATHINFO_EXTENSION);
                            if ($ext == 'json') {
                                $result[] = json_decode($wp_filesystem->get_contents($path . $f), true);
                            }
                            $wp_filesystem->delete($path . $f, true);
                        }
                        closedir($dh);
                        $wp_filesystem->delete($file['tmp_name'], true);
                    }
                }
                if (empty($result)) {
                    die(wp_json_encode(array('error' => __("Data could not be loaded", 'ptb-relation'))));
                } else {
                    $res = array();
                    $ptb_options = PTB::get_option();
                    $rel_option = PTB_Relation::get_option();
                    $post_type = isset($_POST['post_type']) ? esc_attr($_POST['post_type']) : FALSE;
                    if ($post_type && !$ptb_options->get_custom_post_type($post_type)) {
                        die(wp_json_encode(array('error' => sprintf(__("Couldn't find post type with slug %s", 'ptb-relation'), $post_type))));
                    }
                    $relation_templates = array();
                    foreach ($ptb_options->option_post_type_templates as $k => $t) {
                        if (isset($t['relation']) && isset($t['relation']['data']) && isset($t['post_type']) && isset($t['rel_post_type'])) {
                            $relation_templates[$t['post_type'] . '@' . $t['rel_post_type']] = $k;
                        }
                    }
                    $post_types_options = $ptb_options->get_custom_post_types_options();
                    $update = false;
                    foreach ($result as $r) {
                        if (isset($r['relation'])) {
                            if (!isset($r['post_type']) || !isset($r['rel_post_type']) || !isset($post_types_options[$r['rel_post_type']]) || !isset($post_types_options[$r['post_type']])) {
                                continue;
                            }
                            $metabox = $rel_option->get_relation_type_cmb($r['rel_post_type'], $r['post_type']);
                            if (!isset($relation_templates[$r['post_type'] . '@' . $r['rel_post_type']])) {
                                if (!$metabox && (!isset($r['metabox']) || !isset($r['metabox']['post_type']) || !isset($post_types_options[$r['metabox']['post_type']]))) {
                                    continue;
                                }
                                $cpt_id = $ptb_options->get_next_id('ptt', 'ptb_ptt_');
                            } else {
                                $cpt_id = $relation_templates[$r['post_type'] . '@' . $r['rel_post_type']];
                            }
                            if (!$metabox) {
                                $m = $r['metabox'];
                                if (!isset($post_types_options[$r['rel_post_type']]['meta_boxes'][$m['type'] . '_' . $m['id']])) {
                                    $post_types_options[$r['rel_post_type']]['meta_boxes'][$m['type'] . '_' . $m['id']] = $m;
                                } else {
                                    $i = $m['id'] + 1;
                                    while (true) {
                                        if (!isset($post_types_options[$r['rel_post_type']]['meta_boxes'][$m['type'] . '_' . $i])) {
                                            $m['id'] = $i;
                                            $post_types_options[$r['rel_post_type']]['meta_boxes'][$m['type'] . '_' . $i] = $m;
                                            break;
                                        }
                                        $i++;
                                    }
                                }
                            }

                            $ptb_options->option_post_type_templates[$cpt_id]['relation'] = $r['relation'];
                            $ptb_options->option_post_type_templates[$cpt_id]['post_type'] = $r['post_type'];
                            $ptb_options->option_post_type_templates[$cpt_id]['rel_post_type'] = $r['rel_post_type'];
                            $update = true;
                        }
                    }

                    if ($update) {
                        $ptb_options->set_custom_post_types_options($post_types_options);
                        $ptb_options->update();
                        $this->options = $ptb_options;
                    }
                    $success = array('success' => 1);
                    if (!$post_type) {
                        $success['redirect'] = admin_url('admin.php?page=ptb-relation');
                    }
                    die(wp_json_encode($success));
                }
            } else {
                die(wp_json_encode(array('error' => sprintf(__('You can import files only with extensions %s', 'ptb-relation'), implode(',', $allow_extensions)))));
            }
        }
        wp_die();
    }

    public function delete() {
        if (isset($_GET['id']) && current_user_can('manage_options') && check_ajax_referer('ptb_relation_delete', 'nonce')) {
            $ptb_options = PTB::get_option();
            if (isset($ptb_options->option_post_type_templates[$_GET['id']]) && isset($ptb_options->option_post_type_templates[$_GET['id']]['relation'])) {
                unset($ptb_options->option_post_type_templates[$_GET['id']]);
                $ptb_options->update();
                $this->options = $ptb_options;
                $this->get_list();
            }
        }
        wp_die();
    }

    public function get_list() {
        if (!defined('DOING_AJAX')) {
            define('DOING_AJAX', 1);
        }
        if (!isset($GLOBALS['hook_suffix'])) {
            $GLOBALS['hook_suffix'] = 'toplevel_page_ptb-relation';
        }
        include_once 'partials/list.php';
        wp_die();
    }

    public function cpt_update($old_slug, $new_slug) {
        $ptb_options = PTB::get_option();
        $update = false;
        if ($old_slug != $new_slug) {
            foreach ($ptb_options->option_post_type_templates as $id => $p) {
                if (isset($p['relation']) && isset($p['post_type']) && isset($p['rel_post_type'])) {
                    if ($p['post_type'] == $old_slug) {
                        $ptb_options->option_post_type_templates[$id]['post_type'] = $new_slug;
                        $update = true;
                    } elseif ($p['rel_post_type'] == $old_slug) {
                        $ptb_options->option_post_type_templates[$id]['rel_post_type'] = $new_slug;
                        $update = true;
                    }
                }
            }
            $post_types_options = $ptb_options->get_custom_post_types_options();
            foreach ($post_types_options as $slug => &$c) {
                $cmb = $ptb_options->get_cpt_cmb_options($slug);
                if (!empty($cmb)) {
                    foreach ($cmb as $k => $m) {
                        if ($m['type'] == 'relation' && !$m['deleted'] && isset($m['post_type']) && $m['post_type'] == $old_slug) {
                            $c['meta_boxes'][$k]['post_type'] = $new_slug;
                            $update = true;
                        }
                    }
                }
            }
            if ($update) {
                $ptb_options->set_custom_post_types_options($post_types_options);
                $ptb_options->update();
                $this->options = $ptb_options;
            }
        }
        //pre built a template if a template doesn't exist
        $cmb = $ptb_options->get_cpt_cmb_options($new_slug);

        if (!empty($cmb)) {
            $ptb_relation = PTB_Relation::get_option();
            foreach ($cmb as $k => $m) {
                if ($m['type'] == 'relation' && !$m['deleted'] && isset($m['post_type']) && !$ptb_relation->get_relation_template($m['post_type'], $new_slug)) {
                    $cpt_id = $ptb_options->get_next_id('ptt', 'ptb_ptt_');
                    $layout = stripslashes_deep('{"0":{"1-1-0":{"0":{"type":"title","key":"title","title_tag":"1","title_link":"1","text_before":{"en":""},"text_after":{"en":""},"css":""},"1":{"type":"editor","key":"editor","editor":"","css":""}}}');
                    $ptb_options->option_post_type_templates[$cpt_id]['relation']['layout'] = array(
                                    0 =>
                                    array(
                                        '1-1-0' =>
                                        array(
                                            0 =>
                                            array(
                                                'type' => 'title',
                                                'key' => 'title',
                                                'title_tag' => '2',
                                                'title_link' => '1',
                                                'text_before' =>
                                                array(
                                                    'en' => '',
                                                ),
                                                'text_after' =>
                                                array(
                                                    'en' => '',
                                                ),
                                                'css' => '',
                                            ),
                                            1 =>
                                            array(
                                                'type' => 'excerpt',
                                                'key' => 'excerpt',
                                                'excerpt_count' => '',
                                                'can_be_empty' => '1',
                                                'text_before' =>
                                                array(
                                                    'en' => '',
                                                ),
                                                'text_after' =>
                                                array(
                                                    'en' => '',
                                                ),
                                                'css' => '',
                                            ),
                                        ),
                                    ),
                                );
                    $ptb_options->option_post_type_templates[$cpt_id]['relation']['data']['title'] = sprintf(__('Pre-built template for %s', 'ptb-relation'), PTB_Utils::get_label($m['name']));
                    $ptb_options->option_post_type_templates[$cpt_id]['post_type'] = $m['post_type'];
                    $ptb_options->option_post_type_templates[$cpt_id]['rel_post_type'] = $new_slug;
                    $ptb_options->update();
                    $this->options = $ptb_options;
                }
            }
        }
    }

    public function cpt_remove($post_type) {
        $update = false;
        $ptb_options = PTB::get_option();
        foreach ($ptb_options->option_post_type_templates as $id => $p) {
            if (isset($p['relation']) && isset($p['post_type']) && isset($p['rel_post_type']) && ($p['post_type'] == $post_type || $p['rel_post_type'] == $post_type)) {
                $update = true;
                unset($ptb_options->option_post_type_templates[$id]);
            }
        }
        if ($update) {
            $ptb_options->update();
            $this->options = $ptb_options;
        }
    }

    public function screens(array $screens, $screen) {
        $id = __('Post Type Builder', 'ptb');
        $id = sanitize_title($id);
        $screens[] = 'toplevel_page_ptb-cpt';
        $screens[] = $id . '_page_ptb-relation';
        return $screens;
    }

    public function get_related_posts() {

        if (isset($_GET['post_type']) && $_GET['post_type'] && isset($_GET['post_id']) && isset($_GET['nonce'])) {
            check_ajax_referer('ptb_relation_' . $_GET['post_id'], 'nonce');
            if ($this->options->has_custom_post_type($_GET['post_type'])) {
                global $related_posts, $many;
                $related_posts = new WP_Query(
                        array(
                    'post_type' => $_GET['post_type'],
                    'post_status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'nopaging' => 1
                        )
                );
                $many = isset($_GET['many']) && $_GET['many'];
                include_once 'partials/posts.php';
            }
        }
        wp_die();
    }

    public function get_term() {
   
        if (!empty($_REQUEST['post_type']) && !empty($_REQUEST['term'])) {
            $data = array();
            add_filter('posts_search', array($this, 'search_by_title'), 100, 2);
            $posts = get_posts(
                    array(
                        'post_type' => sanitize_key($_REQUEST['post_type']),
                        'orderby' => 'title',
                        'order' => 'ASC',
                        's' => sanitize_text_field($_REQUEST['term']),
                        'nopaging' => 1,
                        'suppress_filters' => false
                    )
            );
            foreach ($posts as $p) {
                $data[] = array('id' => $p->ID, 'label' => $p->post_title, 'title' => $p->post_title);
            }
            echo wp_json_encode($data);
        }
        wp_die();
    }

    public function search_by_title($search, $wp_query) {

       if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
            global $wpdb;

            $q = $wp_query->query_vars;
            $n = ! empty( $q['exact'] ) ? '' : '%';

            $search = array();

            foreach ( ( array ) $q['search_terms'] as $term ){
                $search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
            }

            if ( ! is_user_logged_in() ){
                $search[] = "$wpdb->posts.post_password = ''";
            }

            $search = ' AND ' . implode( ' AND ', $search );
        }

        return $search;
    }
    
    public function ptb_submission_themplate($id, array $args, array $module = array(), array $post_support, array $languages = array()) {
        $default = empty($data);
        $mode = array('autocomplete'=>__('autocomplete', 'ptb-relation'),'select' => __('Select', 'ptb-relation'));
        if(!empty($args['many'])){
            $mode['checkbox']= __('Checkbox', 'ptb-relation');
        }
        else{
            $mode['radio']= __('Radio', 'ptb-relation');
        }
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label><?php _e('Show as', 'ptb-relation') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <?php foreach ($mode as $k => $m): ?>
                    <input type="radio" id="ptb_relation_mode_<?php echo $id ?>_<?php echo $k ?>"
                           name="[<?php echo $id ?>][mode]" value="<?php echo $k ?>"
                           <?php if (($default && $k == 'autocomplete') || ( isset($data['mode']) && $data['mode'] == $k )): ?>checked="checked"<?php endif; ?>/>
                    <label for="ptb_relation_mode_<?php echo $id ?>_<?php echo $k ?>"><?php echo $m ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function ptb_submission_validate(array $post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
        $this->submission_values[$module['key']] = array();
        if (!empty($post_data[$module['key']])) {
            $multply = !empty($args['many']);
            $rel_post_type = $args['post_type'];
            $value = !is_array($post_data[$module['key']])?array_filter(explode(',',$post_data[$module['key']])):$post_data[$module['key']];
            if(!$multply){
                $value = current($value);
            } 
           
            $posts = get_posts(array(
                'post_type'=>$rel_post_type,
                'include'=>$value,
                'nopaging'=>1
            ));
            $value = array();
            if(!empty($posts)){
                foreach($posts as $p){
                    $value[] = $p->ID;
                }
                $this->submission_values[$module['key']] = implode(', ',$value);
            }
        }
        if (empty($this->submission_values[$module['key']]) && isset($module['required'])) {
            return sprintf( __( '%s is required', 'ptb_extra' ), PTB_Utils::get_label( $args['name'] ) );
        }
        return $post_data;
    }

    public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
        return $this->submission_values[$key];
    }

}
