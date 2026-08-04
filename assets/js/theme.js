/**
 * Rasta Commerce frontend interactions.
 * Dependencies: none. WooCommerce's optional jQuery event is handled defensively.
 */

(() => {
  'use strict';

  const theme = window.rastaTheme || {};
  const strings = theme.strings || {};
  const features = theme.features || {};
  const body = document.body;
  const backdrop = document.querySelector('[data-rasta-backdrop]');
  const drawers = [...document.querySelectorAll('[data-rasta-drawer]')];
  const toast = document.querySelector('[data-rasta-toast]');
  let activeDrawer = null;
  let activeTrigger = null;
  let toastTimer = null;

  const closest = (target, selector) => (target instanceof Element ? target.closest(selector) : null);

  const getFocusable = (container) =>
    [...container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')].filter(
      (element) => !element.hidden
    );

  const showToast = (message) => {
    if (!toast || !message) {
      return;
    }

    window.clearTimeout(toastTimer);
    toast.textContent = message;
    toast.classList.add('is-visible');
    toastTimer = window.setTimeout(() => {
      toast.classList.remove('is-visible');
    }, 3200);
  };

  const safeUrl = (value) => {
    if (!value) {
      return '';
    }

    try {
      const url = new URL(value, window.location.origin);
      return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
    } catch {
      return '';
    }
  };

  const createMessage = (message, className = 'rasta-search-empty') => {
    const element = document.createElement('p');
    element.className = className;
    element.textContent = message;
    return element;
  };

  const closeDrawer = (shouldRestoreFocus = true) => {
    if (!activeDrawer) {
      return;
    }

    const closingDrawer = activeDrawer;
    const closingTrigger = activeTrigger;
    closingDrawer.classList.remove('is-open');
    closingDrawer.setAttribute('aria-hidden', 'true');
    activeDrawer = null;
    activeTrigger = null;
    body.classList.remove('rasta-lock-scroll');

    window.setTimeout(() => {
      if (!activeDrawer && backdrop) {
        backdrop.hidden = true;
      }
    }, 220);

    if (shouldRestoreFocus && closingTrigger) {
      closingTrigger.focus();
    }
  };

  const openDrawer = (name, trigger) => {
    const drawer = drawers.find((item) => item.dataset.rastaDrawer === name);
    if (!drawer) {
      return null;
    }

    if (activeDrawer && activeDrawer !== drawer) {
      closeDrawer(false);
    }

    activeDrawer = drawer;
    activeTrigger = trigger || null;
    drawer.setAttribute('aria-hidden', 'false');
    body.classList.add('rasta-lock-scroll');

    if (backdrop) {
      backdrop.hidden = false;
    }

    window.requestAnimationFrame(() => {
      drawer.classList.add('is-open');
    });

    window.setTimeout(() => {
      const [firstFocusable] = getFocusable(drawer);
      if (firstFocusable) {
        firstFocusable.focus();
      } else {
        drawer.focus();
      }
    }, 30);

    return drawer;
  };

  const postAjax = async (action, data = {}, nonce = theme.toolsNonce, signal) => {
    if (!theme.ajaxUrl || !nonce || !window.fetch) {
      throw new Error(strings.networkError || 'اتصال برقرار نشد؛ دوباره تلاش کنید.');
    }

    const formData = new URLSearchParams();
    formData.set('action', action);
    formData.set('nonce', nonce);

    Object.entries(data).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((item) => formData.append(`${key}[]`, String(item)));
      } else if (value !== undefined && value !== null) {
        formData.set(key, String(value));
      }
    });

    const response = await window.fetch(theme.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      body: formData.toString(),
      signal,
    });
    const payload = await response.json();

    if (!response.ok || !payload || !payload.success) {
      const message = payload && payload.data && payload.data.message ? payload.data.message : strings.networkError;
      throw new Error(message || 'اتصال برقرار نشد؛ دوباره تلاش کنید.');
    }

    return payload.data || {};
  };

  document.querySelectorAll('[data-rasta-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const drawer = openDrawer(trigger.dataset.rastaOpen, trigger);
      if (drawer && trigger.dataset.rastaOpen === 'wishlist') {
        refreshWishlistDrawer();
      }
    });
  });

  document.querySelectorAll('[data-rasta-close]').forEach((trigger) => {
    trigger.addEventListener('click', () => closeDrawer());
  });

  if (backdrop) {
    backdrop.addEventListener('click', () => closeDrawer());
  }

  document.addEventListener('keydown', (event) => {
    if (!activeDrawer) {
      return;
    }

    if (event.key === 'Escape') {
      event.preventDefault();
      closeDrawer();
      return;
    }

    if (event.key !== 'Tab') {
      return;
    }

    const focusable = getFocusable(activeDrawer);
    if (!focusable.length) {
      event.preventDefault();
      activeDrawer.focus();
      return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  const menuToggle = document.querySelector('[data-rasta-menu-toggle]');
  const navigation = document.querySelector('[data-rasta-nav]');
  if (menuToggle && navigation) {
    menuToggle.addEventListener('click', () => {
      const isOpen = navigation.classList.toggle('is-open');
      menuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (event) => {
      if (!navigation.classList.contains('is-open') || navigation.contains(event.target) || menuToggle.contains(event.target)) {
        return;
      }

      navigation.classList.remove('is-open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  }

  const searchInput = document.querySelector('[data-product-search]');
  const searchResults = document.querySelector('[data-search-results]');
  let searchTimer = null;
  let searchController = null;
  let searchRequestNumber = 0;

  const clearSearchResults = () => {
    if (searchResults) {
      searchResults.replaceChildren();
    }
  };

  const renderSearchMessage = (message, className) => {
    if (searchResults) {
      searchResults.replaceChildren(createMessage(message, className));
    }
  };

  const productImage = (item, className = '') => {
    const imageUrl = safeUrl(item.image);
    if (!imageUrl) {
      const placeholder = document.createElement('span');
      placeholder.className = className ? `${className} rasta-product-image-placeholder` : 'rasta-product-image-placeholder';
      placeholder.setAttribute('aria-hidden', 'true');
      return placeholder;
    }

    const image = document.createElement('img');
    image.className = className;
    image.src = imageUrl;
    image.alt = item.imageAlt || '';
    image.loading = 'lazy';
    image.decoding = 'async';
    return image;
  };

  const createSearchResult = (item) => {
    const link = document.createElement('a');
    link.className = 'rasta-search-result';
    link.href = safeUrl(item.url) || '#';
    link.append(productImage(item));

    const details = document.createElement('span');
    details.className = 'rasta-search-result__body';
    const name = document.createElement('strong');
    const price = document.createElement('span');
    name.textContent = item.name || '';
    price.textContent = item.price || item.sku || '';
    details.append(name, price);
    link.append(details);

    return link;
  };

  const runProductSearch = async (term) => {
    if (!searchResults) {
      return;
    }

    if (term.trim().length < 2) {
      clearSearchResults();
      return;
    }

    if (!theme.ajaxUrl || !theme.nonce) {
      renderSearchMessage(strings.networkError || 'جست‌وجو در دسترس نیست.', 'rasta-search-empty');
      return;
    }

    if (searchController) {
      searchController.abort();
    }

    const controller = new AbortController();
    searchController = controller;
    const requestId = ++searchRequestNumber;
    renderSearchMessage(strings.searching || 'در حال جست‌وجو…', 'rasta-search-loading');

    try {
      const data = await postAjax('rasta_product_search', { term: term.trim() }, theme.nonce, controller.signal);
      if (requestId !== searchRequestNumber) {
        return;
      }

      const items = data.items || [];
      if (!Array.isArray(items) || !items.length) {
        renderSearchMessage(strings.noResults || 'محصولی پیدا نشد.', 'rasta-search-empty');
        return;
      }

      const fragment = document.createDocumentFragment();
      items.forEach((item) => fragment.append(createSearchResult(item)));
      searchResults.replaceChildren(fragment);
    } catch (error) {
      if ((!error || error.name !== 'AbortError') && requestId === searchRequestNumber) {
        renderSearchMessage(strings.networkError || 'اتصال برقرار نشد؛ دوباره تلاش کنید.', 'rasta-search-empty');
      }
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(() => runProductSearch(searchInput.value), 250);
    });
  }

  const wishlistKey = 'rastaCommerceWishlist';
  const compareKey = 'rastaCommerceCompare';

  const readStoredIds = (key, limit) => {
    try {
      const stored = JSON.parse(window.localStorage.getItem(key) || '[]');
      if (!Array.isArray(stored)) {
        return [];
      }
      return [...new Set(stored.map(String).filter(Boolean))].slice(0, limit);
    } catch {
      return [];
    }
  };

  const writeStoredIds = (key, ids) => {
    try {
      window.localStorage.setItem(key, JSON.stringify(ids));
    } catch {
      // Storage can be disabled by the browser. The controls remain harmless visual preferences.
    }
  };

  const readWishlist = () => readStoredIds(wishlistKey, 24);
  const writeWishlist = (ids) => writeStoredIds(wishlistKey, ids.slice(0, 24));
  const readCompare = () => readStoredIds(compareKey, 4);
  const writeCompare = (ids) => writeStoredIds(compareKey, ids.slice(0, 4));

  const fetchProductCollection = (ids) => postAjax('rasta_product_collection', { ids });

  const refreshWishlistButtons = () => {
    const wishlist = readWishlist();
    document.querySelectorAll('[data-wishlist-product]').forEach((button) => {
      const isActive = wishlist.includes(String(button.dataset.wishlistProduct));
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', String(isActive));
    });
    document.querySelectorAll('[data-wishlist-count]').forEach((badge) => {
      badge.textContent = String(wishlist.length);
      badge.hidden = wishlist.length === 0;
    });
  };

  const createSavedProduct = (item) => {
    const article = document.createElement('article');
    article.className = 'rasta-saved-product';
    const link = document.createElement('a');
    link.className = 'rasta-saved-product__image';
    link.href = safeUrl(item.url) || '#';
    link.append(productImage(item));

    const details = document.createElement('div');
    details.className = 'rasta-saved-product__body';
    const title = document.createElement('a');
    title.href = safeUrl(item.url) || '#';
    title.textContent = item.name || '';
    const price = document.createElement('span');
    price.textContent = item.price || '';
    details.append(title, price);

    const remove = document.createElement('button');
    remove.className = 'rasta-saved-product__remove';
    remove.type = 'button';
    remove.dataset.wishlistProduct = String(item.id || '');
    remove.setAttribute('aria-label', `${strings.remove || 'حذف'} ${item.name || ''}`.trim());
    remove.textContent = strings.remove || 'حذف';
    article.append(link, details, remove);

    return article;
  };

  const refreshWishlistDrawer = async () => {
    const target = document.querySelector('[data-wishlist-results]');
    const ids = readWishlist();
    if (!target) {
      return;
    }

    if (!ids.length) {
      target.replaceChildren(createMessage(strings.wishlistEmpty || 'هنوز محصولی را ذخیره نکرده‌اید.', 'rasta-saved-products__empty'));
      return;
    }

    target.replaceChildren(createMessage(strings.loadingProduct || 'در حال آماده‌سازی محصول…', 'rasta-search-loading'));
    try {
      const data = await fetchProductCollection(ids);
      const items = data.items || [];
      if (!items.length) {
        target.replaceChildren(createMessage(strings.wishlistEmpty || 'هنوز محصولی را ذخیره نکرده‌اید.', 'rasta-saved-products__empty'));
        return;
      }
      const fragment = document.createDocumentFragment();
      items.forEach((item) => fragment.append(createSavedProduct(item)));
      target.replaceChildren(fragment);
    } catch {
      target.replaceChildren(createMessage(strings.networkError || 'اتصال برقرار نشد؛ دوباره تلاش کنید.', 'rasta-saved-products__empty'));
    }
  };

  const refreshCompareButtons = () => {
    const compare = readCompare();
    document.querySelectorAll('[data-compare-product]').forEach((button) => {
      const isActive = compare.includes(String(button.dataset.compareProduct));
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', String(isActive));
    });
  };

  const createCompareTrayItem = (item) => {
    const element = document.createElement('div');
    element.className = 'rasta-compare-tray__item';
    element.append(productImage(item));
    const title = document.createElement('span');
    title.textContent = item.name || '';
    const remove = document.createElement('button');
    remove.type = 'button';
    remove.dataset.compareProduct = String(item.id || '');
    remove.setAttribute('aria-label', `${strings.remove || 'حذف'} ${item.name || ''}`.trim());
    remove.textContent = '×';
    element.append(title, remove);
    return element;
  };

  const refreshCompareTray = async () => {
    const tray = document.querySelector('[data-compare-tray]');
    const summary = document.querySelector('[data-compare-summary]');
    const target = document.querySelector('[data-compare-tray-items]');
    const ids = readCompare();
    if (!tray || !summary || !target) {
      return;
    }

    tray.hidden = ids.length === 0;
    refreshCompareButtons();
    if (!ids.length) {
      target.replaceChildren();
      summary.textContent = 'محصولی برای مقایسه انتخاب نشده است.';
      return;
    }

    summary.textContent = `${ids.length} ${strings.productsInComparison || 'محصول برای مقایسه'}`;
    target.replaceChildren(createMessage(strings.loadingProduct || 'در حال آماده‌سازی محصول…', 'rasta-compare-tray__loading'));
    try {
      const data = await fetchProductCollection(ids);
      const items = data.items || [];
      const fragment = document.createDocumentFragment();
      items.forEach((item) => fragment.append(createCompareTrayItem(item)));
      target.replaceChildren(fragment);
    } catch {
      target.replaceChildren();
    }
  };

  const renderCompareTable = (data, target) => {
    const items = data.items || [];
    const rows = data.rows || [];
    if (items.length < 2) {
      target.replaceChildren(createMessage(strings.compareEmpty || 'برای مقایسه، حداقل دو محصول انتخاب کنید.', 'rasta-compare-empty'));
      return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'rasta-compare-table-wrap';
    const table = document.createElement('table');
    table.className = 'rasta-compare-table';
    const header = document.createElement('thead');
    const headerRow = document.createElement('tr');
    const labelHeader = document.createElement('th');
    labelHeader.scope = 'col';
    labelHeader.textContent = 'مشخصات';
    headerRow.append(labelHeader);

    items.forEach((item) => {
      const cell = document.createElement('th');
      cell.scope = 'col';
      const link = document.createElement('a');
      link.href = safeUrl(item.url) || '#';
      link.className = 'rasta-compare-table__product';
      link.append(productImage(item));
      const name = document.createElement('span');
      name.textContent = item.name || '';
      link.append(name);
      cell.append(link);
      headerRow.append(cell);
    });
    header.append(headerRow);

    const body = document.createElement('tbody');
    rows.forEach((row) => {
      const tableRow = document.createElement('tr');
      const label = document.createElement('th');
      label.scope = 'row';
      label.textContent = row.label || '';
      tableRow.append(label);
      (row.values || []).forEach((value) => {
        const cell = document.createElement('td');
        cell.textContent = value || '—';
        tableRow.append(cell);
      });
      body.append(tableRow);
    });

    table.append(header, body);
    wrapper.append(table);
    target.replaceChildren(wrapper);
  };

  const openCompare = async (trigger) => {
    const ids = readCompare();
    if (ids.length < 2) {
      showToast(strings.compareEmpty || 'برای مقایسه، حداقل دو محصول انتخاب کنید.');
      return;
    }

    const drawer = openDrawer('compare', trigger);
    const target = document.querySelector('[data-compare-content]');
    if (!drawer || !target) {
      return;
    }

    target.replaceChildren(createMessage(strings.loadingProduct || 'در حال آماده‌سازی محصول…', 'rasta-search-loading'));
    try {
      const data = await postAjax('rasta_product_compare', { ids });
      renderCompareTable(data, target);
    } catch {
      target.replaceChildren(createMessage(strings.networkError || 'اتصال برقرار نشد؛ دوباره تلاش کنید.', 'rasta-compare-empty'));
    }
  };

  const renderQuickView = (item, target) => {
    const article = document.createElement('article');
    article.className = 'rasta-quick-view__product';
    const media = document.createElement('div');
    media.className = 'rasta-quick-view__media';
    media.append(productImage(item));

    const content = document.createElement('div');
    content.className = 'rasta-quick-view__body';
    if (item.category) {
      const category = document.createElement('p');
      category.className = 'rasta-quick-view__category';
      category.textContent = item.category;
      content.append(category);
    }

    const title = document.createElement('h3');
    const productLink = document.createElement('a');
    productLink.href = safeUrl(item.url) || '#';
    productLink.textContent = item.name || '';
    title.append(productLink);
    content.append(title);

    const meta = document.createElement('div');
    meta.className = 'rasta-quick-view__meta';
    if (item.rating) {
      const rating = document.createElement('span');
      rating.textContent = `★ ${item.rating}`;
      meta.append(rating);
    }
    const stock = document.createElement('span');
    stock.className = item.inStock ? 'is-in-stock' : 'is-out-of-stock';
    stock.textContent = item.stock || '';
    meta.append(stock);
    content.append(meta);

    if (item.price) {
      const price = document.createElement('p');
      price.className = 'rasta-quick-view__price';
      price.textContent = item.price;
      content.append(price);
    }

    if (item.description) {
      const description = document.createElement('p');
      description.className = 'rasta-quick-view__description';
      description.textContent = item.description;
      content.append(description);
    }

    const actions = document.createElement('div');
    actions.className = 'rasta-quick-view__actions';
    const fullLink = document.createElement('a');
    fullLink.className = 'rasta-text-link';
    fullLink.href = safeUrl(item.url) || '#';
    fullLink.textContent = strings.viewProduct || 'مشاهده محصول';
    actions.append(fullLink);

    const add = document.createElement('a');
    add.className = item.canAjaxAdd ? 'button rasta-quick-view__add-to-cart add_to_cart_button ajax_add_to_cart' : 'rasta-button';
    add.href = safeUrl(item.addToCartUrl) || safeUrl(item.url) || '#';
    add.textContent = item.addToCartLabel || strings.viewProduct || 'مشاهده محصول';
    if (item.canAjaxAdd) {
      add.dataset.product_id = String(item.id || '');
      add.dataset.quantity = '1';
    }
    actions.append(add);
    content.append(actions);

    article.append(media, content);
    target.replaceChildren(article);
  };

  const openQuickView = async (button) => {
    const productId = String(button.dataset.quickViewProduct || '');
    const drawer = openDrawer('quick-view', button);
    const target = document.querySelector('[data-quick-view-content]');
    if (!productId || !drawer || !target) {
      return;
    }

    target.replaceChildren(createMessage(strings.loadingProduct || 'در حال آماده‌سازی محصول…', 'rasta-search-loading'));
    try {
      const data = await postAjax('rasta_quick_view', { product_id: productId });
      if (data.item) {
        renderQuickView(data.item, target);
      } else {
        target.replaceChildren(createMessage(strings.noResults || 'محصولی پیدا نشد.', 'rasta-quick-view__empty'));
      }
    } catch {
      target.replaceChildren(createMessage(strings.networkError || 'اتصال برقرار نشد؛ دوباره تلاش کنید.', 'rasta-quick-view__empty'));
    }
  };

  const createRecentProduct = (item) => {
    const link = document.createElement('a');
    link.className = 'rasta-recent-product';
    link.href = safeUrl(item.url) || '#';
    link.append(productImage(item));
    const details = document.createElement('span');
    const name = document.createElement('strong');
    name.textContent = item.name || '';
    const price = document.createElement('small');
    price.textContent = item.price || '';
    details.append(name, price);
    link.append(details);
    return link;
  };

  const trackRecentlyViewed = async () => {
    if (!features.recentlyViewed) {
      return;
    }

    const section = document.querySelector('[data-recently-viewed-section]');
    const marker = document.querySelector('[data-rasta-product-view]');
    const productId = marker ? String(marker.dataset.rastaProductView || '') : '';
    if (!section || !productId) {
      return;
    }

    const previouslyViewed = readStoredIds('rastaCommerceRecentlyViewed', 9).filter((id) => id !== productId);
    const viewed = [productId, ...previouslyViewed].slice(0, 9);
    writeStoredIds('rastaCommerceRecentlyViewed', viewed);
    const target = document.querySelector('[data-recently-viewed-products]');
    const ids = viewed.filter((id) => id !== productId);
    if (!target || !ids.length) {
      return;
    }

    try {
      const data = await fetchProductCollection(ids);
      const items = data.items || [];
      if (!items.length) {
        return;
      }
      const fragment = document.createDocumentFragment();
      items.forEach((item) => fragment.append(createRecentProduct(item)));
      target.replaceChildren(fragment);
      section.hidden = false;
    } catch {
      section.hidden = true;
    }
  };

  document.addEventListener('click', (event) => {
    const quickViewButton = closest(event.target, '[data-quick-view-product]');
    if (quickViewButton) {
      event.preventDefault();
      openQuickView(quickViewButton);
      return;
    }

    const wishlistButton = closest(event.target, '[data-wishlist-product]');
    if (wishlistButton) {
      event.preventDefault();
      const id = String(wishlistButton.dataset.wishlistProduct || '');
      if (!id) {
        return;
      }

      const wishlist = readWishlist();
      const nextWishlist = wishlist.includes(id) ? wishlist.filter((item) => item !== id) : [...wishlist, id];
      writeWishlist(nextWishlist);
      refreshWishlistButtons();
      if (activeDrawer && activeDrawer.dataset.rastaDrawer === 'wishlist') {
        refreshWishlistDrawer();
      }
      return;
    }

    const compareButton = closest(event.target, '[data-compare-product]');
    if (compareButton) {
      event.preventDefault();
      const id = String(compareButton.dataset.compareProduct || '');
      const compare = readCompare();
      if (!id) {
        return;
      }
      if (compare.includes(id)) {
        writeCompare(compare.filter((item) => item !== id));
      } else if (compare.length >= 4) {
        showToast(strings.compareMax || 'برای مقایسه حداکثر چهار محصول انتخاب کنید.');
        return;
      } else {
        writeCompare([...compare, id]);
        showToast(strings.compareUpdated || 'فهرست مقایسه به‌روزرسانی شد.');
      }
      refreshCompareTray();
      return;
    }

    const compareOpen = closest(event.target, '[data-compare-open]');
    if (compareOpen) {
      event.preventDefault();
      openCompare(compareOpen);
      return;
    }

    const compareClear = closest(event.target, '[data-compare-clear]');
    if (compareClear) {
      event.preventDefault();
      writeCompare([]);
      refreshCompareTray();
      return;
    }

    const scrollToForm = closest(event.target, '[data-scroll-product-form]');
    if (scrollToForm) {
      const productForm = document.querySelector('form.cart');
      if (productForm && typeof productForm.scrollIntoView === 'function') {
        productForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });

  const initializeStickyCart = () => {
    if (!features.stickyCart) {
      return;
    }

    const stickyCart = document.querySelector('[data-sticky-cart]');
    const productForm = document.querySelector('form.cart');
    if (!stickyCart || !productForm) {
      return;
    }

    const setStickyVisibility = (visible) => {
      stickyCart.classList.toggle('is-visible', visible);
      stickyCart.setAttribute('aria-hidden', String(!visible));
    };

    if ('IntersectionObserver' in window) {
      const observer = new window.IntersectionObserver(
        ([entry]) => setStickyVisibility(!entry.isIntersecting),
        { threshold: 0.1 }
      );
      observer.observe(productForm);
      return;
    }

    const fallback = () => {
      const position = productForm.getBoundingClientRect();
      setStickyVisibility(position.bottom < 0);
    };
    window.addEventListener('scroll', fallback, { passive: true });
    fallback();
  };

  const initializeSaleCountdowns = () => {
    if (!features.saleCountdown) {
      return;
    }

    const counters = [...document.querySelectorAll('[data-sale-countdown]')];
    if (!counters.length) {
      return;
    }

    const formatter = new Intl.NumberFormat('fa-IR', { minimumIntegerDigits: 2, useGrouping: false });
    const updateCounters = () => {
      const now = Date.now();
      counters.forEach((counter) => {
        const endsAt = Number(counter.dataset.saleEnds || 0) * 1000;
        const remaining = endsAt - now;
        if (!endsAt || remaining <= 0) {
          counter.hidden = true;
          return;
        }
        const totalSeconds = Math.floor(remaining / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        const value = counter.querySelector('[data-sale-countdown-value]');
        if (value) {
          const dayPrefix = days ? `${formatter.format(days)} روز و ` : '';
          value.textContent = `${dayPrefix}${formatter.format(hours)}:${formatter.format(minutes)}:${formatter.format(seconds)}`;
        }
      });
    };

    updateCounters();
    window.setInterval(updateCounters, 1000);
  };

  refreshWishlistButtons();
  refreshCompareTray();
  trackRecentlyViewed();
  initializeStickyCart();
  initializeSaleCountdowns();

  const scrollTop = document.querySelector('[data-scroll-top]');
  if (scrollTop) {
    scrollTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', () => {
      showToast(strings.addedToCart || 'محصول به سبد خرید اضافه شد.');
    });
  }
})();
