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
$profileInitial = mb_substr($displayName, 0, 1, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>پروفایل مدیر | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>?v=20261107-profile-v1">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>
<body class="admin-settings-page admin-profile-page">
    <a class="admin-skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <main id="main-content" class="admin-settings-main admin-profile-main" tabindex="-1">
        <header class="admin-settings-heading admin-profile-heading">
            <div class="admin-profile-heading-copy">
                <p class="admin-settings-eyebrow">هویت حساب</p>
                <h1>پروفایل مدیر</h1>
                <p>نمای کلی اطلاعات هویتی و وضعیت حسابی که در این نشست وارد پنل شده است.</p>
            </div>
            <div class="admin-profile-heading-identity">
                <span class="admin-profile-avatar" aria-hidden="true"><?= e($profileInitial) ?></span>
                <span class="admin-settings-current-user admin-profile-current-user"><?= e($displayName) ?></span>
            </div>
        </header>

        <section class="admin-settings-section admin-profile-card" aria-labelledby="admin-profile-identity-title">
            <div class="admin-profile-card-intro">
                <div>
                    <p class="admin-profile-section-eyebrow">مشخصات حساب</p>
                    <h2 id="admin-profile-identity-title">اطلاعات هویتی</h2>
                </div>
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
