<?php
/**
 * Offline contract tests for the order notification emails.
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	$GLOBALS['rasta_mail_log']            = array();
	$GLOBALS['rasta_order_emails_enabled'] = true;

	function add_action( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function __( $text, $domain = null ) { return $text; }
	function esc_html( $text ) { return $text; }
	function esc_html__( $text, $domain = null ) { return $text; }
	function esc_attr( $text ) { return $text; }
	function esc_html_e( $text, $domain = null ) { echo $text; }

	function rasta_store_settings() {
		return array(
			'admin_email'   => 'owner@example.com',
			'order_emails'  => $GLOBALS['rasta_order_emails_enabled'],
			'email_subject' => 'سفارش جدید در فروشگاه',
		);
	}
	function get_option( $name ) { return 'owner@example.com'; }
	function is_email( $email ) { return false !== strpos( $email, '@' ); }
	function wp_mail( $to, $subject, $message, $headers ) {
		$GLOBALS['rasta_mail_log'][] = compact( 'to', 'subject', 'message', 'headers' );
		return true;
	}
	function get_bloginfo( $key ) { return 'فروشگاه تست'; }
	function get_theme_mod( $key, $default = '' ) { return $default; }
	function get_post_meta( $id, $key, $single = false ) {
		$data = array(
			'_rasta_order_items'         => array(
				array(
					'quantity'  => 2,
					'lineTotal' => 500000,
					'product'   => array( 'name' => 'هدفون بی‌سیم' ),
				),
			),
			'_rasta_order_total'         => 500000,
			'_rasta_customer_first_name' => 'علی',
			'_rasta_customer_last_name'  => 'رضایی',
			'_rasta_customer_phone'      => '09121234567',
			'_rasta_customer_email'      => 'a@example.com',
			'_rasta_customer_city'       => 'تهران',
			'_rasta_customer_province'   => 'تهران',
			'_rasta_customer_address'    => 'خیابان اصلی',
			'_rasta_customer_postcode'   => '1234567890',
			'_rasta_customer_notes'      => 'زنگ بزنید',
		);
		return array_key_exists( $key, $data ) ? $data[ $key ] : '';
	}
	function rasta_sanitize_brand_color( $color ) { return '#f25c54'; }
	function rasta_to_persian_digits( $value ) { return $value; }
	function rasta_format_currency_plain( $amount ) { return number_format( (float) $amount ) . ' تومان'; }

	require dirname( __DIR__, 2 ) . '/inc/notifications.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_notify_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	/* Email body builder. */
	$html = rasta_build_order_email_html( 42 );
	rasta_notify_assert( false !== strpos( $html, 'هدفون بی‌سیم' ), true, 'Email body should include the product name.' );
	rasta_notify_assert( false !== strpos( $html, 'علی رضایی' ), true, 'Email body should include the customer name.' );
	rasta_notify_assert( false !== strpos( $html, '500,000 تومان' ), true, 'Email body should include the formatted total.' );
	rasta_notify_assert( false !== strpos( $html, 'خیابان اصلی' ), true, 'Email body should include the address.' );

	/* Sending while enabled. */
	rasta_send_admin_order_email( 42 );
	rasta_notify_assert( count( $GLOBALS['rasta_mail_log'] ), 1, 'One email should be sent while notifications are enabled.' );
	rasta_notify_assert( $GLOBALS['rasta_mail_log'][0]['to'], 'owner@example.com', 'Email should be addressed to the configured admin.' );
	rasta_notify_assert( false !== strpos( $GLOBALS['rasta_mail_log'][0]['subject'], '42' ), true, 'Subject should include the order number.' );

	/* Sending while disabled. */
	$GLOBALS['rasta_mail_log']            = array();
	$GLOBALS['rasta_order_emails_enabled'] = false;
	rasta_send_admin_order_email( 42 );
	rasta_notify_assert( count( $GLOBALS['rasta_mail_log'] ), 0, 'No email should be sent while notifications are disabled.' );

	fwrite( STDOUT, "Notifications contract tests: PASS\n" );
}
