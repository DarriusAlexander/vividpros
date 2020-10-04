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
class PTB_Submission_Admin {

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
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('before_template_row', array($this, 'add_row_required'), 10, 4);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('ptb_cpt_update', array($this, 'cpt_update'), 10, 2);
        add_action('ptb_cpt_remove', array($this, 'cpt_remove'), 10, 1);
        add_action('ptb_deactivated', array($this, 'deactivate'));
        add_action('wp_ajax_ptb_submission_add', array($this, 'add_template'));
        add_action('wp_ajax_ptb_submission_edit', array($this, 'edit_template'));
        add_action('wp_ajax_save_ajax', array($this, 'save_temlate'));
        add_action('wp_ajax_ptb_submission_get_import', array($this, 'import_temlate'));
        add_action('wp_ajax_ptb_submission_import', array($this, 'set_import'));
        add_action('wp_ajax_ptb_submission_delete', array($this, 'delete'));
        add_action('wp_ajax_ptb_submission_list', array($this, 'get_list'));
        add_action('wp_ajax_ptb_submission_posts_filter', array($this, 'posts_lists'));
        add_action('wp_ajax_ptb_submission_post_action', array($this, 'post_action'));
        add_filter('ptb_shorcode_template_menu', array($this, 'shortcode_menu'));
        add_filter('ptb_ajax_shortcode_result', array($this, 'ptb_shortcode_result'), 10, 2);
        add_filter('ptb_template_modules', array($this, 'ptb_submission_modules'), 10, 3);
        add_filter('ptb_template_save', array($this, 'ptb_template_save'), 10, 2);
        add_filter('ptb_screens', array($this, 'screens'), 10, 2);
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
        wp_enqueue_script($this->plugin_name-'shortcode', $pluginurl . 'admin/js/shortcode.js', array('jquery'), $this->version, false);
        if (in_array($screen->id, $screens)) {
            $translation_ = array(
                'module' => __('You have already added module', 'ptb-submission'),
                'lng' => PTB_Utils::get_current_language_code()
            );
            wp_enqueue_style('ptb-choosen', $pluginurl . 'admin/css/chosen.css', array(), '1.4.2', 'all');
            wp_enqueue_script('ptb-choosen', $pluginurl . 'admin/js/chosen.jquery.min.js', array('ptb'), '1.4.2', false);
            wp_enqueue_style($this->plugin_name, $pluginurl . 'admin/css/ptb-submission.css', array(), $this->version, 'all');
            wp_register_script($this->plugin_name, $pluginurl . 'admin/js/ptb-submission.js', array('ptb-choosen'), $this->version, false);
            wp_localize_script($this->plugin_name, 'ptb_submission', $translation_);
            wp_enqueue_script($this->plugin_name);
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

    public function ptb_shortcode_result(array $result, $post_type) {
        if ($post_type == 'frontend') {
            $options = PTB::get_option();
            $themplates = $options->option_post_type_templates;
            if (!empty($themplates)) {
                $values = array();

                foreach ($themplates as $t) {
                    if (isset($t['frontend']) && isset($t['frontend']['data'])) {
                        $values[] = array('text' => PTB_Utils::get_label($t['frontend']['data']['title']), 'value' => $t['post_type']);
                    }
                }
                if (!empty($values)) {
                    $result['data']['frontend_post_type'] = array(
                        'label' => __('Post type', 'ptb-submission'),
                        'values' => $values,
                        'type' => 'listbox'
                    );
                }
            }
            $result['title'] = __('PTB Frontend Submission', 'ptb-submission');
        }
        return $result;
    }

    public function shortcode_menu(array $menu) {
        $name = __('Frontend Submission', 'ptb-submission');
        $menu[] = "{'type':'frontend','name':'{$name}','classes':'ptb-submission'}";
        return $menu;
    }

    public function add_row_required($id, array $module, $type, array $language) {
        if ($type != 'frontend' || in_array($id, array('title', 'user_email', 'user_name', 'user_password', 'custom_text', 'custom_image'))) {
            return;
        }
        ?>
        <div class="ptb_back_active_module_row">	
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[required]"><?php _e('Required', 'ptb-submission') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <label>
                    <input id="ptb_<?php echo $id ?>[required]" type="checkbox" name="[<?php echo $id ?>][required]" <?php if (isset($module['required']) && $module['required']): ?>checked="checked"<?php endif; ?> />
                </label>
            </div>
        </div>
        <?php
    }

    public function ptb_submission_modules(array $cmp_options, $type, $post_type) {
        if ($type == 'frontend') {
            unset($cmp_options['comments']);
            unset($cmp_options['date']);
            unset($cmp_options['permalink']);
            unset($cmp_options['author']);
            unset($cmp_options['comment_count']);
            $options = get_option($this->plugin_name . '-settings');
            if (!isset($options) || (isset($options['account']) && $options['account'])) {
                $cmp_options['user_email'] = array('type' => 'user_email', 'name' => PTB_Submissiion_Options::get_name('user_email'));
                $cmp_options['user_name'] = array('type' => 'user_name', 'name' => PTB_Submissiion_Options::get_name('user_name'));
                $cmp_options['user_password'] = array('type' => 'user_password', 'name' => PTB_Submissiion_Options::get_name('user_password'));
            }
        }
        return $cmp_options;
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
                __('PTB Submission', 'ptb'), __('PTB Submission', 'ptb'), 'manage_options', $this->plugin_name, array($this, 'display_list'), 'dashicons-welcome-write-blog', '59.896427'
        );
        add_submenu_page(
                $this->plugin_name, __('Submission Forms', 'ptb-submission'), __('Submission Forms', 'ptb-submission'), 'manage_options', $this->plugin_name
        );
        add_submenu_page(
                $this->plugin_name, __('Submission Posts', 'ptb-submission'), __('Submission Posts', 'ptb-submission'), 'delete_others_posts', $this->plugin_name . '-posts', array($this, 'posts_lists')
        );
        add_submenu_page(
                $this->plugin_name, __('PTB Authors', 'ptb-submission'), __('PTB Authors', 'ptb-submission'), 'delete_users', $this->plugin_name . '-users', array($this, 'users_lists')
        );
        add_submenu_page(
                $this->plugin_name, __('Settings', 'ptb-submission'), __('Settings', 'ptb-submission'), 'manage_options', $this->plugin_name . '-settings', array($this, 'settings')
        );
    }

    public function settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ptb-submission'));
        }
        $sandbox = function_exists('fsockopen');
        $message = $type = '';
        if (!empty($_POST) && isset($_POST['ptb_submission_nonce']) && wp_verify_nonce($_POST['ptb_submission_nonce'], 'ptb_submission_save_settings')) {
            if (isset($_POST['paypal-email']) && $_POST['paypal-email'] && !is_email($_POST['paypal-email'])) {
                $message = __("PayPal Email isn't valid", 'ptb-submission');
                $type = 'error';
            }
            if (!$type) {
                $data = array(
                    'ptb_paypal_sandbox' => 'paypal-sandbox',
                    'ptb_paypal_email' => 'paypal-email',
                    'ptb_paypal_currency' => 'currency',
                    'ptb_paypal_currency_position' => 'currency_position',
                    'ptb_submission_account' => 'account'
                );
                $settings = array();
                if (!$sandbox) {
                    unset($data['ptb_paypal_sandbox']);
                }
                foreach ($data as $key => $key2) {
                    if (isset($_POST[$key])) {
                        $settings[$key2] = $key != 'ptb_paypal_email' ? sanitize_text_field($_POST[$key]) : sanitize_email($_POST[$key]);
                    }
                }
                $type = 'updated';
                update_option($this->plugin_name . '-settings', $settings);
                $message = __('Your settings have been saved', 'ptb-submission');
            }
        }
        $options = get_option($this->plugin_name . '-settings');
        $submission_options = PTB_Submission::get_option();
        $currencies = $submission_options->get_currencies();
        $currency_positions = $submission_options->get_currency_position();
        ?>  
        <div class="wrap ptb-submission-setting">
            <h2 class="ptb-submission-tabs nav-tab-wrapper woo-nav-tab-wrapper">
                <a class="nav-tab" href="#ptb-submission-general"><?php _e('General', 'ptb-submission') ?></a>
                <a class="nav-tab" href="#ptb-submission-checkout"><?php _e('Payment', 'ptb-submission') ?></a>
            </h2>
            <form action="" method="post">
                <?php
                if ($message) {
                    add_settings_error(
                            'ptb-submission-setting', '', $message, $type
                    );
                    settings_errors('ptb-submission-setting');
                }
                ?>
                <?php wp_nonce_field('ptb_submission_save_settings', 'ptb_submission_nonce'); ?>
                <ul class="ptb-submission-tabs-">
                    <li id="ptb-submission-general">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="ptb_submission_account"><?php _e('Submission Account', 'ptb-submission') ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" value="1" <?php if (!$options || isset($options['account'])): ?>checked="checked"<?php endif; ?> name="ptb_submission_account" id="ptb_submission_account" />
                                </td>
                                <td class="ptb-submission-settings-desc">
                                    <?php _e('Enable submission account (this will allow users to managed/edit their submissions)', 'ptb-submission') ?>
                                    <br/>
                                    <?php _e('Insert <strong>[ptb_submission_account]</strong> shortcode in the "My Submissions" page.', 'ptb-submission') ?>
                                </td>
                            </tr>
                        </table>
                    </li>
                    <li id="ptb-submission-checkout">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="ptb_paypal_sandbox"><?php _e('PayPal Sandbox', 'ptb-submission') ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" value="1" <?php if (!$sandbox): ?>disabled="disabled"<?php endif; ?> <?php if ($sandbox && isset($options['paypal-sandbox'])): ?>checked="checked"<?php endif; ?> name="ptb_paypal_sandbox" id="ptb_paypal_sandbox" />
                                </td>
                                <td class="ptb-submission-settings-desc">
                                    <?php if (!$sandbox): ?>
                                        <div class="ptb-submission-warrning"><?php _e("WARNING! Your server doesn't support fsockopen/curl.You need to contact to your hosting.", 'ptb-submission') ?></div>
                                    <?php else: ?>
                                        <?php _e('PayPal sandbox can be used to test payments. Sign up for a developer account <a target="_blank" href="//developer.paypal.com/">here.</a>', 'ptb-submission') ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="ptb_paypal_email"><?php _e('PayPal Email', 'ptb-submission') ?></label>
                                </th>
                                <td>
                                    <input type="email" value="<?php echo isset($options['paypal-email']) ? esc_attr($options['paypal-email']) : get_option('admin_email') ?>" name="ptb_paypal_email" id="ptb_paypal_email" />
                                </td>
                                <td class="ptb-submission-settings-desc"><?php _e("Enter your Paypal account's email address", 'ptb-submission') ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="ptb_paypal_currency"><?php _e('Currecy', 'ptb-submission') ?></label>
                                </th>
                                <td>
                                    <select id="ptb_paypal_currency" name="ptb_paypal_currency" class="ptb-select">
                                        <?php foreach ($currencies as $c => $n): ?>
                                            <option <?php if (isset($options['currency']) && $options['currency'] == $c): ?>selected="selected"<?php endif; ?> value="<?php echo $c ?>"><?php echo $n . '(' . $submission_options->get_currency_symbol($c) . ')'; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="ptb_paypal_currency_position"><?php _e('Currency Position', 'ptb-submission') ?></label>
                                </th>
                                <td>
                                    <select id="ptb_paypal_currency_position" name="ptb_paypal_currency_position" class="ptb-select">
                                        <?php foreach ($currency_positions as $c => $n): ?>
                                            <option <?php if (isset($options['currency_position']) && $options['currency_position'] == $c): ?>selected="selected"<?php endif; ?> value="<?php echo $c ?>"><?php echo $n; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </li>
                </ul>
                <?php submit_button(__('Save changes', 'ptb-submission')); ?>
            </form>
        </div>
        <?php
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
        if (isset($_GET['post_type']) && current_user_can('manage_options')) {
            global $post_type;
            $post_type = sanitize_key($_GET['post_type']);
            $ptb_options = PTB::get_option();
            $cpt = $ptb_options->get_custom_post_type($post_type);
            if ($cpt) {
                $submission_options = PTB_Submission::get_option();
                $submission_form = $submission_options->get_submission_template($post_type);
                if ($submission_form) {
                    global $cpt_id;
                    $cpt_id = $submission_form['id'];
                    include_once 'partials/edit.php';
                }
            }
        }
        wp_die();
    }

    public function save_temlate() {
        check_ajax_referer($this->plugin_name . '-save', '_nonce', true);
        if (isset($_POST['post_type']) && current_user_can('manage_options')) {
            global $cpt_id, $post_type;
            $post_type = esc_attr($_POST['post_type']);
            $ptb_options = PTB::get_option();
            $cpt = $ptb_options->get_custom_post_type($post_type);
            if ($cpt) {
                $options = PTB_Submission::get_option();
                if (($template = $options->get_submission_template($post_type)) != false) {
                    $cpt_id = $template['id'];
                } else {
                    $cpt_id = $ptb_options->get_next_id('ptt', 'ptb_ptt_');
                }
                $ptb_options->option_post_type_templates[$cpt_id]['frontend'] = array();
                $ptb_options->option_post_type_templates[$cpt_id]['post_type'] = $post_type;
                $ptb_options->update();
                $this->options = PTB::get_option();
                global $add;
                $add = 1;
                include_once 'partials/edit.php';
            }
        }
        wp_die();
    }

    public function ptb_template_save(array $post_data, array $data) {
        if (isset($data['ptb_type']) && $data['ptb_type'] == 'frontend' && !empty($data['ptb_submission'])) {
            $post_data['frontend']['data'] = $data['ptb_submission'];
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
        $this->export_template();
    }

    public function export_template() {
        if (isset($_GET['post_type']) && isset($_GET['action']) && $_GET['action'] == 'export' && current_user_can('manage_options')) {
            $post_type = esc_attr($_GET['post_type']);
            $ptb_options = PTB::get_option();
            $options = PTB_Submission::get_option();
            $cpt = $ptb_options->get_custom_post_type($post_type);
            if ($cpt && ($template = $options->get_submission_template($post_type)) != false) {
                $data = array();
                $data['frontend'] = $template['frontend'];
                $data['post_type'] = $post_type;
                ignore_user_abort(true);
                nocache_headers();
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename=ptb-submission-' . $post_type . '-export-' . date('m-d-Y') . '.json');
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
                    $path = sys_get_temp_dir() . '/ptb-submission/';
                    if (!$wp_filesystem->is_dir($path)) {
                        $wp_filesystem->mkdir($path, '777');
                    }
                    if (!unzip_file($file['tmp_name'], $path)) {
                        die(wp_json_encode(array('error' => sprintf(__("Couldn't unzip %s", 'ptb-submission'), $file['name']))));
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
                    die(wp_json_encode(array('error' => __("Data could not be loaded", 'ptb-submission'))));
                } else {
                    $res = array();
                    $ptb_options = PTB::get_option();
                    $post_type = isset($_POST['post_type']) ? esc_attr($_POST['post_type']) : FALSE;
                    if ($post_type && !$ptb_options->get_custom_post_type($post_type)) {
                        die(wp_json_encode(array('error' => sprintf(__("Couldn't find post type with slug %s", 'ptb-submission'), $post_type))));
                    }
                    $frontend_templates = array();
                    foreach ($ptb_options->option_post_type_templates as $k => $t) {
                        $frontend_templates[$t['post_type']] = isset($t['frontend']) ? $k : false;
                    }
                    foreach ($result as $r) {
                        if (isset($r['frontend'])) {
                            if (!$post_type && (!isset($r['post_type']) || !$ptb_options->get_custom_post_type($r['post_type']))) {
                                continue;
                            }
                            if ($post_type) {
                                $cpt_id = $frontend_templates[$post_type];
                            } else {
                                if (!isset($frontend_templates[$r['post_type']]) || !$frontend_templates[$r['post_type']]) {
                                    $cpt_id = $ptb_options->get_next_id('ptt', 'ptb_ptt_');
                                    $ptb_options->option_post_type_templates[$cpt_id]['post_type'] = $r['post_type'];
                                } else {
                                    $cpt_id = $frontend_templates[$r['post_type']];
                                }
                            }
                            $ptb_options->option_post_type_templates[$cpt_id]['frontend'] = $r['frontend'];
                            $ptb_options->update();
                        }
                    }
                    $success = array('success' => 1);
                    if (!$post_type) {
                        $success['redirect'] = admin_url('admin.php?page=ptb-submission');
                    }
                    die(wp_json_encode($success));
                }
            } else {
                die(wp_json_encode(array('error' => sprintf(__('You can import files only with extensions %s', 'ptb-submission'), implode(',', $allow_extensions)))));
            }
        }
        wp_die();
    }

    public function delete() {
        if (isset($_GET['post_type']) && current_user_can('manage_options') && check_ajax_referer('ptb_submission_delete', 'nonce')) {
            $options = PTB_Submission::get_option();
            $post_type = sanitize_key($_GET['post_type']);
            if (($t = $options->get_submission_template($post_type)) != false) {
                $ptb_options = PTB::get_option();
                unset($ptb_options->option_post_type_templates[$t['id']]);
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
            $GLOBALS['hook_suffix'] = 'toplevel_page_ptb-submission';
        }
        include_once 'partials/list.php';
        wp_die();
    }

    public function cpt_update($old_slug, $new_slug) {
        if($new_slug!=$new_slug){
            $options = PTB_Submission::get_option();
            if (($t = $options->get_submission_template($old_slug)) != false) {
                $ptb_options = PTB::get_option();
                $ptb_options->option_post_type_templates[$t['id']]['post_type'] = $new_slug;
                $ptb_options->update();
                $this->options = $ptb_options;
            }
        }
    }

    public function cpt_remove($post_type) {
        $options = PTB_Submission::get_option();
        if (($t = $options->get_submission_template($post_type)) != false) {
            $ptb_options = PTB::get_option();
            unset($ptb_options->option_post_type_templates[$t['id']]);
            $ptb_options->update();
            $this->options = $ptb_options;
        }
    }

    public function posts_lists() {
        if (!defined('DOING_AJAX')) {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style($this->plugin_name . '-datepicker', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        } else {
            if (isset($_POST['submission'])) {
                check_ajax_referer('ptb_submission_posts', '_wpnonce', 1);
            }
            if (!isset($GLOBALS['hook_suffix'])) {
                $GLOBALS['hook_suffix'] = 'toplevel_page_ptb-submission-posts';
            }
        }
        include_once 'partials/posts.php';
    }

    public function post_action() {
        if (defined('DOING_AJAX')) {
            $action = false;
            if (isset($_GET['id']) && is_numeric($_GET['id']) && check_ajax_referer('ptb_submission_post_delete', 'nonce')) {
                $action = isset($_GET['trash']) ? 'trash' : (isset($_GET['approve']) ? 'approve' : 'delete');
                if ($action == 'approve' && !current_user_can('edit_others_posts') || ($action != 'approve' && !current_user_can('delete_others_posts'))) {
                    $action = false;
                }
                $post_data = array(intval($_GET['id']));
            } elseif (isset($_POST['method']) && is_array($_POST['posts']) && !empty($_POST['posts']) && check_ajax_referer('bulk-ptb_submission_posts')) {
                $action = in_array($_POST['method'], array('delete', 'trash', 'approve')) ? $_POST['method'] : false;
                if ($action == 'approve' && !current_user_can('edit_others_posts') || ($action != 'approve' && !current_user_can('delete_others_posts'))) {
                    $action = false;
                }
                $post_data = $_POST['posts'];
            }
            if ($action) {
                foreach ($post_data as $post_id) {
                    if ($action == 'delete') {
                        wp_delete_post($post_id);
                    } elseif ($action == 'trash') {
                        wp_trash_post($post_id);
                    } else {
                        wp_publish_post($post_id);
                    }
                }
                $this->posts_lists();
            }
        }
    }

    public function users_lists() {
        if (current_user_can('delete_users')) {
            include_once 'partials/users.php';
        }
    }

    public function screens(array $screens, $screen) {
        $id = __('PTB Submission','ptb-submission');//multilanguage screen id
        $id = sanitize_title($id);
        $screens[] = 'toplevel_page_ptb-submission';
        $screens[] = $id.'_page_ptb-submission-posts';
        $screens[] = $id.'_page_ptb-submission-settings';
        return $screens;
    }

    public function ptb_submission_post_type_slug($slug,$title) {
        $options = PTB_Submission::get_option();
        if (($t = $options->get_submission_template(get_post_type())) != false) {
            $slug = sanitize_title($title);
        }
        return $slug;
    }

}
