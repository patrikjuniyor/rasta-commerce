<?php
/**
 * Shared Elementor widget helpers.
 *
 * @package Rasta_Commerce_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class shared by all Rasta Commerce Elementor widgets.
 */
abstract class Rasta_Commerce_Elementor_Base_Widget extends \Elementor\Widget_Base {

	/**
	 * Return the Rasta widget category.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'rasta-commerce' );
	}

	/**
	 * Load the common widget stylesheet.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'rasta-commerce-elementor' );
	}

	/**
	 * Render an Elementor icon safely when an icon was selected.
	 *
	 * @param array<string, mixed> $icon Icon settings.
	 * @return void
	 */
	protected function render_icon( $icon ) {
		if ( empty( $icon['value'] ) ) {
			return;
		}

		\Elementor\Icons_Manager::render_icon(
			$icon,
			array(
				'aria-hidden' => 'true',
			)
		);
	}

	/**
	 * Add standard section-heading controls.
	 *
	 * @param array<string, mixed> $defaults Default field values.
	 * @return void
	 */
	protected function add_section_heading_controls( $defaults = array() ) {
		$defaults = wp_parse_args(
			$defaults,
			array(
				'eyebrow'     => '',
				'title'       => '',
				'description' => '',
				'link_text'   => '',
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => esc_html__( 'برچسب کوچک', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => $defaults['eyebrow'],
			)
		);
		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'عنوان', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => $defaults['title'],
			)
		);
		$this->add_control(
			'description',
			array(
				'label'   => esc_html__( 'توضیح', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => $defaults['description'],
				'rows'    => 3,
			)
		);
		$this->add_control(
			'link_text',
			array(
				'label'   => esc_html__( 'متن لینک بیشتر', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => $defaults['link_text'],
			)
		);
		$this->add_control(
			'link_url',
			array(
				'label'       => esc_html__( 'لینک بیشتر', 'rasta-commerce-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'condition'   => array(
					'link_text!' => '',
				),
			)
		);
	}

	/**
	 * Render a section heading configured by add_section_heading_controls().
	 *
	 * @param array<string, mixed> $settings Widget settings.
	 * @return void
	 */
	protected function render_section_heading( $settings ) {
		if ( empty( $settings['eyebrow'] ) && empty( $settings['title'] ) && empty( $settings['description'] ) && empty( $settings['link_text'] ) ) {
			return;
		}
		?>
		<div class="rasta-ec-section-heading">
			<div>
				<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
					<p class="rasta-ec-kicker"><?php echo esc_html( $settings['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $settings['title'] ) ) : ?>
					<h2><?php echo esc_html( $settings['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $settings['description'] ) ) : ?>
					<p class="rasta-ec-section-heading__description"><?php echo esc_html( $settings['description'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $settings['link_text'] ) && ! empty( $settings['link_url']['url'] ) ) : ?>
				<a class="rasta-ec-text-link" href="<?php echo esc_url( $settings['link_url']['url'] ); ?>"<?php echo ! empty( $settings['link_url']['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php echo esc_html( $settings['link_text'] ); ?>
					<span aria-hidden="true">←</span>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Add a limited set of style controls to a widget wrapper.
	 *
	 * @return void
	 */
	protected function add_common_style_controls() {
		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'استایل عمومی', 'rasta-commerce-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'background_color',
			array(
				'label'     => esc_html__( 'رنگ پس‌زمینه', 'rasta-commerce-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .rasta-ec-widget' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'padding',
			array(
				'label'      => esc_html__( 'فاصله داخلی', 'rasta-commerce-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .rasta-ec-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'widget_alignment',
			array(
				'label'   => esc_html__( 'تراز محتوا', 'rasta-commerce-core' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'right'  => array( 'title' => esc_html__( 'راست', 'rasta-commerce-core' ), 'icon' => 'eicon-text-align-right' ),
					'center' => array( 'title' => esc_html__( 'وسط', 'rasta-commerce-core' ), 'icon' => 'eicon-text-align-center' ),
					'left'   => array( 'title' => esc_html__( 'چپ', 'rasta-commerce-core' ), 'icon' => 'eicon-text-align-left' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .rasta-ec-widget' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render a simple, safe card for a WooCommerce product.
	 *
	 * @param WC_Product $product Product instance.
	 * @return void
	 */
	protected function render_product_card( $product ) {
		$image = $product->get_image(
			'woocommerce_thumbnail',
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		?>
		<article class="rasta-ec-product-card">
			<a class="rasta-ec-product-card__image" href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo wp_kses_post( $image ); ?>
			</a>
			<div class="rasta-ec-product-card__body">
				<p class="rasta-ec-product-card__category"><?php echo wp_kses_post( wc_get_product_category_list( $product->get_id(), '، ' ) ); ?></p>
				<h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
				<div class="rasta-ec-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
					<a class="rasta-ec-button add_to_cart_button ajax_add_to_cart" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-quantity="1">
						<?php esc_html_e( 'افزودن به سبد', 'rasta-commerce-core' ); ?>
					</a>
				<?php else : ?>
					<a class="rasta-ec-button" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'مشاهده محصول', 'rasta-commerce-core' ); ?></a>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}

	/**
	 * Render an empty state that is useful in Elementor preview and frontend.
	 *
	 * @param string $message Message to show.
	 * @return void
	 */
	protected function render_empty_state( $message ) {
		printf( '<p class="rasta-ec-empty">%s</p>', esc_html( $message ) );
	}

	/**
	 * Return whether WooCommerce product functions are available.
	 *
	 * @return bool
	 */
	protected function has_woocommerce() {
		return function_exists( 'wc_get_products' );
	}
}
