<?php
/**
 * Child theme bootstrap for Rasta Commerce.
 *
 * @package Rasta_Commerce_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load child overrides after the parent theme styles.
 *
 * @return void
 */
function rasta_commerce_child_enqueue_assets() {
	wp_enqueue_style(
		'rasta-commerce-child',
		get_stylesheet_uri(),
		array( 'rasta-commerce' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'rasta_commerce_child_enqueue_assets', 30 );
