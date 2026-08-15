<?php
/**
 * Built-in cart system using PHP sessions and WordPress transients.
 *
 * Operates independently of WooCommerce, providing a complete shopping cart
 * with add, remove, update quantity, clear, and count operations.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Start a PHP session if not already active (safe for non-CLI context).
 *
 * @return void
 */
function rasta_start_session() {
	/* No sessions for CLI, cron, or REST API requests. */
	if ( ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	/* Session already running. */
	if ( function_exists( 'session_status' ) && PHP_SESSION_ACTIVE === session_status() ) {
		return;
	}

	/* Sessions disabled on this server. */
	if ( function_exists( 'session_status' ) && PHP_SESSION_DISABLED === session_status() ) {
		return;
	}

	/*
	 * Start on both frontend and admin-ajax requests. The cart AJAX handlers
	 * run through admin-ajax.php, so skipping `is_admin()` entirely would
	 * leave $_SESSION unhydrated and lose the cart on every AJAX round-trip.
	 */
	if ( ! headers_sent() ) {
		@session_start();
	}
}
add_action( 'init', 'rasta_start_session', 1 );

/**
 * Get the unique session/cart key for the current visitor.
 *
 * @return string
 */
function rasta_get_cart_session_key() {
	if ( isset( $_SESSION['rasta_cart_key'] ) ) {
		return (string) $_SESSION['rasta_cart_key'];
	}

	$key = wp_generate_uuid4();

	if ( ! headers_sent() ) {
		$_SESSION['rasta_cart_key'] = $key;
	}

	return $key;
}

/**
 * Return the current cart contents as an array.
 *
 * Each item is keyed by product ID and contains:
 *   - product_id  (int)
 *   - quantity    (int)
 *
 * @return array<string, array{product_id: int, quantity: int}>
 */
function rasta_get_cart() {
	if ( ! isset( $_SESSION['rasta_cart'] ) || ! is_array( $_SESSION['rasta_cart'] ) ) {
		$_SESSION['rasta_cart'] = array();
	}

	return $_SESSION['rasta_cart'];
}

/**
 * Persist the cart array back to the session.
 *
 * @param array $cart Cart array.
 * @return void
 */
function rasta_save_cart( $cart ) {
	$_SESSION['rasta_cart'] = $cart;
}

/**
 * Add a product to the cart or increase its quantity.
 *
 * @param int $product_id Built-in product post ID.
 * @param int $quantity   Quantity to add (default 1).
 * @return bool Whether the item was successfully added.
 */
function rasta_add_to_cart( $product_id, $quantity = 1 ) {
	$product_id = absint( $product_id );
	$quantity   = max( 1, absint( $quantity ) );

	$post = get_post( $product_id );
	if ( ! $post || 'rasta_product' !== $post->post_type || 'publish' !== $post->post_status ) {
		return false;
	}

	if ( ! rasta_product_is_in_stock( $product_id ) ) {
		return false;
	}

	/* Respect stock limits when stock management is enabled. */
	$manage  = (bool) get_post_meta( $product_id, '_rasta_manage_stock', true );
	$avail   = (int) get_post_meta( $product_id, '_rasta_stock_quantity', true );

	$cart     = rasta_get_cart();
	$key      = (string) $product_id;
	$existing = isset( $cart[ $key ] ) ? (int) $cart[ $key ]['quantity'] : 0;
	$new_qty  = $existing + $quantity;

	if ( $manage && $avail > 0 && $new_qty > $avail ) {
		return false;
	}

	$cart[ $key ] = array(
		'product_id' => $product_id,
		'quantity'   => $new_qty,
	);

	rasta_save_cart( $cart );

	/* Bump popularity counter for sorting. */
	$popularity = (int) get_post_meta( $product_id, '_rasta_popularity', true );
	update_post_meta( $product_id, '_rasta_popularity', $popularity + $quantity );

	do_action( 'rasta_add_to_cart', $product_id, $quantity );

	return true;
}

/**
 * Remove a product from the cart entirely.
 *
 * @param int $product_id Product post ID.
 * @return bool Whether the item was found and removed.
 */
function rasta_remove_from_cart( $product_id ) {
	$cart = rasta_get_cart();
	$key  = (string) absint( $product_id );

	if ( ! isset( $cart[ $key ] ) ) {
		return false;
	}

	unset( $cart[ $key ] );
	rasta_save_cart( $cart );

	do_action( 'rasta_remove_from_cart', absint( $product_id ) );

	return true;
}

/**
 * Update the quantity of a product in the cart.
 *
 * @param int $product_id Product post ID.
 * @param int $quantity   New quantity (0 removes the item).
 * @return bool
 */
function rasta_update_cart_quantity( $product_id, $quantity ) {
	$product_id = absint( $product_id );
	$quantity   = absint( $quantity );
	$cart       = rasta_get_cart();
	$key        = (string) $product_id;

	if ( $quantity <= 0 ) {
		unset( $cart[ $key ] );
	} else {
		$manage = (bool) get_post_meta( $product_id, '_rasta_manage_stock', true );
		$avail  = (int) get_post_meta( $product_id, '_rasta_stock_quantity', true );

		if ( $manage && $avail > 0 && $quantity > $avail ) {
			$quantity = $avail;
		}

		$cart[ $key ] = array(
			'product_id' => $product_id,
			'quantity'   => $quantity,
		);
	}

	rasta_save_cart( $cart );

	return true;
}

/**
 * Empty the entire cart.
 *
 * @return void
 */
function rasta_empty_cart() {
	$_SESSION['rasta_cart'] = array();
	do_action( 'rasta_cart_emptied' );
}

/**
 * Return the total number of items in the cart (both backends).
 *
 * @return int
 */
function rasta_get_cart_count() {
	/* When WooCommerce is active, delegate to it. */
	if ( class_exists( 'WooCommerce' ) && function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}

	$cart  = rasta_get_cart();
	$count = 0;

	foreach ( $cart as $item ) {
		$count += (int) $item['quantity'];
	}

	return $count;
}

/**
 * Return the cart subtotal (sum of line totals before any adjustments).
 *
 * @return float
 */
function rasta_get_cart_subtotal() {
	/* When WooCommerce is active, delegate to it. */
	if ( class_exists( 'WooCommerce' ) && function_exists( 'WC' ) && WC()->cart ) {
		return (float) WC()->cart->get_cart_contents_total();
	}

	$cart    = rasta_get_cart();
	$subtotal = 0.0;

	foreach ( $cart as $item ) {
		$price    = rasta_get_product_active_price( $item['product_id'] );
		$subtotal += $price * (int) $item['quantity'];
	}

	return $subtotal;
}

/**
 * Return enriched cart data with product payloads.
 *
 * @return array
 */
function rasta_get_cart_items() {
	$cart  = rasta_get_cart();
	$items = array();

	foreach ( $cart as $key => $entry ) {
		$payload = rasta_get_product_payload( $entry['product_id'] );
		if ( empty( $payload ) ) {
			continue;
		}

		$items[] = array(
			'key'        => $key,
			'quantity'   => (int) $entry['quantity'],
			'lineTotal'  => rasta_get_product_active_price( $entry['product_id'] ) * (int) $entry['quantity'],
			'product'    => $payload,
		);
	}

	return $items;
}

/**
 * Return whether the cart has any items.
 *
 * @return bool
 */
function rasta_cart_is_empty() {
	return rasta_get_cart_count() <= 0;
}

/* ─── Order management ─────────────────────────────────────────────────── */

/**
 * Create a new order from the current cart and customer data.
 *
 * @param array $customer_data Customer information (name, email, phone, address).
 * @return int|WP_Error Order post ID or error.
 */
function rasta_create_order( $customer_data = array() ) {
	if ( rasta_cart_is_empty() ) {
		return new \WP_Error( 'empty_cart', __( 'سبد خرید خالی است.', 'rasta-commerce' ) );
	}

	$cart_items = rasta_get_cart_items();
	$subtotal   = rasta_get_cart_subtotal();

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'rasta_order',
			'post_title'  => sprintf(
				/* translators: %s: order number placeholder. */
				__( 'سفارش #%s', 'rasta-commerce' ),
				'[pending]'
			),
			'post_status' => 'rasta-pending',
			'post_author' => get_current_user_id() ?: 0,
		)
	);

	if ( ! $order_id || is_wp_error( $order_id ) ) {
		return new \WP_Error( 'order_creation_failed', __( 'خطا در ایجاد سفارش.', 'rasta-commerce' ) );
	}

	/* Update title with order ID. */
	wp_update_post(
		array(
			'ID'         => $order_id,
			'post_title' => sprintf(
				/* translators: %s: order number. */
				__( 'سفارش شماره %s', 'rasta-commerce' ),
				rasta_to_persian_digits( number_format_i18n( $order_id ) )
			),
		)
	);

	/* Save customer data. */
	$fields = array(
		'first_name' => 'sanitize_text_field',
		'last_name'  => 'sanitize_text_field',
		'email'      => 'sanitize_email',
		'phone'      => 'sanitize_text_field',
		'address'    => 'sanitize_textarea_field',
		'city'       => 'sanitize_text_field',
		'province'   => 'sanitize_text_field',
		'postcode'   => 'sanitize_text_field',
		'notes'      => 'sanitize_textarea_field',
	);

	foreach ( $fields as $field => $sanitize_fn ) {
		$value = isset( $customer_data[ $field ] ) ? $sanitize_fn( $customer_data[ $field ] ) : '';
		update_post_meta( $order_id, '_rasta_customer_' . $field, $value );
	}

	/* Save cart snapshot. */
	update_post_meta( $order_id, '_rasta_order_items', $cart_items );
	update_post_meta( $order_id, '_rasta_order_subtotal', $subtotal );
	update_post_meta( $order_id, '_rasta_order_total', $subtotal );
	update_post_meta( $order_id, '_rasta_order_currency', get_theme_mod( 'rasta_currency', 'IRT' ) );

	/* Reduce stock. */
	foreach ( $cart_items as $item ) {
		$pid    = $item['product']['id'];
		$manage = (bool) get_post_meta( $pid, '_rasta_manage_stock', true );
		if ( $manage ) {
			$current = (int) get_post_meta( $pid, '_rasta_stock_quantity', true );
			$new     = max( 0, $current - (int) $item['quantity'] );
			update_post_meta( $pid, '_rasta_stock_quantity', $new );
			if ( $new <= 0 ) {
				update_post_meta( $pid, '_rasta_stock_status', 'outofstock' );
			}
		}
	}

	/* Clear cart. */
	rasta_empty_cart();

	do_action( 'rasta_order_created', $order_id );

	return $order_id;
}

/**
 * Register the rasta_order custom post type for orders.
 *
 * @return void
 */
function rasta_register_order_cpt() {
	register_post_type(
		'rasta_order',
		array(
			'labels'         => array(
				'name'          => __( 'سفارش‌ها', 'rasta-commerce' ),
				'singular_name' => __( 'سفارش', 'rasta-commerce' ),
			),
			'public'         => false,
			'show_ui'        => true,
			'show_in_menu'   => 'edit.php?post_type=rasta_product',
			'supports'       => array( 'title' ),
			'capabilities'   => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'   => true,
		)
	);

	register_post_status(
		'rasta-pending',
		array(
			'label'                     => __( 'در انتظار پرداخت', 'rasta-commerce' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
		)
	);

	register_post_status(
		'rasta-processing',
		array(
			'label'                     => __( 'در حال پردازش', 'rasta-commerce' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
		)
	);

	register_post_status(
		'rasta-completed',
		array(
			'label'                     => __( 'تکمیل شده', 'rasta-commerce' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
		)
	);

	register_post_status(
		'rasta-cancelled',
		array(
			'label'                     => __( 'لغو شده', 'rasta-commerce' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
		)
	);

	register_post_status(
		'rasta-failed',
		array(
			'label'                     => __( 'ناموفق', 'rasta-commerce' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
		)
	);
}
add_action( 'init', 'rasta_register_order_cpt' );

/**
 * Update order status.
 *
 * @param int    $order_id Order post ID.
 * @param string $status   New status slug.
 * @return bool
 */
function rasta_update_order_status( $order_id, $status ) {
	$valid = array( 'rasta-pending', 'rasta-processing', 'rasta-completed', 'rasta-cancelled', 'rasta-failed' );
	if ( ! in_array( $status, $valid, true ) ) {
		return false;
	}

	$result = wp_update_post(
		array(
			'ID'          => $order_id,
			'post_status' => $status,
		)
	);

	return ! is_wp_error( $result );
}

/* ─── Mini-cart rendering ──────────────────────────────────────────────── */

/**
 * Render mini-cart content for the drawer (works with both built-in and WC).
 *
 * @return void
 */
function rasta_render_mini_cart_content() {
	if ( rasta_using_woocommerce() && function_exists( 'woocommerce_mini_cart' ) ) {
		woocommerce_mini_cart();
		rasta_render_free_shipping_progress();
		return;
	}

	/* Built-in mini-cart. */
	$items = rasta_get_cart_items();
	?>
	<div data-mini-cart-body>
		<?php if ( empty( $items ) ) : ?>
			<div class="rasta-mini-cart__empty">
				<?php rasta_icon( 'cart' ); ?>
				<p><?php esc_html_e( 'سبد خرید شما خالی است.', 'rasta-commerce' ); ?></p>
				<a class="rasta-button" href="<?php echo esc_url( rasta_get_shop_url() ); ?>">
					<?php esc_html_e( 'مشاهده فروشگاه', 'rasta-commerce' ); ?>
				</a>
			</div>
		<?php else : ?>
			<ul class="rasta-mini-cart__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="rasta-mini-cart__item" data-cart-item="<?php echo esc_attr( $item['product']['id'] ); ?>">
						<a class="rasta-mini-cart__image" href="<?php echo esc_url( $item['product']['url'] ); ?>">
							<?php if ( ! empty( $item['product']['image'] ) ) : ?>
								<img src="<?php echo esc_url( $item['product']['image'] ); ?>" alt="<?php echo esc_attr( $item['product']['name'] ); ?>" />
							<?php endif; ?>
						</a>
						<div class="rasta-mini-cart__details">
							<a href="<?php echo esc_url( $item['product']['url'] ); ?>"><?php echo esc_html( $item['product']['name'] ); ?></a>
							<span class="rasta-mini-cart__qty">
								<?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $item['quantity'] ) ) ); ?> ×
								<?php echo wp_kses_post( $item['product']['price'] ); ?>
							</span>
						</div>
						<button class="rasta-mini-cart__remove" type="button" data-remove-cart-item="<?php echo esc_attr( $item['product']['id'] ); ?>" aria-label="<?php esc_attr_e( 'حذف', 'rasta-commerce' ); ?>">
							<?php rasta_icon( 'trash' ); ?>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="rasta-mini-cart__footer">
				<div class="rasta-mini-cart__subtotal">
					<span><?php esc_html_e( 'جمع کل:', 'rasta-commerce' ); ?></span>
					<span data-mini-cart-subtotal><?php echo wp_kses_post( rasta_format_currency( rasta_get_cart_subtotal() ) ); ?></span>
				</div>
				<?php rasta_render_free_shipping_progress(); ?>
				<div class="rasta-mini-cart__actions">
					<a class="rasta-button rasta-button--outline" href="<?php echo esc_url( rasta_get_cart_url() ); ?>">
						<?php esc_html_e( 'مشاهده سبد خرید', 'rasta-commerce' ); ?>
					</a>
					<a class="rasta-button" href="<?php echo esc_url( rasta_get_checkout_url() ); ?>">
						<?php esc_html_e( 'تکمیل خرید', 'rasta-commerce' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render free-shipping progress bar.
 *
 * @return void
 */
function rasta_render_free_shipping_progress() {
	$threshold = (float) get_theme_mod( 'rasta_free_shipping_threshold', 0 );

	if ( $threshold <= 0 ) {
		return;
	}

	$current    = rasta_get_cart_subtotal();
	$remaining  = max( 0, $threshold - $current );
	$percentage = min( 100, max( 0, (int) round( ( $current / $threshold ) * 100 ) ) );
	?>
	<div class="rasta-shipping-progress" role="status">
		<?php if ( $remaining > 0 ) : ?>
			<p>
				<?php
				printf(
					/* translators: %s: remaining cart value. */
					esc_html__( 'فقط %s تا ارسال رایگان مانده است.', 'rasta-commerce' ),
					wp_kses_post( rasta_format_currency( $remaining ) )
				);
				?>
			</p>
		<?php else : ?>
			<p><?php esc_html_e( 'ارسال رایگان برای این سفارش فعال شد.', 'rasta-commerce' ); ?></p>
		<?php endif; ?>
		<span class="rasta-shipping-progress__track" aria-hidden="true"><span style="inline-size: <?php echo esc_attr( $percentage ); ?>%"></span></span>
	</div>
	<?php
}
