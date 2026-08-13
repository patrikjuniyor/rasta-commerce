<?php
/**
 * Store settings page (Settings API) for the built-in store.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const RASTA_STORE_SETTINGS_OPTION = 'rasta_store_settings';

/**
 * Default store settings.
 *
 * @return array<string, mixed>
 */
function rasta_store_settings_defaults() {
	return array(
		'admin_email'   => get_option( 'admin_email' ),
		'order_emails'  => true,
		'email_subject' => __( 'سفارش جدید در فروشگاه', 'rasta-commerce' ),
	);
}

/**
 * Return the merged store settings with defaults applied.
 *
 * @return array<string, mixed>
 */
function rasta_store_settings() {
	return wp_parse_args(
		(array) get_option( RASTA_STORE_SETTINGS_OPTION, array() ),
		rasta_store_settings_defaults()
	);
}

/**
 * Sanitize the store settings option on save.
 *
 * @param mixed $input Submitted value.
 * @return array<string, mixed>
 */
function rasta_sanitize_store_settings( $input ) {
	$input = is_array( $input ) ? $input : array();

	return array(
		'admin_email'   => isset( $input['admin_email'] ) ? sanitize_email( $input['admin_email'] ) : '',
		'order_emails'  => ! empty( $input['order_emails'] ),
		'email_subject' => isset( $input['email_subject'] ) ? sanitize_text_field( $input['email_subject'] ) : '',
	);
}

/**
 * Register the settings page and the underlying option.
 *
 * @return void
 */
function rasta_store_settings_init() {
	register_setting(
		'rasta_store_settings_group',
		RASTA_STORE_SETTINGS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'rasta_sanitize_store_settings',
			'default'           => rasta_store_settings_defaults(),
		)
	);
}
add_action( 'admin_init', 'rasta_store_settings_init' );

/**
 * Register the settings submenu under the store menu.
 *
 * @return void
 */
function rasta_store_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=rasta_product',
		esc_html__( 'تنظیمات فروشگاه', 'rasta-commerce' ),
		esc_html__( 'تنظیمات فروشگاه', 'rasta-commerce' ),
		'manage_options',
		'rasta-store-settings',
		'rasta_render_store_settings'
	);
}
add_action( 'admin_menu', 'rasta_store_settings_menu' );

/**
 * Render the store settings page.
 *
 * @return void
 */
function rasta_render_store_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = rasta_store_settings();

	if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		add_settings_error(
			'rasta_store_settings',
			'rasta_store_settings_saved',
			__( 'تنظیمات فروشگاه ذخیره شد.', 'rasta-commerce' ),
			'success'
		);
	}
	?>
	<div class="wrap rasta-settings">
		<h1><?php esc_html_e( 'تنظیمات فروشگاه', 'rasta-commerce' ); ?></h1>
		<?php settings_errors( 'rasta_store_settings' ); ?>
		<p class="description" style="max-width:640px;"><?php esc_html_e( 'تنظیمات عمومی فروشگاه داخلی راستا، از جمله اطلاع‌رسانی ایمیلی سفارش‌های جدید.', 'rasta-commerce' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'rasta_store_settings_group' ); ?>

			<div class="rasta-settings-card">
				<div class="rasta-settings-card__head">
					<span class="dashicons dashicons-email-alt"></span>
					<h2><?php esc_html_e( 'اطلاع‌رسانی ایمیلی', 'rasta-commerce' ); ?></h2>
				</div>
				<div class="rasta-settings-card__body">
					<div class="rasta-field">
						<label class="rasta-field__label" for="rasta-admin-email"><?php esc_html_e( 'ایمیل مدیریت فروشگاه', 'rasta-commerce' ); ?></label>
						<input
							type="email"
							class="regular-text"
							id="rasta-admin-email"
							name="<?php echo esc_attr( RASTA_STORE_SETTINGS_OPTION ); ?>[admin_email]"
							value="<?php echo esc_attr( $settings['admin_email'] ); ?>"
						/>
						<p class="description"><?php esc_html_e( 'ایمیل اطلاع‌رسانی سفارش‌های جدید به این نشانی ارسال می‌شود.', 'rasta-commerce' ); ?></p>
					</div>

					<div class="rasta-field">
						<label class="rasta-field__label" for="rasta-email-subject"><?php esc_html_e( 'موضوع ایمیل سفارش', 'rasta-commerce' ); ?></label>
						<input
							type="text"
							class="regular-text"
							id="rasta-email-subject"
							name="<?php echo esc_attr( RASTA_STORE_SETTINGS_OPTION ); ?>[email_subject]"
							value="<?php echo esc_attr( $settings['email_subject'] ); ?>"
						/>
					</div>

					<div class="rasta-field">
						<label class="rasta-field__toggle">
							<input
								type="checkbox"
								name="<?php echo esc_attr( RASTA_STORE_SETTINGS_OPTION ); ?>[order_emails]"
								value="1"
								<?php checked( $settings['order_emails'] ); ?>
							/>
							<span>
								<strong><?php esc_html_e( 'اطلاع‌رسانی سفارش‌های جدید', 'rasta-commerce' ); ?></strong>
								<small><?php esc_html_e( 'هنگام ثبت هر سفارش جدید، ایمیلی به مدیر فروشگاه ارسال شود.', 'rasta-commerce' ); ?></small>
							</span>
						</label>
					</div>
				</div>
			</div>

			<?php submit_button( esc_html__( 'ذخیره تنظیمات', 'rasta-commerce' ) ); ?>
		</form>
	</div>

	<style>
		.rasta-settings { max-width: 760px; }
		.rasta-settings-card { margin-block: 18px; background: #fff; border: 1px solid #dcdcde; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 2px rgb(0 0 0 / 4%); }
		.rasta-settings-card__head { display: flex; align-items: center; gap: 9px; padding: 14px 18px; border-bottom: 1px solid #f0f0f1; background: #fbfbfc; }
		.rasta-settings-card__head .dashicons { color: #315bd8; }
		.rasta-settings-card__head h2 { margin: 0; font-size: 14px; }
		.rasta-settings-card__body { padding: 8px 20px 18px; }
		.rasta-field { margin-block: 16px; }
		.rasta-field__label { display: block; margin-bottom: 6px; font-weight: 600; }
		.rasta-field .description { margin-top: 5px; }
		.rasta-field__toggle { display: flex; align-items: flex-start; gap: 10px; padding: 13px 14px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fafbfc; cursor: pointer; }
		.rasta-field__toggle input { margin-top: 3px; }
		.rasta-field__toggle span { display: grid; gap: 2px; }
		.rasta-field__toggle small { color: #646970; }
	</style>
	<?php
}
