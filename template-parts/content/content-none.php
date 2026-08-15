<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Empty results template.
 *
 * @package Rasta_Commerce
 */
?>
<section class="rasta-empty-state">
	<?php rasta_icon( 'search' ); ?>
	<h2><?php esc_html_e( 'چیزی پیدا نشد.', 'rasta-commerce' ); ?></h2>
	<p><?php esc_html_e( 'عبارت دیگری را امتحان کنید یا از منوی فروشگاه کمک بگیرید.', 'rasta-commerce' ); ?></p>
	<?php get_search_form(); ?>
</section>
