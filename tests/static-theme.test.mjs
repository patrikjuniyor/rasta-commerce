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
  'inc/woocommerce.php',
  'woocommerce/content-product.php',
  'assets/js/theme.js',
  'assets/images/hero-showcase.svg',
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
  ['Theme Name: Rasta Commerce', 'Version: 1.0.0', 'Text Domain: rasta-commerce', 'License: GNU General Public License v2 or later'].forEach((metadata) => {
    assert.match(style, new RegExp(metadata.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
  });
  assert.match(style, /rtl-language-support/);
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

test('renders client-side search results without raw HTML injection', () => {
  const script = read('assets/js/theme.js');
  assert.doesNotMatch(script, /\.innerHTML\s*=/);
  assert.match(script, /textContent\s*=/);
  assert.match(script, /new URL\(value, window\.location\.origin\)/);
  assert.match(script, /AbortController/);
});

test('ships a parseable theme.json design system', () => {
  const json = JSON.parse(read('theme.json'));
  assert.equal(json.version, 3);
  assert.ok(json.settings.color.palette.length >= 3);
  assert.equal(json.settings.layout.wideSize, '1240px');
});
