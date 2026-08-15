<?php
/**
 * Archive template for built-in products (shop page).
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_term = get_queried_object();
$is_category  = $current_term instanceof \WP_Term && 'rasta_product_cat' === $current_term->taxonomy;
?>
<main id="content" class="site-main rasta-shop-shell">
	<div class="rasta-container">

		<div class="rasta-shop-breadcrumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'rasta-commerce' ); ?>">
			<nav class="woocommerce-breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'rasta-commerce' ); ?></a>
				<span class="rasta-breadcrumb-separator" aria-hidden="true">/</span>
				<a href="<?php echo esc_url( rasta_get_shop_url() ); ?>"><?php esc_html_e( 'فروشگاه', 'rasta-commerce' ); ?></a>
				<?php if ( $is_category ) : ?>
					<span class="rasta-breadcrumb-separator" aria-hidden="true">/</span>
					<span><?php echo esc_html( $current_term->name ); ?></span>
				<?php endif; ?>
			</nav>
		</div>

		<div class="rasta-section-heading">
			<div>
				<h1>
					<?php if ( $is_category ) : ?>
						<?php echo esc_html( $current_term->name ); ?>
					<?php else : ?>
						<?php esc_html_e( 'فروشگاه', 'rasta-commerce' ); ?>
					<?php endif; ?>
				</h1>
				<?php if ( $is_category && $current_term->description ) : ?>
					<p><?php echo esc_html( $current_term->description ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( have_posts() ) : ?>
			<ul class="products columns-4 rasta-products-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php rasta_render_product_card_from_payload( rasta_get_product_payload( get_the_ID() ) ); ?>
				<?php endwhile; ?>
			</ul>

			<div class="rasta-pagination">
				<?php
				the_posts_pagination(
					array(
						'prev_text' => '← ' . __( 'قبلی', 'rasta-commerce' ),
						'next_text' => __( 'بعدی', 'rasta-commerce' ) . ' →',
					)
				);
				?>
			</div>
		<?php else : ?>
			<div class="rasta-empty-state">
				<?php rasta_icon( 'box' ); ?>
				<p><?php esc_html_e( 'هنوز محصولی برای نمایش وجود ندارد.', 'rasta-commerce' ); ?></p>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					<a class="rasta-button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rasta_product' ) ); ?>"><?php esc_html_e( 'مدیریت محصولات', 'rasta-commerce' ); ?></a>
				<?php else : ?>
					<a class="rasta-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به خانه', 'rasta-commerce' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
</main>
<?php get_footer(); ?>
