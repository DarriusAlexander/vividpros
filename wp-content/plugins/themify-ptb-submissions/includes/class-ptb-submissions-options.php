<?php

/**
 * The plugin options management class
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * The plugin options helper class
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Submissiion_Options {

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
     * Custom Post Types Templates array
     *
     * @since    1.0.0
     * @access   private
     * @var      array $option_post_type_templates The options of custom post types templates.
     */

    /**
     * The plugin options array
     *
     * @since    1.0.0
     * @access   private
     * @var      array $options The options of the plugin.
     */
    private $options;
    private $settings_key = 'options';
    private $options_key_settings = 'settings';
    protected $option_settings = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of this plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings_key = $this->plugin_name . '-' . $this->settings_key;
        $this->load_options();
    }

    /**
     * Loads the plugin options.
     * Default options created if plugin options are empty
     *
     * @since 1.0.0
     */
    private function load_options() {

        $this->options = get_option($this->settings_key);
        if (empty($this->options)) {
            $this->options = $this->get_options_blueprint();
        }

        $this->option_settings = &$this->options[$this->options_key_settings];
    }

    private function get_options_blueprint() {
        return array(
            $this->options_key_settings => array()
        );
    }

    public function get_settings() {
        return $this->option_settings;
    }

    public function set_settings($settings) {
        $this->option_settings = $settings;
    }

    public function update() {
        return update_option($this->settings_key, $this->options);
    }

    public function get_submission_template($post_type) {
        $ptb_options = PTB::get_option();
        foreach ($ptb_options->option_post_type_templates as $k => $t) {
            if (isset($t['frontend']) && isset($t['frontend']['data']) && isset($t['post_type']) && $post_type == $t['post_type']) {
                $t['id'] = $k;
                return $t;
            }
        }
        return FALSE;
    }

    public static function get_name($type, $multi = false) {
        $names = array(
            'editor' => __('Content', 'ptb-submission'),
            'title' => __('Title', 'ptb-submission'),
            'post_tag' => __('Tags', 'ptb-submission'),
            'thumbnail' => __('Featured Image', 'ptb-submission'),
            'taxonomies' => $multi ? __('Taxonomies', 'ptb-submission') : __('Taxonomy', 'ptb-submission'),
            'category' => $multi ? __('Categories', 'ptb-submission') : __('Category', 'ptb-submission'),
            'excerpt' => __('Excerpt', 'ptb-submission'),
            'user_name' => __('Username', 'ptb-submission'),
            'user_email' => __('Email Address', 'ptb-submission'),
            'user_password' => __('Password', 'ptb-submission'),
            'user_confirm_password' => __('Confirm Password', 'ptb-submission')
        );
        return isset($names[$type]) ? $names[$type] : FALSE;
    }

    /**
     * Get full list of currency codes.
     * @return array
     */
    public function get_currencies() {
        return apply_filters('ptb_submission_currencies', PTB_Utils::get_currencies());
    }

    /**
     * Get Currency symbol.
     * @param string $currency
     * @return string
     */
    public function get_currency_symbol($currency) {
        return apply_filters('ptb_submission_currency_symbol', PTB_Utils::get_currency_symbol($currency), $currency);
    }

    /**
     * Get full list of currency codes.
     * @return array
     */
    public function get_currency_position() {
        return apply_filters('ptb_submission_currency_position', PTB_Utils::get_currency_position());
    }

    /**
     * Get the price format depending on the currency position
     *
     * @return string
     */
    function get_price_format($currency_pos, $currency, $price) {
        $format = '%1$s%2$s';
        if (!$currency_pos) {
            $get_options = get_option($this->plugin_name . '-settings');
            $currency_pos = $get_options['currency_position'];
        }
        switch ($currency_pos) {
            case 'left' :
                $format = '%1$s%2$s';
                break;
            case 'right' :
                $format = '%2$s%1$s';
                break;
            case 'left_space' :
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space' :
                $format = '%2$s&nbsp;%1$s';
                break;
        }
        $format = apply_filters('ptb_submission_price_format', $format, $currency, $currency_pos);
        return sprintf($format, $this->get_currency_symbol($currency), $price);
    }

    public static function max_upload_size($size) {
        $max_upload = wp_max_upload_size();
        if (isset($size) || !$size) {
            $size = floatval($size);
            if ($size < 1 || $size > $max_upload) {
                $size = $max_upload;
            }
        } else {
            $size = $max_upload;
        }
        return $size;
    }

    public static function get_allow_ext(array $extension = array(), $type = 'image') {
        switch ($type) {
            case 'video':
                $ext = array('wmv', 'avi', 'flv', 'mov|qt', 'mpeg|mpg|mpe', 'mp4|m4v', 'ogv', 'webm');
                break;
            case 'audio':
                $ext = array('mp3|m4a|m4b', 'ogg|oga', 'wav', 'wma');
                break;
            case 'application':
                $ext = array('doc', 'docx','docm','dotx','dotm', 'xls', 'xlsx','xlsm','xlsb','xltx','xltm','xlam','txt|asc|c|cc|h|srt','zip','gz|gzip','tar','7z','pdf','psd');
                break;
            default:
                $ext = array('gif', 'png', 'bmp', 'jpg|jpeg|jpe', 'tif|tiff', 'ico');
        }
        $allowed_extensions = get_allowed_mime_types();
        $can_be_allowed = !empty($extension) && !in_array('all', $extension) ? $extension : $ext;
        $allow = array();
        foreach ($can_be_allowed as $k => &$e) {
            if (isset($allowed_extensions[$e])) {
                $allow[$e] = $allowed_extensions[$e];
            }
        }
        return $allow;
    }

    public static function HumanFileSize($size, $unit = false) {
        if ((!$unit && $size >= 1 << 30) || $unit == "GB")
            return sprintf( __( "%sGB", 'ptb-submission' ), number_format( $size / (1 << 30), 2 ) );
        if ((!$unit && $size >= 1 << 20) || $unit == "MB")
            return sprintf( __( "%sMB", 'ptb-submission' ), number_format( $size / (1 << 20), 2 ) );
        if ((!$unit && $size >= 1 << 10) || $unit == "KB")
            return sprintf( __("%sKB", 'ptb-submission' ), number_format( $size / (1 << 10), 2 ) );
        return sprintf( __( "%s bytes", 'ptb-submission' ), number_format( $size ) );
    }

    public static function FileUniqname($dir, $filename, $ext) {
        $filename = sanitize_file_name($filename);
        $name = preg_match('/[^A-Za-z0-9-_]$/iu', $filename) ? md5($filename) : $filename;
        return wp_unique_filename($dir, $name . $ext);
    }

    public static function validate_file(array $f, array $allow, $size = NULL) {

        $error = false;
        $size = self::max_upload_size($size);
        if ($f['size'] > $size) {
            $error = sprintf(__("Maximum upload file size is %s", 'ptb-submission'), self::HumanFileSize($size));
        } else {
            if (!function_exists('wp_handle_upload')) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            $movefile = wp_handle_upload($f, array('test_form' => false, 'mimes' => $allow, 'unique_filename_callback' => array(__CLASS__, 'FileUniqname')));
            if ($movefile && !isset($movefile['error'])) {
                $movefile['name'] = $f['name'];
                $movefile['size'] = $f['size'];
            } else {
                $error = $movefile['error'];
            }
        }
        @unlink($f['tmp_name']);
        return $error ? array('error' => $error) : array('file' => $movefile);
    }

}
