<?php
declare(strict_types=1);
$pageTitle = 'بازیابی رمز عبور | نیواد';
include_once __DIR__ . '/../../partials/header.php';
?>
<main class="auth-page" data-auth-page>
  <header class="auth-page__head">
    <h1>بازیابی رمز عبور</h1>
    <p>ایمیل حساب خود را وارد کنید تا در صورت وجود حساب، راهنمای بازیابی ارسال شود.</p>
  </header>
  <section class="auth-card" aria-labelledby="auth-forgot-title">
    <h2 id="auth-forgot-title" class="auth-card__title">درخواست بازیابی</h2>
    <form class="auth-form" data-auth-forgot-form>
      <label for="auth-forgot-email">ایمیل
        <input id="auth-forgot-email" type="email" name="email" autocomplete="email" maxlength="255" required>
      </label>
      <button type="submit">ارسال درخواست بازیابی</button>
      <p class="auth-message" data-auth-message aria-live="polite" role="status"></p>
    </form>
    <nav class="auth-links" aria-label="پیوندهای بازیابی">
      <a href="<?= e(url('/pages/auth/login.php')) ?>">بازگشت به ورود</a>
    </nav>
  </section>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
