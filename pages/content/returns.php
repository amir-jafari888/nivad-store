<?php
declare(strict_types=1);

$pageTitle = 'مرجوعی کالا | نیواد';
$metaDescription = 'راهنمای عمومی بررسی شرایط و ثبت درخواست مرجوعی در فروشگاه نیواد؛ پذیرش نهایی وابسته به بررسی سفارش است.';
include_once __DIR__ . '/../../partials/header.php';

if (CmsContentRenderer::renderPage('returns')) {
    include_once __DIR__ . '/../../partials/footer.php';
    return;
}
?>

<main class="content-page" id="main-content">
    <header class="content-page__head">
        <span class="content-page__eyebrow">خدمات پس از خرید</span>
        <h1>مرجوعی کالا</h1>
        <p>امکان ثبت درخواست مرجوعی و نتیجه بررسی آن به سفارش، اقلام خریداری‌شده و شرایط معتبر فروشگاه بستگی دارد. پیش از اقدام، اطلاعات سفارش و شرایط اعلام‌شده برای آن را بررسی کنید.</p>
    </header>
    <section class="content-page__section" aria-labelledby="returns-eligibility">
        <h2 id="returns-eligibility">بررسی شرایط</h2>
        <p>مهلت، استثناها و شرایط پذیرش باید بر اساس سیاست تأییدشده فروشگاه و اطلاعات سفارش تعیین شوند. این صفحه بدون تأیید سیاست رسمی، مهلت یا پذیرش قطعی برای همه کالاها اعلام نمی‌کند.</p>
    </section>

    <section class="content-page__section" aria-labelledby="returns-request">
        <h2 id="returns-request">ثبت درخواست</h2>
        <p>در صورت در دسترس بودن قابلیت مرجوعی برای سفارش، وارد حساب کاربری شوید و از <a href="<?= e(url('/pages/account/returns/index.php')) ?>">بخش مرجوعی</a> درخواست خود را ثبت کنید. اطلاعات اقلام و دلیل درخواست را دقیق وارد کنید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="returns-review">
        <h2 id="returns-review">بررسی و نتیجه</h2>
        <p>ارسال درخواست به معنی تأیید آن یا تضمین بازپرداخت نیست. وضعیت و نتیجه را در جزئیات درخواست و حساب کاربری بررسی کنید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="returns-help">
        <h2 id="returns-help">پرسش‌های بیشتر</h2>
        <p>برای پرسش‌های عمومی به <a href="<?= e(url('/pages/content/faq.php')) ?>">سؤالات متداول</a> مراجعه کنید. هرگونه مهلت، هزینه یا روش بازپرداخت باید پیش از انتشار به‌صورت رسمی تأیید شود.</p>
    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
