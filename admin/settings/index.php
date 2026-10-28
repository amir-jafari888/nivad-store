<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

$firstName = trim((string)($currentAdmin['first_name'] ?? ''));
$lastName = trim((string)($currentAdmin['last_name'] ?? ''));
$adminName = trim($firstName . ' ' . $lastName);
if ($adminName === '') {
    $adminName = 'مدیر سیستم';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات رابط کاربری | نیواد</title>
</head>
<body class="admin-settings-page">
    <main id="main-content" class="admin-settings-main" tabindex="-1">
        <header class="admin-settings-heading">
            <div>
                <h1>تنظیمات پنل</h1>
                <p>تنظیمات شخصی نمایش پنل؛ این گزینه‌ها فقط در همین مرورگر ذخیره می‌شوند.</p>
            </div>
            <span class="admin-settings-current-user"><?= e($adminName) ?></span>
        </header>

        <form class="admin-settings-form" data-admin-preferences-form>
            <section class="admin-settings-section" aria-labelledby="admin-settings-reading-title">
                <div>
                    <h2 id="admin-settings-reading-title">خوانایی و تراکم</h2>
                    <p>نمایش فشرده‌تر برای جدول‌های طولانی یا فضای بیشتر برای کار روزانه.</p>
                </div>
                <div class="admin-settings-field">
                    <label for="admin-density">تراکم جدول‌ها و فاصله‌ها</label>
                    <select id="admin-density" name="density" data-admin-preference="density">
                        <option value="comfortable">راحت — فاصله بیشتر</option>
                        <option value="compact">فشرده — ردیف‌های بیشتر</option>
                    </select>
                </div>
                <div class="admin-settings-field">
                    <label for="admin-font-scale">اندازه متن</label>
                    <select id="admin-font-scale" name="fontScale" data-admin-preference="fontScale">
                        <option value="normal">معمولی</option>
                        <option value="large">بزرگ‌تر</option>
                    </select>
                </div>
            </section>

            <section class="admin-settings-section" aria-labelledby="admin-settings-navigation-title">
                <div>
                    <h2 id="admin-settings-navigation-title">ناوبری</h2>
                    <p>گروه‌های منو را فقط هنگام نیاز باز کنید؛ باز و بسته‌شدن گروه‌ها روی داده‌های فروشگاه اثری ندارد.</p>
                </div>
                <label class="admin-settings-checkbox">
                    <input type="checkbox" data-admin-preference="openFirstGroup">
                    <span>باز بودن گروه اول منو به‌صورت پیش‌فرض</span>
                </label>
            </section>

            <div class="admin-settings-actions">
                <button type="submit" class="admin-settings-save">ذخیره تنظیمات</button>
                <button type="button" class="admin-settings-reset" data-admin-preferences-reset>بازنشانی</button>
                <p class="admin-settings-feedback" data-admin-preferences-feedback role="status" aria-live="polite"></p>
            </div>
        </form>
    </main>
</body>
</html>
