<?php
/**
 * Offline registration contract test for Rasta Commerce Core widgets.
 */

namespace Elementor {
	class Widget_Base {}

	class Widgets_Manager {
		public $widgets = array();

		public function register( $widget ) {
			$this->widgets[] = $widget;
		}
	}

	class Elements_Manager {
		public $categories = array();

		public function add_category( $slug, $settings ) {
			$this->categories[ $slug ] = $settings;
		}
	}

	class Controls_Manager {
		public const TEXT = 'text';
		public const TEXTAREA = 'textarea';
		public const URL = 'url';
		public const MEDIA = 'media';
		public const COLOR = 'color';
		public const DIMENSIONS = 'dimensions';
		public const CHOOSE = 'choose';
		public const SELECT = 'select';
		public const NUMBER = 'number';
		public const REPEATER = 'repeater';
		public const ICONS = 'icons';
		public const WYSIWYG = 'wysiwyg';
		public const SWITCHER = 'switcher';
		public const TAB_STYLE = 'style';
	}

	class Repeater {
		private $controls = array();

		public function add_control( $name, $settings ) {
			$this->controls[ $name ] = $settings;
		}

		public function get_controls() {
			return $this->controls;
		}
	}

	class Icons_Manager {
		public static function render_icon() {}
	}
}

namespace {
	define( 'ABSPATH', __DIR__ );

	function add_action() { return true; }
	function did_action( $hook ) { return 'elementor/loaded' === $hook; }
	function load_plugin_textdomain() { return true; }
	function plugin_basename( $file ) { return basename( $file ); }
	function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
	function plugin_dir_url() { return 'https://example.test/wp-content/plugins/rasta-commerce-core/'; }
	function esc_html__( $text ) { return $text; }
	function current_user_can() { return true; }

	require dirname( __DIR__, 2 ) . '/plugins/rasta-commerce-core/rasta-commerce-core.php';

	/**
	 * @param mixed  $actual Actual value.
	 * @param mixed  $expected Expected value.
	 * @param string $message Failure message.
	 * @return void
	 */
	function rasta_elementor_assert_same( $actual, $expected, $message ) {
		if ( $actual !== $expected ) {
			fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
			exit( 1 );
		}
	}

	rasta_commerce_core_bootstrap();
	$categories = new \Elementor\Elements_Manager();
	rasta_commerce_core_register_category( $categories );
	rasta_elementor_assert_same( isset( $categories->categories['rasta-commerce'] ), true, 'The Rasta Commerce Elementor category should register.' );

	$widgets = new \Elementor\Widgets_Manager();
	rasta_commerce_core_register_widgets( $widgets );
	rasta_elementor_assert_same( count( $widgets->widgets ), 10, 'Exactly ten storefront widgets should register.' );

	$names = array_map(
		static function ( $widget ) {
			return $widget->get_name();
		},
		$widgets->widgets
	);
	rasta_elementor_assert_same( count( array_unique( $names ) ), 10, 'Each registered Elementor widget should have a unique name.' );
	rasta_elementor_assert_same( in_array( 'rasta-product-grid', $names, true ), true, 'Product Grid widget should be included.' );
	rasta_elementor_assert_same( in_array( 'rasta-faq', $names, true ), true, 'FAQ widget should be included.' );

	fwrite( STDOUT, "Elementor core widget contract tests: PASS\n" );
}
