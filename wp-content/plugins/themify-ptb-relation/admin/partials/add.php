<?php
$post_types = $this->options->get_custom_post_types();
$relation_options = PTB_Relation::get_option();
?>
<form action="<?php echo admin_url('admin-ajax.php') ?>" method="post" id="<?php echo $this->plugin_name ?>-form-save">
    <input type="hidden" name="action" value="ptb_relation_add_template" />
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '-save') ?>" name="_nonce" />
    <div class="ptb-relation-loader"></div>
    <table class="form-table add-relation-form">
        <tr>
            <th scope="row"><label for="<?php echo $this->plugin_name ?>-cpt"><?php _e('Relation Post Type', 'ptb-relation') ?></label></th>
            <td>
                <div class="ptb_custom_select">
                    <select required="required" id="<?php echo $this->plugin_name ?>-cpt" name="post_type">
                        <option value=""><?php _e('Select Post Type', 'ptb-relation') ?></option>
                        <?php if (!empty($post_types)): ?>
                            <?php foreach ($post_types as $post_type): ?>
                                <?php 
                                      $post_type_relations = $relation_options->get_relation_cmb($post_type->slug);
                                ?>
                                <?php if(!empty($post_type_relations)):?>
                                    <?php foreach($post_type_relations as $s=>$r):?>
                                        <?php 
                                        $post_types = explode('@', $s);
                                        $disable = $relation_options->get_relation_template($post_types[1],$post_types[0]); ?>
                                        <option <?php if (!$disable): ?>value="<?php echo $s ?>"<?php else: ?>disabled="disabled"<?php endif; ?>><?php echo $r ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <br/>
                <small><?php _e('Select a post type where this form should submit to. To avoid conflicts, this section is not editable after is form created.', 'ptb-relation') ?></small>
            </td>
        </tr>
        <tr>
            <th><?php submit_button(__('Next', 'ptb-relation')); ?></th>
            <td></td>
        </tr>
    </table>
</form>