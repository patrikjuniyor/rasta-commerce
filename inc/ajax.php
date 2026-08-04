<?php
/**
 * Public AJAX endpoints used by the theme.
 *
 * Every endpoint returns data instead of trusted markup, allowing the frontend to
 * create DOM nodes safely without injecting server-provided HTML.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_rasta_product_search', 'rasta_ajax_product_search' );
add_action( 'wp_ajax_nopriv_rasta_product_search', 'rasta_ajax_product_search' );
add_action( 'wp_ajax_rasta_quick_view', 'rasta_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_rasta_quick_view', 'rasta_ajax_quick_view' );
add_action( 'wp_ajax_rasta_product_collection', 'rasta_ajax_product_collection' );
add_action( 'wp_ajax_nopriv_rasta_product_collection', 'rasta_ajax_product_collection' );
add_action( 'wp_ajax_rasta_product_compare', 'rasta_ajax_product_compare' );
add_action( 'wp_ajax_nopriv_rasta_product_compare', 'rasta_ajax_product_compare' );

/**
 * Stop an AJAX request when WooCommerce is unavailable.
 *
 * @return void
 */
function rasta_ajax_require_woocommerce() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'ووکامرس در دسترس نیست.', 'rasta-commerce' ) ), 503 );
	}
}

/**
 * Get a sanitized and de-duplicated list of requested product IDs.
 *
 * @param int $limit Maximum number of products accepted.
 * @return int[]
 */
function rasta_ajax_product_ids( $limit = 12 ) {
	$raw_ids = isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : array();

	if ( ! is_array( $raw_ids ) ) {
		$raw_ids = explode( ',', (string) $raw_ids );
	}

	$ids = array_filter( array_map( 'absint', $raw_ids ) );
	$ids = array_values( array_unique( $ids ) );

	return array_slice( $ids, 0, max( 1, absint( $limit ) ) );
}

/**
 * Get a visible, published WooCommerce product or null.
 *
 * @param int $product_id Product ID.
 * @return WC_Product|null
 */
function rasta_ajax_visible_product( $product_id ) {
	$product = wc_get_product( absint( $product_id ) );

	if ( ! $product instanceof WC_Product || ! $product->is_visible() || 'publish' !== get_post_status( $product->get_id() ) ) {
		return null;
	}

	return $product;
}

/**
 * Return a short, shopper-facing stock message.
 *
 * @param WC_Product $product Product instance.
 * @return string
 */
function rasta_ajax_stock_label( $product ) {
	if ( ! $product->is_in_stock() ) {
		return esc_html__( 'ناموجود', 'rasta-commerce' );
	}

	if ( $product->managing_stock() && null !== $product->get_stock_quantity() ) {
		/* translators: %s: quantity remaining. */
		return sprintf( esc_html__( '%s عدد موجود', 'rasta-commerce' ), number_format_i18n( (int) $product->get_stock_quantity() ) );
	}

	return esc_html__( 'موجود', 'rasta-commerce' );
}

/**
 * Build a small, safe product record for frontend interfaces.
 *
 * @param WC_Product $product Product instance.
 * @return array<string, mixed>
 */
function rasta_ajax_product_payload( $product ) {
	$image_url = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );
	$price     = wp_strip_all_tags( html_entity_decode( $product->get_price_html(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
	$rating    = (float) $product->get_average_rating();
	$can_add   = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();

	return array(
		'id'          => $product->get_id(),
		'name'        => wp_strip_all_tags( $product->get_name() ),
		'url'         => esc_url_raw( $product->get_permalink() ),
		'image'       => esc_url_raw( $image_url ),
		'imageAlt'    => wp_strip_all_tags( $product->get_name() ),
		'price'       => $price,
		'category'    => wp_strip_all_tags( wc_get_product_category_list( $product->get_id(), '، ' ) ),
		'sku'         => wp_strip_all_tags( $product->get_sku() ),
		'rating'      => $rating > 0 ? number_format_i18n( $rating, 1 ) : '',
		'stock'       => rasta_ajax_stock_label( $product ),
		'inStock'     => $product->is_in_stock(),
		'canAjaxAdd'  => $can_add,
		'addToCartUrl'=> esc_url_raw( $can_add ? $product->add_to_cart_url() : $product->get_permalink() ),
		'addToCartLabel' => $can_add ? esc_html__( 'افزودن به سبد', 'rasta-commerce' ) : esc_html__( 'مشاهده و انتخاب گزینه', 'rasta-commerce' ),
	);
}

/**
 * Return product attributes as comparison-ready key/value pairs.
 *
 * @param WC_Product $product Product instance.
 * @return array<string, array{label: string, value: string}>
 */
function rasta_ajax_product_attributes( $product ) {
	$attributes = array();

	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute instanceof WC_Product_Attribute || ! $attribute->get_visible() ) {
			continue;
		}

		$name   = $attribute->get_name();
		$label  = wp_strip_all_tags( wc_attribute_label( $name ) );
		$values = array();

		if ( $attribute->is_taxonomy() ) {
			$values = wc_get_product_terms( $product->get_id(), $name, array( 'fields' => 'names' ) );
		} else {
			$values = $attribute->get_options();
		}

		$values = array_filter( array_map( 'wp_strip_all_tags', $values ) );
		if ( ! $label || empty( $values ) ) {
			continue;
		}

		$key = 'attribute_' . sanitize_title( $label );
		$attributes[ $key ] = array(
			'label' => $label,
			'value' => implode( '، ', $values ),
		);
	}

	return $attributes;
}

/**
 * Return a small, sanitized product-search result set.
 *
 * @return void
 */
function rasta_ajax_product_search() {
	check_ajax_referer( 'rasta_product_search', 'nonce' );
	rasta_ajax_require_woocommerce();

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
		$items[] = rasta_ajax_product_payload( $product );
	}

	wp_send_json_success( array( 'items' => $items ) );
}

/**
 * Return data for the product quick-view drawer.
 *
 * @return void
 */
function rasta_ajax_quick_view() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );
	rasta_ajax_require_woocommerce();

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	$product    = rasta_ajax_visible_product( $product_id );

	if ( ! $product ) {
		wp_send_json_error( array( 'message' => esc_html__( 'محصول موردنظر در دسترس نیست.', 'rasta-commerce' ) ), 404 );
	}

	$item                = rasta_ajax_product_payload( $product );
	$item['description'] = wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 34 );
	$item['isOnSale']    = $product->is_on_sale();

	wp_send_json_success( array( 'item' => $item ) );
}

/**
 * Return a product collection for local wishlist and recently viewed interfaces.
 *
 * @return void
 */
function rasta_ajax_product_collection() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );
	rasta_ajax_require_woocommerce();

	$items = array();
	foreach ( rasta_ajax_product_ids() as $product_id ) {
		$product = rasta_ajax_visible_product( $product_id );
		if ( $product ) {
			$items[] = rasta_ajax_product_payload( $product );
		}
	}

	wp_send_json_success( array( 'items' => $items ) );
}

/**
 * Return a structured, attribute-aware comparison payload for up to four products.
 *
 * @return void
 */
function rasta_ajax_product_compare() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );
	rasta_ajax_require_woocommerce();

	$products = array();
	foreach ( rasta_ajax_product_ids( 4 ) as $product_id ) {
		$product = rasta_ajax_visible_product( $product_id );
		if ( $product ) {
			$products[] = $product;
		}
	}

	if ( empty( $products ) ) {
		wp_send_json_success(
			array(
				'items' => array(),
				'rows'  => array(),
			)
		);
	}

	$rows = array(
		'price' => array(
			'label'  => esc_html__( 'قیمت', 'rasta-commerce' ),
			'values' => array(),
		),
		'availability' => array(
			'label'  => esc_html__( 'وضعیت موجودی', 'rasta-commerce' ),
			'values' => array(),
		),
		'sku' => array(
			'label'  => esc_html__( 'کد محصول', 'rasta-commerce' ),
			'values' => array(),
		),
	);

	foreach ( $products as $product ) {
		$product_id = $product->get_id();
		$payload    = rasta_ajax_product_payload( $product );
		$rows['price']['values'][ $product_id ]        = $payload['price'] ? $payload['price'] : '—';
		$rows['availability']['values'][ $product_id ] = $payload['stock'];
		$rows['sku']['values'][ $product_id ]          = $payload['sku'] ? $payload['sku'] : '—';

		foreach ( rasta_ajax_product_attributes( $product ) as $key => $attribute ) {
			if ( ! isset( $rows[ $key ] ) ) {
				$rows[ $key ] = array(
					'label'  => $attribute['label'],
					'values' => array(),
				);
			}
			$rows[ $key ]['values'][ $product_id ] = $attribute['value'];
		}
	}

	$comparison_rows = array();
	foreach ( $rows as $row ) {
		$values = array();
		foreach ( $products as $product ) {
			$values[] = isset( $row['values'][ $product->get_id() ] ) ? $row['values'][ $product->get_id() ] : '—';
		}
		$comparison_rows[] = array(
			'label'  => $row['label'],
			'values' => $values,
		);
	}

	$items = array_map( 'rasta_ajax_product_payload', $products );
	wp_send_json_success(
		array(
			'items' => $items,
			'rows'  => $comparison_rows,
		)
	);
}
