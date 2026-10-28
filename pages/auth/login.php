<?php
declare(strict_types=1);
$pageTitle = 'ورود | نیواد';
include_once __DIR__ . '/../../partials/header.php';
?>
<main id="main-content" tabindex="-1" class="auth-page" data-auth-page data-redirect="<?= e(url('/pages/account/index.php')) ?>">
  <header class="auth-page__head">
    <h1>ورود به حساب</h1>
    <p>برای ادامه، وارد حساب کاربری خود شوید.</p>
  </header>
  <section class="auth-card" aria-labelledby="auth-login-title">
    <h2 id="auth-login-title" class="auth-card__title">ورود</h2>
    <form class="auth-form" data-auth-login-form>
      <label for="auth-identifier">ایمیل یا شماره موبایل
        <input id="auth-identifier" type="text" name="identifier" autocomplete="username" required aria-describedby="auth-identifier-hint">
      </label>
      <small id="auth-identifier-hint" class="auth-hint">ایمیل یا شماره موبایل ثبت‌شده را وارد کنید.</small>
      <label for="auth-password">رمز عبور
        <input id="auth-password" type="password" name="password" autocomplete="current-password" required>
      </label>
      <button type="submit">ورود</button>
      <p class="auth-message" data-auth-message aria-live="polite" role="status"></p>
    </form>
    <nav class="auth-links" aria-label="پیوندهای حساب کاربری">
      <a href="<?= e(url('/pages/auth/register.php')) ?>">ساخت حساب</a>
      <a href="<?= e(url('/pages/auth/forgot-password.php')) ?>">فراموشی رمز عبور</a>
      <a href="<?= e(url('/pages/auth/verify-email.php')) ?>">تأیید ایمیل</a>
    </nav>
  </section>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
