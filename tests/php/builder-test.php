<?php
/**
 * Offline contract tests for the Rasta Builder module.
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	if ( ! defined( 'RASTA_VERSION' ) ) {
		define( 'RASTA_VERSION', '2.6.0' );
	}
	if ( ! defined( 'RASTA_DIR' ) ) {
		define( 'RASTA_DIR', dirname( __DIR__, 2 ) );
	}
	if ( ! defined( 'RASTA_URI' ) ) {
		define( 'RASTA_URI', '' );
	}

	/* ── WordPress stubs ── */
	$GLOBALS['rasta_builder_hooks'] = array();
	$GLOBALS['rasta_meta_boxes']    = array();
	$GLOBALS['rasta_shortcodes']    = array();
	$GLOBALS['rasta_meta']          = array();

	function add_action( $hook, $cb = null, $p = 10, $a = 1 ) { $GLOBALS['rasta_builder_hooks'][] = array( $hook, $cb, $p ); return true; }
	function add_filter( $hook, $cb = null, $p = 10, $a = 1 ) { $GLOBALS['rasta_builder_hooks'][] = array( $hook, $cb, $p ); return true; }
	function add_meta_box( $id, $title, $cb, $screen, $ctx, $prio ) { $GLOBALS['rasta_meta_boxes'][] = compact( 'id', 'screen', 'cb' ); }
	function add_shortcode( $tag, $cb ) { $GLOBALS['rasta_shortcodes'][ $tag ] = $cb; }
	function __( $t, $d = null ) { return $t; }
	function esc_html__( $t, $d = null ) { return $t; }
	function esc_html( $t ) { return $t; }
	function esc_attr( $t ) { return $t; }
	function esc_url( $u ) { return $u; }
	function esc_url_raw( $u ) { return $u; }
	function esc_textarea( $t ) { return $t; }
	function sanitize_key( $k ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $k ) ); }
	function sanitize_text_field( $t ) { return trim( strip_tags( (string) $t ) ); }
	function sanitize_textarea_field( $t ) { return trim( strip_tags( (string) $t ) ); }
	function absint( $v ) { return abs( (int) $v ); }
	function wp_parse_args( $a, $d ) { return array_merge( (array) $d, (array) $a ); }
	function wp_json_encode( $d ) { return json_encode( $d ); }
	function wp_unslash( $v ) { return $v; }
	function wp_verify_nonce( $n, $a ) { return true; }
	function current_user_can( $c ) { return true; }
	function get_post_meta( $id, $k, $single = false ) { return isset( $GLOBALS['rasta_meta'][ $k ] ) ? $GLOBALS['rasta_meta'][ $k ] : ''; }
	function update_post_meta( $id, $k, $v ) { $GLOBALS['rasta_meta'][ $k ] = $v; return true; }
	function delete_post_meta( $id, $k ) { unset( $GLOBALS['rasta_meta'][ $k ] ); return true; }
	function apply_filters( $h, $v ) { return $v; }
	function wpautop( $t ) { return $t; }
	function shortcode_atts( $d, $a, $tag ) { return array_merge( $d, $a ); }
	function get_the_ID() { return 1; }
	function is_admin() { return false; }
	function is_singular() { return true; }
	function in_the_loop() { return true; }
	function is_main_query() { return true; }
	function wp_enqueue_style() { return true; }
	function wp_enqueue_script() { return true; }
	function get_current_screen() { return null; }
	function get_post() { return null; }

	/* Minimal product helpers for the products/categories elements. */
	function rasta_get_products( $args = array() ) { return array(); }
	function rasta_get_product_categories( $c = 6 ) { return array(); }
	function rasta_get_product_payload( $p ) { return array(); }
	function rasta_format_currency_plain( $a ) { return (string) $a; }
	function get_term_link( $t ) { return ''; }
	function is_wp_error( $x ) { return false; }

	require dirname( __DIR__, 2 ) . '/inc/builder.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_builder_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	$has_hook = function ( $hook, $cb ) {
		foreach ( $GLOBALS['rasta_builder_hooks'] as $h ) {
			if ( $h[0] === $hook && $h[1] === $cb ) {
				return true;
			}
		}
		return false;
	};

	/* ── Registry ── */
	$elements = rasta_builder_elements();
	rasta_builder_assert( count( $elements ) >= 10, true, 'At least 10 elements should be registered.' );
	rasta_builder_assert( isset( $elements['heading'] ), true, 'Heading element should exist.' );
	rasta_builder_assert( isset( $elements['products'] ), true, 'Products element should exist.' );
	rasta_builder_assert( is_callable( $elements['heading']['render'] ), true, 'Heading render callback should be callable.' );
	rasta_builder_assert( isset( $elements['heading']['defaults']['title'] ), true, 'Heading should have a default title.' );

	/* ── Sanitization ── */
	$clean = rasta_builder_sanitize_layout( array(
		array( 'type' => 'heading', 'props' => array( 'title' => 'سلام <script>', 'level' => 'h2', 'align' => 'center' ) ),
		array( 'type' => 'bogus', 'props' => array() ),
		array( 'type' => 'spacer', 'props' => array( 'height' => '99' ) ),
	) );
	rasta_builder_assert( count( $clean ), 2, 'Unknown types should be dropped.' );
	rasta_builder_assert( $clean[0]['props']['title'], 'سلام', 'HTML should be stripped from title.' );
	rasta_builder_assert( $clean[1]['props']['height'], 99, 'Number should be coerced to int.' );

	/* Sanitize a JSON string input. */
	$clean2 = rasta_builder_sanitize_layout( '[{"type":"divider","props":{}}]' );
	rasta_builder_assert( count( $clean2 ), 1, 'JSON string input should be parsed.' );
	rasta_builder_assert( $clean2[0]['type'], 'divider', 'Divider type should survive.' );

	/* Sanitize garbage. */
	rasta_builder_assert( rasta_builder_sanitize_layout( 'garbage' ), array(), 'Garbage input should yield empty array.' );

	/* ── Rendering ── */
	$html = rasta_builder_render_layout( array(
		array( 'type' => 'heading', 'props' => array( 'title' => 'تیتر', 'level' => 'h2', 'align' => 'center' ) ),
		array( 'type' => 'divider', 'props' => array() ),
	) );
	rasta_builder_assert( false !== strpos( $html, '<h2' ), true, 'Heading should render an h2.' );
	rasta_builder_assert( false !== strpos( $html, 'تیتر' ), true, 'Heading should render the title.' );
	rasta_builder_assert( false !== strpos( $html, 'rb-divider' ), true, 'Divider should render its class.' );

	/* ── Hooks ── */
	rasta_builder_assert( $has_hook( 'add_meta_boxes', 'rasta_builder_register_metabox' ), true, 'Metabox should be registered.' );
	rasta_builder_assert( $has_hook( 'save_post', 'rasta_builder_save' ), true, 'Save handler should be registered.' );
	rasta_builder_assert( $has_hook( 'the_content', 'rasta_builder_render_content' ), true, 'Content filter should be registered.' );
	rasta_builder_assert( $has_hook( 'init', 'rasta_builder_register_shortcode' ), true, 'Shortcode should be registered.' );

	/* Metabox registration covers page + product. */
	rasta_builder_register_metabox();
	$screens = array();
	foreach ( $GLOBALS['rasta_meta_boxes'] as $mb ) {
		if ( 'rasta-builder' === $mb['id'] ) {
			$screens[] = $mb['screen'];
		}
	}
	rasta_builder_assert( in_array( 'page', $screens, true ), true, 'Metabox should target pages.' );
	rasta_builder_assert( in_array( 'rasta_product', $screens, true ), true, 'Metabox should target products.' );

	fwrite( STDOUT, "Builder contract tests: PASS\n" );
}
