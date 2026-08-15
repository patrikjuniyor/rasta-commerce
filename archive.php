<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Generic archive template.
 *
 * @package Rasta_Commerce
 */

get_header();
?>
<main id="content" class="site-main rasta-content-shell">
	<div class="rasta-container rasta-blog-layout">
		<section class="rasta-post-list">
			<header class="rasta-page-header">
				<p class="rasta-kicker"><?php esc_html_e( 'بایگانی', 'rasta-commerce' ); ?></p>
				<?php the_archive_title( '<h1>', '</h1>' ); ?>
				<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
			</header>
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/content', get_post_type() ); ?>
				<?php endwhile; ?>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
			<?php endif; ?>
		</section>
		<?php get_sidebar(); ?>
	</div>
</main>
<?php get_footer(); ?>
