<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Blog sidebar.
 *
 * @package Rasta_Commerce
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside id="secondary" class="widget-area rasta-sidebar" aria-label="<?php esc_attr_e( 'ستون کناری', 'rasta-commerce' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
