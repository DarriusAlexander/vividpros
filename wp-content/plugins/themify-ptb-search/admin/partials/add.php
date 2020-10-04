<?php
$post_types = $this->options->get_custom_post_types();
?>
<form action="<?php echo admin_url('admin-ajax.php') ?>" method="post" id="<?php echo $this->plugin_name ?>-form-save">
    <input type="hidden" name="action" value="ptb_search_add_template" />
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '-save') ?>" name="_nonce" />
    <div class="ptb-search-loader"></div>
    <table class="form-table add-search-form">
        <tr>
            <th scope="row"><label for="<?php echo $this->plugin_name ?>-title"><?php _e('Form Name', 'ptb-search') ?></label></th>
            <td><input type="text" required="required" id="<?php echo $this->plugin_name ?>-title" name="title"/></td>
        </tr>
        <tr>
            <th scope="row"><label for="<?php echo $this->plugin_name ?>-cpt"><?php _e('Post Type', 'ptb-search') ?></label></th>
            <td>
                <div class="ptb_custom_select">
                    <select required="required" id="<?php echo $this->plugin_name ?>-cpt" name="post_type">
                        <option value=""><?php _e('Select Post Type', 'ptb-search') ?></option>
                        <?php if (!empty($post_types)): ?>
                            <?php foreach ($post_types as $post_type): ?>
                                <option value="<?php echo $post_type->slug ?>"><?php echo PTB_Utils::get_label($post_type->singular_label) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <th><?php submit_button(__('Next', 'ptb-search')); ?></th>
            <td></td>
        </tr>
    </table>
</form>