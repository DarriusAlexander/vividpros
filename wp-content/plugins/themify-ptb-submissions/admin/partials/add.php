<?php
$post_types = $this->options->get_custom_post_types();
$submission_options = PTB_Submission::get_option();
?>
<form action="<?php echo admin_url('admin-ajax.php') ?>" method="post" id="<?php echo $this->plugin_name ?>-form-save">
    <input type="hidden" name="action" value="save_ajax" />
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '-save') ?>" name="_nonce" />
    <div class="ptb-frontend-loader"></div>
    <table class="form-table add-submission-form">
        <tr>
            <th scope="row"><label for="<?php echo $this->plugin_name ?>-cpt"><?php _e('Submission Post Type', 'ptb-submission') ?></label></th>
            <td>
                <div class="ptb_custom_select">
                    <select required="required" id="<?php echo $this->plugin_name ?>-cpt" name="post_type">
                        <option value=""><?php _e('Select Post Type', 'ptb-submission') ?></option>
                        <?php if (!empty($post_types)): ?>
                            <?php foreach ($post_types as $post_type): ?>
                                <?php $disable = $submission_options->get_submission_template($post_type->slug); ?>
                                <option <?php if (!$disable): ?>value="<?php echo $post_type->slug ?>"<?php else: ?>disabled="disabled"<?php endif; ?>><?php echo PTB_Utils::get_label($post_type->singular_label) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <small class="ptb-submission-small-description"><?php _e('Select a post type where this form should submit to. To avoid conflicts, this section is not editable after is form created.', 'ptb-submission') ?></small>
            </td>
        </tr>
        <tr>
            <th><?php submit_button(__('Next', 'ptb-submission')); ?></th>
            <td></td>
        </tr>
    </table>
</form>