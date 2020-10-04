<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    PTB
 * @subpackage PTB/public
 * @author     Themify <ptb@themify.me>
 */
class PTB_Relation_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }


    public function public_enqueue_scripts() {
       
            $plugin_url = plugin_dir_url(__FILE__);
   
            wp_enqueue_style($this->plugin_name, $plugin_url . 'css/ptb-relation.css', array(), $this->version, 'all');
            wp_register_style('ptb-bxslider', $plugin_url . 'css/jquery.bxslider.css', array(), $this->version, 'all');
            wp_register_script('ptb-easing', $plugin_url . 'js/jquery.easing.1.3.js', array(), $this->version, true);
            wp_register_script('ptb-bxslider', $plugin_url . 'js/jquery.bxslider.min.js', array(), $this->version, true);
            wp_register_script($this->plugin_name,$plugin_url . 'js/ptb-relation.js', array('ptb-bxslider','ptb-easing'), $this->version, true);
    }
    
    
    
    
    public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, $languages) {
        $multiply = !empty($args['many']);
        if($module['mode']==='autocomplete' && !wp_style_is($this->plugin_name.'-submission')){
            $plugin_url = plugin_dir_url(__FILE__);
            if($multiply){
                $translation = array('confirm' => __('Do you want to delete this?', 'ptb-relation'));
                wp_enqueue_style($this->plugin_name.'-submission', $plugin_url . 'submission/css/ptb-relation-submission.css', array(), $this->version, 'all');
                wp_enqueue_style($this->plugin_name.'-admin', dirname($plugin_url) . '/admin/css/ptb-relation.css', array(), $this->version, 'all');
            }
            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script($this->plugin_name.'-submission',$plugin_url .'submission/js/ptb-relation-submission.js', array('jquery-ui-autocomplete'), $this->version, true);
            if($multiply){
                wp_localize_script($this->plugin_name.'-submission', 'ptb_relation', $translation);
            }
        }
       
        $rel_post_type_slug = $args['post_type'];
        $key = $args['key'];
        $options = PTB::get_option();
        $rel_post_type = $options->get_custom_post_type($rel_post_type_slug);
        $label = $multiply?$rel_post_type->plural_label:$rel_post_type->singular_label;
        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $key, TRUE) : false;
        unset($rel_post_type);
        $posts = array();
        if($multiply){
            if($module['mode']==='radio'){
                $module['mode'] = 'checkbox';
            }
        }
        elseif($module['mode']==='checkbox'){
             $module['mode'] = 'radio';
        }
        if($data){
            $data = array_filter(explode(', ', $data));
            if (!$multiply) {
                $data = current($data);
            }
        }
        if($module['mode']!=='autocomplete'){
            $posts = get_posts(array(
                'nopaging'=>true,
                'post_type'=>$rel_post_type_slug
            ));
            wp_reset_postdata();
        }
        elseif($data){
            $posts = get_posts(array(
                'post_type' =>$rel_post_type_slug,
                'include' => $data,
                'nopaging' => 1,
                'orderby' => 'post__in'
            ));
            if(!$multiply){
                $posts = current($posts);
            }
        }
        if(!is_array($data)){
            $data = array($data);
        }
        $input_name = 'submission['.$key.']';
        ?>
        <div class="ptb_back_active_module_input ptb_relation_submission_wrap">
            <?php switch($module['mode']){
                case 'checkbox':
                case 'radio':
                ?>
                <?php   foreach ($posts as $p):?>
                            <?php $eid = $p->ID;?>
                            <label for="ptb_post_<?php echo $eid ?>">
                                <input type="<?php echo $module['mode'] ?>" <?php if (in_array($eid, $data)): ?>checked="checked"<?php endif; ?> name="<?php echo $input_name ?><?php if ($module['mode'] === 'checkbox'): ?>[]<?php endif; ?>" id="ptb_post_<?php echo $eid ?>" value="<?php echo $eid ?>"/>
                                <span><?php echo $p->post_title; ?></span>
                            </label>
                <?php   endforeach;?>
                <?php 
                break;
                case 'select':
                ?>
                    <select class="ptb-select" name="<?php echo $input_name ?><?php if($multiply):?>[]<?php endif;?>" <?php if($multiply):?>multiple="multiple"<?php endif;?>>
                        <?php if (!isset($module['required'])): ?>
                            <option>---</option>
                        <?php endif; ?>
                        <?php foreach ($posts as $p):?>
                            <option <?php if (in_array($p->ID, $data)): ?>selected="selected"<?php endif; ?> value="<?php echo $p->ID?>"><?php echo $p->post_title; ?></option>
                        <?php endforeach;?>
                    </select>
                <?php
                break;
                default:?>
                <?php if($multiply):?>
                    <div class="ptb_relation_multiply ptb_relation_many">
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
                <?php endif;?>
                    <div class="ui-widget">
                        <input value="<?php echo !$multiply && $posts?$posts->post_title:''?>" placeholder="<?php printf(__('Search a %s', 'ptb-submission'), PTB_Utils::get_label($label)) ?>" type="text" autocomplete="off" class="ptb-relation-autocomplete" value="" data-multyply="<?php echo $multiply?>" data-post_type="<?php echo $rel_post_type_slug ?>"/>
                        <input type="hidden"  name="<?php echo $input_name ?>" value="<?php echo $data?implode(', ',$data):''?>"/>
                    </div>
                 <?php if($multiply):?>
                    </div>
                <?php endif;?>
            <?php }?>
        </div>
        <?php
    }


}
