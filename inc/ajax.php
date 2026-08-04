<?php
/**
 * Public AJAX endpoints used by the theme.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_rasta_product_search', 'rasta_ajax_product_search' );
add_action( 'wp_ajax_nopriv_rasta_product_search', 'rasta_ajax_product_search' );

/**
 * Return a small, sanitized product-search result set.
 *
 * @return void
 */
function rasta_ajax_product_search() {
	check_ajax_referer( 'rasta_product_search', 'nonce' );

	if ( ! function_exists( 'wc_get_products' ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'ووکامرس در دسترس نیست.', 'rasta-commerce' ) ), 503 );
	}

	$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( array( 'items' => array() ) );
	}

	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 6,
			's'       => $term,
			'orderby' => 'relevance',
		)
	);
	$items    = array();

	foreach ( $products as $product ) {
		$image_url = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );
		$items[]   = array(
			'id'    => $product->get_id(),
			'name'  => wp_strip_all_tags( $product->get_name() ),
			'url'   => get_permalink( $product->get_id() ),
			'image' => $image_url,
			'price' => wp_strip_all_tags( $product->get_price_html() ),
			'sku'   => wp_strip_all_tags( $product->get_sku() ),
		);
	}

	wp_send_json_success( array( 'items' => $items ) );
}
