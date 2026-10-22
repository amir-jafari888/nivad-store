<?php
declare(strict_types=1);
$pageTitle = 'تغییر رمز عبور | نیواد';
include_once __DIR__ . '/../../partials/header.php';
?>
<main class="auth-page" data-auth-page>
  <header class="auth-page__head">
    <h1>تغییر رمز عبور</h1>
    <p>توکن بازیابی و رمز عبور جدید را وارد کنید.</p>
  </header>
  <section class="auth-card" aria-labelledby="auth-reset-title">
    <h2 id="auth-reset-title" class="auth-card__title">رمز عبور جدید</h2>
    <form class="auth-form" data-auth-reset-form>
      <label for="auth-reset-token">توکن بازیابی
        <input id="auth-reset-token" type="text" name="token" autocomplete="off" required>
      </label>
      <label for="auth-new-password">رمز عبور جدید
        <input id="auth-new-password" type="password" name="new_password" autocomplete="new-password" minlength="8" maxlength="255" required>
      </label>
      <label for="auth-new-password-confirmation">تکرار رمز عبور جدید
        <input id="auth-new-password-confirmation" type="password" name="new_password_confirmation" autocomplete="new-password" minlength="8" maxlength="255" required>
      </label>
      <button type="submit">تغییر رمز عبور</button>
      <p class="auth-message" data-auth-message aria-live="polite" role="status"></p>
    </form>
    <nav class="auth-links" aria-label="پیوندهای بازیابی">
      <a href="<?= e(url('/pages/auth/login.php')) ?>">بازگشت به ورود</a>
    </nav>
  </section>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
