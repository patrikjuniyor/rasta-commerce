<?php
/**
 * Theme setup, assets, and global presentation settings.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register WordPress features used by the theme.
 *
 * @return void
 */
function rasta_setup() {
	load_theme_textdomain( 'rasta-commerce', RASTA_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-style.css' );

	add_theme_support(
		'custom-logo',
		array(
			'height'               => 72,
			'width'                => 260,
			'flex-width'           => true,
			'flex-height'          => true,
			'unlink-homepage-logo' => true,
		)
	);

	add_theme_support(
		'html5',
		array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);

	/* WooCommerce support (optional enhancement when the plugin is active). */
	if ( class_exists( 'WooCommerce' ) ) {
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 480,
				'single_image_width'    => 920,
				'product_grid'          => array(
					'default_rows'    => 3,
					'min_rows'        => 2,
					'max_rows'        => 8,
					'default_columns' => 4,
					'min_columns'     => 2,
					'max_columns'     => 5,
				),
			)
		);
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	register_nav_menus(
		array(
			'primary' => esc_html__( 'منوی اصلی', 'rasta-commerce' ),
			'footer'  => esc_html__( 'منوی پایین صفحه', 'rasta-commerce' ),
		)
	);
}
add_action( 'after_setup_theme', 'rasta_setup' );

/**
 * Register widget areas.
 *
 * @return void
 */
function rasta_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'ستون کناری وبلاگ', 'rasta-commerce' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'ابزارک‌های این ناحیه در صفحات وبلاگ نمایش داده می‌شوند.', 'rasta-commerce' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'rasta_widgets_init' );

/**
 * Return a cache-busting version for a theme asset.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return string
 */
function rasta_asset_version( $relative_path ) {
	$path = get_theme_file_path( $relative_path );

	return file_exists( $path ) ? (string) filemtime( $path ) : RASTA_VERSION;
}

/**
 * Load frontend assets and pass public, non-sensitive settings to JavaScript.
 *
 * @return void
 */
function rasta_enqueue_assets() {
	wp_enqueue_style( 'rasta-commerce', get_stylesheet_uri(), array(), rasta_asset_version( '/style.css' ) );

	if ( is_rtl() ) {
		wp_enqueue_style( 'rasta-commerce-rtl', RASTA_URI . '/rtl.css', array( 'rasta-commerce' ), rasta_asset_version( '/rtl.css' ) );
	}

	$accent       = rasta_sanitize_brand_color( get_theme_mod( 'rasta_accent_color', '#f25c54' ) );
	$accent_alt   = rasta_sanitize_brand_color( get_theme_mod( 'rasta_accent_alt_color', '#315bd8' ) );
	$shop_columns = function_exists( 'rasta_sanitize_shop_columns' ) ? rasta_sanitize_shop_columns( get_theme_mod( 'rasta_shop_columns', 4 ) ) : 4;
	$inline_css   = sprintf(
		':root{--rasta-accent:%1$s;--rasta-accent-strong:%2$s;--rasta-products-columns:%3$d;}',
		esc_html( $accent ? $accent : '#f25c54' ),
		esc_html( $accent_alt ? $accent_alt : '#315bd8' ),
		(int) $shop_columns
	);
	wp_add_inline_style( 'rasta-commerce', $inline_css );

	wp_enqueue_script( 'rasta-commerce', RASTA_URI . '/assets/js/theme.js', array(), rasta_asset_version( '/assets/js/theme.js' ), true );
	wp_localize_script(
		'rasta-commerce',
		'rastaTheme',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'rasta_product_search' ),
			'toolsNonce' => wp_create_nonce( 'rasta_product_tools' ),
			'cartUrl'    => rasta_get_cart_url(),
			'checkoutUrl'=> rasta_get_checkout_url(),
			'shopUrl'    => rasta_get_shop_url(),
			'features'   => array(
				'quickView'      => rasta_feature_enabled( 'quick_view' ),
				'compare'         => rasta_feature_enabled( 'compare' ),
				'recentlyViewed' => rasta_feature_enabled( 'recently_viewed' ),
				'stickyCart'     => rasta_feature_enabled( 'sticky_cart' ),
				'saleCountdown'  => rasta_feature_enabled( 'sale_countdown' ),
				'darkMode'       => rasta_feature_enabled( 'dark_mode', false ),
				'darkModeDefault'=> rasta_feature_enabled( 'dark_mode_default', false ),
				'dismissiblePromo' => rasta_feature_enabled( 'dismissible_promo', false ),
			),
			'strings'    => array(
				'close'                => esc_html__( 'بستن', 'rasta-commerce' ),
				'searching'            => esc_html__( 'در حال جست‌وجو…', 'rasta-commerce' ),
				'loadingProduct'        => esc_html__( 'در حال آماده‌سازی محصول…', 'rasta-commerce' ),
				'noResults'             => esc_html__( 'محصولی پیدا نشد.', 'rasta-commerce' ),
				'addedToCart'           => esc_html__( 'محصول به سبد خرید اضافه شد.', 'rasta-commerce' ),
				'removedFromCart'       => esc_html__( 'محصول از سبد خرید حذف شد.', 'rasta-commerce' ),
				'cartUpdated'           => esc_html__( 'سبد خرید به‌روزرسانی شد.', 'rasta-commerce' ),
				'networkError'          => esc_html__( 'اتصال برقرار نشد؛ دوباره تلاش کنید.', 'rasta-commerce' ),
				'wishlistEmpty'         => esc_html__( 'هنوز محصولی را ذخیره نکرده‌اید.', 'rasta-commerce' ),
				'compareEmpty'          => esc_html__( 'برای مقایسه، حداقل دو محصول انتخاب کنید.', 'rasta-commerce' ),
				'compareMax'            => esc_html__( 'برای مقایسه حداکثر چهار محصول انتخاب کنید.', 'rasta-commerce' ),
				'compareUpdated'        => esc_html__( 'فهرست مقایسه به‌روزرسانی شد.', 'rasta-commerce' ),
				'remove'                => esc_html__( 'حذف', 'rasta-commerce' ),
				'viewProduct'           => esc_html__( 'مشاهده محصول', 'rasta-commerce' ),
				'productsInComparison' => esc_html__( 'محصول برای مقایسه', 'rasta-commerce' ),
				'goToCart'             => esc_html__( 'مشاهده سبد خرید', 'rasta-commerce' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'rasta_enqueue_assets' );

/**
 * Print a tiny boot script that applies dark mode before first paint.
 *
 * Prevents a flash of the light theme for returning dark-mode visitors.
 *
 * @return void
 */
function rasta_theme_boot_script() {
	if ( ! rasta_feature_enabled( 'dark_mode', false ) ) {
		return;
	}

	$default_dark = rasta_feature_enabled( 'dark_mode_default', false ) ? 'true' : 'false';
	?>
	<script>
		( function () {
			try {
				var stored = window.localStorage.getItem( 'rasta-theme' );
				var dark = stored ? stored === 'dark' : ( <?php echo $default_dark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static boolean literal. ?> || window.matchMedia( '(prefers-color-scheme: dark)' ).matches );
				if ( dark ) {
					document.documentElement.setAttribute( 'data-rasta-theme', 'dark' );
				}
			} catch ( err ) {}
		}() );
	</script>
	<?php
}
add_action( 'wp_head', 'rasta_theme_boot_script', 1 );

/**
 * Add contextual body classes without exposing user data.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function rasta_body_classes( $classes ) {
	$classes[] = 'rasta-site';

	if ( class_exists( 'WooCommerce' ) ) {
		$classes[] = 'rasta-has-woocommerce';
	} else {
		$classes[] = 'rasta-has-built-in-store';
	}

	return $classes;
}
add_filter( 'body_class', 'rasta_body_classes' );
