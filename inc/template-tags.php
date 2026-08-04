<?php
/**
 * Reusable presentation helpers.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a trusted inline SVG icon.
 *
 * @param string $name  Icon name.
 * @param string $class Optional CSS class.
 * @return string
 */
function rasta_get_icon( $name, $class = '' ) {
	$icons = array(
		'arrow-left' => '<path d="M19 12H5m6-6-6 6 6 6"/>',
		'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
		'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
		'search'     => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/>',
		'user'       => '<circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c.9-3.5 3.4-5.25 7.5-5.25S18.6 16.5 19.5 20"/>',
		'cart'       => '<path d="M3.5 4h2l1.7 10h9.9l1.8-7.2H7.1"/><circle cx="9" cy="19" r="1.2"/><circle cx="17" cy="19" r="1.2"/>',
		'menu'       => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'      => '<path d="m6 6 12 12M18 6 6 18"/>',
		'heart'      => '<path d="M20.8 8.6c0 5.1-8.8 10.2-8.8 10.2S3.2 13.7 3.2 8.6A4.5 4.5 0 0 1 12 7.1a4.5 4.5 0 0 1 8.8 1.5Z"/>',
		'eye'        => '<path d="M2.8 12S6.2 6.5 12 6.5 21.2 12 21.2 12 17.8 17.5 12 17.5 2.8 12 2.8 12Z"/><circle cx="12" cy="12" r="2.5"/>',
		'compare'    => '<path d="M8 4v15M5 7l3-3 3 3M16 20V5m-3 12 3 3 3-3"/><path d="M4 20h16"/>',
		'trash'      => '<path d="M4 7h16M10 11v5m4-5v5M9 7l1-3h4l1 3M6 7l1 13h10l1-13"/>',
		'clock'      => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3.5 2"/>',
		'plus'       => '<path d="M12 5v14M5 12h14"/>',
		'truck'      => '<path d="M3 5h11v11H3zM14 9h3l3 3v4h-6z"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/>',
		'shield'     => '<path d="M12 3 20 6v5c0 5-3.2 8.5-8 10-4.8-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/>',
		'headset'    => '<path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h3v6H5a1 1 0 0 1-1-1v-5Zm16 0h-3v6h2a1 1 0 0 0 1-1v-5Z"/><path d="M17 19c0 1.2-1.2 2-3 2h-2"/>',
		'box'        => '<path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/>',
		'star'       => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
		'sparkles'   => '<path d="m12 3 1.2 4.8L18 9l-4.8 1.2L12 15l-1.2-4.8L6 9l4.8-1.2L12 3Zm6 11 .7 2.3L21 17l-2.3.7L18 20l-.7-2.3L15 17l2.3-.7L18 14ZM5 15l.8 2.2L8 18l-2.2.8L5 21l-.8-2.2L2 18l2.2-.8L5 15Z"/>',
		'home'       => '<path d="m3 11 9-8 9 8v9H3v-9Z"/><path d="M9 20v-6h6v6"/>',
		'watch'      => '<rect x="7" y="6" width="10" height="12" rx="3"/><path d="M9 3h6M9 21h6M10 10h4v4h-4z"/>',
		'bag'        => '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/>',
		'laptop'     => '<rect x="4" y="5" width="16" height="11" rx="1"/><path d="M2 19h20"/>',
		'check'      => '<path d="m5 12 4 4L19 6"/>',
		'instagram'  => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.6" r=".75" fill="currentColor" stroke="none"/>',
		'telegram'   => '<path d="m21 4-3 16-5.5-5-3.1 3 .6-4.3L5 11l16-7Z"/><path d="m9.8 13.7 7.6-7"/>',
		'linkedin'   => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 10v6M8 7v.1M12 16v-3.4a2.6 2.6 0 0 1 5.2 0V16M12 10v6"/>',
	);

	$path = isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['sparkles'];
	$attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';

	return '<svg' . $attr . ' viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

/**
 * Echo a trusted theme icon.
 *
 * @param string $name  Icon name.
 * @param string $class Optional CSS class.
 * @return void
 */
function rasta_icon( $name, $class = '' ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG comes from the trusted map above.
	echo rasta_get_icon( $name, $class );
}

/**
 * Return the WooCommerce shop URL or a safe home fallback.
 *
 * @return string
 */
function rasta_get_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}

	return home_url( '/' );
}

/**
 * Return the My Account URL or a safe home fallback.
 *
 * @return string
 */
function rasta_get_account_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	return wp_login_url();
}

/**
 * Get the current cart item count without failing when WooCommerce is inactive.
 *
 * @return int
 */
function rasta_get_cart_count() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Output the cart count element used by WooCommerce fragments.
 *
 * @return void
 */
function rasta_cart_count_markup() {
	printf(
		'<span class="rasta-cart-count" aria-label="%1$s">%2$s</span>',
		esc_attr__( 'تعداد محصولات سبد خرید', 'rasta-commerce' ),
		esc_html( (string) rasta_get_cart_count() )
	);
}

/**
 * Output the primary navigation with a useful fallback before a menu is assigned.
 *
 * @return void
 */
function rasta_primary_navigation() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'rasta-nav__list',
				'menu_id'        => 'rasta-primary-menu',
				'fallback_cb'    => false,
			)
		);
		return;
	}
	?>
	<ul id="rasta-primary-menu" class="rasta-nav__list">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'rasta-commerce' ); ?></a></li>
		<li><a href="<?php echo esc_url( rasta_get_shop_url() ); ?>"><?php esc_html_e( 'فروشگاه', 'rasta-commerce' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'مجله', 'rasta-commerce' ); ?></a></li>
	</ul>
	<?php
}

/**
 * Print a compact post date and author meta row.
 *
 * @return void
 */
function rasta_posted_on() {
	$time_string = sprintf(
		'<time class="entry-date published" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( rasta_get_the_jalali_date() )
	);
	?>
	<div class="entry-meta">
		<span><?php echo wp_kses_post( $time_string ); ?></span>
		<span aria-hidden="true">•</span>
		<span><?php the_author(); ?></span>
	</div>
	<?php
}

/**
 * Render a Jalali-aware comment item.
 *
 * @param WP_Comment $comment Comment object.
 * @param array      $args    Comment list arguments.
 * @param int        $depth   Depth in the thread.
 * @return void
 */
function rasta_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php echo get_avatar( $comment, 46 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core avatar markup. ?>
					<b class="fn"><?php comment_author_link(); ?></b>
				</div>
				<div class="comment-metadata">
					<time datetime="<?php echo esc_attr( get_comment_date( DATE_W3C ) ); ?>"><?php echo esc_html( rasta_jalali_date( 'j F Y', (int) get_comment_time( 'U', true ) ) ); ?></time>
				</div>
			</footer>
			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php esc_html_e( 'دیدگاه شما پس از تأیید نمایش داده می‌شود.', 'rasta-commerce' ); ?></p>
			<?php endif; ?>
			<div class="comment-content"><?php comment_text(); ?></div>
			<div class="reply">
				<?php
				comment_reply_link(
					array_merge(
						$args,
						array(
							'add_below' => 'div-comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
						)
					)
				);
				?>
			</div>
		</article>
	<?php
}

/**
 * Output footer menu or a concise set of useful links.
 *
 * @return void
 */
function rasta_footer_navigation() {
	if ( has_nav_menu( 'footer' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'rasta-footer-links',
				'fallback_cb'    => false,
			)
		);
		return;
	}
	?>
	<ul class="rasta-footer-links">
		<li><a href="<?php echo esc_url( rasta_get_shop_url() ); ?>"><?php esc_html_e( 'فروشگاه', 'rasta-commerce' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'درباره ما', 'rasta-commerce' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'تماس با ما', 'rasta-commerce' ); ?></a></li>
	</ul>
	<?php
}

/**
 * Output only the social links the store owner has configured.
 *
 * @return void
 */
function rasta_social_links() {
	$networks = array(
		'instagram' => array(
			'key'   => 'rasta_instagram_url',
			'label' => 'Instagram',
		),
		'telegram'  => array(
			'key'   => 'rasta_telegram_url',
			'label' => 'Telegram',
		),
		'linkedin'  => array(
			'key'   => 'rasta_linkedin_url',
			'label' => 'LinkedIn',
		),
	);
	$links    = array();

	foreach ( $networks as $icon => $network ) {
		$url = rasta_get_mod( $network['key'], '' );
		if ( $url ) {
			$links[] = array(
				'icon'  => $icon,
				'label' => $network['label'],
				'url'   => $url,
			);
		}
	}

	if ( empty( $links ) ) {
		return;
	}
	?>
	<div class="rasta-social-links" aria-label="<?php esc_attr_e( 'شبکه‌های اجتماعی', 'rasta-commerce' ); ?>">
		<?php foreach ( $links as $link ) : ?>
			<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $link['label'] ); ?>">
				<?php rasta_icon( $link['icon'] ); ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render up to six WooCommerce product categories.
 *
 * @return void
 */
function rasta_render_product_categories() {
	if ( ! function_exists( 'wc_get_page_permalink' ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'      => 6,
			'orderby'     => 'count',
			'order'       => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$icons = array( 'headset', 'sparkles', 'home', 'watch', 'bag', 'laptop' );
	?>
	<div class="rasta-category-grid">
		<?php foreach ( $terms as $index => $term ) : ?>
			<?php
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$image_id   = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : false;
			$icon_name  = $icons[ $index % count( $icons ) ];
			$count_text = sprintf(
				/* translators: %s: number of products in the category. */
				_n( '%s محصول', '%s محصول', (int) $term->count, 'rasta-commerce' ),
				number_format_i18n( (int) $term->count )
			);
			?>
			<a class="rasta-category-card" href="<?php echo esc_url( $link ); ?>">
				<span class="rasta-category-card__visual">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" />
					<?php else : ?>
						<?php rasta_icon( $icon_name, 'rasta-category-card__icon' ); ?>
					<?php endif; ?>
				</span>
				<span class="rasta-category-card__title"><?php echo esc_html( $term->name ); ?></span>
					<span class="rasta-category-card__count"><?php echo esc_html( $count_text ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render a WooCommerce product rail using the theme's product card template.
 *
 * @param string               $title       Section title.
 * @param string               $description Section description.
 * @param array<string, mixed> $query_args  WC product query arguments.
 * @return void
 */
function rasta_render_product_rail( $title, $description, $query_args = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return;
	}

	$products = wc_get_products(
		wp_parse_args(
			$query_args,
			array(
				'limit'   => 4,
				'status'  => 'publish',
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		)
	);
	?>
	<section class="rasta-product-section rasta-section" aria-labelledby="rasta-products-<?php echo esc_attr( sanitize_title( $title ) ); ?>">
		<div class="rasta-section-heading">
			<div>
				<p class="rasta-kicker"><?php esc_html_e( 'منتخب فروشگاه', 'rasta-commerce' ); ?></p>
				<h2 id="rasta-products-<?php echo esc_attr( sanitize_title( $title ) ); ?>"><?php echo esc_html( $title ); ?></h2>
				<?php if ( $description ) : ?>
					<p><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
			<a class="rasta-text-link" href="<?php echo esc_url( rasta_get_shop_url() ); ?>">
				<?php esc_html_e( 'همه محصولات', 'rasta-commerce' ); ?>
				<?php rasta_icon( 'arrow-left' ); ?>
			</a>
		</div>

		<?php if ( ! empty( $products ) ) : ?>
				<?php
			global $post;
			$previous_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
			?>
			<ul class="products columns-4 rasta-products-grid">
				<?php foreach ( $products as $current_product ) : ?>
					<?php
					$post                 = get_post( $current_product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['product']  = $current_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					if ( $post ) {
						setup_postdata( $post );
						wc_get_template_part( 'content', 'product' );
					}
					?>
				<?php endforeach; ?>
			</ul>
			<?php
			wp_reset_postdata();
			if ( null === $previous_product ) {
				unset( $GLOBALS['product'] );
			} else {
				$GLOBALS['product'] = $previous_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
			?>
		<?php elseif ( current_user_can( 'manage_woocommerce' ) ) : ?>
			<div class="rasta-empty-state">
				<?php rasta_icon( 'box' ); ?>
				<p><?php esc_html_e( 'هنوز محصولی برای نمایش ندارید. با افزودن محصول در ووکامرس، این بخش خودکار تکمیل می‌شود.', 'rasta-commerce' ); ?></p>
			</div>
		<?php endif; ?>
	</section>
	<?php
}
