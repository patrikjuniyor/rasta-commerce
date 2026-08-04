<?php
/**
 * Product card used by WooCommerce loops.
 *
 * This is intentionally small and delegates data/rendering to stable WooCommerce hooks.
 *
 * @package Rasta_Commerce
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}

$categories = wc_get_product_category_list( $product->get_id(), ', ' );
?>
<li <?php wc_product_class( 'rasta-product-card', $product ); ?>>
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
	<article class="rasta-product-card__inner">
		<div class="rasta-product-card__visual">
			<a class="rasta-product-card__image" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
				<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
			</a>
			<div class="rasta-product-card__utility">
				<?php if ( rasta_feature_enabled( 'quick_view' ) ) : ?>
					<button class="rasta-product-card__utility-button" type="button" data-quick-view-product="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'نمایش سریع %s', 'rasta-commerce' ), $product->get_name() ) ); ?>">
						<?php rasta_icon( 'eye' ); ?>
					</button>
				<?php endif; ?>
				<button class="rasta-product-card__utility-button rasta-wishlist-button" type="button" data-wishlist-product="<?php echo esc_attr( $product->get_id() ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( __( 'افزودن %s به علاقه‌مندی‌ها', 'rasta-commerce' ), $product->get_name() ) ); ?>">
					<?php rasta_icon( 'heart' ); ?>
				</button>
				<?php if ( rasta_feature_enabled( 'compare' ) ) : ?>
					<button class="rasta-product-card__utility-button rasta-compare-button" type="button" data-compare-product="<?php echo esc_attr( $product->get_id() ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( __( 'افزودن %s به مقایسه', 'rasta-commerce' ), $product->get_name() ) ); ?>">
						<?php rasta_icon( 'compare' ); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
		<div class="rasta-product-card__body">
			<?php if ( $categories ) : ?>
				<div class="rasta-product-card__category"><?php echo wp_kses_post( $categories ); ?></div>
			<?php endif; ?>
			<?php do_action( 'woocommerce_shop_loop_item_title' ); ?>
			<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
			<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
		</div>
	</article>
</li>
