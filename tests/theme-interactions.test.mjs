import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import path from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';
import { JSDOM } from 'jsdom';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const themeScript = readFileSync(path.join(root, 'assets/js/theme.js'), 'utf8');

const createThemeDom = () => {
  const dom = new JSDOM(
    `<!doctype html>
    <html lang="fa" dir="rtl"><body>
      <button id="open-search" data-rasta-open="search">باز کردن جست‌وجو</button>
      <button id="open-cart" data-rasta-open="cart">باز کردن سبد</button>
      <button id="menu" data-rasta-menu-toggle aria-expanded="false">منو</button>
      <nav data-rasta-nav><a href="#test">خانه</a></nav>
      <div data-rasta-backdrop hidden></div>
      <aside data-rasta-drawer="search" aria-hidden="true" tabindex="-1">
        <button data-rasta-close>بستن</button>
        <input data-product-search />
      </aside>
      <aside data-rasta-drawer="cart" aria-hidden="true" tabindex="-1"><button data-rasta-close>بستن</button></aside>
      <aside data-rasta-drawer="quick-view" aria-hidden="true" tabindex="-1"><button data-rasta-close>بستن</button><div data-quick-view-content></div></aside>
      <aside data-rasta-drawer="compare" aria-hidden="true" tabindex="-1"><button data-rasta-close>بستن</button><div data-compare-content></div></aside>
      <button data-wishlist-product="42" aria-pressed="false">علاقه‌مندی</button>
      <button data-quick-view-product="99">نمایش سریع</button>
      <button data-compare-product="42" aria-pressed="false">مقایسه</button>
      <section data-compare-tray hidden><span data-compare-summary></span><div data-compare-tray-items></div><button data-compare-open>باز کردن مقایسه</button><button data-compare-clear>پاک کردن</button></section>
      <button data-scroll-top>بالا</button>
      <div data-rasta-toast></div>
    </body></html>`,
    {
      url: 'https://shop.test/',
      runScripts: 'outside-only',
    }
  );

  dom.window.rastaTheme = {
    ajaxUrl: 'https://shop.test/wp-admin/admin-ajax.php',
    toolsNonce: 'test-tools-nonce',
    features: {
      quickView: true,
      compare: true,
      recentlyViewed: false,
      stickyCart: false,
      saleCountdown: false,
    },
    strings: {
      addedToCart: 'محصول به سبد خرید اضافه شد.',
      loadingProduct: 'در حال آماده‌سازی محصول…',
      compareEmpty: 'برای مقایسه، حداقل دو محصول انتخاب کنید.',
      productsInComparison: 'محصول برای مقایسه',
      remove: 'حذف',
    },
  };
  dom.window.fetch = async (_url, options) => {
    const body = new URLSearchParams(options.body);
    const action = body.get('action');
    const product = {
      id: 42,
      name: '<محصول امن>',
      url: 'https://shop.test/product/42',
      image: 'https://shop.test/product.png',
      imageAlt: 'محصول امن',
      price: '۱۰۰ تومان',
      stock: 'موجود',
      inStock: true,
      canAjaxAdd: true,
      addToCartUrl: 'https://shop.test/?add-to-cart=42',
      addToCartLabel: 'افزودن به سبد',
      rating: '۴.۸',
      description: 'توضیح امن محصول',
    };
    const data = action === 'rasta_product_compare'
      ? {
          items: [product, { ...product, id: 99, name: 'محصول دوم' }],
          rows: [{ label: 'قیمت', values: ['۱۰۰ تومان', '۲۰۰ تومان'] }],
        }
      : action === 'rasta_quick_view'
        ? { item: product }
        : { items: [product] };
    return {
      ok: true,
      json: async () => ({ success: true, data }),
    };
  };
  dom.window.requestAnimationFrame = (callback) => {
    callback(0);
    return 1;
  };
  dom.window.scrollTo = () => {};
  dom.window.eval(themeScript);

  return dom;
};

test('opens, switches, and closes accessible drawers', () => {
  const dom = createThemeDom();
  const { document, KeyboardEvent, MouseEvent } = dom.window;
  const search = document.querySelector('[data-rasta-drawer="search"]');
  const cart = document.querySelector('[data-rasta-drawer="cart"]');
  const backdrop = document.querySelector('[data-rasta-backdrop]');

  document.querySelector('#open-search').dispatchEvent(new MouseEvent('click', { bubbles: true }));
  assert.equal(search.getAttribute('aria-hidden'), 'false');
  assert.equal(search.classList.contains('is-open'), true);
  assert.equal(document.body.classList.contains('rasta-lock-scroll'), true);
  assert.equal(backdrop.hidden, false);

  document.querySelector('#open-cart').dispatchEvent(new MouseEvent('click', { bubbles: true }));
  assert.equal(search.getAttribute('aria-hidden'), 'true');
  assert.equal(cart.getAttribute('aria-hidden'), 'false');
  assert.equal(cart.classList.contains('is-open'), true);

  document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
  assert.equal(cart.getAttribute('aria-hidden'), 'true');
  assert.equal(cart.classList.contains('is-open'), false);
  assert.equal(document.body.classList.contains('rasta-lock-scroll'), false);

  dom.window.close();
});

test('stores a product-card wishlist preference locally and updates aria state', () => {
  const dom = createThemeDom();
  const { document, MouseEvent, localStorage } = dom.window;
  const button = document.querySelector('[data-wishlist-product="42"]');

  button.dispatchEvent(new MouseEvent('click', { bubbles: true }));
  assert.equal(button.getAttribute('aria-pressed'), 'true');
  assert.equal(button.classList.contains('is-active'), true);
  assert.deepEqual(JSON.parse(localStorage.getItem('rastaCommerceWishlist')), ['42']);

  button.dispatchEvent(new MouseEvent('click', { bubbles: true }));
  assert.equal(button.getAttribute('aria-pressed'), 'false');
  assert.deepEqual(JSON.parse(localStorage.getItem('rastaCommerceWishlist')), []);

  dom.window.close();
});

test('keeps the mobile menu aria state in sync with its visual state', () => {
  const dom = createThemeDom();
  const { document, MouseEvent } = dom.window;
  const menu = document.querySelector('#menu');
  const nav = document.querySelector('[data-rasta-nav]');

  menu.dispatchEvent(new MouseEvent('click', { bubbles: true }));
  assert.equal(menu.getAttribute('aria-expanded'), 'true');
  assert.equal(nav.classList.contains('is-open'), true);

  menu.dispatchEvent(new MouseEvent('click', { bubbles: true }));
  assert.equal(menu.getAttribute('aria-expanded'), 'false');
  assert.equal(nav.classList.contains('is-open'), false);

  dom.window.close();
});

test('manages a local compare tray and opens a safe comparison table', async () => {
  const dom = createThemeDom();
  const { document, MouseEvent, localStorage } = dom.window;
  const compareButton = document.querySelector('[data-compare-product="42"]');
  const tray = document.querySelector('[data-compare-tray]');

  compareButton.dispatchEvent(new MouseEvent('click', { bubbles: true }));
  await new Promise((resolve) => dom.window.setTimeout(resolve, 0));
  assert.deepEqual(JSON.parse(localStorage.getItem('rastaCommerceCompare')), ['42']);
  assert.equal(compareButton.getAttribute('aria-pressed'), 'true');
  assert.equal(tray.hidden, false);
  assert.match(document.querySelector('[data-compare-summary]').textContent, /1/);

  localStorage.setItem('rastaCommerceCompare', JSON.stringify(['42', '99']));
  document.querySelector('[data-compare-open]').dispatchEvent(new MouseEvent('click', { bubbles: true }));
  await new Promise((resolve) => dom.window.setTimeout(resolve, 0));
  const compareContent = document.querySelector('[data-compare-content]');
  assert.equal(document.querySelector('[data-rasta-drawer="compare"]').getAttribute('aria-hidden'), 'false');
  assert.match(compareContent.textContent, /محصول امن/);
  assert.equal(compareContent.querySelector('script'), null);

  dom.window.close();
});

test('loads quick-view data as text nodes instead of executable markup', async () => {
  const dom = createThemeDom();
  const { document, MouseEvent } = dom.window;
  document.querySelector('[data-quick-view-product="99"]').dispatchEvent(new MouseEvent('click', { bubbles: true }));
  await new Promise((resolve) => dom.window.setTimeout(resolve, 0));

  const target = document.querySelector('[data-quick-view-content]');
  assert.equal(document.querySelector('[data-rasta-drawer="quick-view"]').getAttribute('aria-hidden'), 'false');
  assert.match(target.textContent, /<محصول امن>/);
  assert.equal(target.querySelector('img[src="x"]'), null);
  assert.equal(target.querySelector('script'), null);

  dom.window.close();
});
