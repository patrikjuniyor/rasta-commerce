<?php
/**
 * RTL-aware search form.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$unique_id = wp_unique_id( 'rasta-search-form-' );
?>
<form role="search" method="get" class="rasta-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( 'جست‌وجو برای:', 'rasta-commerce' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $unique_id ); ?>" class="rasta-searchform__field" name="s" placeholder="<?php esc_attr_e( 'جست‌وجو…', 'rasta-commerce' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
	<button type="submit" class="rasta-searchform__submit" aria-label="<?php esc_attr_e( 'جست‌وجو', 'rasta-commerce' ); ?>">
		<?php rasta_icon( 'search' ); ?>
	</button>
</form>
