<?php
/**
 * Site footer.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer id="colophon" class="site-footer">
	<div class="rasta-container">
		<section class="rasta-footer-promise" aria-label="<?php esc_attr_e( 'تعهدهای فروشگاه', 'rasta-commerce' ); ?>">
			<div>
				<span class="rasta-footer-promise__icon"><?php rasta_icon( 'truck' ); ?></span>
				<span><strong><?php esc_html_e( 'ارسال مطمئن', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'بسته‌بندی امن و پیگیری سفارش', 'rasta-commerce' ); ?></span>
			</div>
			<div>
				<span class="rasta-footer-promise__icon"><?php rasta_icon( 'shield' ); ?></span>
				<span><strong><?php esc_html_e( 'خرید با اطمینان', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'ضمانت اصالت و بازگشت کالا', 'rasta-commerce' ); ?></span>
			</div>
			<div>
				<span class="rasta-footer-promise__icon"><?php rasta_icon( 'headset' ); ?></span>
				<span><strong><?php esc_html_e( 'پاسخ‌گو کنار شما', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'پشتیبانی در تمام مراحل خرید', 'rasta-commerce' ); ?></span>
			</div>
		</section>

		<div class="rasta-footer-grid">
			<div class="rasta-footer-brand">
				<a class="rasta-wordmark rasta-wordmark--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="rasta-wordmark__mark">ر</span>
					<span><?php bloginfo( 'name' ); ?></span>
				</a>
				<p><?php esc_html_e( 'یک تجربه‌ی تمیز، سریع و قابل اعتماد برای خرید آنلاین؛ از انتخاب تا رسیدن سفارش به دست شما.', 'rasta-commerce' ); ?></p>
				<?php rasta_social_links(); ?>
			</div>

			<div class="rasta-footer-column">
				<h2><?php esc_html_e( 'دسترسی سریع', 'rasta-commerce' ); ?></h2>
				<?php rasta_footer_navigation(); ?>
			</div>
			<div class="rasta-footer-column">
				<h2><?php esc_html_e( 'راهنمای خرید', 'rasta-commerce' ); ?></h2>
				<ul class="rasta-footer-links">
					<li><a href="<?php echo esc_url( home_url( '/shipping/' ) ); ?>"><?php esc_html_e( 'روش‌های ارسال', 'rasta-commerce' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/returns/' ) ); ?>"><?php esc_html_e( 'رویه بازگشت کالا', 'rasta-commerce' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'حریم خصوصی', 'rasta-commerce' ); ?></a></li>
				</ul>
			</div>
			<div class="rasta-footer-contact">
				<h2><?php esc_html_e( 'در تماس بمانیم', 'rasta-commerce' ); ?></h2>
				<p><?php esc_html_e( 'برای راهنمایی خرید یا همکاری، پیام بگذارید.', 'rasta-commerce' ); ?></p>
				<a class="rasta-contact-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
					<?php esc_html_e( 'تماس با ما', 'rasta-commerce' ); ?>
					<?php rasta_icon( 'arrow-left' ); ?>
				</a>
			</div>
		</div>

		<div class="rasta-footer-bottom">
			<p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'تمام حقوق محفوظ است.', 'rasta-commerce' ); ?></p>
			<button class="rasta-scroll-top" type="button" data-scroll-top>
				<?php esc_html_e( 'بازگشت به بالا', 'rasta-commerce' ); ?>
				<?php rasta_icon( 'arrow-left' ); ?>
			</button>
		</div>
	</div>
</footer>
<div class="rasta-toast" role="status" aria-live="polite" data-rasta-toast></div>
<?php wp_footer(); ?>
</body>
</html>
