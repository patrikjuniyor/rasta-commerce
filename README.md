# راستا کامرس (Rasta Commerce)

> قالب حرفه‌ای، سریع و **RTL-first** برای فروشگاه‌های WooCommerce فارسی.

![پیش‌نمایش راستا کامرس](screenshot.png)

راستا کامرس یک قالب کلاسیک مدرن وردپرس است که برای تجربه‌ی خرید راست‌چین طراحی شده؛ نه نسخه‌ی آینه‌شده‌ی یک قالب LTR. ساختار با CSS Logical Properties، نشانه‌گذاری معنایی و افزونه‌ی WooCommerce ساخته شده تا فروشگاه در فارسی، عربی و زبان‌های راست‌به‌چپ طبیعی باقی بماند.

## امکانات اصلی

- **ویترین فروشگاهی حرفه‌ای:** هیرو، دسته‌بندی‌های پویا، محصولات پرفروش/تازه، نوار مزیت‌ها و بخش مجله.
- **تجربه خرید سریع:** سبد خرید کشویی با WooCommerce fragments، افزودن AJAX به سبد و جست‌وجوی لحظه‌ای محصول.
- **ابزارهای کشف محصول:** نمایش سریع بدون ترک فهرست، علاقه‌مندی خصوصی، مقایسه حداکثر چهار محصول، ریل محصولات اخیراً دیده‌شده و نوار خرید چسبان صفحه محصول.
- **فروش هوشمند بدون داده‌سازی:** شمارش‌گر فقط برای تخفیف زمان‌دار واقعی WooCommerce، هشدار موجودی پایین و نوار اختیاری پیشرفت ارسال رایگان در mini-cart.
- **RTL واقعی و بومی:** فایل `rtl.css`، فاصله‌گذاری logical، فونت Vazirmatn با مجوز OFL و نمایش تاریخ جلالی در خروجی‌های قالب.
- **واکنش‌گرا و دسترس‌پذیر:** منوی موبایل، focus trap برای drawerها، `Escape`، skip link، حالت focus واضح و `prefers-reduced-motion`.
- **WooCommerce-native:** اعلام رسمی پشتیبانی، gallery zoom/lightbox/slider، wrapperهای مبتنی بر hook و کارت محصول اختصاصی.
- **امنیت معقول سمت قالب:** nonce و sanitize برای AJAX، escape خروجی‌ها، و ساخت نتایج جست‌وجو با DOM API به جای HTML خام.
- **سفارشی‌سازی کم‌اصطکاک:** رنگ اصلی/ثانویه، محتوای هیرو و نوار اطلاع‌رسانی، **حالت تاریک**، متن پایین صفحه، شبکه‌های اجتماعی و چیدمان ستون محصولات در Customizer — با پیش‌نمایش زنده و نمایش شرطی گزینه‌ها.
- **داشبورد مدیریت فروشگاه:** نمای کلی زنده از محصولات، سفارش‌ها، درآمد و هشدار موجودی، به‌همراه ستون‌های قیمت/موجودی/SKU و فیلترهای سریع در فهرست محصولات.
- **حالت تعمیر (Coming Soon):** صفحه «به‌زودی بازمی‌گردیم» قابل تنظیم از Customizer برای بستن موقت فروشگاه به روی بازدیدکنندگان.
- **پشتیبانی واتساپ:** دکمه شناور چت واتساپ با شماره و پیام پیش‌فرض قابل تنظیم از Customizer.
- **اطلاع‌رسانی سفارش و تنظیمات فروشگاه:** ایمیل خودکار HTML به مدیر هنگام ثبت سفارش + صفحه تنظیمات فروشگاه (ایمیل مدیریت، موضوع ایمیل و کلید اطلاع‌رسانی).
- **بدون وابستگی ظاهری خارجی:** فونت CDN، کتابخانه JS و tracker خارجی ندارد؛ تصویرسازی هیرو اختصاصی و SVG است.
- **CI آماده:** GitHub Actions برای lint، تست‌های رگرسیون ایستا، PHP syntax check و تولید ZIP نصب‌پذیر.

## الهام طراحی، بدون کپی‌برداری

برای جهت‌گیری محصول، الگوهای عمومی فروشگاه‌های موفق (کارت محصول کم‌اصطکاک، mini-cart، دسته‌بندی روشن، سلسله‌مراتب صفحه‌ی محصول و تمرکز بر سرعت) بررسی شده‌اند؛ از جمله تجربه‌های رایج در **Flatsome**، **WoodMart** و **Blocksy**. هیچ کد، فایل PSD، تصویر، متن یا template آن‌ها وارد این پروژه نشده است.

پیاده‌سازی WooCommerce عمداً از **hooks** استفاده می‌کند و از override کردن templateهای بزرگ پرهیز دارد؛ این رویکرد با راهنمای رسمی توسعه‌ی قالب WooCommerce سازگارتر و کم‌هزینه‌تر در به‌روزرسانی‌هاست. سیاست الهام‌گیری از منابع مرجع و مرزبندی مالکیت فکری در [docs/reference-inspiration-policy.fa.md](docs/reference-inspiration-policy.fa.md) ثبت شده است.

- [WooCommerce Classic Theme Developer Handbook](https://developer.woocommerce.com/docs/theming/theme-development/classic-theme-developer-handbook/)
- [WooCommerce Template Structure](https://developer.woocommerce.com/docs/theming/theme-development/template-structure)
- [راهنمای RTL وردپرس](https://developer.wordpress.org/)

### بررسی افزونه‌ها و مرز قابلیت‌ها

قابلیت‌های مفیدی که معمولاً در افزونه‌های Quick View، Wishlist، Compare، Product Recommendations، Variation Swatches و Product Filter دیده می‌شوند بررسی و فقط موارد سبک، فروشگاهی و غیرحساس به قالب افزوده شده‌اند. پرداخت، مالیات، حمل‌ونقل، اشتراک، قیمت‌گذاری پویا و داده‌ی دائمی کاربر عمداً به افزونه‌های تخصصی سپرده می‌شوند. جزئیات و سازگاری‌های اختیاری در [docs/extension-audit.md](docs/extension-audit.md) آمده است.

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

1. از بخش **Releases** فایل `rasta-commerce-2.4.0.zip` را دریافت کنید، یا محلی بسازید:
   ```bash
   npm ci
   npm run package
   ```
2. در پیشخوان وردپرس به **نمایش ← پوسته‌ها ← افزودن پوسته ← بارگذاری پوسته** بروید.
3. فایل `release/rasta-commerce-2.4.0.zip` را بارگذاری و فعال کنید.
4. افزونه‌ی WooCommerce را نصب/فعال کنید.
5. از **نمایش ← فهرست‌ها**، فهرست «منوی اصلی» را تنظیم کنید.
6. از **نمایش ← سفارشی‌سازی ← ویترین راستا** رنگ‌ها و محتوای صفحه‌ی اول را شخصی‌سازی کنید؛ سپس در **ابزارهای خرید راستا** نمایش سریع، مقایسه، محصولات دیده‌شده، نوار خرید و آستانه ارسال رایگان را متناسب با فروشگاه تنظیم کنید.

### صفحه‌ساز Elementor

برای ساخت و بازتولید صفحه‌های فروشگاهی با Elementor، افزونه `rasta-commerce-core-1.0.1.zip` را نصب کنید. پس از فعال‌سازی Elementor، ده ویجت فارسی در دسته «راستا کامرس» شامل Hero، محصول، دسته‌بندی، بنر، اعتماد، مجله، کارت ویژگی و FAQ در دسترس هستند.

### اتصال زرین‌پال

منطق پرداخت عمداً در ZIP قالب قرار نمی‌گیرد تا با تغییر قالب، تنظیمات درگاه و سفارش‌ها پایدار بمانند. برای فعال‌سازی:

1. از همان Release، فایل `rasta-zarinpal-gateway-1.0.1.zip` را دانلود و از مسیر **افزونه‌ها ← افزودن ← بارگذاری افزونه** نصب کنید.
2. به **WooCommerce ← تنظیمات ← پرداخت‌ها ← زرین‌پال** بروید.
3. Merchant ID خود را وارد، درگاه را فعال و واحد مبلغ را بررسی کنید.
4. ابتدا Sandbox را با UUID آزمایشی تست کنید؛ سپس Sandbox را غیرفعال و Merchant ID واقعی را وارد کنید.

افزونه از API v4 زرین‌پال، callback امن با order key و Authority ذخیره‌شده، و verify سمت سرور پشتیبانی می‌کند. هیچ شماره کارت یا `card_hash` در سفارش و log ذخیره نمی‌شود. راهنمای افزونه در [plugins/rasta-zarinpal-gateway/README.md](plugins/rasta-zarinpal-gateway/README.md) است.

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
npm run test:php      # php -l برای تمام فایل‌های PHP
npm run test:gateway  # تست قرارداد تبدیل مبلغ، URL و شماره موبایل زرین‌پال
npm run test:jalali   # تست تبدیل و فرمت تاریخ جلالی
npm run test:elementor # تست ثبت 10 ویجت افزونه Core
npm run check         # همه‌ی موارد بالا
npm run package    # ZIP نصب‌پذیر در release/
```

CI در `.github/workflows/ci.yml` همین مراحل را روی هر push و pull request اجرا می‌کند و ZIP را به‌عنوان artifact نگه می‌دارد.

### دامنه‌ی تست فعلی

تست‌های موجود شامل syntax PHP برای قالب و افزونه‌ها، lint CSS/JS، metadata قابل نصب، hookهای ضروری، ساختار RTL، تاریخ جلالی، endpointهای nonceدارِ جست‌وجو/نمایش سریع/مقایسه، قراردادهای امنیتی API v4 زرین‌پال، ثبت هر ۱۰ ویجت Elementor، جلوگیری از raw HTML injection در JS و تست‌های JSDOM برای drawer، علاقه‌مندی، مقایسه و نمایش سریع هستند. تست end-to-end در یک نصب واقعی WordPress + WooCommerce (پرداخت، درگاه، مالیات، افزونه‌های فیلتر و داده‌ی واقعی محصول) باید پیش از انتشار production روی محیط staging انجام شود.

## آماده‌سازی عرضه در راست چین

برای ایجاد پکیج بازبینی‌شدهٔ راست چین:

```bash
npm run marketplace:prepare
```

این فرمان قالب اصلی، child theme، افزونه ضروری، WXR آغازین و `help.pdf` را در ساختار استاندارد آماده می‌کند؛ اما تا زمانی که دموی Duplicator و دارایی‌های انسانی کاور/آیکون فراهم نشوند، پکیج نهایی ساخته نمی‌شود.

```bash
npm run marketplace:validate  # کنترل فایل‌های تأمین‌شده توسط مالک
npm run marketplace:build     # ساخت پکیج نهایی پس از عبور از کنترل
```

جزئیات وضعیت در [گزارش آمادگی راست چین](docs/rtl-theme-marketplace-audit.fa.md) و [چک‌لیست عرضه](marketplace/requirements-checklist.md) قرار دارد.

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
├── docs/extension-audit.md    # ارزیابی قابلیت‌های افزونه‌ای
├── marketplace/               # راهنمای PDF، WXR، child theme و preflight راست چین
├── plugins/
│   ├── rasta-commerce-core/   # 10 ویجت اختصاصی Elementor
│   └── rasta-zarinpal-gateway/ # افزونه مستقل درگاه پرداخت API v4
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

مخزن عمومی پروژه در [patrikjuniyor/rasta-commerce](https://github.com/patrikjuniyor/rasta-commerce) قرار دارد. هر push و pull request، workflow کیفیت را اجرا می‌کند و ZIP قالب را به‌عنوان artifact نگه می‌دارد. برای انتشار نسخه جدید:

```bash
npm run check
npm run package
git tag v2.4.0
git push origin main --tags
```

سپس ZIP ساخته‌شده را به GitHub Release متصل کنید.

## مجوز

این قالب تحت مجوز [GPL-2.0-or-later](LICENSE) منتشر شده است تا با اکوسیستم WordPress سازگار باشد. فونت Vazirmatn داخل `assets/fonts/` تحت مجوز SIL Open Font License 1.1 قرار دارد؛ متن مجوز آن در `assets/fonts/OFL-Vazirmatn.txt` موجود است.
