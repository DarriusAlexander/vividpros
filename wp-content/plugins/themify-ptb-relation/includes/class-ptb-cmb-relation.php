<?php

/**
 * Custom meta box class to create slider
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Relation extends PTB_CMB_Base {

    /**
     * Adds the custom meta type to the plugin meta types array
     *
     * @since 1.0.0
     *
     * @param array $cmb_types Array of custom meta types of plugin
     *
     * @return array
     */
    public function filter_register_custom_meta_box_type($cmb_types) {

        $cmb_types[$this->get_type()] = array(
            'name' => __('Relation', 'ptb-relation')
        );

        return $cmb_types;
    }

    /**
     * @param string $id the id template
     * @param array $languages
     */
    public function action_template_type($id, array $languages) {
        $cpt_id = PTB_Admin::get_current_custom_post_type_id();
        $ptb_options = PTB::get_option();
        $post_types = $ptb_options->get_custom_post_types();
        $rel_options = PTB_Relation::get_option();
        ?>
        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_post_type" class="ptb_cmb_input_label"><?php _e("Post Type", 'ptb-relation'); ?></label>
            <fieldset class="ptb_cmb_input">
                <select name="<?php echo $id; ?>_post_type" id="<?php echo $id; ?>_post_type">
                    <?php if (!empty($post_types)): ?>
                        <?php foreach ($post_types as $p): ?>
                            <?php if ($p->slug != $cpt_id): ?>
                                <?php $disable = $rel_options->get_relation_type_cmb($cpt_id, $p->slug); ?>
                                <option <?php echo $disable ? 'disabled="disabled"' : '' ?> value="<?php echo $p->slug ?>"><?php echo PTB_Utils::get_label($p->singular_label) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <br/>
                <small><?php _e('To avoid conflicts, this section is not editable after submit.', 'ptb-relation') ?></small>
            </fieldset>
        </div>
        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_many" class="ptb_cmb_input_label">
                <?php _e("One to many relation?", 'ptb-relation'); ?>
            </label>
            <fieldset class="ptb_cmb_input">
                <input type="checkbox" id="<?php echo $id; ?>_many" name="<?php echo $id; ?>_many" value="1"/>
            </fieldset>
        </div>
        <?php
    }

    /**
     * Renders the meta boxes for themplates
     *
     * @since 1.0.0
     *
     * @param string $id the metabox id
     * @param string $type the type of the page(eg. Arhive)
     * @param array $args Array of custom meta types of plugin
     * @param array $data saved data
     * @param array $languages languages array
     */
    public function action_them_themplate($id, $type, $args, $data = array(), array $languages = array()) {
        $default = empty($data);
        $multiply = !empty($args['many']);
        $relation_option = PTB_Relation::get_option();
        $ptb_options = PTB::get_option();
        $rel = false;
        $template_id = sanitize_key($_GET['ptb-ptt']);
        $ptt_options = $ptb_options->get_templates_options();
        if (isset($ptt_options[$template_id])) {
            $rel = $relation_option->get_relation_template($args['post_type'], $ptt_options[$template_id]['post_type']);
        }
        ?>
        <?php if (!$rel): ?>
            <center>
                <strong><?php printf(__("Template doesn't exist for metabox %s.", 'ptb-relation'), PTB_Utils::get_label($args['name'])) ?>&nbsp; </strong>
                <a style="text-decoration: none;" target="_blank" href="<?php echo admin_url('admin.php?page=ptb-relation') ?>"><?php _e("Create Template", 'ptb-relation') ?></a>
            </center>
        <?php endif; ?>
        <?php if ($multiply): ?>
            <?php
            $mode = array('grid' => __('Grid', 'ptb-relation'), 'slider' => __('Slider', 'ptb-relation'));
            $sortfields = PTB_Form_PTT_Archive::get_sort_fields($ptb_options->get_cpt_cmb_options($args['post_type']));
            $order = array(
                'asc'=>__('Ascending', 'ptb-relation'),
                'desc'=>__('Descending', 'ptb-relation')
            );
            ?>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label><?php _e('Layout', 'ptb-relation') ?></label>
                </div>
                <div class="ptb_back_active_module_input ptb_relation_mode">
                    <?php foreach ($mode as $k => $m): ?>
                        <input data-id="ptb_relation_mode_<?php echo $id ?>_<?php echo $k ?>" type="radio" id="ptb_relation_mode_<?php echo $id ?>_<?php echo $k ?>"
                               name="[<?php echo $id ?>][mode]" value="<?php echo $k ?>"
                               <?php if (($default && $k == 'grid') || ( isset($data['mode']) && $data['mode'] == $k )): ?>checked="checked"<?php endif; ?>/>
                        <label for="ptb_relation_mode_<?php echo $id ?>_<?php echo $k ?>"><?php echo $m ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <fieldset id="ptb_relation_mode_<?php echo $id ?>_slider_" <?php if ($default): ?>style="display: none;"<?php endif; ?>>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[minSlides]"><?php _e('Visible', 'ptb-relation') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>[minSlides]"
                                    name="[<?php echo $id ?>][minSlides]">
                                        <?php for ($i = 1; $i < 8; $i++): ?>
                                    <option <?php if (isset($data['minSlides']) && $data['minSlides'] == $i): ?>selected="selected"<?php endif; ?>value="<?php echo $i ?>"><?php echo $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <span><?php _e('Minimum number of slides. Works only in vertical and horizonal mode', 'ptb-relation') ?></span>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[slideWidth]"><?php _e('Slide Width', 'ptb-relation') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input type="number" step="1" name="[<?php echo $id ?>][slideWidth]" id="ptb_<?php echo $id ?>[slideWidth]" value="<?php echo isset($data['slideWidth']) && $data['slideWidth'] > 0 ? $data['slideWidth'] : '' ?>" min="0"/>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[slideHeight]"><?php _e('Slide Height', 'ptb-relation') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input type="number" step="1" name="[<?php echo $id ?>][slideHeight]" id="ptb_<?php echo $id ?>[slideHeight]" value="<?php echo isset($data['slideHeight']) && $data['slideHeight'] > 0 ? $data['slideHeight'] : '' ?>" min="0"/>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[autoHover]"><?php _e('Pause On Hover', 'ptb-relation') ?></label>
                    </div>
                    <div value="1" class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>[autoHover]" name="[<?php echo $id ?>][autoHover]">
                                <option <?php if (isset($data['autoHover']) && $data['autoHover']): ?>selected="selected"<?php endif; ?> value="1"><?php _e('Yes', 'ptb-relation') ?></option>
                                <option <?php if (isset($data['autoHover']) && !$data['autoHover']): ?>selected="selected"<?php endif; ?> value="0"><?php _e('No', 'ptb-relation') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[pause]"><?php _e('Auto Scroll', 'ptb-relation') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>[pause]" name="[<?php echo $id ?>][pause]">
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <option <?php if ((!isset($data['pause']) && $i == 3) || (isset($data['pause']) && $data['pause'] == $i)): ?>selected="selected"<?php endif; ?>value="<?php echo $i ?>">
                                        <?php if ($i == 0): ?>
                                            <?php _e('Off', 'ptb-relation') ?>
                                        <?php else: ?>
                                            <?php echo $i ?> <?php _e('sec', 'ptb-relation') ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <span><?php _e('Set auto transition time in seconds', 'ptb-relation') ?></span>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label><?php _e('Show slider pagination', 'ptb-relation') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>[pager]" name="[<?php echo $id ?>][pager]">
                                <option <?php if (isset($data['pager']) && $data['pager']): ?>selected="selected"<?php endif; ?> value="1"><?php _e('Yes', 'ptb-relation') ?></option>
                                <option <?php if (isset($data['pager']) && !$data['pager']): ?>selected="selected"<?php endif; ?> value="0"><?php _e('No', 'ptb-relation') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[controls]"><?php _e('Show slider arrow buttons', 'ptb-relation') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>[controls]" name="[<?php echo $id ?>][controls]">
                                <option <?php if (isset($data['controls']) && $data['controls']): ?>selected="selected"<?php endif; ?> value="1"><?php _e('Yes', 'ptb-relation') ?></option>
                                <option <?php if (isset($data['controls']) && !$data['controls']): ?>selected="selected"<?php endif; ?> value="0"><?php _e('No', 'ptb-relation') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset id="ptb_relation_mode_<?php echo $id ?>_grid_">
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[columns]"><?php _e('Columns', 'ptb-relation') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <div class="ptb_custom_select">
                            <select id="ptb_<?php echo $id ?>[columns]" name="[<?php echo $id ?>][columns]">
                                <?php for ($i = 1; $i <= 9; $i++): ?>
                                    <option <?php if (isset($data['columns']) && $data['columns'] == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i ?>">
                                        <?php echo $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>
            
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[orderby]"><?php _e('Post Order', 'ptb-relation') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <select name="[<?php echo $id ?>][orderby]" for="ptb_<?php echo $id ?>[orderby]">
                        <?php foreach ($sortfields as $k => $sort): ?>
                            <option <?php if (isset($data['orderby']) && $data['orderby'] == $k): ?>selected="selected" <?php endif; ?> value="<?php echo $k ?>"><?php echo $sort ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[order]"><?php _e('Order', 'ptb-relation') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <select name="[<?php echo $id ?>][order]" for="ptb_<?php echo $id ?>[order]">
                        <?php foreach ($order as $k => $ord): ?>
                            <option <?php if (isset($data['order']) && $data['order'] == $k): ?>selected="selected" <?php endif; ?> value="<?php echo $k ?>"><?php echo $ord ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
        <?php
    }

    /**
     * Renders the meta boxes in public
     *
     * @since 1.0.0
     *
     * @param array $args Array of custom meta types of plugin
     * @param array $data themplate data
     * @param array or string $meta_data post data
     * @param string $lang language code
     * @param boolean $is_single single page
     */
    public function action_public_themplate(array $args, array $data, $meta_data, $lang = false, $is_single = false) {

        if (!empty($meta_data) && isset($meta_data[$args['key']]) && !empty($meta_data[$args['key']])) {
            $value = array_filter(explode(', ', $meta_data[$args['key']]));
            if (!$value) {
                return;
            }
            PTB_Public::$shortcode = true;
            $post_type = get_post_type();
            $rel_post_type = $args['post_type'];
            $rel_options = PTB_Relation::get_option();
            $template = $rel_options->get_relation_template($rel_post_type, $post_type);
            if (!$template) {
                return;
            }
            $ptb_options = PTB::get_option();
            $themplate_layout = $ptb_options->get_post_type_template($template['id']);
            if (!isset($themplate_layout['relation']['layout'])) {
                return;
            }
            $many = !empty($args['many']);
            if (!$many) {
                $value = array(current($value));
            }
            $value = array_unique($value);
            $ver = $this->get_plugin_version();
            $content = '';
            PTB_Public::$render_content = true;
            $themplate = new PTB_Form_PTT_Them('ptb', $ver);
            $args = array(
                    'post_type'=>$rel_post_type,
                    'post_status'=>'publish',
                    'post__in'=>$value,
                    'order'=>!empty($data['order'])?$data['order']:'ASC',
                    'orderby'=>!empty($data['orderby'])?$data['orderby']:'post__in',
                    'posts_per_page'=>count($value),
                    'no_found_rows'=>1
                );
            if (!empty($data['orderby']) && !isset(PTB_Form_PTT_Archive::$sortfields[$data['orderby']])) {
                $cmb_options = $ptb_options->get_cpt_cmb_options($rel_post_type);
                if(isset($cmb_options[$data['orderby']])){
                    $args['meta_key'] = 'ptb_' . $data['orderby']; 
                    $args['orderby']  = $cmb_options[$args['orderby']]['type']==='number' && empty($cmb_options[$data['orderby']]['range'])?'meta_value_num':'meta_value';
                }
            }
            $args = apply_filters('ptb_relation_query_args',$args,$data,$post_type,$rel_post_type);
            $query = new WP_Query($args); 
            while ( $query->have_posts() ) {
                $query->the_post();
                $cmb_options = $post_support = $post_meta = $post_taxonomies = array();
                $ptb_options->get_post_type_data($rel_post_type, $cmb_options, $post_support, $post_taxonomies);
                $post_meta['post_url'] = get_permalink();
                $post_meta['taxonomies'] = !empty($post_taxonomies) ? wp_get_post_terms(get_the_ID(), array_values($post_taxonomies)) : array();
                $post_meta = array_merge($post_meta, get_post_custom(), get_post('', ARRAY_A));
                $content.='<li class="ptb_relation_item">' . $themplate->display_public_themplate($themplate_layout['relation'], $post_support, $cmb_options, $post_meta, $rel_post_type, false) . '</li>';
            }
            wp_reset_postdata();
            PTB_Public::$render_content = PTB_Public::$shortcode = false;
            if (!$content) {
                return;
            }
            $js_data = array();
            if ($many && isset($data['mode']) && $data['mode'] == 'slider') {
                if (!wp_script_is('ptb-relation')) {
                    wp_enqueue_style('ptb-bxslider');
                    wp_enqueue_script('ptb-relation');
                }
                foreach ($data as $key => $arg) {
                    if (!in_array($key, array('text_after', 'text_before', 'text_after', 'css', 'type', 'key', 'mode', 'columns'))) {
                        if (!is_array($arg)) {
                            $js_data[$key] = $arg;
                        } elseif (isset($arg[$lang])) {
                            $js_data[$key] = $arg[$lang];
                        }
                    }
                }
            }
            ?>
            <div class="ptb_loops_shortcode clearfix ptb_relation_<?php echo $data['mode'] ?>">
                <ul <?php if (!empty($js_data)): ?>data-slider="<?php echo esc_attr(json_encode($js_data)); ?>" class="ptb_relation_post_slider"<?php else: ?>class="ptb_relation_posts ptb_relation_columns_<?php echo $data['columns'] ?>"<?php endif; ?>>
                    <?php echo $content ?>    
                </ul>
            </div>
            <?php
        }
    }

    /**
     * Renders the meta boxes on post edit dashboard
     *
     * @since 1.0.0
     *
     * @param WP_Post $post
     * @param string $meta_key
     * @param array $args
     */
    public function render_post_type_meta($post, $meta_key, $args) {
        $ptb_options = PTB::get_option();
        $post_type = $ptb_options->get_custom_post_type($args['post_type']);
        if (!$post_type) {
            return;
        }
        $wp_meta_key = sprintf('%s_%s', $this->get_plugin_name(), $meta_key);
        $value = get_post_meta($post->ID, $wp_meta_key, true);
        $multiply = isset($args['many']) && $args['many'];
        $label = $multiply ? PTB_Utils::get_label($post_type->plural_label) : PTB_Utils::get_label($post_type->singular_label);
        $label = sprintf(__('Select %s', 'ptb-relation'), $label);
        $nonce = wp_create_nonce('ptb_relation_' . $post->ID);
        $query = array(
            'action' => 'ptb_relation_get_term',
            'post_type' => $args['post_type']
        );
        $ajax = admin_url('admin-ajax.php' . add_query_arg($query));
        if (!wp_script_is(self::$plugin_name . '-cmb-autocomplete')) {
            $pluginurl = plugin_dir_url(dirname(__FILE__));
            $translation = array('confirm' => __('Do you want to delete this?', 'ptb-relation'));
            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_style('jquery-ui-styles', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
            wp_enqueue_style(self::$plugin_name . '-cmb', $pluginurl . 'admin/css/ptb-relation.css', array(), $this->get_plugin_version(), 'all');
            wp_register_script(self::$plugin_name . '-cmb-autocomplete', $pluginurl . 'admin/js/ptb-cmb-relation-autocomplete.js', array('jquery-ui-autocomplete', 'jquery-ui-draggable', 'jquery-ui-droppable'), $this->get_plugin_version(), false);
            wp_localize_script(self::$plugin_name . '-cmb-autocomplete', 'ptb_relation', $translation);
            wp_enqueue_script(self::$plugin_name . '-cmb-autocomplete');
        }
        $query['action'] = 'ptb_relation_get_post';
        $query['many'] = $multiply;
        $query['nonce'] = $nonce;
        $query['post_id'] = $post->ID;
        $posts = array();
        if ($value) {
            $value = array_filter(explode(', ', $value));
            if (!$multiply) {
                $value = array(current($value));
            }
            if ($value) {
                $posts = get_posts(array(
                    'post_type' => $args['post_type'],
                    'include' => $value,
                    'nopaging' => 1,
                    'orderby' => 'post__in'
                ));
                if (!$multiply) {
                    $posts = current($posts);
                }
            }
        }
        ?>
        <fieldset class="ptb_cmb_input">
            <div class="ptb_relation_autocomplete_wrap<?php echo $multiply ? ' ptb_relation_many' : '' ?>">
                <?php if ($multiply): ?>
                    <div class="ptb_relation_multiply">
                        <ul>
                            <?php if (!empty($posts)): ?>
                                <?php foreach ($posts as $p): ?>
                                    <li data-id="<?php echo $p->ID ?>">
                                        <span class="ptb_relation_term"><?php echo $p->post_title ?></span>
                                        <span data-id="<?php echo $p->ID ?>" class="ti-close ptb_relation_remove_term"></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                    <input data-multiply="<?php echo $multiply ?>" data-ajax="<?php esc_attr_e($ajax) ?>" class="ptb_relation_autocomplete" value="<?php echo!$multiply && !empty($posts) ? $posts->post_title : '' ?>" type="text" placeholder="<?php _e('Search by post title', 'ptb-relation') ?>" autocomplete="off"  id="<?php echo $meta_key; ?>"/>
                    <?php if ($multiply): ?>
                    </div>
                <?php endif; ?>
                <a href="<?php echo add_query_arg( $query, admin_url( 'admin-ajax.php' ) ); ?>" title="<?php echo $label ?>" class="ptb_custom_lightbox" data-top="25%" data-class="ptb_relation_lightbox"><?php echo $label; ?></a>
                <input type="hidden" name="<?php echo $meta_key ?>" value="<?php echo $value ? implode(', ', $value) : '' ?>" />
            </div>
        </fieldset>
        <?php
        if (!empty($posts)) {
            wp_reset_postdata();
        }
    }

}
