<?php
/**
 * Blog index fallback template.
 *
 * @package Rasta_Commerce
 */

get_header();
?>
<main id="content" class="site-main rasta-content-shell">
	<div class="rasta-container rasta-blog-layout">
		<section class="rasta-post-list" aria-label="<?php esc_attr_e( 'نوشته‌ها', 'rasta-commerce' ); ?>">
			<header class="rasta-page-header">
				<p class="rasta-kicker"><?php esc_html_e( 'مجله راستا', 'rasta-commerce' ); ?></p>
				<h1><?php echo esc_html( is_home() ? single_post_title( '', false ) : __( 'تازه‌ها', 'rasta-commerce' ) ); ?></h1>
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
