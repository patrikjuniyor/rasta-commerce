<?php
/**
 * Plugin Name: Rasta Commerce Core for Elementor
 * Plugin URI: https://github.com/patrikjuniyor/rasta-commerce
 * Description: Ten RTL-native Elementor widgets for building Rasta Commerce storefront pages.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Requires Plugins: elementor
 * Elementor tested up to: 3.30.0
 * Author: Rasta Commerce Contributors
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rasta-commerce-core
 * Domain Path: /languages
 *
 * @package Rasta_Commerce_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RASTA_CORE_VERSION', '1.0.0' );
define( 'RASTA_CORE_FILE', __FILE__ );
define( 'RASTA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'RASTA_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Bootstrap Elementor integration after all plugins have loaded.
 *
 * @return void
 */
function rasta_commerce_core_bootstrap() {
	load_plugin_textdomain( 'rasta-commerce-core', false, dirname( plugin_basename( RASTA_CORE_FILE ) ) . '/languages' );

	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'rasta_commerce_core_missing_elementor_notice' );
		return;
	}

	require_once RASTA_CORE_DIR . 'includes/class-rasta-commerce-elementor-base.php';
	require_once RASTA_CORE_DIR . 'includes/class-rasta-commerce-elementor-widgets.php';

	add_action( 'elementor/elements/categories_registered', 'rasta_commerce_core_register_category' );
	add_action( 'elementor/widgets/register', 'rasta_commerce_core_register_widgets' );
	add_action( 'elementor/frontend/after_register_styles', 'rasta_commerce_core_register_assets' );
	add_action( 'elementor/editor/before_enqueue_styles', 'rasta_commerce_core_register_assets' );
	add_action( 'wp_enqueue_scripts', 'rasta_commerce_core_register_assets', 5 );
}
add_action( 'plugins_loaded', 'rasta_commerce_core_bootstrap', 20 );

/**
 * Explain the dependency when Elementor is missing.
 *
 * @return void
 */
function rasta_commerce_core_missing_elementor_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'افزونه Rasta Commerce Core برای فعال شدن به Elementor نیاز دارد.', 'rasta-commerce-core' ); ?></p>
	</div>
	<?php
}

/**
 * Register a dedicated, Persian-named Elementor widget category.
 *
 * @param Elementor\Elements_Manager $elements_manager Elementor elements manager.
 * @return void
 */
function rasta_commerce_core_register_category( $elements_manager ) {
	$elements_manager->add_category(
		'rasta-commerce',
		array(
			'title' => esc_html__( 'راستا کامرس', 'rasta-commerce-core' ),
			'icon'  => 'eicon-store',
		)
	);
}

/**
 * Register the ten storefront widgets.
 *
 * @param Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
 * @return void
 */
function rasta_commerce_core_register_widgets( $widgets_manager ) {
	$widgets_manager->register( new Rasta_Commerce_Elementor_Hero_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Section_Heading_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Product_Grid_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Product_Rail_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Category_Grid_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Promo_Banner_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Trust_Strip_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Blog_Grid_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_Feature_Card_Widget() );
	$widgets_manager->register( new Rasta_Commerce_Elementor_FAQ_Widget() );
}

/**
 * Register frontend styles used by all Rasta Elementor widgets.
 *
 * @return void
 */
function rasta_commerce_core_register_assets() {
	wp_register_style(
		'rasta-commerce-elementor',
		RASTA_CORE_URL . 'assets/css/rasta-commerce-elementor.css',
		array(),
		RASTA_CORE_VERSION
	);
}
