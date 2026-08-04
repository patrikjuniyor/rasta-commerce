/**
 * Rasta Commerce frontend interactions.
 * Dependencies: none. WooCommerce's optional jQuery event is handled defensively.
 */

(() => {
  'use strict';

  const theme = window.rastaTheme || {};
  const strings = theme.strings || {};
  const body = document.body;
  const backdrop = document.querySelector('[data-rasta-backdrop]');
  const drawers = [...document.querySelectorAll('[data-rasta-drawer]')];
  const toast = document.querySelector('[data-rasta-toast]');
  let activeDrawer = null;
  let activeTrigger = null;
  let toastTimer = null;

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
      return;
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
  };

  document.querySelectorAll('[data-rasta-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      openDrawer(trigger.dataset.rastaOpen, trigger);
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
  let requestNumber = 0;

  const clearSearchResults = () => {
    if (searchResults) {
      searchResults.replaceChildren();
    }
  };

  const renderSearchMessage = (message, className) => {
    if (!searchResults) {
      return;
    }

    const text = document.createElement('p');
    text.className = className;
    text.textContent = message;
    searchResults.replaceChildren(text);
  };

  const safeImageUrl = (value) => {
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

  const createSearchResult = (item) => {
    const link = document.createElement('a');
    const imageUrl = safeImageUrl(item.image);
    link.className = 'rasta-search-result';
    link.href = item.url || '#';

    if (imageUrl) {
      const image = document.createElement('img');
      image.src = imageUrl;
      image.alt = '';
      image.loading = 'lazy';
      link.append(image);
    } else {
      const placeholder = document.createElement('span');
      placeholder.className = 'rasta-search-result__placeholder';
      placeholder.setAttribute('aria-hidden', 'true');
      link.append(placeholder);
    }

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
    const requestId = ++requestNumber;
    renderSearchMessage(strings.searching || 'در حال جست‌وجو…', 'rasta-search-loading');

    const formData = new URLSearchParams();
    formData.set('action', 'rasta_product_search');
    formData.set('nonce', theme.nonce);
    formData.set('term', term.trim());

    try {
      const response = await fetch(theme.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: formData.toString(),
        signal: controller.signal,
      });
      const payload = await response.json();

      if (requestId !== requestNumber) {
        return;
      }

      const items = payload && payload.success && payload.data ? payload.data.items : [];
      if (!Array.isArray(items) || !items.length) {
        renderSearchMessage(strings.noResults || 'محصولی پیدا نشد.', 'rasta-search-empty');
        return;
      }

      const fragment = document.createDocumentFragment();
      items.forEach((item) => fragment.append(createSearchResult(item)));
      searchResults.replaceChildren(fragment);
    } catch (error) {
      if (error.name !== 'AbortError' && requestId === requestNumber) {
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
  const readWishlist = () => {
    try {
      const stored = JSON.parse(window.localStorage.getItem(wishlistKey) || '[]');
      return Array.isArray(stored) ? stored.map(String) : [];
    } catch {
      return [];
    }
  };

  const writeWishlist = (ids) => {
    try {
      window.localStorage.setItem(wishlistKey, JSON.stringify(ids));
    } catch {
      // Storage can be disabled by the browser. The button remains a harmless visual preference.
    }
  };

  const refreshWishlistButtons = () => {
    const wishlist = readWishlist();
    document.querySelectorAll('[data-wishlist-product]').forEach((button) => {
      const isActive = wishlist.includes(String(button.dataset.wishlistProduct));
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', String(isActive));
    });
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-wishlist-product]');
    if (!button) {
      return;
    }

    const id = String(button.dataset.wishlistProduct || '');
    if (!id) {
      return;
    }

    const wishlist = readWishlist();
    const nextWishlist = wishlist.includes(id) ? wishlist.filter((item) => item !== id) : [...wishlist, id];
    writeWishlist(nextWishlist);
    refreshWishlistButtons();
  });

  refreshWishlistButtons();

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
