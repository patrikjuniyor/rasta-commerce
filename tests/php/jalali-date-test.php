<?php
/**
 * Offline contract tests for Jalali conversion helpers.
 */

define( 'ABSPATH', __DIR__ );

function current_time( $type ) {
	return 'timestamp' === $type ? 1785801600 : '';
}

function wp_timezone() {
	return new DateTimeZone( 'UTC' );
}

function get_post_timestamp() {
	return 1785801600;
}

function get_the_ID() {
	return 1;
}

function absint( $value ) {
	return abs( (int) $value );
}

require dirname( __DIR__, 2 ) . '/inc/jalali.php';

/**
 * @param mixed  $actual Actual value.
 * @param mixed  $expected Expected value.
 * @param string $message Failure message.
 * @return void
 */
function rasta_jalali_assert_same( $actual, $expected, $message ) {
	if ( $actual !== $expected ) {
		fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

rasta_jalali_assert_same( rasta_gregorian_to_jalali( 2026, 8, 4 ), array( 1405, 5, 13 ), 'Gregorian 2026-08-04 should convert to 1405-05-13.' );
rasta_jalali_assert_same( rasta_jalali_date( 'j F Y', 1785801600 ), '۱۳ مرداد ۱۴۰۵', 'Formatted Jalali date should use Persian month and digits.' );
rasta_jalali_assert_same( rasta_jalali_date( 'Y/m/d', 1785801600, false ), '1405/05/13', 'ASCII mode should preserve Latin digits.' );
rasta_jalali_assert_same( rasta_to_persian_digits( '1405/05/13' ), '۱۴۰۵/۰۵/۱۳', 'Persian digit conversion should cover all Latin digits.' );

fwrite( STDOUT, "Jalali date contract tests: PASS\n" );
