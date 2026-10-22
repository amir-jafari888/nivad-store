<?php
declare(strict_types=1);
$pageTitle = 'تأیید ایمیل | نیواد';
include_once __DIR__ . '/../../partials/header.php';
?>
<main class="auth-page" data-auth-page>
  <header class="auth-page__head">
    <h1>تأیید ایمیل</h1>
    <p>برای تأیید ایمیل، توکن دریافتی را وارد کنید یا درخواست ارسال مجدد بدهید.</p>
  </header>
  <section class="auth-card" aria-labelledby="auth-verify-title">
    <h2 id="auth-verify-title" class="auth-card__title">تأیید ایمیل</h2>
    <form class="auth-form" data-auth-verify-form novalidate>
      <label for="auth-verify-token">توکن تأیید
        <input id="auth-verify-token" type="text" name="token" autocomplete="off" required>
      </label>
      <button type="submit">تأیید ایمیل</button>
      <p class="auth-message" data-auth-message aria-live="polite" role="status"></p>
    </form>
    <div class="auth-divider" aria-hidden="true"></div>
    <form class="auth-form" data-auth-resend-form novalidate>
      <label for="auth-resend-email">ایمیل برای ارسال مجدد
        <input id="auth-resend-email" type="email" name="email" autocomplete="email" maxlength="190" required>
      </label>
      <button type="submit">ارسال مجدد تأیید ایمیل</button>
      <p class="auth-message" data-auth-message aria-live="polite" role="status"></p>
    </form>
    <nav class="auth-links" aria-label="پیوندهای حساب کاربری">
      <a href="<?= e(url('/pages/auth/login.php')) ?>">بازگشت به ورود</a>
    </nav>
  </section>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
