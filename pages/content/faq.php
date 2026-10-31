<?php
declare(strict_types=1);
$pageTitle = 'سؤالات متداول | نیواد';
$metaDescription = 'پاسخ پرسش‌های رایج درباره پیدا کردن محصول، سفارش، ارسال و مرجوعی در فروشگاه نیواد.';
include_once __DIR__ . '/../../partials/header.php';

if (CmsContentRenderer::renderPage('faq')) {
    include_once __DIR__ . '/../../partials/footer.php';
    return;
}
?>
<main class="content-page" id="main-content">
  <header class="content-page__head">
    <span class="content-page__eyebrow">راهنمای مشتری</span>
    <h1>سؤالات متداول</h1>
    <p>پاسخ‌های کوتاه به پرسش‌های رایج درباره استفاده از فروشگاه نیواد.</p>
  </header>
  <section class="content-page__faq" aria-label="پرسش‌ها و پاسخ‌های متداول">
    <details>
      <summary>چطور محصول موردنظر خود را پیدا کنم؟</summary>
      <p>از جست‌وجوی فروشگاه، دسته‌بندی‌ها و فیلترهای موجود استفاده کنید. مشخصات و سازگاری محصول را پیش از خرید بررسی کنید. <a href="<?= e(url('/products')) ?>">مشاهده محصولات</a></p>
    </details>
    <details>
      <summary>چطور وضعیت سفارش خود را پیگیری کنم؟</summary>
      <p>وارد حساب کاربری شوید و بخش سفارش‌ها را باز کنید. اطلاعات قابل مشاهده به وضعیت ثبت‌شده برای سفارش وابسته است. <a href="<?= e(url('/pages/account/orders/index.php')) ?>">رفتن به سفارش‌ها</a></p>
    </details>
    <details>
      <summary>هزینه و زمان ارسال چطور مشخص می‌شود؟</summary>
      <p>هزینه و گزینه‌های ارسال را در مراحل خرید و اطلاعات سفارش بررسی کنید. این صفحه زمان یا هزینه ثابتی را تضمین نمی‌کند. <a href="<?= e(url('/pages/content/shipping.php')) ?>">راهنمای ارسال</a></p>
    </details>
    <details>
      <summary>چطور درخواست مرجوعی ثبت کنم؟</summary>
      <p>شرایط مربوط به سفارش را در راهنمای مرجوعی بخوانید و در صورت واجد شرایط بودن، از بخش مرجوعی حساب کاربری اقدام کنید. ثبت درخواست به معنی تأیید آن نیست. <a href="<?= e(url('/pages/content/returns.php')) ?>">شرایط مرجوعی</a></p>
    </details>
    <details>
      <summary>چطور برای انتخاب محصول راهنمایی بگیرم؟</summary>
      <p>راهنمای خرید را برای نکات مربوط به شارژر، کابل USB-C و Thunderbolt و کابل HDMI ببینید. <a href="<?= e(url('/pages/content/buying-guide.php')) ?>">مشاهده راهنمای خرید</a></p>
    </details>
    <details>
      <summary>برای مسائل مربوط به حساب کاربری چه کنم؟</summary>
      <p>از صفحه حساب کاربری و بخش مربوط به سفارش‌ها یا مرجوعی استفاده کنید. برای ورود و ثبت‌نام نیز فقط از فرم‌های داخل دامنه رسمی فروشگاه استفاده کنید.</p>
    </details>
  </section>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
