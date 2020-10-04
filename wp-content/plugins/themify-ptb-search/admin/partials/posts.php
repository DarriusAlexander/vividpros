<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
global $related_posts;
if ($related_posts->have_posts()) {
    ?>
    <form action="" method="post" id="ptb_relation_select_posts">
        <input type="text" value="" placeholder="<?php _e('Search posts', 'ptb-relation') ?>" class="ptb_relation_searh_posts alignright"/>
        <div class="ptb_relation_posts_wrap">
            <?php while ($related_posts->have_posts()): $related_posts->the_post(); ?>
                <label>
                    <input type="<?php echo $many ? 'checkbox' : 'radio' ?>" id="ptb-related-post-<?php the_ID() ?>" name="posts<?php echo $many ? '[]' : '' ?>" value="<?php the_ID() ?>"/><?php the_title() ?>
                </label>
            <?php endwhile; ?>
        </div>
        <a href="#" class="button ptb_relation_uncheck alignleft"><?php _e('Uncheck All', 'ptb-relation') ?></a>
        <input type="submit"  class="button button-primary alignright" value="<?php _e('Save', 'ptb-relation') ?>" />
    </form>

    <?php
}
else {
    _e('Nothing Found', 'ptb-relation');
}

