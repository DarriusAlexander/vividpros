<?php
/**
 * Plugin Name:       PTB Search
 * Plugin URI:        http://themify.me
 * Description:       Addon to use with Post Type Builder plugin that allows users to create search forms for ptb custom post types
 * Version:           1.0.7
 * Author:            Themify
 * Author URI:        http://themify.me
 * Text Domain:       ptb-search
 * Domain Path:       /languages
 *
 * @link              http://themify.me
 * @since             1.0.0
 * @package           PTB
 */
// If this file is called directly, abort.

defined('ABSPATH') or die('-1');
define('PTB_SEARCH_SLUG', 'ptb-search');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (is_plugin_active('themify-ptb/post-type-builder.php')) {
    if (is_plugin_active_for_network('themify-ptb/post-type-builder.php') && class_exists('PTB')){
        ptb_search();
    } else {
        add_action('ptb_loaded', 'ptb_search');
    }
} else {
    add_action('admin_notices', 'ptb_search_admin_notice');
}

function ptb_search() {
    include_once plugin_dir_path(__FILE__) . 'includes/class-ptb-search.php';
    $version = PTB::get_plugin_version(__FILE__);
    new PTB_Search($version);
}

function ptb_search_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e('Please, activate Post Type Builder plugin" to "In order to activate PTB Search, you need to have Post Type Builder plugin activated first.', 'ptb-search'); ?></p>
    </div>
    <?php
    deactivate_plugins(plugin_basename(__FILE__));
}

function ptb_search_activate() {

    $args = array(
        'name' => PTB_SEARCH_SLUG,
        'post_type' => 'page',
        'post_status' => 'any',
        'numberposts' => 1
    );
    $submissions_page = get_posts($args);
    if (!$submissions_page) {
        wp_insert_post(array(
            'post_name' => PTB_SEARCH_SLUG,
            'post_title' => __('PTB Search', 'ptb-search'),
            'post_status' => 'publish',
            'post_type' => 'page'
        ));
    }
}

register_activation_hook(__FILE__, 'ptb_search_activate');
/**
 * Initialize updater.
 * 
 * @since 1.0.0
 */
add_action('ptb_check_update', 'ptb_search_update');

function ptb_search_update() {
    $plugin_basename = plugin_basename(__FILE__);
    $plugin_data = get_plugin_data(trailingslashit(plugin_dir_path(__FILE__)) . basename($plugin_basename));
    $name = trim(dirname($plugin_basename), '/');
    new PTB_Update_Check(array(
        'name' => $name,
        'nicename' => $plugin_data['Name'],
        'update_type' => 'plugin',
            ), $plugin_data['Version'], $name);
}
