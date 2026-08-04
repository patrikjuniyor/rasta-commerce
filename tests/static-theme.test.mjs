import assert from 'node:assert/strict';
import { existsSync, readFileSync, statSync } from 'node:fs';
import path from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const read = (file) => readFileSync(path.join(root, file), 'utf8');

const requiredThemeFiles = [
  'style.css',
  'rtl.css',
  'functions.php',
  'header.php',
  'footer.php',
  'front-page.php',
  'theme.json',
  'screenshot.png',
  'inc/ajax.php',
  'inc/jalali.php',
  'inc/woocommerce.php',
  'woocommerce/content-product.php',
  'assets/js/theme.js',
  'assets/images/hero-showcase.svg',
  'assets/fonts/Vazirmatn-Regular.woff2',
  'assets/fonts/OFL-Vazirmatn.txt',
  'languages/rasta-commerce.pot',
  'plugins/rasta-zarinpal-gateway/languages/rasta-zarinpal-gateway.pot',
  'marketplace/theme-child/rasta-commerce-child/style.css',
  'marketplace/help.pdf',
  'marketplace/import-files/rasta-commerce-starter.xml',
  'plugins/rasta-zarinpal-gateway/rasta-zarinpal-gateway.php',
  'plugins/rasta-zarinpal-gateway/includes/class-rasta-zarinpal-gateway.php',
  'tests/php/zarinpal-gateway-test.php',
];

test('includes the required WordPress theme files and visual assets', () => {
  requiredThemeFiles.forEach((file) => {
    const location = path.join(root, file);
    assert.ok(existsSync(location), `${file} should exist`);
    assert.ok(statSync(location).size > 0, `${file} should not be empty`);
  });
});

test('has valid, installable theme metadata', () => {
  const style = read('style.css');
  ['Theme Name: Rasta Commerce', 'Version: 1.3.0', 'Text Domain: rasta-commerce', 'License: GNU General Public License v2 or later'].forEach((metadata) => {
    assert.match(style, new RegExp(metadata.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
  });
  assert.match(style, /rtl-language-support/);
  assert.match(style, /Vazirmatn-Regular\.woff2/);
});

test('uses WordPress lifecycle hooks and declares WooCommerce support', () => {
  const setup = read('inc/setup.php');
  const woocommerce = read('inc/woocommerce.php');
  assert.match(setup, /add_theme_support\(\s*'woocommerce'/);
  assert.match(setup, /wc-product-gallery-zoom/);
  assert.match(setup, /wp_enqueue_script\(\s*'rasta-commerce'/);
  assert.match(woocommerce, /woocommerce_add_to_cart_fragments/);
  assert.match(woocommerce, /woocommerce_before_main_content/);
});

test('retains critical WordPress header and footer hooks', () => {
  const header = read('header.php');
  const footer = read('footer.php');
  assert.match(header, /wp_head\(\)/);
  assert.match(header, /wp_body_open\(\)/);
  assert.match(header, /body_class\(\)/);
  assert.match(footer, /wp_footer\(\)/);
});

test('uses logical CSS properties and responsive breakpoints for native RTL layouts', () => {
  const style = read('style.css');
  assert.match(style, /margin-inline/);
  assert.match(style, /padding-inline/);
  assert.match(style, /inset-inline/);
  assert.match(style, /@media \(width <= 900px\)/);
  assert.match(style, /@media \(width <= 640px\)/);
  assert.match(read('rtl.css'), /html\[dir='rtl'\]/);
});

test('protects the public product-search endpoint with a nonce and sanitation', () => {
  const ajax = read('inc/ajax.php');
  assert.match(ajax, /check_ajax_referer\(\s*'rasta_product_search'/);
  assert.match(ajax, /sanitize_text_field\(\s*wp_unslash\(/);
  assert.match(ajax, /wp_send_json_success/);
  assert.doesNotMatch(ajax, /\beval\s*\(/);
});

test('protects quick view, collection, and comparison requests with a separate nonce', () => {
  const ajax = read('inc/ajax.php');
  const setup = read('inc/setup.php');
  assert.match(ajax, /rasta_ajax_quick_view/);
  assert.match(ajax, /rasta_ajax_product_collection/);
  assert.match(ajax, /rasta_ajax_product_compare/);
  assert.match(ajax, /check_ajax_referer\(\s*'rasta_product_tools'/);
  assert.match(ajax, /rasta_ajax_product_ids/);
  assert.match(setup, /toolsNonce/);
});

test('ships a native Jalali frontend date layer', () => {
  const jalali = read('inc/jalali.php');
  const templateTags = read('inc/template-tags.php');
  assert.match(jalali, /function rasta_gregorian_to_jalali/);
  assert.match(jalali, /فروردین/);
  assert.match(templateTags, /rasta_get_the_jalali_date/);
});

test('ships product-discovery enhancements with opt-out controls', () => {
  const customizer = read('inc/customizer.php');
  const header = read('header.php');
  const footer = read('footer.php');
  const productCard = read('woocommerce/content-product.php');
  const woocommerce = read('inc/woocommerce.php');
  ['quick_view', 'compare', 'recently_viewed', 'sticky_cart', 'sale_countdown'].forEach((feature) => {
    assert.match(customizer, new RegExp(`rasta_enable_${feature}`));
  });
  assert.match(header, /data-rasta-drawer="wishlist"/);
  assert.match(header, /data-rasta-drawer="quick-view"/);
  assert.match(header, /data-rasta-drawer="compare"/);
  assert.match(footer, /data-compare-tray/);
  assert.match(productCard, /data-quick-view-product/);
  assert.match(productCard, /data-compare-product/);
  assert.match(woocommerce, /rasta_recently_viewed_placeholder/);
  assert.match(woocommerce, /rasta_render_sticky_add_to_cart/);
});

test('renders client-side search results without raw HTML injection', () => {
  const script = read('assets/js/theme.js');
  assert.doesNotMatch(script, /\.innerHTML\s*=/);
  assert.match(script, /textContent\s*=/);
  assert.match(script, /new URL\(value, window\.location\.origin\)/);
  assert.match(script, /AbortController/);
});

test('ships marketplace preparation assets without claiming a missing demo is complete', () => {
  const childTheme = read('marketplace/theme-child/rasta-commerce-child/style.css');
  const marketplaceScript = read('scripts/package-marketplace.mjs');
  const marketplaceReadme = read('marketplace/README.md');
  assert.match(childTheme, /Template: rasta-commerce/);
  assert.match(marketplaceScript, /MISSING-BEFORE-SUBMISSION/);
  assert.match(marketplaceScript, /installer\.php/);
  assert.match(marketplaceReadme, /marketplace:validate/);
  assert.match(read('marketplace/import-files/rasta-commerce-starter.xml'), /<wp:wxr_version>1\.2<\/wp:wxr_version>/);
});

test('ships a standalone ZarinPal v4 gateway with server-side verification safeguards', () => {
  const plugin = read('plugins/rasta-zarinpal-gateway/rasta-zarinpal-gateway.php');
  const gateway = read('plugins/rasta-zarinpal-gateway/includes/class-rasta-zarinpal-gateway.php');
  const packageScript = read('scripts/package-zarinpal-plugin.mjs');
  assert.match(plugin, /Plugin Name: Rasta ZarinPal Gateway for WooCommerce/);
  assert.match(plugin, /Requires Plugins: woocommerce/);
  assert.match(plugin, /woocommerce_api_wc_gateway_rasta_zarinpal/);
  assert.match(plugin, /rasta_zarinpal_gateway_callback_router/);
  assert.match(gateway, /\/pg\/v4\/payment\/request\.json/);
  assert.match(gateway, /\/pg\/v4\/payment\/verify\.json/);
  assert.match(gateway, /wp_remote_post/);
  assert.match(gateway, /hash_equals\( \$order->get_order_key\(\), \$order_key \)/);
  assert.match(gateway, /in_array\( \$code, array\( 100, 101 \), true \)/);
  assert.match(gateway, /payment_complete/);
  assert.doesNotMatch(gateway, /\bcurl_exec\s*\(/);
  assert.doesNotMatch(gateway, /\beval\s*\(/);
  assert.match(packageScript, /rasta-zarinpal-gateway/);
});

test('ships a parseable theme.json design system', () => {
  const json = JSON.parse(read('theme.json'));
  assert.equal(json.version, 3);
  assert.ok(json.settings.color.palette.length >= 3);
  assert.equal(json.settings.layout.wideSize, '1240px');
});
