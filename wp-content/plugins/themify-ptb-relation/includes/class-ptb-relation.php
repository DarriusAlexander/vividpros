<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Base class
 *
 */
class PTB_Relation {

    /**
     * The ID of plugin.
     *
     * @since    1.0.0
     * @access   public
     * @var      string $plugin_name The ID of this plugin.
     */
    private static $plugin_name;

    /**
     * The version of plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private static $version;

    /**
     * The type of custom meta box.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $type The type of custom meta box.
     */
    private static $options;

    public function __construct($version) {

        self::$plugin_name = 'ptb-relation';
        self::$version = $version;
        self::load_dependencies();
        self::set_locale();
        if (is_admin()) {
            $this->define_admin_hooks();
        }
        else{
            $this->define_public_hooks();
        }
        new PTB_CMB_Relation('relation','ptb', self::$version);
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - PTB_Loader. Orchestrates the hooks of the plugin.
     * - PTB_i18n. Defines internationalization functionality.
     * - PTB_Admin. Defines all hooks for the dashboard.
     * - PTB_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function load_dependencies() {
        $plugin_dir = plugin_dir_path(dirname(__FILE__));

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once $plugin_dir . 'includes/class-ptb-relation-i18n.php';
        require_once $plugin_dir . 'includes/class-ptb-relation-options.php';
        require_once $plugin_dir . 'includes/class-ptb-cmb-relation.php';
        if (is_admin()) {
            require_once $plugin_dir . 'includes/class-ptb-relation-list.php';
            require_once $plugin_dir . 'admin/class-ptb-relation-admin.php';
            require_once $plugin_dir . 'includes/class-ptb-form-ptt-relation.php';
        }
        else{
             require_once $plugin_dir . 'public/class-ptb-relation-public.php';
        }
      
        do_action('ptb_relation_loaded');
    }

    private function define_admin_hooks() {
        $options = PTB::get_option();
        $plugin_admin = new PTB_Relation_Admin(self::$plugin_name, self::$version, $options);
        add_action('admin_init', array($plugin_admin, 'init'));
        add_filter('ptb_admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        add_action('ptb_cpt_update', array($plugin_admin, 'cpt_update'), 11, 2);
        add_action('ptb_cpt_remove', array($plugin_admin, 'cpt_remove'), 11, 1);
        add_action('ptb_deactivated', array($plugin_admin, 'deactivate'));
        add_action('wp_ajax_ptb_relation_add', array($plugin_admin, 'add_template'));
        add_action('wp_ajax_ptb_relation_edit', array($plugin_admin, 'edit_template'));
        add_action('wp_ajax_ptb_relation_get_post', array($plugin_admin, 'get_related_posts'));
        
        add_action('wp_ajax_ptb_relation_get_term', array($plugin_admin, 'get_term'));
        add_action('wp_ajax_ptb_relation_add_template', array($plugin_admin, 'save_temlate'));
        add_action('wp_ajax_ptb_relation_get_import', array($plugin_admin, 'import_temlate'));
        add_action('wp_ajax_ptb_relation_import', array($plugin_admin, 'set_import'));
        add_action('wp_ajax_ptb_relation_delete', array($plugin_admin, 'delete'));
        add_action('wp_ajax_ptb_relation_list', array($plugin_admin, 'get_list'));
        add_filter('ptb_template_save', array($plugin_admin, 'ptb_template_save'), 10, 2);
        add_filter('ptb_screens', array($plugin_admin, 'screens'), 10, 2);
        //Submission
        add_action('wp_ajax_ptb_relation_submission_posts', array($plugin_admin, 'get_term'));
        add_action('wp_ajax_nopriv_ptb_relation_submission_posts', array($plugin_admin, 'get_term'));
        add_action('ptb_submission_template_relation', array($plugin_admin, 'ptb_submission_themplate'), 10, 5);
        add_filter('ptb_submission_validate_relation', array($plugin_admin, 'ptb_submission_validate'), 10, 7);
        add_filter('ptb_submission_metabox_save_relation', array($plugin_admin, 'ptb_submission_save'), 10, 5);
        
    }

    private function define_public_hooks() {
        $plugin_public = new PTB_Relation_Public(self::$plugin_name, self::$version);
        add_action('wp_enqueue_scripts', array($plugin_public, 'public_enqueue_scripts'));
        add_action('ptb_submission_relation', array($plugin_public, 'ptb_submission_form'), 10, 6);
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the PTB_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function set_locale() {

        $plugin_i18n = new PTB_Relation_i18n();
        $plugin_i18n->set_domain(self::$plugin_name);
        $plugin_i18n->load_plugin_textdomain();
    }

    public static function get_option() {
        if (!isset(self::$options)) {
            self::$options = new PTB_Relation_Options(self::$plugin_name, self::$version);
        }
        return self::$options;
    }

}
