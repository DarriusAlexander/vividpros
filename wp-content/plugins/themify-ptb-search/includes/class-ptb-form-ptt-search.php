<?php

class PTB_Form_PTT_Search extends PTB_Form_PTT_Them {

    private $template_id = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     * @param string themplate_id
     *
     */
    public function __construct($plugin_name, $version, $themplate_id = false) {
        $this->template_id = $themplate_id;
        parent::__construct($plugin_name, $version, $themplate_id);
        add_action('ptb_search_template', array($this, 'search_template'), 10, 6);
    }

    /**
     * Search layout parametrs
     *
     * @since 1.0.0
     */
    public function add_fields($data = array()) {
        
    }

    public function search_template($type, $id, array $args, array $module, array $post_support, array $languages) {
        $empty = empty($module);
        $show_as = array(
            'radio' => __('Radio', 'ptb-search'),
            'checkbox' => __('Checkbox', 'ptb-search'),
            'select' => __('Select', 'ptb-search')
        );
        ?>

        <?php if (!in_array($type, array('custom_image', 'custom_text','button'))): ?>
            <div class="ptb_back_active_module_row">
                <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'label', __('Label', 'ptb')); ?>
            </div>
        <?php endif; ?>

        <?php
        switch ($type) {
            case 'number':
                $show_as = array(
                    'text' => __('Text', 'ptb-search'),
                    'slider' => 'Min/Max Slider'
                );
                ?>  
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Input Option', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <?php foreach ($show_as as $key => $input): ?>
                            <input type="radio" id="ptb_<?php echo $id ?>[<?php echo $key ?>]" name="[<?php echo $id ?>][show_as]" value="<?php echo $key ?>"
                                   <?php if ((isset($module['show_as']) && $module['show_as'] == $key) || ($empty && $key == 'text')): ?>checked="checked"<?php endif; ?> />
                            <label for="ptb_<?php echo $id ?>[<?php echo $key ?>]"><?php echo $input ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                break;
            case 'text':
                break;
            case 'taxonomies':
            case 'category':
            case 'post_tag':
                $show_as['autocomplete'] = __('AutoComplete', 'ptb-search');
                $show_as['multiselect'] = __('Multiple Select', 'ptb-search');
                if($type==='post_tag' && empty($module['show_as'])){
                    $module['show_as'] = 'autocomplete';
                }
                $orderby = array(
                    'id'=>__('ID','ptb-search'),
                    'count '=>__('Count ','ptb-search'),
                    'name'=>__('Name','ptb-search'),
                    'slug'=>__('Slug','ptb-search'),
                    'description'=>__('Description','ptb-search'),
                    'term_group'=>__('Term Group','ptb-search')
                    );
                $order = array('ASC'=>__('ASC','ptb-search'),'DESC'=>__('DESC','ptb-search'));
                ?>
                <?php if ($type == 'taxonomies'): ?>
                    <div class="ptb_back_active_module_row">
                        <div class="ptb_back_active_module_label">
                            <label for="ptb_select_taxonomy"><?php _e('Select Taxonomy', 'ptb-search') ?></label>
                        </div>
                        <div class="ptb_back_active_module_input">                          
                            <select class="ptb-select" id="ptb_select_taxonomy" name="[<?php echo $id ?>][taxonomy]">
                                <?php foreach ($this->post_taxonomies as $tax => $tax_name): ?>
                                    <option <?php if (isset($module['taxonomy']) && $module['taxonomy'] == $tax): ?>selected="selected"<?php endif; ?> value="<?php echo $tax ?>">
                                        <?php echo $tax_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Input Option', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input ptb_change_disable" data-action="1" data-disabled="autocomplete">
                        <?php foreach ($show_as as $key => $input): ?>
                            <input type="radio" id="ptb_<?php echo $id ?>[<?php echo $key ?>]" name="[<?php echo $id ?>][show_as]" value="<?php echo $key ?>"
                                   <?php if ((isset($module['show_as']) && $module['show_as'] == $key) || ($empty && $key == 'select')): ?>checked="checked"<?php endif; ?> />
                            <label for="ptb_<?php echo $id ?>[<?php echo $key ?>]"><?php echo $input ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="ptb_back_active_module_row ptb_maybe_disabled">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Orderby', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <select class="ptb-select" name="[<?php echo $id ?>][orderby]">
                            <?php foreach ($orderby as $k => $v): ?>
                                <option <?php if (isset($module['orderby']) && $module['orderby'] == $k): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>">
                                    <?php echo $v ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="ptb-select" name="[<?php echo $id ?>][order]">
                            <?php foreach ($order as $k => $v): ?>
                                <option <?php if (isset($module['order']) && $module['order'] == $k): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>">
                                    <?php echo $v ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php
                break;
            case 'has':
                $fields = array(
                    'comments' => PTB_Search_Options::get_name('comments'),
                    'thumbnail' => PTB_Search_Options::get_name('thumbnail'),
                );
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_has_field"><?php _e('Select Field', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">                          
                        <select class="ptb-select" id="ptb_has_field" name="[<?php echo $id ?>][has_field]">
                            <?php foreach ($fields as $f => $name): ?>
                                <?php if(in_array($f,$post_support)):?>
                                    <option <?php if (isset($module['has_field']) && $module['has_field'] == $f): ?>selected="selected"<?php endif; ?> value="<?php echo $f ?>">
                                        <?php echo $name ?>
                                    </option>
                                    <?php endif;?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Input Option', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <?php foreach ($show_as as $key => $input): ?>
                            <input type="radio" id="ptb_<?php echo $id ?>[<?php echo $key ?>]" name="[<?php echo $id ?>][show_as]" value="<?php echo $key ?>"
                                   <?php if ((isset($module['show_as']) && $module['show_as'] == $key) || ($empty && $key == 'checkbox')): ?>checked="checked"<?php endif; ?> />
                            <label for="ptb_<?php echo $id ?>[<?php echo $key ?>]"><?php echo $input ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                break;
            case 'button':
                $aligment = array(
                    'left' =>__('Left','ptb-search'),
                    'center' => __('Center','ptb-search'),
                    'right' => __('Right','ptb-search')
                );
                $colors = array(
                    'white' => __('White', 'ptb'),
                    'yellow' => __('Yellow', 'ptb'),
                    'orange' => __('Orange', 'ptb'),
                    'blue' => __('Blue', 'ptb'),
                    'green' => __('Green', 'ptb'),
                    'red' => __('Red', 'ptb'),
                    'black' => __('Black', 'ptb'),
                    'purple' => __('Purple', 'ptb'),
                    'gray' => __('Gray', 'ptb'),
                    'light-yellow' => __('Light-yellow', 'ptb'),
                    'light-green' => __('Light-green', 'ptb'),
                    'pink' => __('Pink', 'ptb'),
                    'lavender' => __('Lavender', 'ptb')
                );
                ?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_has_field"><?php _e('Alignment', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">                          
                        <select class="ptb-select" name="[<?php echo $id ?>][aligmnet]">
                            <?php foreach ($aligment as $f => $name): ?>
                                <option <?php if (isset($module['aligmnet']) && $module['aligmnet'] == $f): ?>selected="selected"<?php endif; ?> value="<?php echo $f ?>">
                                    <?php echo $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'text', __('Button Text', 'ptb-search')); ?>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_color"><?php _e('Button Color', 'ptb-search') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>_color" name="[<?php echo $id ?>][color]">
                                <?php foreach ($colors as $color => $name): ?>
                                    <option class="shortcode ptb_link_button <?php echo $color ?>" <?php if (isset($module['color']) && $module['color'] === $color): ?>selected="selected"<?php endif; ?> value="<?php echo $color ?>"><?php echo $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php _e('OR', 'ptb') ?>
                        <input class="ptb_color_picker" type="text" name="[<?php echo $id ?>][custom_color]" <?php if (isset($module['custom_color']) && $module['custom_color']): ?>data-value="<?php echo $module['custom_color'] ?>"<?php endif; ?> />
                    </div>
                </div>
                <?php
                break;
            case 'editor':
                ?>
                <input type="hidden" name="[<?php echo $id ?>][editor]" value="1" />
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>_html"><?php _e('Use html editor(not recomended)', 'ptb-search') ?></label>
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
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'text', __('Text for link label', 'ptb-search')); ?>
                </div>
                <div class="ptb_back_active_module_row">
                    <?php PTB_CMB_Base::module_multi_text($id, $module, $languages, 'url', __('Text for link url', 'ptb-search')); ?>
                </div>
                <?php
                break;
            case 'custom_text':
            case 'custom_image':
                $this->get_main_fields($id, '', $module, $languages);
                break;
            default:
                ?>
                <?php do_action('ptb_search_template_' . $type, $id, $args, $module, $post_support, $languages); ?>
                <input type="hidden" name="[<?php echo $id ?>][<?php echo $id ?>]"/>
                <?php
                break;
        }
        ?>
        <div class="ptb_back_active_module_row">	
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[display_inline]"><?php _e('Display Inline', 'ptb-search') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <label>
                    <input id="ptb_<?php echo $id ?>[display_inline]" type="checkbox" name="[<?php echo $id ?>][display_inline]" />
                    <?php _e('Display this module inline (float left)', 'ptb-search'); ?>
                </label>
            </div>
        </div>
        <?php if (false && !in_array($type, $post_support)): ?>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>_show_description"><?php _e('Show metabox description', 'ptb-search') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input id="ptb_<?php echo $id ?>_show_description" type="checkbox" name="[<?php echo $id ?>][show_description]"
                           value="1" <?php if (isset($module['show_description'])): ?>checked="checked"<?php endif; ?> />
                </div>
            </div>
        <?php endif; ?>
        <?php
    }

}
