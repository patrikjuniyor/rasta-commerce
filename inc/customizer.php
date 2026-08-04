<?php
/**
 * Theme Customizer options.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a hexadecimal brand color.
 *
 * @param string $value Candidate color.
 * @return string|null
 */
function rasta_sanitize_brand_color( $value ) {
	return sanitize_hex_color( $value );
}

/**
 * Sanitize a boolean checkbox setting.
 *
 * @param mixed $checked Submitted value.
 * @return bool
 */
function rasta_sanitize_checkbox( $checked ) {
	return (bool) $checked;
}

/**
 * Return a Customizer setting, falling back for empty values.
 *
 * @param string $key     Theme mod key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function rasta_get_mod( $key, $default = '' ) {
	$value = get_theme_mod( $key, $default );

	return '' === $value || null === $value ? $default : $value;
}

/**
 * Register focused storefront customization controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function rasta_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'rasta_storefront',
		array(
			'title'       => esc_html__( 'ویترین راستا', 'rasta-commerce' ),
			'description' => esc_html__( 'متن و رنگ‌های اصلی فروشگاه را بدون نیاز به کدنویسی تغییر دهید.', 'rasta-commerce' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_setting(
		'rasta_accent_color',
		array(
			'default'           => '#f25c54',
			'sanitize_callback' => 'rasta_sanitize_brand_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'rasta_accent_color',
			array(
				'label'   => esc_html__( 'رنگ تأکیدی', 'rasta-commerce' ),
				'section' => 'rasta_storefront',
			)
		)
	);

	$wp_customize->add_setting(
		'rasta_accent_alt_color',
		array(
			'default'           => '#315bd8',
			'sanitize_callback' => 'rasta_sanitize_brand_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'rasta_accent_alt_color',
			array(
				'label'   => esc_html__( 'رنگ ثانویه', 'rasta-commerce' ),
				'section' => 'rasta_storefront',
			)
		)
	);

	$fields = array(
		'rasta_promo_text' => array(
			'label'    => esc_html__( 'نوار اطلاع‌رسانی', 'rasta-commerce' ),
			'default'  => esc_html__( 'ارسال رایگان برای سفارش‌های بالای ۲ میلیون تومان', 'rasta-commerce' ),
			'sanitize' => 'sanitize_text_field',
		),
		'rasta_hero_eyebrow' => array(
			'label'    => esc_html__( 'برچسب بالای قهرمان', 'rasta-commerce' ),
			'default'  => esc_html__( 'انتخاب هوشمند، خرید آسوده', 'rasta-commerce' ),
			'sanitize' => 'sanitize_text_field',
		),
		'rasta_hero_title' => array(
			'label'    => esc_html__( 'تیتر اصلی صفحه نخست', 'rasta-commerce' ),
			'default'  => esc_html__( 'چیزهای خوب، برای زندگیِ خوب', 'rasta-commerce' ),
			'sanitize' => 'sanitize_text_field',
		),
		'rasta_hero_text' => array(
			'label'    => esc_html__( 'توضیح صفحه نخست', 'rasta-commerce' ),
			'default'  => esc_html__( 'یک ویترین خوش‌ساخت برای پیدا کردن محصولاتی که هر روزتان را ساده‌تر و زیباتر می‌کنند.', 'rasta-commerce' ),
			'sanitize' => 'sanitize_textarea_field',
		),
		'rasta_hero_cta' => array(
			'label'    => esc_html__( 'متن دکمه اصلی', 'rasta-commerce' ),
			'default'  => esc_html__( 'مشاهده فروشگاه', 'rasta-commerce' ),
			'sanitize' => 'sanitize_text_field',
		),
	);

	foreach ( $fields as $setting_id => $field ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => $field['sanitize'],
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => $field['label'],
				'section' => 'rasta_storefront',
				'type'    => 'rasta_hero_text' === $setting_id ? 'textarea' : 'text',
			)
		);
	}

	$wp_customize->add_setting(
		'rasta_hero_image',
		array(
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'rasta_hero_image',
			array(
				'label'       => esc_html__( 'تصویر قهرمان (اختیاری)', 'rasta-commerce' ),
				'description' => esc_html__( 'اگر تصویری انتخاب نکنید، تصویرسازی اختصاصی خود قالب نمایش داده می‌شود.', 'rasta-commerce' ),
				'section'     => 'rasta_storefront',
			)
		)
	);

	$social_fields = array(
		'rasta_instagram_url' => esc_html__( 'نشانی اینستاگرام', 'rasta-commerce' ),
		'rasta_telegram_url'  => esc_html__( 'نشانی تلگرام', 'rasta-commerce' ),
		'rasta_linkedin_url'  => esc_html__( 'نشانی لینکدین', 'rasta-commerce' ),
	);

	foreach ( $social_fields as $setting_id => $label ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'       => $label,
				'description' => esc_html__( 'اختیاری؛ فقط لینک‌های واردشده در پایین صفحه نمایش داده می‌شوند.', 'rasta-commerce' ),
				'section'     => 'rasta_storefront',
				'type'        => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'rasta_customize_register' );
