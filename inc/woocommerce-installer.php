<?php
/**
 * WooCommerce auto-installer.
 *
 * When the feature is enabled (the default), the theme installs and activates
 * WooCommerce automatically if it is missing. This makes the full storefront
 * experience available without manual plugin installation.
 *
 * Disable it at any time by defining the constant in wp-config.php:
 *
 *     define( 'RASTA_AUTO_INSTALL_WOOCOMMERCE', false );
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ─── Feature flag ─────────────────────────────────────────────────────── */

/**
 * Whether automatic WooCommerce installation is enabled.
 *
 * @return bool
 */
function rasta_wc_auto_install_enabled() {
	if ( defined( 'RASTA_AUTO_INSTALL_WOOCOMMERCE' ) ) {
		return (bool) RASTA_AUTO_INSTALL_WOOCOMMERCE;
	}

	return (bool) apply_filters( 'rasta_auto_install_woocommerce', true );
}

/* ─── State helpers ────────────────────────────────────────────────────── */

/**
 * Whether WooCommerce is currently active.
 *
 * @return bool
 */
function rasta_wc_is_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Whether the WooCommerce plugin files are present (even if inactive).
 *
 * @return bool
 */
function rasta_wc_is_installed() {
	return file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
}

/* ─── Install / activate ───────────────────────────────────────────────── */

/**
 * Activate WooCommerce if it is already installed but inactive.
 *
 * @return bool True on success (or if already active).
 */
function rasta_activate_woocommerce() {
	if ( rasta_wc_is_active() ) {
		return true;
	}

	if ( ! rasta_wc_is_installed() ) {
		return false;
	}

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return false;
	}

	if ( ! function_exists( 'activate_plugin' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$result = activate_plugin( 'woocommerce/woocommerce.php' );

	return ! is_wp_error( $result );
}

/**
 * Download, install, and activate WooCommerce from the plugin repository.
 *
 * @return bool True on success (or if already active).
 */
function rasta_install_woocommerce() {
	if ( rasta_wc_is_active() ) {
		return true;
	}

	if ( ! current_user_can( 'install_plugins' ) ) {
		return false;
	}

	/* Already installed — just activate. */
	if ( rasta_wc_is_installed() ) {
		return rasta_activate_woocommerce();
	}

	if ( ! function_exists( 'plugins_api' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	}
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';

	$api = plugins_api(
		'plugin_information',
		array(
			'slug'   => 'woocommerce',
			'fields' => array(
				'short_description' => false,
				'sections'          => false,
			),
		)
	);

	if ( is_wp_error( $api ) || empty( $api->download_link ) ) {
		return false;
	}

	$upgrader  = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
	$installed = $upgrader->install( $api->download_link );

	if ( is_wp_error( $installed ) || ! $installed ) {
		return false;
	}

	return rasta_activate_woocommerce();
}

/* ─── Auto-run ─────────────────────────────────────────────────────────── */

/**
 * Attempt to install and activate WooCommerce automatically.
 *
 * Runs once per hour at most (guarded by a transient) so a failure does not
 * re-trigger a download on every admin page load.
 *
 * @return void
 */
function rasta_maybe_auto_install_woocommerce() {
	if ( ! rasta_wc_auto_install_enabled() ) {
		return;
	}
	if ( rasta_wc_is_active() ) {
		return;
	}
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	if ( get_transient( 'rasta_wc_install_attempted' ) ) {
		return;
	}

	set_transient( 'rasta_wc_install_attempted', 1, HOUR_IN_SECONDS );

	$result = rasta_install_woocommerce();

	update_option( 'rasta_wc_auto_install_result', $result ? 'installed' : 'failed' );

	if ( $result && function_exists( 'flush_rewrite_rules' ) ) {
		flush_rewrite_rules();
	}
}
add_action( 'admin_init', 'rasta_maybe_auto_install_woocommerce', 20 );

/**
 * Kick off the installer immediately on theme activation.
 *
 * `admin_init` covers this too, but running on activation gives the most
 * immediate result right after the theme is switched.
 *
 * @return void
 */
function rasta_wc_auto_install_on_activation() {
	if ( ! rasta_wc_auto_install_enabled() || rasta_wc_is_active() ) {
		return;
	}
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	$result = rasta_install_woocommerce();
	update_option( 'rasta_wc_auto_install_result', $result ? 'installed' : 'failed' );
}
add_action( 'after_switch_theme', 'rasta_wc_auto_install_on_activation', 10 );

/* ─── Admin notice (status + manual fallback) ──────────────────────────── */

/**
 * Show a dismissible notice when WooCommerce is missing, with a manual
 * "install now" action and the result of any automatic attempt.
 *
 * @return void
 */
function rasta_wc_admin_notice() {
	if ( rasta_wc_is_active() ) {
		return;
	}

	if ( ! current_user_can( 'install_plugins' ) ) {
		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'راستا کامرس: برای استفاده از امکانات کامل فروشگاه (درگاه پرداخت و ارسال)، افزونه WooCommerce را نصب کنید.', 'rasta-commerce' )
		);
		return;
	}

	$result = get_option( 'rasta_wc_auto_install_result' );

	if ( 'failed' === $result ) {
		$message = esc_html__( 'نصب خودکار WooCommerce ناموفق بود. می‌توانید دوباره تلاش کنید یا آن را دستی نصب کنید.', 'rasta-commerce' );
	} else {
		$message = esc_html__( 'راستا کامرس می‌تواند افزونه WooCommerce را به‌صورت خودکار نصب و فعال کند.', 'rasta-commerce' );
	}

	$install_url = wp_nonce_url( admin_url( 'admin.php?rasta_install_wc=1' ), 'rasta_install_wc' );

	printf(
		'<div class="notice notice-info is-dismissible"><p>%s</p><p><a class="button button-primary" href="%s">%s</a> <a class="button" href="%s">%s</a></p></div>',
		$message, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		esc_url( $install_url ),
		esc_html__( 'نصب و فعال‌سازی WooCommerce', 'rasta-commerce' ),
		esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ),
		esc_html__( 'نصب دستی', 'rasta-commerce' )
	);
}
add_action( 'admin_notices', 'rasta_wc_admin_notice' );

/**
 * Handle the manual "install now" link from the admin notice.
 *
 * @return void
 */
function rasta_wc_handle_install_request() {
	if ( ! isset( $_GET['rasta_install_wc'] ) ) {
		return;
	}
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	check_admin_referer( 'rasta_install_wc' );

	$result = rasta_install_woocommerce();
	update_option( 'rasta_wc_auto_install_result', $result ? 'installed' : 'failed' );

	$redirect = add_query_arg( 'rasta_wc_status', $result ? 'installed' : 'failed', admin_url( 'themes.php' ) );
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_init', 'rasta_wc_handle_install_request', 30 );
