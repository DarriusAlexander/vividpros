<?php
global $post_types;
global $cpt_id, $add;
?>
<div id="<?php echo $this->plugin_name ?>-edit-form"<?php if (isset($add)): ?>class="ptb-submission-add-temp"<?php endif; ?>>
    <?php
    $them = new PTB_Form_PTT_Frontend('ptb', $this->version, $cpt_id);
    $them->add_settings_section('frontend');
    ?>
</div>