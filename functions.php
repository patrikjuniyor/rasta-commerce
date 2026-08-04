<?php
/**
 * Rasta Commerce functions and definitions.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RASTA_VERSION', '1.0.0' );
define( 'RASTA_DIR', get_template_directory() );
define( 'RASTA_URI', get_template_directory_uri() );

require RASTA_DIR . '/inc/customizer.php';
require RASTA_DIR . '/inc/template-tags.php';
require RASTA_DIR . '/inc/setup.php';
require RASTA_DIR . '/inc/woocommerce.php';
require RASTA_DIR . '/inc/ajax.php';
