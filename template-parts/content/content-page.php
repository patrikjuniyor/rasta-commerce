<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Page content template.
 *
 * @package Rasta_Commerce
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'rasta-page' ); ?>>
	<header class="entry-header rasta-page-header">
		<p class="rasta-kicker"><?php esc_html_e( 'راستا', 'rasta-commerce' ); ?></p>
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>
	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages(); ?>
	</div>
</article>
