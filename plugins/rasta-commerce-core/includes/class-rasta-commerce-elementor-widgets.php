<?php
/**
 * Rasta Commerce Elementor widget collection.
 *
 * @package Rasta_Commerce_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hero widget.
 */
class Rasta_Commerce_Elementor_Hero_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() {
		return 'rasta-hero';
	}

	public function get_title() {
		return esc_html__( 'راستا: هیرو فروشگاه', 'rasta-commerce-core' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_keywords() {
		return array( 'rasta', 'hero', 'store', 'فروشگاه', 'هیرو' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ),
			)
		);
		$this->add_control(
			'eyebrow',
			array(
				'label'   => esc_html__( 'برچسب', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'انتخاب هوشمند، خرید آسوده', 'rasta-commerce-core' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'تیتر', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'چیزهای خوب، برای زندگیِ خوب', 'rasta-commerce-core' ),
				'rows'    => 2,
			)
		);
		$this->add_control(
			'description',
			array(
				'label'   => esc_html__( 'توضیح', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'یک ویترین خوش‌ساخت برای پیدا کردن محصولاتی که هر روزتان را ساده‌تر و زیباتر می‌کنند.', 'rasta-commerce-core' ),
				'rows'    => 4,
			)
		);
		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'متن دکمه اصلی', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'مشاهده فروشگاه', 'rasta-commerce-core' ),
			)
		);
		$this->add_control(
			'button_url',
			array(
				'label'       => esc_html__( 'لینک دکمه اصلی', 'rasta-commerce-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com/shop',
			)
		);
		$this->add_control(
			'secondary_text',
			array(
				'label' => esc_html__( 'متن لینک ثانویه', 'rasta-commerce-core' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'secondary_url',
			array(
				'label'       => esc_html__( 'لینک ثانویه', 'rasta-commerce-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com/categories',
				'condition'   => array( 'secondary_text!' => '' ),
			)
		);
		$this->add_control(
			'image',
			array(
				'label' => esc_html__( 'تصویر', 'rasta-commerce-core' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_hero_style',
			array(
				'label' => esc_html__( 'استایل هیرو', 'rasta-commerce-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'background',
			array(
				'label'     => esc_html__( 'پس‌زمینه', 'rasta-commerce-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#273b7a',
				'selectors' => array( '{{WRAPPER}} .rasta-ec-hero' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'رنگ متن', 'rasta-commerce-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .rasta-ec-hero' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->add_render_attribute( 'hero', 'class', array( 'rasta-ec-widget', 'rasta-ec-hero' ) );
		?>
		<section <?php echo wp_kses_post( $this->get_render_attribute_string( 'hero' ) ); ?>>
			<div class="rasta-ec-hero__copy">
				<?php if ( ! empty( $settings['eyebrow'] ) ) : ?><p class="rasta-ec-kicker rasta-ec-kicker--light"><?php echo esc_html( $settings['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( ! empty( $settings['title'] ) ) : ?><h1><?php echo esc_html( $settings['title'] ); ?></h1><?php endif; ?>
				<?php if ( ! empty( $settings['description'] ) ) : ?><p class="rasta-ec-hero__description"><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				<div class="rasta-ec-hero__actions">
					<?php if ( ! empty( $settings['button_text'] ) && ! empty( $settings['button_url']['url'] ) ) : ?>
						<a class="rasta-ec-button rasta-ec-button--light" href="<?php echo esc_url( $settings['button_url']['url'] ); ?>"<?php echo ! empty( $settings['button_url']['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $settings['button_text'] ); ?><span aria-hidden="true">←</span></a>
					<?php endif; ?>
					<?php if ( ! empty( $settings['secondary_text'] ) && ! empty( $settings['secondary_url']['url'] ) ) : ?>
						<a class="rasta-ec-hero__secondary" href="<?php echo esc_url( $settings['secondary_url']['url'] ); ?>"<?php echo ! empty( $settings['secondary_url']['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $settings['secondary_text'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( ! empty( $settings['image']['url'] ) ) : ?>
				<div class="rasta-ec-hero__media"><img src="<?php echo esc_url( $settings['image']['url'] ); ?>" alt="" loading="lazy" /></div>
			<?php endif; ?>
		</section>
		<?php
	}
}

/**
 * Section heading widget.
 */
class Rasta_Commerce_Elementor_Section_Heading_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() { return 'rasta-section-heading'; }
	public function get_title() { return esc_html__( 'راستا: تیتر بخش', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-heading'; }
	public function get_keywords() { return array( 'rasta', 'heading', 'title', 'تیتر' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_section_heading_controls(
			array(
				'eyebrow'     => esc_html__( 'منتخب فروشگاه', 'rasta-commerce-core' ),
				'title'       => esc_html__( 'عنوان بخش فروشگاه', 'rasta-commerce-core' ),
				'description' => esc_html__( 'توضیح کوتاه برای معرفی این بخش.', 'rasta-commerce-core' ),
				'link_text'   => esc_html__( 'مشاهده بیشتر', 'rasta-commerce-core' ),
			)
		);
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="rasta-ec-widget rasta-ec-section-heading-widget">
			<?php $this->render_section_heading( $settings ); ?>
		</section>
		<?php
	}
}

/**
 * Shared product-query widget functionality.
 */
abstract class Rasta_Commerce_Elementor_Product_Query_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	/**
	 * Add product-query controls used by grid and rail widgets.
	 *
	 * @param int $default_limit Default product count.
	 * @return void
	 */
	protected function add_product_query_controls( $default_limit = 4 ) {
		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'تعداد محصول', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => $default_limit,
				'min'     => 1,
				'max'     => 12,
			)
		);
		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'مرتب‌سازی', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => esc_html__( 'جدیدترین', 'rasta-commerce-core' ),
					'popularity' => esc_html__( 'پرفروش‌ترین', 'rasta-commerce-core' ),
					'rating'     => esc_html__( 'بالاترین امتیاز', 'rasta-commerce-core' ),
					'price'      => esc_html__( 'قیمت', 'rasta-commerce-core' ),
					'rand'       => esc_html__( 'تصادفی', 'rasta-commerce-core' ),
				),
			)
		);
		$this->add_control(
			'order',
			array(
				'label'   => esc_html__( 'جهت', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => esc_html__( 'نزولی', 'rasta-commerce-core' ),
					'ASC'  => esc_html__( 'صعودی', 'rasta-commerce-core' ),
				),
			)
		);
	}

	/**
	 * Fetch products from WooCommerce using widget settings.
	 *
	 * @param array<string, mixed> $settings Widget settings.
	 * @return WC_Product[]
	 */
	protected function get_products( $settings ) {
		if ( ! $this->has_woocommerce() ) {
			return array();
		}

		return wc_get_products(
			array(
				'limit'   => max( 1, absint( $settings['limit'] ) ),
				'status'  => 'publish',
				'orderby' => sanitize_key( $settings['orderby'] ),
				'order'   => 'ASC' === $settings['order'] ? 'ASC' : 'DESC',
			)
		);
	}
}

/**
 * Product grid widget.
 */
class Rasta_Commerce_Elementor_Product_Grid_Widget extends Rasta_Commerce_Elementor_Product_Query_Widget {

	public function get_name() { return 'rasta-product-grid'; }
	public function get_title() { return esc_html__( 'راستا: شبکه محصولات', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-products'; }
	public function get_keywords() { return array( 'rasta', 'woocommerce', 'product', 'grid', 'محصول' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_section_heading_controls(
			array(
				'eyebrow'     => esc_html__( 'منتخب فروشگاه', 'rasta-commerce-core' ),
				'title'       => esc_html__( 'محصولات پیشنهادی', 'rasta-commerce-core' ),
				'description' => esc_html__( 'انتخاب‌هایی برای شروع خرید.', 'rasta-commerce-core' ),
				'link_text'   => esc_html__( 'همه محصولات', 'rasta-commerce-core' ),
			)
		);
		$this->add_product_query_controls( 4 );
		$this->add_control(
			'columns',
			array(
				'label'   => esc_html__( 'تعداد ستون', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '4',
				'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
			)
		);
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$products = $this->get_products( $settings );
		?>
		<section class="rasta-ec-widget rasta-ec-product-section">
			<?php $this->render_section_heading( $settings ); ?>
			<?php if ( empty( $products ) ) : ?>
				<?php $this->render_empty_state( esc_html__( 'برای نمایش این ویجت، WooCommerce و حداقل یک محصول منتشرشده لازم است.', 'rasta-commerce-core' ) ); ?>
			<?php else : ?>
				<div class="rasta-ec-product-grid" style="--rasta-ec-columns: <?php echo esc_attr( $settings['columns'] ); ?>">
					<?php foreach ( $products as $product ) : ?><?php $this->render_product_card( $product ); ?><?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}

/**
 * Horizontal product rail widget.
 */
class Rasta_Commerce_Elementor_Product_Rail_Widget extends Rasta_Commerce_Elementor_Product_Query_Widget {

	public function get_name() { return 'rasta-product-rail'; }
	public function get_title() { return esc_html__( 'راستا: ریل محصولات', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-post-slider'; }
	public function get_keywords() { return array( 'rasta', 'woocommerce', 'product', 'rail', 'اسلایدر', 'محصول' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_section_heading_controls(
			array(
				'eyebrow'     => esc_html__( 'محبوب این هفته', 'rasta-commerce-core' ),
				'title'       => esc_html__( 'محصولاتی برای کشف بیشتر', 'rasta-commerce-core' ),
				'description' => esc_html__( 'ریل قابل اسکرول و لمسی برای محصولات.', 'rasta-commerce-core' ),
			)
		);
		$this->add_product_query_controls( 6 );
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$products = $this->get_products( $settings );
		?>
		<section class="rasta-ec-widget rasta-ec-product-section">
			<?php $this->render_section_heading( $settings ); ?>
			<?php if ( empty( $products ) ) : ?>
				<?php $this->render_empty_state( esc_html__( 'محصولی برای این ریل پیدا نشد.', 'rasta-commerce-core' ) ); ?>
			<?php else : ?>
				<div class="rasta-ec-product-rail">
					<?php foreach ( $products as $product ) : ?><?php $this->render_product_card( $product ); ?><?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}

/**
 * Product category grid widget.
 */
class Rasta_Commerce_Elementor_Category_Grid_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() { return 'rasta-category-grid'; }
	public function get_title() { return esc_html__( 'راستا: دسته‌بندی محصولات', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-folder-o'; }
	public function get_keywords() { return array( 'rasta', 'category', 'woocommerce', 'دسته‌بندی' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_section_heading_controls(
			array(
				'eyebrow'     => esc_html__( 'شروع کنید', 'rasta-commerce-core' ),
				'title'       => esc_html__( 'دسته‌بندی‌های فروشگاه', 'rasta-commerce-core' ),
				'description' => esc_html__( 'سریع‌تر به چیزی برسید که دنبالش هستید.', 'rasta-commerce-core' ),
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'تعداد دسته', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 12,
			)
		);
		$this->add_control(
			'columns',
			array(
				'label'   => esc_html__( 'تعداد ستون', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '6',
				'options' => array( '2' => '2', '3' => '3', '4' => '4', '6' => '6' ),
			)
		);
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			?>
			<section class="rasta-ec-widget"><?php $this->render_empty_state( esc_html__( 'WooCommerce برای نمایش دسته‌بندی‌ها فعال نیست.', 'rasta-commerce-core' ) ); ?></section>
			<?php
			return;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'      => max( 1, absint( $settings['limit'] ) ),
				'orderby'     => 'count',
				'order'       => 'DESC',
			)
		);
		?>
		<section class="rasta-ec-widget rasta-ec-category-section">
			<?php $this->render_section_heading( $settings ); ?>
			<?php if ( is_wp_error( $terms ) || empty( $terms ) ) : ?>
				<?php $this->render_empty_state( esc_html__( 'دسته‌بندی محصولی برای نمایش وجود ندارد.', 'rasta-commerce-core' ) ); ?>
			<?php else : ?>
				<div class="rasta-ec-category-grid" style="--rasta-ec-columns: <?php echo esc_attr( $settings['columns'] ); ?>">
					<?php foreach ( $terms as $term ) : ?>
						<?php
						$link = get_term_link( $term );
						if ( is_wp_error( $link ) ) {
							continue;
						}
							$image_id   = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
							$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
							$count_text = sprintf(
								/* translators: %s: number of products in the category. */
								_n( '%s محصول', '%s محصول', (int) $term->count, 'rasta-commerce-core' ),
								number_format_i18n( (int) $term->count )
							);
							?>
						<a class="rasta-ec-category-card" href="<?php echo esc_url( $link ); ?>">
							<span class="rasta-ec-category-card__image">
								<?php if ( $image_url ) : ?><img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" /><?php else : ?><span aria-hidden="true">✦</span><?php endif; ?>
							</span>
							<strong><?php echo esc_html( $term->name ); ?></strong>
								<small><?php echo esc_html( $count_text ); ?></small>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}

/**
 * Promotional banner widget.
 */
class Rasta_Commerce_Elementor_Promo_Banner_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() { return 'rasta-promo-banner'; }
	public function get_title() { return esc_html__( 'راستا: بنر تبلیغاتی', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'rasta', 'banner', 'promo', 'بنر', 'تبلیغ' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => esc_html__( 'برچسب', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'با انتخاب بهتر', 'rasta-commerce-core' ) ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => esc_html__( 'جزئیات کوچک، حس خوب بزرگ', 'rasta-commerce-core' ) ) );
		$this->add_control( 'description', array( 'label' => esc_html__( 'توضیح', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => esc_html__( 'پیشنهادهایی برای بهتر کردن خانه و زندگی روزمره.', 'rasta-commerce-core' ) ) );
		$this->add_control( 'button_text', array( 'label' => esc_html__( 'متن دکمه', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'پیشنهادهای ویژه', 'rasta-commerce-core' ) ) );
		$this->add_control( 'button_url', array( 'label' => esc_html__( 'لینک دکمه', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::URL ) );
		$this->add_control( 'image', array( 'label' => esc_html__( 'تصویر کنار بنر', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => esc_html__( 'استایل', 'rasta-commerce-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => esc_html__( 'رنگ پس‌زمینه', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#182033', 'selectors' => array( '{{WRAPPER}} .rasta-ec-promo' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'text_color', array( 'label' => esc_html__( 'رنگ متن', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => array( '{{WRAPPER}} .rasta-ec-promo' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="rasta-ec-widget rasta-ec-promo">
			<div class="rasta-ec-promo__copy">
				<?php if ( ! empty( $settings['eyebrow'] ) ) : ?><p class="rasta-ec-kicker rasta-ec-kicker--light"><?php echo esc_html( $settings['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( ! empty( $settings['title'] ) ) : ?><h2><?php echo esc_html( $settings['title'] ); ?></h2><?php endif; ?>
				<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				<?php if ( ! empty( $settings['button_text'] ) && ! empty( $settings['button_url']['url'] ) ) : ?><a class="rasta-ec-button rasta-ec-button--light" href="<?php echo esc_url( $settings['button_url']['url'] ); ?>"><?php echo esc_html( $settings['button_text'] ); ?><span aria-hidden="true">←</span></a><?php endif; ?>
			</div>
			<?php if ( ! empty( $settings['image']['url'] ) ) : ?><div class="rasta-ec-promo__media"><img src="<?php echo esc_url( $settings['image']['url'] ); ?>" alt="" loading="lazy" /></div><?php endif; ?>
		</section>
		<?php
	}
}

/**
 * Trust-strip widget.
 */
class Rasta_Commerce_Elementor_Trust_Strip_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() { return 'rasta-trust-strip'; }
	public function get_title() { return esc_html__( 'راستا: نوار اعتماد', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-shield'; }
	public function get_keywords() { return array( 'rasta', 'trust', 'feature', 'اعتماد', 'مزیت' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'آیتم‌ها', 'rasta-commerce-core' ) ) );
		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'icon', array( 'label' => esc_html__( 'آیکون', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-check', 'library' => 'fa-solid' ) ) );
		$repeater->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'ارسال سریع', 'rasta-commerce-core' ) ) );
		$repeater->add_control( 'text', array( 'label' => esc_html__( 'توضیح', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'تحویل امن و قابل پیگیری', 'rasta-commerce-core' ) ) );
		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'مزیت‌ها', 'rasta-commerce-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'title' => esc_html__( 'ارسال سریع', 'rasta-commerce-core' ), 'text' => esc_html__( 'تحویل امن و قابل پیگیری', 'rasta-commerce-core' ) ),
					array( 'title' => esc_html__( 'تضمین اصالت', 'rasta-commerce-core' ), 'text' => esc_html__( 'خرید با خیال راحت', 'rasta-commerce-core' ) ),
					array( 'title' => esc_html__( 'پشتیبانی انسانی', 'rasta-commerce-core' ), 'text' => esc_html__( 'کنار شما تا پایان خرید', 'rasta-commerce-core' ) ),
				),
				'title_field' => '{{{ title }}}',
			)
		);
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="rasta-ec-widget rasta-ec-trust-strip">
			<?php foreach ( $settings['items'] as $item ) : ?>
				<div class="rasta-ec-trust-strip__item">
					<span class="rasta-ec-trust-strip__icon"><?php $this->render_icon( $item['icon'] ); ?></span>
					<span><strong><?php echo esc_html( $item['title'] ); ?></strong><small><?php echo esc_html( $item['text'] ); ?></small></span>
				</div>
			<?php endforeach; ?>
		</section>
		<?php
	}
}

/**
 * Blog-grid widget.
 */
class Rasta_Commerce_Elementor_Blog_Grid_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() { return 'rasta-blog-grid'; }
	public function get_title() { return esc_html__( 'راستا: شبکه مجله', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-posts-grid'; }
	public function get_keywords() { return array( 'rasta', 'blog', 'post', 'مجله', 'وبلاگ' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_section_heading_controls(
			array(
				'eyebrow'     => esc_html__( 'مجله راستا', 'rasta-commerce-core' ),
				'title'       => esc_html__( 'برای انتخاب آگاهانه‌تر', 'rasta-commerce-core' ),
				'description' => esc_html__( 'راهنماها و ایده‌هایی برای بهتر خریدن.', 'rasta-commerce-core' ),
				'link_text'   => esc_html__( 'همه نوشته‌ها', 'rasta-commerce-core' ),
			)
		);
		$this->add_control( 'limit', array( 'label' => esc_html__( 'تعداد نوشته', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 12 ) );
		$this->add_control( 'columns', array( 'label' => esc_html__( 'تعداد ستون', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::SELECT, 'default' => '3', 'options' => array( '2' => '2', '3' => '3', '4' => '4' ) ) );
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$query    = new WP_Query(
			array(
				'posts_per_page'      => max( 1, absint( $settings['limit'] ) ),
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
			)
		);
		?>
		<section class="rasta-ec-widget rasta-ec-blog-section">
			<?php $this->render_section_heading( $settings ); ?>
			<?php if ( ! $query->have_posts() ) : ?>
				<?php $this->render_empty_state( esc_html__( 'نوشته‌ای برای نمایش وجود ندارد.', 'rasta-commerce-core' ) ); ?>
			<?php else : ?>
				<div class="rasta-ec-blog-grid" style="--rasta-ec-columns: <?php echo esc_attr( $settings['columns'] ); ?>">
					<?php while ( $query->have_posts() ) : ?>
						<?php $query->the_post(); ?>
						<article <?php post_class( 'rasta-ec-blog-card' ); ?>>
							<a class="rasta-ec-blog-card__image" href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) : ?><?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?><?php else : ?><span aria-hidden="true">✦</span><?php endif; ?>
							</a>
							<div>
								<small><?php echo esc_html( function_exists( 'rasta_get_the_jalali_date' ) ? rasta_get_the_jalali_date() : get_the_date() ); ?></small>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php endif; ?>
		</section>
		<?php
	}
}

/**
 * Feature-card widget.
 */
class Rasta_Commerce_Elementor_Feature_Card_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() { return 'rasta-feature-card'; }
	public function get_title() { return esc_html__( 'راستا: کارت ویژگی', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-info-circle-o'; }
	public function get_keywords() { return array( 'rasta', 'feature', 'card', 'ویژگی', 'کارت' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_control( 'icon', array( 'label' => esc_html__( 'آیکون', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-sparkles', 'library' => 'fa-solid' ) ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'تجربه‌ای بهتر از خرید', 'rasta-commerce-core' ) ) );
		$this->add_control( 'description', array( 'label' => esc_html__( 'توضیح', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => esc_html__( 'یک ویژگی کوتاه و روشن برای جلب اعتماد مشتری.', 'rasta-commerce-core' ) ) );
		$this->add_control( 'link_text', array( 'label' => esc_html__( 'متن لینک', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXT ) );
		$this->add_control( 'link_url', array( 'label' => esc_html__( 'لینک', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::URL, 'condition' => array( 'link_text!' => '' ) ) );
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<article class="rasta-ec-widget rasta-ec-feature-card">
			<span class="rasta-ec-feature-card__icon"><?php $this->render_icon( $settings['icon'] ); ?></span>
			<?php if ( ! empty( $settings['title'] ) ) : ?><h3><?php echo esc_html( $settings['title'] ); ?></h3><?php endif; ?>
			<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
			<?php if ( ! empty( $settings['link_text'] ) && ! empty( $settings['link_url']['url'] ) ) : ?><a class="rasta-ec-text-link" href="<?php echo esc_url( $settings['link_url']['url'] ); ?>"><?php echo esc_html( $settings['link_text'] ); ?><span aria-hidden="true">←</span></a><?php endif; ?>
		</article>
		<?php
	}
}

/**
 * FAQ widget using native details elements for accessibility.
 */
class Rasta_Commerce_Elementor_FAQ_Widget extends Rasta_Commerce_Elementor_Base_Widget {

	public function get_name() { return 'rasta-faq'; }
	public function get_title() { return esc_html__( 'راستا: پرسش‌های متداول', 'rasta-commerce-core' ); }
	public function get_icon() { return 'eicon-help-o'; }
	public function get_keywords() { return array( 'rasta', 'faq', 'accordion', 'پرسش', 'سوال' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'rasta-commerce-core' ) ) );
		$this->add_section_heading_controls(
			array(
				'eyebrow' => esc_html__( 'پیش از خرید', 'rasta-commerce-core' ),
				'title'   => esc_html__( 'پرسش‌های پرتکرار', 'rasta-commerce-core' ),
			)
		);
		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'question', array( 'label' => esc_html__( 'پرسش', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'زمان ارسال سفارش چقدر است؟', 'rasta-commerce-core' ) ) );
		$repeater->add_control( 'answer', array( 'label' => esc_html__( 'پاسخ', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::WYSIWYG, 'default' => esc_html__( 'زمان ارسال را بر اساس روش حمل و مقصد در صفحه محصول و پرداخت نمایش دهید.', 'rasta-commerce-core' ) ) );
		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'پرسش‌ها', 'rasta-commerce-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'question' => esc_html__( 'زمان ارسال سفارش چقدر است؟', 'rasta-commerce-core' ), 'answer' => esc_html__( 'زمان ارسال را بر اساس روش حمل و مقصد در صفحه محصول و پرداخت نمایش دهید.', 'rasta-commerce-core' ) ),
					array( 'question' => esc_html__( 'آیا امکان بازگشت کالا وجود دارد؟', 'rasta-commerce-core' ), 'answer' => esc_html__( 'شرایط بازگشت کالا را به‌صورت شفاف در یک برگه اختصاصی منتشر کنید.', 'rasta-commerce-core' ) ),
				),
				'title_field' => '{{{ question }}}',
			)
		);
		$this->add_control( 'open_first', array( 'label' => esc_html__( 'باز بودن اولین پاسخ', 'rasta-commerce-core' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->add_common_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="rasta-ec-widget rasta-ec-faq">
			<?php $this->render_section_heading( $settings ); ?>
			<div class="rasta-ec-faq__items">
				<?php foreach ( $settings['items'] as $index => $item ) : ?>
					<details<?php echo 0 === $index && 'yes' === $settings['open_first'] ? ' open' : ''; ?>>
						<summary><?php echo esc_html( $item['question'] ); ?><span aria-hidden="true">+</span></summary>
						<div><?php echo wp_kses_post( $item['answer'] ); ?></div>
					</details>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
