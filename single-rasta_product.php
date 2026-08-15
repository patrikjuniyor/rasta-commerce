<?php
/**
 * Single product template for built-in products.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) : the_post();
	$payload    = rasta_get_product_payload( get_the_ID() );
	$gallery    = rasta_get_product_gallery_ids( get_the_ID() );
	$main_image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	$sku        = rasta_get_product_sku( get_the_ID() );
	$cats       = get_the_terms( get_the_ID(), 'rasta_product_cat' );
	$tags       = get_the_terms( get_the_ID(), 'rasta_product_tag' );
	?>
	<main id="content" class="site-main rasta-single-product">
		<div class="rasta-container">

			<div class="rasta-shop-breadcrumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'rasta-commerce' ); ?>">
				<nav class="woocommerce-breadcrumb">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'rasta-commerce' ); ?></a>
					<span class="rasta-breadcrumb-separator" aria-hidden="true">/</span>
					<a href="<?php echo esc_url( rasta_get_shop_url() ); ?>"><?php esc_html_e( 'فروشگاه', 'rasta-commerce' ); ?></a>
					<?php if ( ! is_wp_error( $cats ) && $cats ) : ?>
						<?php $first_cat = $cats[0]; ?>
						<span class="rasta-breadcrumb-separator" aria-hidden="true">/</span>
						<a href="<?php echo esc_url( get_term_link( $first_cat ) ); ?>"><?php echo esc_html( $first_cat->name ); ?></a>
					<?php endif; ?>
					<span class="rasta-breadcrumb-separator" aria-hidden="true">/</span>
					<span><?php the_title(); ?></span>
				</nav>
			</div>

			<div class="rasta-product-single__grid">
				<div class="rasta-product-single__gallery">
					<?php if ( $main_image ) : ?>
						<div class="rasta-product-single__main-image">
							<img src="<?php echo esc_url( $main_image ); ?>" alt="<?php the_title_attribute(); ?>" />
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $gallery ) ) : ?>
						<div class="rasta-product-single__thumbnails">
							<?php foreach ( $gallery as $att_id ) : ?>
								<?php $thumb = wp_get_attachment_image_url( $att_id, 'thumbnail' ); ?>
								<?php if ( $thumb ) : ?>
									<button type="button" class="rasta-product-single__thumb" data-full-image="<?php echo esc_url( wp_get_attachment_image_url( $att_id, 'large' ) ); ?>">
										<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
									</button>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="rasta-product-single__info">
					<h1 class="rasta-product-single__title"><?php the_title(); ?></h1>

					<?php if ( $sku ) : ?>
						<p class="rasta-product-single__sku">
							<?php esc_html_e( 'کد محصول:', 'rasta-commerce' ); ?>
							<span><?php echo esc_html( $sku ); ?></span>
						</p>
					<?php endif; ?>

					<div class="rasta-product-single__price">
						<?php
						if ( ! empty( $payload['isOnSale'] ) && isset( $payload['salePrice'] ) && $payload['salePrice'] > 0 ) {
							echo wp_kses_post(
								'<del aria-hidden="true">' . rasta_format_currency( $payload['regularPrice'] ) . '</del> <ins>' . rasta_format_currency( $payload['salePrice'] ) . '</ins>'
							);
						} else {
							echo wp_kses_post( rasta_format_currency( $payload['priceValue'] ) );
						}
						?>
					</div>

					<?php if ( ! empty( $payload['isOnSale'] ) && rasta_feature_enabled( 'sale_countdown' ) && $payload['saleEnd'] > time() ) : ?>
						<span class="rasta-sale-countdown" data-sale-countdown data-sale-ends="<?php echo esc_attr( $payload['saleEnd'] ); ?>">
							<?php rasta_icon( 'clock' ); ?>
							<span data-sale-countdown-value><?php esc_html_e( 'در حال محاسبه…', 'rasta-commerce' ); ?></span>
						</span>
					<?php endif; ?>

					<div class="rasta-product-single__stock">
						<?php echo esc_html( $payload['stock'] ); ?>
					</div>

					<?php if ( ! empty( $payload['inStock'] ) ) : ?>
						<div class="rasta-product-single__add-to-cart">
							<div class="rasta-quantity-selector">
								<button type="button" class="rasta-qty-btn" data-qty-decrease aria-label="<?php esc_attr_e( 'کاهش تعداد', 'rasta-commerce' ); ?>">−</button>
								<input type="number" class="rasta-qty-input" value="1" min="1" data-qty-input aria-label="<?php esc_attr_e( 'تعداد', 'rasta-commerce' ); ?>" />
								<button type="button" class="rasta-qty-btn" data-qty-increase aria-label="<?php esc_attr_e( 'افزایش تعداد', 'rasta-commerce' ); ?>">+</button>
							</div>
							<button class="rasta-button rasta-button--large" type="button" data-add-to-cart data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
								<?php rasta_icon( 'cart' ); ?>
								<?php esc_html_e( 'افزودن به سبد خرید', 'rasta-commerce' ); ?>
							</button>
						</div>
					<?php else : ?>
						<p class="rasta-product-single__out-of-stock"><?php esc_html_e( 'این محصول در حال حاضر ناموجود است.', 'rasta-commerce' ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $payload['description'] ) ) : ?>
						<div class="rasta-product-single__short-description">
							<p><?php echo esc_html( $payload['description'] ); ?></p>
						</div>
					<?php endif; ?>

					<div class="rasta-product-single__meta">
						<button class="rasta-wishlist-button rasta-text-link" type="button" data-wishlist-product="<?php echo esc_attr( get_the_ID() ); ?>" aria-pressed="false">
							<?php rasta_icon( 'heart' ); ?>
							<?php esc_html_e( 'افزودن به علاقه‌مندی‌ها', 'rasta-commerce' ); ?>
						</button>
						<?php if ( rasta_feature_enabled( 'compare' ) ) : ?>
							<button class="rasta-compare-button rasta-text-link" type="button" data-compare-product="<?php echo esc_attr( get_the_ID() ); ?>" aria-pressed="false">
								<?php rasta_icon( 'compare' ); ?>
								<?php esc_html_e( 'مقایسه', 'rasta-commerce' ); ?>
							</button>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $payload['attributes'] ) ) : ?>
						<div class="rasta-product-single__attributes">
							<h3><?php esc_html_e( 'مشخصات', 'rasta-commerce' ); ?></h3>
							<dl>
								<?php foreach ( $payload['attributes'] as $attr ) : ?>
									<dt><?php echo esc_html( $attr['label'] ); ?></dt>
									<dd><?php echo esc_html( $attr['value'] ); ?></dd>
								<?php endforeach; ?>
							</dl>
						</div>
					<?php endif; ?>

					<?php if ( ! is_wp_error( $cats ) && $cats ) : ?>
						<div class="rasta-product-single__categories">
							<strong><?php esc_html_e( 'دسته‌بندی:', 'rasta-commerce' ); ?></strong>
							<?php foreach ( $cats as $cat ) : ?>
								<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( get_the_content() ) : ?>
				<section class="rasta-product-single__description rasta-section">
					<h2><?php esc_html_e( 'توضیحات محصول', 'rasta-commerce' ); ?></h2>
					<div class="rasta-content"><?php the_content(); ?></div>
				</section>
			<?php endif; ?>

			<?php if ( rasta_feature_enabled( 'recently_viewed' ) ) : ?>
				<section class="rasta-recently-viewed" data-recently-viewed-section data-rasta-product-view="<?php echo esc_attr( get_the_ID() ); ?>" hidden>
					<div class="rasta-section-heading rasta-section-heading--compact">
						<div>
							<p class="rasta-kicker"><?php esc_html_e( 'برای ادامه‌ی انتخاب', 'rasta-commerce' ); ?></p>
							<h2><?php esc_html_e( 'اخیراً دیده‌اید', 'rasta-commerce' ); ?></h2>
						</div>
					</div>
					<div class="rasta-recently-viewed__grid" data-recently-viewed-products></div>
				</section>
			<?php endif; ?>

		</div>
	</main>
<?php endwhile; ?>

<?php if ( rasta_feature_enabled( 'sticky_cart' ) ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php $payload = rasta_get_product_payload( get_the_ID() ); ?>
		<?php if ( ! empty( $payload['inStock'] ) ) : ?>
			<section class="rasta-sticky-cart" data-sticky-cart aria-hidden="true" aria-label="<?php esc_attr_e( 'افزودن سریع به سبد خرید', 'rasta-commerce' ); ?>">
				<div class="rasta-container rasta-sticky-cart__inner">
					<div class="rasta-sticky-cart__product">
						<?php $thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ); ?>
						<?php if ( $thumb ) : ?>
							<div class="rasta-sticky-cart__image"><img src="<?php echo esc_url( $thumb ); ?>" alt="" /></div>
						<?php endif; ?>
						<div>
							<strong><?php the_title(); ?></strong>
							<span><?php echo wp_kses_post( $payload['price'] ); ?></span>
						</div>
					</div>
					<button class="rasta-button rasta-sticky-cart__button" type="button" data-add-to-cart data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
						<?php rasta_icon( 'cart' ); ?>
						<?php esc_html_e( 'افزودن به سبد', 'rasta-commerce' ); ?>
					</button>
				</div>
			</section>
		<?php endif; ?>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
<?php endif; ?>

<?php get_footer(); ?>
