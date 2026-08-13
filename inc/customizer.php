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

	/* ─── Appearance (dark mode) ────────────────────────────────────────── */

	$wp_customize->add_section(
		'rasta_appearance',
		array(
			'title'       => esc_html__( 'حالت نمایش', 'rasta-commerce' ),
			'description' => esc_html__( 'کنترل حالت روشن و تاریک فروشگاه.', 'rasta-commerce' ),
			'priority'    => 32,
		)
	);

	$wp_customize->add_setting(
		'rasta_enable_dark_mode',
		array(
			'default'           => false,
			'sanitize_callback' => 'rasta_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'rasta_enable_dark_mode',
		array(
			'label'       => esc_html__( 'فعال‌سازی حالت تاریک', 'rasta-commerce' ),
			'description' => esc_html__( 'دکمه‌ای برای تغییر حالت روشن/تاریک به هدر اضافه می‌کند و در نخستین بازدید، تنظیم سیستم کاربر را می‌خواند.', 'rasta-commerce' ),
			'section'     => 'rasta_appearance',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'rasta_enable_dark_mode_default',
		array(
			'default'           => false,
			'sanitize_callback' => 'rasta_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'rasta_enable_dark_mode_default',
		array(
			'label'       => esc_html__( 'حالت تاریک پیش‌فرض', 'rasta-commerce' ),
			'description' => esc_html__( 'اگر فعال باشد، بازدیدکنندگان تازه به‌صورت پیش‌فرض حالت تاریک را می‌بینند.', 'rasta-commerce' ),
			'section'     => 'rasta_appearance',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'rasta_shop_columns',
		array(
			'default'           => 4,
			'sanitize_callback' => 'rasta_sanitize_shop_columns',
		)
	);
	$wp_customize->add_control(
		'rasta_shop_columns',
		array(
			'label'       => esc_html__( 'تعداد ستون محصولات', 'rasta-commerce' ),
			'description' => esc_html__( 'تعداد ستون شبکه محصولات در صفحات فروشگاه (۲ تا ۵).', 'rasta-commerce' ),
			'section'     => 'rasta_appearance',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 2,
				'max'  => 5,
				'step' => 1,
			),
		)
	);

	/* ─── Footer ────────────────────────────────────────────────────────── */

	$wp_customize->add_section(
		'rasta_footer',
		array(
			'title'       => esc_html__( 'پایین صفحه', 'rasta-commerce' ),
			'description' => esc_html__( 'متن‌های بخش پایین صفحه را بدون کدنویسی ویرایش کنید.', 'rasta-commerce' ),
			'priority'    => 33,
		)
	);

	$wp_customize->add_setting(
		'rasta_footer_about',
		array(
			'default'           => esc_html__( 'یک تجربه‌ی تمیز، سریع و قابل اعتماد برای خرید آنلاین؛ از انتخاب تا رسیدن سفارش به دست شما.', 'rasta-commerce' ),
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'rasta_footer_about',
		array(
			'label'   => esc_html__( 'متن معرفی فروشگاه', 'rasta-commerce' ),
			'section' => 'rasta_footer',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'rasta_footer_copyright',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'rasta_footer_copyright',
		array(
			'label'       => esc_html__( 'متن کپی‌رایت', 'rasta-commerce' ),
			'description' => esc_html__( 'می‌توانید از {year} برای سال جاری و {site} برای نام سایت استفاده کنید. خالی بگذارید تا متن پیش‌فرض نمایش داده شود.', 'rasta-commerce' ),
			'section'     => 'rasta_footer',
			'type'        => 'text',
		)
	);

	/* ─── Announcement bar extras ──────────────────────────────────────── */

	$wp_customize->add_setting(
		'rasta_promo_link',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'rasta_promo_link',
		array(
			'label'       => esc_html__( 'نشانی پیوند نوار اطلاع‌رسانی', 'rasta-commerce' ),
			'description' => esc_html__( 'اختیاری؛ اگر خالی باشد پیوند «پیگیری سفارش» پیش‌فرض نمایش داده می‌شود.', 'rasta-commerce' ),
			'section'     => 'rasta_storefront',
			'type'        => 'url',
		)
	);

	$wp_customize->add_setting(
		'rasta_promo_link_text',
		array(
			'default'           => esc_html__( 'پیگیری سفارش', 'rasta-commerce' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'rasta_promo_link_text',
		array(
			'label'   => esc_html__( 'متن پیوند نوار اطلاع‌رسانی', 'rasta-commerce' ),
			'section' => 'rasta_storefront',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'rasta_enable_dismissible_promo',
		array(
			'default'           => false,
			'sanitize_callback' => 'rasta_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'rasta_enable_dismissible_promo',
		array(
			'label'       => esc_html__( 'نوار اطلاع‌رسانی قابل بستن', 'rasta-commerce' ),
			'description' => esc_html__( 'دکمه بستن به نوار اضافه می‌کند تا بازدیدکننده بتواند آن را مخفی کند.', 'rasta-commerce' ),
			'section'     => 'rasta_storefront_features',
			'type'        => 'checkbox',
		)
	);

	/* ─── Extra social networks ────────────────────────────────────────── */

	$extra_social_fields = array(
		'rasta_whatsapp_url' => esc_html__( 'نشانی واتساپ', 'rasta-commerce' ),
		'rasta_twitter_url'  => esc_html__( 'نشانی توییتر / ایکس', 'rasta-commerce' ),
		'rasta_aparat_url'   => esc_html__( 'نشانی آپارات', 'rasta-commerce' ),
	);

	foreach ( $extra_social_fields as $setting_id => $label ) {
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

	/* ─── Store state (maintenance mode) ───────────────────────────────── */

	$wp_customize->add_section(
		'rasta_store_state',
		array(
			'title'       => esc_html__( 'وضعیت فروشگاه', 'rasta-commerce' ),
			'description' => esc_html__( 'فروشگاه را موقتاً برای بازدیدکنندگان ببندید؛ مدیران و کاربران واردشده سایت را عادی می‌بینند.', 'rasta-commerce' ),
			'priority'    => 34,
		)
	);

	$wp_customize->add_setting(
		'rasta_enable_maintenance',
		array(
			'default'           => false,
			'sanitize_callback' => 'rasta_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'rasta_enable_maintenance',
		array(
			'label'       => esc_html__( 'فعال‌سازی حالت تعمیر', 'rasta-commerce' ),
			'description' => esc_html__( 'تا وقتی این گزینه روشن است، صفحه «به‌زودی بازمی‌گردیم» به بازدیدکنندگان نشان داده می‌شود.', 'rasta-commerce' ),
			'section'     => 'rasta_store_state',
			'type'        => 'checkbox',
		)
	);

	$maintenance_fields = array(
		'rasta_maintenance_headline' => array(
			'label'    => esc_html__( 'تیتر صفحه تعمیر', 'rasta-commerce' ),
			'default'  => esc_html__( 'فروشگاه به‌زودی بازمی‌گردد', 'rasta-commerce' ),
			'sanitize' => 'sanitize_text_field',
		),
		'rasta_maintenance_message'  => array(
			'label'    => esc_html__( 'متن صفحه تعمیر', 'rasta-commerce' ),
			'default'  => esc_html__( 'در حال به‌روزرسانی و آماده‌سازی فروشگاه هستیم. چند لحظه دیگر با تجربه‌ای بهتر بازمی‌گردیم.', 'rasta-commerce' ),
			'sanitize' => 'sanitize_textarea_field',
		),
	);

	foreach ( $maintenance_fields as $setting_id => $field ) {
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
				'section' => 'rasta_store_state',
				'type'    => 'rasta_maintenance_message' === $setting_id ? 'textarea' : 'text',
			)
		);
	}
}
add_action( 'customize_register', 'rasta_customize_register' );

/**
 * Sanitize the shop column count to a 2–5 range.
 *
 * @param mixed $value Candidate value.
 * @return int
 */
function rasta_sanitize_shop_columns( $value ) {
	return min( 5, max( 2, (int) $value ) );
}
