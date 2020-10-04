<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
global $sitepress;
if (isset($sitepress)) {
    include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
}

/**
 * The List table class which extends WP_List_Table Wordpress Admin core class
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Submission_Posts_Table_CPT extends WP_List_Table {

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
    private static $nonce = false;
    private static $record_count = 0;
    private static $posts_per_page = 10;

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
            'singular' => 'ptb_submission_post', //singular name of the listed records
            'plural' => 'ptb_submission_posts', //plural name of the listed records
            'ajax' => TRUE              //does this table support ajax?
        ));
        self::$nonce = wp_create_nonce('ptb_submission_post_delete');
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $options;
    }

    private function prepare_data() {

        if (!empty($this->options->option_post_type_templates)) {
            $submission_types = array();
            $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'date';
            $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
            foreach ($this->options->option_post_type_templates as $id => $t) {
                if (isset($t['frontend'])) {
                    if (isset($t['frontend']['data']) && isset($t['post_type'])) {
                        $submission_types[] = $t['post_type'];
                    }
                }
            }
            $args = array(
                'posts_per_page' => self::$posts_per_page,
                'offset' => isset($_REQUEST['paged']) && $_REQUEST['paged'] > 0 ? self::$posts_per_page * (intval($_REQUEST['paged']) - 1) : 0,
                'orderby' => $orderby,
                'order' => $order,
                'post_type' => $submission_types,
                'post_status' => 'any',
                'meta_key' => 'ptb_submission_is_post',
                'suppress_filters' => false
            );
            if (isset($_REQUEST['submission']) && !empty($_REQUEST['submission'])) {
                $filter = sanitize_post($_REQUEST['submission'], 'display');
                if (isset($filter['type']) && $filter['type']) {
                    $args['post_type'] = $filter['type'];
                }
                if (isset($filter['status']) && $filter['status']) {
                    $args['post_status'] = $filter['status'];
                }

                if (isset($filter['s']) && trim($filter['s'])) {
                    $args['s'] = sanitize_text_field($filter['s']);
                    add_filter('posts_search', array($this, 'pre_search'), 500, 2);
                }
                if ((isset($filter['from']) && $filter['from']) || (isset($filter['to']) && $filter['to'])) {
                    $args['date_query'] = array(
                        'compare' => 'BETWEEN',
                        'inclusive' => true,
                        'column' => 'post_date'
                    );
                    if (isset($filter['from'])) {
                        $args['date_query']['after'] = $filter['from'];
                    }
                    if (isset($filter['to'])) {
                        $args['date_query']['before'] = $filter['to'];
                    }
                }
                if (isset($filter['author']) && $filter['author'] > 0) {
                    $args['author'] = intval($filter['author']);
                }
                //sortable works only with GET
                $_GET['order'] = $order;
                $_GET['orderby'] = $orderby;
            }
            $_posts = new WP_Query($args);
            self::$record_count = $_posts->found_posts;
            wp_reset_postdata();
            return $_posts->get_posts();
        }
        return array();
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
        return $item->{$column_name};
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
    function column_post_title($item) {
        //Build row actions
        $actions = array();
        if (current_user_can('edit_others_posts')) {
            $actions['edit'] = sprintf(
                    '<a href="' . get_edit_post_link($item->ID) . '">%1$s</a>', __('Edit', 'ptb-submission')
            );
            if ($item->post_status != 'publish') {
                $actions['published'] = sprintf(
                        '<a href="' . admin_url('admin-ajax.php?action=%1$s&id=%2$s&nonce=%3$s&approve=1') . '" class="ptb-submission-post-action ptb-submission-post-apptove">%4$s</a>', 'ptb_submission_post_action', $item->ID, self::$nonce, __('Approve', 'ptb-submission')
                );
            }
            $actions['view'] = sprintf(
                    '<a target="_blank" href="' . add_query_arg(array('preview' => 'true'), get_post_permalink($item->ID)) . '">%1$s</a>', __('View', 'ptb-submission')
            );
        }
        if (current_user_can('delete_others_posts')) {
            $actions['trash'] = sprintf(
                    '<a href="' . admin_url('admin-ajax.php?action=%1$s&id=%2$s&trash=1&nonce=%3$s') . '" class="ptb-submission-post-action ptb-submission-post-delete">%4$s</a>', 'ptb_submission_post_action', $item->ID, self::$nonce, __('Trash', 'ptb-submission')
            );
            $actions['delete'] = sprintf(
                    '<a href="' . admin_url('admin-ajax.php?action=%1$s&id=%2$s&nonce=%3$s') . '" class="ptb-submission-post-action ptb-submission-post-delete">%4$s</a>', 'ptb_submission_post_action', $item->ID, self::$nonce, __('Delete', 'ptb-submission')
            );
        }
        return !empty($actions) ? sprintf('%1$s %2$s', $item->post_title, $this->row_actions($actions)) : false;
    }

    function column_paid($item) {
        global $sitepress;
        $post_id = $item->ID;
        if (isset($sitepress)) {
            $post_id = icl_object_id($item->ID, $item->post_type, true, PTB_Utils::get_default_language_code());
        }
        $paid = get_post_meta($post_id, 'ptb_submission_payment_data', TRUE);
        $ptb_submissions = PTB_Submission::get_option();
        return $paid ? $ptb_submissions->get_price_format(false, $paid['currency'], $paid['price']) : '';
    }

    function column_post_author($item) {
        $author = '';
        if ($item->post_author) {
            $user = get_user_by('id', $item->post_author);
            if ($user) {
                $author = '<a href="' . get_edit_user_link($user->ID) . '">' . $user->user_login . '</a>';
            }
        }
        return $author;
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
        return sprintf(
                '<input type="checkbox" name="posts[]" value="%1$s" />', $item->ID                //The value of the checkbox should be the record's id
        );
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
        $actions = array(
            'approve' => __('Approve', 'ptb-submission'),
            'delete' => __('Delete', 'ptb-submission'),
            'trash' => __('Move to Trash', 'ptb-submission')
        );

        return $actions;
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
            'post_title' => __('Name', 'ptb-submission'),
            'post_status' => __('Post Status', 'ptb-submission'),
            'post_type' => __('Post Type', 'ptb-submission'),
            'post_author' => __('Post Author', 'ptb-submission'),
            'paid' => __('Post Paid', 'ptb-submission'),
            'post_date' => __('Date', 'ptb-submission')
        );

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
            'post_type' => array('type', false), //true means it's already sorted
            'post_title' => array('title', false),
            'post_author' => array('author', false),
            'post_date' => array('date', true)
        );

        return $sortable_columns;
    }

}
