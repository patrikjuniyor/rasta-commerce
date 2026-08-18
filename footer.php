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
				<p><?php echo esc_html( rasta_get_mod( 'rasta_footer_about', __( 'یک تجربه‌ی تمیز، سریع و قابل اعتماد برای خرید آنلاین؛ از انتخاب تا رسیدن سفارش به دست شما.', 'rasta-commerce' ) ) ); ?></p>
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
			<p>
				<?php
				$copyright = rasta_get_mod( 'rasta_footer_copyright', '' );
				if ( $copyright ) {
					$copyright = str_replace(
						array( '{year}', '{site}' ),
						array( wp_date( 'Y' ), get_bloginfo( 'name' ) ),
						$copyright
					);
					echo esc_html( $copyright );
				} else {
					echo '© ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . ' — ' . esc_html__( 'تمام حقوق محفوظ است.', 'rasta-commerce' );
				}
				?>
			</p>
			<button class="rasta-scroll-top" type="button" data-scroll-top>
				<?php esc_html_e( 'بازگشت به بالا', 'rasta-commerce' ); ?>
				<?php rasta_icon( 'arrow-left' ); ?>
			</button>
		</div>
	</div>
</footer>
<?php if ( class_exists( 'WooCommerce' ) && rasta_feature_enabled( 'compare' ) ) : ?>
	<section class="rasta-compare-tray" data-compare-tray aria-live="polite" hidden>
		<div class="rasta-container rasta-compare-tray__inner">
			<div class="rasta-compare-tray__summary">
				<span class="rasta-compare-tray__icon"><?php rasta_icon( 'compare' ); ?></span>
				<span data-compare-summary><?php esc_html_e( 'محصولی برای مقایسه انتخاب نشده است.', 'rasta-commerce' ); ?></span>
			</div>
			<div class="rasta-compare-tray__items" data-compare-tray-items></div>
			<div class="rasta-compare-tray__actions">
				<button class="rasta-button" type="button" data-compare-open><?php esc_html_e( 'مقایسه کن', 'rasta-commerce' ); ?><?php rasta_icon( 'arrow-left' ); ?></button>
				<button class="rasta-compare-tray__clear" type="button" data-compare-clear><?php esc_html_e( 'پاک‌کردن', 'rasta-commerce' ); ?></button>
			</div>
		</div>
	</section>
<?php endif; ?>
<?php if ( function_exists( 'rasta_render_sticky_add_to_cart' ) ) : ?>
	<?php rasta_render_sticky_add_to_cart(); ?>
<?php endif; ?>
<?php rasta_render_whatsapp_button(); ?>
<div class="rasta-toast" role="status" aria-live="polite" data-rasta-toast></div>
<?php wp_footer(); ?>
</body>
</html>
