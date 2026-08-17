<?php
/**
 * Installation welcome page.
 *
 * Renders a Persian "getting started" page under Appearance after the theme
 * is activated, with quick-start steps, feature highlights, and one-click
 * shortcuts into the store tools.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the welcome page and its assets.
 *
 * @return void
 */
function rasta_welcome_menu() {
	add_theme_page(
		esc_html__( 'خوش آمدید راستا', 'rasta-commerce' ),
		esc_html__( 'خوش آمدید', 'rasta-commerce' ),
		'manage_options',
		'rasta-welcome',
		'rasta_welcome_page'
	);

	add_action( 'admin_enqueue_scripts', 'rasta_welcome_assets' );
}
add_action( 'admin_menu', 'rasta_welcome_menu' );

/**
 * Enqueue inline styles for the welcome screen.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function rasta_welcome_assets( $hook_suffix ) {
	if ( 'appearance_page_rasta-welcome' !== $hook_suffix ) {
		return;
	}

	wp_add_inline_style(
		'wp-admin',
		'
		.rasta-welcome { max-width: 1040px; margin-inline: auto; }
		.rasta-welcome__hero { position: relative; overflow: hidden; margin-block: 18px 26px; padding: 40px 44px; border-radius: 16px; background: linear-gradient(135deg, #182033 0%, #26324d 55%, #315bd8 130%); color: #fff; }
		.rasta-welcome__hero::after { content: ""; position: absolute; inset: 0; background: radial-gradient(400px 220px at 88% -10%, rgba(242,92,84,.35), transparent 60%); pointer-events: none; }
		.rasta-welcome__hero h1 { position: relative; margin: 8px 0 10px; font-size: 26px; color: #fff; }
		.rasta-welcome__hero p { position: relative; margin: 0; color: #c7d0e8; font-size: 14px; max-width: 60ch; }
		.rasta-welcome__version { position: relative; display: inline-flex; align-items: center; gap: 6px; margin-top: 16px; padding: 5px 12px; border-radius: 999px; background: rgba(255,255,255,.12); color: #e7ecfa; font-size: 12px; font-weight: 600; }
		.rasta-welcome__brand { position: relative; display: inline-flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 700; }
		.rasta-welcome__brand .mark { display: grid; place-items: center; inline-size: 40px; block-size: 40px; border-radius: 12px; background: linear-gradient(135deg, #f25c54, #315bd8); font-size: 20px; }

		.rasta-welcome__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-block: 0 24px; }
		.rasta-welcome__card { background: #fff; border: 1px solid #dcdcde; border-radius: 12px; padding: 22px; box-shadow: 0 1px 2px rgb(0 0 0 / 4%); }
		.rasta-welcome__card .ic { display: grid; place-items: center; inline-size: 44px; block-size: 44px; border-radius: 12px; margin-bottom: 14px; background: #fdf0ef; color: #f25c54; }
		.rasta-welcome__card--blue .ic { background: #eef2ff; color: #315bd8; }
		.rasta-welcome__card--green .ic { background: #e9f6ef; color: #17865d; }
		.rasta-welcome__card--gold .ic { background: #fdf3d8; color: #996800; }
		.rasta-welcome__card .ic .dashicons { font-size: 22px; inline-size: 22px; block-size: 22px; }
		.rasta-welcome__card h2 { margin: 0 0 8px; font-size: 15px; }
		.rasta-welcome__card p { margin: 0; color: #646970; font-size: 13px; line-height: 1.8; }
		.rasta-welcome__card .button { margin-top: 14px; }

		.rasta-welcome__steps { background: #fff; border: 1px solid #dcdcde; border-radius: 12px; padding: 8px 22px 18px; margin-bottom: 24px; }
		.rasta-welcome__steps h2 { font-size: 15px; margin: 16px 0 4px; }
		.rasta-welcome__steps .desc { color: #646970; margin: 0 0 14px; }
		.rasta-welcome__step { display: flex; gap: 14px; align-items: flex-start; padding: 12px 0; border-bottom: 1px solid #f0f0f1; }
		.rasta-welcome__step:last-child { border-bottom: 0; }
		.rasta-welcome__step .num { display: grid; place-items: center; inline-size: 28px; block-size: 28px; border-radius: 50%; background: #315bd8; color: #fff; font-weight: 700; font-size: 13px; flex-shrink: 0; margin-top: 2px; }
		.rasta-welcome__step .txt b { font-size: 14px; }
		.rasta-welcome__step .txt p { margin: 2px 0 0; color: #646970; font-size: 13px; }
		.rasta-welcome__foot { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding: 18px 22px; border-radius: 12px; background: #f6f7fb; border: 1px solid #e5e7eb; color: #50575e; font-size: 13px; }
		'
	);
}

/**
 * Redirect to the welcome page once, immediately after activation.
 *
 * @return void
 */
function rasta_welcome_redirect() {
	/* Only once per activation. */
	if ( get_option( 'rasta_welcome_dismissed' ) ) {
		return;
	}

	/* Skip bulk activations. */
	if ( isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	update_option( 'rasta_welcome_dismissed', true );

	wp_safe_redirect( admin_url( 'themes.php?page=rasta-welcome' ) );
	exit;
}
/* Run late (priority 99) so store-page creation and rewrite flush finish first. */
add_action( 'after_switch_theme', 'rasta_welcome_redirect', 99 );

/**
 * Render the welcome page.
 *
 * @return void
 */
function rasta_welcome_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$steps = array(
		array(
			'title' => esc_html__( 'پوسته را فعال کنید', 'rasta-commerce' ),
			'text'  => esc_html__( 'از بخش نمایش ← پوسته‌ها، «راستا کامرس» را فعال کنید. با فعال‌سازی، صفحات سبد خرید، تکمیل خرید و حساب کاربری به‌صورت خودکار ساخته می‌شوند.', 'rasta-commerce' ),
		),
		array(
			'title' => esc_html__( 'WooCommerce را نصب کنید (اختیاری)', 'rasta-commerce' ),
			'text'  => esc_html__( 'بدون WooCommerce هم فروشگاه داخلی قالب کار می‌کند؛ اما برای درگاه پرداخت و امکانات پیشرفته‌تر، افزونه WooCommerce را نصب و فعال کنید.', 'rasta-commerce' ),
		),
		array(
			'title' => esc_html__( 'منوها را بچینید', 'rasta-commerce' ),
			'text'  => esc_html__( 'از نمایش ← فهرست‌ها، منوی «منوی اصلی» و «منوی پایین صفحه» را با صفحات فروشگاه پر کنید.', 'rasta-commerce' ),
		),
		array(
			'title' => esc_html__( 'فروشگاه را شخصی‌سازی کنید', 'rasta-commerce' ),
			'text'  => esc_html__( 'رنگ‌ها، متن هیرو، حالت تاریک و ابزارهای خرید را از نمایش ← سفارشی‌سازی تنظیم کنید.', 'rasta-commerce' ),
		),
	);
	?>
	<div class="wrap rasta-welcome">
		<div class="rasta-welcome__hero">
			<span class="rasta-welcome__brand"><span class="mark">ر</span>راستا کامرس</span>
			<h1><?php esc_html_e( 'خوش آمدید! فروشگاه شما آماده است', 'rasta-commerce' ); ?></h1>
			<p><?php esc_html_e( 'یک قالب فروشگاهی سریع، راست‌چین و فارسی که بدون نیاز به کدنویسی، فروشگاهتان را راه می‌اندازد. چند قدم کوتاه تا اولین فروش فاصله دارید.', 'rasta-commerce' ); ?></p>
			<span class="rasta-welcome__version"><?php echo esc_html( sprintf( /* translators: %s: version. */ __( 'نسخه %s', 'rasta-commerce' ), RASTA_VERSION ) ); ?></span>
		</div>

		<div class="rasta-welcome__grid">
			<div class="rasta-welcome__card">
				<div class="ic"><span class="dashicons dashicons-admin-customizer"></span></div>
				<h2><?php esc_html_e( 'سفارشی‌سازی قالب', 'rasta-commerce' ); ?></h2>
				<p><?php esc_html_e( 'رنگ‌های تأکیدی، محتوای هیرو، حالت تاریک، شبکه‌های اجتماعی و چیدمان محصولات.', 'rasta-commerce' ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'شروع سفارشی‌سازی', 'rasta-commerce' ); ?></a>
			</div>

			<div class="rasta-welcome__card rasta-welcome__card--blue">
				<div class="ic"><span class="dashicons dashicons-dashboard"></span></div>
				<h2><?php esc_html_e( 'داشبورد فروشگاه', 'rasta-commerce' ); ?></h2>
				<p><?php esc_html_e( 'نمای زنده از محصولات، سفارش‌ها، درآمد و هشدار موجودی کم.', 'rasta-commerce' ); ?></p>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rasta_product&page=rasta-store-overview' ) ); ?>"><?php esc_html_e( 'مشاهده داشبورد', 'rasta-commerce' ); ?></a>
			</div>

			<div class="rasta-welcome__card rasta-welcome__card--green">
				<div class="ic"><span class="dashicons dashicons-plus-alt2"></span></div>
				<h2><?php esc_html_e( 'افزودن محصول', 'rasta-commerce' ); ?></h2>
				<p><?php esc_html_e( 'قیمت، موجودی، تصویر گالری و دسته‌بندی هر محصول را از پیشخوان مدیریت کنید.', 'rasta-commerce' ); ?></p>
				<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=rasta_product' ) ); ?>"><?php esc_html_e( 'افزودن محصول', 'rasta-commerce' ); ?></a>
			</div>

			<div class="rasta-welcome__card rasta-welcome__card--gold">
				<div class="ic"><span class="dashicons dashicons-admin-settings"></span></div>
				<h2><?php esc_html_e( 'تنظیمات فروشگاه', 'rasta-commerce' ); ?></h2>
				<p><?php esc_html_e( 'ایمیل مدیریت و اطلاع‌رسانی ایمیلی سفارش‌های جدید را پیکربندی کنید.', 'rasta-commerce' ); ?></p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=rasta-store-settings' ) ); ?>"><?php esc_html_e( 'تنظیمات', 'rasta-commerce' ); ?></a>
			</div>
		</div>

		<div class="rasta-welcome__steps">
			<h2><?php esc_html_e( 'راهنمای شروع سریع', 'rasta-commerce' ); ?></h2>
			<p class="desc"><?php esc_html_e( 'برای راه‌اندازی کامل فروشگاه، این چهار قدم را دنبال کنید.', 'rasta-commerce' ); ?></p>
			<?php foreach ( $steps as $index => $step ) : ?>
				<div class="rasta-welcome__step">
					<span class="num"><?php echo esc_html( rasta_to_persian_digits( (string) ( $index + 1 ) ) ); ?></span>
					<div class="txt">
						<b><?php echo esc_html( $step['title'] ); ?></b>
						<p><?php echo esc_html( $step['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="rasta-welcome__foot">
			<span><?php esc_html_e( 'برای راهنمای کامل نصب و مستندات، فایل README قالب را ببینید.', 'rasta-commerce' ); ?></span>
			<a class="button button-secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'مشاهده فروشگاه', 'rasta-commerce' ); ?></a>
		</div>
	</div>
	<?php
}
