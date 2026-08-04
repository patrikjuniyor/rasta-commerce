<?php
/**
 * Single post template.
 *
 * @package Rasta_Commerce
 */

get_header();
?>
<main id="content" class="site-main rasta-content-shell">
	<div class="rasta-container rasta-reading-width">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'rasta-single-post' ); ?>>
				<header class="entry-header">
					<p class="rasta-kicker"><?php echo esc_html( get_the_category_list( ' / ' ) ? wp_strip_all_tags( get_the_category_list( ' / ' ) ) : __( 'مجله راستا', 'rasta-commerce' ) ); ?></p>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php rasta_posted_on(); ?>
				</header>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="rasta-single-post__featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?></div>
				<?php endif; ?>
				<div class="entry-content">
					<?php the_content(); ?>
					<?php wp_link_pages(); ?>
				</div>
				<footer class="entry-footer">
					<?php the_tags( '<span class="rasta-tags">', ' ', '</span>' ); ?>
				</footer>
			</article>
			<?php the_post_navigation(); ?>
			<?php if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		<?php endwhile; ?>
	</div>
</main>
<?php get_footer(); ?>
