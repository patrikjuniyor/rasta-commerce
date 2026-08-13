<?php
/**
 * Maintenance / coming-soon template.
 *
 * Shown to logged-out visitors while maintenance mode is enabled.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$headline = rasta_get_mod( 'rasta_maintenance_headline', __( 'فروشگاه به‌زودی بازمی‌گردد', 'rasta-commerce' ) );
$message  = rasta_get_mod( 'rasta_maintenance_message', __( 'در حال به‌روزرسانی و آماده‌سازی فروشگاه هستیم. چند لحظه دیگر با تجربه‌ای بهتر بازمی‌گردیم.', 'rasta-commerce' ) );
$accent   = rasta_sanitize_brand_color( get_theme_mod( 'rasta_accent_color', '#f25c54' ) );
$accent   = $accent ? $accent : '#f25c54';
$year     = wp_date( 'Y' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex, nofollow" />
	<title><?php echo esc_html( $headline ); ?> — <?php bloginfo( 'name' ); ?></title>
	<style>
		@font-face {
			font-family: vazirmatn;
			src: url('<?php echo esc_url( RASTA_URI ); ?>/assets/fonts/Vazirmatn-Regular.woff2') format('woff2');
			font-display: swap;
		}

		@font-face {
			font-family: vazirmatn;
			src: url('<?php echo esc_url( RASTA_URI ); ?>/assets/fonts/Vazirmatn-Bold.woff2') format('woff2');
			font-display: swap;
			font-weight: 700;
		}

		* { box-sizing: border-box; }

		body {
			display: grid;
			place-items: center;
			min-height: 100vh;
			margin: 0;
			padding: 24px;
			background: #0f1424;
			color: #eef2fb;
			font-family: vazirmatn, Tahoma, Arial, sans-serif;
			text-align: center;
		}

		.rasta-maintenance {
			max-width: 560px;
		}

		.rasta-maintenance__mark {
			display: inline-grid;
			place-items: center;
			width: 72px;
			height: 72px;
			margin-bottom: 26px;
			border-radius: 22px;
			background: <?php echo esc_html( $accent ); ?>;
			color: #fff;
			font-size: 2.1rem;
			font-weight: 700;
			box-shadow: 0 18px 40px <?php echo esc_html( $accent ); ?>55;
		}

		.rasta-maintenance h1 {
			margin: 0 0 14px;
			font-size: clamp(1.5rem, 4vw, 2.2rem);
			line-height: 1.4;
		}

		.rasta-maintenance p {
			margin: 0 0 30px;
			color: #aeb8cf;
			font-size: 1rem;
			line-height: 2;
		}

		.rasta-maintenance__footer {
			color: #6d7891;
			font-size: 0.8rem;
		}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'rasta-maintenance-body' ); ?>>
	<main class="rasta-maintenance">
		<span class="rasta-maintenance__mark">ر</span>
		<h1><?php echo esc_html( $headline ); ?></h1>
		<p><?php echo esc_html( $message ); ?></p>
		<p class="rasta-maintenance__footer">© <?php echo esc_html( $year ); ?> <?php bloginfo( 'name' ); ?></p>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
