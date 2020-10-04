<?php
/**
 * Timeline List template
 *
 * @var $items
 * @var $settings
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
extract( $settings );

$items = apply_filters( 'themify_builder_timeline_list_items', $items, $settings );

$animation_effect = $this->parse_animation_effect( $animation_effect, $settings );
?>

<?php if( ! empty( $items ) ) : ?>
<ul>
	<?php foreach( $items as $item ) : ?>

		<li id="timeline-<?php echo $item['id']; ?>" class="clearfix timeline-post <?php echo '' == $item['icon'] ? 'without-icon' : 'with-icon'; ?> <?php echo $animation_effect; ?>">

			<span class="module-timeline-date">
				<?php echo $item['date_formatted']; ?>
			</span>

			<?php if( '' == $item['icon'] ) : ?>
				<div class="module-timeline-dot"></div>
			<?php else : ?>
				<?php $background_style = ( '' != $item['icon_color'] ) ? ' style="background-color: ' . $this->stylesheet->get_rgba_color( $item['icon_color'] ) . '"' : ''; ?>
				<div class="module-timeline-icon" <?php echo $background_style; ?>><i class="<?php echo themify_get_icon( $item['icon'] ); ?>"></i></div>
			<?php endif; ?>

			<div class="module-timeline-content-wrap">
				<div class="module-timeline-content">

					<?php if( isset( $item['link'] ) ) : ?>
						<h2 class="module-timeline-title"><a href="<?php echo $item['link']; ?>"><?php echo $item['title']; ?></a></h2>
					<?php else : ?>
						<h2 class="module-timeline-title"><?php echo $item['title']; ?></h2>
					<?php endif; ?>

					<?php if( ! $item['hide_featured_image'] ): ?>
						<figure class="module-timeline-image">
							<?php echo $item['image']; ?>
						</figure>
					<?php endif; // hide image ?>
					
					<?php if( ! $item['hide_content'] ) : ?>
					<div class="entry-content" itemprop="articleBody">
							<?php echo $item['content']; ?>
					</div><!-- /.entry-content -->
					<?php endif; //hide_content ?>

				</div><!-- /.timeline-content -->
			</div><!-- /.timeline-content-wrap -->

		</li>

	<?php endforeach; ?>
</ul>
<?php endif; ?>