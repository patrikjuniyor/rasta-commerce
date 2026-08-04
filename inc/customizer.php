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
 * Return whether an optional storefront enhancement is enabled.
 *
 * @param string $feature Feature slug without the rasta_enable_ prefix.
 * @param bool   $default Default value when the setting was never saved.
 * @return bool
 */
function rasta_feature_enabled( $feature, $default = true ) {
	return (bool) get_theme_mod( 'rasta_enable_' . sanitize_key( $feature ), $default );
}

/**
 * Sanitize a non-negative whole number.
 *
 * @param mixed $value Candidate value.
 * @return int
 */
function rasta_sanitize_non_negative_integer( $value ) {
	return max( 0, absint( $value ) );
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

	$wp_customize->add_section(
		'rasta_storefront_features',
		array(
			'title'       => esc_html__( 'ابزارهای خرید راستا', 'rasta-commerce' ),
			'description' => esc_html__( 'ویژگی‌های سبک و اختیاری برای کشف محصول را فعال یا غیرفعال کنید. برای منطق پیچیده‌ی قیمت، پرداخت، ارسال یا عضویت از افزونه‌ی تخصصی استفاده کنید.', 'rasta-commerce' ),
			'priority'    => 31,
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

	$feature_toggles = array(
		'rasta_enable_quick_view'      => esc_html__( 'نمایش سریع محصول', 'rasta-commerce' ),
		'rasta_enable_compare'         => esc_html__( 'مقایسه محصول', 'rasta-commerce' ),
		'rasta_enable_recently_viewed' => esc_html__( 'محصولات اخیراً دیده‌شده', 'rasta-commerce' ),
		'rasta_enable_sticky_cart'     => esc_html__( 'نوار چسبان افزودن به سبد', 'rasta-commerce' ),
		'rasta_enable_sale_countdown'  => esc_html__( 'شمارش‌گر پایان تخفیف', 'rasta-commerce' ),
	);

	foreach ( $feature_toggles as $setting_id => $label ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => true,
				'sanitize_callback' => 'rasta_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => $label,
				'section' => 'rasta_storefront_features',
				'type'    => 'checkbox',
			)
		);
	}

	$number_fields = array(
		'rasta_newness_days' => array(
			'label'       => esc_html__( 'نمایش نشان «تازه» تا چند روز', 'rasta-commerce' ),
			'default'     => 30,
			'max'         => 365,
			'description' => esc_html__( 'برای غیرفعال شدن این نشان، عدد صفر وارد کنید.', 'rasta-commerce' ),
		),
		'rasta_low_stock_threshold' => array(
			'label'       => esc_html__( 'آستانه هشدار موجودی کم', 'rasta-commerce' ),
			'default'     => 3,
			'max'         => 1000,
			'description' => esc_html__( 'اگر مدیریت موجودی فعال باشد، تعداد کمتر یا مساوی این مقدار هشدار می‌گیرد. صفر یعنی غیرفعال.', 'rasta-commerce' ),
		),
		'rasta_free_shipping_threshold' => array(
			'label'       => esc_html__( 'حداقل مبلغ ارسال رایگان', 'rasta-commerce' ),
			'default'     => 0,
			'max'         => 1000000000,
			'description' => esc_html__( 'به واحد پول فروشگاه وارد کنید. صفر یعنی نوار پیشرفت ارسال رایگان در سبد نمایش داده نشود.', 'rasta-commerce' ),
		),
	);

	foreach ( $number_fields as $setting_id => $field ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'rasta_sanitize_non_negative_integer',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'       => $field['label'],
				'description' => $field['description'],
				'section'     => 'rasta_storefront_features',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => $field['max'],
					'step' => 1,
				),
			)
		);
	}
}
add_action( 'customize_register', 'rasta_customize_register' );
