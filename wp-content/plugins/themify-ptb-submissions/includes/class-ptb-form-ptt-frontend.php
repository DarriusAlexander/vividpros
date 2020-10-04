<?php

class PTB_Form_PTT_Frontend extends PTB_Form_PTT_Them {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param PTB_Options $options the plugin options instance
     * @param string themplate_id
     *
     */
    public function __construct($plugin_name, $version, $themplate_id = false) {
        parent::__construct($plugin_name, $version, $themplate_id);
        add_action('ptb_frontend_template', array($this, 'submission_template'), 10, 6);
    }

    /**
     * Frontend layout parametrs
     *
     * @since 1.0.0
     */
    public function add_fields($data = array()) {
        $isset = isset($data['data']);
        $data = $isset ? $data['data'] : array();
        $languages = PTB_Utils::get_all_languages();
        ?>
        <div class="ptb-frontend-loader">
            <img  src="<?php echo plugin_dir_url(dirname(__FILE__)) ?>public/img/loading.gif" width="32" height="32" alt="<?php _e('Loading...') ?>" />
        </div>
        <table class="form-table add-submission-form">
            <tr>
                <th scope="row"><label for="ptb-submission-form-title"><?php _e('Form Title', 'ptb-submission') ?></label></th>
                <td>
                    <?php PTB_CMB_Base::module_language_tabs('ptb_submission', $data, $languages, 'title', 'text', false, true) ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ptb-submission-form-email"><?php _e('Notification Email', 'ptb-submission') ?></label></th>
                <td>
                    <input type="email" value="<?php echo isset($data['email']) && $data['email'] ? sanitize_email($data['email']) : '' ?>" name="ptb_submission[email]" id="ptb-submission-form-email" />
                    <small class="ptb-submission-small-description"><?php _e("If empty, notification will send to admin's email address", 'ptb-submission') ?></small>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ptb-submission-form-fee"><?php _e('Submission Fee', 'ptb-submission') ?></label></th>
                <td>
                    <input type="checkbox" <?php if (isset($data['fee'])): ?>checked="checked"<?php endif; ?> name="ptb_submission[fee]" id="ptb-submission-form-fee" />
                    <label for="ptb-submission-form-fee"><strong><?php _e('Charge submission fee', 'ptb-submission') ?></strong></label>
                    <div class="ptb-submission-small-input">
                        <input type="number" value="<?php echo isset($data['amount']) && $data['amount'] > 0 ? floatval($data['amount']) : '' ?>" min="1" name="ptb_submission[amount]" />
                        <strong><?php _e('Amount', 'ptb-submission') ?></strong>
                        <small class="ptb-submission-small-description"><?php echo sprintf(__('Set payment gateway in PTB Submission > <a target="_blank" href="%s">Settings</a> > Payment', 'ptb-submission'), admin_url('admin.php?page=ptb-submission-settings')) ?></small>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ptb-submission-form-approve"><?php _e('Auto Publish', 'ptb-submission') ?></label></th>
                <td>
                    <input type="checkbox" <?php if (isset($data['approve'])): ?>checked="checked"<?php endif; ?> name="ptb_submission[approve]" id="ptb-submission-form-approve" />
                    <label for="ptb-submission-form-approve"><?php _e("Auto publish submitted posts without approval", 'ptb-submission') ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ptb-submission-form-success-message"><?php _e('Successful Message', 'ptb-submission') ?></label></th>
                <td>
                    <div class="ptb-submission-success-message">
                        <input type="radio" <?php if (!$isset || $data['success'] == 'm'): ?>checked="checked"<?php endif; ?> value="m" name="ptb_submission[success]" id="ptb-submission-form-success-message" />
                        <label for="ptb-submission-form-success-message"><?php _e("Display successful message", 'ptb-submission') ?></label>
                        <?php PTB_CMB_Base::module_language_tabs('ptb_submission', $data, $languages, 'm', 'textarea', false, TRUE); ?>
                    </div>
                    <div class="ptb-submission-success-redirect">
                        <input type="radio" <?php if (isset($data['success']) && $data['success'] == 'r'): ?>checked="checked"<?php endif; ?>  value="r" name="ptb_submission[success]" id="ptb-submission-form-success-redirect" />
                        <label for="ptb-submission-form-success-redirect"><?php _e("Redirect to thank you page URL:", 'ptb-submission') ?></label>
                        <input type="text" value="<?php echo isset($data['r']) && $data['r'] ? esc_url_raw($data['r']) : '' ?>" name="ptb_submission[r]" />
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ptb-submission-form-captcha"><?php _e('Captcha', 'ptb-submission') ?></label></th>
                <td>
                    <input type="checkbox" <?php if (isset($data['captcha']) || !$isset): ?>checked="checked"<?php endif; ?> name="ptb_submission[captcha]" id="ptb-submission-form-captcha" />
                    <label for="ptb-submission-form-captcha"><?php _e("Enable captcha to prevent spamming", 'ptb-submission') ?></label>
                </td>
            </tr>
        </table>
        <?php
    }

    public function submission_template($type, $id, array $args, array $module, array $post_support, array $languages) {

        switch ($type) {
            case 'taxonomies':
            case 'category':
                if (empty($this->post_taxonomies)) {
                    return;
                }
                $show_as = array('select' => __('Select', 'ptb-submission'),
                    'checkbox' => __('Checkbox', 'ptb-submission'),
                    'radio' => __('Radio', 'ptb-submission'),
                    'multiselect' => __('Multiple Select', 'ptb-submission'),
                    'autocomplete' => __('AutoComplete', 'ptb-submission')
                );
                ?>
                <?php if ($type == 'taxonomies'): ?>
                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_select_taxonomies"><?php _e('Select Taxonomies', 'ptb-submission') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">                          
                            <select class="ptb-select" id="ptb_select_taxonomies" name="[<?php echo $type ?>][taxonomies]">
                                <?php foreach ($this->post_taxonomies as $tax => $tax_name): ?>
                                    <option
                                        <?php if (isset($module['taxonomies']) && $module['taxonomies'] == $tax): ?>selected="selected"<?php endif; ?>
                                        value="<?php echo $tax ?>"><?php echo $tax_name ?></option>
                                    <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_show_as"><?php _e('Show as', 'ptb-submission') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_show_as" name="[<?php echo $type ?>][show_as]">
                                <?php foreach ($show_as as $key => $input): ?>
                                    <option <?php if (isset($module['show_as']) && $module['show_as'] == $key): ?>selected="selected"<?php endif; ?> value="<?php echo $key ?>">
                                        <?php echo $input ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_allow"><?php _e('Allow users to add term(s)', 'ptb-submission') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_<?php echo $id ?>_allow" type="checkbox" name="[<?php echo $id ?>][allow]"
                               value="1" <?php if (isset($module['allow'])): ?>checked="checked"<?php endif; ?> />
                    </div>
                </div>
                <?php
                break;
            case 'thumbnail':
            case 'image':
                $max_upload = wp_max_upload_size();
                $size = PTB_Submissiion_Options::max_upload_size(isset($module['size']) ? $module['size'] : NULL);
                $can_be_allowed = array_keys(PTB_Submissiion_Options::get_allow_ext());
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_extensions"><?php _e('Allowed extensions', 'ptb-submission') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <select size="10" class="ptb-select" multiple="multiple" name="[<?php echo $id ?>][extensions][arr]" id="ptb_<?php echo $id ?>_extensions">
                            <option <?php if (!isset($module['extensions']) || isset($module['extensions']) && in_array('all', $module['extensions'])): ?>selected="selected"<?php endif; ?> value="all"><?php _e('ALL', 'ptb-submission') ?></option>
                            <?php foreach ($can_be_allowed as $ext): ?>
                                <option  <?php if (isset($module['extensions']) && in_array($ext, $module['extensions']) && !in_array('all', $module['extensions'])): ?>selected="selected"<?php endif; ?>  value="<?php echo $ext ?>"><?php echo $ext ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_size"><?php _e('Maximum image size(b)', 'ptb-submission') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input type="number" name="[<?php echo $id ?>][size]" value="<?php echo $size ?>" min="1" max="<?php echo $max_upload ?>" />
                    </div>
                </div>
                <?php
                break;
            case 'editor':
                ?>
                <input type="hidden" name="[<?php echo $id ?>][editor]" value="1" />
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_html"><?php _e('Use html editor(not recomended)', 'ptb-submission') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input id="ptb_<?php echo $id ?>_html" type="checkbox" name="[<?php echo $id ?>][html]"
                               value="1" <?php if (isset($module['html'])): ?>checked="checked"<?php endif; ?> />
                    </div>
                </div>
                <?php
                break;
            case 'link_button':
                ?>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'text', __('Text for link label', 'ptb-submission')); ?>
                </div>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'url', __('Text for link url', 'ptb-submission')); ?>
                </div>
                <?php
                break;
            case 'number':
                $min = isset($module['min']) && $module['min'] ? $module['min'] : '';
                $max = isset($module['max']) && $module['max'] ? $module['max'] : '';
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Min/Max Values', 'ptb-submission') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <label for="ptb_<?php echo $id ?>_min"><?php _e('Min', 'ptb-submission') ?></label>
                        <input id="ptb_<?php echo $id ?>_min" type="number" name="[<?php echo $id ?>][min]" step="0.01" value="<?php echo $min ?>"/>
                        >
                        <label for="ptb_<?php echo $id ?>_max"><?php _e('Max', 'ptb-submission') ?></label>
                        <input id="ptb_<?php echo $id ?>_range" type="number" name="[<?php echo $id ?>][max]" step="0.01" value="<?php echo $max ?>"/>
                    </div>
                </div>
                <?php
                break;
            case 'custom_text':
            case 'custom_image':
                $this->get_main_fields($id, '', $module, $languages);
                break;
            case 'user_password':
                ?>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'label_confirm', __('Label of Confirm Password', 'ptb-submission')); ?>
                </div>
                <?php
                break;
            default:
                ?>
                <?php do_action('ptb_submission_template_' . $type, $id, $args, $module, $post_support, $languages); ?>
                <input type="hidden" name="[<?php echo $id ?>][<?php echo $id ?>]"/>
                <?php
                break;
        }
        $post_support[] = 'user_email';
        $post_support[] = 'user_name';
        $post_support[] = 'user_password';
        if (!in_array($type, $post_support)) {
            ?>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>_show_description"><?php _e('Show metabox description', 'ptb-submission') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input id="ptb_<?php echo $id ?>_show_description" type="checkbox" name="[<?php echo $id ?>][show_description]"
                           value="1" <?php if (isset($module['show_description'])): ?>checked="checked"<?php endif; ?> />
                </div>
            </div>
            <?php
        }
        ?>
        <?php if (!in_array($type, array('custom_image', 'custom_text'))): ?>
            <div class="ptb_back_active_module_row">
                <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'label', __('Label', 'ptb')); ?>
            </div>
        <?php endif; ?>
        <?php
    }

}
