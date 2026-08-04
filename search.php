<?php
/**
 * Search results template.
 *
 * @package Rasta_Commerce
 */

get_header();
?>
<main id="content" class="site-main rasta-content-shell">
	<div class="rasta-container rasta-reading-width">
		<header class="rasta-page-header">
			<p class="rasta-kicker"><?php esc_html_e( 'نتایج جست‌وجو', 'rasta-commerce' ); ?></p>
				<h1>
					<?php
					printf(
						/* translators: %s: search query. */
						esc_html__( 'نتایج برای «%s»', 'rasta-commerce' ),
						esc_html( get_search_query() )
					);
					?>
				</h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="rasta-search-page-results">
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/content', get_post_type() ); ?>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
