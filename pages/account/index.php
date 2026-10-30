<?php
declare(strict_types=1);
$pageTitle = 'حساب کاربری | نیواد';
include_once __DIR__ . '/../../partials/header.php';
?>
<main class="customer-shop account-page" data-account-page data-api-base="<?= htmlspecialchars(($baseUrl ?? '') . '/api/index.php', ENT_QUOTES, 'UTF-8') ?>">
  <header class="customer-shop__head"><span class="customer-shop__eyebrow">حساب کاربری</span><h1>حساب من</h1><p data-account-state role="status" aria-live="polite">در حال دریافت اطلاعات حساب...</p></header>
  <section class="account-grid" aria-label="خلاصه حساب">
    <article class="customer-shop__panel" data-account-security aria-live="polite"><h2>امنیت حساب</h2><p>در حال دریافت اطلاعات...</p></article>
    <article class="customer-shop__panel" data-account-verification aria-live="polite"><h2>تأیید حساب</h2><p>در حال دریافت اطلاعات...</p></article>
  </section>
  <section class="account-grid account-metrics" aria-label="خلاصه فعالیت">
    <article class="customer-shop__panel"><h2>اعلان‌های خوانده‌نشده</h2><p><strong data-account-metric="notifications">—</strong></p></article>
    <article class="customer-shop__panel"><h2>تعداد سفارش‌ها</h2><p><strong data-account-metric="orders">—</strong></p></article>
  </section>
  <section class="customer-shop__panel account-panel">
    <div class="account-section-head"><div><h2>نشست‌های فعال دیگر</h2><p>نشست‌های دیگر حساب خود را بررسی و مدیریت کنید.</p></div><button type="button" data-logout-other-sessions>بستن نشست‌های دیگر</button></div>
    <div data-account-sessions class="account-sessions" aria-live="polite">در حال دریافت نشست‌ها...</div>
  </section>
  <section class="customer-shop__panel account-panel"><h2>تغییر رمز عبور</h2>
    <form data-password-form class="account-password-form">
      <label><span>رمز عبور فعلی</span><input type="password" name="current_password" autocomplete="current-password" required></label>
      <label><span>رمز عبور جدید</span><input type="password" name="new_password" autocomplete="new-password" minlength="8" required></label>
      <label><span>تکرار رمز عبور جدید</span><input type="password" name="new_password_confirmation" autocomplete="new-password" minlength="8" required></label>
      <button type="submit" class="customer-shop__button">تغییر رمز عبور</button>
    </form><p data-account-message role="status" aria-live="polite"></p>
  </section>
  <nav class="account-navigation" aria-label="مدیریت حساب">
    <a href="profile.php">پروفایل</a><a href="addresses.php">آدرس‌ها</a><a href="orders/index.php">سفارش‌ها</a><a href="returns/index.php">مرجوعی‌ها</a><a href="reviews.php">دیدگاه‌ها</a><a href="notifications.php">اعلان‌ها</a><a href="<?= htmlspecialchars(($baseUrl ?? '') . '/account/support', ENT_QUOTES, 'UTF-8') ?>">پشتیبانی</a>
    <a href="<?= htmlspecialchars(($baseUrl ?? '') . '/wishlist', ENT_QUOTES, 'UTF-8') ?>">علاقه‌مندی‌ها</a><a href="<?= htmlspecialchars(($baseUrl ?? '') . '/cart', ENT_QUOTES, 'UTF-8') ?>">سبد خرید</a>
  </nav>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
