<?php
/**
 * Offline contract tests for the store overview dashboard and product list.
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	function add_action( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function add_filter( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function __( $text, $domain = null ) { return $text; }
	function esc_html__( $text, $domain = null ) { return $text; }
	function esc_html( $text ) { return $text; }
	function esc_html_e( $text, $domain = null ) { echo $text; }
	function esc_attr( $text ) { return $text; }
	function esc_attr_e( $text, $domain = null ) { echo $text; }
	function esc_url( $url ) { return $url; }

	require dirname( __DIR__, 2 ) . '/inc/admin.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_admin_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	$labels = rasta_order_status_labels();
	rasta_admin_assert( count( $labels ), 5, 'Exactly five order statuses should be defined.' );
	rasta_admin_assert( isset( $labels['rasta-pending'] ), true, 'Pending status should be present.' );
	rasta_admin_assert( isset( $labels['rasta-processing'] ), true, 'Processing status should be present.' );
	rasta_admin_assert( isset( $labels['rasta-completed'] ), true, 'Completed status should be present.' );
	rasta_admin_assert( isset( $labels['rasta-cancelled'] ), true, 'Cancelled status should be present.' );
	rasta_admin_assert( isset( $labels['rasta-failed'] ), true, 'Failed status should be present.' );

	$columns = rasta_product_columns(
		array(
			'cb'    => 'Checkbox',
			'title' => 'Title',
			'date'  => 'Date',
		)
	);
	$keys    = array_keys( $columns );

	rasta_admin_assert( in_array( 'rasta_sku', $keys, true ), true, 'SKU column should be registered.' );
	rasta_admin_assert( in_array( 'rasta_price', $keys, true ), true, 'Price column should be registered.' );
	rasta_admin_assert( in_array( 'rasta_stock', $keys, true ), true, 'Stock column should be registered.' );

	$title_index = array_search( 'title', $keys, true );
	rasta_admin_assert( $keys[ $title_index + 1 ], 'rasta_sku', 'SKU column should follow the title column.' );

	fwrite( STDOUT, "Admin dashboard contract tests: PASS\n" );
}
