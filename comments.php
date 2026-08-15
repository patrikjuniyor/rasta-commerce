<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Comments template.
 *
 * @package Rasta_Commerce
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			printf(
				/* translators: %s: number of comments. */
				esc_html( _nx( '%1$s دیدگاه', '%1$s دیدگاه', get_comments_number(), 'comments title', 'rasta-commerce' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>
		<ol class="comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true, 'callback' => 'rasta_comment' ) ); ?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'دیدگاه‌ها بسته شده‌اند.', 'rasta-commerce' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
