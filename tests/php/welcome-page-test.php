<?php
/**
 * Offline contract tests for the installation welcome page.
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	if ( ! defined( 'RASTA_VERSION' ) ) {
		define( 'RASTA_VERSION', '2.5.0' );
	}
	if ( ! defined( 'RASTA_DIR' ) ) {
		define( 'RASTA_DIR', dirname( __DIR__, 2 ) );
	}

	$GLOBALS['rasta_theme_pages']   = array();
	$GLOBALS['rasta_hooks']         = array();
	$GLOBALS['rasta_welcome_dismissed'] = null;

	function add_action( $hook, $callback = null, $priority = 10, $accepted = 1 ) {
		$GLOBALS['rasta_hooks'][] = compact( 'hook', 'callback', 'priority' );
		return true;
	}
	function add_filter( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function esc_html__( $text, $domain = null ) { return $text; }
	function esc_html( $text ) { return $text; }
	function esc_html_e( $text, $domain = null ) { echo $text; }
	function esc_attr( $text ) { return $text; }
	function esc_url( $url ) { return $url; }
	function current_user_can( $cap ) { return true; }
	function add_theme_page( $title, $menu, $cap, $slug, $cb ) {
		$GLOBALS['rasta_theme_pages'][] = compact( 'title', 'menu', 'cap', 'slug', 'cb' );
	}
	function wp_add_inline_style( $handle, $css ) { return true; }
	function get_option( $name ) { return $GLOBALS['rasta_welcome_dismissed']; }
	function update_option( $name, $value ) { $GLOBALS['rasta_welcome_dismissed'] = $value; }
	function admin_url( $path = '' ) { return $path; }
	function home_url( $path = '' ) { return $path; }
	function rasta_to_persian_digits( $value ) { return $value; }

	require dirname( __DIR__, 2 ) . '/inc/welcome.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_welcome_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	/* Menu registration. */
	rasta_welcome_menu();
	rasta_welcome_assert( count( $GLOBALS['rasta_theme_pages'] ), 1, 'One theme page should be registered.' );
	rasta_welcome_assert( $GLOBALS['rasta_theme_pages'][0]['slug'], 'rasta-welcome', 'Welcome page slug should be rasta-welcome.' );
	rasta_welcome_assert( $GLOBALS['rasta_theme_pages'][0]['cap'], 'manage_options', 'Welcome page should require manage_options.' );
	rasta_welcome_assert( is_callable( $GLOBALS['rasta_theme_pages'][0]['cb'] ), true, 'Welcome page callback should be callable.' );

	/* The redirect must be registered on after_switch_theme with a late priority,
	 * so store-page creation and rewrite flush run first. */
	$redirect_hooks = array_values(
		array_filter(
			$GLOBALS['rasta_hooks'],
			static function ( $entry ) {
				return 'after_switch_theme' === $entry['hook'] && 'rasta_welcome_redirect' === $entry['callback'];
			}
		)
	);
	rasta_welcome_assert( count( $redirect_hooks ), 1, 'Welcome redirect should be registered on after_switch_theme.' );
	rasta_welcome_assert( $redirect_hooks[0]['priority'], 99, 'Welcome redirect should run at priority 99 (after store-page creation).' );

	fwrite( STDOUT, "Welcome page contract tests: PASS\n" );
}
