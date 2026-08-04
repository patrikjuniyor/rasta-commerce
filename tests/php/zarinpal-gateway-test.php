<?php
/**
 * Offline contract tests for the ZarinPal gateway's pure conversion and URL logic.
 * These run without WordPress or WooCommerce installed.
 */

define( 'ABSPATH', __DIR__ );

$GLOBALS['rasta_zarinpal_test_settings'] = array(
	'merchant_id' => '00000000-0000-0000-0000-000000000000',
	'currency'    => 'IRR',
	'sandbox'     => 'yes',
	'debug'       => 'no',
);
$GLOBALS['rasta_zarinpal_test_currency'] = 'IRT';

function esc_html__( $text ) {
	return $text;
}

function add_action() {
	return true;
}

function get_woocommerce_currency() {
	return $GLOBALS['rasta_zarinpal_test_currency'];
}

function absint( $value ) {
	return abs( (int) $value );
}

class WC_Payment_Gateway {
	public $id;
	public $enabled = 'yes';

	public function init_settings() {}

	public function get_option( $key, $default = '' ) {
		return isset( $GLOBALS['rasta_zarinpal_test_settings'][ $key ] ) ? $GLOBALS['rasta_zarinpal_test_settings'][ $key ] : $default;
	}

	public function is_available() {
		return true;
	}
}

class WC_Order {
	private $total;

	public function __construct( $total ) {
		$this->total = $total;
	}

	public function get_total() {
		return $this->total;
	}
}

require dirname( __DIR__, 2 ) . '/plugins/rasta-zarinpal-gateway/includes/class-rasta-zarinpal-gateway.php';

/**
 * @param mixed  $actual Actual value.
 * @param mixed  $expected Expected value.
 * @param string $message Failure message.
 * @return void
 */
function rasta_zarinpal_assert_same( $actual, $expected, $message ) {
	if ( $actual !== $expected ) {
		fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

/**
 * @param object $object Object under test.
 * @param string $method Private method name.
 * @param mixed  ...$arguments Arguments.
 * @return mixed
 */
function rasta_zarinpal_call_private( $object, $method, ...$arguments ) {
	$reflection = new ReflectionMethod( $object, $method );
	$reflection->setAccessible( true );

	return $reflection->invoke( $object, ...$arguments );
}

$gateway = new WC_Gateway_Rasta_Zarinpal();
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $gateway, 'get_gateway_currency' ), 'IRR', 'Gateway should honor an explicitly configured IRR API currency.' );
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $gateway, 'get_gateway_amount', new WC_Order( 1250 ) ), 12500, 'IRT store totals should convert to IRR when configured.' );
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $gateway, 'get_api_url', '/pg/v4/payment/request.json' ), 'https://sandbox.zarinpal.com/pg/v4/payment/request.json', 'Sandbox request URL should use the documented sandbox host.' );
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $gateway, 'get_start_pay_url', 'S-test/authority' ), 'https://sandbox.zarinpal.com/pg/StartPay/S-test%2Fauthority', 'Sandbox StartPay URL should encode the authority safely.' );
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $gateway, 'normalize_mobile', '۹۱۲-۱۲۳-۴۵۶۷' ), '09121234567', 'Persian-digit mobile numbers should normalize to the metadata format.' );

$GLOBALS['rasta_zarinpal_test_settings']['currency'] = 'auto';
$GLOBALS['rasta_zarinpal_test_settings']['sandbox']  = 'no';
$GLOBALS['rasta_zarinpal_test_currency']             = 'IRR';
$production_gateway = new WC_Gateway_Rasta_Zarinpal();
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $production_gateway, 'get_gateway_currency' ), 'IRR', 'Auto mode should preserve an IRR store currency.' );
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $production_gateway, 'get_gateway_amount', new WC_Order( 12500 ) ), 12500, 'Matching currencies should not convert the order amount.' );
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $production_gateway, 'get_api_url', '/pg/v4/payment/verify.json' ), 'https://payment.zarinpal.com/pg/v4/payment/verify.json', 'Production verification should use the documented payment host.' );
rasta_zarinpal_assert_same( rasta_zarinpal_call_private( $production_gateway, 'get_start_pay_url', 'A0001' ), 'https://www.zarinpal.com/pg/StartPay/A0001', 'Production StartPay should use the public hosted payment URL.' );

fwrite( STDOUT, "ZarinPal gateway contract tests: PASS\n" );
