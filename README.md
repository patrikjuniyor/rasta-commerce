# راستا کامرس (Rasta Commerce)

> قالب حرفه‌ای، سریع و **RTL-first** برای فروشگاه‌های WooCommerce فارسی.

![پیش‌نمایش راستا کامرس](screenshot.png)

راستا کامرس یک قالب کلاسیک مدرن وردپرس است که برای تجربه‌ی خرید راست‌چین طراحی شده؛ نه نسخه‌ی آینه‌شده‌ی یک قالب LTR. ساختار با CSS Logical Properties، نشانه‌گذاری معنایی و افزونه‌ی WooCommerce ساخته شده تا فروشگاه در فارسی، عربی و زبان‌های راست‌به‌چپ طبیعی باقی بماند.

## امکانات اصلی

- **ویترین فروشگاهی حرفه‌ای:** هیرو، دسته‌بندی‌های پویا، محصولات پرفروش/تازه، نوار مزیت‌ها و بخش مجله.
- **تجربه خرید سریع:** سبد خرید کشویی با WooCommerce fragments، افزودن AJAX به سبد و جست‌وجوی لحظه‌ای محصول.
- **RTL واقعی:** فایل `rtl.css`، فاصله‌گذاری logical، متون و تعاملات مناسب صفحه‌های راست‌چین.
- **واکنش‌گرا و دسترس‌پذیر:** منوی موبایل، focus trap برای drawerها، `Escape`، skip link، حالت focus واضح و `prefers-reduced-motion`.
- **WooCommerce-native:** اعلام رسمی پشتیبانی، gallery zoom/lightbox/slider، wrapperهای مبتنی بر hook و کارت محصول اختصاصی.
- **امنیت معقول سمت قالب:** nonce و sanitize برای AJAX، escape خروجی‌ها، و ساخت نتایج جست‌وجو با DOM API به جای HTML خام.
- **سفارشی‌سازی کم‌اصطکاک:** رنگ اصلی/ثانویه، متن نوار اطلاع‌رسانی و محتوای هیرو در Customizer.
- **بدون وابستگی ظاهری خارجی:** فونت CDN، کتابخانه JS و tracker خارجی ندارد؛ تصویرسازی هیرو اختصاصی و SVG است.
- **CI آماده:** GitHub Actions برای lint، تست‌های رگرسیون ایستا، PHP syntax check و تولید ZIP نصب‌پذیر.

## الهام طراحی، بدون کپی‌برداری

برای جهت‌گیری محصول، الگوهای عمومی فروشگاه‌های موفق (کارت محصول کم‌اصطکاک، mini-cart، دسته‌بندی روشن، سلسله‌مراتب صفحه‌ی محصول و تمرکز بر سرعت) بررسی شده‌اند؛ از جمله تجربه‌های رایج در **Flatsome**، **WoodMart** و **Blocksy**. هیچ کد، فایل PSD، تصویر، متن یا template آن‌ها وارد این پروژه نشده است.

پیاده‌سازی WooCommerce عمداً از **hooks** استفاده می‌کند و از override کردن templateهای بزرگ پرهیز دارد؛ این رویکرد با راهنمای رسمی توسعه‌ی قالب WooCommerce سازگارتر و کم‌هزینه‌تر در به‌روزرسانی‌هاست.

- [WooCommerce Classic Theme Developer Handbook](https://developer.woocommerce.com/docs/theming/theme-development/classic-theme-developer-handbook/)
- [WooCommerce Template Structure](https://developer.woocommerce.com/docs/theming/theme-development/template-structure)
- [راهنمای RTL وردپرس](https://developer.wordpress.org/)

## نیازمندی‌ها

| مورد | حداقل نسخه |
| --- | --- |
| WordPress | 6.4 |
| PHP | 8.0 |
| WooCommerce | نسخه پایدار جاری |
| مرورگر | نسخه‌های مدرن با CSS Grid و ES2022 |

بدون WooCommerce نیز بخش وبلاگ و پوسته‌ی پایه کار می‌کند؛ اما قابلیت‌های سبد، محصولات و جست‌وجوی محصول برای فعال‌شدن به WooCommerce نیاز دارند.

## نصب

### نصب از ZIP

1. از بخش **Releases** فایل `rasta-commerce-1.0.0.zip` را دریافت کنید، یا محلی بسازید:
   ```bash
   npm ci
   npm run package
   ```
2. در پیشخوان وردپرس به **نمایش ← پوسته‌ها ← افزودن پوسته ← بارگذاری پوسته** بروید.
3. فایل `release/rasta-commerce-1.0.0.zip` را بارگذاری و فعال کنید.
4. افزونه‌ی WooCommerce را نصب/فعال کنید.
5. از **نمایش ← فهرست‌ها**، فهرست «منوی اصلی» را تنظیم کنید.
6. از **نمایش ← سفارشی‌سازی ← ویترین راستا** رنگ‌ها و محتوای صفحه‌ی اول را شخصی‌سازی کنید.

### نصب برای توسعه

```bash
git clone https://github.com/<YOUR-ACCOUNT>/rasta-commerce.git
cd rasta-commerce
npm ci
npm run check
```

سپس پوشه‌ی پروژه را به `wp-content/themes/rasta-commerce` در نصب محلی وردپرس منتقل کنید و قالب را فعال کنید.

## کیفیت و تست

```bash
npm ci
npm run lint       # ESLint + Stylelint
npm test           # تست‌های رگرسیون ساختار، RTL، امنیت و theme.json
npm run test:php   # php -l برای تمام فایل‌های PHP
npm run check      # همه‌ی موارد بالا
npm run package    # ZIP نصب‌پذیر در release/
```

CI در `.github/workflows/ci.yml` همین مراحل را روی هر push و pull request اجرا می‌کند و ZIP را به‌عنوان artifact نگه می‌دارد.

### دامنه‌ی تست فعلی

تست‌های موجود شامل syntax PHP، lint CSS/JS، metadata قابل نصب قالب، hookهای ضروری، ساختار RTL، endpoint جست‌وجو و جلوگیری از raw HTML injection در JS هستند. تست end-to-end در یک نصب واقعی WordPress + WooCommerce (پرداخت، درگاه، مالیات، افزونه‌های فیلتر و داده‌ی واقعی محصول) باید پیش از انتشار production روی محیط staging انجام شود.

## ساختار پروژه

```text
rasta-commerce/
├── assets/
│   ├── css/editor-style.css
│   ├── images/hero-showcase.svg
│   └── js/theme.js
├── inc/
│   ├── ajax.php              # جست‌وجوی امن محصول
│   ├── customizer.php        # کنترل‌های ویترین
│   ├── setup.php              # قابلیت‌ها و assetها
│   ├── template-tags.php      # اجزای تکرارپذیر
│   └── woocommerce.php        # hookهای ووکامرس
├── template-parts/content/
├── woocommerce/content-product.php
├── tests/static-theme.test.mjs
├── .github/workflows/ci.yml
├── style.css / rtl.css / theme.json
└── screenshot.png
```

## نکات توسعه

- برای هر متن جدید از text domain `rasta-commerce` استفاده کنید.
- خروجی‌های پویا را با تابع escape متناسب امن کنید.
- برای RTL از propertyهای logical مانند `padding-inline`، `margin-inline` و `inset-inline` استفاده کنید؛ از `left/right` فقط زمانی استفاده کنید که واقعاً جهت‌دار باشد.
- در صورت وجود hook ووکامرس، از override کردن فایل‌های کامل `woocommerce/templates` پرهیز کنید.
- برای تولید فایل ترجمه در محیطی که WP-CLI دارد:
  ```bash
  ./scripts/make-pot.sh
  ```

راهنمای کامل مشارکت در [CONTRIBUTING.md](CONTRIBUTING.md) قرار دارد.

## انتشار در GitHub

پس از اتصال حساب GitHub، این دستورات remote را وصل و push می‌کنند:

```bash
git remote add origin https://github.com/<YOUR-ACCOUNT>/rasta-commerce.git
git push -u origin main
```

برای ساخت release، در GitHub یک tag مانند `v1.0.0` بسازید؛ workflow کیفیت را اجرا کنید و ZIP artifact را به Release متصل کنید.

## مجوز

این قالب تحت مجوز [GPL-2.0-or-later](LICENSE) منتشر شده است تا با اکوسیستم WordPress سازگار باشد.
