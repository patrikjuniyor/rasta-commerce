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
      <button data-wishlist-product="42" aria-pressed="false">علاقه‌مندی</button>
      <button data-scroll-top>بالا</button>
      <div data-rasta-toast></div>
    </body></html>`,
    {
      url: 'https://shop.test/',
      runScripts: 'outside-only',
    }
  );

  dom.window.rastaTheme = {
    strings: {
      addedToCart: 'محصول به سبد خرید اضافه شد.',
    },
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
