<?php
/**
 * Front-page storefront template.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hero_image = rasta_get_mod( 'rasta_hero_image', '' );
if ( ! $hero_image ) {
	$hero_image = RASTA_URI . '/assets/images/hero-showcase.svg';
}
?>
<main id="content" class="site-main rasta-home">
	<section class="rasta-hero">
		<div class="rasta-container rasta-hero__grid">
			<div class="rasta-hero__copy">
				<p class="rasta-kicker rasta-kicker--light">
					<?php rasta_icon( 'sparkles' ); ?>
					<?php echo esc_html( rasta_get_mod( 'rasta_hero_eyebrow', __( 'انتخاب هوشمند، خرید آسوده', 'rasta-commerce' ) ) ); ?>
				</p>
				<h1><?php echo esc_html( rasta_get_mod( 'rasta_hero_title', __( 'چیزهای خوب، برای زندگیِ خوب', 'rasta-commerce' ) ) ); ?></h1>
				<p class="rasta-hero__description"><?php echo esc_html( rasta_get_mod( 'rasta_hero_text', __( 'یک ویترین خوش‌ساخت برای پیدا کردن محصولاتی که هر روزتان را ساده‌تر و زیباتر می‌کنند.', 'rasta-commerce' ) ) ); ?></p>
				<div class="rasta-hero__actions">
					<a class="rasta-button rasta-button--light" href="<?php echo esc_url( rasta_get_shop_url() ); ?>">
						<?php echo esc_html( rasta_get_mod( 'rasta_hero_cta', __( 'مشاهده فروشگاه', 'rasta-commerce' ) ) ); ?>
						<?php rasta_icon( 'arrow-left' ); ?>
					</a>
					<a class="rasta-hero__secondary" href="#rasta-categories"><?php esc_html_e( 'دسته‌بندی‌ها را ببینید', 'rasta-commerce' ); ?></a>
				</div>
				<div class="rasta-hero__proof" aria-label="<?php esc_attr_e( 'مزیت‌های خرید', 'rasta-commerce' ); ?>">
					<span><?php rasta_icon( 'check' ); ?><?php esc_html_e( 'ارسال سریع', 'rasta-commerce' ); ?></span>
					<span><?php rasta_icon( 'check' ); ?><?php esc_html_e( 'پرداخت امن', 'rasta-commerce' ); ?></span>
					<span><?php rasta_icon( 'check' ); ?><?php esc_html_e( 'پشتیبانی واقعی', 'rasta-commerce' ); ?></span>
				</div>
			</div>
			<div class="rasta-hero__media" aria-hidden="true">
				<img src="<?php echo esc_url( $hero_image ); ?>" alt="" width="664" height="566" fetchpriority="high" decoding="async" />
				<div class="rasta-floating-card rasta-floating-card--rating">
					<?php rasta_icon( 'star' ); ?>
					<span><strong>۴.۹/۵</strong><?php esc_html_e( 'رضایت خریداران', 'rasta-commerce' ); ?></span>
				</div>
				<div class="rasta-floating-card rasta-floating-card--delivery">
					<?php rasta_icon( 'box' ); ?>
					<span><strong><?php esc_html_e( 'ارسال به سراسر ایران', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'با پیگیری لحظه‌ای', 'rasta-commerce' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<section class="rasta-trust-strip" aria-label="<?php esc_attr_e( 'مزیت‌های فروشگاه', 'rasta-commerce' ); ?>">
		<div class="rasta-container rasta-trust-strip__grid">
			<div><?php rasta_icon( 'truck' ); ?><span><strong><?php esc_html_e( 'ارسال سریع', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'تحویل امن و قابل پیگیری', 'rasta-commerce' ); ?></span></div>
			<div><?php rasta_icon( 'shield' ); ?><span><strong><?php esc_html_e( 'تضمین اصالت', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'خرید با خیال راحت', 'rasta-commerce' ); ?></span></div>
			<div><?php rasta_icon( 'headset' ); ?><span><strong><?php esc_html_e( 'پشتیبانی انسانی', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'کنار شما تا پایان خرید', 'rasta-commerce' ); ?></span></div>
			<div><?php rasta_icon( 'box' ); ?><span><strong><?php esc_html_e( 'بازگشت آسان', 'rasta-commerce' ); ?></strong><?php esc_html_e( 'فرصت تصمیم‌گیری بیشتر', 'rasta-commerce' ); ?></span></div>
		</div>
	</section>

	<section id="rasta-categories" class="rasta-section rasta-category-section">
		<div class="rasta-container">
			<div class="rasta-section-heading">
				<div>
					<p class="rasta-kicker"><?php esc_html_e( 'شروع کنید', 'rasta-commerce' ); ?></p>
					<h2><?php esc_html_e( 'برای امروزتان چه چیزی لازم دارید؟', 'rasta-commerce' ); ?></h2>
					<p><?php esc_html_e( 'سریع‌تر به چیزی برسید که دنبالش هستید.', 'rasta-commerce' ); ?></p>
				</div>
				<a class="rasta-text-link" href="<?php echo esc_url( rasta_get_shop_url() ); ?>">
					<?php esc_html_e( 'همه دسته‌ها', 'rasta-commerce' ); ?>
					<?php rasta_icon( 'arrow-left' ); ?>
				</a>
			</div>
			<?php rasta_render_product_categories(); ?>
		</div>
	</section>

	<div class="rasta-container">
		<?php
		rasta_render_product_rail(
			__( 'پرفروش‌های این هفته', 'rasta-commerce' ),
			__( 'محصولاتی که خریداران بیشتر از همه به آن‌ها اعتماد کرده‌اند.', 'rasta-commerce' ),
			array(
				'orderby' => 'popularity',
				'order'   => 'DESC',
			)
		);
		?>
	</div>

	<section class="rasta-promo-band">
		<div class="rasta-container rasta-promo-band__grid">
			<div class="rasta-promo-band__art" aria-hidden="true">
				<span class="rasta-promo-orb rasta-promo-orb--one"></span>
				<span class="rasta-promo-orb rasta-promo-orb--two"></span>
				<?php rasta_icon( 'sparkles' ); ?>
			</div>
			<div>
				<p class="rasta-kicker rasta-kicker--light"><?php esc_html_e( 'با انتخاب بهتر', 'rasta-commerce' ); ?></p>
				<h2><?php esc_html_e( 'جزئیات کوچک، حس خوب بزرگ', 'rasta-commerce' ); ?></h2>
				<p><?php esc_html_e( 'از ابزار روزمره تا هدیه‌ای دوست‌داشتنی؛ با انتخاب‌های دقیق‌تر، خانه و کارتان را دلپذیرتر کنید.', 'rasta-commerce' ); ?></p>
				<a class="rasta-button rasta-button--light" href="<?php echo esc_url( rasta_get_shop_url() ); ?>">
					<?php esc_html_e( 'پیشنهادهای ویژه', 'rasta-commerce' ); ?>
					<?php rasta_icon( 'arrow-left' ); ?>
				</a>
			</div>
		</div>
	</section>

	<div class="rasta-container">
		<?php
		rasta_render_product_rail(
			__( 'تازه به فروشگاه رسیده', 'rasta-commerce' ),
			__( 'تازه‌ترین انتخاب‌ها، آماده برای دیده شدن.', 'rasta-commerce' )
		);
		?>
	</div>

	<?php
	$blog_page_id = (int) get_option( 'page_for_posts' );
	$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/blog/' );
	$latest_posts = new WP_Query(
		array(
			'posts_per_page'      => 3,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
		)
	);
	?>
	<?php if ( $latest_posts->have_posts() ) : ?>
		<section class="rasta-section rasta-journal-section">
			<div class="rasta-container">
				<div class="rasta-section-heading">
					<div>
						<p class="rasta-kicker"><?php esc_html_e( 'مجله راستا', 'rasta-commerce' ); ?></p>
						<h2><?php esc_html_e( 'برای انتخاب آگاهانه‌تر', 'rasta-commerce' ); ?></h2>
						<p><?php esc_html_e( 'راهنماها و ایده‌هایی برای بهتر خریدن و بهتر زندگی کردن.', 'rasta-commerce' ); ?></p>
					</div>
					<a class="rasta-text-link" href="<?php echo esc_url( $blog_url ); ?>">
						<?php esc_html_e( 'همه نوشته‌ها', 'rasta-commerce' ); ?>
						<?php rasta_icon( 'arrow-left' ); ?>
					</a>
				</div>
				<div class="rasta-journal-grid">
					<?php while ( $latest_posts->have_posts() ) : ?>
						<?php $latest_posts->the_post(); ?>
						<article <?php post_class( 'rasta-journal-card' ); ?>>
							<a class="rasta-journal-card__image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
								<?php else : ?>
									<span><?php rasta_icon( 'sparkles' ); ?></span>
								<?php endif; ?>
							</a>
							<div class="rasta-journal-card__body">
								<span class="rasta-journal-card__date"><?php echo esc_html( rasta_get_the_jalali_date() ); ?></span>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
								<a class="rasta-text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'ادامه مطلب', 'rasta-commerce' ); ?><?php rasta_icon( 'arrow-left' ); ?></a>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
			</div>
		</section>
		<?php wp_reset_postdata(); ?>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php if ( trim( get_the_content() ) ) : ?>
				<section class="rasta-section rasta-home-page-content">
					<div class="rasta-container entry-content"><?php the_content(); ?></div>
				</section>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
