<?php
/**
 * Offline contract tests for Customizer controls (dark mode, footer, layout).
 */

namespace {
	define( 'ABSPATH', __DIR__ );

	function add_action( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function add_filter( $hook, $callback = null, $priority = 10, $accepted = 1 ) { return true; }
	function esc_html__( $text, $domain = null ) { return $text; }
	function esc_html_e( $text, $domain = null ) { echo $text; }
	function esc_html( $text ) { return $text; }
	function esc_attr__( $text, $domain = null ) { return $text; }
	function esc_attr_e( $text, $domain = null ) { echo $text; }
	function esc_attr( $text ) { return $text; }
	function esc_url_raw( $url ) { return $url; }
	function sanitize_hex_color( $color ) { return preg_match( '/^#[0-9a-f]{3,8}$/i', (string) $color ) ? $color : null; }
	function sanitize_text_field( $text ) { return trim( (string) $text ); }
	function sanitize_textarea_field( $text ) { return trim( (string) $text ); }
	function sanitize_key( $key ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) ); }
	function absint( $value ) { return abs( (int) $value ); }
	function get_theme_mod( $name, $default = '' ) { return $default; }

	class WP_Customize_Manager {
		public $sections = array();
		public $settings = array();
		public $controls = array();

		public function add_section( $id, $args = array() ) { $this->sections[ $id ] = $args; }
		public function add_setting( $id, $args = array() ) { $this->settings[ $id ] = $args; }
		public function add_control( $id, $args = array() ) {
			if ( is_object( $id ) ) {
				return;
			}
			$this->controls[ $id ] = $args;
		}
	}

	class WP_Customize_Color_Control {
		public function __construct( $manager, $id, $args = array() ) { $manager->add_control( $id, $args ); }
	}

	class WP_Customize_Image_Control {
		public function __construct( $manager, $id, $args = array() ) { $manager->add_control( $id, $args ); }
	}

	require dirname( __DIR__, 2 ) . '/inc/customizer.php';

	/**
	 * @param mixed  $actual   Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message  Failure message.
	 * @return void
	 */
	function rasta_customizer_assert( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	/* Sanitizers. */
	rasta_customizer_assert( rasta_sanitize_shop_columns( 1 ), 2, 'Shop columns below 2 should clamp to 2.' );
	rasta_customizer_assert( rasta_sanitize_shop_columns( 9 ), 5, 'Shop columns above 5 should clamp to 5.' );
	rasta_customizer_assert( rasta_sanitize_shop_columns( 4 ), 4, 'Shop columns within range should pass through.' );
	rasta_customizer_assert( rasta_sanitize_checkbox( 'on' ), true, 'Checkbox "on" should sanitize to true.' );
	rasta_customizer_assert( rasta_sanitize_checkbox( '' ), false, 'Empty checkbox should sanitize to false.' );
	rasta_customizer_assert( rasta_sanitize_non_negative_integer( -5 ), 5, 'Negative integer should resolve to its absolute value.' );
	rasta_customizer_assert( rasta_sanitize_non_negative_integer( '12' ), 12, 'Numeric string should become int.' );
	rasta_customizer_assert( rasta_sanitize_non_negative_integer( 0 ), 0, 'Zero should pass through unchanged.' );
	rasta_customizer_assert( rasta_sanitize_brand_color( '#f25c54' ), '#f25c54', 'Valid hex color should pass through.' );
	rasta_customizer_assert( rasta_sanitize_brand_color( 'not-a-color' ), null, 'Invalid color should sanitize to null.' );
	rasta_customizer_assert( rasta_sanitize_phone( '+98 912 123 4567' ), '989121234567', 'Phone should be stripped to digits only.' );
	rasta_customizer_assert( rasta_sanitize_phone( '0912-123-45-67' ), '09121234567', 'Phone should drop dashes and spaces.' );

	/* Registration contract for the new sections and controls. */
	$manager = new WP_Customize_Manager();
	rasta_customize_register( $manager );

	rasta_customizer_assert( isset( $manager->sections['rasta_appearance'] ), true, 'Appearance section should be registered.' );
	rasta_customizer_assert( isset( $manager->sections['rasta_footer'] ), true, 'Footer section should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_enable_dark_mode'] ), true, 'Dark mode toggle should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_enable_dark_mode_default'] ), true, 'Dark mode default toggle should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_shop_columns'] ), true, 'Shop columns setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_footer_about'] ), true, 'Footer about setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_footer_copyright'] ), true, 'Footer copyright setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_promo_link'] ), true, 'Promo link setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_enable_dismissible_promo'] ), true, 'Dismissible promo toggle should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_whatsapp_url'] ), true, 'WhatsApp URL setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_twitter_url'] ), true, 'Twitter URL setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_aparat_url'] ), true, 'Aparat URL setting should be registered.' );

	rasta_customizer_assert( isset( $manager->sections['rasta_store_state'] ), true, 'Store state section should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_enable_maintenance'] ), true, 'Maintenance toggle should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_maintenance_headline'] ), true, 'Maintenance headline setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_maintenance_message'] ), true, 'Maintenance message setting should be registered.' );

	rasta_customizer_assert( isset( $manager->sections['rasta_whatsapp'] ), true, 'WhatsApp section should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_enable_whatsapp'] ), true, 'WhatsApp toggle should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_whatsapp_number'] ), true, 'WhatsApp number setting should be registered.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_whatsapp_message'] ), true, 'WhatsApp message setting should be registered.' );

	/* Currency control + sanitizer. */
	rasta_customizer_assert( rasta_sanitize_currency( 'IRT' ), 'IRT', 'Valid currency IRT should pass through.' );
	rasta_customizer_assert( rasta_sanitize_currency( 'IRR' ), 'IRR', 'Valid currency IRR should pass through.' );
	rasta_customizer_assert( rasta_sanitize_currency( 'USD' ), 'IRT', 'Unsupported currency should fall back to IRT.' );
	rasta_customizer_assert( isset( $manager->settings['rasta_currency'] ), true, 'Currency setting should be registered.' );
	rasta_customizer_assert( $manager->controls['rasta_currency']['type'], 'select', 'Currency should be a select control.' );

	/* UX: shop columns uses a labeled select control. */
	rasta_customizer_assert( $manager->controls['rasta_shop_columns']['type'], 'select', 'Shop columns should be a select control.' );
	rasta_customizer_assert( count( $manager->controls['rasta_shop_columns']['choices'] ), 4, 'Shop columns select should offer four choices.' );

	/* UX: conditional controls are hidden behind their parent toggles. */
	rasta_customizer_assert( is_callable( $manager->controls['rasta_enable_dark_mode_default']['active_callback'] ), true, 'Dark mode default should be conditionally shown.' );
	rasta_customizer_assert( is_callable( $manager->controls['rasta_whatsapp_number']['active_callback'] ), true, 'WhatsApp number should be conditionally shown.' );
	rasta_customizer_assert( is_callable( $manager->controls['rasta_maintenance_headline']['active_callback'] ), true, 'Maintenance headline should be conditionally shown.' );

	fwrite( STDOUT, "Customizer contract tests: PASS\n" );
}
