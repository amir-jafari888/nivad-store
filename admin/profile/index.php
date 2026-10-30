<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

$admin = is_array($currentAdmin ?? null) ? $currentAdmin : [];
$firstName = trim((string)($admin['first_name'] ?? ''));
$lastName = trim((string)($admin['last_name'] ?? ''));
$displayName = trim($firstName . ' ' . $lastName);
if ($displayName === '') {
    $displayName = 'نام مدیر ثبت نشده';
}
$adminId = isset($admin['id']) ? (int)$admin['id'] : null;
$status = trim((string)($admin['status'] ?? ''));
$lastLoginAt = trim((string)($admin['last_login_at'] ?? ''));
$createdAt = trim((string)($admin['created_at'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>پروفایل مدیر | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>
<body class="admin-settings-page">
    <a class="admin-skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <main id="main-content" class="admin-settings-main" tabindex="-1">
        <header class="admin-settings-heading">
            <div>
                <p class="admin-settings-eyebrow">هویت حساب</p>
                <h1>پروفایل مدیر</h1>
                <p>اطلاعات هویتی امن حسابی که در این نشست وارد پنل شده است.</p>
            </div>
            <span class="admin-settings-current-user"><?= e($displayName) ?></span>
        </header>

        <section class="admin-settings-section" aria-labelledby="admin-profile-identity-title">
            <div>
                <h2 id="admin-profile-identity-title">اطلاعات هویتی</h2>
                <p>این صفحه فقط اطلاعات تأییدشده موجود در نشست مدیر را نمایش می‌دهد. ویرایش پروفایل تا زمان وجود قرارداد به‌روزرسانی معتبر فعال نمی‌شود.</p>
            </div>
            <dl class="admin-profile-details">
                <div>
                    <dt>نام</dt>
                    <dd><?= e($firstName !== '' ? $firstName : 'ثبت نشده') ?></dd>
                </div>
                <div>
                    <dt>نام خانوادگی</dt>
                    <dd><?= e($lastName !== '' ? $lastName : 'ثبت نشده') ?></dd>
                </div>
                <div>
                    <dt>شناسه مدیر</dt>
                    <dd><?= $adminId !== null && $adminId > 0 ? e((string)$adminId) : 'در دسترس نیست' ?></dd>
                </div>
                <div>
                    <dt>وضعیت حساب</dt>
                    <dd><?= e($status !== '' ? $status : 'نامشخص') ?></dd>
                </div>
                <div>
                    <dt>آخرین ورود ثبت‌شده</dt>
                    <dd><?= e($lastLoginAt !== '' ? $lastLoginAt : 'ثبت نشده') ?></dd>
                </div>
                <div>
                    <dt>تاریخ ایجاد حساب</dt>
                    <dd><?= e($createdAt !== '' ? $createdAt : 'در دسترس نیست') ?></dd>
                </div>
            </dl>
        </section>
    </main>
</body>
</html>
