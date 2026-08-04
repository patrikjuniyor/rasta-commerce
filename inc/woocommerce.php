<?php
/**
 * WooCommerce integration built with hooks instead of fragile full-template copies.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure WooCommerce wrappers and product loop callbacks after WooCommerce loads.
 *
 * @return void
 */
function rasta_configure_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

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
 * Output the product image and sale state in a card.
 *
 * @return void
 */
function rasta_loop_product_visual() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$image = $product->get_image(
		'woocommerce_thumbnail',
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
	?>
	<?php if ( $product->is_on_sale() ) : ?>
		<span class="rasta-product-card__badge"><?php esc_html_e( 'پیشنهاد ویژه', 'rasta-commerce' ); ?></span>
	<?php endif; ?>
	<?php echo wp_kses_post( $image ); ?>
	<?php if ( ! $product->is_in_stock() ) : ?>
		<span class="rasta-product-card__stock rasta-product-card__stock--out"><?php esc_html_e( 'ناموجود', 'rasta-commerce' ); ?></span>
	<?php endif; ?>
	<?php
}

/**
 * Output a linked product title.
 *
 * @return void
 */
function rasta_loop_product_title() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
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
 * Output an AJAX-ready add-to-cart button provided by WooCommerce.
 *
 * @return void
 */
function rasta_loop_product_add_to_cart() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
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
 * Define card grid columns in WooCommerce archives.
 *
 * @return int
 */
function rasta_loop_shop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'rasta_loop_shop_columns' );

/**
 * Use a compact, familiar related-products section.
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
 * Use a simple RTL-aware delimiter for WooCommerce breadcrumbs.
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
