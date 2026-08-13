<?php
/**
 * Maintenance (coming-soon) mode.
 *
 * Shows a branded "coming soon" page to logged-out visitors while the store
 * owner works on the site. Logged-in users and the REST API stay accessible.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return whether maintenance mode is currently enabled.
 *
 * @return bool
 */
function rasta_maintenance_enabled() {
	return (bool) get_theme_mod( 'rasta_enable_maintenance', false );
}

/**
 * Locate the maintenance template.
 *
 * @return string Absolute path to the maintenance template.
 */
function rasta_maintenance_template() {
	$template = locate_template( array( 'maintenance.php' ) );

	return $template ? $template : RASTA_DIR . '/maintenance.php';
}

/**
 * Redirect logged-out visitors to the maintenance page.
 *
 * @return void
 */
function rasta_maybe_show_maintenance() {
	if ( is_admin() || ! rasta_maintenance_enabled() ) {
		return;
	}

	/* Keep the login, registration, and REST endpoints reachable. */
	if ( is_user_logged_in() || rasta_is_login_page() || rasta_is_rest_request() ) {
		return;
	}

	nocache_headers();
	status_header( 503 );

	require rasta_maintenance_template();
	exit;
}
add_action( 'template_redirect', 'rasta_maybe_show_maintenance', 1 );

/**
 * Detect whether the current request targets a login/registration page.
 *
 * @return bool
 */
function rasta_is_login_page() {
	global $pagenow;

	if ( isset( $pagenow ) && in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return true;
	}

	/* The wp_login_url() points at wp-login.php, which is covered above. */
	return false;
}

/**
 * Detect whether the current request is a REST API request.
 *
 * @return bool
 */
function rasta_is_rest_request() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	$rest_prefix = rest_get_url_prefix();
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$request_uri = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
		return $request_uri && 0 === strpos( ltrim( $request_uri, '/' ), $rest_prefix );
	}

	return false;
}

/**
 * Add an admin bar reminder while maintenance mode is active.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 * @return void
 */
function rasta_maintenance_admin_bar( $wp_admin_bar ) {
	if ( ! rasta_maintenance_enabled() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$wp_admin_bar->add_node(
		array(
			'id'    => 'rasta-maintenance',
			'title' => esc_html__( 'حالت تعمیر فعال است', 'rasta-commerce' ),
			'href'  => admin_url( 'customize.php?autofocus[section]=rasta_store_state' ),
			'meta'  => array(
				'class' => 'rasta-maintenance-warning',
			),
		)
	);
}
add_action( 'admin_bar_menu', 'rasta_maintenance_admin_bar', 90 );

/**
 * Style the maintenance admin-bar warning.
 *
 * @return void
 */
function rasta_maintenance_admin_styles() {
	wp_add_inline_style(
		'wp-admin',
		'#wpadminbar .rasta-maintenance-warning > a { background: #996800 !important; color: #fff !important; }'
	);
}
add_action( 'admin_enqueue_scripts', 'rasta_maintenance_admin_styles' );
