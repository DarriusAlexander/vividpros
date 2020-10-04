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
class PTB_Search_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private static $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private static $version;
    private static $resp = '';
    private static $data = array();
    private static $slug = false;
    private static $permalink = false;
    private static $ptb_page = PTB_SEARCH_SLUG;
    private static $cache_enabled = false;
    private static $is_page = false;
    private static $submit_btn = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        self::$plugin_name = $plugin_name;
        self::$version = $version;

        self::$cache_enabled = apply_filters('ptb_search_enable_cache', self::$cache_enabled);
        self::$ptb_page = apply_filters('ptb_search_page_slug', self::$ptb_page);
        self::$ptb_page = trim(self::$ptb_page, '/');
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
                add_action('wp_head', array($this, 'get_post'));
                add_action('wp_enqueue_scripts', array($this, 'public_enqueue_scripts'));
                add_filter( 'widget_text', array($this,'get_ptb_search_shortcode'),10,1);
        } else {
                add_action('template_redirect', array($this, 'get_post'));
                add_action('wp_ajax_nopriv_ptb_search_set_values', array($this, 'set_values'));
                add_action('wp_ajax_ptb_search_set_values', array($this, 'set_values'));
                add_action('wp_ajax_nopriv_ptb_search_autocomplete', array($this, 'get_terms'));
                add_action('wp_ajax_ptb_search_autocomplete', array($this, 'get_terms'));
                add_action('wp_ajax_nopriv_ptb_ajax_search', array($this, 'get_post'));
                add_action('wp_ajax_ptb_ajax_search', array($this, 'get_post'));
        }
        add_shortcode('ptb_search', array($this, 'ptb_search'));
		
    }

    public function public_enqueue_scripts() {

        $plugin_url = plugin_dir_url(__FILE__);
        global $wp_scripts;
        $translation_ = array(
            'url' => $plugin_url,
            'ver' => self::$version
        );
        wp_register_style('themify-icons', dirname( plugin_dir_url(dirname(__FILE__))) . '/themify-ptb/admin/themify-icons/themify-icons.css', array(), self::$version, 'all');

        wp_register_script(self::$plugin_name . '-date', $plugin_url . 'js/jquery-ui-timepicker.js', array('jquery-ui-datepicker',self::$plugin_name), self::$version, false);
        wp_register_style(self::$plugin_name, $plugin_url . 'css/ptb-search.css', array(), self::$version, 'all');
        wp_register_script(self::$plugin_name, $plugin_url . 'js/ptb-search.js', array('ptb'), self::$version, true);
        wp_register_style(self::$plugin_name . '-select', $plugin_url . 'css/chosen.css', array('themify-icons'), self::$version, 'all');
        wp_register_script(self::$plugin_name . '-select', $plugin_url . 'js/chosen.jquery.min.js', array(), self::$version, true);
        
        wp_localize_script(self::$plugin_name, 'ptb_search', $translation_);
        $ui = $wp_scripts->query('jquery-ui-core');
        wp_register_style(self::$plugin_name . 'ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css', false, self::$version, false);
        self::$is_page = is_page();
    }
    
    public function get_ptb_search_shortcode($text){
        if($text && has_shortcode($text, 'ptb_search')){
           $text = PTB_CMB_Base::format_text($text);
        }
        return $text;
    }

    public function ptb_search($atts) {
        if (!isset($atts['form'])) {
            return;
        }
        $ptb_options = PTB::get_option();
        if (!isset($ptb_options->option_post_type_templates[$atts['form']]) || !isset($ptb_options->option_post_type_templates[$atts['form']]['search'])) {
            return;
        }
        $template = $ptb_options->option_post_type_templates[$atts['form']];
        if (!isset($template['search']['layout']) || !$template['search']['layout']) {
            return;
        }
        $languages = PTB_Utils::get_all_languages();
        $lang = PTB_Utils::get_current_language_code();
        $count = count($template['search']['layout']) - 1;
        $post_type = $template['post_type'];
        if (!$ptb_options->has_custom_post_type($post_type)) {
            return;
        }
        $is_submit = self::$permalink !== self::$ptb_page;
        $cmb_options = $post_support = $post_taxonomies = array();
        $ptb_options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
        $cmb_options['has'] = array('type' => 'has');
        $cmb_options['button'] = array('type' => 'button');
        $post_support[] = 'has';
        $post_support[] = 'button';
        $cmb_options = apply_filters('ptb_search_render', $cmb_options, $post_support);
        $form_keys = array();
        if($is_submit){
            $page = get_page_by_path( self::$ptb_page );
            $page = !empty($page)?get_the_permalink($page):get_site_url() . '/' . self::$ptb_page . '/';
        }
        ob_start();
        ?>
        <form method="get"  class="ptb-search-form ptb-search-<?php echo $atts['form'] ?><?php if ($is_submit): ?> ptb-search-form-submit<?php endif; ?>" action="<?php echo!$is_submit ? admin_url('admin-ajax.php?action=ptb_ajax_search') : $page?>">
            <input type="hidden" class="ptb-search-post-type" value="<?php echo $post_type ?>" />
            <input type="hidden" name="f" value="<?php echo $atts['form'] ?>" />
            <input type="hidden" name="ptb-search" value="1" />
            <?php if (!$is_submit): ?>
                <input type="hidden" name="ptb_page" value="1" />
            <?php endif; ?>
            <?php
            foreach ($template['search']['layout'] as $k => $row) {

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
                                        if (isset($cmb_options[$meta_key]) && ($module['type'] !== 'has' || (isset($module['has_field']) && $module['type'] === 'has' && in_array($module['has_field'], $post_support)))):
                                            $args = $cmb_options[$meta_key];
                                            $type = $module['type'];
                                            $args['key'] = $meta_key;
                                            $field = in_array($type, $post_support);
                                            $id = 'ptb_' . $atts['form'] . '_';
                                            $label = isset($module['label']) && $module['label'] ? PTB_Utils::get_label($module['label']) : false;
                                            $m = $module;
                                            unset($m['label']);
                                            if ($type != 'taxonomies') {
                                                $form_keys[$atts['form']][$meta_key] = $m;
                                                $id.=$meta_key;
                                                if ($type === 'has') {
                                                    $id.='_' . $module['has_field'];
                                                }
                                            } else {
                                                $id.= $meta_key . '_' . $module['taxonomy'];
                                            }
                                            if (!$label && $module['type']!=='button') {
                                                if ($type != 'taxonomies') {

                                                    $multy = isset($module['show_as']) ? PTB_Search_Options::is_multy($module['show_as']) : false;
                                                    $label = !$field ? PTB_Utils::get_label($args['name']) : PTB_Search_Options::get_name($type, $multy);
                                                    if ($type === 'has') {
                                                        $label = $label . ' ' . PTB_Search_Options::get_name($module['has_field']);
                                                    }
                                                } else {
                                                    $tax_ = $ptb_options->get_custom_taxonomy($module['taxonomy']);

                                                    if (!$tax_) {
                                                        continue;
                                                    }
                                                    $label = PTB_Search_Options::is_multy($module['show_as']) ? PTB_Utils::get_label($tax_->plural_label) : PTB_Utils::get_label($tax_->singular_label);
                                                }
                                            }
                                            ?>
                                            <div  class="ptb_search_module ptb_search_<?php echo $type ?><?php if (!$field): ?> ptb_search_<?php echo $meta_key ?><?php endif; ?>">
                                                <?php if($label):?>
                                                    <div class="ptb_search_label">
                                                        <label for="<?php echo $id ?>"><?php echo $label; ?></label>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (has_action('ptb_search_' . $type)): ?>
                                                    <?php $value = isset(self::$data[$post_type][$meta_key]) && self::$data[$post_type][$meta_key] ? self::$data[$post_type][$meta_key] : false; ?>
                                                    <?php do_action('ptb_search_' . $type, $post_type, $id, $args, $module, $value, $label, $lang, $languages); ?>
                                                <?php else: ?>
                                                    <?php $this->render($type, $post_type, $id, $args, $module, $label, $lang, $languages, $field); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php $i ++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php } ?>
            <input type="hidden" value="<?php echo base64_encode(json_encode($form_keys)); ?>" class="ptb_search_keys" />
            <?php if(!self::$submit_btn):?>
                 <input type="submit" value="<?php  _e('Search','ptb-search') ?>" />
            <?php else:?>
                 <?php  self::$submit_btn = false;?>
            <?php endif;?>
        </form>
        <?php
        $result = ob_get_contents();
        ob_end_clean();
        if (!wp_script_is(self::$plugin_name)) {
            wp_enqueue_script(self::$plugin_name);
        }
        if(!wp_style_is(self::$plugin_name)){
            wp_enqueue_style(self::$plugin_name);
        }
        return $result;
    }

    public function render($type, $post_type, $id, array $args, array $module, $label, $lang, array $languages, $field) {
        $name = isset($args['name']) ? PTB_Utils::get_label($args['name']) : PTB_Search_Options::get_name($type);
        $meta_key = $args['key'];
        $name = $name ? sanitize_title($name) : $meta_key;
        $value = isset(self::$data[$post_type][$meta_key]) && self::$data[$post_type][$meta_key] ? self::$data[$post_type][$meta_key] : false;
        switch ($type) {
            case 'button':
                $style = false;
                $class = array();
                if (isset($module['custom_color']) && $module['custom_color']) {
                    $style= 'background-color:' . $module['custom_color'] . ' !important;';
                } elseif (isset($module['color'])) {
                    $class[] = $module['color'];
                }
                if (isset($module['aligmnet'])) {
                    $class[] = 'ptb-search-align-'.$module['aligmnet'];
                }
                $label = PTB_Utils::get_label($module['text']);
                if(!$label){
                    $label = __('Search','ptb-search');
                }
                self::$submit_btn = true;
                ?>
                    <input <?php if ($style): ?>style="<?php echo $style ?>"<?php endif; ?> class="shortcode ptb_link_button <?php echo implode(' ',$class)?>" type="submit" value="<?php echo $label ?>" />
                <?php
                break;
            case 'date':
            case 'author':
                self::show_as($type, $post_type, $id, $name, $value, $meta_key, $label);
                break;
            case 'text':
            case 'title':
            case 'radio_button':
            case 'checkbox':
            case 'select':
                $options = array();
                if ($type == 'radio_button') {
                    $type = 'radio';
                }
                if (isset($args['options'])) {
                    if (empty($args['options'])) {
                        return;
                    }
                    foreach ($args['options'] as $opt) {
                        $options[$opt['id']] = PTB_Utils::get_label($opt);
                    }
                }

                self::show_as($type, $post_type, $id, $name, $value, $meta_key, $label, $options);
                break;
            case 'taxonomies':
            case 'category':
                $slug = $type === 'taxonomies' ? $module['taxonomy'] : $type;
                $v = '';
                if ($value) {
                    if (isset($value[$slug])) {
                        $v = $value[$slug];
                    } elseif ($type !== 'taxonomies') {
                        $v = $value;
                    }
                }
                $options = array();
                if ($module['show_as'] != 'autocomplete') {
                    $orderby = !empty($module['orderby'])?$module['orderby']:'name';
                    $order = !empty($module['order'])?$module['order']:'ASC';
                    $terms = get_terms($slug, array('hide_empty' => true, 'hierarchical' => false, 'orderby' => $orderby, 'order' => $order));
                    if(!empty($terms)){
                        foreach ($terms as $t) {
                            $options[$t->slug] = $t->name;
                        }
                    }
                } else {
                    if ($v) {
                        if (is_array($v)) {
                            $v = reset($v);
                        }
                        $get_term = get_term_by('slug', $v, $slug);
                        $v = $get_term ?array('slug'=>$v,'name'=>$get_term->name) : '';
                    }
                }
                self::show_as($module['show_as'], $post_type, $id, $slug, $v, $meta_key, $label, $options, $slug);
                break;
            case 'post_tag':
                $options = array();
                if(empty($module['show_as'])){
                    $module['show_as'] = 'autocomplete';
                }
                elseif($module['show_as']!=='autocomplete'){
                    $args = array( 'post_type' =>$post_type, 'orderby'=>'ID', 'order' => 'ASC','numberposts'=>1,'tag'=>'' );
                    $terms = get_tags(array(
                        'get'=>'all',
                        'orderby'=>'name',
                        'order'=>'ASC'
                    ));
                    foreach ($terms as $t){
                        $args['tag']=$t->slug;
                        $is_empty = get_posts($args);
                        if(!empty($is_empty)){
                            $options[$t->slug] = $t->name;
                        }
                    }
                    
                    wp_reset_postdata();
                }
                self::show_as($module['show_as'], $post_type, $id, $type, $value, $meta_key, $label, $options, $type);
                break;
            case 'custom_image':
                ?>
                <?php if (isset($module['image']) && $module['image']): ?>
                    <?php
                    $url = PTB_CMB_Base::ptb_resize($module['image'], $module['width'], $module['height']);
                    ?>
                    <figure class="ptb_search_post_image clearfix">
                        <?php
                        if (isset($module['link']) && $module['link']): 
                            echo '<a href="' . $module['link'] . '">';
                        endif;
                        ?>
                            <img src="<?php echo $url ?>" />
                        <?php
                        if (isset($module['link']) && $module['link']): 
                            echo '</a>';
                        endif;
                        ?>
                    </figure>
                <?php endif; ?>
                <?php
                break;
                case 'custom_text':
                if ($module['text'][$lang]){
                    echo!has_shortcode($module['text'][$lang], self::$plugin_name) ? do_shortcode($module['text'][$lang]) : $module['text'][$lang]; 
                }
                break;
            case 'has':
                $options = array(
                    'yes' => __('Yes', 'ptb-search'),
                    'no' => __('No', 'ptb-search')
                );
                if ($module['show_as'] === 'checkbox') {
                    $options = array(1 => '');
                }
                $name = 'has[' . $module['has_field'] . ']';
                if ($value && isset($value[$module['has_field']]) && isset($options[$value[$module['has_field']]])) {
                    $value = $value[$module['has_field']];
                } else {
                    $value = '';
                }
                self::show_as($module['show_as'], $post_type, $id, $name, $value, $meta_key, $label, $options, false, __('---', 'ptb-search'));
                break;
            case 'number':
                $from = $to = '';
                if ($value) {
                    if (isset($value['from']) && is_numeric($value['from'])) {
                        $from = floatval($value['from']);
                    }
                    if (isset($value['to']) && is_numeric($value['to'])) {
                        $to = floatval($value['to']);
                    }
                }
                $slider = $module['show_as'] == 'slider';
                ?>  
                <div class="ptb_search_wrap_number ptb_search_wrap_number_<?php echo $module['show_as'] ?>">
                        <?php if (!$slider): ?>
                            <div class="ptb_search_wrap_min">
                        <?php endif; ?>
                            <input placeholder="<?php _e('From', 'ptb-search') ?>" class="ptb_search_number_min" type="<?php echo $slider ? 'hidden' : 'number' ?>" id="<?php echo $id ?>_min" value="<?php echo $from ?>" name="<?php echo $name ?>-from" />
                        <?php if (!$slider): ?>
                            </div>
                            <div class="ptb_search_wrap_max">
                        <?php endif; ?>
                        <input placeholder="<?php _e('To', 'ptb-search') ?>" class="ptb_search_number_max" type="<?php echo $slider ? 'hidden' : 'number' ?>" id="<?php echo $id ?>_max" value="<?php echo $to ?>" name="<?php echo $name ?>-to"  />
                        <?php if ($slider): ?>

                            <div class="ptb-search-slider"></div>
                            <?php
                            if (!wp_script_is('jquery-ui-slider')) {
                                wp_enqueue_style(self::$plugin_name . 'ui-css');
                                wp_enqueue_script('jquery-ui-slider');
                            }
                            ?>
                        <?php else: ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                break;
        }
    }

    public static function show_as($type, $post_type, $id, $name, $value, $key, $label, $data = array(), $slug = false, $placeholder = false) {
        
        switch ($type) {
            case 'date':
                if (!wp_script_is(self::$plugin_name . '-date')) {
                    wp_enqueue_script(self::$plugin_name . '-date');
                }
                ?>
                    <div class="ptb_search_field_date">
                        <input type="text" class="ptb_search_date_from" data-id="<?php echo $id?>" id="<?php echo $id?>_start" name="<?php echo $name?>-from" placeholder="<?php _e('From', 'ptb-search') ?>" value="<?php echo isset($value['from'])?$value['from']:''?>" />
                        <input type="text" class="ptb_search_date_to" id="<?php echo $id?>_end" name="<?php echo $name?>-to" placeholder="<?php _e('To', 'ptb-search') ?>" value="<?php echo isset($value['to'])?$value['to']:''?>" />
                    </div>
                <?php 
                break;
            case 'select':
            case 'multiselect':
                if (!wp_script_is(self::$plugin_name . '-select')) {
                    wp_enqueue_style(self::$plugin_name . '-select');
                    wp_enqueue_script(self::$plugin_name . '-select');
                }
                if ($value && !is_array($value)) {
                    $value = array($value);
                }
                ?>
                <div class="ptb_search_<?php echo $type ?>_wrap">
                    <select data-placeholder="<?php echo $placeholder ? $placeholder : sprintf(__('Show All %s', 'ptb-search'), $label) ?>" <?php if ($type == 'multiselect'): ?>multiple="multiple" <?php endif; ?>name="<?php echo $name ?><?php echo $type == 'multiselect' ? '[]' : '' ?>" id="<?php echo $id ?>">
                        <?php if ($type != 'multiselect'): ?>
                            <option value=""><?php echo $placeholder ? $placeholder : sprintf(__('Show All %s', 'ptb-search'), $label) ?></option>
                        <?php endif; ?>
                        <?php foreach ($data as $k => $v): ?>
                            <option <?php if ($value && in_array($k, $value)): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <?php
                break;
            case 'autocomplete':
            case 'text':
            case 'title':
            case 'author':
                if (!wp_script_is('jquery-ui-autocomplete')) {
                    wp_enqueue_style(self::$plugin_name . 'ui-css');
                    wp_enqueue_script('jquery-ui-autocomplete');
                }
                $v1 = $value;
                $v2 = '';
                if($key === 'taxonomies' && !empty($value['name'])){
                    $v1 = $value['name'];
                    $v2 = $value['slug'];
                }
                ?>
                <input type="text" <?php if ($key !== 'taxonomies'): ?>name="<?php echo $name ?>"<?php endif; ?> data-post_type="<?php echo $post_type ?>" data-key="<?php echo base64_encode(json_encode(array('post_type' => $post_type, 'key' => $key, 'slug' => $slug))) ?>" class="ptb-search-autocomplete" value="<?php  esc_attr_e($v1) ?>"  id="<?php echo $id ?>" />
                <input type="hidden" value="<?php esc_attr_e($v2)?>" <?php if ($key === 'taxonomies'): ?>name="<?php echo $name ?>"<?php endif; ?> />
                <?php
                break;
            case 'radio':
            case 'checkbox':
                ?>
                <div class="ptb_search_<?php echo $type ?>_wrap ptb_search_option_wrap">
                <?php
                $is_one = count($data) === 1;
                if ($type == 'checkbox') {
                    $name.= '[]';
                } else {
                    if ($value) {
                        $value = array($value);
                    }
                    ?>
                    
                        <label>
                            <input <?php if (!$value): ?> checked="checked"<?php endif; ?> type="<?php echo $type ?>" name="<?php echo $name ?>" value="" />
                            <?php _e('Any', 'ptb-search') ?>
                        </label>
               
                <?php } ?>
               
                <?php foreach ($data as $k => $v): ?>
                        <?php if (!$is_one): ?>
                            <label>
                                <input <?php if ($value && in_array($k, $value)): ?> checked="checked"<?php endif; ?>id="<?php echo $id ?>_<?php echo $k ?>" type="<?php echo $type ?>" name="<?php echo $name ?>" value="<?php echo $k ?>" />
                                <?php echo $v ?>
                            </label>
                        <?php else: ?>
                            <label>
                                <input <?php if ($value && in_array($k, $value)): ?> checked="checked"<?php endif; ?>id="<?php echo $id ?>" type="<?php echo $type ?>" name="<?php echo $name ?>" value="<?php echo $k ?>" />
                                <?php echo $v ?>
                            </label>
                        <?php endif; ?>
                <?php endforeach ?>
                </div>
                <?php
                break;
        }
    }

    public function set_values() {
        if (!isset($_POST['data']) || !$_POST['data']) {
            wp_die();
        }
        $data = sanitize_text_field($_POST['data']);
        $data = json_decode(base64_decode($data), true);
        if (!$data) {
            wp_die();
        }
        $slug = key($data);
        $ptb_options = PTB::get_option();
        if (!isset($ptb_options->option_post_type_templates[$slug]) || !isset($ptb_options->option_post_type_templates[$slug]['search']) || !isset($ptb_options->option_post_type_templates[$slug]['search']['layout'])) {
            wp_die();
        }
        $post_type = $ptb_options->option_post_type_templates[$slug]['post_type'];
        $cache = PTB_Search_Options::get_cache();
        if (isset($cache['default']) && isset($cache['default'][$post_type])) {
            echo wp_json_encode($cache['default'][$post_type]);
            wp_die();
        }
        $cmb_options = $ptb_options->get_cpt_cmb_options($post_type);
        $result = array();
        global $wpdb;
        PTB_Public::$shortcode = true;
        foreach ($data[$slug] as $meta_key => $m) {
            if (!isset($cmb_options[$meta_key])) {
                continue;
            }
            $args = $cmb_options[$meta_key];
            $meta_key = sanitize_key($meta_key);
            $id = 'ptb_' . $slug . '_' . $meta_key;
            $meta_key = 'ptb_' . $meta_key;
            switch ($m['type']) {
                case 'number':
                    $get_values = $wpdb->get_results("SELECT `post_id`,`meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '$meta_key' AND `meta_value`!=''");
                    $max = $min = array();
                    if (!empty($get_values)) {
                        $ids = array();
                        foreach ($get_values as $val) {
                            $ids[] = $val->post_id;
                        }
                        $ids = implode(',', $ids);
                        $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                        if (!empty($posts)) {
                            $ids = array();
                            foreach ($posts as $p) {
                                $ids[$p->ID] = 1;
                            }
                            foreach ($get_values as $val) {
                                if (isset($ids[$val->post_id])) {
                                    $v = maybe_unserialize($val->meta_value);
                                    $m = isset($v['to']) ? $v['to'] : $v;
                                    $n = isset($v['from']) ? $v['from'] : $v;
                                    if (is_numeric($m)) {
                                        $min[$val->post_id] = $m;
                                    }
                                    if (is_numeric($n)) {
                                        $max[$val->post_id] = $n;
                                    }
                                }
                            }
                            $result[$id] = array('min' => !empty($min) ? min($min) : 0, 'max' => !empty($max) ? max($max) : 0);
                        } else {
                            $result[$id] = 1;
                        }
                    } else {
                        $result[$id] = 1;
                    }
                    break;
                case 'radio_button':
                case 'checkbox':
                case 'select':

                    foreach ($args['options'] as $opt) {
                        $condition = $m['type'] != 'radio_button' ? "LOCATE('{$opt['id']}',`meta_value`,15)>0" : "meta_value='{$opt['id']}'";
                        $get_values = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '$meta_key' AND $condition");
                        if (empty($get_values)) {
                            $result[$id][] = $opt['id'];
                        } else {
                            $ids = array();
                            foreach ($get_values as $val) {
                                $ids[] = $val->post_id;
                            }
                            $ids = implode(',', $ids);
                            $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish' LIMIT 1");
                            if (empty($posts)) {
                                $result[$id][] = $opt['id'];
                            }
                        }
                    }
                    break;
                case 'has':
                    if ($m['has_field'] == 'comments') {
                        $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '$post_type' AND `post_status`='publish' AND comment_count>0 LIMIT 1");
                        if (empty($posts)) {
                            $result[$id] = 1;
                        }
                    } elseif ($m['has_field'] == 'thumbnail') {
                        $args = array(
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'posts_per_page' => 1,
                            'meta_query' => array(
                                array(
                                    'key' => '_thumbnail_id',
                                    'compare' => 'EXISTS'
                                ),
                            )
                        );
                        $query = new WP_Query($args);
                        if (!$query->have_posts()) {
                            $result[$id] = 1;
                        }
                    }
                    break;
            }
        }
        $cache['default'][$post_type] = $result;
        PTB_Search_Options::set_cache($cache);
        echo wp_json_encode($result);
        PTB_Public::$shortcode = false;
        wp_die();
    }

    public function get_terms() {
        if (isset($_GET['key']) && isset($_GET['term'])) {
            $data = sanitize_text_field($_GET['key']);
            $data = json_decode(base64_decode($data), true);
            if (!$data) {
                wp_die();
            }
            $post_type = sanitize_key($data['post_type']);
            $ptb_options = PTB::get_option();
            if (!$ptb_options->has_custom_post_type($post_type)) {
                wp_die();
            }
            $key = esc_sql($data['key']);
            $term = esc_sql($_GET['term']);
            $options = array();
            PTB_Public::$shortcode = true;
            if ($key === 'title' || $key === 'taxonomies' || $key==='author' || $key==='post_tag') {
               if($key==='author'){
                    global $wpdb;
                    $get_values = $wpdb->get_results("SELECT display_name,ID FROM (((SELECT post_author FROM `{$wpdb->posts}`  WHERE post_status='publish' AND post_type='$post_type') AS A ) JOIN (SELECT display_name,ID FROM `{$wpdb->users}` WHERE LOCATE('{$term}',`display_name`)>0) AS B) WHERE B.ID=post_author GROUP BY display_name ORDER BY NULL  LIMIT 15");
                    foreach ($get_values as $author) {
                        $options[$author->ID] = array('id' => $author->ID, 'label' => $author->display_name);
                    }
                }
                elseif($key==='taxonomies'){
                    $slug = sanitize_key($data['slug']);
                    $terms = get_terms($slug, array('name__like'=>$term,'hide_empty' => true, 'hierarchical'=>false,'orderby' => 'name', 'order' => 'ASC','number'=>15));
                    foreach ($terms as $t) {
                        $options[$t->term_id] = array('id' => $t->slug, 'label' => $t->name);
                    }
                } 
                elseif($key==='post_tag'){
                     $terms = get_tags(array(
                        'name__like'=>$term,
                        'get'=>'all',
                        'orderby'=>'name',
                        'order'=>'ASC',
                        'number'=>15
                    ));
                    foreach ($terms as $t) {
                        $options[$t->term_id] = array('id' => $t->slug, 'label' => $t->name);
                    }
                }
                else {
                    add_filter('posts_search', array(__CLASS__, 'search_by_title'), 100, 2);
                    $posts = get_posts(
                            array(
                                'post_type' => $post_type,
                                'orderby' => 'title',
                                'order' => 'ASC',
                                's' => $term,
                                'posts_per_page' => 15
                            )
                    );
                    foreach ($posts as $p) {
                        $options[$p->ID] = array('id' => $p->post_title, 'label' => $p->post_title);
                    }
                }
            } else {
                global $wpdb;
                $get_values = $wpdb->get_results("SELECT `post_id`,meta_value FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$key' AND LOCATE('{$term}',`meta_value`)>0");
                if (!empty($get_values)) {
                    $values = array();
                    foreach ($get_values as $val) {
                        $values[$val->post_id] = $val->meta_value;
                    }
                    $ids = implode(',', array_keys($values));
                    $posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                    if (!empty($posts)) {
                        $p = array();
                        foreach ($posts as $post) {
                            $p[$post->ID] = 1;
                        }
                        foreach ($values as $k => $v) {
                            if (isset($p[$k])) {
                                $v = maybe_unserialize($v);
                                if (is_array($v)) {
                                    foreach ($v as $v1) {
                                        if (stripos($v1, $term) !== false) {
                                            $options[$v1] = array('id' => $v1, 'label' => $v1);
                                        }
                                    }
                                } else {
                                    $options[$v] = array('id' => $v, 'label' => $v);
                                }
                            }
                        }
                    }
                }
            }
            echo wp_json_encode($options);
        }
        PTB_Public::$shortcode = false;
        wp_die();
    }

    public static function search_by_title($search, &$wp_query) {
        global $wpdb;
        if (empty($search))
            return $search; // skip processing - no search term in query
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

    private static function search($post_type, $slug, array $post = array(), $post_id = false, array $cmb_options = array(), array $post_support = array(), array $post_taxonomies = array(), $echo = true) {
        PTB_Public::$shortcode = true;
       if ($post_id === false) {
            $cache = PTB_Search_Options::get_cache();
            $post_id = array();
            global $wpdb;
            $post = apply_filters('ptb_search_post', $post, $cmb_options, $post_type, $slug, $post_taxonomies);
        
            foreach ($post as $meta_key => $value) {
                if (!isset($cmb_options[$meta_key]) || !$value) {
                    continue;
                }
                $args = $cmb_options[$meta_key];
                $type = $args['type'];
                
                switch ($type) {
                    case 'date':
                        
                        if((isset($value['from']) && $value['from']) || isset($value['to']) && $value['to']){
                            $query_args = array(
                               'fields' => 'ids',
                               'post_type' => $post_type,
                               'orderby' => 'ID',
                               'order' => 'ASC',
                               'nopaging' => 1,
                               'include'=> !empty($post_id) ? implode(',', array_keys($post_id)) : '',
                               'date_query' => array(
                                       array(
                                           'after'     => isset($value['from']) && $value['from']?$value['from']:'',
                                           'before'    => isset($value['to']) && $value['to']?$value['to']:'',
                                           'inclusive' => true,
                                       ),
                                    )
                            );
                            $posts_array = get_posts($query_args);
                            if (empty($posts_array)) {
                                self::response();
                                return;
                            } else {
                                $post_id = array();
                                foreach ($posts_array as $p) {
                                    $post_id[$p] = 1;
                                }
                            }
                        }
                    break;
                    case 'checkbox':
                    case 'select':
                    case 'radio_button':
                    case 'text':
                    case 'title':
                    case 'author':
                        
                        if($type==='author'){
                               $value = esc_sql($value);
                               $condition = !empty($post_id)?' AND ID IN('.implode(',',array_keys($post_id)).')':'';
                               $get_posts = $wpdb->get_results("SELECT P.ID FROM (((SELECT post_author,ID FROM `{$wpdb->posts}`  WHERE post_status='publish' AND post_type='$post_type' $condition) AS P ) JOIN (SELECT ID FROM `{$wpdb->users}` WHERE LOCATE('{$value}',`display_name`)>0) AS U) WHERE U.ID=P.post_author");
                               if(empty($get_posts)){
                                    self::response();
                                    return;
                                }
                                else{
                                    $post_id = array();
                                    foreach ($get_posts as $p) {
                                       $post_id[$p->ID] = 1;
                                    }
                                }
                        }
                        elseif ($type === 'title') {
                            add_filter('posts_search', array(__CLASS__, 'search_by_title'), 100, 2);
                            $get_posts = get_posts(
                                    array(
                                        'fields' => 'ids',
                                        'post_type' => $post_type,
                                        'orderby' => 'ID',
                                        'order' => 'ASC',
                                        'include' => !empty($post_id) ? implode(',', array_keys($post_id)) : '',
                                        's' => sanitize_text_field($value),
                                        'nopaging' => 1
                                    )
                            );

                            if (empty($get_posts)) {
                                self::response();
                                return;
                            } else {
                                $post_id = array();
                                foreach ($get_posts as $p) {
                                    $post_id[$p] = 1;
                                }
                            }
                        } else {
                            if ($type !== 'text' && $type !== 'title') {
                                if (!is_array($value)) {
                                    $value = array($value);
                                }
                                $options = array();
                                foreach ($args['options'] as $opt) {
                                    $options[$opt['id']] = 1;
                                }
                                foreach ($value as $k => &$ch_m) {
                                    if (!isset($options[$ch_m])) {
                                        unset($value[$k]);
                                    }
                                }
                                if (empty($value)) {
                                    self::response();
                                    return;
                                }
                            }
                            if ($type === 'text') {
                                $value = array($value);
                            }
                            foreach ($value as $ch) {
                                $ch = esc_sql($ch);
                                $condition = !empty($post_id) ? ' AND post_id IN(' . implode(',', array_keys($post_id)) . ')' : '';
                                $get_values = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$meta_key' AND LOCATE('{$ch}',`meta_value`)>0 $condition");
                                if (!empty($get_values)) {
                                    $ids = array();
                                    foreach ($get_values as $val) {
                                        $ids[] = $val->post_id;
                                    }
                                    $ids = implode(',', $ids);

                                    $get_posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                                    if (!empty($get_posts)) {
                                        $post_id = array();
                                        foreach ($get_posts as $p) {
                                            $post_id[$p->ID] = 1;
                                        }
                                    } else {
                                        self::response();
                                        return;
                                    }
                                } else {
                                    self::response();
                                    return;
                                }
                            }
                        }
                        break;
                    case 'taxonomies':
                    case 'category':
                        $query_args = array(
                            'fields' => 'ids',
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'nopaging' => 1
                        );
                        if ($type !== 'taxonomies') {
                            $value = array($type => $value);
                        }
                        $tax_post_id = $post_id;
                        foreach ($value as $tax => $v) {
                            if (!empty($v) && in_array($tax, $post_taxonomies)) {

                                if ($tax === 'category') {
                                    $query_args['category_name'] = is_array($v)?implode(', ',$v):$v;
                                } else {
                                    $query_args['tax_query'] = array(
                                        array(
                                            'taxonomy' => $tax,
                                            'field' => 'slug',
                                            'terms' => $v,
                                            'operator' => 'IN',
                                        )
                                    );
                                }
                                $query_args['include'] = !empty($tax_post_id) ? implode(',', array_keys($tax_post_id)) : '';
                                $posts_array = get_posts($query_args);
                                if (empty($posts_array)) {
                                    self::response();
                                    return;
                                }
                                $tax_post_id = array();
                                foreach ($posts_array as $p) {
                                    $tax_post_id[$p] = 1;
                                }
                            }
                        }
                        $post_id = $tax_post_id;
                        break;
                    
                    case 'post_tag':
                        $query_args = array(
                            'fields' => 'ids',
                            'tag'=>$value,
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'nopaging' => 1,
                            'include'=>!empty($post_id) ? implode(',', array_keys($post_id)) : ''
                        );
                        $posts_array = get_posts($query_args);
                        if (empty($posts_array)) {
                            self::response();
                            return;
                        }  
                       
                        $post_id = array();
                        foreach ($posts_array as $p) { 
                            $post_id[$p] = 1;
                        }
                    break;
                    case 'has':
                        if (!is_array($value)) {
                            $value = array($value);
                        }
                        $field_post_id = $post_id;

                        $query_args = array(
                            'fields' => 'ids',
                            'post_type' => $post_type,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'nopaging' => 1,
                            'meta_query' => array(
                                array(
                                    'key' => '_thumbnail_id',
                                    'compare' => 'EXISTS'
                                ),
                            )
                        );
                        foreach ($value as $field => $v) {
                            if ($v) {
                                $posts_array = array();
                                if ($field === 'comments') {
                                    $condition = $v === 'no' ? 'comment_count=0' : 'comment_count>0';
                                    $include = !empty($field_post_id) ? ' AND ID IN(' . implode(',', array_keys($field_post_id)) . ')' : '';
                                    $posts_array = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '$post_type' AND `post_status`='publish' AND $condition $include");
                                } elseif ($field === 'thumbnail') {
                                    $query_args['meta_query'][0]['compare'] = $v === 'no' ? 'NOT EXISTS' : 'EXISTS';
                                    $query_args['include'] = !empty($field_post_id) ? implode(',', array_keys($field_post_id)) : '';
                                    $posts_array = get_posts($query_args);
                                }
                                if (empty($posts_array)) {
                                    self::response();
                                    return;
                                }
                                $field_post_id = array();
                                foreach ($posts_array as $p) {
                                    if ($field === 'comments') {
                                        $field_post_id[$p->ID] = 1;
                                    } else {
                                        $field_post_id[$p] = 1;
                                    }
                                }
                            }
                        }
                        $post_id = $field_post_id;
                        break;
                    case 'number':
                        if (isset($cache['default']) && isset($cache['default'][$post_type]) && isset($value['from']) && isset($value['to'])) {
                            $id = 'ptb_' . $slug . '_' . $meta_key;
                            if (isset($cache['default'][$post_type][$id])) {
                                $min = floor($cache['default'][$post_type][$id]['min']);
                                $max = floor($cache['default'][$post_type][$id]['max']);
                                $from = floatval($value['from']);
                                $to = floatval($value['to']);
                                if ($from >= $min && $from <= $max && $to >= $min && $to <= $max) {
                                    if ($from == $min && $to == $max) {
                                        unset($post[$meta_key]);
                                        continue 2;
                                    }
                                    $condition = !empty($post_id) ? ' AND post_id IN(' . implode(',', array_keys($post_id)) . ')' : '';
                                    $get_values = $wpdb->get_results("SELECT `post_id`,`meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'ptb_$meta_key' $condition");
                                    if (!empty($get_values)) {
                                        $ids = array();
                                        foreach ($get_values as $val) {
                                            $ids[] = $val->post_id;
                                        }
                                        $ids = implode(',', $ids);
                                        $get_posts = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE  ID IN({$ids}) AND `post_type` = '$post_type' AND `post_status`='publish'");
                                        if (!empty($get_posts)) {
                                            $ids = $range_ids = array();
                                            foreach ($get_posts as $p) {
                                                $ids[$p->ID] = 1;
                                            }
                                            foreach ($get_values as $val) {
                                                if (isset($ids[$val->post_id])) {
                                                    $v = maybe_unserialize($val->meta_value);
                                                    $max = isset($v['to']) ? $v['to'] : $v;
                                                    $min = isset($v['from']) ? $v['from'] : $v;
                                                    $max = floor($max);
                                                    $min = floor($min);
                                                    if ((!$min || $from <= $min) && (!$max || $max <= $to)) {
                                                        $range_ids[$val->post_id] = 1;
                                                    }
                                                }
                                            }
                                            if (empty($range_ids)) {
                                                self::response();
                                                return;
                                            } else {
                                                $post_id = $range_ids;
                                            }
                                        } else {
                                            self::response();
                                            return;
                                        }
                                    } elseif ($condition) {
                                        self::response();
                                        return;
                                    }
                                } else {
                                    self::response();
                                    return;
                                }
                            } else {
                                self::response();
                                return;
                            }
                        }
                        break;
                    default :
                        $post_id = apply_filters('ptb_search_by_'.$type,$post_id,$post_type,$value,$args,$meta_key,$post_taxonomies);
                        if (empty($post_id)) {
                            self::response();
                            return;
                        }
                    break;
                    
                }
            }
            $post_id = array_keys($post_id);
        }
        $post_id = apply_filters('ptb_search_result', $post_id, $post, $cmb_options, $post_type, $slug, $post_taxonomies);
        wp_reset_postdata();
        if ($echo) {
            self::response($post_id, $post_type);
        } else {
            return $post_id;
        }
    }

    public function get_post() {

        if (isset($_REQUEST['ptb-search']) && isset($_REQUEST['f'])) {

            $slug = sanitize_key($_REQUEST['f']);
            $ptb_options = PTB::get_option();
            if (!isset($ptb_options->option_post_type_templates[$slug]) || !isset($ptb_options->option_post_type_templates[$slug]['search']) || !isset($ptb_options->option_post_type_templates[$slug]['search']['layout'])) {
                return;
            }
            $post_type = $ptb_options->option_post_type_templates[$slug]['post_type'];
            if (!$ptb_options->has_custom_post_type($post_type)) {
                return;
            }
            if (!self::$permalink) {
                self::$permalink = trim(str_replace(home_url(), '', get_permalink()), '/');
            }
            $post_id = self::$cache_enabled ? PTB_Search_Options::get_query_cache($post_type, $_REQUEST) : false;
            $cmb_options = $post_support = $post_taxonomies = array();
            $ptb_options->get_post_type_data($post_type, $cmb_options, $post_support, $post_taxonomies);
            $post_taxonomies[] = 'category';
            $post_taxonomies[] = 'post_tag';
            $cmb_options['has'] = array('type' => 'has');
            $post_support[] = 'has';
            $cmb_options = apply_filters('ptb_search_render', $cmb_options, $post_support);
            $options = array();
            self::$slug = $slug;
            foreach ($cmb_options as $key => $cmb) {

                $name = isset($cmb['name']) ? PTB_Utils::get_label($cmb['name']) : ($key === 'has' ? $key : PTB_Search_Options::get_name($key));
                $name = $name ? sanitize_title($name) : $key;
                if ($cmb['type'] === 'number' || $cmb['type']==='date') {
                    $options[$name . '-to']  = $options[$name . '-from'] = array('label' => $name, 'key' => $key, 'type' => $cmb['type']);
                }
                else {
                    $options[$name] = array('key' => $key, 'type' => $cmb['type']);
                }
            }

            self::$data[$post_type] = array();
            foreach ($_REQUEST as $k => $v) {
                if ($v) {
                    if (in_array($k, $post_taxonomies)) {
                        if ($k !== 'category' && $k !== 'post_tag') {
                            self::$data[$post_type]['taxonomies'][$k] = $v;
                        } else {
                            self::$data[$post_type][$k] = $v;
                        }
                    } elseif (isset($options[$k])) {
                        if ($options[$k]['type'] !== 'number' && $options[$k]['type'] !== 'date') {
                            self::$data[$post_type][$options[$k]['key']] = $v;
                        } else {
                            if(isset($_REQUEST[$options[$k]['label'] . '-from'])){
                                self::$data[$post_type][$options[$k]['key']]['from'] = $_REQUEST[$options[$k]['label'] . '-from'];
                            }
                            if(isset($_REQUEST[$options[$k]['label'] . '-to'])){
                                self::$data[$post_type][$options[$k]['key']]['to'] = $_REQUEST[$options[$k]['label'] . '-to'];
                            }
                        }
                    }
                }
            }
   
            self::$data[$post_type] = apply_filters('ptb_search_filter_by_slug',self::$data[$post_type], $post_id,$options, $cmb_options, $post_support, $post_taxonomies);
            unset($options);
            self::search($post_type, $slug, self::$data[$post_type], $post_id, $cmb_options, $post_support, $post_taxonomies);
           
        }
        add_action('the_content', array($this, 'markup'), 20, 1);
    }

    private static function response(array $post_id = array(), $post_type = false) {
        if ($post_type) {
            $ptb_options = PTB::get_option();
            $templateObject = $ptb_options->get_post_type_template_by_type($post_type);
            if($templateObject){
                    $grid = $templateObject->get_archive();
                    $style = $grid[$ptb_options->prefix_ptt_id . 'layout_post'];
            }
            if(!$style){
                $style  = 'list-post';
            }
            $args = array('type' => $post_type,
                'pagination' => 1,
                'ids' => implode(',', $post_id),
                'paged' => isset($_REQUEST['ptb_page']) ? ($_REQUEST['ptb_page'] > 0 ? intval($_REQUEST['ptb_page']) : 1) : get_query_var('paged', 1),
                'style'=>$style
            );
            $args = apply_filters('ptb_search_result_shortcode', $args);
            $query = '';
            foreach ($args as $k => $v) {
                $query.=' ' . $k . '="' . $v . '"';
            }

            self::$resp = do_shortcode('[ptb ' . $query . ']');
        } else {
            self::$resp = __('No Items found', 'ptb-search');
        }
        if (self::$cache_enabled) {
            PTB_Search_Options::set_query_cache($post_type, $_REQUEST, $post_id);
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            echo self::$resp;
            wp_die();
        }
    }

    public function markup($content) {

        return !PTB_Public::$render_content && self::$is_page && self::$permalink === self::$ptb_page ? $content.'<div data-slug="' . self::$slug . '" class="ptb-search-container">' . self::$resp . '</div>' : $content;
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

}
