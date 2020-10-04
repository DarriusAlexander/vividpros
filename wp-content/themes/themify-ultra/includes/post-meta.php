<?php global $themify;
	  $is_portfolio =  is_singular('portfolio');
 ?>
<div class="post-content">

	<?php if ( ! $is_portfolio && $themify->hide_date != 'yes' ) : ?>
		<?php themify_theme_post_date(); ?>
	<?php endif; //post date ?>

	<?php if ( $themify->hide_meta != 'yes' &&  $themify->hide_meta_category != 'yes' && $is_portfolio): ?>
		<p class="post-meta entry-meta">
			<?php the_terms( get_the_id(), get_post_type() . '-category', '<span class="post-category">', ' <span class="separator">/</span> ', ' </span>' ) ?>
		</p>
	<?php endif; //post meta ?>
	<?php if($themify->hide_title != 'yes'): ?>
		<?php themify_before_post_title(); // Hook ?>

		<<?php themify_theme_entry_title_tag(); ?> class="post-title entry-title">
			<?php if($themify->unlink_title == 'yes'): ?>
				<?php the_title(); ?>
			<?php else: ?>
				<a href="<?php echo themify_get_featured_image_link(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
			<?php endif; //unlink post title ?>
		</<?php themify_theme_entry_title_tag(); ?>>

		<?php themify_after_post_title(); // Hook ?>
	<?php endif; //post title ?>

	<?php if ( $themify->hide_meta != 'yes' ) : ?>
		<p class="post-meta entry-meta">
			<?php if (!$is_portfolio && $themify->hide_meta_author != 'yes' ): ?>
				<span class="post-author"><?php echo themify_get_author_link() ?></span>
			<?php endif; ?>

			<?php if (!$is_portfolio && $themify->hide_meta_category != 'yes' ): ?>
				<?php the_terms( get_the_id(), 'category', ' <span class="post-category">', ', ', '</span>' ); ?>
			<?php endif; // meta category ?>

			<?php if ( $themify->hide_meta_tag != 'yes' ): ?>
				<?php the_terms( get_the_id(), 'post_tag', ' <span class="post-tag">', ', ', '</span>' ); ?>
			<?php endif; // meta tag ?>

			<?php if ( ! themify_get( 'setting-comments_posts' ) && comments_open() && $themify->hide_meta_comment != 'yes' ) : ?>
				<span class="post-comment"><?php comments_popup_link( '0', '1', '%' ); ?></span>
			<?php endif; // meta comments ?>
		</p>
		<!-- /post-meta -->
	<?php endif; //post meta ?>

	<?php if ( 'below' == $themify->media_position && $themify->post_layout_type === 'classic' ) get_template_part( 'includes/post-media', 'loop' ); ?>
	<?php if (!is_singular() && ! is_attachment() && has_excerpt()) : ?>
		<div class="entry-content">
			<?php the_excerpt(); ?>
		</div><!-- /.entry-content -->
	<?php endif; ?>
	<?php if ( ( $is_portfolio && $themify->post_layout_type != "classic" ) || !$is_portfolio ) : ?>
		<?php edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>'); ?>
	<?php endif; ?>
</div>