# آماده‌سازی پکیج راست چین

این پوشه برای ساخت پکیج استاندارد عرضه در راست چین است. خروجی نهایی فقط زمانی «قابل ارسال» محسوب می‌شود که تمام موارد `requirements-checklist.md` پاس شوند.

## خروجی خودکار

فرمان زیر قالب اصلی، child theme، افزونه‌های ضروری (Core Elementor و زرین‌پال)، WXR آغازین و راهنمای PDF را در ساختار استاندارد آماده می‌کند:

```bash
npm run marketplace:prepare
```

## فایل‌هایی که باید توسط مالک پروژه تأمین شوند

- بسته Duplicator دموی واقعی (`installer.php` و archive) در `input/demo/`
- آیکون 320×320، کاور 2100×1040 و اینفوگرافی‌های انسانی در `input/assets/`
- نتیجه تست staging، VirusTotal و تأیید سیاست صفحه‌ساز راست چین

بعد از فراهم شدن موارد بالا:

```bash
npm run marketplace:validate
npm run marketplace:build
```

> فایل‌های مشتری، دسترسی‌ها، Merchant ID و archive دیتابیس را داخل Git قرار ندهید.
