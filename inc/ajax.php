<?php
/**
 * Public AJAX endpoints for product search, quick view, cart, compare, and wishlist.
 *
 * All endpoints work with the built-in product system. When WooCommerce is
 * active, the WC-backed versions take priority automatically.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ─── Endpoint registration ────────────────────────────────────────────── */

add_action( 'wp_ajax_rasta_product_search', 'rasta_ajax_product_search' );
add_action( 'wp_ajax_nopriv_rasta_product_search', 'rasta_ajax_product_search' );
add_action( 'wp_ajax_rasta_quick_view', 'rasta_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_rasta_quick_view', 'rasta_ajax_quick_view' );
add_action( 'wp_ajax_rasta_product_collection', 'rasta_ajax_product_collection' );
add_action( 'wp_ajax_nopriv_rasta_product_collection', 'rasta_ajax_product_collection' );
add_action( 'wp_ajax_rasta_product_compare', 'rasta_ajax_product_compare' );
add_action( 'wp_ajax_nopriv_rasta_product_compare', 'rasta_ajax_product_compare' );

/* Cart AJAX endpoints. */
add_action( 'wp_ajax_rasta_add_to_cart', 'rasta_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_rasta_add_to_cart', 'rasta_ajax_add_to_cart' );
add_action( 'wp_ajax_rasta_update_cart', 'rasta_ajax_update_cart' );
add_action( 'wp_ajax_nopriv_rasta_update_cart', 'rasta_ajax_update_cart' );
add_action( 'wp_ajax_rasta_remove_cart_item', 'rasta_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_rasta_remove_cart_item', 'rasta_ajax_remove_cart_item' );
add_action( 'wp_ajax_rasta_get_cart_count', 'rasta_ajax_get_cart_count' );
add_action( 'wp_ajax_nopriv_rasta_get_cart_count', 'rasta_ajax_get_cart_count' );

/* ─── Helpers ──────────────────────────────────────────────────────────── */

/**
 * Return whether WooCommerce is the active product backend.
 *
 * @return bool
 */
function rasta_using_woocommerce() {
	return class_exists( 'WooCommerce' );
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
 * Build a safe product payload that works for both backends.
 *
 * @param int $product_id Product post ID.
 * @return array<string, mixed>
 */
function rasta_ajax_build_product_payload( $product_id ) {
	$product_id = absint( $product_id );
	$post       = get_post( $product_id );

	if ( ! $post || 'publish' !== $post->post_status ) {
		return array();
	}

	/* WooCommerce backend. */
	if ( rasta_using_woocommerce() && 'product' === $post->post_type ) {
		return rasta_ajax_wc_product_payload( $product_id );
	}

	/* Built-in backend. */
	if ( 'rasta_product' === $post->post_type ) {
		return rasta_get_product_payload( $product_id );
	}

	return array();
}

/**
 * Build product payload using WooCommerce API.
 *
 * @param int $product_id WC product ID.
 * @return array<string, mixed>
 */
function rasta_ajax_wc_product_payload( $product_id ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return array();
	}

	$product = wc_get_product( $product_id );
	if ( ! $product instanceof \WC_Product || ! $product->is_visible() || 'publish' !== get_post_status( $product->get_id() ) ) {
		return array();
	}

	$image_url = $product->get_image_id()
		? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' )
		: wc_placeholder_img_src( 'woocommerce_thumbnail' );

	$price   = wp_strip_all_tags( html_entity_decode( $product->get_price_html(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
	$rating  = (float) $product->get_average_rating();
	$can_add = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();

	$sale_end_ts = 0;
	$sale_to     = $product->get_date_on_sale_to();
	if ( $sale_to ) {
		$sale_end_ts = (int) $sale_to->getTimestamp();
	}

	$stock_label = '';
	if ( ! $product->is_in_stock() ) {
		$stock_label = esc_html__( 'ناموجود', 'rasta-commerce' );
	} elseif ( $product->managing_stock() && null !== $product->get_stock_quantity() ) {
		$stock_label = sprintf(
			/* translators: %s: quantity remaining. */
			esc_html__( '%s عدد موجود', 'rasta-commerce' ),
			number_format_i18n( (int) $product->get_stock_quantity() )
		);
	} else {
		$stock_label = esc_html__( 'موجود', 'rasta-commerce' );
	}

	$attributes = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute instanceof \WC_Product_Attribute || ! $attribute->get_visible() ) {
			continue;
		}
		$name  = $attribute->get_name();
		$label = wp_strip_all_tags( wc_attribute_label( $name ) );
		$values = $attribute->is_taxonomy()
			? wc_get_product_terms( $product->get_id(), $name, array( 'fields' => 'names' ) )
			: $attribute->get_options();
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

	return array(
		'id'           => $product->get_id(),
		'name'         => wp_strip_all_tags( $product->get_name() ),
		'url'          => esc_url_raw( $product->get_permalink() ),
		'image'        => esc_url_raw( $image_url ),
		'imageAlt'     => wp_strip_all_tags( $product->get_name() ),
		'price'        => $price,
		'priceValue'   => (float) $product->get_price(),
		'regularPrice' => (float) $product->get_regular_price(),
		'salePrice'    => (float) $product->get_sale_price(),
		'isOnSale'     => $product->is_on_sale(),
		'saleEnd'      => $sale_end_ts,
		'category'     => wp_strip_all_tags( wc_get_product_category_list( $product->get_id(), '، ' ) ),
		'sku'          => wp_strip_all_tags( $product->get_sku() ),
		'rating'       => $rating > 0 ? number_format_i18n( $rating, 1 ) : '',
		'stock'        => $stock_label,
		'inStock'      => $product->is_in_stock(),
		'canAjaxAdd'   => $can_add,
		'isNew'        => false,
		'description'  => wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 34 ),
		'attributes'   => $attributes,
		'addToCartUrl' => esc_url_raw( $can_add ? $product->add_to_cart_url() : $product->get_permalink() ),
		'addToCartLabel' => $can_add ? esc_html__( 'افزودن به سبد', 'rasta-commerce' ) : esc_html__( 'مشاهده و انتخاب گزینه', 'rasta-commerce' ),
	);
}

/**
 * Get a visible, published product regardless of backend.
 *
 * @param int $product_id Product post ID.
 * @return array<string, mixed>|empty
 */
function rasta_ajax_visible_product( $product_id ) {
	return rasta_ajax_build_product_payload( $product_id );
}

/* ─── Product search ───────────────────────────────────────────────────── */

/**
 * Search products (built-in or WooCommerce) and return structured results.
 *
 * @return void
 */
function rasta_ajax_product_search() {
	check_ajax_referer( 'rasta_product_search', 'nonce' );

	$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( array( 'items' => array() ) );
	}

	$items = array();

	if ( rasta_using_woocommerce() && function_exists( 'wc_get_products' ) ) {
		$wc_products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 6,
				's'       => $term,
				'orderby' => 'relevance',
			)
		);

		foreach ( $wc_products as $product ) {
			$payload = rasta_ajax_wc_product_payload( $product->get_id() );
			if ( ! empty( $payload ) ) {
				$items[] = $payload;
			}
		}
	} else {
		$builtin = rasta_get_products(
			array(
				'limit'   => 6,
				'search'  => $term,
				'orderby' => 'relevance',
			)
		);

		foreach ( $builtin as $post ) {
			$payload = rasta_get_product_payload( $post );
			if ( ! empty( $payload ) ) {
				$items[] = $payload;
			}
		}
	}

	wp_send_json_success( array( 'items' => $items ) );
}

/* ─── Quick view ───────────────────────────────────────────────────────── */

/**
 * Return data for the product quick-view drawer.
 *
 * @return void
 */
function rasta_ajax_quick_view() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	$item       = rasta_ajax_visible_product( $product_id );

	if ( empty( $item ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'محصول موردنظر در دسترس نیست.', 'rasta-commerce' ) ), 404 );
	}

	wp_send_json_success( array( 'item' => $item ) );
}

/* ─── Product collection (wishlist & recently viewed) ──────────────────── */

/**
 * Return a product collection for wishlist and recently viewed.
 *
 * @return void
 */
function rasta_ajax_product_collection() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );

	$items = array();
	foreach ( rasta_ajax_product_ids() as $product_id ) {
		$payload = rasta_ajax_visible_product( $product_id );
		if ( ! empty( $payload ) ) {
			$items[] = $payload;
		}
	}

	wp_send_json_success( array( 'items' => $items ) );
}

/* ─── Product compare ──────────────────────────────────────────────────── */

/**
 * Return a structured comparison payload for up to four products.
 *
 * @return void
 */
function rasta_ajax_product_compare() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );

	$products = array();
	$payloads = array();

	foreach ( rasta_ajax_product_ids( 4 ) as $product_id ) {
		$payload = rasta_ajax_visible_product( $product_id );
		if ( ! empty( $payload ) ) {
			$products[] = $payload;
			$payloads[ $payload['id'] ] = $payload;
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

	foreach ( $products as $p ) {
		$pid = $p['id'];
		$rows['price']['values'][ $pid ]          = ! empty( $p['price'] ) ? $p['price'] : '—';
		$rows['availability']['values'][ $pid ]   = $p['stock'];
		$rows['sku']['values'][ $pid ]            = ! empty( $p['sku'] ) ? $p['sku'] : '—';

		if ( ! empty( $p['attributes'] ) ) {
			foreach ( $p['attributes'] as $key => $attribute ) {
				if ( ! isset( $rows[ $key ] ) ) {
					$rows[ $key ] = array(
						'label'  => $attribute['label'],
						'values' => array(),
					);
				}
				$rows[ $key ]['values'][ $pid ] = $attribute['value'];
			}
		}
	}

	$comparison_rows = array();
	foreach ( $rows as $row ) {
		$values = array();
		foreach ( $products as $p ) {
			$values[] = isset( $row['values'][ $p['id'] ] ) ? $row['values'][ $p['id'] ] : '—';
		}
		$comparison_rows[] = array(
			'label'  => $row['label'],
			'values' => $values,
		);
	}

	wp_send_json_success(
		array(
			'items' => $products,
			'rows'  => $comparison_rows,
		)
	);
}

/* ─── Cart AJAX handlers ───────────────────────────────────────────────── */

/**
 * Add a product to the built-in cart via AJAX.
 *
 * @return void
 */
function rasta_ajax_add_to_cart() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 1;

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => esc_html__( 'محصول نامعتبر است.', 'rasta-commerce' ) ), 400 );
	}

	/* If WooCommerce is active and this is a WC product, delegate. */
	if ( rasta_using_woocommerce() && 'product' === get_post_type( $product_id ) ) {
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$added = WC()->cart->add_to_cart( $product_id, max( 1, $quantity ) );
			if ( $added ) {
				wp_send_json_success(
					array(
						'message'    => esc_html__( 'محصول به سبد خرید اضافه شد.', 'rasta-commerce' ),
						'cartCount'  => WC()->cart->get_cart_contents_count(),
						'cartUrl'    => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : rasta_get_cart_url(),
					)
				);
			}
		}
		wp_send_json_error( array( 'message' => esc_html__( 'امکان افزودن به سبد خرید وجود ندارد.', 'rasta-commerce' ) ), 400 );
		return;
	}

	/* Built-in cart. */
	$result = rasta_add_to_cart( $product_id, max( 1, $quantity ) );

	if ( $result ) {
		wp_send_json_success(
			array(
				'message'    => esc_html__( 'محصول به سبد خرید اضافه شد.', 'rasta-commerce' ),
				'cartCount'  => rasta_get_cart_count(),
				'cartUrl'    => rasta_get_cart_url(),
			)
		);
	}

	wp_send_json_error( array( 'message' => esc_html__( 'امکان افزودن به سبد خرید وجود ندارد.', 'rasta-commerce' ) ), 400 );
}

/**
 * Update cart item quantity via AJAX.
 *
 * @return void
 */
function rasta_ajax_update_cart() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 0;

	rasta_update_cart_quantity( $product_id, $quantity );

	wp_send_json_success(
		array(
			'message'    => esc_html__( 'سبد خرید به‌روزرسانی شد.', 'rasta-commerce' ),
			'cartCount'  => rasta_get_cart_count(),
			'cartItems'  => rasta_get_cart_items(),
			'subtotal'   => rasta_format_currency_plain( rasta_get_cart_subtotal() ),
			'subtotalValue' => (float) rasta_get_cart_subtotal(),
		)
	);
}

/**
 * Remove an item from the cart via AJAX.
 *
 * @return void
 */
function rasta_ajax_remove_cart_item() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;

	rasta_remove_from_cart( $product_id );

	wp_send_json_success(
		array(
			'message'    => esc_html__( 'محصول از سبد خرید حذف شد.', 'rasta-commerce' ),
			'cartCount'  => rasta_get_cart_count(),
			'cartItems'  => rasta_get_cart_items(),
			'subtotal'   => rasta_format_currency_plain( rasta_get_cart_subtotal() ),
			'subtotalValue' => (float) rasta_get_cart_subtotal(),
			'isEmpty'    => rasta_cart_is_empty(),
		)
	);
}

/**
 * Return current cart count for fragment updates.
 *
 * @return void
 */
function rasta_ajax_get_cart_count() {
	check_ajax_referer( 'rasta_product_tools', 'nonce' );

	wp_send_json_success(
		array(
			'cartCount' => rasta_get_cart_count(),
			'cartItems' => rasta_get_cart_items(),
			'subtotal'  => rasta_format_currency_plain( rasta_get_cart_subtotal() ),
			'subtotalValue' => (float) rasta_get_cart_subtotal(),
		)
	);
}
