<?php
declare(strict_types=1);

$pageTitle = 'شرایط استفاده | نیواد';
$metaDescription = 'شرایط عمومی استفاده از امکانات فروشگاه نیواد؛ نسخه نهایی باید پیش از انتشار توسط مالک کسب‌وکار تأیید شود.';
include_once __DIR__ . '/../../partials/header.php';
?>

<main class="content-page" id="main-content">
    <header class="content-page__head">
        <span class="content-page__eyebrow">قوانین فروشگاه</span>
        <h1>شرایط استفاده</h1>
        <p>با استفاده از امکانات فروشگاه، لازم است اطلاعات نمایش‌داده‌شده در هر مرحله را با دقت بررسی کنید. این متن چارچوب عمومی است و جایگزین شرایط حقوقی نهایی و تأییدشده کسب‌وکار نیست.</p>
    </header>
    <section class="content-page__section" aria-labelledby="terms-account">
        <h2 id="terms-account">حساب کاربری</h2>
        <p>اطلاعات ثبت‌نام و سفارش را دقیق وارد کنید و از دسترسی دیگران به حساب خود جلوگیری کنید. در صورت مشاهده فعالیت ناشناس، از امکانات امنیتی حساب استفاده کنید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="terms-product">
        <h2 id="terms-product">اطلاعات محصول و قیمت</h2>
        <p>مشخصات، قیمت، گزینه‌ها و موجودی قابل مشاهده را در صفحه محصول و پیش از تأیید نهایی سفارش بررسی کنید. ثبت اطلاعات در فرم به‌تنهایی به معنی تأیید یا تکمیل سفارش نیست؛ وضعیت نهایی را از پیام سامانه و بخش سفارش‌ها بررسی کنید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="terms-order">
        <h2 id="terms-order">ثبت و پیگیری سفارش</h2>
        <p>پیش از ثبت نهایی، اقلام، تعداد، نشانی و خلاصه هزینه‌ها را بررسی کنید. هرگونه تغییر وضعیت سفارش باید بر اساس اطلاعات ثبت‌شده در سامانه پیگیری شود.</p>
    </section>

    <section class="content-page__section" aria-labelledby="terms-returns">
        <h2 id="terms-returns">ارسال و مرجوعی</h2>
        <p>اطلاعات مربوط به ارسال و شرایط مرجوعی در صفحات <a href="<?= e(url('/pages/content/shipping.php')) ?>">ارسال سفارش</a> و <a href="<?= e(url('/pages/content/returns.php')) ?>">مرجوعی</a> ارائه می‌شود. شرایط نهایی کسب‌وکار باید پیش از انتشار این صفحه تأیید شود.</p>
    </section>

    <section class="content-page__section" aria-labelledby="terms-review">
        <h2 id="terms-review">بازبینی شرایط</h2>
        <p>نسخه نهایی باید شامل تاریخ اجرا، اطلاعات مسئول کسب‌وکار و مفاد حقوقی تأییدشده باشد. تا پیش از این تأیید، این صفحه نباید به‌عنوان مشاوره حقوقی یا سند حقوقی کامل تلقی شود.</p>
    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
