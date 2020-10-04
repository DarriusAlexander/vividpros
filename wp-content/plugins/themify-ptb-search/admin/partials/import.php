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
?>
<?php
$message = __('Import will overwrite any existing search template with the same template name/slug. Press OK to continue, Cancel to stop.', 'ptb-search');
$extensions = array(
    array(
        'title' => __('Json file', 'ptb-search'),
        'extensions' => "json"
    ),
    array(
        'title' => __('Archive file', 'ptb-search'),
        'extensions' => "zip"
    )
);
?>
<form method="post" action="" id="<?php echo $this->plugin_name ?>-import-form"  enctype="multipart/form-data">
    <input type="hidden" value="ptb_search_import" name="action" />
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '-import') ?>" name="_nonce" />
    <div class="ptb-search-loader"></div>
    <a data-formats='<?php echo wp_json_encode($extensions) ?>' data-name="import" data-confirm="<?php echo $message ?>" id="<?php echo $this->plugin_name ?>-import-btn" class="<?php echo $this->plugin_name ?>-file-btn" href=""><?php _e('Import', 'ptb-search') ?></a>
    <div class="ptb-search-error"></div>
</form>
<script type="text/javascript">
    jQuery(document).ready(function () {
        ptb_create_pluploader(jQuery('#<?php echo $this->plugin_name ?>-import-btn'));
    });
</script>
