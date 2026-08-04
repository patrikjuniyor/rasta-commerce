<?php
/**
 * Lightweight Jalali date helpers for frontend dates rendered by the theme.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert a Gregorian calendar date to Jalali (Persian) components.
 *
 * @param int $year  Gregorian year.
 * @param int $month Gregorian month.
 * @param int $day   Gregorian day.
 * @return array{0: int, 1: int, 2: int}
 */
function rasta_gregorian_to_jalali( $year, $month, $day ) {
	$gregorian_days_before_month = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gregorian_year              = (int) $year;
	$gregorian_month             = (int) $month;
	$gregorian_day               = (int) $day;

	if ( $gregorian_year > 1600 ) {
		$jalali_year = 979;
		$gregorian_year -= 1600;
	} else {
		$jalali_year = 0;
		$gregorian_year -= 621;
	}

	$adjusted_year = $gregorian_month > 2 ? $gregorian_year + 1 : $gregorian_year;
	$days          = ( 365 * $gregorian_year )
		+ intdiv( $adjusted_year + 3, 4 )
		- intdiv( $adjusted_year + 99, 100 )
		+ intdiv( $adjusted_year + 399, 400 )
		- 80
		+ $gregorian_day
		+ $gregorian_days_before_month[ $gregorian_month - 1 ];

	$jalali_year += 33 * intdiv( $days, 12053 );
	$days        %= 12053;
	$jalali_year += 4 * intdiv( $days, 1461 );
	$days        %= 1461;

	if ( $days > 365 ) {
		$jalali_year += intdiv( $days - 1, 365 );
		$days         = ( $days - 1 ) % 365;
	}

	if ( $days < 186 ) {
		$jalali_month = 1 + intdiv( $days, 31 );
		$jalali_day   = 1 + ( $days % 31 );
	} else {
		$jalali_month = 7 + intdiv( $days - 186, 30 );
		$jalali_day   = 1 + ( ( $days - 186 ) % 30 );
	}

	return array( $jalali_year, $jalali_month, $jalali_day );
}

/**
 * Convert Latin digits to Persian digits for a shopper-facing string.
 *
 * @param string $value Value containing Latin digits.
 * @return string
 */
function rasta_to_persian_digits( $value ) {
	return strtr(
		(string) $value,
		array(
			'0' => '۰',
			'1' => '۱',
			'2' => '۲',
			'3' => '۳',
			'4' => '۴',
			'5' => '۵',
			'6' => '۶',
			'7' => '۷',
			'8' => '۸',
			'9' => '۹',
		)
	);
}

/**
 * Format a Unix timestamp using the Jalali calendar in the WordPress timezone.
 *
 * Supported date-format tokens: Y, y, m, n, d, j and F.
 *
 * @param string   $format    Output format.
 * @param int|null $timestamp Unix timestamp; current time when omitted.
 * @param bool     $digits    Whether to use Persian digits.
 * @return string
 */
function rasta_jalali_date( $format = 'j F Y', $timestamp = null, $digits = true ) {
	$timestamp = null === $timestamp ? current_time( 'timestamp' ) : (int) $timestamp;
	$datetime  = ( new DateTimeImmutable( '@' . $timestamp ) )->setTimezone( wp_timezone() );
	$date      = $datetime->format( 'Y-n-j' );
	$parts     = array_map( 'absint', explode( '-', $date ) );
	$jalali    = rasta_gregorian_to_jalali( $parts[0], $parts[1], $parts[2] );
	$months    = array(
		1  => 'فروردین',
		2  => 'اردیبهشت',
		3  => 'خرداد',
		4  => 'تیر',
		5  => 'مرداد',
		6  => 'شهریور',
		7  => 'مهر',
		8  => 'آبان',
		9  => 'آذر',
		10 => 'دی',
		11 => 'بهمن',
		12 => 'اسفند',
	);

	$replacements = array(
		'Y' => (string) $jalali[0],
		'y' => substr( (string) $jalali[0], -2 ),
		'm' => sprintf( '%02d', $jalali[1] ),
		'n' => (string) $jalali[1],
		'd' => sprintf( '%02d', $jalali[2] ),
		'j' => (string) $jalali[2],
		'F' => $months[ $jalali[1] ],
	);
	$output       = strtr( $format, $replacements );

	return $digits ? rasta_to_persian_digits( $output ) : $output;
}

/**
 * Return the Jalali display date for the current post.
 *
 * @param string $format Display format.
 * @return string
 */
function rasta_get_the_jalali_date( $format = 'j F Y' ) {
	$timestamp = get_post_timestamp( get_the_ID() );

	return rasta_jalali_date( $format, $timestamp );
}
