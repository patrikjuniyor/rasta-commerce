<?php
/**
 * WooCommerce compatibility layer.
 *
 * This file is only loaded when WooCommerce is active. It provides
 * enhanced hooks and wrappers that improve the WC experience but are
 * not required — the theme's built-in store works without this file.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* This file should only run when WooCommerce is loaded. */
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Configure WooCommerce wrappers and product loop callbacks.
 *
 * @return void
 */
function rasta_configure_woocommerce() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	add_action( 'woocommerce_before_main_content', 'rasta_woocommerce_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'rasta_woocommerce_wrapper_end', 10 );

	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

	add_action( 'woocommerce_before_shop_loop_item_title', 'rasta_loop_product_visual', 10 );
	add_action( 'woocommerce_shop_loop_item_title', 'rasta_loop_product_title', 10 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'rasta_loop_product_rating', 5 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'rasta_loop_product_price', 10 );
	add_action( 'woocommerce_after_shop_loop_item', 'rasta_loop_product_add_to_cart', 10 );

	if ( rasta_feature_enabled( 'recently_viewed' ) ) {
		add_action( 'woocommerce_after_single_product_summary', 'rasta_recently_viewed_placeholder', 25 );
	}
}
add_action( 'wp', 'rasta_configure_woocommerce', 20 );

/**
 * Start the theme's WooCommerce content wrapper.
 *
 * @return void
 */
function rasta_woocommerce_wrapper_start() {
	?>
	<main id="content" class="site-main rasta-shop-shell">
		<div class="rasta-container">
			<?php if ( ! is_cart() && ! is_checkout() ) : ?>
				<div class="rasta-shop-breadcrumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'rasta-commerce' ); ?>">
					<?php woocommerce_breadcrumb(); ?>
				</div>
			<?php endif; ?>
	<?php
}

/**
 * End the theme's WooCommerce content wrapper.
 *
 * @return void
 */
function rasta_woocommerce_wrapper_end() {
	?>
		</div>
	</main>
	<?php
}

/**
 * Output the product image and sale state in a WC card.
 *
 * @return void
 */
function rasta_loop_product_visual() {
	global $product;

	if ( ! $product instanceof \WC_Product ) {
		return;
	}

	$image              = $product->get_image(
		'woocommerce_thumbnail',
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
	$sale_end_timestamp = rasta_product_sale_end_timestamp( $product );
	$low_stock_quantity = rasta_product_low_stock_quantity( $product );
	?>
	<div class="rasta-product-card__badges">
		<?php if ( $product->is_on_sale() ) : ?>
			<span class="rasta-product-card__badge"><?php esc_html_e( 'پیشنهاد ویژه', 'rasta-commerce' ); ?></span>
		<?php endif; ?>
		<?php if ( rasta_product_is_new( $product ) ) : ?>
			<span class="rasta-product-card__badge rasta-product-card__badge--new"><?php esc_html_e( 'تازه', 'rasta-commerce' ); ?></span>
		<?php endif; ?>
	</div>
	<?php echo wp_kses_post( $image ); ?>
	<?php if ( rasta_feature_enabled( 'sale_countdown' ) && $product->is_on_sale() && $sale_end_timestamp > time() ) : ?>
		<span class="rasta-sale-countdown" data-sale-countdown data-sale-ends="<?php echo esc_attr( $sale_end_timestamp ); ?>">
			<?php rasta_icon( 'clock' ); ?>
			<span data-sale-countdown-value><?php esc_html_e( 'در حال محاسبه…', 'rasta-commerce' ); ?></span>
		</span>
	<?php endif; ?>
	<?php if ( ! $product->is_in_stock() ) : ?>
		<span class="rasta-product-card__stock rasta-product-card__stock--out"><?php esc_html_e( 'ناموجود', 'rasta-commerce' ); ?></span>
	<?php elseif ( false !== $low_stock_quantity ) : ?>
		<span class="rasta-product-card__stock rasta-product-card__stock--low">
			<?php
			printf(
				/* translators: %s: quantity remaining. */
				esc_html__( 'فقط %s عدد باقی مانده', 'rasta-commerce' ),
				esc_html( number_format_i18n( $low_stock_quantity ) )
			);
			?>
		</span>
	<?php endif; ?>
	<?php
}

/**
 * Output a linked product title (WC loop).
 *
 * @return void
 */
function rasta_loop_product_title() {
	global $product;

	if ( ! $product instanceof \WC_Product ) {
		return;
	}
	?>
	<h3 class="woocommerce-loop-product__title rasta-product-card__title">
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
	</h3>
	<?php
}

/**
 * Output the WooCommerce rating only when reviews are enabled.
 *
 * @return void
 */
function rasta_loop_product_rating() {
	if ( wc_review_ratings_enabled() ) {
		woocommerce_template_loop_rating();
	}
}

/**
 * Output the product price inside a stable semantic wrapper.
 *
 * @return void
 */
function rasta_loop_product_price() {
	?>
	<div class="rasta-product-card__price">
		<?php woocommerce_template_loop_price(); ?>
	</div>
	<?php
}

/**
 * Output an AJAX-ready add-to-cart button.
 *
 * @return void
 */
function rasta_loop_product_add_to_cart() {
	global $product;

	if ( ! $product instanceof \WC_Product ) {
		return;
	}

	$args = array(
		'class'      => 'button rasta-add-to-cart',
		'aria-label' => sprintf(
			/* translators: %s: product name. */
			esc_attr__( 'افزودن «%s» به سبد خرید', 'rasta-commerce' ),
			$product->get_name()
		),
	);
	?>
	<div class="rasta-product-card__actions">
		<?php woocommerce_template_loop_add_to_cart( $args ); ?>
	</div>
	<?php
}

/**
 * Determine whether a WC product should receive the "new" badge.
 *
 * @param \WC_Product $product Product instance.
 * @return bool
 */
function rasta_product_is_new( $product ) {
	$days    = (int) get_theme_mod( 'rasta_newness_days', 30 );
	$created = $product->get_date_created();

	return $days > 0 && $created && $created->getTimestamp() >= ( time() - ( DAY_IN_SECONDS * $days ) );
}

/**
 * Return a scheduled sale end timestamp for WC products.
 *
 * @param \WC_Product $product Product instance.
 * @return int
 */
function rasta_product_sale_end_timestamp( $product ) {
	$sale_end = $product->get_date_on_sale_to();
	return $sale_end ? (int) $sale_end->getTimestamp() : 0;
}

/**
 * Return remaining stock that warrants a warning (WC).
 *
 * @param \WC_Product $product Product instance.
 * @return int|false
 */
function rasta_product_low_stock_quantity( $product ) {
	$threshold = (int) get_theme_mod( 'rasta_low_stock_threshold', 3 );
	$quantity  = $product->get_stock_quantity();

	if ( $threshold <= 0 || ! $product->managing_stock() || ! $product->is_in_stock() || null === $quantity ) {
		return false;
	}

	return $quantity <= $threshold ? (int) $quantity : false;
}

/**
 * Define card grid columns in WooCommerce archives.
 *
 * @return int
 */
function rasta_loop_shop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'rasta_loop_shop_columns' );

/**
 * Use a compact related-products section.
 *
 * @param array<string, mixed> $args Existing related product query arguments.
 * @return array<string, mixed>
 */
function rasta_related_products_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'rasta_related_products_args' );

/**
 * Use a RTL-aware delimiter for WooCommerce breadcrumbs.
 *
 * @param array<string, mixed> $defaults Breadcrumb defaults.
 * @return array<string, mixed>
 */
function rasta_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter']   = '<span class="rasta-breadcrumb-separator" aria-hidden="true">/</span>';
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'مسیر صفحه', 'rasta-commerce' ) . '">';
	$defaults['wrap_after']  = '</nav>';
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'rasta_breadcrumb_defaults' );

/**
 * Refresh only the cart count when WooCommerce adds an item through AJAX.
 *
 * @param array<string, string> $fragments Existing fragments.
 * @return array<string, string>
 */
function rasta_cart_count_fragment( $fragments ) {
	ob_start();
	rasta_cart_count_markup();
	$fragments['.rasta-cart-count'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'rasta_cart_count_fragment' );

/**
 * Output an initially empty recently-viewed product rail on product pages.
 *
 * @return void
 */
function rasta_recently_viewed_placeholder() {
	global $product;

	if ( ! $product instanceof \WC_Product ) {
		return;
	}
	?>
	<section class="rasta-recently-viewed" data-recently-viewed-section data-rasta-product-view="<?php echo esc_attr( $product->get_id() ); ?>" hidden>
		<div class="rasta-section-heading rasta-section-heading--compact">
			<div>
				<p class="rasta-kicker"><?php esc_html_e( 'برای ادامه‌ی انتخاب', 'rasta-commerce' ); ?></p>
				<h2><?php esc_html_e( 'اخیراً دیده‌اید', 'rasta-commerce' ); ?></h2>
			</div>
		</div>
		<div class="rasta-recently-viewed__grid" data-recently-viewed-products></div>
	</section>
	<?php
}

/**
 * Output a mobile-friendly sticky purchase bar on single WC product pages.
 *
 * @return void
 */
function rasta_render_sticky_add_to_cart() {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! rasta_feature_enabled( 'sticky_cart' ) ) {
		return;
	}

	$product = isset( $GLOBALS['product'] ) && $GLOBALS['product'] instanceof \WC_Product ? $GLOBALS['product'] : wc_get_product( get_queried_object_id() );

	if ( ! $product instanceof \WC_Product || ! $product->is_purchasable() ) {
		return;
	}

	$can_ajax_add = $product->is_type( 'simple' ) && $product->is_in_stock();
	$image        = $product->get_image(
		'woocommerce_thumbnail',
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
	?>
	<section class="rasta-sticky-cart" data-sticky-cart aria-hidden="true" aria-label="<?php esc_attr_e( 'افزودن سریع به سبد خرید', 'rasta-commerce' ); ?>">
		<div class="rasta-container rasta-sticky-cart__inner">
			<div class="rasta-sticky-cart__product">
				<div class="rasta-sticky-cart__image"><?php echo wp_kses_post( $image ); ?></div>
				<div>
					<strong><?php echo esc_html( $product->get_name() ); ?></strong>
					<span><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				</div>
			</div>
			<?php if ( $can_ajax_add ) : ?>
				<a class="button rasta-sticky-cart__button add_to_cart_button ajax_add_to_cart" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-quantity="1">
					<?php rasta_icon( 'cart' ); ?>
					<?php esc_html_e( 'افزودن به سبد', 'rasta-commerce' ); ?>
				</a>
			<?php else : ?>
				<button class="rasta-button rasta-sticky-cart__button" type="button" data-scroll-product-form>
					<?php esc_html_e( 'انتخاب گزینه‌ها', 'rasta-commerce' ); ?>
					<?php rasta_icon( 'arrow-left' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Keep the themed mini cart and optional shipping progress in sync after AJAX cart events.
 *
 * @param array<string, string> $fragments Existing fragments.
 * @return array<string, string>
 */
function rasta_mini_cart_fragment( $fragments ) {
	if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
		return $fragments;
	}

	ob_start();
	rasta_render_mini_cart_content();
	$fragments['[data-mini-cart]'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'rasta_mini_cart_fragment' );
