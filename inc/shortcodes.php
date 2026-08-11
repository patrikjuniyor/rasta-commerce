<?php
/**
 * Shortcodes for cart, checkout, and account pages.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register built-in shortcodes.
 *
 * @return void
 */
function rasta_register_shortcodes() {
	add_shortcode( 'rasta_cart', 'rasta_cart_shortcode' );
	add_shortcode( 'rasta_checkout', 'rasta_checkout_shortcode' );
	add_shortcode( 'rasta_account', 'rasta_account_shortcode' );
}
add_action( 'init', 'rasta_register_shortcodes' );

/**
 * Render the cart page.
 *
 * @return string
 */
function rasta_cart_shortcode() {
	if ( rasta_using_woocommerce() && function_exists( 'wc_get_cart_url' ) ) {
		return ''; /* Let WooCommerce handle it. */
	}

	ob_start();
	$items = rasta_get_cart_items();
	?>
	<div class="rasta-cart-page">
		<h1><?php esc_html_e( 'سبد خرید', 'rasta-commerce' ); ?></h1>

		<?php if ( empty( $items ) ) : ?>
			<div class="rasta-empty-state">
				<?php rasta_icon( 'cart' ); ?>
				<p><?php esc_html_e( 'سبد خرید شما خالی است.', 'rasta-commerce' ); ?></p>
				<a class="rasta-button" href="<?php echo esc_url( rasta_get_shop_url() ); ?>">
					<?php esc_html_e( 'بازگشت به فروشگاه', 'rasta-commerce' ); ?>
				</a>
			</div>
		<?php else : ?>
			<table class="rasta-cart-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'محصول', 'rasta-commerce' ); ?></th>
						<th><?php esc_html_e( 'قیمت واحد', 'rasta-commerce' ); ?></th>
						<th><?php esc_html_e( 'تعداد', 'rasta-commerce' ); ?></th>
						<th><?php esc_html_e( 'جمع', 'rasta-commerce' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr data-cart-item="<?php echo esc_attr( $item['product']['id'] ); ?>">
							<td class="rasta-cart-table__product">
								<?php if ( ! empty( $item['product']['image'] ) ) : ?>
									<img src="<?php echo esc_url( $item['product']['image'] ); ?>" alt="" class="rasta-cart-table__thumb" />
								<?php endif; ?>
								<a href="<?php echo esc_url( $item['product']['url'] ); ?>"><?php echo esc_html( $item['product']['name'] ); ?></a>
							</td>
							<td><?php echo wp_kses_post( $item['product']['price'] ); ?></td>
							<td>
								<div class="rasta-quantity-selector">
									<button type="button" class="rasta-qty-btn" data-cart-update="<?php echo esc_attr( $item['product']['id'] ); ?>" data-qty="<?php echo esc_attr( max( 1, $item['quantity'] - 1 ) ); ?>">−</button>
									<span class="rasta-qty-display"><?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $item['quantity'] ) ) ); ?></span>
									<button type="button" class="rasta-qty-btn" data-cart-update="<?php echo esc_attr( $item['product']['id'] ); ?>" data-qty="<?php echo esc_attr( $item['quantity'] + 1 ); ?>">+</button>
								</div>
							</td>
							<td><?php echo wp_kses_post( rasta_format_currency( $item['lineTotal'] ) ); ?></td>
							<td>
								<button type="button" class="rasta-icon-button rasta-cart-remove" data-remove-cart-item="<?php echo esc_attr( $item['product']['id'] ); ?>" aria-label="<?php esc_attr_e( 'حذف', 'rasta-commerce' ); ?>">
									<?php rasta_icon( 'trash' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="rasta-cart-summary">
				<div class="rasta-cart-summary__row rasta-cart-summary__total">
					<span><?php esc_html_e( 'جمع کل:', 'rasta-commerce' ); ?></span>
					<span data-cart-subtotal><?php echo wp_kses_post( rasta_format_currency( rasta_get_cart_subtotal() ) ); ?></span>
				</div>
				<?php rasta_render_free_shipping_progress(); ?>
				<div class="rasta-cart-summary__actions">
					<a class="rasta-button rasta-button--outline" href="<?php echo esc_url( rasta_get_shop_url() ); ?>">
						<?php esc_html_e( 'ادامه خرید', 'rasta-commerce' ); ?>
					</a>
					<a class="rasta-button rasta-button--large" href="<?php echo esc_url( rasta_get_checkout_url() ); ?>">
						<?php esc_html_e( 'تکمیل خرید', 'rasta-commerce' ); ?>
						<?php rasta_icon( 'arrow-left' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render the checkout page.
 *
 * @return string
 */
function rasta_checkout_shortcode() {
	if ( rasta_using_woocommerce() && function_exists( 'wc_get_checkout_url' ) ) {
		return ''; /* Let WooCommerce handle it. */
	}

	if ( rasta_cart_is_empty() ) {
		return '<div class="rasta-empty-state">'
			. '<p>' . esc_html__( 'سبد خرید شما خالی است.', 'rasta-commerce' ) . '</p>'
			. '<a class="rasta-button" href="' . esc_url( rasta_get_shop_url() ) . '">' . esc_html__( 'بازگشت به فروشگاه', 'rasta-commerce' ) . '</a>'
			. '</div>';
	}

	ob_start();
	$items  = rasta_get_cart_items();
	$totals = rasta_get_cart_subtotal();
	?>
	<div class="rasta-checkout-page">
		<h1><?php esc_html_e( 'تکمیل خرید', 'rasta-commerce' ); ?></h1>

		<form class="rasta-checkout-form" data-checkout-form>
			<?php wp_nonce_field( 'rasta_checkout', 'rasta_checkout_nonce' ); ?>

			<div class="rasta-checkout-grid">
				<div class="rasta-checkout-form__fields">
					<h2><?php esc_html_e( 'اطلاعات خریدار', 'rasta-commerce' ); ?></h2>

					<div class="rasta-form-row">
						<div class="rasta-form-field">
							<label for="rasta_first_name"><?php esc_html_e( 'نام', 'rasta-commerce' ); ?> <span class="required">*</span></label>
							<input type="text" id="rasta_first_name" name="first_name" required autocomplete="given-name" />
						</div>
						<div class="rasta-form-field">
							<label for="rasta_last_name"><?php esc_html_e( 'نام خانوادگی', 'rasta-commerce' ); ?> <span class="required">*</span></label>
							<input type="text" id="rasta_last_name" name="last_name" required autocomplete="family-name" />
						</div>
					</div>

					<div class="rasta-form-row">
						<div class="rasta-form-field">
							<label for="rasta_email"><?php esc_html_e( 'ایمیل', 'rasta-commerce' ); ?> <span class="required">*</span></label>
							<input type="email" id="rasta_email" name="email" required autocomplete="email" />
						</div>
						<div class="rasta-form-field">
							<label for="rasta_phone"><?php esc_html_e( 'شماره موبایل', 'rasta-commerce' ); ?> <span class="required">*</span></label>
							<input type="tel" id="rasta_phone" name="phone" required autocomplete="tel" placeholder="۰۹۱۲۳۴۵۶۷۸۹" />
						</div>
					</div>

					<div class="rasta-form-field">
						<label for="rasta_address"><?php esc_html_e( 'نشانی کامل', 'rasta-commerce' ); ?> <span class="required">*</span></label>
						<textarea id="rasta_address" name="address" rows="3" required autocomplete="street-address"></textarea>
					</div>

					<div class="rasta-form-row">
						<div class="rasta-form-field">
							<label for="rasta_city"><?php esc_html_e( 'شهر', 'rasta-commerce' ); ?> <span class="required">*</span></label>
							<input type="text" id="rasta_city" name="city" required />
						</div>
						<div class="rasta-form-field">
							<label for="rasta_province"><?php esc_html_e( 'استان', 'rasta-commerce' ); ?></label>
							<input type="text" id="rasta_province" name="province" />
						</div>
					</div>

					<div class="rasta-form-field">
						<label for="rasta_postcode"><?php esc_html_e( 'کد پستی', 'rasta-commerce' ); ?></label>
						<input type="text" id="rasta_postcode" name="postcode" />
					</div>

					<div class="rasta-form-field">
						<label for="rasta_notes"><?php esc_html_e( 'توضیحات سفارش (اختیاری)', 'rasta-commerce' ); ?></label>
						<textarea id="rasta_notes" name="notes" rows="2"></textarea>
					</div>
				</div>

				<div class="rasta-checkout-order">
					<h2><?php esc_html_e( 'خلاصه سفارش', 'rasta-commerce' ); ?></h2>
					<ul class="rasta-checkout-items">
						<?php foreach ( $items as $item ) : ?>
							<li>
								<span><?php echo esc_html( $item['product']['name'] ); ?>
									<em>× <?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $item['quantity'] ) ) ); ?></em>
								</span>
								<span><?php echo wp_kses_post( rasta_format_currency( $item['lineTotal'] ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="rasta-checkout-total">
						<span><?php esc_html_e( 'جمع کل:', 'rasta-commerce' ); ?></span>
						<strong><?php echo wp_kses_post( rasta_format_currency( $totals ) ); ?></strong>
					</div>

					<div class="rasta-checkout-payment">
						<h3><?php esc_html_e( 'روش پرداخت', 'rasta-commerce' ); ?></h3>
						<label class="rasta-payment-option">
							<input type="radio" name="payment_method" value="cod" checked />
							<span><?php esc_html_e( 'پرداخت در محل', 'rasta-commerce' ); ?></span>
						</label>
						<label class="rasta-payment-option">
							<input type="radio" name="payment_method" value="online" />
							<span><?php esc_html_e( 'پرداخت آنلاین', 'rasta-commerce' ); ?></span>
						</label>
					</div>

					<button type="submit" class="rasta-button rasta-button--large rasta-button--full" data-submit-order>
						<?php rasta_icon( 'check' ); ?>
						<?php esc_html_e( 'ثبت و پرداخت سفارش', 'rasta-commerce' ); ?>
					</button>
				</div>
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render the account page.
 *
 * @return string
 */
function rasta_account_shortcode() {
	if ( ! is_user_logged_in() ) {
		ob_start();
		?>
		<div class="rasta-account-page">
			<h1><?php esc_html_e( 'حساب کاربری', 'rasta-commerce' ); ?></h1>
			<p><?php esc_html_e( 'برای مشاهده سفارش‌ها و حساب کاربری، وارد شوید.', 'rasta-commerce' ); ?></p>
			<a class="rasta-button" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
				<?php esc_html_e( 'ورود', 'rasta-commerce' ); ?>
			</a>
		</div>
		<?php
		return ob_get_clean();
	}

	/* Show user orders. */
	$orders = get_posts(
		array(
			'post_type'   => 'rasta_order',
			'post_status' => array( 'rasta-pending', 'rasta-processing', 'rasta-completed', 'rasta-cancelled', 'rasta-failed' ),
			'author'      => get_current_user_id(),
			'orderby'     => 'date',
			'order'       => 'DESC',
			'numberposts' => 20,
		)
	);

	ob_start();
	?>
	<div class="rasta-account-page">
		<h1><?php esc_html_e( 'حساب کاربری', 'rasta-commerce' ); ?></h1>
		<p><?php printf( esc_html__( 'سلام %s!', 'rasta-commerce' ), esc_html( wp_get_current_user()->display_name ) ); ?></p>

		<h2><?php esc_html_e( 'سفارش‌های شما', 'rasta-commerce' ); ?></h2>
		<?php if ( empty( $orders ) ) : ?>
			<p><?php esc_html_e( 'هنوز سفارشی ثبت نکرده‌اید.', 'rasta-commerce' ); ?></p>
		<?php else : ?>
			<table class="rasta-orders-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'شماره سفارش', 'rasta-commerce' ); ?></th>
						<th><?php esc_html_e( 'تاریخ', 'rasta-commerce' ); ?></th>
						<th><?php esc_html_e( 'وضعیت', 'rasta-commerce' ); ?></th>
						<th><?php esc_html_e( 'مبلغ', 'rasta-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $orders as $order ) : ?>
						<?php $total = get_post_meta( $order->ID, '_rasta_order_total', true ); ?>
						<tr>
							<td>#<?php echo esc_html( rasta_to_persian_digits( number_format_i18n( $order->ID ) ) ); ?></td>
							<td><?php echo esc_html( rasta_jalali_date( 'j F Y', get_post_time( 'U', true, $order->ID ) ) ); ?></td>
							<td><?php echo esc_html( get_post_status_object( $order->post_status )->label ?? $order->post_status ); ?></td>
							<td><?php echo wp_kses_post( rasta_format_currency( (float) $total ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<p><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'خروج از حساب', 'rasta-commerce' ); ?></a></p>
	</div>
	<?php
	return ob_get_clean();
}

/* ─── AJAX checkout handler ────────────────────────────────────────────── */

/**
 * Process checkout form submission via AJAX.
 *
 * @return void
 */
function rasta_ajax_process_checkout() {
	check_ajax_referer( 'rasta_checkout', 'nonce' );

	if ( rasta_cart_is_empty() ) {
		wp_send_json_error( array( 'message' => esc_html__( 'سبد خرید خالی است.', 'rasta-commerce' ) ), 400 );
	}

	$customer_data = array(
		'first_name' => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
		'last_name'  => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
		'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'phone'      => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
		'address'    => isset( $_POST['address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['address'] ) ) : '',
		'city'       => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
		'province'   => isset( $_POST['province'] ) ? sanitize_text_field( wp_unslash( $_POST['province'] ) ) : '',
		'postcode'   => isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '',
		'notes'      => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
	);

	/* Validate required fields. */
	$required = array( 'first_name', 'last_name', 'email', 'phone', 'address', 'city' );
	foreach ( $required as $field ) {
		if ( empty( $customer_data[ $field ] ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %s: field name. */
						esc_html__( 'لطفاً فیلد «%s» را پر کنید.', 'rasta-commerce' ),
						$field
					),
				),
				400
			);
		}
	}

	$order_id = rasta_create_order( $customer_data );

	if ( is_wp_error( $order_id ) ) {
		wp_send_json_error( array( 'message' => $order_id->get_error_message() ), 500 );
	}

	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : 'cod';

	/* Update status based on payment method. */
	if ( 'cod' === $payment_method ) {
		rasta_update_order_status( $order_id, 'rasta-processing' );
	} else {
		rasta_update_order_status( $order_id, 'rasta-pending' );
	}

	wp_send_json_success(
		array(
			'message'  => esc_html__( 'سفارش شما با موفقیت ثبت شد.', 'rasta-commerce' ),
			'order_id' => $order_id,
			'redirect' => add_query_arg( 'order', $order_id, home_url( '/order-received/' ) ),
		)
	);
}
add_action( 'wp_ajax_rasta_checkout', 'rasta_ajax_process_checkout' );
add_action( 'wp_ajax_nopriv_rasta_checkout', 'rasta_ajax_process_checkout' );
