<?php
/**
 * Built-in product system: CPT, taxonomy, meta boxes, and query helpers.
 *
 * Provides a complete product management layer so the theme works without
 * WooCommerce. When WooCommerce is active it takes priority automatically.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the rasta_product custom post type and related taxonomies.
 *
 * @return void
 */
function rasta_register_product_cpt() {
	register_post_type(
		'rasta_product',
		array(
			'labels'              => array(
				'name'                  => __( 'محصولات', 'rasta-commerce' ),
				'singular_name'         => __( 'محصول', 'rasta-commerce' ),
				'add_new'               => __( 'افزودن محصول', 'rasta-commerce' ),
				'add_new_item'          => __( 'افزودن محصول جدید', 'rasta-commerce' ),
				'edit_item'             => __( 'ویرایش محصول', 'rasta-commerce' ),
				'new_item'              => __( 'محصول جدید', 'rasta-commerce' ),
				'view_item'             => __( 'مشاهده محصول', 'rasta-commerce' ),
				'search_items'          => __( 'جست‌وجوی محصول', 'rasta-commerce' ),
				'not_found'             => __( 'محصولی پیدا نشد.', 'rasta-commerce' ),
				'not_found_in_trash'    => __( 'محصولی در زباله‌دان نیست.', 'rasta-commerce' ),
				'all_items'             => __( 'همه محصولات', 'rasta-commerce' ),
				'menu_name'             => __( 'فروشگاه', 'rasta-commerce' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'product', 'with_front' => false ),
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-cart',
			'menu_position'       => 6,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_in_nav_menus'   => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
		)
	);

	register_taxonomy(
		'rasta_product_cat',
		'rasta_product',
		array(
			'labels'            => array(
				'name'              => __( 'دسته‌بندی محصولات', 'rasta-commerce' ),
				'singular_name'     => __( 'دسته‌بندی', 'rasta-commerce' ),
				'add_new_item'      => __( 'افزودن دسته‌بندی جدید', 'rasta-commerce' ),
				'edit_item'         => __( 'ویرایش دسته‌بندی', 'rasta-commerce' ),
				'search_items'      => __( 'جست‌وجوی دسته‌بندی', 'rasta-commerce' ),
				'all_items'         => __( 'همه دسته‌بندی‌ها', 'rasta-commerce' ),
				'parent_item'       => __( 'دسته‌بندی مادر', 'rasta-commerce' ),
				'parent_item_colon' => __( 'دسته‌بندی مادر:', 'rasta-commerce' ),
				'menu_name'         => __( 'دسته‌بندی‌ها', 'rasta-commerce' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'product-category', 'with_front' => false ),
			'show_ui'           => true,
		)
	);

	register_taxonomy(
		'rasta_product_tag',
		'rasta_product',
		array(
			'labels'            => array(
				'name'          => __( 'برچسب‌های محصول', 'rasta-commerce' ),
				'singular_name' => __( 'برچسب', 'rasta-commerce' ),
				'add_new_item'  => __( 'افزودن برچسب جدید', 'rasta-commerce' ),
				'edit_item'     => __( 'ویرایش برچسب', 'rasta-commerce' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'product-tag', 'with_front' => false ),
			'show_ui'           => true,
		)
	);
}
add_action( 'init', 'rasta_register_product_cpt' );

/**
 * Flush rewrite rules on theme activation so product URLs work immediately.
 *
 * @return void
 */
function rasta_flush_rewrite_rules() {
	rasta_register_product_cpt();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'rasta_flush_rewrite_rules' );

/**
 * Register custom meta fields for rasta_product.
 *
 * @return void
 */
function rasta_register_product_meta() {
	$fields = array(
		'_rasta_price'            => 'number',
		'_rasta_sale_price'       => 'number',
		'_rasta_sku'              => 'string',
		'_rasta_stock_quantity'   => 'integer',
		'_rasta_manage_stock'     => 'boolean',
		'_rasta_stock_status'     => 'string',
		'_rasta_gallery_ids'      => 'string',
		'_rasta_weight'           => 'string',
		'_rasta_sale_end_date'    => 'string',
		'_rasta_featured'         => 'boolean',
	);

	foreach ( $fields as $key => $type ) {
		register_post_meta(
			'rasta_product',
			$key,
			array(
				'single'        => true,
				'type'          => $type,
				'show_in_rest'  => true,
				'sanitize_callback' => 'rasta_sanitize_product_meta',
			)
		);
	}
}
add_action( 'init', 'rasta_register_product_meta' );

/**
 * Generic sanitizer for product meta values.
 *
 * @param mixed  $value   Submitted value.
 * @param string $key     Meta key.
 * @param string $type    Declared type.
 * @param string $object  Object subtype (post type).
 * @return mixed Sanitized value.
 */
function rasta_sanitize_product_meta( $value, $key, $type, $object ) {
	return match ( $type ) {
		'number'  => max( 0, (float) $value ),
		'integer' => max( 0, (int) $value ),
		'boolean' => (bool) $value,
		default   => sanitize_text_field( (string) $value ),
	};
}

/* ─── Admin meta boxes ─────────────────────────────────────────────────── */

/**
 * Register product data meta box in the admin editor.
 *
 * @return void
 */
function rasta_product_meta_box_setup() {
	add_meta_box(
		'rasta_product_data',
		__( 'اطلاعات محصول', 'rasta-commerce' ),
		'rasta_render_product_meta_box',
		'rasta_product',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'rasta_product_meta_box_setup' );

/**
 * Render the product data meta box with pricing, stock, and SKU fields.
 *
 * @param \WP_Post $post Current post.
 * @return void
 */
function rasta_render_product_meta_box( $post ) {
	wp_nonce_field( 'rasta_product_data', 'rasta_product_nonce' );

	$price          = get_post_meta( $post->ID, '_rasta_price', true );
	$sale_price     = get_post_meta( $post->ID, '_rasta_sale_price', true );
	$sku            = get_post_meta( $post->ID, '_rasta_sku', true );
	$stock_qty      = get_post_meta( $post->ID, '_rasta_stock_quantity', true );
	$manage_stock   = get_post_meta( $post->ID, '_rasta_manage_stock', true );
	$stock_status   = get_post_meta( $post->ID, '_rasta_stock_status', true ) ?: 'instock';
	$sale_end       = get_post_meta( $post->ID, '_rasta_sale_end_date', true );
	$featured       = get_post_meta( $post->ID, '_rasta_featured', true );
	$gallery_ids    = get_post_meta( $post->ID, '_rasta_gallery_ids', true );
	$weight         = get_post_meta( $post->ID, '_rasta_weight', true );
	?>
	<style>
		.rasta-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 20px; }
		.rasta-meta-grid label { display: block; font-weight: 600; margin-bottom: 4px; }
		.rasta-meta-grid input, .rasta-meta-grid select { width: 100%; }
		.rasta-meta-full { grid-column: 1 / -1; }
	</style>
	<div class="rasta-meta-grid">
		<div>
			<label for="rasta_price"><?php esc_html_e( 'قیمت (تومان)', 'rasta-commerce' ); ?></label>
			<input type="number" id="rasta_price" name="_rasta_price" value="<?php echo esc_attr( $price ); ?>" min="0" step="1" />
		</div>
		<div>
			<label for="rasta_sale_price"><?php esc_html_e( 'قیمت فروش ویژه', 'rasta-commerce' ); ?></label>
			<input type="number" id="rasta_sale_price" name="_rasta_sale_price" value="<?php echo esc_attr( $sale_price ); ?>" min="0" step="1" />
		</div>
		<div>
			<label for="rasta_sku"><?php esc_html_e( 'کد محصول (SKU)', 'rasta-commerce' ); ?></label>
			<input type="text" id="rasta_sku" name="_rasta_sku" value="<?php echo esc_attr( $sku ); ?>" />
		</div>
		<div>
			<label for="rasta_weight"><?php esc_html_e( 'وزن (گرم)', 'rasta-commerce' ); ?></label>
			<input type="text" id="rasta_weight" name="_rasta_weight" value="<?php echo esc_attr( $weight ); ?>" />
		</div>
		<div>
			<label for="rasta_manage_stock">
				<input type="checkbox" id="rasta_manage_stock" name="_rasta_manage_stock" value="1" <?php checked( $manage_stock ); ?> />
				<?php esc_html_e( 'مدیریت موجودی', 'rasta-commerce' ); ?>
			</label>
		</div>
		<div>
			<label for="rasta_stock_quantity"><?php esc_html_e( 'تعداد موجودی', 'rasta-commerce' ); ?></label>
			<input type="number" id="rasta_stock_quantity" name="_rasta_stock_quantity" value="<?php echo esc_attr( $stock_qty ); ?>" min="0" step="1" />
		</div>
		<div>
			<label for="rasta_stock_status"><?php esc_html_e( 'وضعیت موجودی', 'rasta-commerce' ); ?></label>
			<select id="rasta_stock_status" name="_rasta_stock_status">
				<option value="instock" <?php selected( $stock_status, 'instock' ); ?>><?php esc_html_e( 'موجود', 'rasta-commerce' ); ?></option>
				<option value="outofstock" <?php selected( $stock_status, 'outofstock' ); ?>><?php esc_html_e( 'ناموجود', 'rasta-commerce' ); ?></option>
			</select>
		</div>
		<div>
			<label for="rasta_sale_end"><?php esc_html_e( 'پایان تخفیف (YYYY-MM-DD)', 'rasta-commerce' ); ?></label>
			<input type="date" id="rasta_sale_end" name="_rasta_sale_end_date" value="<?php echo esc_attr( $sale_end ); ?>" />
		</div>
		<div>
			<label>
				<input type="checkbox" name="_rasta_featured" value="1" <?php checked( $featured ); ?> />
				<?php esc_html_e( 'محصول ویژه', 'rasta-commerce' ); ?>
			</label>
		</div>
		<div class="rasta-meta-full">
			<label for="rasta_gallery_ids"><?php esc_html_e( 'شناسه تصاویر گالری (comma-separated IDs)', 'rasta-commerce' ); ?></label>
			<input type="text" id="rasta_gallery_ids" name="_rasta_gallery_ids" value="<?php echo esc_attr( $gallery_ids ); ?>" />
		</div>
	</div>
	<?php
}

/**
 * Save product meta fields submitted from the editor.
 *
 * @param int      $post_id Post ID.
 * @param \WP_Post $post    Post object.
 * @return void
 */
function rasta_save_product_meta( $post_id, $post ) {
	if ( ! isset( $_POST['rasta_product_nonce'] ) || ! wp_verify_nonce( $_POST['rasta_product_nonce'], 'rasta_product_data' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'rasta_product' !== $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$number_fields  = array( '_rasta_price', '_rasta_sale_price', '_rasta_stock_quantity' );
	$text_fields    = array( '_rasta_sku', '_rasta_weight', '_rasta_stock_status', '_rasta_sale_end_date', '_rasta_gallery_ids' );
	$boolean_fields = array( '_rasta_manage_stock', '_rasta_featured' );

	foreach ( $number_fields as $key ) {
		$value = isset( $_POST[ $key ] ) ? max( 0, (float) $_POST[ $key ] ) : '';
		update_post_meta( $post_id, $key, $value );
	}

	foreach ( $text_fields as $key ) {
		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		update_post_meta( $post_id, $key, $value );
	}

	foreach ( $boolean_fields as $key ) {
		$value = isset( $_POST[ $key ] ) ? 1 : 0;
		update_post_meta( $post_id, $key, $value );
	}

	/* Auto-determine stock status from quantity when management is enabled. */
	$manage  = isset( $_POST['_rasta_manage_stock'] ) ? 1 : 0;
	$qty     = isset( $_POST['_rasta_stock_quantity'] ) ? (int) $_POST['_rasta_stock_quantity'] : 0;
	$status  = isset( $_POST['_rasta_stock_status'] ) ? sanitize_text_field( wp_unslash( $_POST['_rasta_stock_status'] ) ) : 'instock';

	if ( $manage && $qty <= 0 ) {
		update_post_meta( $post_id, '_rasta_stock_status', 'outofstock' );
	} elseif ( $manage && $qty > 0 ) {
		update_post_meta( $post_id, '_rasta_stock_status', 'instock' );
	} else {
		update_post_meta( $post_id, '_rasta_stock_status', $status );
	}
}
add_action( 'save_post', 'rasta_save_product_meta', 10, 2 );

/* ─── Front-end product helpers ────────────────────────────────────────── */

/**
 * Return the regular price for a built-in product.
 *
 * @param int $product_id Product post ID.
 * @return float
 */
function rasta_get_product_price( $product_id ) {
	return (float) get_post_meta( $product_id, '_rasta_price', true );
}

/**
 * Return the sale price for a built-in product (or 0 if not on sale).
 *
 * @param int $product_id Product post ID.
 * @return float
 */
function rasta_get_product_sale_price( $product_id ) {
	$sale = (float) get_post_meta( $product_id, '_rasta_sale_price', true );
	if ( $sale <= 0 ) {
		return 0.0;
	}

	$end = get_post_meta( $product_id, '_rasta_sale_end_date', true );
	if ( $end && strtotime( $end ) < current_time( 'timestamp' ) ) {
		return 0.0;
	}

	return $sale;
}

/**
 * Whether a built-in product is currently on sale.
 *
 * @param int $product_id Product post ID.
 * @return bool
 */
function rasta_product_is_on_sale( $product_id ) {
	$sale = rasta_get_product_sale_price( $product_id );
	return $sale > 0;
}

/**
 * Return the active price (sale price when applicable, otherwise regular).
 *
 * @param int $product_id Product post ID.
 * @return float
 */
function rasta_get_product_active_price( $product_id ) {
	$sale = rasta_get_product_sale_price( $product_id );
	return $sale > 0 ? $sale : rasta_get_product_price( $product_id );
}

/**
 * Build an HTML price string for a built-in product.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function rasta_get_product_price_html( $product_id ) {
	$regular = rasta_get_product_price( $product_id );
	$sale    = rasta_get_product_sale_price( $product_id );

	if ( $regular <= 0 ) {
		return esc_html__( 'رایگان', 'rasta-commerce' );
	}

	$formatted_regular = rasta_format_currency( $regular );

	if ( $sale > 0 && $sale < $regular ) {
		$formatted_sale = rasta_format_currency( $sale );
		return sprintf(
			'<del aria-hidden="true">%1$s</del> <ins>%2$s</ins>',
			$formatted_regular,
			$formatted_sale
		);
	}

	return $formatted_regular;
}

/**
 * Format a numeric value as the store currency (Toman by default).
 *
 * @param float $amount Numeric amount.
 * @return string Formatted currency string.
 */
function rasta_format_currency( $amount ) {
	$currency     = get_theme_mod( 'rasta_currency', 'IRT' );
	$symbol       = 'IRR' === $currency ? '﷼' : 'تومان';
	$decimals     = 'IRR' === $currency ? 0 : 0;
	$thousands    = '،';
	$formatted    = number_format( (float) $amount, $decimals, '.', $thousands );

	return '<span class="rasta-price">' . rasta_to_persian_digits( $formatted ) . ' ' . $symbol . '</span>';
}

/**
 * Return a plain-text formatted currency value (no markup).
 *
 * Used in AJAX payloads so the client can render the value safely with
 * textContent instead of innerHTML.
 *
 * @param float|int|string $amount Amount to format.
 * @return string
 */
function rasta_format_currency_plain( $amount ) {
	$currency  = get_theme_mod( 'rasta_currency', 'IRT' );
	$symbol    = 'IRR' === $currency ? '﷼' : 'تومان';
	$formatted = number_format( (float) $amount, 0, '.', '،' );

	return rasta_to_persian_digits( $formatted ) . ' ' . $symbol;
}

/**
 * Return the stock status label for a built-in product.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function rasta_get_product_stock_label( $product_id ) {
	$status = get_post_meta( $product_id, '_rasta_stock_status', true ) ?: 'instock';
	$manage = (bool) get_post_meta( $product_id, '_rasta_manage_stock', true );
	$qty    = (int) get_post_meta( $product_id, '_rasta_stock_quantity', true );

	if ( 'outofstock' === $status ) {
		return esc_html__( 'ناموجود', 'rasta-commerce' );
	}

	if ( $manage ) {
		$threshold = (int) get_theme_mod( 'rasta_low_stock_threshold', 3 );
		if ( $threshold > 0 && $qty <= $threshold ) {
			return sprintf(
				/* translators: %s: quantity remaining. */
				esc_html__( 'فقط %s عدد باقی مانده', 'rasta-commerce' ),
				rasta_to_persian_digits( number_format_i18n( $qty ) )
			);
		}

		return sprintf(
			/* translators: %s: quantity available. */
			esc_html__( '%s عدد موجود', 'rasta-commerce' ),
			rasta_to_persian_digits( number_format_i18n( $qty ) )
		);
	}

	return esc_html__( 'موجود', 'rasta-commerce' );
}

/**
 * Whether a built-in product is in stock.
 *
 * @param int $product_id Product post ID.
 * @return bool
 */
function rasta_product_is_in_stock( $product_id ) {
	$status = get_post_meta( $product_id, '_rasta_stock_status', true ) ?: 'instock';
	return 'outofstock' !== $status;
}

/**
 * Return the SKU for a built-in product.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function rasta_get_product_sku( $product_id ) {
	return (string) get_post_meta( $product_id, '_rasta_sku', true );
}

/**
 * Return the product image URL.
 *
 * @param int    $product_id Product post ID.
 * @param string $size       Image size.
 * @return string
 */
function rasta_get_product_image_url( $product_id, $size = 'medium' ) {
	$thumb_id = get_post_thumbnail_id( $product_id );
	if ( ! $thumb_id ) {
		return '';
	}

	$url = wp_get_attachment_image_url( $thumb_id, $size );
	return $url ? $url : '';
}

/**
 * Return gallery image IDs for a built-in product.
 *
 * @param int $product_id Product post ID.
 * @return int[]
 */
function rasta_get_product_gallery_ids( $product_id ) {
	$raw = get_post_meta( $product_id, '_rasta_gallery_ids', true );
	if ( ! $raw ) {
		return array();
	}

	return array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
}

/**
 * Whether a product is "new" based on its publish date.
 *
 * @param int $product_id Product post ID.
 * @return bool
 */
function rasta_product_is_new( $product_id ) {
	$days    = (int) get_theme_mod( 'rasta_newness_days', 30 );
	$created = get_post_time( 'U', true, $product_id );

	return $days > 0 && $created >= ( time() - ( DAY_IN_SECONDS * $days ) );
}

/**
 * Return the sale end timestamp for countdown purposes.
 *
 * @param int $product_id Product post ID.
 * @return int
 */
function rasta_get_product_sale_end_timestamp( $product_id ) {
	$end = get_post_meta( $product_id, '_rasta_sale_end_date', true );
	return $end ? (int) strtotime( $end . ' 23:59:59' ) : 0;
}

/* ─── Query helpers ────────────────────────────────────────────────────── */

/**
 * Query built-in products with flexible arguments.
 *
 * @param array $args Query arguments (limit, orderby, order, category, featured, search).
 * @return \WP_Post[]
 */
function rasta_get_products( $args = array() ) {
	$defaults = array(
		'limit'    => 12,
		'orderby'  => 'date',
		'order'    => 'DESC',
		'category' => '',
		'featured' => false,
		'search'   => '',
		'offset'   => 0,
	);

	$args = wp_parse_args( $args, $defaults );

	$query_args = array(
		'post_type'      => 'rasta_product',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $args['limit'],
		'orderby'        => $args['orderby'],
		'order'          => $args['order'],
		'offset'         => (int) $args['offset'],
	);

	if ( $args['search'] ) {
		$query_args['s'] = sanitize_text_field( $args['search'] );
	}

	if ( $args['category'] ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'rasta_product_cat',
				'field'    => 'slug',
				'terms'    => $args['category'],
			),
		);
	}

	if ( $args['featured'] ) {
		$query_args['meta_query'] = array(
			array(
				'key'   => '_rasta_featured',
				'value' => '1',
			),
		);
	}

	if ( 'popularity' === $args['orderby'] ) {
		$query_args['orderby']  = 'meta_value_num';
		$query_args['meta_key'] = '_rasta_popularity';
	}

	if ( 'price' === $args['orderby'] ) {
		$query_args['orderby']  = 'meta_value_num';
		$query_args['meta_key'] = '_rasta_price';
	}

	return get_posts( $query_args );
}

/**
 * Return a built-in product as a structured payload for AJAX responses.
 *
 * @param int|\WP_Post $product Product post ID or object.
 * @return array<string, mixed>
 */
function rasta_get_product_payload( $product ) {
	$post = $product instanceof \WP_Post ? $product : get_post( $product );
	if ( ! $post || 'rasta_product' !== $post->post_type ) {
		return array();
	}

	$id        = $post->ID;
	$image_url = rasta_get_product_image_url( $id, 'medium' );

	$categories = get_the_terms( $id, 'rasta_product_cat' );
	$cat_names  = ! is_wp_error( $categories ) && $categories ? wp_list_pluck( $categories, 'name' ) : array();

	$attrs = array();
	$all_meta = get_post_meta( $id );
	foreach ( $all_meta as $key => $values ) {
		if ( str_starts_with( $key, '_rasta_attr_' ) ) {
			$label = str_replace( '_', ' ', substr( $key, 12 ) );
			$attrs[ $key ] = array(
				'label' => sanitize_text_field( $label ),
				'value' => sanitize_text_field( $values[0] ),
			);
		}
	}

	$sale_end = rasta_get_product_sale_end_timestamp( $id );

	return array(
		'id'           => $id,
		'name'         => wp_strip_all_tags( $post->post_title ),
		'url'          => get_permalink( $id ),
		'image'        => esc_url_raw( $image_url ),
		'imageAlt'     => wp_strip_all_tags( $post->post_title ),
		'price'        => wp_strip_all_tags( rasta_get_product_price_html( $id ) ),
		'priceValue'   => rasta_get_product_active_price( $id ),
		'regularPrice' => rasta_get_product_price( $id ),
		'salePrice'    => rasta_get_product_sale_price( $id ),
		'isOnSale'     => rasta_product_is_on_sale( $id ),
		'saleEnd'      => $sale_end,
		'category'     => implode( '، ', $cat_names ),
		'sku'          => rasta_get_product_sku( $id ),
		'stock'        => rasta_get_product_stock_label( $id ),
		'inStock'      => rasta_product_is_in_stock( $id ),
		'isNew'        => rasta_product_is_new( $id ),
		'description'  => wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 34 ),
		'attributes'   => $attrs,
	);
}

/* ─── Product categories helper ────────────────────────────────────────── */

/**
 * Return top product categories for the storefront.
 *
 * @param int $count Maximum number of categories.
 * @return \WP_Term[]
 */
function rasta_get_product_categories( $count = 6 ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'rasta_product_cat',
			'hide_empty' => false,
			'number'     => $count,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Return the shop page URL.
 *
 * @return string
 */
function rasta_get_shop_url() {
	$post_type = get_post_type_archive_link( 'rasta_product' );
	return $post_type ? $post_type : home_url( '/product/' );
}

/**
 * Return the cart page URL.
 *
 * @return string
 */
function rasta_get_cart_url() {
	$page = get_option( 'rasta_cart_page_id' );
	if ( $page ) {
		$url = get_permalink( $page );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/cart/' );
}

/**
 * Return the checkout page URL.
 *
 * @return string
 */
function rasta_get_checkout_url() {
	$page = get_option( 'rasta_checkout_page_id' );
	if ( $page ) {
		$url = get_permalink( $page );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/checkout/' );
}

/**
 * Return the My Account URL.
 *
 * @return string
 */
function rasta_get_account_url() {
	$page = get_option( 'rasta_account_page_id' );
	if ( $page ) {
		$url = get_permalink( $page );
		if ( $url ) {
			return $url;
		}
	}
	return wp_login_url();
}

/* ─── Store page auto-creation ─────────────────────────────────────────── */

/**
 * Auto-create shop, cart, checkout, and account pages on theme activation.
 *
 * @return void
 */
function rasta_create_store_pages() {
	$pages = array(
		'rasta_cart_page_id'     => array(
			'title'   => __( 'سبد خرید', 'rasta-commerce' ),
			'slug'    => 'cart',
			'content' => '[rasta_cart]',
		),
		'rasta_checkout_page_id' => array(
			'title'   => __( 'تکمیل خرید', 'rasta-commerce' ),
			'slug'    => 'checkout',
			'content' => '[rasta_checkout]',
		),
		'rasta_account_page_id'  => array(
			'title'   => __( 'حساب کاربری', 'rasta-commerce' ),
			'slug'    => 'my-account',
			'content' => '[rasta_account]',
		),
	);

	foreach ( $pages as $option_key => $page_data ) {
		$existing = get_option( $option_key );
		if ( $existing && get_post( $existing ) ) {
			continue;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $page_data['title'],
				'post_name'    => $page_data['slug'],
				'post_content' => $page_data['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( $option_key, $page_id );
		}
	}
}
add_action( 'after_switch_theme', 'rasta_create_store_pages' );
