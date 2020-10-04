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
if (current_user_can('delete_users')) {
    PTB_Submission_Users_Table_CPT::$nonce = wp_create_nonce('bulk-users');
}
$cptListTable = new PTB_Submission_Users_Table_CPT($this->plugin_name, $this->version, $this->options);
$cptListTable->prepare_items();
?>
<div class="wrap">
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <h1><?php _e('PTB Authors', 'ptb-submission') ?></h1>
    <?php if (isset($_GET['update'])): ?>
        <?php
        $message = '';
        if (isset($_GET['delete_count']) && $_GET['delete_count'] > 0) {
            $delete_count = intval($_GET['delete_count']);
            $message = $delete_count == 1 ? __('User deleted.', 'ptb-submission') : _n('%s user deleted.', '%s users deleted.', $delete_count, 'ptb-submission');
            $message = sprintf($message, number_format_i18n($delete_count));
        } elseif ($_GET['update'] == 'remove') {
            $message = __('User removed from this site.', 'ptb-submission');
        }
        if ($message) {
            add_settings_error('ptb-submission-users', '', $message, 'updated');
        }
        ?>
    <?php endif; ?>
    <?php settings_errors('ptb-submission-users') ?>
    <form action="" method="get">
        <?php $cptListTable->search_box(__('Search Users'), 'ptb-submission-users'); ?>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    </form>
    <form  method="get" id="ptb-submission-users-form" action="<?php echo admin_url('users.php') ?>">
        <?php $cptListTable->display(); ?>
        <?php if (PTB_Submission_Users_Table_CPT::$nonce): ?>
            <input type="hidden"  name="_wpnonce" value="<?php echo PTB_Submission_Users_Table_CPT::$nonce ?>"/>
            <input type="hidden"  name="wp_http_referer" value="<?php echo admin_url('admin.php?page=' . $_REQUEST['page']) ?>"/>
        <?php endif; ?>
    </form>
</div>