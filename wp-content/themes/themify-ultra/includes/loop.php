<?php
/**
 * Template for generic post display.
 * @package themify
 * @since 1.0.0
 */
?>
<?php if(!is_single()){ global $more; $more = 0; } //enable more link ?>

<?php
/** Themify Default Variables
 *  @var object */
global $themify;
$posfix = !empty($themify->post_module_hook) ? '_module' : '';
?>

<?php themify_post_before($posfix); // hook ?>
<article id="post-<?php the_id(); ?>" <?php post_class( 'post clearfix' ); ?>>
	<?php themify_post_start($posfix); // hook ?>

    <?php if($themify->unlink_title!='yes' || $themify->unlink_image!='yes'):?>
        <a href="<?php echo themify_get_featured_image_link(); ?>" aria-label="<?php the_title_attribute() ?>" data-post-permalink="yes" style="display: none;"></a>
    <?php endif;?>

	<?php if( 'below' != $themify->media_position ) get_template_part( 'includes/post-media', 'loop'); ?>

	<div class="post-content">
		<div class="post-content-inner">

			<?php if ( $themify->hide_date != 'yes' ) : ?>
				<?php themify_theme_post_date(); ?>
			<?php endif; //post date ?>

			<?php if($themify->hide_title != 'yes'): ?>
				<?php themify_post_title(); ?>
			<?php endif; //post title ?>

			<?php if ( $themify->hide_meta != 'yes' ) : ?>
				<p class="post-meta entry-meta">
					<?php if ( $themify->hide_meta_author != 'yes' ): ?>
						<span class="post-author"><?php echo themify_get_author_link() ?></span>
					<?php endif; ?>

					<?php if ( $themify->hide_meta_category != 'yes' ): ?>
						<?php themify_the_terms( get_the_id(), 'category', ' <span class="post-category">', '<span class="separator">, </span>', '</span>' ); ?>
					<?php endif; // meta category ?>

					<?php if ( $themify->hide_meta_tag != 'yes' ): ?>
						<?php the_terms( get_the_id(), 'post_tag', ' <span class="post-tag">', '<span class="separator">, </span>', '</span>' ); ?>
					<?php endif; // meta tag ?>

					<?php if ( ! themify_get( 'setting-comments_posts' ) && comments_open() && $themify->hide_meta_comment != 'yes' ) : ?>
						<span class="post-comment"><?php comments_popup_link( '0', '1', '%' ); ?></span>
					<?php endif; // meta comments ?>
				</p>
				<!-- /post-meta -->
			<?php endif; //post meta ?>

			<?php if( 'below' == $themify->media_position ) get_template_part( 'includes/post-media', 'loop'); ?>

			<div class="entry-content">

				<?php if ( 'excerpt' == $themify->display_content && ! is_attachment() ) : ?>

					<?php the_excerpt(); ?>

					<?php if( themify_check('setting-excerpt_more') ) : ?>

                        <p><a href="<?php echo themify_get_featured_image_link(); ?>" class="more-link"><?php echo themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify') ?></a></p>

					<?php endif; ?>

				<?php elseif($themify->display_content == 'none'): ?>

				<?php else: ?>

					<?php the_content(themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>

				<?php endif; //display content ?>

			</div><!-- /.entry-content -->

			<?php edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>'); ?>

		</div>
		<!-- /.post-content-inner -->
	</div>
	<!-- /.post-content -->
	<?php themify_post_end($posfix); // hook ?>

</article>
<!-- /.post -->
<?php themify_post_after($posfix); // hook ?>
