<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
$cptListTable = new PTB_Submission_Posts_Table_CPT($this->plugin_name, $this->version, $this->options);
$cptListTable->prepare_items();
if (!defined('DOING_AJAX')) {

    $submission_types = array();
    foreach ($this->options->option_post_type_templates as $id => $t) {
        if (isset($t['frontend'])) {
            if (isset($t['frontend']['data']) && isset($t['post_type'])) {
                $cpt = $this->options->get_custom_post_type($t['post_type']);
                $submission_types[$t['post_type']] = PTB_Utils::get_label($cpt->singular_label);
            }
        }
    }
    $authors = get_users(array('count_total' => false,
        'fields' => array('user_login', 'ID'),
        'number' => 10000,
        'offset' => 0,
        'orderby' => 'login',
        'order' => 'ASC',
        'role' => 'ptb',
        'blog_id' => get_current_blog_id()
            )
    );
    ?>
    <div class="wrap">
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <h1><?php _e('Submission Posts', 'ptb-submission') ?></h1>
        <form action="" method="get" id="ptb-submission-posts-filter">
            <input type="hidden" name="action" value="ptb_submission_posts_filter" />
            <input type="hidden" name="paged" value="" />
            <input type="hidden" name="order" value="" />
            <input type="hidden" name="orderby" value="" />
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('ptb_submission_posts') ?>" />
            <div class="tablenav top">
                <select data-placeholder="<?php _e('All Post Types'); ?>" name="submission[type][]" class="ptb-select" multiple="multiple">
                    <?php if ($submission_types): ?>
                        <?php foreach ($submission_types as $type => $name): ?>
                            <option value="<?php echo $type ?>"><?php echo $name ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <select data-placeholder="<?php _e('All Statuses'); ?>" name="submission[status][]" class="ptb-select" multiple="multiple">
                    <option value="draft"><?php _e('Draft', 'ptb-submission') ?></option>
                    <option value="pending"><?php _e('Pending', 'ptb-submission') ?></option>
                    <option value="publish"><?php _e('Publish', 'ptb-submission') ?></option>
                </select>
                <select name="submission[author]" class="ptb-select">
                    <option value="0"><?php _e('All Authors'); ?></option>
                    <?php if ($authors): ?>
                        <?php foreach ($authors as $a): ?>
                            <option value="<?php echo $a->ID; ?>"><?php esc_attr_e($a->user_login) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <input placeholder="<?php _e('Date From', 'ptb-submission') ?>" type="text" name="submission[from]" id="ptb-submission-posts-from" class="ptb-submission-datepicker" />
                <input placeholder="<?php _e('Date to', 'ptb-submission') ?>" type="text" name="submission[to]" id="ptb-submission-posts-to" class="ptb-submission-datepicker" />
                <input placeholder="<?php _e('Title', 'ptb-submission') ?>" type="text" name="submission[s]" />
            </div>
            <?php submit_button(__('Search Posts', 'ptb-submission')); ?>
        </form>
        <form  method="post" action="" id="ptb-submission-posts-form">
            <input type="hidden" name="action" value="ptb_submission_post_action" />
            <input type="hidden" name="method" value="" />
            <div class="ptb-frontend-loader"></div>
            <!-- Now we can render the completed list table -->
            <div id="ptb-submission-posts-wrap">
                <?php $cptListTable->display(); ?>
            </div>
        </form>
    </div>

    <?php
}
else {
    $cptListTable->display();
    wp_die();
}

