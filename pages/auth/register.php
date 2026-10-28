<?php
declare(strict_types=1);
$pageTitle = 'ثبت‌نام | نیواد';
include_once __DIR__ . '/../../partials/header.php';
?>
<main id="main-content" tabindex="-1" class="auth-page" data-auth-page>
  <header class="auth-page__head">
    <h1>ساخت حساب کاربری</h1>
    <p>برای ایجاد حساب، اطلاعات زیر را وارد کنید.</p>
  </header>
  <section class="auth-card" aria-labelledby="auth-register-title">
    <h2 id="auth-register-title" class="auth-card__title">اطلاعات حساب</h2>
    <form class="auth-form" data-auth-register-form>
      <label for="auth-first-name">نام
        <input id="auth-first-name" type="text" name="first_name" autocomplete="given-name" maxlength="100" required>
      </label>
      <label for="auth-last-name">نام خانوادگی
        <input id="auth-last-name" type="text" name="last_name" autocomplete="family-name" maxlength="100" required>
      </label>
      <label for="auth-register-email">ایمیل
        <input id="auth-register-email" type="email" name="email" autocomplete="email" maxlength="255" required>
      </label>
      <label for="auth-phone">شماره موبایل
        <input id="auth-phone" type="tel" name="phone" autocomplete="tel" inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" required>
      </label>
      <label for="auth-register-password">رمز عبور
        <input id="auth-register-password" type="password" name="password" autocomplete="new-password" minlength="8" maxlength="255" required>
      </label>
      <label for="auth-password-confirmation">تکرار رمز عبور
        <input id="auth-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" maxlength="255" required>
      </label>
      <button type="submit">ثبت‌نام</button>
      <p class="auth-message" data-auth-message aria-live="polite" role="status"></p>
    </form>
    <nav class="auth-links" aria-label="پیوندهای حساب کاربری">
      <a href="<?= e(url('/pages/auth/login.php')) ?>">قبلاً حساب دارید؟ وارد شوید</a>
      <a href="<?= e(url('/pages/auth/verify-email.php')) ?>">تأیید ایمیل</a>
    </nav>
  </section>
</main>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
