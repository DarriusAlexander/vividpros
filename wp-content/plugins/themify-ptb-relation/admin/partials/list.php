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
$cptListTable = new PTB_Relation_List_Table_CPT($this->plugin_name, $this->version, $this->options);
$cptListTable->prepare_items();
if (!defined('DOING_AJAX')) {
   // place js config array for plupload
    $plupload_init = array(
        'runtimes' => 'html5,silverlight,flash,html4',
        'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
        'container' => 'plupload-upload-ui', // will be adjusted per uploader
        'drop_element' => 'drag-drop-area', // will be adjusted per uploader
        'file_data_name' => 'async-upload', // will be adjusted per uploader
        'multiple_queues' => true,
        'max_file_size' => wp_max_upload_size() . 'b',
        'url' => admin_url('admin-ajax.php'),
        'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
        'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
        'filters' => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
        'multipart' => true,
        'urlstream_upload' => true,
        'multi_selection' => false, // will be added per uploader
        // additional post data to send to our ajax hook
        'multipart_params' => array(
            '_ajax_nonce' => "", // will be added per uploader
            'action' => 'plupload_action', // the ajax action name
            'imgid' => 0 // will be added per uploader
        )
    );
    ?>
    <script type="text/javascript">
        var ptb_plupload_config =<?php echo wp_json_encode($plupload_init); ?>;
    </script>
    <div class="wrap">
        <h2>
    <?php _e('PTB Relations', 'ptb-relation'); ?>
            <?php echo sprintf('<a onclick="javascript:void(0);" href="' . admin_url('admin-ajax.php?page=%s&action=%s&_nonce=%s') . '" class="add-new-h2 ptb_custom_lightbox" title="%s">%s</a>', $_REQUEST['page'], 'ptb_relation_add', wp_create_nonce($this->plugin_name . '-add'), __('New Relation', 'ptb-relation'), __('Add New', 'ptb-relation')); ?>
            <?php echo sprintf('<a onclick="javascript:void(0);" href="' . admin_url('admin-ajax.php?page=%s&action=%s&_nonce=%s') . '" class="add-new-h2 ptb_custom_lightbox" title="%s">%s</a>', $_REQUEST['page'], 'ptb_relation_get_import', wp_create_nonce($this->plugin_name . '-import'), __('Import Relation', 'ptb-relation'), __('Import', 'ptb-relation')); ?>
        </h2>
            <?php settings_errors($this->plugin_name . '_notices'); ?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form  method="post" action="">
            <div class="ptb-relation-loader"></div>
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <!-- Now we can render the completed list table -->
            <div  id="ptb-relation-list-form">
                <?php $cptListTable->display() ?>
            </div>
        </form>
    </div>
<?php
} else {
    $cptListTable->display();
}
