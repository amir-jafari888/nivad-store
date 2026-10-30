<?php
declare(strict_types=1);
$pageTitle = 'پشتیبانی و تیکت‌ها | نیواد';
$baseUrl = $baseUrl ?? '';
include_once __DIR__ . '/../../partials/header.php';
?>
<main class="customer-shop account-page" data-customer-support-page data-api-base="<?= htmlspecialchars(($baseUrl ?? '') . '/api/index.php', ENT_QUOTES, 'UTF-8') ?>">
  <header class="customer-shop__head">
    <span class="customer-shop__eyebrow">پشتیبانی نیواد</span>
    <h1>درخواست‌های پشتیبانی من</h1>
    <p data-support-state role="status" aria-live="polite">در حال دریافت تیکت‌ها…</p>
  </header>
  <section class="customer-shop__panel account-panel">
    <h2>ثبت درخواست جدید</h2>
    <form data-customer-ticket-form class="account-password-form">
      <label><span>موضوع</span><input name="subject" maxlength="255" required></label>
      <label><span>دسته‌بندی</span>
        <select name="category"><option value="general">عمومی</option><option value="orders">سفارش</option><option value="payment">پرداخت</option><option value="shipping">ارسال</option><option value="product">محصول</option><option value="return">مرجوعی</option></select>
      </label>
      <label><span>اولویت</span><select name="priority"><option value="normal">عادی</option><option value="low">کم</option><option value="high">زیاد</option></select></label>
      <label><span>شرح درخواست</span><textarea name="message" rows="4" maxlength="20000" required></textarea></label>
      <button type="submit" class="customer-shop__button">ثبت تیکت</button>
    </form>
    <p data-support-message role="status" aria-live="polite"></p>
  </section>
  <section class="customer-shop__panel account-panel">
    <div class="account-section-head"><div><h2>تاریخچه درخواست‌ها</h2><p>فقط پیام‌های قابل مشاهده برای مشتری نمایش داده می‌شوند.</p></div><button type="button" data-support-refresh>به‌روزرسانی</button></div>
    <div data-customer-ticket-list class="account-sessions" aria-live="polite"></div>
  </section>
  <nav class="account-navigation" aria-label="مدیریت حساب">
    <a href="index.php">حساب من</a><a href="orders/index.php">سفارش‌ها</a><a href="returns/index.php">مرجوعی‌ها</a><a href="notifications.php">اعلان‌ها</a>
  </nav>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
