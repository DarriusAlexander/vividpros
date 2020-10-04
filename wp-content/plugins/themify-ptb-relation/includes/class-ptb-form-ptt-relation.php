<?php

class PTB_Form_PTT_Relation extends PTB_Form_PTT_Them {
    
    private $template_id = false;
    
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
    public function __construct($plugin_name, $version, $themplate_id=false) {
        $this->template_id = $themplate_id;
        parent::__construct($plugin_name, $version, $themplate_id);
    }

    /**
     * Relations layout parametrs
     *
     * @since 1.0.0
     */
     public function add_fields($data = array()) {
        $isset = isset($data['data']);
        $data = $isset ? $data['data'] : array();
        ?>
        <div class="ptb-relation-loader"></div>
        <div class="ptb_lightbox_row ptb_layout_post ">
            <div class="ptb_lightbox_label"><?php _e('Relation Title', 'ptb-relation'); ?></div>
            <div class="ptb_lightbox_input">
                <?php PTB_CMB_Base::module_language_tabs('ptb_relation', $data,  PTB_Utils::get_all_languages(), 'title', 'text', false, true) ?>
            </div>
        </div>
        <?php
    }


}
