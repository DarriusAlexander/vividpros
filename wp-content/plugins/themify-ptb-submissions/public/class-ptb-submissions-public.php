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
class PTB_Submission_Public {

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
    public static $files = array();
    private $post = false;
    private static $ptb_author = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('init', array($this, 'register_session'));
        if (!is_admin()) {
            add_filter('the_title', array($this, 'page_title'), 10, 2);
            add_filter('wp_title', array($this, 'wp_title'), 10, 2);
            add_filter('author_link', array($this, 'author_url'), 10, 3);
            add_filter( 'widget_text', array($this,'get_ptb_submission_shortcode'),10,1);
            add_filter('script_loader_tag', array($this, 'ptb_ignore_rocket_loader'));
            add_shortcode('ptb_submission', array($this, 'shortcode'));
            add_shortcode('ptb_submission_account', array($this, 'account'));
        }
        add_action('wp_ajax_ptb_submission_tag_terms', array($this, 'get_tag_terms'));
        add_action('wp_ajax_nopriv_ptb_submission_tag_terms', array($this, 'get_tag_terms'));
        
        add_action('wp_ajax_ptb_submission_terms', array($this, 'get_terms'));
        add_action('wp_ajax_nopriv_ptb_submission_terms', array($this, 'get_terms'));
        add_action('wp_ajax_ptb_account_filter', array($this, 'posts_filter'));
        add_action('wp_ajax_ptb_account_remove_post', array($this, 'remove_post'));
        add_action('wp_ajax_ptb_submission_save', array($this, 'save_post'));
        add_action('wp_ajax_nopriv_ptb_submission_save', array($this, 'save_post'));
        add_action('wp_ajax_ptb_submission_profile', array($this, 'edit_profile'));
        add_action('wp_ajax_nopriv_ptb_submission_payment', array($this, 'payment_form'));
        add_action('wp_ajax_ptb_submission_payment', array($this, 'payment_form'));
        add_action('wp_ajax_nopriv_ptb_submission_payment_result', array($this, 'payment_result'));
        add_action('wp_ajax_ptb_submission_payment_result', array($this, 'payment_result'));
        add_action('wp_ajax_nopriv_ptb_submission_add_tax', array($this, 'add_tax'));
        add_action('wp_ajax_ptb_submission_add_tax', array($this, 'add_tax'));
        add_action('wp_ajax_nopriv_ptb_submission_add_temp_tax', array($this, 'add_temp_tax'));
        add_action('wp_ajax_ptb_submission_add_temp_tax', array($this, 'add_temp_tax'));
    }

    /**
     * Add attribute so Allground scripts are ignored by Rocket Loader.
     * @param string $script_tag
     * @return string
     */
    public function ptb_ignore_rocket_loader($script_tag) {
        if (false !== stripos($script_tag, 'themify-ptb-submissions/public/js/')) {
            $script_tag = str_replace('src=', 'data-cfasync="false" src=', $script_tag);
        }
        return $script_tag;
    }
    
    public function get_ptb_submission_shortcode($text){
        if($text && has_shortcode($text, 'ptb_submission')){
           $text = PTB_CMB_Base::format_text($text);
        }
        return $text;
    }

    public function user_profile_scripts() {
        if (!wp_script_is($this->plugin_name . '-profile')) {
            $plugin_url = plugin_dir_url(__FILE__);
            wp_enqueue_script($this->plugin_name . '-profile', $plugin_url . 'js/ptb-submission-profile.js', array('jquery'), $this->version, false);
            wp_enqueue_style($this->plugin_name . '-account', $plugin_url . 'css/ptb-submission-account.css', array(), $this->version, 'all');
        }
    }

    public function user_account_scripts() {
        if (!wp_script_is($this->plugin_name . '-account')) {
            $plugin_url = plugin_dir_url(__FILE__);
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style($this->plugin_name . '-datepicker', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
            wp_register_script($this->plugin_name . '-account', $plugin_url . 'js/ptb-submission-account.js', array($this->plugin_name . '-tablesaw', 'jquery-ui-datepicker'), $this->version, false);
            $translation_ = array(
                'delete' => __('Are You sure?', 'ptb-submission'),
                'lng' => PTB_Utils::get_current_language_code()
            );
            wp_localize_script($this->plugin_name . '-account', 'ptb_submission', $translation_);
            wp_enqueue_style($this->plugin_name . '-tablesaw', $plugin_url . 'css/tablesaw.css', array(), '1.4', 'all');
            wp_enqueue_style($this->plugin_name . '-account', $plugin_url . 'css/ptb-submission-account.css', array(), $this->version, 'all');
            wp_enqueue_script($this->plugin_name . '-tablesaw', $plugin_url . 'js/tablesaw.js', array('jquery'), '1.4', false);
            wp_enqueue_script($this->plugin_name . '-account');
        }
    }

    public function add_post_scripts() {
        if (!wp_script_is($this->plugin_name)) {
            $plugin_url = plugin_dir_url(__FILE__);
            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-effects-blind');
            wp_enqueue_script('jquery-effects-highlight');
            $langeages = PTB_Utils::get_all_languages();
            if (count($langeages) > 1) {
                wp_enqueue_style($this->plugin_name . '-languages', $plugin_url . 'css/ptb-submission-languages.css', array(), $this->version, 'all');
            }
            $translation_ = array(
                'errors' => __('Please check form fields', 'ptb-submission'),
                'tax_error' => __('Please fill input', 'ptb-submission'),
                'tax_all_errors' => __('Please fill all inputs', 'ptb-submission'),
                'lng' => PTB_Utils::get_current_language_code()
            );
            wp_enqueue_style($this->plugin_name, $plugin_url . 'css/ptb-submission-public.css', array(), $this->version, 'all');
            wp_enqueue_style('ptb-choosen', dirname($plugin_url) . '/admin/css/chosen.css', array(), '1.4.2', 'all');
            wp_enqueue_script($this->plugin_name . '-ui-widget', $plugin_url . 'js/jquery.ui.widget.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name . '-load-image', $plugin_url . 'js/load-image.all.min.js', array($this->plugin_name . '-ui-widget'), $this->version, false);
            wp_enqueue_script($this->plugin_name . '-iframe-transport', $plugin_url . 'js/jquery.iframe-transport.js', array($this->plugin_name . '-load-image'), $this->version, false);
            wp_enqueue_script($this->plugin_name . '-fileupload', $plugin_url . 'js/jquery.fileupload.js', array($this->plugin_name . '-iframe-transport'), $this->version, false);
            wp_enqueue_script($this->plugin_name . '-fileupload-process', $plugin_url . 'js/jquery.fileupload-process.js', array($this->plugin_name . '-fileupload'), $this->version, false);
            wp_enqueue_script($this->plugin_name . '-fileupload-validate', $plugin_url . 'js/jquery.fileupload-validate.js', array($this->plugin_name . '-fileupload-process'), $this->version, false);
            wp_enqueue_script($this->plugin_name . '-fileupload-image', $plugin_url . 'js/jquery.fileupload-image.js', array($this->plugin_name . '-fileupload'), $this->version, false);
            wp_enqueue_script('ptb-choosen', dirname($plugin_url) . '/admin/js/chosen.jquery.min.js', array('ptb'), '1.4.2', false);
            wp_register_script($this->plugin_name, $plugin_url . 'js/ptb-submission-public.js', array($this->plugin_name . '-fileupload-image', 'ptb-choosen', 'jquery-ui-autocomplete', 'jquery-ui-sortable'), $this->version, false);
            wp_localize_script($this->plugin_name, 'ptb_submission', $translation_);
            wp_enqueue_script($this->plugin_name);
        }
    }

    public function register_session() {
        PTB_Submission_Captcha::start_session();
    }

    public function shortcode($atts) {
        if (!isset($atts[0])) {
            return;
        }

        $post_type = esc_attr(str_replace(array('"', '='), '', $atts[0]));
        $options = PTB::get_option();
        $submission_options = PTB_Submission::get_option();
        $template = $submission_options->get_submission_template($post_type);
        if (!$template || !isset($template['frontend']['layout'])) {
            return;
        }
        $lang = PTB_Utils::get_current_language_code();
        $data = $template['frontend']['data'];
        if (isset($_GET['nonce']) && isset($_GET['form_id']) && wp_verify_nonce($_GET['nonce'], 'ptb_submission_success_' . $_GET['form_id'])) {
            return stripslashes_deep((PTB_Utils::get_label($data['m'])));
        }
        if (!empty($_POST) && (isset($_GET['success']) || isset($_GET['fail']))) {

            $config = get_option($this->plugin_name . '-settings');
            if (isset($_POST['receiver_email']) && $config['paypal-email'] == $_POST['receiver_email'] && isset($_POST['mc_gross']) && floatval($data['amount']) <= floatval($_POST['mc_gross'])) {
                $handler = apply_filters('ptb_submission_payment_success', 'PayPal', $_POST, $post_type, $data, $this->post);
                $payment = new PTB_Submission_Payment($handler, $this->plugin_name, $this->version);
                $item = $payment->get();
                ob_start();
                if (isset($_GET['success'])) {
                    $item->success($_POST, $data, $this->post);
                } elseif (isset($_GET['fail'])) {
                    $item->fail($_POST, $data, $this->post);
                }
                $content = ob_get_contents();
                ob_end_clean();
                if ($content) {
                    return $content;
                }
            }
        }
        $this->add_post_scripts();
        $layout = $template['frontend']['layout'];
        $cmb_options = $post_support = $post_taxonomies = array();
        $options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);

        $languages = PTB_Utils::get_all_languages();
        $count = count($layout) - 1;
        $form_id = uniqid();
        $logged = is_user_logged_in();
        $get_options = get_option($this->plugin_name . '-settings');
        if (!$logged && isset($get_options['account']) && $get_options['account']) {
            $cmb_options['user_email'] = array('type' => 'user_email');
            $cmb_options['user_name'] = array('type' => 'user_name');
            $cmb_options['user_password'] = array('type' => 'user_password');
            $post_support[] = 'user_email';
            $post_support[] = 'user_name';
            $post_support[] = 'user_password';
        }
        $cmb_options = apply_filters('ptb_submission_render', $cmb_options, $post_support, $this->post);
        ob_start();
        ?>
        <form method="post" class="ptb-submission-module-form ptb-submission-form" action="<?php echo admin_url('admin-ajax.php?action=ptb_submission_save') ?>" enctype="multipart/form-data">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce($this->plugin_name . '-' . $form_id) ?>" />
            <input type="hidden" name="form_id" value="<?php echo $form_id ?>" />
            <input type="hidden" name="post_type" value="<?php echo $post_type ?>" />
            <?php if ($this->post): ?>
                <input type="hidden" name="id" value="<?php echo $this->post->ID ?>" />
            <?php endif; ?>
            <div class="ptb-submission-loader"><div></div></div>
            <div class="ptb-submission-errors"></div>
            <div class="ptb-submission-form-layout">
                <?php foreach ($layout as $k => $row): ?>
                    <?php
                    $class = '';
                    if ($k == 0) {
                        $class .= 'first';
                    } elseif ($k == $count) {
                        $class .= 'last';
                    }
                    ?>
                    <div class="<?php if ($class): ?>ptb_<?php echo $class ?>_row<?php endif; ?> ptb_row ptb_<?php echo $post_type ?>_row">
                        <?php if (!empty($row)): ?>
                            <?php
                            $colums_count = count($row) - 1;
                            $i = 0;
                            ?>
                            <?php foreach ($row as $col_key => $col): ?>
                                <?php
                                $tmp_key = explode('-', $col_key);
                                $key = $tmp_key[0] . '-' . $tmp_key[1];
                                ?>
                                <div class="ptb_col ptb_col<?php echo $key ?><?php if ($i == 0): ?> ptb_col_first<?php elseif ($i == $colums_count): ?> ptb_col_last<?php endif; ?>">
                                    <?php if (!empty($col)): ?>
                                        <?php foreach ($col as $module): ?>
                                            <?php
                                            $meta_key = $module['key'];
                                            if (!isset($cmb_options[$meta_key])) {
                                                continue;
                                            }
                                            $args = $cmb_options[$meta_key];
                                            $type = $module['type'];
                                            $args['key'] = $meta_key;
                                            $field = in_array($type, $post_support);
                                            $cl = '';
                                            if (in_array($type, array('title', 'user_email', 'user_name', 'user_password'))) {
                                                $module['required'] = 1;
                                            } elseif ($type == 'text' && isset($args['repeatable']) && $args['repeatable']) {
                                                $cl = 'ptb_text_multi';
                                            }
                                            ?>
                                            <div data-type="<?php echo $type ?>" class="ptb_module<?php if (isset($module['required'])): ?> ptb-submission-module-req<?php endif; ?> ptb_<?php echo $type ?> <?php echo $cl ?><?php if (!$field): ?> ptb_<?php echo $meta_key ?><?php endif; ?>">
                                                <?php if (!$field): ?>
                                                    <?php $label = isset($module['label']) && $module['label'] ? PTB_Utils::get_label($module['label']) : false; ?>
                                                    <div class="ptb_back_active_module_label">
                                                        <label for="ptb_submission_<?php echo $meta_key ?>"><?php echo $label ? $label : PTB_Utils::get_label($args['name']); ?><?php if (isset($module['required'])): ?><span class="ptb-submission-required">*</span><?php endif; ?></label>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (has_action('ptb_submission_' . $type)): ?>
                                                    <?php do_action('ptb_submission_' . $type, $post_type, $args, $module, $this->post, $lang, $languages); ?>
                                                <?php else: ?>
                                                    <?php $this->render($type, $post_type, $args, $module, $this->post, $lang, $languages, $field); ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($type == 'user_password' && !has_action('ptb_submission_user_confirm_password')): ?>
                                                <div class="ptb_module ptb-submission-module-req ptb_submission_user_confirm_password">
                                                    <?php $args['key'] = 'user_confirm_password'; ?>
                                                    <?php $this->render($args['key'], $post_type, $args, $module, $this->post, $lang, $languages, $field); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <?php $i ++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$logged && isset($data['captcha']) && $data['captcha']): ?>
                    <?php
                    $captcha = PTB_Submission_Captcha::get_image_src($post_type); 
                    ?>
                    <div class="ptb-submission-module-req ptb_submission_captcha">
                        <div class="ptb_back_active_module_label"><label for="ptb_submission_captcha"><?php _e('Captcha', 'ptb-submission') ?><span class="ptb-submission-required">*</span></label></div>
                        <div class="ptb_back_active_module_input">
                            <table class="ptb-submission-captcha">
                                <tr>
                                    <td class="ptb-submission-captcha-img"><img src="<?php echo esc_url($captcha) ?>" alt="<?php _e('Captcha', 'ptb-submission') ?>" title="<?php _e('Captcha', 'ptb-submission') ?>" /></td>
                                    <td class="ptb-submission-captcha-code">                        
                                        <div>
                                            <input type="text" name="captcha" autocomplete="off"  maxlength="5" required="required" id="ptb_submission_captcha" />
                                            <a href="<?php echo admin_url('admin-ajax.php?action=ptb_submission_captcha') ?>" class="ptb-submission-upload-btn ptb-submission-captcha-refresh"><?php _e('Refresh', 'ptb-submission') ?></a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($this->checkpayment($data)): ?>
                    <?php $price_format = $submission_options->get_price_format($get_options['currency_position'], $get_options['currency'], $data['amount']); ?>
                    <div class="ptb-submission-fee-amount"><?php echo sprintf(__('Fee: %s', 'ptb-submission'), $price_format) ?></div>
                <?php endif; ?>
                <input class="ptb-submission-submit-btn" type="submit" value="<?php _e('Submit', 'ptb-submission') ?>"/>
                <div class="ptb-submission-form-error"></div>
                <div class="ptb-submission-success-text"></div>
            </div>
        </form>
        <?php 
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function render($type, $post_type, array $args, array $module, $post, $lang, $languages, $field) {
        $multifields = array('textarea', 'editor', 'text', 'title', 'excerpt');
        $data = array();
        $post_id = $post ? $post->ID : false;
        if ($post_id && $type != 'taxonomies' && $type != 'category') {
            $post = (array) $post;
            if (!in_array($type, $multifields)) {
                $data = get_post_meta($post_id, 'ptb_' . $args['key'], TRUE);
            } else {
                global $sitepress;
                $original_post = $post;
                foreach ($languages as $code => $lng) {
                    $post_ml_Id = isset($sitepress) ? icl_object_id($post_id, $post_type, FALSE, $code) : $post_id;
                    if (!$field && in_array($type, $multifields)) {
                        $data[$args['key']][$code] = get_post_meta($post_ml_Id, 'ptb_' . $args['key'], TRUE);
                    } elseif ($field) {
                        if ($post_ml_Id != $post_id) {
                            $post = get_post($post_ml_Id, ARRAY_A);
                        } else {
                            $post = $original_post;
                        }
                        if ($type == 'editor') {
                            $value = $post['post_content'];
                        } else {
                            $value = isset($post['post_' . $type]) ? $post['post_' . $type] : '';
                        }
                        $data[$args['key']][$code] = $value;
                    }
                }
                $post = $original_post;
                unset($original_post);
            }
        }
        ?>
        <?php if ($field && !in_array($type, array('taxonomies', 'category', 'custom_image', 'custom_text'))): ?>
            <?php
            if ($type === 'user_confirm_password') {
                $label = isset($module['label_confirm']) && $module['label_confirm'] ? PTB_Utils::get_label($module['label_confirm']) : false;
                if (!$label && isset($module['label']) && $module['label']) {
                    $label = PTB_Utils::get_label($module['label']);
                    $label = sprintf(__('Confirm %s', 'ptb-subbmission'), $label);
                } else {
                    $label = PTB_Submissiion_Options::get_name($type);
                }
            } else {
                $label = isset($module['label']) && $module['label'] ? PTB_Utils::get_label($module['label']) : PTB_Submissiion_Options::get_name($type);
            }
            ?>
            <div class="ptb_back_active_module_label">	
                <label for="ptb_submission_<?php echo $args['key'] ?>"><?php echo $label ? $label : PTB_Submissiion_Options::get_name($type) ?><?php if ($type == 'title' || isset($module['required'])): ?><span class="ptb-submission-required">*</span><?php endif; ?></label>
            </div>
        <?php endif; ?>
        <?php
        switch ($type) {
            case in_array($type, $multifields):
                if (($type != 'text') || ($type == 'text' && !$args['repeatable'])) {
                    $input = $type != 'editor' ?
                            ($type == 'textarea' || $type == 'excerpt' ? 'textarea' : 'text') : (isset($module['html']) ? 'wp_editor' : 'textarea');
                    PTB_CMB_Base::module_language_tabs('submission', $data, $languages, $args['key'], $input, false, true);
                } else {
                    $empty = true;
                    ?>
                    <div class="ptb_back_active_module_input ptb-submission-multi-text">
                        <ul>
                            <?php if (!empty($data[$args['key']])): ?>
                                <?php foreach ($data[$args['key']] as $value): ?>
                                    <?php
                                    $v[$args['key']] = array();
                                    foreach ($languages as $code => $lng) {
                                        if (isset($data[$args['key']][$code]) && !empty($data[$args['key']][$code])) {
                                            $k = key($data[$args['key']][$code]);
                                            $v[$args['key']][$code] = $data[$args['key']][$code][$k];
                                            unset($data[$args['key']][$code][$k]);
                                        }
                                    }
                                    if (empty($v[$args['key']])) {
                                        continue;
                                    }
                                    $empty = false;
                                    ?>
                                    <li class="ptb-submission-text-option">
                                        <i title="<?php _e('Sort', 'ptb-submission') ?>" class="fa fa-sort ptb-submission-option-sort"></i>
                                        <?php PTB_CMB_Base::module_language_tabs('submission', $v, $languages, $args['key'], 'text', false, true); ?>
                                        <i title="<?php _e('Remove', 'ptb-submission') ?>" class="ptb-submission-remove fa fa-times-circle"></i>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($empty): ?>
                                <li class="ptb-submission-text-option">
                                    <i title="<?php _e('Sort', 'ptb-submission') ?>" class="fa fa-sort ptb-submission-option-sort"></i>
                                    <?php PTB_CMB_Base::module_language_tabs('submission', array(), $languages, $args['key'], 'text', false, true); ?>
                                    <i title="<?php _e('Remove', 'ptb-submission') ?>" class="ptb-submission-remove fa fa-times-circle"></i>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class=" ptb-submission-option-add">
                            <i class="fa fa-plus-circle"></i>
                            <?php _e('Add new', 'ptb-submission') ?>
                        </div>
                    </div>
                    <?php
                }
                if (isset($module['show_description'])):
                    ?>
                    <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
                    <?php
                endif;
                break;
            case 'checkbox':
            case 'radio_button':
                if (!empty($args['options'])) {
                    if (!is_array($data)) {
                        $data = array($data);
                    }
                    ?>
                    <div class="ptb_back_active_module_input">
                        <?php foreach ($args['options'] as $opt): ?>
                            <?php $opt['id'] = esc_attr($opt['id']); ?>
                            <label for="ptb_<?php echo $opt['id'] ?>">
                                <input type="<?php echo $type == 'radio_button' ? 'radio' : $type ?>" <?php if ((!$data && (isset($opt['checked']) && $opt['checked'])) || in_array($opt['id'], $data)): ?>checked="checked"<?php endif; ?> name="submission[<?php echo $args['key'] ?>]<?php if ($type == 'checkbox'): ?>[]<?php endif; ?>" id="ptb_<?php echo $opt['id'] ?>" value="<?php echo $opt['id'] ?>"  />
                                <span><?php echo PTB_Utils::get_label($opt); ?></span>
                            </label>
                        <?php endforeach; ?>
                        <?php if (isset($module['show_description'])): ?>
                            <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                break;
            case 'select':
                if (!empty($args['options'])) {
                    $multi = !empty($args['multipleSelects']);
                    if (!is_array($data)) {
                        $data = array($data);
                    }
                    ?>
                    <div class="ptb_back_active_module_input">
                        <select class="ptb-select" name="submission[<?php echo $args['key'] ?>]<?php if ($multi): ?>[]<?php endif; ?>" <?php if ($multi): ?>multiple="multiple"<?php endif; ?> id="ptb_submission_<?php echo $args['key'] ?>">
                            <?php if(!isset($module['required']) && !$multi):?>
                                 <option>---</option>
                            <?php endif;?>
                            <?php foreach ($args['options'] as $opt): ?>
                                <?php $opt['id'] = esc_attr($opt['id']); ?>
                                <option <?php if (in_array($opt['id'], $data)): ?>selected="selected"<?php endif; ?> value="<?php echo $opt['id'] ?>"><?php echo PTB_Utils::get_label($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($module['show_description'])): ?>
                            <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                break;
            case 'thumbnail':
            case 'image':
                if (!isset($module['size'])) {
                    $module['size'] = false;
                }
                if (!isset($module['extensions']) || empty($module['extensions'])) {
                    $module['extensions'] = array('all');
                }
                $size = PTB_Submissiion_Options::max_upload_size($module['size']);
                $module['extensions'] = array_keys(PTB_Submissiion_Options::get_allow_ext($module['extensions']));
                ?>
                <div class="ptb_back_active_module_input">
                    <label data-label="<?php _e('Choose Image', 'ptb_extra') ?>" class="ptb-submission-upload-btn" for="ptb_submission_<?php echo $args['key'] ?>"><?php _e('Choose Image', 'ptb-submission') ?></label>
                    <div class="ptb-submission-file-wrap">
                        <input type="file" name="<?php echo $args['key'] ?>" id="ptb_submission_<?php echo $args['key'] ?>" class="ptb-submission-file" data-extension="<?php esc_attr_e(str_replace(',', '|', implode('|', $module['extensions']))) ?>" data-size="<?php echo $size ?>" />
                    </div>
                    <div class="ptb-submission-priview">
                        <?php
                        if ($type == 'thumbnail') {
                            $thumb = get_post_thumbnail_id($post_id);
                            if ($thumb) {
                                $data = wp_get_attachment_thumb_url($thumb);
                            }
                        }
                        ?> 
                        <?php if ($data): ?>
                            <?php $data = is_array($data) && isset($data[1]) ? $data[1] : (isset($data['url']) ? $data['url'] : $data); ?>
                            <img width="100" height="100" src="<?php echo esc_url_raw($data) ?>" />
                            <input type="hidden" value="1" name="submission[<?php echo $args['key'] ?>]" />
                            <?php if (!isset($module['required'])): ?>
                                <span data-slug="ptb_submission_<?php echo $args['key'] ?>" class="fa fa-minus-circle ptb-submission-file-remove"></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($module['show_description'])): ?>
                        <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
                    <?php endif; ?>
                </div>
                <?php
                break;
            case 'category':
            case 'taxonomies':
                $label = isset($module['label']) && $module['label'] ? PTB_Utils::get_label($module['label']) : false;
                if ($type == 'category') {
                    $terms = $module['show_as'] != 'autocomplete' ? get_terms('category', array('hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC', 'fields' => 'id=>name')) : array();
                    $name = $label ? $label : (in_array($module['show_as'], array('multiselect', 'checkbox')) ? __('Categories', 'ptb-submission') : __('Category', 'ptb-submission'));
                    $slug = 'category';
                } else {
                    $slug = sanitize_key($module['taxonomies']);
                    $options = PTB::get_option();
                    $taxonomy = $options->get_custom_taxonomy($slug);
                    if (empty($taxonomy)) {
                        break;
                    }
                    $terms = $module['show_as'] != 'autocomplete' ? get_terms($slug, array('hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC', 'fields' => 'id=>name')) : array();
                    $name = $label ? $label : (in_array($module['show_as'], array('multiselect', 'checkbox')) ? PTB_Utils::get_label($taxonomy->plural_label) : PTB_Utils::get_label($taxonomy->singular_label));
                }
                if ($post_id) {
                    $vals = get_the_terms($post_id, $slug);
                    if (!empty($vals)) {
                        foreach ($vals as $v) {
                            $data[$v->term_id] = $v->name;
                        }
                    }
                }

                $this->show_as($terms, $module, $args, $data, $name, $slug, $post_type);
                break;
            case 'post_tag':
                if($post_id){
                    global $sitepress;
                    $id = $post_id;
                    foreach($languages as $code=>$lng){
                        if(isset($sitepress)){
                            $id = icl_object_id($post_id,'post',false,$code);
                        }
                        $tags = get_the_tags($id);
                        $post_tags = array();
                        foreach($tags as $t){
                            $post_tags[] = $t->name;
                        }
                        $data[$args['key']][$code] = implode(', ',$post_tags);
                    }
                }
                if(empty($data)){
                    $data = array();
                }
                PTB_CMB_Base::module_language_tabs('submission', $data, $languages, $args['key'],'text', false, true);
                ?>
                <br/>
                <small><?php _e('Separate tags with commas','ptb-submission');?></small>
                <?php
                break;
            case 'email':
                ?>
                <div class="ptb_back_active_module_input">
                    <input type="email" name="submission[<?php echo $args['key'] ?>]" value="<?php echo $data ? esc_html($data) : '' ?>" id="ptb_submission_<?php echo $args['key'] ?>" />
                    <?php if (isset($module['show_description'])): ?>
                        <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
                    <?php endif; ?>
                </div>
                <?php
                break;
            case 'link_button':
                $ltext = isset($module['text']) ? PTB_Utils::get_label($module['text']) : __('Link Text', 'ptb-submission');
                $utext = isset($module['url']) ? PTB_Utils::get_label($module['url']) : __('Link Url', 'ptb-submission');
                ?>
                <div class="ptb_back_active_module_input">
                    <table class="ptb-submission-link">
                        <tr>
                            <td><input placeholder="<?php echo $ltext ?>" type="text" <?php if ($data && isset($data[0])): ?>value="<?php esc_html_e($data[0]) ?>"<?php endif; ?> name="submission[<?php echo $args['key'] ?>][]"/></td>
                            <td class="ptb-submission-link-arrow"><span class="ptb-submission-link-span"><?php _e('Link', 'ptb-submission') ?></span><span class="fa fa-arrow-right"></span></td>
                            <td><input placeholder="<?php echo $utext ?>" type="text" <?php if ($data && isset($data[1])): ?>value="<?php echo esc_url($data[1]) ?>"<?php endif; ?> name="submission[<?php echo $args['key'] ?>][]" id="ptb_submission_<?php echo $args['key'] ?>" /></td>
                        </tr>
                    </table>
                    <?php if (isset($module['show_description'])): ?>
                        <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
                    <?php endif; ?>
                </div>
                <?php
                break;
            case 'custom_image':
                ?>
                <?php if (isset($module['image']) && $module['image']): ?>
                    <?php
                    $url = PTB_CMB_Base::ptb_resize($module['image'], $module['width'], $module['height']);
                    ?>
                    <figure class="<?php echo $this->plugin_name ?>_post_image clearfix">
                        <?php
                        if (isset($module['link']) && $module['link']): echo '<a href="' . $module['link'] . '">';
                        endif;
                        ?>
                        <img src="<?php echo $url ?>" />
                        <?php
                        if (isset($module['link']) && $module['link']): echo '</a>';
                        endif;
                        ?>
                    </figure>
                <?php endif; ?>
                <?php
                break;
            case 'custom_text':
                ?>
                <?php if ($module['text'][$lang]): ?>
                    <?php echo!has_shortcode($module['text'][$lang], $this->plugin_name) ? do_shortcode($module['text'][$lang]) : $module['text'][$lang]; ?>
                <?php endif; ?>
                <?php
                break;
            case 'number':
                $range = isset($args['range']) && $args['range'];
                $min = isset($module['min']) && $module['min'] ? $module['min'] : '';
                $max = isset($module['max']) && $module['max'] && $module['max'] >= $min ? $module['max'] : '';
                if (!$data) {
                    $data = $range ? array('from' => $min, 'to' => $max) : $min;
                }
                ?>
                <div class="ptb_back_active_module_input">
                    <?php if ($range): ?>
                        <table class="ptb-submission-link">
                            <tr>
                                <td><input class="ptb_number_min" placeholder="<?php _e('From', 'ptb-submission') ?>" step="0.01" max="<?php echo $max ?>" min="<?php echo $min ?>" type="number" name="submission[<?php echo $args['key'] ?>][from]" value="<?php echo is_numeric($data) ? $data : (is_array($data) && isset($data['from']) ? $data['from'] : ''); ?>"/></td>
                                <td class="ptb-submission-link-arrow"><span class="ptb-submission-link-span"><?php _e('To', 'ptb-submission') ?></span><span class="fa fa-arrow-right"></span></td>
                                <td><input class="ptb_number_max" placeholder="<?php _e('To', 'ptb-submission') ?>" step="0.01" max="<?php echo $max ?>" min="<?php echo $min ?>" type="number"  name="submission[<?php echo $args['key'] ?>][to]" value="<?php echo is_array($data) && isset($data['to']) ? $data['to'] : ''; ?>"/></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <input type="number"  step="0.01" name="submission[<?php echo $args['key'] ?>]" min="<?php echo $min ?>" max="<?php echo $max ?>" value="<?php echo is_numeric($data) ? $data : (is_array($data) && isset($data['from']) ? $data['from'] : ''); ?>"/>
                    <?php endif; ?>
                </div>
                <?php
                break;
            case 'user_email':
            case 'user_name':
            case 'user_password':
            case 'user_confirm_password':
                ?>
                <div class="ptb_back_active_module_input">
                    <?php
                    if ($type == 'user_password' || $type == 'user_confirm_password') {
                        $input_type = 'password';
                    } elseif ($type == 'user_email') {
                        $input_type = 'email';
                    } else {
                        $input_type = 'text';
                    }
                    ?>
                    <input type="<?php echo $input_type ?>" required="required" name="submission[<?php echo $type ?>]" id="ptb_submission_<?php echo $args['key'] ?>" />
                </div>
                <?php
                break;
        }
    }

    private function show_as(array $terms, array $module, array $args, array $data, $name, $slug, $post_type) {

        if ($slug == 'category') {
            $input_name = '[' . $slug . ']';
            $id = $slug;
        } else {
            $input_name = '[' . $args['key'] . '][' . $slug . ']';
            $id = $args['key'] . '_' . $slug;
        }
        $input_name = 'submission' . $input_name;
        ?>
        <div class="ptb_back_active_module_label">
            <label for="ptb_tax_<?php echo $id ?>"><?php echo $name ?><?php if (isset($module['required'])): ?><span class="ptb-submission-required">*</span><?php endif; ?></label>
        </div>
        <div class="ptb_back_active_module_input">
            <?php if (!empty($terms) || $module['show_as'] == 'autocomplete'): ?>
                <?php
                switch ($module['show_as']) {
                    case 'select':
                    case 'multiselect':
                        $multi = $module['show_as'] == 'multiselect';

                        if ($data) {
                            $data = array_keys($data);
                        }
                        ?>
                        <select class="ptb-select" data-placeholder="<?php echo $multi ? sprintf(__('Select %s', 'ptb-submission'), $name) : sprintf(__('Select a %s', 'ptb-submission'), $name) ?>" name="<?php echo $input_name ?><?php if ($multi): ?>[]<?php endif; ?>" <?php if ($multi): ?>multiple="multiple"<?php endif; ?> id="ptb_tax_<?php echo $id ?>">
                            <?php if (!isset($module['required']) && !$multi): ?>
                                <option>---</option>
                            <?php endif; ?>
                            <?php foreach ($terms as $id => $t): ?>
                                <option <?php if (in_array($id, $data)): ?>selected="selected"<?php endif; ?> value="<?php echo intval($id); ?>"><?php esc_html_e($t); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        break;
                    case 'checkbox':
                    case 'radio':
                        if ($data) {
                            $data = array_keys($data);
                        }
                        foreach ($terms as $eid => $t):
                            ?>
                            <label for="ptb_tax_<?php echo $eid ?>">
                                <input type="<?php echo $module['show_as'] ?>" <?php if (in_array($eid, $data)): ?>checked="checked"<?php endif; ?> name="<?php echo $input_name ?><?php if ($module['show_as'] == 'checkbox'): ?>[]<?php endif; ?>" id="ptb_tax_<?php echo $eid ?>" value="<?php echo $eid ?>"/>
                                <span><?php esc_html_e($t); ?></span>
                            </label>
                            <?php
                        endforeach;
                        break;
                    case 'autocomplete':
                        $key = is_array($data) && $data ? key($data) : false;
                        ?>
                        <div class="ui-widget">
                            <input placeholder="<?php printf(__('Search a %s', 'ptb-submission'), $name) ?>" type="text" autocomplete="off" value="<?php echo $key ? $data[$key] : '' ?>" class="ptb-submission-autocomplete" data-slug="<?php echo $slug ?>" data-post_type="<?php echo $post_type ?>" id="ptb_tax_<?php echo $id ?>"/>
                            <input type="hidden"  name="<?php echo $input_name ?>" value="<?php echo $key ? $key : '' ?>"/>
                        </div>
                        <?php
                        break;
                }
                ?>
            <?php endif; ?>
            <?php if (isset($module['allow']) && $module['allow']): ?>
                <?php $multi = $module['show_as'] == 'multiselect' || $module['show_as'] == 'checkbox' || $module['show_as'] == 'radio'; ?>
                <?php _e('or', 'ptb-submission') ?>
                <a id="ptb_submission_lightbox_<?php echo $slug ?>" href="<?php echo admin_url('admin-ajax.php?action=ptb_submission_add_tax&slug=' . $slug . '&label=' . base64_encode($name) . '&multi=' . intval($multi)) ?>" class="ptb_open_lightbox"><?php printf(__('Add %s', 'ptb-submission'), $name) ?></a>
                <input type="hidden" value="" name="submission[<?php echo $slug ?>_add]" />
            <?php endif; ?>
        </div>
        <?php
    }

    public function account($atts) {
        if (!is_user_logged_in()) {
            add_filter('login_form_middle',array($this,'social_login'),10,2);
            return wp_login_form(array('echo' => false));
        }

        $page = isset($_GET['ptb_action']) ? sanitize_text_field($_GET['ptb_action']) : false;
        ob_start();
        if ($page == 'edit') {
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                $this->user_account_scripts();
                $this->display_user_posts();
            } else {
                $id = intval($_GET['id']);
                $post = get_post($id);
                if (!$post || $post->post_author != get_current_user_id()) {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header(404);
                    wp_die();
                }
                wp_reset_postdata();
                $post_type = $post->post_type;
                $this->post = $post;
                echo do_shortcode('[ptb_submission=' . $post_type . ']');
            }
        } elseif ($page == 'profile') {
            $this->user_profile_scripts();
            $this->display_edit_profile();
        } else {
            $this->user_account_scripts();
            $this->display_user_posts();
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    public function social_login($log,$args){
        ob_start();
        do_action('login_form');
        $log.= ob_get_contents();
        ob_end_clean();
        remove_filter('login_form_middle',array($this,'social_login'),10,2);
        return $log;
    }

    public function display_user_posts() {

        $ptb_options = PTB::get_option();
        $post_types = $ptb_options->get_custom_post_types();
        $submission_options = PTB_Submission::get_option();
        $submission_types = array();
        if (!empty($post_types)) {

            $post_type_data = array();
            foreach ($post_types as $post_type) {
                $submission_data = $submission_options->get_submission_template($post_type->slug);
                if ($submission_data) {
                    $submission_types[$post_type->slug] = $submission_data;
                    $post_type_data[$post_type->slug] = (array) $post_type;
                }
            }
        }
        $submission_types = apply_filters('ptb_submission_posts_tabs', $submission_types);
        ?>
        <div class="ptb-submission-account-page">
            <?php if (!empty($submission_types)): ?>
                <?php $post_types = array_keys($submission_types); ?>
                <form action="<?php echo admin_url('admin-ajax.php') ?>" method="post" class="ptb-submission-module-form ptb_submission_account_filter">
                    <input type="hidden" name="action" value="ptb_account_filter" />
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce($this->plugin_name . '-filter-posts') ?>" />
                    <input  type="hidden" name="paged" value="1" />
                    <table class="tablesaw tablesaw-stack">
                        <tr>
                            <td>
                                <select name="submission[post_status]">
                                    <option value=""><?php _e('Status', 'ptb-submission'); ?></option>
                                    <option value="publish"><?php _e('Publish', 'ptb-submission') ?></option>
                                    <option value="pending"><?php _e('Pending', 'ptb-submission') ?></option>
                                    <option value="draft"><?php _e('Draft', 'ptb-submission') ?></option>
                                </select>
                            </td>
                            <td>
                                <select  name="submission[post_type]">
                                    <option value=""><?php _e('Post Type', 'ptb-submission'); ?></option>
                                    <?php foreach ($post_type_data as $k => $p): ?>
                                        <option value="<?php echo $k ?>"><?php echo PTB_Utils::get_label($p['plural_label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="ptb_submission_datepicker" placeholder="<?php _e('Date From', 'ptb-submission'); ?>" id="ptb_submission_date_from" name="submission[from]" />
                            </td>
                            <td>   
                                <input type="text" class="ptb_submission_datepicker" placeholder="<?php _e('Date To', 'ptb-submission'); ?>" id="ptb_submission_date_to" name="submission[to]" />
                            </td>
                            <td>
                                <input type="text" placeholder="<?php _e('Post Title', 'ptb-submission'); ?>" name="submission[post_title]" />
                            </td>
                        </tr>
                    </table>
                    <input type="submit" class="ptb-submission-submit-btn" value="<?php _e('Search', 'ptb-submission') ?>" />
                    <div class="ptb_submission_table_wrapper">
                        <?php $this->get_user_posts(); ?>
                    </div>
                </form>
                <div class="ptb-submission-loader"><div></div></div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function get_user_posts($paged = false, array $args = array()) {
        $ptb_options = PTB::get_option();
        $post_types = $ptb_options->get_custom_post_types();
        $submission_options = PTB_Submission::get_option();
        $submission_types = array();
        foreach ($post_types as $post_type) {
            $submission_data = $submission_options->get_submission_template($post_type->slug);
            if ($submission_data) {
                $submission_types[$post_type->slug] = $submission_data['frontend']['data'];
            }
        }
        if (empty($args)) {
            $args = array(
                'author' => get_current_user_id(),
                'orderby' => 'date',
                'order' => 'DESC',
                'post_status' => 'publish,pending,draft',
                'post_type' => array_keys($submission_types),
                'pagination' => true,
                'posts_per_page' => get_option('posts_per_page'),
                'paged' => ( get_query_var('paged') ? get_query_var('paged') : 1 )
            );
        }
        if ($paged) {
            $args['paged'] = $paged;
        }
        $args = apply_filters('ptb_submission_args', $args);
        if (has_action('ptb_submission_account_posts')) {
            do_action('ptb_submission_account_posts', $args);
        } else {
            $posts = new WP_Query($args);
            $posts = apply_filters('ptb_submission_posts', $posts);
            $column = array(
                'post_title' => __('Name', 'ptb-submission'),
                'post_type' => __('Post Type', 'ptb-submission'),
                'post_status' => __('Status', 'ptb-subbmission'),
                'payment_status' => __('Payment Status', 'ptb-subbmission')
            );

            $admin_url = admin_url('admin-ajax.php');
            $column = apply_filters('ptb_submission_column', $column);
            $rnonce = wp_create_nonce($this->plugin_name . '-remove-post');
            $current_page = get_the_permalink();
            if(!$current_page || defined( 'DOING_AJAX' )){
                $current_page = $_SERVER['HTTP_REFERER'];
            }
            ?>
            <?php if ($posts->have_posts()): ?>
                <table class="tablesaw tablesaw-stack ptb-submisssion-account-posts" data-tablesaw-mode="stack">
                    <thead>
                        <tr>
                            <?php foreach ($column as $c => $title): ?>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist" class="<?php if ($c == 'title'): ?> title<?php endif ?>ptb_submission_<?php echo $c ?>"><?php echo $title ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <?php
                    while ($posts->have_posts()):$posts->the_post();
                        global $post;
                        ?>
                        <tr>
                            <td class="ptb_submission_post_name">
                                <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
                                <div class="ptb_submission_actions">
                                    <a class="ptb_submission_post_edit" href="<?php echo add_query_arg(array('ptb_action' => 'edit', 'id' => $post->ID),$current_page) ?>"><?php _e('Edit', 'ptb-submission') ?></a>
                                    <span class="ptb_submission_action_seperator">|</span>
                                    <a class="ptb_submission_post_delete" href="<?php echo add_query_arg(array('id' => $post->ID, 'nonce' => $rnonce), admin_url('admin-ajax.php')) ?>"><?php _e('Delete', 'ptb-submission') ?></a>
                                </div>
                            </td>
                            <td class="ptb_submission_post_type"><?php echo $post->post_type; ?></td>
                            <td class="ptb_submission_post_details">
                                <div class="ptb_submission_post_date"><?php the_date(); ?></div>
                                <div class="ptb_submission_post_status"><?php echo $post->post_status; ?></div>
                            </td>
                            <td class="ptb_submission_payment_status">
                                <?php if ($post->post_status == 'draft'): ?>
                                    <?php _e('Not Paid', 'ptb-subbmission'); ?>
                                    <a href="<?php echo add_query_arg(array('action' => 'ptb_submission_payment', 'post_id' => $post->ID), $admin_url); ?>"><?php _e('Pay', 'ptb-submission') ?></a>
                                <?php else: ?>
                                    <?php _e('Paid', 'ptb-subbmission'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <div class="ptb_submission_pagenav">
                    <?php
                    echo paginate_links(array(
                        'total' => $posts->max_num_pages,
                        'format' => '?paged=%#%',
                        'current' => $args['paged']
                    ));
                    ?>
                </div>
            <?php else: ?>
                <div class="ptb_subbmission_account_not_found"><?php _e('Not Found', 'ptb-subbmission') ?></div>
            <?php endif; ?>
        <?php } ?>
        <?php
        wp_reset_query();
    }

    public function save_post() {
        global $wp_query;

        if (isset($_POST['submission']) && isset($_POST['post_type']) && isset($_POST['form_id']) && isset($_POST['nonce']) && defined('DOING_AJAX') && check_ajax_referer($this->plugin_name . '-' . $_POST['form_id'], 'nonce')) {

            $ptb_options = PTB::get_option();
            $post_type = sanitize_key($_POST['post_type']);
            $post_type_data = $ptb_options->get_custom_post_type($post_type);
            if (!$post_type_data) {
                $wp_query->set_404();
                status_header(404);
                wp_die();
            }
            $options = PTB_Submission::get_option();
            $template_frontend = $options->get_submission_template($post_type);
            if (!$template_frontend || !isset($template_frontend['frontend']['layout'])) {
                $wp_query->set_404();
                status_header(404);
                wp_die();
            }
            $logged = is_user_logged_in();
            if (isset($_POST['id'])) {
                if (!$logged) {
                    $wp_query->set_404();
                    status_header(404);
                    wp_die();
                }
                $post_id = intval($_POST['id']);
                $post = get_post($post_id);
                if (!$post || $post->post_author != get_current_user_id()) {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header(404);
                    wp_die();
                }
            } else {
                $post_id = false;
            }
            $data = $template_frontend['frontend']['data'];
            if (!$logged && isset($data['captcha']) && $data['captcha'] && (!isset($_POST['captcha']) || !PTB_Submission_Captcha::compaier_code($post_type, $_POST['captcha']))) {
                die(json_encode(array('captcha' => __('Wrong captcha', 'ptb-submission'))));
            }
            $post_data = sanitize_post($_POST['submission'], 'db');
            $cmb_options = $post_support = $errors = $post_taxonomies = array();
            $lang = PTB_Utils::get_current_language_code();
            $languages = PTB_Utils::get_all_languages();
            $ptb_options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
            $layout = $template_frontend['frontend']['layout'];
            $result_data = $result_meta = array();
            if (!$logged) {
                $get_options = get_option($this->plugin_name . '-settings');
                if (isset($get_options['account']) && $get_options['account']) {
                    $cmb_options['user_email'] = array('type' => 'user_email');
                    $cmb_options['user_name'] = array('type' => 'user_name');
                    $cmb_options['user_password'] = array('type' => 'user_password');
                    $post_support[] = 'user_email';
                    $post_support[] = 'user_name';
                    $post_support[] = 'user_password';
                }
            }

            foreach ($layout as $k => $row) {
                foreach ($row as $col_key => $col) {
                    foreach ($col as $module) {
                        $meta_key = esc_attr($module['key']);
                        if (!isset($cmb_options[$meta_key])) {
                            continue;
                        }
                        $args = $cmb_options[$meta_key];
                        $type = $module['type'];
                        if (isset($cmb_options['user_email']) && in_array($type, array('user_email', 'user_password', 'user_name'))) {
                            $module['required'] = 1;
                        }
                        $metabox = !in_array($type, $post_support);
                        $result = has_filter('ptb_submission_validate_' . $type) ?
                                apply_filters('ptb_submission_validate_' . $type, $post_data, $args, $module, $post_type, $post_id, $lang, $languages) : $this->validate($type, $post_type, $args, $module, $post_data, $metabox, $post_id, $lang);
                        if ($result && !is_array($result)) {
                            $errors[$meta_key] = $result;
                        } else{   
                                if(is_array($result) && has_filter('ptb_submission_validate_' . $type)){
                                    $post_data=$result;
                                }
                                if (isset($post_data[$meta_key]) || isset(self::$files[$meta_key])) {
                                    $val = isset($post_data[$meta_key]) ? $post_data[$meta_key] : self::$files[$meta_key];
                                    if ($metabox) {
                                        $result_meta[$meta_key] = array('value' => $val, 'type' => $type);
                                    } else {
                                        $result_data[$meta_key] = $val;
                                    }
                                }
                        }
                    }
                }
            }
            if (!empty($errors)) {
                $this->removefiles();
                die(wp_json_encode($errors));
            } else {


                if (is_user_logged_in()) {
                    $current_user = wp_get_current_user();
                } elseif (isset($result_data['user_name'])) {
                    $user = wp_create_user($result_data['user_name'], $result_data['user_password'], $result_data['user_email']);
                    if (is_wp_error($user)) {
                        $this->removefiles();
                        die(wp_json_encode($user->get_error_message()));
                    }
                    $current_user = get_user_by('id', $user);
                    wp_clear_auth_cookie();
                    wp_set_current_user($user);
                    wp_set_auth_cookie($user, true, false);
                    update_user_caches($current_user);
                    wp_new_user_notification($user, null, 'user');
                }
                if (isset($current_user)) {
                    $current_user->add_role('ptb');
                }
                $post_status = $this->checkpayment($data) ? 'draft' : (isset($data['approve']) && $data['approve'] ? 'publish' : 'pending');
                $_post = array('post_type' => $post_type,
                    'post_status' => $post_status,
                    'post_author' => isset($current_user) ? $current_user->ID : false
                );

                if (isset($result_data['category']) && !empty($result_data['category'])) {
                    $_post['post_category'] = is_array($result_data['category']) ?
                            $result_data['category'] :
                            array($result_data['category']);
                    unset($result_data['category']);
                }
                $multilfields = array('title' => 'post_title', 'excerpt' => 'post_excerpt', 'editor' => 'post_content','post_tag'=>'post_tag');
                $multilfields = apply_filters('ptb_submission_multifields_save', $multilfields, $post_data);
                foreach ($multilfields as $k => $m) {
                    if (isset($result_data[$k]) && isset($result_data[$k][$lang])) {
                        $_post[$m] = $result_data[$k][$lang];
                    }
                }
                if ($post_id) {
                    $_post['ID'] = $post_id;
                } else {
                    $_post['slug'] = sanitize_title($_post['post_title']);
                }
                //creating post with arguments above and assign post id to $def_post_id
                $def_post_id = wp_insert_post($_post, TRUE);
                if (!is_wp_error($def_post_id)) {
                    $attach_id = false;
                    if (isset(self::$files['thumbnail'])) {
                        $wp_upload_dir = wp_upload_dir();
                        $attachment = array(
                            'guid' => $wp_upload_dir['url'] . '/' . basename(self::$files['thumbnail']['file']),
                            'post_mime_type' => self::$files['thumbnail']['type'],
                            'post_title' => basename(self::$files['thumbnail']['file']),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );
                        // Insert the attachment.
                        $attach_id = wp_insert_attachment($attachment, self::$files['thumbnail']['file'], $def_post_id);
                        if ($attach_id > 0) {
                            require_once( ABSPATH . 'wp-admin/includes/image.php' );
                            $attach_data = wp_generate_attachment_metadata($attach_id, self::$files['thumbnail']['file']);
                            wp_update_attachment_metadata($attach_id, $attach_data);
                            set_post_thumbnail($def_post_id, $attach_id);
                        }
                        unset($result_data['thumbnail'], self::$files['thumbnail']);
                    } elseif (isset($result_data['thumbnail']) && $post_id) {
                        $remove_thumb = 1;
                        delete_post_thumbnail($post_id);
                        unset($result_data['thumbnail']);
                    }
                   
                    $taxonomies = !empty($result_data['taxonomies']) ? $result_data['taxonomies'] : false;
                    if ($taxonomies) {
                        $this->set_post_taxonomies($taxonomies, $def_post_id);
                        unset($result_data['taxonomies']);
                    }
                    $post_tags = !empty($result_data['post_tag']) ? $result_data['post_tag'] : false;

                    if($post_tags){
                        wp_set_post_tags( $def_post_id, $post_tags[$lang], false );
                    }
                    $this->set_post_meta($result_meta, $def_post_id, $post_data, $lang);
                    unset($languages[$lang]);
                    global $sitepress;
                    if (!empty($languages) && isset($sitepress)) {
                        include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
                        wpml_update_translatable_content('post_' . $post_type, $def_post_id, $lang);
                        $def_trid = wpml_get_content_trid('post_' . $post_type, $def_post_id);
                        foreach ($languages as $code => $lng) {
                            $_post_ml = array();
                            foreach ($multilfields as $k => $m) {
                                if (isset($result_data[$k][$code])) {
                                    $_post_ml[$m] = $result_data[$k][$code];
                                }
                            }
                            $_post_ml = array_merge($_post, $_post_ml);
                            if ($post_id) {
                                $post_ml_Id = icl_object_id($post_id, $post_type, FALSE, $code);
                                if ($post_ml_Id) {
                                    $_post_ml['ID'] = $post_ml_Id;
                                }
                            } else {
                                $_post_ml['slug'] = sanitize_title($_post_ml['post_title']);
                            }
                            $_post_ml_id = wp_insert_post($_post_ml, true);
                            if (!is_wp_error($_post_ml_id)) {
                                if (!$this->post) {
                                    update_post_meta($_post_ml_id, 'ptb_submission_is_post', 1);
                                }
                                wpml_update_translatable_content('post_' . $post_type, $_post_ml_id, $code);
                                if ($attach_id) {
                                    set_post_thumbnail($_post_ml_id, $attach_id);
                                } elseif (isset($remove_thumb)) {
                                    delete_post_thumbnail($_post_ml_id);
                                }
                                if ($taxonomies) {
                                    $this->set_post_taxonomies($taxonomies, $_post_ml_id);
                                }
                                if(!empty($post_tags[$code])){
                                    wp_set_post_tags( $_post_ml_id, $post_tags[$code], false );
                                }
                                $this->set_post_meta($result_meta, $_post_ml_id, $post_data, $code);
                                //change language and trid of second post to match $code and default post trid
                                $sitepress->set_element_language_details($_post_ml_id, 'post_' . $post_type, $def_trid, $code);
                            }
                        }
                    }
                    $notify_email = isset($data['email']) && trim($data['email']) ? sanitize_email($data['email']) : get_option('admin_email');
                    if (!$this->post) {
                        if ($notify_email) {
                            $subject = sprintf(__('PTB Submissions - New post (%s) has been added', 'ptb-submission'), $post_type_data->singular_label[$lang]);
                            $body = '<table>';
                            if (isset($current_user)) {
                                $body.='<tr><td>' . __('User', 'ptb-submission') . ':</td>';
                                $body.='<td><a href="' . get_edit_user_link($current_user->ID) . '">' . $current_user->user_login . '</a></td></tr>';
                            }
                            $body.='<tr><td>' . __('Post Title', 'ptb-submission') . ':</td><td><a href="' . get_edit_post_link($def_post_id) . '">' . $_post['post_title'] . '</a></td></tr>';
                            $body.='<tr><td>' . __('Post Status', 'ptb-submission') . ':</td><td>' . $_post['post_status'] . '</td></tr>';
                            $body.='</table>';
                            wp_mail($notify_email, $subject, $body, array('content-type: text/html'));
                        }
                        update_post_meta($def_post_id, 'ptb_submission_is_post', 1);
                    }
                    if ($this->checkpayment($data)) {
                        $payment_url = add_query_arg(array('action' => 'ptb_submission_payment', 'post_id' => $def_post_id), admin_url('admin-ajax.php'));
                        die(wp_json_encode(array('fee' => $payment_url)));
                    } else {
                        $r = $data['success'] == 'r';
                        $response = array('r' => $r);
                        if ($r) {
                            $response['success'] = esc_url_raw($data[$data['success']]);
                        } else {
                            $response['success'] = 1;
                            $response['form_id'] = $_POST['form_id'];
                            $response['nonce'] = wp_create_nonce('ptb_submission_success_' . $_POST['form_id']);
                        }
                        die(wp_json_encode($response));
                    }
                }
            }
        } else {
            $wp_query->set_404();
            status_header(404);
        }
        wp_die();
    }

    private function removefiles() {
        if (!empty(self::$files)) {
            foreach (self::$files as $f) {
                if (isset($f['file'])) {
                    @unlink($f['file']);
                } elseif (is_array($f)) {
                    foreach ($f as $f2) {
                        if (isset($f2['file'])) {
                            @unlink($f2['file']);
                        }
                    }
                }
            }
        }
    }

    private function checkpayment(array $data) {
        return (!$this->post || $this->post->post_status == 'draft') && isset($data['fee']) && $data['fee'] && $data['amount'] > 0;
    }

    private function set_post_taxonomies(array $taxonomies, $post_id) {
        $post_id = intval($post_id);
        foreach ($taxonomies as $tax => $val) {
            if (is_array($val)) {
                foreach ($val as &$v) {
                    $v = intval($v);
                }
            } else {
                $val = intval($val);
            }
            wp_set_post_terms($post_id, $val, $tax, false);
        }
    }
    
    private function set_post_meta(array $metaboxes, $post_id, array $post_data, $lng) {
        if (!empty($metaboxes)) {
            $post_id = intval($post_id);
            $metaboxes = apply_filters('ptb_submission_set_meta', $metaboxes, $post_id, $lng);
            foreach ($metaboxes as $_key => $m) {
                if (isset($m['type']) && has_filter('ptb_submission_metabox_save_' . $m['type'])) {
                    $m = apply_filters('ptb_submission_metabox_save_' . $m['type'], $m, $_key, $post_data, $post_id, $lng);
                } else {
                    if (isset($m['value'][$lng])) {
                        $m['value'] = $m['value'][$lng];
                    }
                }

                if (isset($m['type'])) {
                    unset($m['type']);
                }
                if (!isset($m['value'])) {
                    $m = array('value' => $m);
                }
                update_post_meta($post_id, 'ptb_' . $_key, $m['value']);
            }
        }
    }

    public static function check_multi_language(array &$data, $key, $type, $name, array $module = array()) {
        $error = array();
        $languages = PTB_Utils::get_all_languages();
        foreach ($languages as $code => $title) {
            if (isset($data[$key][$code])) {
                $val = $data[$key][$code];
                switch ($type) {
                    case 'textarea':
                    case 'excerpt':
                        $val = esc_textarea($val);
                        break;
                    case 'editor':
                        $val = isset($module['html']) ? wp_filter_kses($val) : wp_filter_nohtml_kses($val);
                        break;
                    default:
                        $val = sanitize_text_field($val);
                        break;
                }
            } else {
                $val = false;
            }
            if (!$val) {
                $error[$code] = $title['name'] ?
                        sprintf(__('Please fill %s(%s)', 'ptb-submission'), $name, $title['name']) :
                        sprintf(__('Please fill %s', 'ptb-submission'), $name);
            } else {
                $data[$key][$code] = $val;
            }
        }
        return empty($error) ? FALSE : $error;
    }

    public function validate($type, $post_type, array $args, array $module, array &$post_data, $metabox, $post_id, $lang) {
        $error = array();
        $multifields = array('textarea', 'editor', 'text', 'title', 'excerpt');
        $multifields = apply_filters('ptb_submission_multifields_validate', $multifields, $post_data);
        if ($metabox) {
            $key = $module['key'];
            $name = PTB_Utils::get_label($args['name']);
        } else {
            $key = $type;
            $name = PTB_Submissiion_Options::get_name($key);
        }
        if (isset($module['required']) && !in_array($type, array('image', 'thumbnail')) && (!isset($post_data[$key]) || !$post_data[$key])) {
            if (in_array($key, $multifields)) {
                $error[$lang] = sprintf(__('%s is required', 'ptb-submission'), $name);
            } else {
                $error = sprintf(__('%s is required', 'ptb-submission'), $name);
            }
            return $error;
        }
        if ($type == 'title') {
            $module['required'] = 1;
        }
        switch ($type) {
            case in_array($type, $multifields):
                if (($type != 'text') || ($type == 'text' && !$args['repeatable'])) {
                    $error = self::check_multi_language($post_data, $key, $type, $name, $module);
                } elseif ($type == 'text' && $args['repeatable']) {
                    $languages = PTB_Utils::get_all_languages();
                    foreach ($languages as $code => $lng) {
                        if (isset($post_data[$key][$code]) && !empty($post_data[$key][$code])) {
                            foreach ($post_data[$key][$code] as $p => &$v) {
                                $v = sanitize_text_field($v);
                                if (!$v) {
                                    unset($post_data[$key][$code][$p]);
                                }
                            }
                            if (empty($post_data[$key][$code])) {
                                $error[$code] = $lng['name'] ?
                                        sprintf(__('Please fill %s(%s) all options', 'ptb-submission'), $name, $lng['name']) :
                                        sprintf(__('Please fill %s all options', 'ptb-submission'), $name);
                                unset($post_data[$key][$code]);
                            }
                        }
                    }
                }
                if (!isset($module['required']) && $error) {
                    $error = false;
                }
                break;
            case 'taxonomies':
            case 'category':
                $options = PTB::get_option();
                $category = $type == 'category';
                $custom_post_taxes = $options->get_cpt_cmb_taxonomies($post_type);
                if ($category) {
                    $tax = $tax_key = 'category';
                    $data = isset($post_data[$tax_key]) ? $post_data[$tax_key] : false;
                } else {
                    $tax = $module['taxonomies'];
                    $tax_key = 'taxonomies';
                    $data = isset($post_data[$tax_key][$tax]) ? $post_data[$tax_key][$tax] : false;
                }
                if (!in_array($tax, $custom_post_taxes)) {
                    if ($category) {
                        unset($post_data[$tax_key]);
                    } else {
                        unset($post_data[$tax_key][$tax]);
                    }
                } else {
                    $multi = in_array($module['show_as'], array('multiselect', 'checkbox'));
                    if ($data) {
                        $taxes = $multi ? $data : array($data);
                        if (isset($module['allow']) && $module['allow'] && isset($post_data[$tax . '_add']) && $post_data[$tax . '_add']) {
                            $new_tax = json_decode(base64_decode($post_data[$tax . '_add']), true);
                            $add_taxes = array();
                            $default_lng = PTB_Utils::get_default_language_code();
                            if ($new_tax && isset($new_tax[$default_lng]) && is_array($new_tax[$default_lng])) {
                                foreach ($new_tax[$default_lng] as $tk => $t) {
                                    $tslug = sanitize_title($t);
                                    $index = array_search($tslug, $taxes);
                                    if ($index !== false) {
                                        $get_term = get_term_by('slug', $tslug, $tax);
                                        if (!$get_term) {
                                            $add_taxes[$tk] = array('value' => sanitize_text_field($t), 'slug' => $tslug);
                                        } else {
                                            $taxes[] = $get_term->term_id;
                                        }
                                        unset($taxes[$index]);
                                    }
                                }

                                if (!empty($add_taxes)) {
                                    global $sitepress;
                                    if (isset($sitepress)) {
                                        $languages = PTB_Utils::get_all_languages();
                                        unset($languages[$default_lng]);
                                        if (!empty($languages)) {
                                            foreach ($languages as $code => $ln) {
                                                foreach ($new_tax[$code] as $tk => $t) {
                                                    if (isset($add_taxes[$tk])) {
                                                        $tslug = sanitize_title($t);
                                                        $get_term = get_term_by('slug', $tslug, $tax);
                                                        if ($get_term) {
                                                            $taxes[] = $sitepress->get_original_element_id($get_term->term_id, 'tax_' . $tax);
                                                            unset($add_taxes[$tk]);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                if (!empty($add_taxes)) {
                                    foreach ($add_taxes as $tk => $new_term) {
                                        $term_id = wp_insert_term($new_term['value'], $tax, array('slug' => $new_term['slug']));
                                        if (is_array($term_id) && isset($term_id['term_id'])) {
                                            $taxes[] = $term_id['term_id'];
                                            if (isset($sitepress)) {
                                                $trid = $sitepress->get_element_trid($term_id['term_id'], 'tax_' . $tax);
                                                foreach ($languages as $code => $ln) {
                                                    if (isset($new_tax[$code][$tk])) {
                                                        $tslug = sanitize_title($new_tax[$code][$tk]);
                                                        $wpml = wp_insert_term($new_tax[$code][$tk], $tax, array('slug' => $tslug));
                                                        if (is_array($wpml) && isset($wpml['term_id'])) {
                                                            $sitepress->set_element_language_details($wpml['term_id'], 'tax_' . $tax, $trid, $code, $default_lng);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                if ($category) {
                                    $post_data[$tax_key] = $taxes;
                                } else {
                                    $post_data[$tax_key][$tax] = $taxes;
                                }
                            }
                        }
                        foreach ($taxes as $k => $t) {
                            if (!is_numeric($t) || is_array($t) || !term_exists(intval($t), $tax)) {
                                if ($multi) {
                                    if ($category) {
                                        unset($post_data[$tax_key][$k]);
                                    } else {
                                        unset($post_data[$tax_key][$tax][$k]);
                                    }
                                } else {
                                    if ($category) {
                                        unset($post_data[$tax_key]);
                                    } else {
                                        unset($post_data[$tax_key][$tax]);
                                    }
                                }
                            }
                        }
                    }
                    if (isset($module['required'])) {
                        if ($category) {
                            if (!$data || empty($post_data[$tax_key])) {
                                $name = PTB_Submissiion_Options::get_name($tax, $multi);
                                $error = sprintf(__('%s is required', 'ptb-submission'), $name);
                            }
                        } elseif (!$data || empty($post_data[$tax_key][$tax])) {
                            $tax_ = $options->get_custom_taxonomy($tax);
                            $name = $multi ? PTB_Utils::get_label($tax_->plural_label) : PTB_Utils::get_label($tax_->singular_label);
                            $error[$tax] = sprintf(__('%s is required', 'ptb-submission'), $name);
                        }
                    }
                }
                break;
            case 'post_tag':
                $error = self::check_multi_language($post_data, $key, $type, $name, $module);
                if (!isset($module['required']) && $error) {
                    $error = false;
                }
                break;
            case 'checkbox':
            case 'radio_button':
            case 'select':
                $arr = $type == 'checkbox' ? true : ($type == 'radio_button' ? FALSE : ($type == 'select' && $args['multipleSelects']));
                $vals = isset($post_data[$key]) ? ($arr ? $post_data[$key] : array($post_data[$key])) : array();
                $options_id = array();
                foreach ($args['options'] as $opt) {
                    $options_id[] = $opt['id'];
                }
                if(empty($post_data[$key])){
                    $post_data[$key] = array();
                }
                foreach ($vals as $k => $v) {
                    if (!in_array($v, $options_id)) {
                        if ($arr) {
                            unset($post_data[$key][$k]);
                        } else {
                            unset($post_data[$key]);
                        }
                    }
                }
                if (isset($module['required']) && empty($post_data[$key])) {
                    $error = sprintf(__('%s is required', 'ptb-submission'), PTB_Utils::get_label($args['name']));
                    break;
                }
                if (!empty($post_data[$key]) && $type == 'select' && !$args['multipleSelects'] && is_array($post_data[$key])) {
                    $post_data[$key] = end($post_data[$key]);
                }
                break;
            case 'email':
                if (isset($module['required']) && (!trim($post_data[$key]) || !is_email($post_data[$key]))) {
                    $error = sprintf(__("%s email isn't valid", 'ptb-submission'), $post_data[$key]);
                } elseif (trim($post_data[$key]) && !is_email($post_data[$key])) {
                    $error = sprintf(__("%s email isn't valid", 'ptb-submission'), $post_data[$key]);
                } else {
                    $post_data[$key] = sanitize_email($post_data[$key]);
                }
                break;
            case 'image':
            case 'thumbnail':

                if (isset($_FILES[$key])) {
                    if (!isset($module['extensions'])) {
                        $module['extensions'] = array();
                    }
                    $allow = PTB_Submissiion_Options::get_allow_ext($module['extensions']);
                    $check = PTB_Submissiion_Options::validate_file($_FILES[$key], $allow, isset($module['size']) ? $module['size'] : null);
                    if (isset($check['error'])) {
                        $error = $check['error'];
                    } else {
                        self::$files[$key] = $check['file'];
                        if ($type != 'thumbnail') {
                            $post_data[$key] = array(1 => $check['file']['url']);
                        }
                    }
                } else {
                    if (isset($module['required'])) {
                        if ($post_id) {
                            $check = $type != 'thumbnail' ? get_post_meta($post_id, 'ptb_' . $key, TRUE) : get_the_post_thumbnail($post_id);
                            if (!$check) {
                                $error = $name . ' ' . __('is required');
                            } elseif (isset($post_data[$key])) {
                                unset($post_data[$key]);
                            }
                        } else {
                            $error = $name . ' ' . __('is required');
                        }
                    } elseif (!isset($post_data[$key])) {
                        $post_data[$key] = $type == 'thumbnail' ? '1' : array();
                    } else {
                        unset($post_data[$key]);
                    }
                }
                break;
            case 'number':
                $min = isset($module['min']) && $module['min'] ? floatval($module['min']) : false;
                $max = isset($module['max']) && $module['max'] ? floatval($module['max']) : false;
                $range = isset($args['range']) && $args['range'];
                $keys = $range ? array('from', 'to') : array(1);
                $required = isset($module['required']);

                foreach ($keys as $k) {
                    if ($range) {
                        if ($required && (!isset($post_data[$key][$k]) || !$post_data[$key][$k])) {
                            $error = sprintf(__('%s is required', 'ptb-submission'), $name);
                        } else {
                            $v = $post_data[$key][$k] = floatval($post_data[$key][$k]);
                        }
                    } else {
                        $v = $post_data[$key] = floatval($post_data[$key]);
                    }
                    if (!$error) {
                        if ($min && $max && ($min > $v || $v > $max)) {
                            $error = sprintf(__('%1$s must be between %2$s and %3$s', 'ptb-submission'), $v, $min, $max);
                        } elseif ($min && $min > $v) {
                            $error = sprintf(__('%1$s must be greater than %2$s', 'ptb-submission'), $v, $min);
                        } elseif ($max && $max < $v) {
                            $error = sprintf(__('%1$s must be less than %2$s', 'ptb-submission'), $v, $max);
                        }
                    }
                    if ($error) {
                        break;
                    }
                }
                if (!$error && $range && $post_data[$key]['from'] >= $post_data[$key]['to']) {
                    $error = sprintf(__('%1$s must be greater than %2$s', 'ptb-submission'), $post_data[$key]['to'], $post_data[$key]['from']);
                }
                break;
            case 'user_email':
                $post_data[$key] = sanitize_email($post_data[$key]);
                if (!is_email($post_data[$key])) {
                    $error = sprintf(__("%s email isn't valid", 'ptb-submission'), $post_data[$key]);
                } elseif (email_exists($post_data[$key])) {
                    $error = sprintf(__("%s email already is used", 'ptb-submission'), $post_data[$key]);
                }
                break;
            case 'user_name':
                $post_data[$key] = sanitize_user($post_data[$key]);
                if (username_exists($post_data[$key])) {
                    $error = sprintf(__("%s username already is used", 'ptb-submission'), $post_data[$key]);
                }
                break;
            case 'user_password':
                if (!trim($post_data[$key])) {
                    $error = __("Password is required", 'ptb-submission');
                } elseif (!isset($post_data['user_confirm_password']) || $post_data[$key] != $post_data['user_confirm_password']) {
                    $error = __("Password doesn't match Confirm Password", 'ptb-submission');
                }
                break;
        }
        return empty($error) ? false : $error;
    }

    public function get_terms() {
        if (isset($_POST['post_type']) && !empty($_POST['slug']) && isset($_POST['term']) && strlen($_POST['term']) > 1) {
            $post_type = sanitize_text_field($_POST['post_type']);
            $ptb_options = PTB::get_option();
            $cpt = $ptb_options->get_custom_post_type($post_type);
            if ($cpt) {
                $slug = sanitize_text_field($_POST['slug']);
                $post_taxes = $ptb_options->get_cpt_cmb_taxonomies($post_type);
                if (in_array($slug, $post_taxes)) {
                    $term = sanitize_text_field($_POST['term']);
                    $terms = get_terms($slug, array('hide_empty' => false, 'name__like' => $term, 'orderby' => 'name', 'order' => 'ASC', 'fields' => 'id=>name'));
                    $response = array();
                    foreach ($terms as $k => $t) {
                        $response[] = array('value' => $k, 'label' => $t);
                    }
                    die(wp_json_encode($response));
                }
            }
        }
        wp_die();
    }
    
    public function get_tag_terms(){
        if (isset($_POST['post_type']) && !empty($_POST['slug']) && isset($_POST['term']) && strlen($_POST['term']) > 1) {
            $post_type = sanitize_text_field($_POST['post_type']);
            $ptb_options = PTB::get_option();
            $cpt = $ptb_options->get_custom_post_type($post_type);
            if ($cpt) {
                $term = sanitize_text_field($_POST['term']);
                global $sitepress;
                if(isset($sitepress)){
                    $lang = $_POST['slug'];
                    $sitepress->switch_lang($lang, true);
                }
                
                add_filter( 'terms_clauses', array($this,'get_terms_fields'), 10, 3 );
                
                $terms = get_tags(array(
                    'name__like'=>$term,
                    'get'=>'all',
                    'orderby'=>'name',
                    'order'=>'ASC',
                    'number'=>15
                ));
                $response = array();
                if(!empty($terms)){
                    foreach ($terms as $t) {
                        $response[] = array('value' => $t->term_id, 'label' => $t->name);
                    }
                }
                die(wp_json_encode($response));
                
            }
        }
        wp_die();
        
    }
    
    function get_terms_fields( $clauses, $taxonomies, $args ) {
        if(!empty($args['name__like'])){
            global $wpdb;
            $term = $wpdb->esc_like( $args['name__like'] );
            if ( ! isset( $clauses['where'] ) ){
                $clauses['where'] = '1=1';
            }

            $clauses['where'] .= $wpdb->prepare( " AND t.name LIKE %s", "$term%" );
        }
        return $clauses;
    }


    public function remove_post() {

        if (isset($_GET['id']) && is_user_logged_in() && isset($_GET['nonce']) && check_ajax_referer($this->plugin_name . '-remove-post', 'nonce')) {
            $id = intval($_GET['id']);
            $posts = get_post($id);
            $options = PTB_Submission::get_option();
            if ($posts && $posts->post_author == get_current_user_id() && $options->get_submission_template($posts->post_type) && wp_delete_post($id, true)) {
                die('1');
            }
        }
        wp_die();
    }

    public function posts_filter() {

        if (is_user_logged_in() && isset($_POST['submission']) && isset($_POST['nonce']) && check_ajax_referer($this->plugin_name . '-filter-posts', 'nonce')) {
            $ptb_options = PTB::get_option();
            $post_types = $ptb_options->get_custom_post_types();
            $submission_options = PTB_Submission::get_option();
            $submission_types = array();
            foreach ($post_types as $post_type) {
                $submission_data = $submission_options->get_submission_template($post_type->slug);
                if ($submission_data) {
                    $submission_types[] = $post_type->slug;
                }
            }
            if (!empty($submission_types)) {

                $form_fields = array(
                    'post_status' => 'post_status',
                    'post_title' => 's',
                    'from' => 'after',
                    'to' => 'before'
                );
                $post_data = sanitize_post($_POST['submission'], 'db');
                $args = array(
                    'author' => get_current_user_id(),
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'post_status' => 'publish,pending,draft',
                    'post_type' => $submission_types,
                    'pagination' => true,
                    'posts_per_page' => get_option('posts_per_page'),
                    'paged' => ( get_query_var('paged') ? get_query_var('paged') : 1 )
                );
                foreach ($form_fields as $fkey => $wp_key) {
                    if (isset($post_data[$fkey]) && ($post_data[$fkey] = sanitize_text_field($post_data[$fkey]))) {
                        $args[$wp_key] = $post_data[$fkey];
                    }
                }
                if (isset($args['post_status']) && !in_array($args['post_status'], array('publish', 'pending', 'draft'))) {
                    $args['post_status'] = 'publish,pending,draft';
                }
                if (isset($args['after']) || isset($args['before'])) {
                    $args['date_query'] = array(
                        'compare' => 'BETWEEN',
                        'inclusive' => true,
                        'column' => 'post_date'
                    );
                    if (isset($args['after'])) {
                        $args['date_query']['after'] = $args['after'];
                        unset($args['after']);
                    }
                    if (isset($args['before'])) {
                        $args['date_query']['before'] = $args['before'];
                        unset($args['before']);
                    }
                }
                if (isset($post_data['post_type']) && in_array($post_data['post_type'], $submission_types)) {
                    $args['post_type'] = sanitize_text_field($post_data['post_type']);
                }
                if (isset($_POST['paged']) && $_POST['paged'] >= 1) {
                    $args['paged'] = intval($_POST['paged']);
                }
                add_filter('posts_search', array($this, 'pre_search'), 500, 2);
                $this->get_user_posts($args['paged'], $args);
            }
        }
        wp_die();
    }

    public function pre_search($search, &$wp_query) {
        global $wpdb;
        if (empty($search)) {
            return $search; // skip processing - no search term in query
        }
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = $searchand = '';
        foreach ((array) $q['search_terms'] as $term) {
            $term = esc_sql($wpdb->esc_like($term));
            $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
            $searchand = ' AND ';
        }
        if (!empty($search)) {
            $search = " AND ({$search}) ";
            if (!is_user_logged_in())
                $search .= " AND ($wpdb->posts.post_password = '') ";
        }
        return $search;
    }

    public function payment_form() {

        if (isset($_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
            $post = get_post($post_id);
            if ($post && $post->post_status == 'draft') {
                $options = PTB_Submission::get_option();
                $post_type = $post->post_type;
                $template = $options->get_submission_template($post_type);
                if ($template && isset($template['frontend']['data'])) {
                    $data = $template['frontend']['data'];
                    if ($this->checkpayment($data)) {
                        $handler = apply_filters('ptb_submission_payment_method', 'PayPal', $post_id, $post_type, $data);
                        $payment = new PTB_Submission_Payment($handler, $this->plugin_name, $this->version);
                        $item = $payment->get();
                        $page = $_SERVER['HTTP_REFERER'];
                        $success_page = $data['success'] == 'r' ? esc_url($data[$data['success']]) : apply_filters('ptb_submission_payment_success_url', add_query_arg(array('success' => 1), $page));
                        $cancel_page = apply_filters('ptb_submission_payment_fail_url', add_query_arg(array('fail' => 1), $page));
                        $item->form($post_type, $post_id, $data, $success_page, $cancel_page);
                    }
                }
            }
        }
        wp_die();
    }

    public function display_edit_profile() {
        $user = wp_get_current_user();
        if (!$user) {
            wp_die();
        }
        ?>
        <form id="ptb-submission-edit-form" class="ptb-submission-module-form" action="<?php echo admin_url('admin-ajax.php') ?>" method="post">
            <input type="hidden" name="action" value="ptb_submission_profile" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce($this->plugin_name . '-edit-profile'); ?>" />
            <table class="ptb-submission-edit-profile">
                <tr>
                    <td><label for="ptb_submission_username"><?php _e('Username', 'ptb-submission') ?>:</label></td>
                    <td><input required="required" type="text" id="ptb_submission_username" name="submission[user_login]" value="<?php echo $user->user_login ?>" /></td>
                </tr>
                <tr>
                    <td><label for="ptb_submission_email"><?php _e('Email', 'ptb-submission') ?>:</label></td>
                    <td><input required="required" type="email" id="ptb_submission_email" name="submission[user_email]" value="<?php echo $user->user_email ?>" /></td>
                </tr>
                <tr>
                    <td><label for="ptb_submission_password"><?php _e('Password', 'ptb-submission') ?>:</label></td>
                    <td><input type="password" id="ptb_submission_password" name="submission[user_pass]" value="" /></td>
                </tr>
                <tr>
                    <td><label for="ptb_submission_confirm_password"><?php _e('Confirm Password', 'ptb-submission') ?>:</label></td>
                    <td><input type="password" id="ptb_submission_confirm_password" name="submission[cpassword]" value="" /></td>
                </tr>
            </table>
            <input class="ptb-submission-submit-btn" type="submit" value="<?php _e('Edit', 'ptb-submission') ?>" />
            <div class="ptb-submission-success-text"></div>
            <div class="ptb-submission-loader"><div></div></div>
        </form>
        <?php
    }

    public function edit_profile() {
        if (isset($_POST['submission']) && isset($_POST['nonce']) && check_ajax_referer($this->plugin_name . '-edit-profile', 'nonce') && is_user_logged_in()) {
            $post_data = sanitize_post($_POST['submission'], 'db');
            $user_id = get_current_user_id();
            $errors = array();
            if (!isset($post_data['user_login']) || !$post_data['user_login']) {
                $errors['user_login'] = __('Username is required', 'ptb-submission');
            } else {
                $post_data['user_login'] = sanitize_user($post_data['user_login']);
                $new_user = get_user_by('login', $post_data['user_login']);
                if ($new_user && $new_user->ID != $user_id) {
                    $errors['user_login'] = sprintf(__('Username "%s" already is used', 'ptb-submission'), $post_data['user_login']);
                }
            }
            if (!isset($post_data['user_email']) || !$post_data['user_email']) {
                $errors['user_email'] = __('Email is required', 'ptb-submission');
            } else {
                $post_data['user_email'] = sanitize_email($post_data['user_email']);
                if (!is_email($post_data['user_email'])) {
                    $errors['user_email'] = sprintf(__("Email %s isn`t valid", 'ptb-submission'), $post_data['user_email']);
                } else {
                    $new_user = get_user_by('email', $post_data['user_email']);
                    if ($new_user && $new_user->ID != $user_id) {
                        $errors['user_email'] = sprintf(__('Email %s already is used', 'ptb-submission'), $post_data['user_email']);
                    }
                }
            }
            if (isset($post_data['user_pass']) && $post_data['user_pass'] && isset($post_data['cpassword']) && $post_data['cpassword'] != $post_data['user_pass']) {
                $errors['user_pass'] = __("Password doesn't match Confirm Password", 'ptb-submission');
            } else {
                $post_data['user_pass'] = false;
            }
            if (!empty($errors)) {
                die(wp_json_encode($errors));
            }
            $user_data = array();
            $user_data['ID'] = $user_id;
            $user_data['user_email'] = $post_data['user_email'];
            $user_data['user_login'] = $post_data['user_login'];
            if ($post_data['user_pass']) {
                $user_data['user_pass'] = $post_data['user_pass'];
            }
            $update = wp_update_user($user_data);
            if (!is_wp_error($update)) {
                die(wp_json_encode(array('success' => __('Your data is updated', 'ptb-submission'))));
            } else {
                die(wp_json_encode(array($update->get_error_message())));
            }
        }
        wp_die();
    }

    public function page_title($title) {
        if (is_page() && in_the_loop()) {
            global $post;
            if (is_user_logged_in() && has_shortcode($post->post_content, 'ptb_submission_account')) {
                remove_filter('the_title', array($this, 'page_title'));
                if (isset($_GET['ptb_action']) && $_GET['ptb_action'] == 'profile') {
                    $title = __('Edit Profile', 'ptb-submission');
                } elseif (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                    $title.='<p class="ptb_edit_profile"><a href="' . add_query_arg(array('ptb_action' => 'profile'), get_the_permalink()) . '">' . __('Edit Profile', 'ptb-submission') . '</a></p>';
                } else {
                    $title = __('Edit Submission', 'ptb-submission');
                }
            }
            if (self::$ptb_author) {
                return '';
            }
        }
        return $title;
    }

    public function wp_title($title) {
        if (isset($_GET['ptb_author']) && $_GET['ptb_author'] && is_page() && strpos(trim(get_permalink(), '/'), apply_filters('ptb_submission_author_page', PTB_SLUG_AUTHOR))) {
            $author = new WP_User_Query(array(
                'search' => esc_attr($_GET['ptb_author']),
                'search_columns' => array('user_nicename'),
                'role' => 'ptb',
                'number' => 1
                    )
            );
            $author = $author->get_results();
            if (!empty($author)) {
                $author = current($author);
                $title = $author->user_nicename;
                self::$ptb_author = $author;
                $plugin_url = plugin_dir_url(__FILE__);
                wp_enqueue_style($this->plugin_name . '-account', $plugin_url . 'css/ptb-submission-author.css', array(), $this->version, 'all');
                add_filter('body_class', array($this, 'author_body'));
                add_filter('the_content', array($this, 'author_content'));
            }
        }
        return $title;
    }

    public function payment_result() {
        if (!empty($_POST)) {
            $handler = apply_filters('ptb_submission_payment_method_handler', 'PayPal');
            $payment = new PTB_Submission_Payment($handler, $this->plugin_name, $this->version);
            $item = $payment->get();
            $post_id = $item->result($_POST);
            do_action('ptb_submission_payment_result', $post_id, $handler, $_POST, $item);
            if ($post_id) {
                $this->set_paid_post($post_id, $_POST, $handler);
            }
        }
    }

    public function set_paid_post($post_id, array $post_data, $handler) {
        if ($handler == 'PayPal') {
            update_post_meta($post_id, 'ptb_submission_payment_data', array('price' => $post_data['mc_gross'], 'currency' => $post_data['currency']));
        }
    }

    public function author_url($url, $id, $nick) {
        global $post;
        if (isset($post)) {
            $options = PTB_Submission::get_option();
            if ($options->get_submission_template($post->post_type)) {
                $slug_author = apply_filters('ptb_submission_author_page', PTB_SLUG_AUTHOR);
                return esc_url(add_query_arg(array('ptb_author' => $nick), '/' . $slug_author . '/'));
            }
        }
        return $url;
    }

    public function author_body($classes) {
        $classes[] = 'ptb_author_page';
        return $classes;
    }

    public function author_content($content) {
        remove_filter('the_content', array($this, 'author_content'));
        $name = self::$ptb_author->first_name . ' ' . self::$ptb_author->last_name;
        $ptb_options = PTB::get_option();
        $post_types = $ptb_options->get_custom_post_types();

        $submission_options = PTB_Submission::get_option();
        $author_posts = $submission_types = array();
        foreach ($post_types as $post_type) {
            if ($submission_options->get_submission_template($post_type->slug)) {
                $submission_types[] = $post_type->slug;
            }
        }
        ob_start();
        ?>
        <div class="ptb-author-bio">
            <p class="ptb-author-avatar"><?php echo get_avatar(self::$ptb_author->user_email, 80, false, $name) ?></p>
            <div class="ptb-author-profile">
                <h2 class="ptb-author-name"><?php echo $name; ?></h2>
                <?php if (self::$ptb_author->user_description): ?>
                    <div class="ptb-author-description">
                        <?php echo self::$ptb_author->user_description; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <h2 class="ptb-author-posts-by"><?php _e('Posts by', 'ptb-submission'); ?> <?php echo self::$ptb_author->first_name; ?> <?php echo self::$ptb_author->last_name; ?>:</h2>
        <?php if (!empty($submission_types)): ?>
            <?php ?>
            <?php echo do_shortcode('[ptb posts_per_page="2" pagination="1" style="grid3" author="' . self::$ptb_author->ID . '" type="' . implode(',', $submission_types) . '"]') ?>
        <?php endif; ?>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function add_tax() {
        if (isset($_GET['slug']) && $_GET['slug'] && isset($_GET['multi']) && isset($_GET['label'])) {
            $slug = sanitize_key($_GET['slug']);
            $multi = $_GET['multi'];
            if ($slug != 'category') {
                $ptb_options = PTB::get_option();
                if (!$ptb_options->has_custom_taxonomy($slug)) {
                    wp_die();
                }
            }
            $label = base64_decode(sanitize_text_field($_GET['label']));
            $languages = PTB_Utils::get_all_languages();
            ?>
            <form action="<?php echo admin_url('admin-ajax.php?action=ptb_submission_add_temp_tax') ?>" method="post" class="add_temp_tax">
                <div class="ptb-submission-loader"><div></div></div>
                <div class="ptb_module ptb_lightbox_add_tax">
                    <h3><?php printf(__('Add New %s', 'ptb-ubmission'), $label) ?></h3>
                    <?php if ($multi): ?>
                        <div class="ptb_back_active_module_input ptb-submission-multi-text">
                            <ul>
                                <li class="ptb-submission-text-option">
                                    <i title="<?php _e('Sort', 'ptb-submission') ?>" class="fa fa-sort ptb-submission-option-sort"></i>
                                    <?php PTB_CMB_Base::module_language_tabs('tax', array(), $languages, $slug, 'text', false, true); ?>
                                    <i title="<?php _e('Remove', 'ptb-submission') ?>" class="ptb-submission-remove fa fa-times-circle"></i>
                                </li>
                            </ul>
                            <div class=" ptb-submission-option-add">
                                <i class="fa fa-plus-circle"></i>
                                <?php _e('Add new', 'ptb-submission'); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="ptb_back_active_module_input">
                            <?php PTB_CMB_Base::module_language_tabs('tax', array(), $languages, $slug, 'text', false, true); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <input type="submit" value="<?php _e('Add', 'ptb-submission') ?>" />
            </form>
            <?php
        }
        wp_die();
    }

    public function add_temp_tax() {
        if (isset($_POST['tax']) && !empty($_POST['tax'])) {
            $tax = sanitize_post($_POST['tax']);
            $slug = key($tax);
            if ($slug != 'category') {
                $ptb_options = PTB::get_option();
                if (!$ptb_options->has_custom_taxonomy($slug)) {
                    wp_die();
                }
            }
            global $sitepress;
            $terms = array('slug' => $slug);
            $languages = PTB_Utils::get_all_languages();
            $default_lng = PTB_Utils::get_default_language_code();
            foreach ($languages as $code => $lng) {
                if (isset($tax[$slug][$code]) && is_array($tax[$slug][$code])) {
                    foreach ($tax[$slug][$code] as $k => $term) {
                        $term = sanitize_text_field($term);
                        if ($term) {
                            $tslug = sanitize_title($term);
                            $get_term = get_term_by('slug', $tslug, $slug);
                            if (!$get_term || (isset($sitepress) && $default_lng != $code)) {
                                $terms['add'][$code][] = array('value' => sanitize_text_field($term), 'slug' => $tslug);
                            } else {
                                $terms['exists'][$get_term->term_id] = $term;
                            }
                        }
                    }
                }
            }

            if (isset($terms['add'])) {

                $terms['lng'] = PTB_Utils::get_current_language_code();
            }
            echo wp_json_encode($terms);
        }
        wp_die();
    }

}
