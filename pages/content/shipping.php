<?php
declare(strict_types=1);

$pageTitle = 'ارسال سفارش | نیواد';
$metaDescription = 'راهنمای بررسی گزینه‌ها، هزینه و وضعیت ارسال سفارش در فروشگاه نیواد.';
include_once __DIR__ . '/../../partials/header.php';
?>

<main class="content-page" id="main-content">
    <header class="content-page__head">
        <span class="content-page__eyebrow">تحویل سفارش</span>
        <h1>ارسال سفارش</h1>
        <p>گزینه‌ها و جزئیات ارسال ممکن است بر اساس سفارش و نشانی تحویل متفاوت باشند. اطلاعات نهایی و قابل اتکا را در مراحل خرید و جزئیات سفارش خود بررسی کنید.</p>
    </header>
    <section class="content-page__section" aria-labelledby="shipping-options">
        <h2 id="shipping-options">گزینه‌های ارسال</h2>
        <p>روش‌های قابل انتخاب، در صورت پشتیبانی، هنگام ثبت سفارش نمایش داده می‌شوند. اگر گزینه‌ای برای نشانی یا سفارش شما نمایش داده نمی‌شود، آن را در دسترس فرض نکنید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="shipping-cost">
        <h2 id="shipping-cost">هزینه و زمان تحویل</h2>
        <p>هزینه و برآورد زمان تحویل را از خلاصه سفارش و اطلاعات ثبت‌شده همان سفارش بررسی کنید. این صفحه عدد ثابت یا زمان تحویل تضمین‌شده‌ای اعلام نمی‌کند.</p>
    </section>

    <section class="content-page__section" aria-labelledby="shipping-tracking">
        <h2 id="shipping-tracking">پیگیری سفارش</h2>
        <p>پس از ثبت سفارش، وضعیت موجود را در <a href="<?= e(url('/pages/account/orders/index.php')) ?>">بخش سفارش‌های حساب کاربری</a> بررسی کنید. جزئیات پیگیری به اطلاعاتی وابسته است که برای سفارش ثبت شده است.</p>
    </section>

    <section class="content-page__section" aria-labelledby="shipping-help">
        <h2 id="shipping-help">نیاز به راهنمایی دارید؟</h2>
        <p>ابتدا <a href="<?= e(url('/pages/content/faq.php')) ?>">سؤالات متداول</a> را ببینید. برای سفارش ثبت‌شده، اطلاعات همان سفارش را آماده داشته باشید.</p>
    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
