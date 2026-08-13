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
		<p class="description"><?php esc_html_e( 'تنظیمات عمومی فروشگاه داخلی راستا، از جمله اطلاع‌رسانی ایمیلی سفارش‌های جدید.', 'rasta-commerce' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'rasta_store_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="rasta-admin-email"><?php esc_html_e( 'ایمیل مدیریت فروشگاه', 'rasta-commerce' ); ?></label>
					</th>
					<td>
						<input
							type="email"
							class="regular-text"
							id="rasta-admin-email"
							name="<?php echo esc_attr( RASTA_STORE_SETTINGS_OPTION ); ?>[admin_email]"
							value="<?php echo esc_attr( $settings['admin_email'] ); ?>"
						/>
						<p class="description"><?php esc_html_e( 'ایمیل اطلاع‌رسانی سفارش‌های جدید به این نشانی ارسال می‌شود.', 'rasta-commerce' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'اطلاع‌رسانی سفارش', 'rasta-commerce' ); ?></th>
					<td>
						<label>
							<input
								type="checkbox"
								name="<?php echo esc_attr( RASTA_STORE_SETTINGS_OPTION ); ?>[order_emails]"
								value="1"
								<?php checked( $settings['order_emails'] ); ?>
							/>
							<?php esc_html_e( 'هنگام ثبت هر سفارش جدید، ایمیلی به مدیر فروشگاه ارسال شود.', 'rasta-commerce' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="rasta-email-subject"><?php esc_html_e( 'موضوع ایمیل سفارش', 'rasta-commerce' ); ?></label>
					</th>
					<td>
						<input
							type="text"
							class="regular-text"
							id="rasta-email-subject"
							name="<?php echo esc_attr( RASTA_STORE_SETTINGS_OPTION ); ?>[email_subject]"
							value="<?php echo esc_attr( $settings['email_subject'] ); ?>"
						/>
					</td>
				</tr>
			</table>
			<?php submit_button( esc_html__( 'ذخیره تنظیمات', 'rasta-commerce' ) ); ?>
		</form>
	</div>
	<?php
}
