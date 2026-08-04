<?php
/**
 * Plugin Name: Rasta ZarinPal Gateway for WooCommerce
 * Plugin URI: https://github.com/patrikjuniyor/rasta-commerce
 * Description: A secure, lightweight ZarinPal v4 payment gateway for WooCommerce, designed to complement Rasta Commerce.
 * Version: 1.0.1
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Author: Rasta Commerce Contributors
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rasta-zarinpal-gateway
 * Domain Path: /languages
 *
 * @package Rasta_Zarinpal_Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RASTA_ZARINPAL_VERSION', '1.0.1' );
define( 'RASTA_ZARINPAL_FILE', __FILE__ );
define( 'RASTA_ZARINPAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'RASTA_ZARINPAL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load the gateway only after WooCommerce is ready.
 *
 * @return void
 */
function rasta_zarinpal_gateway_init() {
	load_plugin_textdomain( 'rasta-zarinpal-gateway', false, dirname( plugin_basename( RASTA_ZARINPAL_FILE ) ) . '/languages' );

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'rasta_zarinpal_missing_woocommerce_notice' );
		return;
	}

	require_once RASTA_ZARINPAL_DIR . 'includes/class-rasta-zarinpal-gateway.php';

	add_filter(
		'woocommerce_payment_gateways',
		static function ( $gateways ) {
			$gateways[] = 'WC_Gateway_Rasta_Zarinpal';
			return $gateways;
		}
	);
}
add_action( 'plugins_loaded', 'rasta_zarinpal_gateway_init', 20 );

/**
 * Explain the dependency when WooCommerce is inactive.
 *
 * @return void
 */
function rasta_zarinpal_missing_woocommerce_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'درگاه زرین‌پال راستا برای فعال شدن به افزونه WooCommerce نیاز دارد.', 'rasta-zarinpal-gateway' ); ?></p>
	</div>
	<?php
}

/**
 * Route ZarinPal callbacks outside the lazily-loaded gateway instance.
 * WooCommerce can dispatch wc-api requests before checkout instantiates gateways.
 *
 * @return void
 */
function rasta_zarinpal_gateway_callback_router() {
	if ( ! function_exists( 'WC' ) || ! class_exists( 'WC_Gateway_Rasta_Zarinpal' ) ) {
		wp_die( esc_html__( 'درگاه زرین‌پال در دسترس نیست.', 'rasta-zarinpal-gateway' ), esc_html__( 'خطای پرداخت', 'rasta-zarinpal-gateway' ), array( 'response' => 503 ) );
	}

	$gateways = WC()->payment_gateways()->payment_gateways();
	$gateway  = isset( $gateways['rasta_zarinpal'] ) ? $gateways['rasta_zarinpal'] : null;

	if ( ! $gateway instanceof WC_Gateway_Rasta_Zarinpal ) {
		wp_die( esc_html__( 'درگاه زرین‌پال بارگذاری نشد.', 'rasta-zarinpal-gateway' ), esc_html__( 'خطای پرداخت', 'rasta-zarinpal-gateway' ), array( 'response' => 503 ) );
	}

	$gateway->handle_callback();
}
add_action( 'woocommerce_api_wc_gateway_rasta_zarinpal', 'rasta_zarinpal_gateway_callback_router' );
