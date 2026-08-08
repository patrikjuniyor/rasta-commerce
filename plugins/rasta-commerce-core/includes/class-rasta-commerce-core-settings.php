<?php
/**
 * Persian admin controls for enabling Elementor storefront widgets.
 *
 * @package Rasta_Commerce_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage the Rasta Commerce Core widget modules without modifying Elementor itself.
 */
class Rasta_Commerce_Core_Settings {

	/**
	 * Option name used for enabled widget slugs.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'rasta_commerce_core_enabled_widgets';

	/**
	 * Register WordPress admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
	}

	/**
	 * Return enabled widget slugs, defaulting to every supported widget.
	 *
	 * @return string[]
	 */
	public static function get_enabled_widgets() {
		$defaults = array_keys( rasta_commerce_core_widget_definitions() );
		$saved    = get_option( self::OPTION_NAME, $defaults );

		return self::sanitize_widgets( $saved );
	}

	/**
	 * Register the Persian settings page under Appearance.
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_theme_page(
			esc_html__( 'هسته المنتور راستا', 'rasta-commerce-core' ),
			esc_html__( 'هسته راستا', 'rasta-commerce-core' ),
			'manage_options',
			'rasta-commerce-core',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register widget-module settings.
	 *
	 * @return void
	 */
	public static function register_setting() {
		register_setting(
			'rasta_commerce_core',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_widgets' ),
				'default'           => array_keys( rasta_commerce_core_widget_definitions() ),
			)
		);
	}

	/**
	 * Allow only known widget slugs. Empty selection is permitted for lean storefronts.
	 *
	 * @param mixed $value Submitted setting value.
	 * @return string[]
	 */
	public static function sanitize_widgets( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$allowed = array_keys( rasta_commerce_core_widget_definitions() );
		$widgets = array_map( 'sanitize_key', $value );
		$widgets = array_values( array_unique( array_intersect( $widgets, $allowed ) ) );

		return $widgets;
	}

	/**
	 * Render the Persian settings page.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$definitions = rasta_commerce_core_widget_definitions();
		$enabled     = self::get_enabled_widgets();
		?>
		<div class="wrap rasta-core-admin">
			<h1><?php esc_html_e( 'هسته Elementor راستا کامرس', 'rasta-commerce-core' ); ?></h1>
			<p class="description"><?php esc_html_e( 'ویجت‌های مورد نیاز دموی فروشگاهی را فعال یا غیرفعال کنید. این صفحه Elementor را تغییر نمی‌دهد و فقط ماژول‌های راستا را مدیریت می‌کند.', 'rasta-commerce-core' ); ?></p>
			<div class="rasta-core-admin__notice">
				<strong><?php esc_html_e( 'راهنما:', 'rasta-commerce-core' ); ?></strong>
				<?php esc_html_e( 'پس از ذخیره، ویرایشگر Elementor را یک‌بار بازخوانی کنید تا فهرست ویجت‌ها به‌روزرسانی شود.', 'rasta-commerce-core' ); ?>
			</div>
			<form method="post" action="options.php">
				<?php settings_fields( 'rasta_commerce_core' ); ?>
				<div class="rasta-core-admin__grid">
					<?php foreach ( $definitions as $slug => $definition ) : ?>
						<label class="rasta-core-admin__card">
							<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $enabled, true ) ); ?> />
							<span>
								<strong><?php echo esc_html( $definition['label'] ); ?></strong>
								<small><?php echo esc_html( $definition['description'] ); ?></small>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
				<?php submit_button( esc_html__( 'ذخیره ماژول‌ها', 'rasta-commerce-core' ) ); ?>
			</form>
			<style>
				.rasta-core-admin { max-width: 1000px; }
				.rasta-core-admin__notice { margin: 20px 0; padding: 14px 16px; border-right: 4px solid #315bd8; background: #f1f5ff; }
				.rasta-core-admin__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(255px, 1fr)); gap: 14px; }
				.rasta-core-admin__card { display: flex; gap: 11px; align-items: flex-start; padding: 16px; border: 1px solid #dce2ec; border-radius: 10px; background: #fff; }
				.rasta-core-admin__card input { margin: 4px 0 0; }
				.rasta-core-admin__card span { display: grid; gap: 4px; }
				.rasta-core-admin__card small { color: #667085; }
			</style>
		</div>
		<?php
	}
}
