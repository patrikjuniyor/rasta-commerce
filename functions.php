<?php
/**
 * Rasta Commerce functions and definitions.
 *
 * Loads the built-in product system, cart, AJAX handlers, and templates.
 * WooCommerce support is optional — when active, it enhances the experience.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RASTA_VERSION', '2.5.0' );
define( 'RASTA_DIR', get_template_directory() );
define( 'RASTA_URI', get_template_directory_uri() );

/* ─── Core modules ─────────────────────────────────────────────────────── */

require RASTA_DIR . '/inc/customizer.php';
require RASTA_DIR . '/inc/admin.php';
require RASTA_DIR . '/inc/maintenance.php';
require RASTA_DIR . '/inc/store-settings.php';
require RASTA_DIR . '/inc/notifications.php';
require RASTA_DIR . '/inc/jalali.php';
require RASTA_DIR . '/inc/products.php';
require RASTA_DIR . '/inc/cart.php';
require RASTA_DIR . '/inc/template-tags.php';
require RASTA_DIR . '/inc/setup.php';
require RASTA_DIR . '/inc/ajax.php';
require RASTA_DIR . '/inc/shortcodes.php';

/* Load WooCommerce compatibility layer only when the plugin is active. */
if ( class_exists( 'WooCommerce' ) ) {
	require RASTA_DIR . '/inc/woocommerce.php';
}
