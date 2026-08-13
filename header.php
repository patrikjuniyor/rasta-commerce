<?php
/**
 * Site header.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'پرش به محتوا', 'rasta-commerce' ); ?></a>

<div class="rasta-announcement" data-rasta-announcement>
	<div class="rasta-container rasta-announcement__inner">
		<span class="rasta-announcement__message">
			<?php rasta_icon( 'truck' ); ?>
			<?php echo esc_html( rasta_get_mod( 'rasta_promo_text', __( 'ارسال رایگان برای سفارش‌های بالای ۲ میلیون تومان', 'rasta-commerce' ) ) ); ?>
		</span>
		<span class="rasta-announcement__aside">
			<a href="<?php echo esc_url( rasta_get_mod( 'rasta_promo_link', home_url( '/contact/' ) ) ); ?>"><?php echo esc_html( rasta_get_mod( 'rasta_promo_link_text', __( 'پیگیری سفارش', 'rasta-commerce' ) ) ); ?></a>
			<?php if ( rasta_feature_enabled( 'dismissible_promo', false ) ) : ?>
				<button class="rasta-announcement__close" type="button" data-rasta-dismiss aria-label="<?php esc_attr_e( 'بستن نوار اطلاع‌رسانی', 'rasta-commerce' ); ?>">
					<?php rasta_icon( 'close' ); ?>
				</button>
			<?php endif; ?>
		</span>
	</div>
</div>

<header id="masthead" class="site-header">
	<div class="rasta-container rasta-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="rasta-wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="rasta-wordmark__mark">ر</span>
					<span><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<button class="rasta-menu-toggle" type="button" aria-expanded="false" aria-controls="rasta-primary-menu" data-rasta-menu-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'باز و بسته کردن منو', 'rasta-commerce' ); ?></span>
			<?php rasta_icon( 'menu' ); ?>
		</button>

		<nav id="site-navigation" class="rasta-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'rasta-commerce' ); ?>" data-rasta-nav>
			<?php rasta_primary_navigation(); ?>
		</nav>

		<div class="rasta-header-actions">
			<button class="rasta-header-action" type="button" data-rasta-open="search" aria-haspopup="dialog" aria-controls="rasta-search-drawer">
				<?php rasta_icon( 'search' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'جست‌وجوی محصول', 'rasta-commerce' ); ?></span>
			</button>
			<button class="rasta-header-action rasta-header-action--wishlist" type="button" data-rasta-open="wishlist" aria-haspopup="dialog" aria-controls="rasta-wishlist-drawer">
				<?php rasta_icon( 'heart' ); ?>
				<span class="rasta-wishlist-count" data-wishlist-count hidden>0</span>
				<span class="screen-reader-text"><?php esc_html_e( 'علاقه‌مندی‌ها', 'rasta-commerce' ); ?></span>
			</button>
			<a class="rasta-header-action rasta-header-action--account" href="<?php echo esc_url( rasta_get_account_url() ); ?>">
				<?php rasta_icon( 'user' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'حساب کاربری', 'rasta-commerce' ); ?></span>
			</a>
			<?php if ( rasta_feature_enabled( 'dark_mode', false ) ) : ?>
				<button class="rasta-header-action rasta-header-action--theme" type="button" data-rasta-theme-toggle aria-pressed="false" aria-label="<?php esc_attr_e( 'تغییر حالت روشن و تاریک', 'rasta-commerce' ); ?>">
					<span class="rasta-theme-icon rasta-theme-icon--moon"><?php rasta_icon( 'moon' ); ?></span>
					<span class="rasta-theme-icon rasta-theme-icon--sun" hidden><?php rasta_icon( 'sun' ); ?></span>
					<span class="screen-reader-text"><?php esc_html_e( 'حالت تاریک', 'rasta-commerce' ); ?></span>
				</button>
			<?php endif; ?>
			<button class="rasta-header-action rasta-header-action--cart" type="button" data-rasta-open="cart" aria-haspopup="dialog" aria-controls="rasta-cart-drawer">
				<?php rasta_icon( 'cart' ); ?>
				<?php rasta_cart_count_markup(); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'باز کردن سبد خرید', 'rasta-commerce' ); ?></span>
			</button>
		</div>
	</div>
</header>

<div class="rasta-drawer-backdrop" data-rasta-backdrop hidden></div>
<aside id="rasta-search-drawer" class="rasta-drawer rasta-drawer--search" data-rasta-drawer="search" aria-hidden="true" aria-labelledby="rasta-search-title" tabindex="-1">
	<div class="rasta-drawer__header">
		<h2 id="rasta-search-title"><?php esc_html_e( 'جست‌وجوی سریع', 'rasta-commerce' ); ?></h2>
		<button class="rasta-icon-button" type="button" data-rasta-close aria-label="<?php esc_attr_e( 'بستن جست‌وجو', 'rasta-commerce' ); ?>">
			<?php rasta_icon( 'close' ); ?>
		</button>
	</div>
	<form class="rasta-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="rasta-product-search"><?php esc_html_e( 'نام محصول', 'rasta-commerce' ); ?></label>
		<?php if ( rasta_using_woocommerce() ) : ?>
			<input type="hidden" name="post_type" value="product" />
		<?php else : ?>
			<input type="hidden" name="post_type" value="rasta_product" />
		<?php endif; ?>
		<div class="rasta-search-form__field">
			<?php rasta_icon( 'search' ); ?>
			<input id="rasta-product-search" name="s" type="search" placeholder="<?php esc_attr_e( 'نام یا مدل محصول را بنویسید…', 'rasta-commerce' ); ?>" autocomplete="off" data-product-search />
		</div>
	</form>
	<div class="rasta-search-results" data-search-results aria-live="polite"></div>
	<p class="rasta-search-hint"><?php esc_html_e( 'حداقل دو حرف بنویسید تا نتایج لحظه‌ای را ببینید.', 'rasta-commerce' ); ?></p>
</aside>

<aside id="rasta-cart-drawer" class="rasta-drawer rasta-drawer--cart" data-rasta-drawer="cart" aria-hidden="true" aria-labelledby="rasta-cart-title" tabindex="-1">
	<div class="rasta-drawer__header">
		<h2 id="rasta-cart-title"><?php esc_html_e( 'سبد خرید شما', 'rasta-commerce' ); ?></h2>
		<button class="rasta-icon-button" type="button" data-rasta-close aria-label="<?php esc_attr_e( 'بستن سبد خرید', 'rasta-commerce' ); ?>">
			<?php rasta_icon( 'close' ); ?>
		</button>
	</div>
	<div class="rasta-mini-cart" data-mini-cart>
		<?php rasta_render_mini_cart_content(); ?>
	</div>
</aside>

<aside id="rasta-wishlist-drawer" class="rasta-drawer rasta-drawer--wishlist" data-rasta-drawer="wishlist" aria-hidden="true" aria-labelledby="rasta-wishlist-title" tabindex="-1">
	<div class="rasta-drawer__header">
		<h2 id="rasta-wishlist-title"><?php esc_html_e( 'علاقه‌مندی‌های شما', 'rasta-commerce' ); ?></h2>
		<button class="rasta-icon-button" type="button" data-rasta-close aria-label="<?php esc_attr_e( 'بستن علاقه‌مندی‌ها', 'rasta-commerce' ); ?>">
			<?php rasta_icon( 'close' ); ?>
		</button>
	</div>
	<div class="rasta-saved-products" data-wishlist-results aria-live="polite"></div>
</aside>

<?php if ( rasta_feature_enabled( 'quick_view' ) ) : ?>
	<aside id="rasta-quick-view-drawer" class="rasta-drawer rasta-drawer--quick-view" data-rasta-drawer="quick-view" aria-hidden="true" aria-labelledby="rasta-quick-view-title" tabindex="-1">
		<div class="rasta-drawer__header">
			<h2 id="rasta-quick-view-title"><?php esc_html_e( 'نمایش سریع', 'rasta-commerce' ); ?></h2>
			<button class="rasta-icon-button" type="button" data-rasta-close aria-label="<?php esc_attr_e( 'بستن نمایش سریع', 'rasta-commerce' ); ?>">
				<?php rasta_icon( 'close' ); ?>
			</button>
		</div>
		<div class="rasta-quick-view" data-quick-view-content aria-live="polite"></div>
	</aside>
<?php endif; ?>

<?php if ( rasta_feature_enabled( 'compare' ) ) : ?>
	<aside id="rasta-compare-drawer" class="rasta-drawer rasta-drawer--compare" data-rasta-drawer="compare" aria-hidden="true" aria-labelledby="rasta-compare-title" tabindex="-1">
		<div class="rasta-drawer__header">
			<h2 id="rasta-compare-title"><?php esc_html_e( 'مقایسه محصولات', 'rasta-commerce' ); ?></h2>
			<button class="rasta-icon-button" type="button" data-rasta-close aria-label="<?php esc_attr_e( 'بستن مقایسه', 'rasta-commerce' ); ?>">
				<?php rasta_icon( 'close' ); ?>
			</button>
		</div>
		<div class="rasta-compare-content" data-compare-content aria-live="polite"></div>
	</aside>
<?php endif; ?>
