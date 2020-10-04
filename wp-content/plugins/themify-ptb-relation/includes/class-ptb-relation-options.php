<?php

/**
 * The plugin options management class
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

class PTB_Relation_Options {

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
     * @var      string $plugin_name The name of this plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }


    public function get_relation_template($post_type,$rel_post_type) {
        $ptb_options = PTB::get_option();
        foreach ($ptb_options->option_post_type_templates as $k => $t) {
            if (isset($t['relation']) && isset($t['post_type']) && isset($t['rel_post_type']) && $t['post_type']==$post_type && $t['rel_post_type']==$rel_post_type) {
                $t['id'] = $k;
                return $t;
            }
        }
        return FALSE;
    }

    
    public function get_relation_cmb($post_type) {
        $ptb_options = PTB::get_option();
        $post_type1 = $ptb_options->get_custom_post_type($post_type);
        $relations = array();
        if($post_type1){
            $cmb = $ptb_options->get_cpt_cmb_options($post_type);
            if(!empty($cmb)){
                foreach ($cmb as $c){
                    if($c['type']=='relation' && isset($c['post_type']) && $c['post_type']){
                        $post_type2 = $ptb_options->get_custom_post_type($c['post_type']);
                        if($post_type2){
                            $relations[$post_type.'@'.$post_type2->slug] = PTB_Utils::get_label($post_type1->singular_label).'->'.PTB_Utils::get_label($post_type2->singular_label).' ( '.PTB_Utils::get_label($c['name']).' )';
                        }
                    }
                }
            }
        }
        return $relations;
    }
    
    public function get_relation_type_cmb($post_type,$rel_post_type) {
        $ptb_options = PTB::get_option();
        $cmb = $ptb_options->get_cpt_cmb_options($post_type);
        if(!empty($cmb)){
            foreach ($cmb as $c){
                if($c['type']=='relation' && isset($c['post_type']) && $c['post_type']==$rel_post_type){
                    return $c;
                }
            }
        }
        return false;
    }

}
