<?php
/**
 * ZarinPal v4 payment gateway implementation.
 *
 * @package Rasta_Zarinpal_Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provide ZarinPal's hosted checkout to WooCommerce.
 */
class WC_Gateway_Rasta_Zarinpal extends WC_Payment_Gateway {

	/**
	 * ZarinPal payment-request API path.
	 *
	 * @var string
	 */
	private const REQUEST_PATH = '/pg/v4/payment/request.json';

	/**
	 * ZarinPal payment-verification API path.
	 *
	 * @var string
	 */
	private const VERIFY_PATH = '/pg/v4/payment/verify.json';

	/**
	 * Merchant ID supplied by ZarinPal.
	 *
	 * @var string
	 */
	private $merchant_id;

	/**
	 * Whether the sandbox host is used.
	 *
	 * @var bool
	 */
	private $sandbox;

	/**
	 * Currency sent to ZarinPal.
	 *
	 * @var string
	 */
	private $zarinpal_currency;

	/**
	 * Whether diagnostic logging is enabled.
	 *
	 * @var bool
	 */
	private $debug;

	/**
	 * Set up gateway properties and callback handler.
	 */
	public function __construct() {
		$this->id                 = 'rasta_zarinpal';
		$this->method_title       = esc_html__( 'زرین‌پال', 'rasta-zarinpal-gateway' );
		$this->method_description = esc_html__( 'پرداخت امن از طریق درگاه زرین‌پال API v4.', 'rasta-zarinpal-gateway' );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title               = $this->get_option( 'title', esc_html__( 'پرداخت امن زرین‌پال', 'rasta-zarinpal-gateway' ) );
		$this->description         = $this->get_option( 'description', esc_html__( 'پرداخت با تمام کارت‌های عضو شبکه شتاب از طریق زرین‌پال.', 'rasta-zarinpal-gateway' ) );
		$this->merchant_id         = trim( (string) $this->get_option( 'merchant_id', '' ) );
		$this->sandbox             = 'yes' === $this->get_option( 'sandbox', 'no' );
		$this->zarinpal_currency   = $this->get_option( 'currency', 'auto' );
		$this->debug               = 'yes' === $this->get_option( 'debug', 'no' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	/**
	 * Declare merchant-facing gateway settings.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => esc_html__( 'فعال‌سازی', 'rasta-zarinpal-gateway' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'فعال‌سازی پرداخت با زرین‌پال', 'rasta-zarinpal-gateway' ),
				'default' => 'no',
			),
			'title' => array(
				'title'       => esc_html__( 'عنوان درگاه', 'rasta-zarinpal-gateway' ),
				'type'        => 'text',
				'default'     => esc_html__( 'پرداخت امن زرین‌پال', 'rasta-zarinpal-gateway' ),
				'desc_tip'    => true,
				'description' => esc_html__( 'عنوانی که مشتری در صفحه پرداخت می‌بیند.', 'rasta-zarinpal-gateway' ),
			),
			'description' => array(
				'title'       => esc_html__( 'توضیح', 'rasta-zarinpal-gateway' ),
				'type'        => 'textarea',
				'default'     => esc_html__( 'پرداخت با تمام کارت‌های عضو شبکه شتاب از طریق زرین‌پال.', 'rasta-zarinpal-gateway' ),
				'desc_tip'    => true,
				'description' => esc_html__( 'توضیحی که زیر عنوان درگاه نمایش داده می‌شود.', 'rasta-zarinpal-gateway' ),
			),
			'merchant_id' => array(
				'title'       => esc_html__( 'Merchant ID', 'rasta-zarinpal-gateway' ),
				'type'        => 'text',
				'default'     => '',
				'desc_tip'    => true,
				'description' => esc_html__( 'شناسه ۳۶ کاراکتری که زرین‌پال برای پذیرنده صادر کرده است. این مقدار هرگز در سمت مشتری چاپ نمی‌شود.', 'rasta-zarinpal-gateway' ),
			),
			'currency' => array(
				'title'       => esc_html__( 'واحد مبلغ API', 'rasta-zarinpal-gateway' ),
				'type'        => 'select',
				'default'     => 'auto',
				'options'     => array(
					'auto' => esc_html__( 'خودکار بر اساس ارز فروشگاه', 'rasta-zarinpal-gateway' ),
					'IRR'  => esc_html__( 'ریال (IRR)', 'rasta-zarinpal-gateway' ),
					'IRT'  => esc_html__( 'تومان (IRT)', 'rasta-zarinpal-gateway' ),
				),
				'desc_tip'    => true,
				'description' => esc_html__( 'اگر ارز فروشگاه و واحد انتخابی متفاوت باشند، افزونه تبدیل ۱۰ برابری ریال/تومان را انجام می‌دهد.', 'rasta-zarinpal-gateway' ),
			),
			'sandbox' => array(
				'title'       => esc_html__( 'Sandbox', 'rasta-zarinpal-gateway' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'استفاده از محیط آزمایشی زرین‌پال', 'rasta-zarinpal-gateway' ),
				'default'     => 'no',
				'description' => esc_html__( 'برای تست بدون پرداخت واقعی. در sandbox می‌توان از یک UUID دلخواه به‌عنوان Merchant ID استفاده کرد.', 'rasta-zarinpal-gateway' ),
			),
			'debug' => array(
				'title'       => esc_html__( 'گزارش فنی', 'rasta-zarinpal-gateway' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'ثبت خطاهای فنی در WooCommerce → Status → Logs', 'rasta-zarinpal-gateway' ),
				'default'     => 'no',
				'description' => esc_html__( 'فقط هنگام عیب‌یابی موقت فعال کنید؛ شماره کارت و داده حساس پرداخت ثبت نمی‌شود.', 'rasta-zarinpal-gateway' ),
			),
		);
	}

	/**
	 * Keep merchant ID settings free from markup and surrounding spaces.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Submitted value.
	 * @return string
	 */
	public function validate_merchant_id_field( $key, $value ) {
		return trim( sanitize_text_field( wp_unslash( $value ) ) );
	}

	/**
	 * Only expose the gateway when its required configuration is valid.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ! parent::is_available() || ! $this->merchant_id ) {
			return false;
		}

		return in_array( get_woocommerce_currency(), array( 'IRR', 'IRT' ), true );
	}

	/**
	 * Start the ZarinPal payment request and redirect the shopper to StartPay.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return array<string, string>|null
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			wc_add_notice( esc_html__( 'سفارش معتبر نیست. لطفاً دوباره تلاش کنید.', 'rasta-zarinpal-gateway' ), 'error' );
			return null;
		}

		if ( ! $this->merchant_id ) {
			wc_add_notice( esc_html__( 'Merchant ID زرین‌پال هنوز تنظیم نشده است.', 'rasta-zarinpal-gateway' ), 'error' );
			return null;
		}

		$amount = $this->get_gateway_amount( $order );
		if ( $amount < 1 ) {
			wc_add_notice( esc_html__( 'مبلغ سفارش برای ارسال به زرین‌پال معتبر نیست.', 'rasta-zarinpal-gateway' ), 'error' );
			return null;
		}

		$payload  = array(
			'merchant_id'  => $this->merchant_id,
			'amount'       => $amount,
			'currency'     => $this->get_gateway_currency(),
			'callback_url' => $this->get_callback_url( $order ),
			'description'  => sprintf(
				/* translators: %s: order number. */
				esc_html__( 'پرداخت سفارش شماره %s', 'rasta-zarinpal-gateway' ),
				$order->get_order_number()
			),
			'metadata'     => $this->get_metadata( $order ),
		);
		$response = $this->api_post( self::REQUEST_PATH, $payload );

		if ( is_wp_error( $response ) ) {
			$this->log( 'Request error: ' . $response->get_error_message() );
			wc_add_notice( esc_html__( 'ارتباط با زرین‌پال برقرار نشد. لطفاً دوباره تلاش کنید.', 'rasta-zarinpal-gateway' ), 'error' );
			return null;
		}

		$code      = isset( $response['data']['code'] ) ? absint( $response['data']['code'] ) : 0;
		$authority = isset( $response['data']['authority'] ) ? sanitize_text_field( $response['data']['authority'] ) : '';
		if ( 100 !== $code || ! $authority ) {
			$this->log( 'Request rejected with code ' . $code . '.' );
			wc_add_notice( esc_html__( 'درخواست پرداخت توسط زرین‌پال پذیرفته نشد. لطفاً روش دیگری انتخاب یا دوباره تلاش کنید.', 'rasta-zarinpal-gateway' ), 'error' );
			return null;
		}

		$order->update_meta_data( '_rasta_zarinpal_authority', $authority );
		$order->update_meta_data( '_rasta_zarinpal_amount', $amount );
		$order->update_meta_data( '_rasta_zarinpal_currency', $this->get_gateway_currency() );
		$order->save();
		$order->add_order_note( sprintf( 'زرین‌پال: درخواست پرداخت ایجاد شد. Authority: %s', $authority ) );

		return array(
			'result'   => 'success',
			'redirect' => $this->get_start_pay_url( $authority ),
		);
	}

	/**
	 * Verify the callback from ZarinPal before marking an order paid.
	 *
	 * @return void
	 */
	public function handle_callback() {
		$order_id  = isset( $_GET['order_id'] ) ? absint( wp_unslash( $_GET['order_id'] ) ) : 0;
		$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
		$status    = isset( $_GET['Status'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['Status'] ) ) ) : '';
		$authority = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : '';
		$order     = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order || ! $order_key || ! hash_equals( $order->get_order_key(), $order_key ) ) {
			wp_die( esc_html__( 'بازگشت پرداخت معتبر نیست.', 'rasta-zarinpal-gateway' ), esc_html__( 'خطای پرداخت', 'rasta-zarinpal-gateway' ), array( 'response' => 400 ) );
		}

		$stored_authority = (string) $order->get_meta( '_rasta_zarinpal_authority', true );
		if ( ! $authority || ! $stored_authority || ! hash_equals( $stored_authority, $authority ) ) {
			$this->fail_order_and_redirect( $order, esc_html__( 'شناسه بازگشتی زرین‌پال با درخواست سفارش مطابقت ندارد.', 'rasta-zarinpal-gateway' ) );
		}

		if ( 'OK' !== $status ) {
			$this->fail_order_and_redirect( $order, esc_html__( 'پرداخت توسط مشتری لغو شد یا ناموفق بود.', 'rasta-zarinpal-gateway' ) );
		}

		$amount   = absint( $order->get_meta( '_rasta_zarinpal_amount', true ) );
		$response = $this->api_post(
			self::VERIFY_PATH,
			array(
				'merchant_id' => $this->merchant_id,
				'amount'      => $amount,
				'authority'   => $authority,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log( 'Verify error: ' . $response->get_error_message() );
			$this->fail_order_and_redirect( $order, esc_html__( 'تأیید پرداخت در زرین‌پال انجام نشد. لطفاً با پشتیبانی فروشگاه تماس بگیرید.', 'rasta-zarinpal-gateway' ) );
		}

		$code   = isset( $response['data']['code'] ) ? absint( $response['data']['code'] ) : 0;
		$ref_id = isset( $response['data']['ref_id'] ) ? absint( $response['data']['ref_id'] ) : 0;
		if ( ! in_array( $code, array( 100, 101 ), true ) || ! $ref_id ) {
			$this->log( 'Verify rejected with code ' . $code . '.' );
			$this->fail_order_and_redirect( $order, esc_html__( 'تأیید پرداخت توسط زرین‌پال ناموفق بود.', 'rasta-zarinpal-gateway' ) );
		}

		$order->update_meta_data( '_rasta_zarinpal_ref_id', $ref_id );
		$order->save();
		if ( ! $order->is_paid() ) {
			$order->payment_complete( (string) $ref_id );
		}
		$order->add_order_note( sprintf( 'زرین‌پال: پرداخت با موفقیت تأیید شد. RefID: %s', $ref_id ) );

		wc_add_notice( esc_html__( 'پرداخت شما با موفقیت تأیید شد.', 'rasta-zarinpal-gateway' ), 'success' );
		wp_safe_redirect( $this->get_return_url( $order ) );
		exit;
	}

	/**
	 * Display the ZarinPal tracking number on the order-received page.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return void
	 */
	public function thankyou_page( $order_id ) {
		$order  = wc_get_order( $order_id );
		$ref_id = $order instanceof WC_Order ? $order->get_meta( '_rasta_zarinpal_ref_id', true ) : '';

		if ( ! $ref_id ) {
			return;
		}
		?>
		<p class="woocommerce-info rasta-zarinpal-reference">
			<?php
			printf(
				/* translators: %s: ZarinPal reference ID. */
				esc_html__( 'شماره پیگیری زرین‌پال: %s', 'rasta-zarinpal-gateway' ),
				esc_html( $ref_id )
			);
			?>
		</p>
		<?php
	}

	/**
	 * Build the callback URL while preserving an order-specific proof key.
	 *
	 * @param WC_Order $order Order instance.
	 * @return string
	 */
	private function get_callback_url( $order ) {
		$callback = WC()->api_request_url( strtolower( get_class( $this ) ) );

		return add_query_arg(
			array(
				'order_id' => $order->get_id(),
				'key'      => $order->get_order_key(),
			),
			$callback
		);
	}

	/**
	 * Get request metadata without sending unnecessary customer data.
	 *
	 * @param WC_Order $order Order instance.
	 * @return array<string, string>
	 */
	private function get_metadata( $order ) {
		$metadata = array(
			'order_id' => (string) $order->get_order_number(),
		);
		$email    = sanitize_email( $order->get_billing_email() );
		$mobile   = $this->normalize_mobile( $order->get_billing_phone() );

		if ( $email ) {
			$metadata['email'] = $email;
		}
		if ( $mobile ) {
			$metadata['mobile'] = $mobile;
		}

		return $metadata;
	}

	/**
	 * Convert common Persian/Arabic phone input to 09xxxxxxxxx when possible.
	 *
	 * @param string $phone Raw customer phone.
	 * @return string
	 */
	private function normalize_mobile( $phone ) {
		$phone = strtr(
			(string) $phone,
			array(
				'۰' => '0',
				'۱' => '1',
				'۲' => '2',
				'۳' => '3',
				'۴' => '4',
				'۵' => '5',
				'۶' => '6',
				'۷' => '7',
				'۸' => '8',
				'۹' => '9',
				'٠' => '0',
				'١' => '1',
				'٢' => '2',
				'٣' => '3',
				'٤' => '4',
				'٥' => '5',
				'٦' => '6',
				'٧' => '7',
				'٨' => '8',
				'٩' => '9',
			)
		);
		$phone = preg_replace( '/\D+/', '', $phone );

		if ( str_starts_with( $phone, '989' ) && 12 === strlen( $phone ) ) {
			$phone = '0' . substr( $phone, 2 );
		} elseif ( str_starts_with( $phone, '9' ) && 10 === strlen( $phone ) ) {
			$phone = '0' . $phone;
		}

		return preg_match( '/^09\d{9}$/', $phone ) ? $phone : '';
	}

	/**
	 * Resolve the currency to send to ZarinPal.
	 *
	 * @return string
	 */
	private function get_gateway_currency() {
		if ( in_array( $this->zarinpal_currency, array( 'IRR', 'IRT' ), true ) ) {
			return $this->zarinpal_currency;
		}

		return get_woocommerce_currency();
	}

	/**
	 * Convert the order total between IRR and IRT when required.
	 *
	 * @param WC_Order $order Order instance.
	 * @return int
	 */
	private function get_gateway_amount( $order ) {
		$amount         = (float) $order->get_total();
		$store_currency = get_woocommerce_currency();
		$api_currency   = $this->get_gateway_currency();

		if ( 'IRR' === $api_currency && 'IRT' === $store_currency ) {
			$amount *= 10;
		} elseif ( 'IRT' === $api_currency && 'IRR' === $store_currency ) {
			$amount /= 10;
		}

		return absint( round( $amount ) );
	}

	/**
	 * Resolve the API base URL for the selected environment.
	 *
	 * @param string $path API path.
	 * @return string
	 */
	private function get_api_url( $path ) {
		$host = $this->sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com';

		return $host . $path;
	}

	/**
	 * Build a hosted StartPay URL for a verified authority.
	 *
	 * @param string $authority ZarinPal authority.
	 * @return string
	 */
	private function get_start_pay_url( $authority ) {
		$host = $this->sandbox ? 'https://sandbox.zarinpal.com' : 'https://www.zarinpal.com';

		return $host . '/pg/StartPay/' . rawurlencode( $authority );
	}

	/**
	 * Send a JSON POST request to ZarinPal v4 and normalize the result.
	 *
	 * @param string               $path    API path.
	 * @param array<string, mixed> $payload Request payload.
	 * @return array<string, mixed>|WP_Error
	 */
	private function api_post( $path, $payload ) {
		$response = wp_remote_post(
			$this->get_api_url( $path ),
			array(
				'timeout' => 30,
				'headers' => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return new WP_Error( 'rasta_zarinpal_invalid_response', esc_html__( 'پاسخ زرین‌پال قابل پردازش نیست.', 'rasta-zarinpal-gateway' ) );
		}

		if ( $status_code < 200 || $status_code >= 300 ) {
			$error_code = isset( $body['errors']['code'] ) ? absint( $body['errors']['code'] ) : $status_code;
			return new WP_Error( 'rasta_zarinpal_http_error', sprintf( 'ZarinPal HTTP error %d.', $error_code ) );
		}

		return $body;
	}

	/**
	 * Mark a payment as failed, add a customer notice, then return to checkout.
	 *
	 * @param WC_Order $order   Order instance.
	 * @param string   $message Shopper-facing failure message.
	 * @return void
	 */
	private function fail_order_and_redirect( $order, $message ) {
		if ( ! $order->is_paid() ) {
			$order->update_status( 'failed', $message );
		}
		wc_add_notice( $message, 'error' );
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}

	/**
	 * Write non-sensitive diagnostics to WooCommerce logs only when requested.
	 *
	 * @param string $message Log message.
	 * @return void
	 */
	private function log( $message ) {
		if ( ! $this->debug || ! function_exists( 'wc_get_logger' ) ) {
			return;
		}

		wc_get_logger()->error( $message, array( 'source' => 'rasta-zarinpal' ) );
	}
}
