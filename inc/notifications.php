<?php
/**
 * Order notification emails for the built-in store.
 *
 * Sends a Persian HTML email to the store manager whenever a new order is
 * created, using the settings configured on the store settings page.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send the admin order notification email.
 *
 * Hooked to the `rasta_order_created` action fired in rasta_create_order().
 *
 * @param int $order_id Order post ID.
 * @return void
 */
function rasta_send_admin_order_email( $order_id ) {
	$settings = rasta_store_settings();

	if ( empty( $settings['order_emails'] ) ) {
		return;
	}

	$to = $settings['admin_email'] ? $settings['admin_email'] : get_option( 'admin_email' );

	if ( ! is_email( $to ) ) {
		return;
	}

	$subject = $settings['email_subject'] ? $settings['email_subject'] : __( 'سفارش جدید در فروشگاه', 'rasta-commerce' );
	$subject = sprintf( '%s — %s', $subject, rasta_to_persian_digits( (string) $order_id ) );

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	wp_mail( $to, $subject, rasta_build_order_email_html( $order_id ), $headers );
}
add_action( 'rasta_order_created', 'rasta_send_admin_order_email' );

/**
 * Build the HTML body for the order notification email.
 *
 * @param int $order_id Order post ID.
 * @return string
 */
function rasta_build_order_email_html( $order_id ) {
	$items      = (array) get_post_meta( $order_id, '_rasta_order_items', true );
	$total      = (float) get_post_meta( $order_id, '_rasta_order_total', true );
	$first_name = (string) get_post_meta( $order_id, '_rasta_customer_first_name', true );
	$last_name  = (string) get_post_meta( $order_id, '_rasta_customer_last_name', true );
	$phone      = (string) get_post_meta( $order_id, '_rasta_customer_phone', true );
	$email      = (string) get_post_meta( $order_id, '_rasta_customer_email', true );
	$city       = (string) get_post_meta( $order_id, '_rasta_customer_city', true );
	$province   = (string) get_post_meta( $order_id, '_rasta_customer_province', true );
	$address    = (string) get_post_meta( $order_id, '_rasta_customer_address', true );
	$postcode   = (string) get_post_meta( $order_id, '_rasta_customer_postcode', true );
	$notes      = (string) get_post_meta( $order_id, '_rasta_customer_notes', true );

	$customer_name = trim( $first_name . ' ' . $last_name );
	$accent        = rasta_sanitize_brand_color( get_theme_mod( 'rasta_accent_color', '#f25c54' ) );
	$accent        = $accent ? $accent : '#f25c54';

	$rows = '';
	foreach ( $items as $item ) {
		$product = isset( $item['product'] ) && is_array( $item['product'] ) ? $item['product'] : array();
		$name    = isset( $product['name'] ) ? $product['name'] : __( 'محصول', 'rasta-commerce' );
		$qty     = isset( $item['quantity'] ) ? (int) $item['quantity'] : 1;
		$line    = isset( $item['lineTotal'] ) ? (float) $item['lineTotal'] : 0;

		$rows .= '<tr>'
			. '<td style="padding:10px 12px;border-bottom:1px solid #eceef2;">' . esc_html( $name ) . '</td>'
			. '<td style="padding:10px 12px;border-bottom:1px solid #eceef2;text-align:center;">' . esc_html( rasta_to_persian_digits( (string) $qty ) ) . '</td>'
			. '<td style="padding:10px 12px;border-bottom:1px solid #eceef2;text-align:left;white-space:nowrap;">' . esc_html( rasta_format_currency_plain( $line ) ) . '</td>'
			. '</tr>';
	}

	if ( '' === $rows ) {
		$rows = '<tr><td colspan="3" style="padding:14px;text-align:center;color:#667085;">' . esc_html__( 'جزئیات اقلام در دسترس نیست.', 'rasta-commerce' ) . '</td></tr>';
	}

	$total_cell = esc_html( rasta_format_currency_plain( $total ) );

	$html  = '<!doctype html><html dir="rtl" lang="fa"><body style="margin:0;padding:0;background:#f4f6fa;font-family:Tahoma,Arial,sans-serif;color:#182033;">';
	$html .= '<div style="max-width:600px;margin:0 auto;padding:24px;">';
	$html .= '<div style="background:' . esc_attr( $accent ) . ';color:#fff;padding:20px 24px;border-radius:14px 14px 0 0;font-size:18px;font-weight:bold;">' . esc_html( get_bloginfo( 'name' ) ) . '</div>';
	$html .= '<div style="background:#fff;padding:24px;border-radius:0 0 14px 14px;">';
	$html .= '<h1 style="margin:0 0 16px;font-size:19px;">' . esc_html( sprintf( /* translators: %s: order number. */ __( 'سفارش جدید ثبت شد — شماره %s', 'rasta-commerce' ), rasta_to_persian_digits( (string) $order_id ) ) ) . '</h1>';

	$html .= '<h2 style="margin:0 0 10px;font-size:15px;color:#5f687a;">' . esc_html__( 'مشخصات مشتری', 'rasta-commerce' ) . '</h2>';
	$html .= '<table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:22px;">';
	if ( $customer_name ) {
		$html .= '<tr><td style="padding:4px 0;color:#8490a4;width:110px;">' . esc_html__( 'نام', 'rasta-commerce' ) . '</td><td>' . esc_html( $customer_name ) . '</td></tr>';
	}
	if ( $phone ) {
		$html .= '<tr><td style="padding:4px 0;color:#8490a4;">' . esc_html__( 'تلفن', 'rasta-commerce' ) . '</td><td>' . esc_html( $phone ) . '</td></tr>';
	}
	if ( $email ) {
		$html .= '<tr><td style="padding:4px 0;color:#8490a4;">' . esc_html__( 'ایمیل', 'rasta-commerce' ) . '</td><td>' . esc_html( $email ) . '</td></tr>';
	}
	if ( $city || $province ) {
		$html .= '<tr><td style="padding:4px 0;color:#8490a4;">' . esc_html__( 'شهر/استان', 'rasta-commerce' ) . '</td><td>' . esc_html( trim( $province . ' ' . $city ) ) . '</td></tr>';
	}
	if ( $address ) {
		$html .= '<tr><td style="padding:4px 0;color:#8490a4;">' . esc_html__( 'نشانی', 'rasta-commerce' ) . '</td><td>' . esc_html( $address ) . '</td></tr>';
	}
	if ( $postcode ) {
		$html .= '<tr><td style="padding:4px 0;color:#8490a4;">' . esc_html__( 'کد پستی', 'rasta-commerce' ) . '</td><td>' . esc_html( $postcode ) . '</td></tr>';
	}
	if ( $notes ) {
		$html .= '<tr><td style="padding:4px 0;color:#8490a4;">' . esc_html__( 'توضیحات', 'rasta-commerce' ) . '</td><td>' . esc_html( $notes ) . '</td></tr>';
	}
	$html .= '</table>';

	$html .= '<h2 style="margin:0 0 10px;font-size:15px;color:#5f687a;">' . esc_html__( 'اقلام سفارش', 'rasta-commerce' ) . '</h2>';
	$html .= '<table style="width:100%;border-collapse:collapse;font-size:14px;">';
	$html .= '<thead><tr style="background:#f7f8fb;color:#5f687a;">';
	$html .= '<th style="padding:10px 12px;text-align:right;">' . esc_html__( 'محصول', 'rasta-commerce' ) . '</th>';
	$html .= '<th style="padding:10px 12px;text-align:center;">' . esc_html__( 'تعداد', 'rasta-commerce' ) . '</th>';
	$html .= '<th style="padding:10px 12px;text-align:left;">' . esc_html__( 'مبلغ', 'rasta-commerce' ) . '</th>';
	$html .= '</tr></thead><tbody>';
	$html .= $rows;
	$html .= '</tbody></table>';

	$html .= '<div style="margin-top:18px;padding-top:14px;border-top:2px solid #eceef2;text-align:left;font-size:16px;">';
	$html .= '<strong>' . esc_html__( 'جمع کل: ', 'rasta-commerce' ) . '</strong>' . $total_cell;
	$html .= '</div>';

	$html .= '<p style="margin:22px 0 0;color:#8490a4;font-size:12px;line-height:1.9;">' . esc_html__( 'این ایمیل به‌صورت خودکار از فروشگاه شما ارسال شده است.', 'rasta-commerce' ) . '</p>';
	$html .= '</div></div></body></html>';

	return $html;
}
