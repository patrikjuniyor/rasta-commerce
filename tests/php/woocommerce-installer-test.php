<?php
/**
 * Offline contract tests for the WooCommerce auto-installer.
 *
 * `WooCommerce` is intentionally NOT defined here, so class_exists('WooCommerce')
 * is naturally false — this exercises the "not active" path of the module.
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
		define( 'WP_PLUGIN_DIR', '/tmp/fake-plugins' );
	}

	$GLOBALS['rasta_wc_hooks']      = array();
	$GLOBALS['rasta_wc_options']    = array();
	$GLOBALS['rasta_wc_transients'] = array();
	$GLOBALS['rasta_wc_installed']  = false;
	$GLOBALS['rasta_wc_activated']  = null;

	function add_action( $hook, $cb = null, $p = 10, $a = 1 ) { $GLOBALS['rasta_wc_hooks'][] = array( $hook, $cb, $p ); return true; }
	function add_filter( $hook, $cb = null, $p = 10, $a = 1 ) { $GLOBALS['rasta_wc_hooks'][] = array( $hook, $cb, $p ); return true; }
	function esc_html__( $t, $d = null ) { return $t; }
	function esc_html( $t ) { return $t; }
	function esc_url( $u ) { return $u; }
	function current_user_can( $c ) { return true; }
	function get_option( $k, $d = false ) { return isset( $GLOBALS['rasta_wc_options'][ $k ] ) ? $GLOBALS['rasta_wc_options'][ $k ] : $d; }
	function update_option( $k, $v ) { $GLOBALS['rasta_wc_options'][ $k ] = $v; return true; }
	function get_transient( $k ) { return isset( $GLOBALS['rasta_wc_transients'][ $k ] ) ? $GLOBALS['rasta_wc_transients'][ $k ] : false; }
	function set_transient( $k, $v, $e ) { $GLOBALS['rasta_wc_transients'][ $k ] = $v; return true; }
	function activate_plugin( $p ) { $GLOBALS['rasta_wc_activated'] = $p; return null; }
	function is_wp_error( $x ) { return false; }
	function apply_filters( $h, $v ) { return $v; }
	function plugins_api() { return (object) array( 'download_link' => 'https://downloads.wordpress.org/plugin/woocommerce.zip' ); }
	function wp_nonce_url( $u, $a ) { return $u; }
	function admin_url( $p = '' ) { return $p; }
	function check_admin_referer( $a ) { return true; }
	function flush_rewrite_rules() {}

	/* Let the module see the fake filesystem state. */
	function rasta_wc_test_file_exists( $p ) {
		return $GLOBALS['rasta_wc_installed'];
	}

	require dirname( __DIR__, 2 ) . '/inc/woocommerce-installer.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_wc_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	$has_hook = function ( $hook, $cb ) {
		foreach ( $GLOBALS['rasta_wc_hooks'] as $h ) {
			if ( $h[0] === $hook && $h[1] === $cb ) {
				return true;
			}
		}
		return false;
	};

	/* Feature flag defaults on. */
	rasta_wc_assert( rasta_wc_auto_install_enabled(), true, 'Auto-install should default to enabled.' );

	/* WC is NOT active in this environment. */
	rasta_wc_assert( rasta_wc_is_active(), false, 'WC should be detected as inactive.' );
	rasta_wc_assert( rasta_wc_is_installed(), false, 'WC should not be installed by default.' );

	/* Not installed, not active → activation returns false. */
	rasta_wc_assert( rasta_activate_woocommerce(), false, 'Activation should fail when WC is not installed.' );

	/* Auto-run is guarded by a transient. */
	$GLOBALS['rasta_wc_transients']['rasta_wc_install_attempted'] = 1;
	rasta_maybe_auto_install_woocommerce();
	rasta_wc_assert( isset( $GLOBALS['rasta_wc_options']['rasta_wc_auto_install_result'] ), false, 'Guard transient should skip the install attempt.' );

	/* Hooks. */
	rasta_wc_assert( $has_hook( 'admin_init', 'rasta_maybe_auto_install_woocommerce' ), true, 'Auto-install should run on admin_init.' );
	rasta_wc_assert( $has_hook( 'after_switch_theme', 'rasta_wc_auto_install_on_activation' ), true, 'Auto-install should run on theme activation.' );
	rasta_wc_assert( $has_hook( 'admin_notices', 'rasta_wc_admin_notice' ), true, 'Admin notice should be registered.' );
	rasta_wc_assert( $has_hook( 'admin_init', 'rasta_wc_handle_install_request' ), true, 'Manual install handler should be registered.' );

	/* The activation path must target the canonical plugin file. */
	$fake_plugin_file = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
	@mkdir( WP_PLUGIN_DIR . '/woocommerce', 0777, true );
	file_put_contents( $fake_plugin_file, '<?php' );
	$GLOBALS['rasta_wc_activated'] = null;
	rasta_wc_assert( rasta_wc_is_installed(), true, 'Installed state should be detected via the real filesystem.' );
	rasta_activate_woocommerce();
	rasta_wc_assert( $GLOBALS['rasta_wc_activated'], 'woocommerce/woocommerce.php', 'Activation should target woocommerce/woocommerce.php.' );
	@unlink( $fake_plugin_file );

	fwrite( STDOUT, "WooCommerce installer contract tests: PASS\n" );
}
