# ارزیابی قابلیت‌های افزونه‌های فروشگاهی

این سند مشخص می‌کند که چه چیزهایی از الگوهای رایج افزونه‌های WooCommerce الهام گرفته شده و چرا بعضی قابلیت‌ها عمداً داخل قالب پیاده‌سازی نشده‌اند.

## معیار انتخاب

هر قابلیت باید هم‌زمان این چهار شرط را داشته باشد:

1. مسیر کشف یا تصمیم‌گیری خریدار را کوتاه کند.
2. بدون حساب خارجی، tracker یا اسکریپت سنگین قابل اجرا باشد.
3. وضعیت حساس فروشگاه (پرداخت، مالیات، سفارش و موجودی) را دوباره‌سازی نکند.
4. در موبایل، RTL و کیبورد قابل استفاده باشد.

راهنمای UX افزونه‌های WooCommerce نیز بر تجربه‌ی پاسخ‌گو، عدم ایجاد رابط بی‌دلیل و تمرکز بر کار اصلی فروشنده تأکید دارد.

## قابلیت‌های انتخاب‌شده در راستا کامرس ۱.۱

| الگوی افزونه‌ای | پیاده‌سازی در راستا | دلیل انتخاب |
| --- | --- | --- |
| Quick View | drawer نمایش سریع با داده‌ی AJAX امن، CTA برای افزودن محصول ساده و لینک محصول کامل برای variationها | مشاهده‌ی جزئیات بدون ترک فهرست محصول |
| Wishlist | فهرست علاقه‌مندی خصوصی در `localStorage` به همراه drawer و شمارنده‌ی هدر | بدون نیاز به ذخیره‌ی حساب کاربری یا داده‌ی شخصی |
| Product Compare | انتخاب حداکثر چهار محصول، tray ثابت و جدول مشخصات/موجودی/ویژگی‌ها | مناسب کالاهای دارای مشخصات مانند دیجیتال، لوازم خانه و ابزار |
| Recently Viewed | ریل «اخیراً دیده‌اید» در صفحه محصول | کمک به بازگشت به محصولات دیده‌شده بدون tracker خارجی |
| Sticky Add to Cart | نوار خرید چسبان هنگام خروج فرم خرید از دید | کاهش اصطکاک خرید در موبایل و صفحات بلند |
| Sale Countdown + Stock cue | شمارش‌گر فقط برای تخفیف زمان‌دار WooCommerce و هشدار موجودی پایین فقط با مدیریت موجودی فعال | استفاده از داده‌های واقعی خود WooCommerce و پرهیز از شمارش مصنوعی |
| Free-shipping progress | نوار اختیاری mini-cart با آستانه قابل تنظیم | شفاف‌سازی مبلغ باقی‌مانده تا ارسال رایگان |

## مرز مسئولیت قالب و افزونه

موارد زیر **عمداً** به یک افزونه‌ی تخصصی واگذار شده‌اند؛ چون داده یا منطق تجاری دائمی دارند:

- درگاه پرداخت، مالیات، صدور فاکتور و حمل‌ونقل
- قیمت‌گذاری پویا، کوپن‌های پیچیده، باندل و product add-on با قیمت
- اشتراک، رزرو، بازار چندفروشندگی، affiliate و CRM
- wishlist یا compare همگام‌شونده بین چند دستگاه/حساب کاربری
- فیلتر ایندکس‌شده برای کاتالوگ‌های بزرگ

این جداسازی باعث می‌شود با تعویض قالب، داده‌ی مهم فروشگاه از بین نرود و قالب هم سبک باقی بماند.

## سازگاری اختیاری

CSS راستا برای ظاهر هماهنگ با خانواده‌های زیر آماده است، اما هیچ وابستگی اجباری اضافه نمی‌کند:

- Variation Swatches (کلاس‌های `variable-items-wrapper`)
- YITH Wishlist / Compare
- YITH Ajax Product Filter و HUSKY/WOOF

برای فروشگاه‌هایی با هزاران SKU، به‌جای پیاده‌سازی فیلتر سفارشی در قالب، یک افزونه‌ی faceted/indexed filter را روی staging بررسی کنید.

## منابع بررسی‌شده

- [WooCommerce Quick View Documentation](https://woocommerce.com/document/woocommerce-quick-view/)
- [WooCommerce Product Recommendations](https://woocommerce.com/products/product-recommendations/)
- [WooCommerce Extension UX Best Practices](https://developer.woocommerce.com/docs/extensions/ux-guidelines-extensions/best-practices/)
- [Productive Commerce در WordPress.org](https://wordpress.org/plugins/productive-commerce/)
- [مقایسه افزونه‌های Compare](https://www.wpxpo.com/blog/best-woocommerce-product-comparison-plugins/)
- [مقایسه افزونه‌های Variation Swatches](https://barn2.com/blog/woocommerce-swatches/)
