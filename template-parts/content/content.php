<?php
/**
 * Post card used in archives and search.
 *
 * @package Rasta_Commerce
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'rasta-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="rasta-post-card__image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>
	<div class="rasta-post-card__body">
		<?php rasta_posted_on(); ?>
		<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="entry-summary"><?php the_excerpt(); ?></div>
		<a class="rasta-text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'خواندن مطلب', 'rasta-commerce' ); ?><?php rasta_icon( 'arrow-left' ); ?></a>
	</div>
</article>
