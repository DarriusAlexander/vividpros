<?php
/**
 * Template for displaying the loop
 */

$param_image = 'w=' . $img_width . '&h=' . $img_height . '&ignore=true';
if ($this->is_img_php_disabled())
	$param_image .= $image_size != '' ? '&image_size=' . $image_size : '';

if( $query->have_posts() ) : while( $query->have_posts() ) : $query->the_post(); ?>

	<div <?php post_class( array( 'post', $animation_effect ) ); ?>>

		<div class="infinite-post-cover" style="background-color: <?php echo $this->stylesheet->get_rgba_color( $overlay_color ); ?>;"></div>

		<div class="infinite-post-image">
			<?php if( ( $layout == 'list' || $layout == 'grid' ) && $unlink_image != 'yes' ) : ?>
				<a href="<?php echo ( $permalink == 'lightboxed' ) ? themify_get_lightbox_iframe_link( get_permalink() ) : get_permalink(); ?>" <?php if( $permalink == 'newwindow' ) echo 'target="_blank"'; ?> class="<?php if( $permalink == 'lightboxed' ) echo 'themify_lightbox'; ?>">
					<?php echo themify_get_image( $param_image ); ?>
				</a>
			<?php else : ?>
				<?php echo themify_get_image( $param_image ); ?>
			<?php endif; ?>
		</div>

		<div class="infinite-post-inner-wrap">
			<div class="infinite-post-inner">

				<div class="bip-post-text">

					<?php if( $hide_post_date != 'yes' ) : ?>
						<time datetime="<?php the_time('o-m-d') ?>" class="post-date entry-date updated"><?php echo get_the_date( get_option( 'date_format' ) ) ?></time>
					<?php endif; ?>

					<?php if( $hide_post_title != 'yes' ) : ?>
						<h2 class="post-title">
							<?php if( $unlink_post_title != 'yes' ) : ?>
								<a href="<?php echo ( $permalink == 'lightboxed' ) ? themify_get_lightbox_iframe_link( get_permalink() ) : get_permalink(); ?>" <?php if( $permalink == 'newwindow' ) echo 'target="_blank"'; ?> class="<?php if( $permalink == 'lightboxed' ) echo 'themify_lightbox'; ?>">
									<?php the_title(); ?>
								</a>
							<?php else : ?>
								<?php the_title(); ?>
							<?php endif; ?>
						</h2>
					<?php endif; ?>

					<?php if( $hide_post_meta != 'yes' ) : ?>
						<p class="post-meta entry-meta">

							<span class="post-author"><?php echo themify_get_author_link() ?></span>
							<span class="post-category"><?php the_category(', ') ?></span>
							<?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
							<?php  if( !themify_get('setting-comments_posts') && comments_open() ) : ?>
								<span class="post-comment"><?php comments_popup_link( __( '0 Comments', 'builder-infinite-posts' ), __( '1 Comment', 'builder-infinite-posts' ), __( '% Comments', 'builder-infinite-posts' ) ); ?></span>
							<?php endif; //post comment ?>
						</p>
					<?php endif; ?>

					<?php if( $display_content == 'excerpt' ) : ?>
						<div class="bip-post-content">
							<?php the_excerpt(); ?>
						</div>
					<?php elseif( $display_content == 'content' ) : ?>
						<div class="bip-post-content">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>

					<?php if( $hide_read_more_button != 'yes' ) : ?>
						<div class="read-more-button-wrap">
							<a href="<?php echo ( $permalink == 'lightboxed' ) ? themify_get_lightbox_iframe_link( get_permalink() ) : get_permalink(); ?>" <?php if( $permalink == 'newwindow' ) echo 'target="_blank"'; ?> class="read-more-button button-size-<?php echo $read_more_size; ?> <?php echo ( $color_button != 'default' ) ? 'ui builder_button ' . $color_button : ''; ?> <?php if( $permalink == 'lightboxed' ) echo 'themify_lightbox'; ?> button-style-<?php echo $buttons_style; ?>">
								<?php echo $read_more_text; ?>
							</a>
						</div>
					<?php endif; ?>

				</div><!-- .bip-post-text -->
			</div><!-- .infinite-post-inner -->

		</div><!-- .infinite-post-inner -->

		<?php
		if( $layout == 'parallax' ) {
			echo '<style>
				#' . $module_ID . ' ' . '.post-' . $post->ID . ' { background-image: url(' . wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ) . '); }
			</style>';
		} ?>

	</div><!-- .post -->
<?php endwhile; wp_reset_postdata(); endif;