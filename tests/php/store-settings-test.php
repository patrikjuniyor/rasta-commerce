<?php
/**
 * Offline contract tests for the store settings page.
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	function add_action( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function add_filter( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function __( $text, $domain = null ) { return $text; }
	function esc_html__( $text, $domain = null ) { return $text; }
	function esc_html( $text ) { return $text; }
	function esc_attr( $text ) { return $text; }
	function esc_attr_e( $text, $domain = null ) { echo $text; }
	function esc_url( $url ) { return $url; }
	function get_option( $name, $default = false ) { return 'admin_email' === $name ? 'owner@example.com' : $default; }
	function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, (array) $args ); }
	function sanitize_email( $email ) { return filter_var( $email, FILTER_VALIDATE_EMAIL ) ? $email : ''; }
	function sanitize_text_field( $text ) { return trim( strip_tags( (string) $text ) ); }
	function current_user_can( $cap ) { return true; }
	function settings_fields( $group ) { return $group; }
	function submit_button( $text ) { return $text; }

	require dirname( __DIR__, 2 ) . '/inc/store-settings.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_settings_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	/* Sanitizer: valid input. */
	$clean = rasta_sanitize_store_settings(
		array(
			'admin_email'   => 'Owner@Example.com',
			'order_emails'  => '1',
			'email_subject' => 'سفارش <b>جدید</b>',
		)
	);
	rasta_settings_assert( $clean['admin_email'], 'Owner@Example.com', 'Valid email should pass through.' );
	rasta_settings_assert( $clean['order_emails'], true, 'Checked order_emails should be true.' );
	rasta_settings_assert( $clean['email_subject'], 'سفارش جدید', 'Subject should have tags stripped.' );

	/* Sanitizer: unchecked checkbox and invalid email. */
	$clean = rasta_sanitize_store_settings(
		array(
			'admin_email'   => 'not-an-email',
			'email_subject' => 'x',
		)
	);
	rasta_settings_assert( $clean['admin_email'], '', 'Invalid email should sanitize to empty string.' );
	rasta_settings_assert( $clean['order_emails'], false, 'Missing checkbox should resolve to false.' );

	/* Sanitizer: non-array input. */
	$clean = rasta_sanitize_store_settings( 'garbage' );
	rasta_settings_assert( is_array( $clean ), true, 'Non-array input should still yield an array.' );

	/* Defaults merge. */
	$defaults = rasta_store_settings_defaults();
	rasta_settings_assert( $defaults['admin_email'], 'owner@example.com', 'Default admin email should fall back to the site admin email.' );
	rasta_settings_assert( $defaults['order_emails'], true, 'Order emails should default to enabled.' );

	fwrite( STDOUT, "Store settings contract tests: PASS\n" );
}
