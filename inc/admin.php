<?php
/**
 * Admin dashboard and product-list enhancements for the built-in store.
 *
 * Adds a Persian store overview page (products, orders, revenue, low-stock
 * alerts), plus informative columns and quick filters on the product list.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ─── Store overview dashboard ────────────────────────────────────────── */

/**
 * Register the store overview page and assets.
 *
 * @return void
 */
function rasta_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=rasta_product',
		esc_html__( 'نمای کلی فروشگاه', 'rasta-commerce' ),
		esc_html__( 'نمای کلی فروشگاه', 'rasta-commerce' ),
		'manage_options',
		'rasta-store-overview',
		'rasta_render_store_overview'
	);

	add_action( 'admin_enqueue_scripts', 'rasta_admin_assets' );
}
add_action( 'admin_menu', 'rasta_admin_menu' );

/**
 * Enqueue dashboard styles on the store overview screen.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function rasta_admin_assets( $hook_suffix ) {
	if ( 'rasta_product_page_rasta-store-overview' !== $hook_suffix ) {
		return;
	}

	wp_add_inline_style(
		'wp-admin',
		'
		.rasta-overview { max-width: 1140px; }
		.rasta-overview__header { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 8px 0 4px; }
		.rasta-overview__actions { display: flex; gap: 8px; flex-wrap: wrap; }
		.rasta-overview__actions .button { display: inline-flex; align-items: center; gap: 6px; }
		.rasta-overview__cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin: 18px 0 24px; }
		.rasta-overview__card { position: relative; display: flex; flex-direction: column; gap: 4px; background: #fff; border: 1px solid #dcdcde; border-radius: 12px; padding: 18px; box-shadow: 0 1px 2px rgb(0 0 0 / 4%); overflow: hidden; }
		.rasta-overview__card::before { content: \'\'; position: absolute; inset-block: 0; inset-inline-start: 0; inline-size: 4px; background: var(--rasta-card-accent, #dcdcde); }
		.rasta-overview__card--accent { --rasta-card-accent: #f25c54; }
		.rasta-overview__card--blue { --rasta-card-accent: #315bd8; }
		.rasta-overview__card--green { --rasta-card-accent: #17865d; }
		.rasta-overview__card--gold { --rasta-card-accent: #f2b84b; }
		.rasta-overview__card--red { --rasta-card-accent: #d63638; }
		.rasta-overview__card .rasta-overview__top { display: flex; align-items: center; justify-content: space-between; }
		.rasta-overview__card .rasta-overview__icon { display: grid; place-items: center; inline-size: 34px; block-size: 34px; border-radius: 9px; background: #f6f7f7; color: #50575e; }
		.rasta-overview__card--accent .rasta-overview__icon { background: #fdf0ef; color: #f25c54; }
		.rasta-overview__card--blue .rasta-overview__icon { background: #eef2ff; color: #315bd8; }
		.rasta-overview__card--green .rasta-overview__icon { background: #e9f6ef; color: #17865d; }
		.rasta-overview__card--gold .rasta-overview__icon { background: #fdf3d8; color: #996800; }
		.rasta-overview__card--red .rasta-overview__icon { background: #fbeaea; color: #d63638; }
		.rasta-overview__card .rasta-overview__label { color: #646970; font-size: 12px; margin: 8px 0 0; }
		.rasta-overview__card .rasta-overview__value { font-size: 26px; font-weight: 700; line-height: 1.15; color: #1d2327; }
		.rasta-overview__card .rasta-overview__value small { font-size: 13px; font-weight: 600; color: #646970; }
		.rasta-overview__card .rasta-overview__hint { color: #8c8f94; font-size: 12px; margin: 0; }
		.rasta-overview__card .rasta-overview__hint a { color: #315bd8; text-decoration: none; }
		.rasta-overview__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
		@media (max-width: 960px) { .rasta-overview__grid { grid-template-columns: 1fr; } }
		.rasta-overview__panel { background: #fff; border: 1px solid #dcdcde; border-radius: 12px; overflow: hidden; }
		.rasta-overview__panel-head { display: flex; align-items: center; gap: 9px; padding: 14px 18px; border-bottom: 1px solid #f0f0f1; background: #fbfbfc; }
		.rasta-overview__panel-head .dashicons { color: #50575e; }
		.rasta-overview__panel-head h2 { margin: 0; font-size: 14px; }
		.rasta-overview__panel-body { padding: 6px 18px 16px; }
		.rasta-overview__panel-body > p:first-of-type { margin: 12px 0 10px; color: #646970; }
		.rasta-overview__table { width: 100%; border-collapse: collapse; }
		.rasta-overview__table th, .rasta-overview__table td { text-align: right; padding: 10px 6px; border-bottom: 1px solid #f0f0f1; font-size: 13px; }
		.rasta-overview__table th { color: #50575e; font-weight: 600; }
		.rasta-overview__table tbody tr:last-child td { border-bottom: 0; }
		.rasta-overview__table tbody tr:hover td { background: #fafbfc; }
		.rasta-overview__empty { display: grid; place-items: center; gap: 6px; padding: 30px 16px; color: #8c8f94; text-align: center; }
		.rasta-overview__empty .dashicons { font-size: 34px; inline-size: 34px; block-size: 34px; color: #c3c4c7; }
		.rasta-status { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; white-space: nowrap; }
		.rasta-status--completed { background: #e9f6ef; color: #17865d; }
		.rasta-status--processing { background: #eef2ff; color: #315bd8; }
		.rasta-status--pending { background: #fdf3d8; color: #996800; }
		.rasta-status--cancelled { background: #fbeaea; color: #d63638; }
		.rasta-status--failed { background: #f6f7f7; color: #646970; }
		'
	);
}

/**
 * Return order statuses with their Persian labels.
 *
 * @return array<string, string> Status slug => label.
 */
function rasta_order_status_labels() {
	return array(
		'rasta-pending'    => __( 'در انتظار پرداخت', 'rasta-commerce' ),
		'rasta-processing' => __( 'در حال پردازش', 'rasta-commerce' ),
		'rasta-completed'  => __( 'تکمیل شده', 'rasta-commerce' ),
		'rasta-cancelled'  => __( 'لغو شده', 'rasta-commerce' ),
		'rasta-failed'     => __( 'ناموفق', 'rasta-commerce' ),
	);
}

/**
 * Render the store overview page.
 *
 * @return void
 */
function rasta_render_store_overview() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$product_count = wp_count_posts( 'rasta_product' );
	$published     = (int) ( $product_count->publish ?? 0 );

	$order_statuses = array_keys( rasta_order_status_labels() );
	$orders         = new WP_Query(
		array(
			'post_type'      => 'rasta_order',
			'post_status'    => $order_statuses,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	$order_count    = (int) $orders->post_count;
	$revenue        = 0;
	$completed      = 0;

	foreach ( $orders->posts as $order_id ) {
		$total = (float) get_post_meta( $order_id, '_rasta_order_total', true );
		$revenue += $total;

		if ( 'rasta-completed' === get_post_status( $order_id ) ) {
			$completed++;
		}
	}

	$low_stock_products = rasta_get_low_stock_products();
	$recent_orders      = rasta_get_recent_orders( 8 );

	$currency = get_theme_mod( 'rasta_currency', 'IRT' );
	$currency = 'IRR' === strtoupper( (string) $currency ) ? __( 'ریال', 'rasta-commerce' ) : __( 'تومان', 'rasta-commerce' );

	$stat_labels = array(
		'products'   => __( 'محصولات منتشرشده', 'rasta-commerce' ),
		'orders'     => __( 'کل سفارش‌ها', 'rasta-commerce' ),
		'completed'  => __( 'سفارش‌های تکمیل‌شده', 'rasta-commerce' ),
		'revenue'    => __( 'جمع مبلغ سفارش‌ها', 'rasta-commerce' ),
		'low_stock'  => __( 'محصولات کم‌موجود', 'rasta-commerce' ),
	);

	$completion_rate = $order_count > 0 ? (int) round( $completed / $order_count * 100 ) : 0;
	?>
	<div class="wrap rasta-overview">
		<div class="rasta-overview__header">
			<div>
				<h1 style="margin-bottom:4px;"><?php esc_html_e( 'نمای کلی فروشگاه', 'rasta-commerce' ); ?></h1>
				<p class="description" style="margin-top:0;"><?php esc_html_e( 'چشم‌اندازی زنده از محصولات، سفارش‌ها و موجودی فروشگاه.', 'rasta-commerce' ); ?></p>
			</div>
			<div class="rasta-overview__actions">
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=rasta_product' ) ); ?>">
					<span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e( 'افزودن محصول', 'rasta-commerce' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rasta_order' ) ); ?>">
					<span class="dashicons dashicons-cart"></span><?php esc_html_e( 'سفارش‌ها', 'rasta-commerce' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=rasta-store-settings' ) ); ?>">
					<span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'تنظیمات فروشگاه', 'rasta-commerce' ); ?>
				</a>
			</div>
		</div>

		<?php if ( function_exists( 'rasta_maintenance_enabled' ) && rasta_maintenance_enabled() ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<strong><?php esc_html_e( 'حالت تعمیر فعال است.', 'rasta-commerce' ); ?></strong>
					<?php esc_html_e( 'بازدیدکنندگان واردنشده صفحه «به‌زودی بازمی‌گردیم» را می‌بینند.', 'rasta-commerce' ); ?>
					<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=rasta_store_state' ) ); ?>"><?php esc_html_e( 'مدیریت وضعیت فروشگاه', 'rasta-commerce' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<div class="rasta-overview__cards">
			<div class="rasta-overview__card rasta-overview__card--accent">
				<div class="rasta-overview__top">
					<span class="rasta-overview__icon"><span class="dashicons dashicons-products"></span></span>
				</div>
				<p class="rasta-overview__label"><?php echo esc_html( $stat_labels['products'] ); ?></p>
				<div class="rasta-overview__value"><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $published ) ) ); ?></div>
				<p class="rasta-overview__hint"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rasta_product' ) ); ?>"><?php esc_html_e( 'مدیریت محصولات', 'rasta-commerce' ); ?> ←</a></p>
			</div>

			<div class="rasta-overview__card rasta-overview__card--blue">
				<div class="rasta-overview__top">
					<span class="rasta-overview__icon"><span class="dashicons dashicons-cart"></span></span>
				</div>
				<p class="rasta-overview__label"><?php echo esc_html( $stat_labels['orders'] ); ?></p>
				<div class="rasta-overview__value"><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $order_count ) ) ); ?></div>
				<p class="rasta-overview__hint"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rasta_order' ) ); ?>"><?php esc_html_e( 'مدیریت سفارش‌ها', 'rasta-commerce' ); ?> ←</a></p>
			</div>

			<div class="rasta-overview__card rasta-overview__card--green">
				<div class="rasta-overview__top">
					<span class="rasta-overview__icon"><span class="dashicons dashicons-yes-alt"></span></span>
				</div>
				<p class="rasta-overview__label"><?php echo esc_html( $stat_labels['completed'] ); ?></p>
				<div class="rasta-overview__value"><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $completed ) ) ); ?></div>
				<p class="rasta-overview__hint"><?php echo esc_html( sprintf( /* translators: %s: percentage. */ __( 'نرخ تکمیل: %s٪', 'rasta-commerce' ), rasta_to_persian_digits( (string) $completion_rate ) ) ); ?></p>
			</div>

			<div class="rasta-overview__card rasta-overview__card--gold">
				<div class="rasta-overview__top">
					<span class="rasta-overview__icon"><span class="dashicons dashicons-money-alt"></span></span>
				</div>
				<p class="rasta-overview__label"><?php echo esc_html( $stat_labels['revenue'] ); ?></p>
				<div class="rasta-overview__value"><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $revenue ) ) ); ?> <small><?php echo esc_html( $currency ); ?></small></div>
				<p class="rasta-overview__hint"><?php esc_html_e( 'جمع کل مبالغ ثبت‌شده', 'rasta-commerce' ); ?></p>
			</div>

			<div class="rasta-overview__card <?php echo $low_stock_products ? 'rasta-overview__card--red' : 'rasta-overview__card--green'; ?>">
				<div class="rasta-overview__top">
					<span class="rasta-overview__icon"><span class="dashicons dashicons-warning"></span></span>
				</div>
				<p class="rasta-overview__label"><?php echo esc_html( $stat_labels['low_stock'] ); ?></p>
				<div class="rasta-overview__value"><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( count( $low_stock_products ) ) ) ); ?></div>
				<p class="rasta-overview__hint"><?php echo $low_stock_products ? esc_html__( 'نیازمند بازبینی موجودی', 'rasta-commerce' ) : esc_html__( 'همه موجودی‌ها در وضعیت مطلوب‌اند', 'rasta-commerce' ); ?></p>
			</div>
		</div>

		<div class="rasta-overview__grid">
			<section class="rasta-overview__panel">
				<div class="rasta-overview__panel-head">
					<span class="dashicons dashicons-clock"></span>
					<h2><?php esc_html_e( 'آخرین سفارش‌ها', 'rasta-commerce' ); ?></h2>
				</div>
				<div class="rasta-overview__panel-body">
					<?php if ( empty( $recent_orders ) ) : ?>
						<div class="rasta-overview__empty">
							<span class="dashicons dashicons-cart"></span>
							<span><?php esc_html_e( 'هنوز سفارشی ثبت نشده است.', 'rasta-commerce' ); ?></span>
						</div>
					<?php else : ?>
						<table class="rasta-overview__table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'سفارش', 'rasta-commerce' ); ?></th>
									<th><?php esc_html_e( 'مبلغ', 'rasta-commerce' ); ?></th>
									<th><?php esc_html_e( 'وضعیت', 'rasta-commerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $recent_orders as $order ) : ?>
									<tr>
										<td><a href="<?php echo esc_url( get_edit_post_link( $order['id'] ) ); ?>"><?php echo esc_html( $order['title'] ); ?></a></td>
										<td><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $order['total'] ) ) ); ?> <?php echo esc_html( $currency ); ?></td>
										<td><span class="rasta-status rasta-status--<?php echo esc_attr( str_replace( 'rasta-', '', $order['status'] ) ); ?>"><?php echo esc_html( $order['status_label'] ); ?></span></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</section>

			<section class="rasta-overview__panel">
				<div class="rasta-overview__panel-head">
					<span class="dashicons dashicons-warning"></span>
					<h2><?php esc_html_e( 'هشدار موجودی', 'rasta-commerce' ); ?></h2>
				</div>
				<div class="rasta-overview__panel-body">
					<?php if ( empty( $low_stock_products ) ) : ?>
						<div class="rasta-overview__empty">
							<span class="dashicons dashicons-smiley"></span>
							<span><?php esc_html_e( 'هیچ محصول کم‌موجودی وجود ندارد. عالی است!', 'rasta-commerce' ); ?></span>
						</div>
					<?php else : ?>
						<table class="rasta-overview__table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'محصول', 'rasta-commerce' ); ?></th>
									<th><?php esc_html_e( 'موجودی', 'rasta-commerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $low_stock_products as $product ) : ?>
									<tr>
										<td><a href="<?php echo esc_url( get_edit_post_link( $product['id'] ) ); ?>"><?php echo esc_html( $product['title'] ); ?></a></td>
										<td><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $product['stock'] ) ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</section>
		</div>
	</div>
	<?php
}

/**
 * Fetch products at or below the low-stock threshold.
 *
 * @param int $limit Maximum number of rows to return.
 * @return array<int, array{id: int, title: string, stock: int}>
 */
function rasta_get_low_stock_products( $limit = 20 ) {
	$threshold = (int) get_theme_mod( 'rasta_low_stock_threshold', 3 );

	if ( $threshold <= 0 ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'rasta_product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'     => '_rasta_manage_stock',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => '_rasta_stock_quantity',
					'value'   => $threshold,
					'type'    => 'NUMERIC',
					'compare' => '<=',
				),
			),
		)
	);

	$products = array();

	foreach ( $query->posts as $product_id ) {
		$products[] = array(
			'id'    => (int) $product_id,
			'title' => get_the_title( $product_id ),
			'stock' => (int) get_post_meta( $product_id, '_rasta_stock_quantity', true ),
		);
	}

	return $products;
}

/**
 * Fetch the most recent orders with totals and status labels.
 *
 * @param int $limit Maximum number of rows to return.
 * @return array<int, array{id: int, title: string, total: float, status: string, status_label: string}>
 */
function rasta_get_recent_orders( $limit = 8 ) {
	$labels = rasta_order_status_labels();
	$query  = new WP_Query(
		array(
			'post_type'      => 'rasta_order',
			'post_status'    => array_keys( $labels ),
			'posts_per_page' => $limit,
			'no_found_rows'  => true,
		)
	);

	$orders = array();

	foreach ( $query->posts as $order ) {
		$status = get_post_status( $order );
		$orders[] = array(
			'id'           => $order->ID,
			'title'        => $order->post_title,
			'total'        => (float) get_post_meta( $order->ID, '_rasta_order_total', true ),
			'status'       => $status,
			'status_label' => $labels[ $status ] ?? $status,
		);
	}

	return $orders;
}

/* ─── Product list columns ─────────────────────────────────────────────── */

/**
 * Register custom columns for the rasta_product list table.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function rasta_product_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['rasta_sku']   = esc_html__( 'کد محصول', 'rasta-commerce' );
			$new['rasta_price'] = esc_html__( 'قیمت', 'rasta-commerce' );
			$new['rasta_stock'] = esc_html__( 'موجودی', 'rasta-commerce' );
		}
	}

	return $new;
}
add_filter( 'manage_rasta_product_posts_columns', 'rasta_product_columns' );

/**
 * Render custom column content for the rasta_product list table.
 *
 * @param string $column  Column key.
 * @param int    $post_id Current post ID.
 * @return void
 */
function rasta_product_column_content( $column, $post_id ) {
	$price     = (float) get_post_meta( $post_id, '_rasta_price', true );
	$sale      = (float) get_post_meta( $post_id, '_rasta_sale_price', true );
	$sku       = (string) get_post_meta( $post_id, '_rasta_sku', true );
	$manage    = (bool) get_post_meta( $post_id, '_rasta_manage_stock', true );
	$stock     = (int) get_post_meta( $post_id, '_rasta_stock_quantity', true );
	$status    = (string) get_post_meta( $post_id, '_rasta_stock_status', true ) ?: 'instock';
	$threshold = (int) get_theme_mod( 'rasta_low_stock_threshold', 3 );

	switch ( $column ) {
		case 'rasta_sku':
			echo $sku ? esc_html( $sku ) : '<span aria-hidden="true">—</span>';
			break;

		case 'rasta_price':
			if ( $sale > 0 && $sale < $price ) {
				printf(
					'<span style="color:#d63638;font-weight:600;">%s</span> <del style="color:#8c8f94;">%s</del>',
					esc_html( rasta_to_persian_digits( number_format_i18n( $sale ) ) ),
					esc_html( rasta_to_persian_digits( number_format_i18n( $price ) ) )
				);
			} elseif ( $price > 0 ) {
				echo esc_html( rasta_to_persian_digits( number_format_i18n( $price ) ) );
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			break;

		case 'rasta_stock':
			if ( ! $manage ) {
				echo '<span aria-hidden="true">—</span>';
				break;
			}

			if ( $stock <= 0 || 'outofstock' === $status ) {
				echo '<span style="color:#d63638;font-weight:600;">' . esc_html__( 'ناموجود', 'rasta-commerce' ) . '</span>';
			} elseif ( $threshold > 0 && $stock <= $threshold ) {
				echo '<span style="color:#996800;font-weight:600;">' . esc_html( rasta_to_persian_digits( number_format_i18n( $stock ) ) ) . '</span>';
			} else {
				echo esc_html( rasta_to_persian_digits( number_format_i18n( $stock ) ) );
			}
			break;
	}
}
add_action( 'manage_rasta_product_posts_custom_column', 'rasta_product_column_content', 10, 2 );

/**
 * Register a low-stock quick filter on the product list.
 *
 * @return void
 */
function rasta_product_list_filters() {
	global $typenow;

	if ( 'rasta_product' !== $typenow ) {
		return;
	}

	$current = isset( $_GET['rasta_stock_filter'] ) ? sanitize_key( wp_unslash( $_GET['rasta_stock_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	echo '<select name="rasta_stock_filter" id="rasta-stock-filter">';
	echo '<option value="">' . esc_html__( 'همه وضعیت‌های موجودی', 'rasta-commerce' ) . '</option>';
	echo '<option value="low"' . selected( $current, 'low', false ) . '>' . esc_html__( 'موجودی کم', 'rasta-commerce' ) . '</option>';
	echo '<option value="out"' . selected( $current, 'out', false ) . '>' . esc_html__( 'ناموجود', 'rasta-commerce' ) . '</option>';
	echo '</select>';

	wp_nonce_field( 'rasta_stock_filter', 'rasta_stock_filter_nonce' );
}
add_action( 'restrict_manage_posts', 'rasta_product_list_filters' );

/**
 * Apply the low-stock quick filter to the product query.
 *
 * @param WP_Query $query Current query.
 * @return void
 */
function rasta_product_list_filter_query( $query ) {
	global $pagenow, $typenow;

	if ( 'edit.php' !== $pagenow || 'rasta_product' !== $typenow || ! $query->is_main_query() ) {
		return;
	}

	if ( ! isset( $_GET['rasta_stock_filter'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$filter = sanitize_key( wp_unslash( $_GET['rasta_stock_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$query->set(
		'meta_query',
		array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'AND',
			array(
				'key'     => '_rasta_manage_stock',
				'value'   => '1',
				'compare' => '=',
			),
			'low' === $filter
				? array(
					'key'     => '_rasta_stock_quantity',
					'value'   => max( 1, (int) get_theme_mod( 'rasta_low_stock_threshold', 3 ) ),
					'type'    => 'NUMERIC',
					'compare' => '<=',
				)
				: array(
					'key'     => '_rasta_stock_quantity',
					'value'   => 0,
					'type'    => 'NUMERIC',
					'compare' => '<=',
				),
		)
	);
}
add_action( 'pre_get_posts', 'rasta_product_list_filter_query' );
