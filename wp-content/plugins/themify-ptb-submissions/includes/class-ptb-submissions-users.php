<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * The List table class which extends WP_List_Table Wordpress Admin core class
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * The List table class which extends WP_List_Table Wordpress Admin core class
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Submission_Users_Table_CPT extends WP_List_Table {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    private $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of the plugin.
     */
    private $version;

    /**
     * The plugin options object.
     *
     * @since    1.0.0
     * @access   private
     * @var      PTB_Options $version The options object.
     */
    private $options;
    private static $record_count = 0;
    private static $posts_per_page = 10;
    private static $submission_types = array();
    public static $nonce = false;
    private static $current_id = false;

    /**     * ***********************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     * *************************************************************************
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options
     */
    function __construct($plugin_name, $version, $options) {

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'ptb_submission_user', //singular name of the listed records
            'plural' => 'ptb_submission_users', //plural name of the listed records
            'ajax' => TRUE              //does this table support ajax?
        ));

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $options;
        foreach ($this->options->option_post_type_templates as $id => $t) {
            if (isset($t['frontend'])) {
                if (isset($t['frontend']['data']) && isset($t['post_type'])) {
                    self::$submission_types[] = $t['post_type'];
                }
            }
        }
        self::$current_id = get_current_user_id();
    }

    private function prepare_data() {



        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'date';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'ASC';

        $args = array(
            'number' => self::$posts_per_page,
            'offset' => isset($_REQUEST['paged']) && $_REQUEST['paged'] > 0 ? self::$posts_per_page * (intval($_REQUEST['paged']) - 1) : 0,
            'orderby' => $orderby,
            'order' => $order,
            'role' => 'ptb',
            'blog_id' => get_current_blog_id()
        );
        if (isset($_REQUEST['s']) && trim($_REQUEST['s'])) {
            $args['search'] = is_email($_REQUEST['s']) ? sanitize_email($_REQUEST['s']) : sanitize_text_field($_REQUEST['s']);
            $args['search'] = '*' . $args['search'] . '*';
            $args['search_columns'] = array('user_login', 'user_email', 'user_nicename', 'user_url', 'ID');
        }
        $users = new WP_User_Query($args);

        self::$record_count = $users->get_total();
        return $users->get_results();
    }

    /**     * ***********************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     *
     * @return string Text or HTML to be placed inside the column <td>
     * ************************************************************************ */
    function column_default($item, $column_name) {
        return $column_name == 'email' ? $item->user_email : $item->{$column_name};
    }

    /**     * ***********************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     * ************************************************************************ */
    function get_bulk_actions() {
        if (self::$nonce) {
            return is_multisite() ? array('remove' => __('Remove', 'ptb-submission')) : array('delete' => __('Delete', 'ptb-submission'));
        }
    }

    /**     * ***********************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     * ************************************************************************ */
    function column_cb($item) {
        return self::$nonce && $item->ID != self::$current_id ? sprintf('<input type="checkbox" name="users[]" value="%1$s" />', $item->ID) : '';
    }

    /**     * ***********************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     *
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     *
     *
     * @see WP_List_Table::::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     * ************************************************************************ */
    function column_username($item) {

        //Build row actions
        $ulink = get_edit_user_link($item->ID);
        $actions = array(
            'edit' => sprintf(
                    '<a href="' . $ulink . '">%1$s</a>', __('Edit', 'ptb-submission')
            )
        );

        if (self::$nonce && $item->ID != self::$current_id) {
            if (is_multisite()) {
                $action = 'remove';
                $name = __('Remove', 'ptb-submission');
            } else {
                $action = 'delete';
                $name = __('Delete', 'ptb-submission');
            }

            $actions['delete'] = sprintf(
                    '<a href="' . admin_url('users.php?action=%1$s&user=%2$s&_wpnonce=%4$s&wp_http_referer=%5$s') . '" class="ptb-submission-post-delete">%3$s</a>', $action, $item->ID, $name, self::$nonce, admin_url('admin.php?page=' . $_REQUEST['page'])
            );
        }
        return sprintf('%1$s %2$s', get_avatar($item->ID, 32) . '<strong><a href="' . $ulink . '">' . $item->user_login . '</a></strong>', $this->row_actions($actions)
        );
    }

    function column_name($item) {
        return $item->first_name . ' ' . $item->last_name;
    }

    function column_posts($item) {
        $posts = new WP_Query(array(
            'posts_per_page' => 1,
            'offset' => 0,
            'orderby' => 'ID',
            'order' => 'ASC',
            'post_type' => self::$submission_types,
            'post_status' => 'publish,pending',
            'meta_key' => 'ptb_submission_is_post',
            'author' => $item->ID,
            'suppress_filters' => false
                )
        );
        wp_reset_postdata();
        return $posts->found_posts;
    }

    /**     * ***********************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     * ************************************************************************ */
    function prepare_items() {

        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);




        /*         * *********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         * ******************************************************************** */


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $this->get_pagenum();

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $this->prepare_data();


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array(
            'total_items' => self::$record_count, //WE have to calculate the total number of items
            'per_page' => self::$posts_per_page, //WE have to determine how many items to show on a page
        ));
    }

    /**     * ***********************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     * ************************************************************************ */
    function get_columns() {

        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'username' => __('UserName', 'ptb-submission'),
            'name' => __('Name', 'ptb-submission'),
            'email' => __('E-mail', 'ptb-submission'),
            'posts' => __('Posts', 'ptb-submission')
        );
        if (!self::$nonce) {
            unset($columns['cb']);
        }
        return $columns;
    }

    /**     * ***********************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     * ************************************************************************ */
    function get_sortable_columns() {
        $sortable_columns = array(
            'username' => array('login', false), //true means it's already sorted
            'name' => array('name', true),
            'email' => array('email', false)
        );

        return $sortable_columns;
    }

}
