<?php
/**
 * 404 template.
 *
 * @package Rasta_Commerce
 */

get_header();
?>
<main id="content" class="site-main rasta-content-shell">
	<div class="rasta-container rasta-reading-width">
		<section class="rasta-empty-state rasta-empty-state--large">
			<span class="rasta-empty-state__code">۴۰۴</span>
			<?php rasta_icon( 'sparkles' ); ?>
			<h1><?php esc_html_e( 'این مسیر به جایی نمی‌رسد.', 'rasta-commerce' ); ?></h1>
			<p><?php esc_html_e( 'ممکن است آدرس تغییر کرده باشد یا صفحه‌ای که می‌خواستید دیگر وجود نداشته باشد.', 'rasta-commerce' ); ?></p>
			<a class="rasta-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به خانه', 'rasta-commerce' ); ?><?php rasta_icon( 'arrow-left' ); ?></a>
		</section>
	</div>
</main>
<?php get_footer(); ?>
