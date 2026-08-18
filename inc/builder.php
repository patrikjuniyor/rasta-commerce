<?php
/**
 * Rasta Builder — a lightweight drag-and-drop page builder for the theme.
 *
 * Lets editors compose a page from registered building blocks. The layout is
 * stored as JSON in post meta (`_rasta_builder_data`) and rendered server-side
 * on the front end, so no JavaScript is required for visitors.
 *
 * @package Rasta_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const RASTA_BUILDER_META = '_rasta_builder_data';
const RASTA_BUILDER_NONCE = 'rasta_builder_save';

/* ─── Element registry ─────────────────────────────────────────────────── */

/**
 * Return the registered builder elements.
 *
 * Each element declares:
 *  - label       (string) human-readable name
 *  - icon        (string) dashicons class or emoji
 *  - category    (string) grouping
 *  - fields      (array) editable fields { key, label, type }
 *  - defaults    (array) default values keyed by field key
 *  - render      (callable) receives the props array, returns escaped HTML
 *
 * @return array<string, array<string, mixed>>
 */
function rasta_builder_elements() {
	$elements = array(
		'heading' => array(
			'label'    => __( 'تیتر', 'rasta-commerce' ),
			'icon'     => 'dashicons-heading',
			'category' => __( 'محتوا', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'title', 'label' => __( 'متن تیتر', 'rasta-commerce' ), 'type' => 'text' ),
				array( 'key' => 'level', 'label' => __( 'سطح', 'rasta-commerce' ), 'type' => 'select', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4' ) ),
				array( 'key' => 'align', 'label' => __( 'تراز', 'rasta-commerce' ), 'type' => 'select', 'options' => array( 'start' => __( 'راست', 'rasta-commerce' ), 'center' => __( 'وسط', 'rasta-commerce' ), 'end' => __( 'چپ', 'rasta-commerce' ) ) ),
			),
			'defaults' => array( 'title' => __( 'یک تیتر جدید', 'rasta-commerce' ), 'level' => 'h2', 'align' => 'center' ),
			'render'   => function ( $p ) {
				$level = in_array( $p['level'], array( 'h2', 'h3', 'h4' ), true ) ? $p['level'] : 'h2';
				$align = in_array( $p['align'], array( 'start', 'center', 'end' ), true ) ? $p['align'] : 'center';
				return sprintf( '<%1$s class="rb-heading" style="text-align:%2$s">%3$s</%1$s>', $level, esc_attr( $align ), esc_html( $p['title'] ) );
			},
		),
		'text' => array(
			'label'    => __( 'متن', 'rasta-commerce' ),
			'icon'     => 'dashicons-text',
			'category' => __( 'محتوا', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'content', 'label' => __( 'متن', 'rasta-commerce' ), 'type' => 'textarea' ),
			),
			'defaults' => array( 'content' => __( 'متن خود را اینجا بنویسید…', 'rasta-commerce' ) ),
			'render'   => function ( $p ) {
				return '<div class="rb-text">' . wpautop( esc_html( $p['content'] ) ) . '</div>';
			},
		),
		'button' => array(
			'label'    => __( 'دکمه', 'rasta-commerce' ),
			'icon'     => 'dashicons-button',
			'category' => __( 'محتوا', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'label', 'label' => __( 'متن دکمه', 'rasta-commerce' ), 'type' => 'text' ),
				array( 'key' => 'url', 'label' => __( 'پیوند', 'rasta-commerce' ), 'type' => 'url' ),
				array( 'key' => 'align', 'label' => __( 'تراز', 'rasta-commerce' ), 'type' => 'select', 'options' => array( 'start' => __( 'راست', 'rasta-commerce' ), 'center' => __( 'وسط', 'rasta-commerce' ), 'end' => __( 'چپ', 'rasta-commerce' ) ) ),
			),
			'defaults' => array( 'label' => __( 'بیشتر بدانید', 'rasta-commerce' ), 'url' => '#', 'align' => 'center' ),
			'render'   => function ( $p ) {
				$align = in_array( $p['align'], array( 'start', 'center', 'end' ), true ) ? $p['align'] : 'center';
				return sprintf( '<div class="rb-button" style="text-align:%1$s"><a class="rb-btn" href="%2$s">%3$s</a></div>', esc_attr( $align ), esc_url( $p['url'] ), esc_html( $p['label'] ) );
			},
		),
		'image' => array(
			'label'    => __( 'تصویر', 'rasta-commerce' ),
			'icon'     => 'dashicons-format-image',
			'category' => __( 'محتوا', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'url', 'label' => __( 'نشانی تصویر', 'rasta-commerce' ), 'type' => 'url' ),
				array( 'key' => 'alt', 'label' => __( 'متن جایگزین', 'rasta-commerce' ), 'type' => 'text' ),
			),
			'defaults' => array( 'url' => '', 'alt' => '' ),
			'render'   => function ( $p ) {
				if ( ! $p['url'] ) {
					return '';
				}
				return sprintf( '<div class="rb-image"><img src="%1$s" alt="%2$s" loading="lazy" /></div>', esc_url( $p['url'] ), esc_attr( $p['alt'] ) );
			},
		),
		'divider' => array(
			'label'    => __( 'جداکننده', 'rasta-commerce' ),
			'icon'     => 'dashicons-minus',
			'category' => __( 'چیدمان', 'rasta-commerce' ),
			'fields'   => array(),
			'defaults' => array(),
			'render'   => function () {
				return '<hr class="rb-divider" />';
			},
		),
		'spacer' => array(
			'label'    => __( 'فاصله', 'rasta-commerce' ),
			'icon'     => 'dashicons-editor-expand',
			'category' => __( 'چیدمان', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'height', 'label' => __( 'ارتفاع (پیکسل)', 'rasta-commerce' ), 'type' => 'number' ),
			),
			'defaults' => array( 'height' => 40 ),
			'render'   => function ( $p ) {
				$h = max( 8, absint( $p['height'] ) );
				return sprintf( '<div class="rb-spacer" style="height:%1$dpx" aria-hidden="true"></div>', $h );
			},
		),
		'cta' => array(
			'label'    => __( 'فراخوان اقدام', 'rasta-commerce' ),
			'icon'     => 'dashicons-megaphone',
			'category' => __( 'بخش‌ها', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'title', 'label' => __( 'تیتر', 'rasta-commerce' ), 'type' => 'text' ),
				array( 'key' => 'text', 'label' => __( 'توضیح', 'rasta-commerce' ), 'type' => 'textarea' ),
				array( 'key' => 'btn', 'label' => __( 'متن دکمه', 'rasta-commerce' ), 'type' => 'text' ),
				array( 'key' => 'url', 'label' => __( 'پیوند دکمه', 'rasta-commerce' ), 'type' => 'url' ),
			),
			'defaults' => array( 'title' => __( 'همین امروز شروع کنید', 'rasta-commerce' ), 'text' => __( 'توضیح کوتاهی دربارهٔ فراخوان اقدام.', 'rasta-commerce' ), 'btn' => __( 'شروع', 'rasta-commerce' ), 'url' => '#' ),
			'render'   => function ( $p ) {
				return '<div class="rb-cta"><h3>' . esc_html( $p['title'] ) . '</h3><p>' . esc_html( $p['text'] ) . '</p><a class="rb-btn rb-btn--light" href="' . esc_url( $p['url'] ) . '">' . esc_html( $p['btn'] ) . '</a></div>';
			},
		),
		'features' => array(
			'label'    => __( 'لیست ویژگی', 'rasta-commerce' ),
			'icon'     => 'dashicons-yes',
			'category' => __( 'بخش‌ها', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'title', 'label' => __( 'عنوان بخش', 'rasta-commerce' ), 'type' => 'text' ),
				array( 'key' => 'items', 'label' => __( 'ویژگی‌ها (هر خط: عنوان | توضیح)', 'rasta-commerce' ), 'type' => 'textarea' ),
			),
			'defaults' => array( 'title' => __( 'چرا ما؟', 'rasta-commerce' ), 'items' => "ارسال سریع | تحویل ۲۴ ساعته\nضمانت اصالت | تضمین کیفیت\nپشتیبانی واقعی | پاسخ‌گویی ۷ روز هفته" ),
			'render'   => function ( $p ) {
				$items = array_filter( array_map( 'trim', explode( "\n", (string) $p['items'] ) ) );
				$html  = '<div class="rb-features"><h3>' . esc_html( $p['title'] ) . '</h3><ul>';
				foreach ( $items as $line ) {
					$parts = array_map( 'trim', explode( '|', $line, 2 ) );
					$html .= '<li><strong>' . esc_html( $parts[0] ) . '</strong>';
					if ( isset( $parts[1] ) && '' !== $parts[1] ) {
						$html .= '<span>' . esc_html( $parts[1] ) . '</span>';
					}
					$html .= '</li>';
				}
				return $html . '</ul></div>';
			},
		),
		'products' => array(
			'label'    => __( 'شبکه محصول', 'rasta-commerce' ),
			'icon'     => 'dashicons-cart',
			'category' => __( 'فروشگاه', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'title', 'label' => __( 'عنوان بخش', 'rasta-commerce' ), 'type' => 'text' ),
				array( 'key' => 'limit', 'label' => __( 'تعداد محصول', 'rasta-commerce' ), 'type' => 'number' ),
			),
			'defaults' => array( 'title' => __( 'منتخب فروشگاه', 'rasta-commerce' ), 'limit' => 4 ),
			'render'   => function ( $p ) {
				$limit = max( 1, absint( $p['limit'] ) );
				$products = rasta_get_products( array( 'limit' => $limit ) );
				$html = '<div class="rb-products"><h3>' . esc_html( $p['title'] ) . '</h3>';
				if ( empty( $products ) ) {
					return $html . '<p class="rb-empty">' . esc_html__( 'هنوز محصولی منتشر نشده است.', 'rasta-commerce' ) . '</p></div>';
				}
				$html .= '<ul class="rb-products__grid">';
				foreach ( $products as $post ) {
					$payload = rasta_get_product_payload( $post );
					if ( empty( $payload ) ) {
						continue;
					}
					$html .= '<li><a class="rb-products__img" href="' . esc_url( $payload['url'] ) . '">';
					if ( $payload['image'] ) {
						$html .= '<img src="' . esc_url( $payload['image'] ) . '" alt="' . esc_attr( $payload['name'] ) . '" loading="lazy" />';
					}
					$html .= '</a><a class="rb-products__name" href="' . esc_url( $payload['url'] ) . '">' . esc_html( $payload['name'] ) . '</a>';
					$html .= '<span class="rb-products__price">' . esc_html( rasta_format_currency_plain( $payload['priceValue'] ) ) . '</span></li>';
				}
				return $html . '</ul></div>';
			},
		),
		'categories' => array(
			'label'    => __( 'دسته‌بندی', 'rasta-commerce' ),
			'icon'     => 'dashicons-category',
			'category' => __( 'فروشگاه', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'title', 'label' => __( 'عنوان بخش', 'rasta-commerce' ), 'type' => 'text' ),
			),
			'defaults' => array( 'title' => __( 'دسته‌بندی‌ها', 'rasta-commerce' ) ),
			'render'   => function ( $p ) {
				$terms = rasta_get_product_categories( 6 );
				$html  = '<div class="rb-categories"><h3>' . esc_html( $p['title'] ) . '</h3>';
				if ( empty( $terms ) ) {
					return $html . '<p class="rb-empty">' . esc_html__( 'دسته‌بندی‌ای یافت نشد.', 'rasta-commerce' ) . '</p></div>';
				}
				$html .= '<ul class="rb-categories__grid">';
				foreach ( $terms as $term ) {
					$link = get_term_link( $term );
					if ( is_wp_error( $link ) ) {
						continue;
					}
					$html .= '<li><a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></li>';
				}
				return $html . '</ul></div>';
			},
		),
		'testimonials' => array(
			'label'    => __( 'نظرات مشتریان', 'rasta-commerce' ),
			'icon'     => 'dashicons-format-quote',
			'category' => __( 'بخش‌ها', 'rasta-commerce' ),
			'fields'   => array(
				array( 'key' => 'title', 'label' => __( 'عنوان بخش', 'rasta-commerce' ), 'type' => 'text' ),
				array( 'key' => 'items', 'label' => __( 'نظرات (هر خط: نام | نظر)', 'rasta-commerce' ), 'type' => 'textarea' ),
			),
			'defaults' => array( 'title' => __( 'مشتریان چه می‌گویند؟', 'rasta-commerce' ), 'items' => "علی رضایی | تجربهٔ خرید عالی بود.\nسارا احمدی | ارسال سریع و کیفیت خوب." ),
			'render'   => function ( $p ) {
				$items = array_filter( array_map( 'trim', explode( "\n", (string) $p['items'] ) ) );
				$html  = '<div class="rb-testimonials"><h3>' . esc_html( $p['title'] ) . '</h3><div class="rb-testimonials__grid">';
				foreach ( $items as $line ) {
					$parts = array_map( 'trim', explode( '|', $line, 2 ) );
					$html .= '<blockquote><p>' . esc_html( isset( $parts[1] ) ? $parts[1] : $parts[0] ) . '</p><cite>' . esc_html( $parts[0] ) . '</cite></blockquote>';
				}
				return $html . '</div></div>';
			},
		),
	);

	/**
	 * Filter the registered builder elements.
	 *
	 * @param array<string, array<string, mixed>> $elements Element registry.
	 */
	return apply_filters( 'rasta_builder_elements', $elements );
}

/**
 * Get a single element definition by slug.
 *
 * @param string $slug Element slug.
 * @return array<string, mixed>|null
 */
function rasta_builder_get_element( $slug ) {
	$elements = rasta_builder_elements();
	return isset( $elements[ $slug ] ) ? $elements[ $slug ] : null;
}

/* ─── Layout rendering ─────────────────────────────────────────────────── */

/**
 * Render a builder layout (array of elements) to HTML.
 *
 * @param array $elements Layout: list of { type, props }.
 * @return string
 */
function rasta_builder_render_layout( $elements ) {
	$registry = rasta_builder_elements();
	$output   = '';

	foreach ( (array) $elements as $block ) {
		$type = isset( $block['type'] ) ? sanitize_key( $block['type'] ) : '';
		if ( ! isset( $registry[ $type ] ) ) {
			continue;
		}
		$def     = $registry[ $type ];
		$props   = isset( $block['props'] ) && is_array( $block['props'] ) ? $block['props'] : array();
		$props   = wp_parse_args( $props, $def['defaults'] );
		$rendered = is_callable( $def['render'] ) ? call_user_func( $def['render'], $props ) : '';

		if ( '' === $rendered ) {
			continue;
		}

		$output .= '<div class="rb-block rb-block--' . esc_attr( $type ) . '">' . $rendered . '</div>';
	}

	return $output;
}

/**
 * Sanitize a builder layout for storage.
 *
 * @param mixed $input Raw layout (JSON string or array).
 * @return array Sanitized layout.
 */
function rasta_builder_sanitize_layout( $input ) {
	if ( is_string( $input ) ) {
		$input = json_decode( $input, true );
	}

	if ( ! is_array( $input ) ) {
		return array();
	}

	$registry = rasta_builder_elements();
	$clean    = array();

	foreach ( $input as $block ) {
		if ( ! is_array( $block ) || empty( $block['type'] ) ) {
			continue;
		}
		$type = sanitize_key( $block['type'] );
		if ( ! isset( $registry[ $type ] ) ) {
			continue;
		}
		$def   = $registry[ $type ];
		$props = array();
		foreach ( $def['fields'] as $field ) {
			$key = $field['key'];
			$val = isset( $block['props'][ $key ] ) ? $block['props'][ $key ] : ( isset( $def['defaults'][ $key ] ) ? $def['defaults'][ $key ] : '' );

			if ( 'number' === $field['type'] ) {
				$props[ $key ] = absint( $val );
			} elseif ( 'url' === $field['type'] ) {
				$props[ $key ] = esc_url_raw( (string) $val );
			} elseif ( 'textarea' === $field['type'] ) {
				$props[ $key ] = sanitize_textarea_field( (string) $val );
			} elseif ( 'select' === $field['type'] ) {
				$allowed = array_keys( $field['options'] );
				$props[ $key ] = in_array( $val, $allowed, true ) ? $val : $def['defaults'][ $key ];
			} else {
				$props[ $key ] = sanitize_text_field( (string) $val );
			}
		}
		$clean[] = array(
			'type'  => $type,
			'props' => $props,
		);
	}

	return $clean;
}

/* ─── Meta box (admin editor) ──────────────────────────────────────────── */

/**
 * Register the builder meta box on supported post types.
 *
 * @return void
 */
function rasta_builder_register_metabox() {
	$post_types = apply_filters( 'rasta_builder_post_types', array( 'page', 'post', 'rasta_product' ) );

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'rasta-builder',
			__( 'راستا ساز — صفحه‌ساز', 'rasta-commerce' ),
			'rasta_builder_metabox_html',
			$post_type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'rasta_builder_register_metabox' );

/**
 * Render the builder meta box.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function rasta_builder_metabox_html( $post ) {
	wp_nonce_field( RASTA_BUILDER_NONCE, 'rasta_builder_nonce' );

	$layout = get_post_meta( $post->ID, RASTA_BUILDER_META, true );
	$layout = is_array( $layout ) ? $layout : array();
	$elements = rasta_builder_elements();
	?>
	<div class="rb-admin" data-rb-admin>
		<div class="rb-admin__toolbar">
			<label class="rb-admin__toggle">
				<input type="checkbox" name="rasta_builder_enabled" value="1" <?php checked( ! empty( $layout ) ); ?> data-rb-enabled />
				<?php esc_html_e( 'فعال‌سازی صفحه‌ساز برای این صفحه', 'rasta-commerce' ); ?>
			</label>
			<span class="rb-admin__hint"><?php esc_html_e( 'المنت‌ها را از فهرست زیر بکشید و در چیدمان رها کنید.', 'rasta-commerce' ); ?></span>
		</div>

		<div class="rb-admin__cols">
			<div class="rb-admin__palette">
				<?php
				$cats = array();
				foreach ( $elements as $slug => $def ) {
					$cats[ $def['category'] ][] = $slug;
				}
				foreach ( $cats as $cat => $slugs ) :
					?>
					<div class="rb-admin__cat-title"><?php echo esc_html( $cat ); ?></div>
					<div class="rb-admin__palette-grid">
						<?php foreach ( $slugs as $slug ) : ?>
							<div class="rb-pitem" draggable="true" data-rb-type="<?php echo esc_attr( $slug ); ?>">
								<span class="dashicons <?php echo esc_attr( $elements[ $slug ]['icon'] ); ?>"></span>
								<span><?php echo esc_html( $elements[ $slug ]['label'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="rb-admin__canvas" data-rb-canvas>
				<div class="rb-admin__canvas-empty" data-rb-empty>
					<?php esc_html_e( 'المنت‌ها را اینجا رها کنید', 'rasta-commerce' ); ?>
				</div>
				<?php foreach ( $layout as $index => $block ) : ?>
					<?php rasta_builder_metabox_block( $block, $index ); ?>
				<?php endforeach; ?>
			</div>
		</div>

		<input type="hidden" name="rasta_builder_data" data-rb-input value="<?php echo esc_attr( wp_json_encode( $layout ) ); ?>" />

		<script type="application/json" data-rb-schema>
			<?php echo wp_json_encode( rasta_builder_schema_for_js() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script. ?>
		</script>
	</div>
	<?php
}

/**
 * Build a JS-friendly schema of the element registry.
 *
 * @return array
 */
function rasta_builder_schema_for_js() {
	$out = array();
	foreach ( rasta_builder_elements() as $slug => $def ) {
		$out[ $slug ] = array(
			'label'    => $def['label'],
			'icon'     => $def['icon'],
			'category' => $def['category'],
			'fields'   => $def['fields'],
			'defaults' => $def['defaults'],
		);
	}
	return $out;
}

/**
 * Output a single builder block row in the admin canvas.
 *
 * @param array $block Block data.
 * @param int   $index Block index.
 * @return void
 */
function rasta_builder_metabox_block( $block, $index ) {
	$type = isset( $block['type'] ) ? sanitize_key( $block['type'] ) : '';
	$def  = rasta_builder_get_element( $type );
	if ( ! $def ) {
		return;
	}
	$props = wp_parse_args( isset( $block['props'] ) ? $block['props'] : array(), $def['defaults'] );
	?>
	<div class="rb-block-item" draggable="true" data-rb-block data-rb-type="<?php echo esc_attr( $type ); ?>" data-rb-index="<?php echo esc_attr( $index ); ?>">
		<div class="rb-block-item__head">
			<span class="dashicons <?php echo esc_attr( $def['icon'] ); ?>"></span>
			<strong><?php echo esc_html( $def['label'] ); ?></strong>
			<span class="rb-block-item__tools">
				<button type="button" class="rb-tool" data-rb-up title="<?php esc_attr_e( 'بالا', 'rasta-commerce' ); ?>">↑</button>
				<button type="button" class="rb-tool" data-rb-down title="<?php esc_attr_e( 'پایین', 'rasta-commerce' ); ?>">↓</button>
				<button type="button" class="rb-tool rb-tool--danger" data-rb-del title="<?php esc_attr_e( 'حذف', 'rasta-commerce' ); ?>">✕</button>
			</span>
		</div>
		<div class="rb-block-item__fields">
			<?php foreach ( $def['fields'] as $field ) : ?>
				<?php $val = isset( $props[ $field['key'] ] ) ? $props[ $field['key'] ] : ''; ?>
				<label class="rb-field">
					<span><?php echo esc_html( $field['label'] ); ?></span>
					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea data-rb-prop="<?php echo esc_attr( $field['key'] ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
					<?php elseif ( 'select' === $field['type'] ) : ?>
						<select data-rb-prop="<?php echo esc_attr( $field['key'] ); ?>">
							<?php foreach ( $field['options'] as $ov => $ol ) : ?>
								<option value="<?php echo esc_attr( $ov ); ?>" <?php selected( $val, $ov ); ?>><?php echo esc_html( $ol ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'number' === $field['type'] ) : ?>
						<input type="number" data-rb-prop="<?php echo esc_attr( $field['key'] ); ?>" value="<?php echo esc_attr( $val ); ?>" min="1" />
					<?php elseif ( 'url' === $field['type'] ) : ?>
						<input type="url" data-rb-prop="<?php echo esc_attr( $field['key'] ); ?>" value="<?php echo esc_attr( $val ); ?>" />
					<?php else : ?>
						<input type="text" data-rb-prop="<?php echo esc_attr( $field['key'] ); ?>" value="<?php echo esc_attr( $val ); ?>" />
					<?php endif; ?>
				</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Save the builder layout when the post is saved.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function rasta_builder_save( $post_id ) {
	if ( ! isset( $_POST['rasta_builder_nonce'] ) || ! wp_verify_nonce( $_POST['rasta_builder_nonce'], RASTA_BUILDER_NONCE ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$enabled = isset( $_POST['rasta_builder_enabled'] );
	$raw     = isset( $_POST['rasta_builder_data'] ) ? wp_unslash( $_POST['rasta_builder_data'] ) : '';

	if ( $enabled ) {
		$layout = rasta_builder_sanitize_layout( $raw );
		if ( ! empty( $layout ) ) {
			update_post_meta( $post_id, RASTA_BUILDER_META, $layout );
		} else {
			delete_post_meta( $post_id, RASTA_BUILDER_META );
		}
	} else {
		delete_post_meta( $post_id, RASTA_BUILDER_META );
	}
}
add_action( 'save_post', 'rasta_builder_save' );

/* ─── Front-end rendering ──────────────────────────────────────────────── */

/**
 * Replace the post content with the builder layout when one exists.
 *
 * @param string $content Original post content.
 * @return string
 */
function rasta_builder_render_content( $content ) {
	if ( is_admin() || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id = get_the_ID();
	$layout  = get_post_meta( $post_id, RASTA_BUILDER_META, true );

	if ( empty( $layout ) ) {
		return $content;
	}

	return rasta_builder_render_layout( $layout );
}
add_filter( 'the_content', 'rasta_builder_render_content', 9 );

/* ─── Shortcode ────────────────────────────────────────────────────────── */

/**
 * Register the builder shortcode.
 *
 * @return void
 */
function rasta_builder_register_shortcode() {
	add_shortcode( 'rasta_builder', 'rasta_builder_shortcode' );
}
add_action( 'init', 'rasta_builder_register_shortcode' );

/**
 * Render a saved builder layout via shortcode.
 *
 * Usage: [rasta_builder id="123"] renders the layout of page/post 123.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function rasta_builder_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'rasta_builder' );
	$id   = absint( $atts['id'] );

	if ( ! $id ) {
		return '';
	}

	$layout = get_post_meta( $id, RASTA_BUILDER_META, true );
	if ( empty( $layout ) ) {
		return '';
	}

	return rasta_builder_render_layout( $layout );
}

/* ─── Admin assets ─────────────────────────────────────────────────────── */

/**
 * Enqueue builder admin assets on the post edit screen.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function rasta_builder_admin_assets( $hook_suffix ) {
	if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'page', 'post', 'rasta_product' ), true ) ) {
		return;
	}

	wp_enqueue_style( 'rasta-builder-admin', RASTA_URI . '/assets/css/builder-admin.css', array(), RASTA_VERSION );
	wp_enqueue_script( 'rasta-builder-admin', RASTA_URI . '/assets/js/builder-admin.js', array(), RASTA_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'rasta_builder_admin_assets' );

/* ─── Front-end styles ─────────────────────────────────────────────────── */

/**
 * Enqueue builder front-end styles.
 *
 * @return void
 */
function rasta_builder_frontend_styles() {
	wp_enqueue_style( 'rasta-builder', RASTA_URI . '/assets/css/builder.css', array( 'rasta-commerce' ), RASTA_VERSION );
}
add_action( 'wp_enqueue_scripts', 'rasta_builder_frontend_styles' );
