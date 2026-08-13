<?php
/**
 * Offline contract tests for the maintenance (coming-soon) mode.
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	if ( ! defined( 'RASTA_DIR' ) ) {
		define( 'RASTA_DIR', dirname( __DIR__, 2 ) );
	}

	$GLOBALS['rasta_test_theme_mods'] = array();

	function get_theme_mod( $name, $default = '' ) {
		return array_key_exists( $name, $GLOBALS['rasta_test_theme_mods'] ) ? $GLOBALS['rasta_test_theme_mods'][ $name ] : $default;
	}
	function add_action( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function add_filter( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function is_admin() { return false; }
	function is_user_logged_in() { return false; }
	function current_user_can( $cap ) { return true; }
	function esc_html__( $text, $domain = null ) { return $text; }
	function esc_html( $text ) { return $text; }
	function admin_url( $path = '' ) { return $path; }
	function wp_add_inline_style( $handle, $css ) { return true; }
	function locate_template( $templates ) { return ''; }
	function rest_get_url_prefix() { return 'wp-json'; }
	function sanitize_key( $key ) { return $key; }

	require dirname( __DIR__, 2 ) . '/inc/maintenance.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_maintenance_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	$GLOBALS['rasta_test_theme_mods']['rasta_enable_maintenance'] = true;
	rasta_maintenance_assert( rasta_maintenance_enabled(), true, 'Maintenance should be enabled when the toggle is on.' );

	$GLOBALS['rasta_test_theme_mods']['rasta_enable_maintenance'] = false;
	rasta_maintenance_assert( rasta_maintenance_enabled(), false, 'Maintenance should be disabled when the toggle is off.' );

	unset( $GLOBALS['rasta_test_theme_mods']['rasta_enable_maintenance'] );
	rasta_maintenance_assert( rasta_maintenance_enabled(), false, 'Maintenance should default to disabled.' );

	$template = rasta_maintenance_template();
	rasta_maintenance_assert( file_exists( $template ), true, 'Maintenance template should exist on disk.' );
	rasta_maintenance_assert( basename( $template ), 'maintenance.php', 'Maintenance template should resolve to maintenance.php.' );

	$GLOBALS['pagenow'] = 'wp-login.php';
	rasta_maintenance_assert( rasta_is_login_page(), true, 'wp-login.php should be recognised as a login page.' );

	$GLOBALS['pagenow'] = 'index.php';
	rasta_maintenance_assert( rasta_is_login_page(), false, 'A regular page should not be recognised as a login page.' );
	unset( $GLOBALS['pagenow'] );

	fwrite( STDOUT, "Maintenance contract tests: PASS\n" );
}
