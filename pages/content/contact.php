<?php
declare(strict_types=1);

$pageTitle = 'تماس با ما | نیواد';
$metaDescription = 'راهنماهای ارتباط با نیواد برای پرسش‌های مربوط به محصولات، سفارش‌ها و خدمات فروشگاه.';
include_once __DIR__ . '/../../partials/header.php';

if (CmsContentRenderer::renderPage('contact')) {
    include_once __DIR__ . '/../../partials/footer.php';
    return;
}
?>

<main class="content-page" id="main-content">
    <header class="content-page__head">
        <span class="content-page__eyebrow">پشتیبانی مشتریان</span>
        <h1>تماس با ما</h1>
        <p>اگر درباره محصول یا سفارش خود پرسشی دارید، ابتدا راهنمای مرتبط را بررسی کنید. اطلاعات تماس مستقیم تنها زمانی نمایش داده می‌شود که مسیر رسمی و تأییدشده‌ای برای آن در پروژه تعریف شده باشد.</p>
    </header>
    <section class="content-page__section" aria-labelledby="contact-orders">
        <h2 id="contact-orders">پرسش درباره سفارش</h2>
        <p>برای بررسی سفارش‌های ثبت‌شده، از <a href="<?= e(url('/pages/account/orders/index.php')) ?>">بخش سفارش‌های حساب کاربری</a> استفاده کنید. مشاهده این بخش به ورود به حساب نیاز دارد.</p>
    </section>

    <section class="content-page__section" aria-labelledby="contact-returns">
        <h2 id="contact-returns">درخواست مرجوعی</h2>
        <p>برای سفارش واجد شرایط، جزئیات و وضعیت درخواست را از <a href="<?= e(url('/pages/account/returns/index.php')) ?>">بخش مرجوعی حساب کاربری</a> پیگیری کنید. ثبت درخواست به معنی تأیید خودکار آن نیست.</p>
    </section>

    <section class="content-page__section" aria-labelledby="contact-help">
        <h2 id="contact-help">راهنماهای خودیاری</h2>
        <p>پاسخ پرسش‌های رایج را در <a href="<?= e(url('/pages/content/faq.php')) ?>">سؤالات متداول</a> و اطلاعات ارسال را در <a href="<?= e(url('/pages/content/shipping.php')) ?>">راهنمای ارسال سفارش</a> بخوانید.</p>
    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
