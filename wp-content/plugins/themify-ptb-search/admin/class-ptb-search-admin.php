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
class PTB_Search_Admin {

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
    private $options;

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
        add_action('admin_init', array($this, 'init'));
        add_filter('ptb_admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('ptb_cpt_update', array($this, 'cpt_update'), 15, 2);
        add_action('ptb_cpt_remove', array($this, 'cpt_remove'), 15, 1);
        add_action('ptb_deactivated', array($this, 'deactivate'));
        add_action('wp_ajax_ptb_search_add', array($this, 'add_template'));
        add_action('wp_ajax_ptb_search_edit', array($this, 'edit_template'));
        add_action('wp_ajax_ptb_search_add_template', array($this, 'save_temlate'));
        add_action('wp_ajax_ptb_search_get_import', array($this, 'import_temlate'));
        add_action('wp_ajax_ptb_search_import', array($this, 'set_import'));
        add_action('wp_ajax_ptb_search_delete', array($this, 'delete'));
        add_action('wp_ajax_ptb_search_list', array($this, 'get_list'));
        add_filter('ptb_template_modules', array($this, 'ptb_search_modules'), 10, 3);
        add_filter('ptb_screens', array($this, 'screens'), 10, 2);
        add_action('save_post', array($this, 'clear_cache'), 10, 3);
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
        $translation_ = array(
            'module' => __('You have already added module', 'ptb-search')
        );
        if (in_array($screen->id, $screens)) {
            wp_enqueue_style($this->plugin_name, $pluginurl . 'admin/css/ptb-search.css', array(), $this->version, 'all');
            wp_enqueue_script($this->plugin_name, $pluginurl . 'admin/js/ptb-search.js', array('jquery'), $this->version, false);
        } elseif ($screen->id == 'post-type-builder_page_ptb-ptt') {
            wp_enqueue_script($this->plugin_name, $pluginurl . 'admin/js/ptb-search.js', array('jquery'), $this->version, false);
        }
        wp_localize_script($this->plugin_name, 'ptb_search', $translation_);
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

        $menu[$this->plugin_name] = array(__('Search Templates', 'ptb-search'), __('Search Templates', 'ptb-search'), 'manage_options', array($this, 'display_list'));
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
        if (isset($_POST['post_type']) && isset($_POST['title']) && current_user_can('manage_options')) {
            global $cpt_id, $post_type;
            $post_type = sanitize_key($_POST['post_type']);
            $title = sanitize_text_field($_POST['title']);
            $cpt_id = str_replace('-', '_', sanitize_title($title));
            $ptb_options = PTB::get_option();
            if (isset($ptb_options->option_post_type_templates[$cpt_id])) {
                $i = 1;
                while (true) {
                    if (!isset($ptb_options->option_post_type_templates[$cpt_id . '_' . $i])) {
                        $cpt_id = $cpt_id . '_' . $i;
                        break;
                    }
                    $i++;
                }
            }
            $ptb_options->option_post_type_templates[$cpt_id]['search'] = array();
            $ptb_options->option_post_type_templates[$cpt_id]['post_type'] = $post_type;
            $ptb_options->option_post_type_templates[$cpt_id]['title'] = $title;
            $ptb_options->update();
            $this->options = $ptb_options;
            include_once 'partials/edit.php';
        }
        wp_die();
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
                if ($ext === 'json') {
                    $data = json_decode($wp_filesystem->get_contents($file['tmp_name']), true);
                    $key = key($data);
                    $result[$key] = $data[$key];
                    $wp_filesystem->delete($file['tmp_name'], true);
                } else {
                    $path = sys_get_temp_dir() . '/ptb-search/';
                    if (!$wp_filesystem->is_dir($path)) {
                        $wp_filesystem->mkdir($path, '777');
                    }
                    if (!unzip_file($file['tmp_name'], $path)) {
                        die(wp_json_encode(array('error' => sprintf(__("Couldn't unzip %s", 'ptb-search'), $file['name']))));
                    } elseif ($dh = opendir($path)) {
                        while (($f = readdir($dh)) !== false) {
                            $ext = pathinfo($f, PATHINFO_EXTENSION);
                            if ($ext === 'json') {
                                $data = json_decode($wp_filesystem->get_contents($path . $f), true);
                                $key = key($data);
                                $result[$key] = $data[$key];
                            }
                            $wp_filesystem->delete($path . $f, true);
                        }
                        closedir($dh);
                        $wp_filesystem->delete($file['tmp_name'], true);
                    }
                }
                if (empty($result)) {
                    die(wp_json_encode(array('error' => __("Data could not be loaded", 'ptb-search'))));
                } else {
                    $ptb_options = PTB::get_option();
                    foreach ($result as $key=>$r) {
                        if (isset($r['search'])) {
                            if (!isset($r['post_type']) || !$ptb_options->get_custom_post_type($r['post_type'])){
                                continue;
                            }
                            $ptb_options->option_post_type_templates[$key] = $r;
                            $ptb_options->update();
                        }
                    }
                    $success = array('success' => 1,'redirect'=>admin_url('admin.php?page=ptb-search'));
                    die(wp_json_encode($success));
                }
            } else {
                die(wp_json_encode(array('error' => sprintf(__('You can import files only with extensions %s', 'ptb-search'), implode(',', $allow_extensions)))));
            }
        }
        wp_die();
    }

    public function init() {
        //export template
        if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'export' && current_user_can('manage_options')) {
            $tid = utf8_uri_encode($_GET['id']);
            $ptb_options = PTB::get_option();
            if (isset($ptb_options->option_post_type_templates[$tid]) && isset($ptb_options->option_post_type_templates[$tid]['search'])) {
                $template = $ptb_options->option_post_type_templates[$tid];
                $data = array();
                $data[$tid] = $ptb_options->option_post_type_templates[$tid];
                ignore_user_abort(true);
                nocache_headers();
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename=ptb-search-' . $template['title'] . '-' . $template['post_type'] . '-export-' . date('m-d-Y') . '.json');
                header("Expires: 0");
                header("Pragma: no-cache");
                echo wp_json_encode($data);
                exit;
            }
        }
    }


    public function delete() {
        if (isset($_GET['id']) && current_user_can('manage_options') && check_ajax_referer('ptb_search_delete', 'nonce')) {
            $ptb_options = PTB::get_option();
            $tid = utf8_uri_encode($_GET['id']);
            if (isset($ptb_options->option_post_type_templates[$tid]) && isset($ptb_options->option_post_type_templates[$tid]['search'])) {
                unset($ptb_options->option_post_type_templates[$tid]);
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
            $GLOBALS['hook_suffix'] = 'toplevel_page_ptb-search';
        }
        include_once 'partials/list.php';
        wp_die();
    }

    public function cpt_update($old_slug, $new_slug) {
        if ($old_slug != $new_slug) {
            $ptb_options = PTB::get_option();
            $update = false;
            foreach ($ptb_options->option_post_type_templates as $id => $p) {
                if (isset($p['searchh']) && isset($p['post_type'])) {
                    if ($p['post_type'] == $old_slug) {
                        $ptb_options->option_post_type_templates[$id]['post_type'] = $new_slug;
                        $update = true;
                    }
                }
            }
            if ($update) {
                $ptb_options->update();
                $this->options = $ptb_options;
            }
        }
    }

    public function cpt_remove($post_type) {
        $update = false;
        $ptb_options = PTB::get_option();
        foreach ($ptb_options->option_post_type_templates as $id => $p) {
            if (isset($p['search']) && isset($p['post_type']) && $p['post_type'] == $post_type) {
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
        $screens[] = $id . '_page_ptb-search';
        return $screens;
    }

    public function ptb_search_modules(array $cmp_options, $type, $post_type) {
        if ($type == 'search') {
            unset($cmp_options['permalink']);
            unset($cmp_options['editor']);
            unset($cmp_options['excerpt']);
            unset($cmp_options['thumbnail']);
            unset($cmp_options['comments']);
            unset($cmp_options['comment_count']);
            $cmp_options['date']['name'] =PTB_Search_Options::get_name('date');
            foreach ($cmp_options as $id => $v) {
                if (in_array($v['type'], array('gallery', 'slider', 'file', 'icon', 'audio', 'video', 'image', 'relationship','textarea'))) {
                    unset($cmp_options[$id]);
                }
            }
            $cmp_options['button'] = array('type' => 'button', 'name' => PTB_Search_Options::get_name('button'));
            $cmp_options['has'] = array('type' => 'has', 'name' => PTB_Search_Options::get_name('has'));
        }
        return $cmp_options;
    }

    public function clear_cache($post_id, $post, $update) {
        if (wp_is_post_revision($post) || in_array($post->post_type, array('post', 'page', 'attachment', 'nav_menu_item', 'revision')) || !$this->options->has_custom_post_type($post->post_type)) {
            return;
        }
        $is_update = false;
        $cache = PTB_Search_Options::get_cache();
        $post_type = $post->post_type;
        if (isset($cache['default']) && isset($cache['default'][$post_type])) {
            unset($cache['default'][$post_type]);
            $is_update = true;
        }
        if (isset($cache['response']) && isset($cache['response'][$post_type])) {
            unset($cache['response'][$post_type]);
            $is_update = true;
        }
        if ($is_update) {
            PTB_Search_Options::set_cache($cache);
        }
    }

}