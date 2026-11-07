<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

$admin = is_array($currentAdmin ?? null) ? $currentAdmin : [];
$adminId = (int)($admin['id'] ?? 0);
$firstName = trim((string)($admin['first_name'] ?? ''));
$lastName = trim((string)($admin['last_name'] ?? ''));
$displayName = trim($firstName . ' ' . $lastName);
if ($displayName === '') {
    $displayName = 'نام مدیر ثبت نشده';
}
$roles = (new AdminRepository())->roleNamesForAdmin($adminId);
$statusLabels = [
    'active' => 'فعال',
    'inactive' => 'غیرفعال',
    'blocked' => 'مسدود',
    'pending' => 'در انتظار',
];
$status = $statusLabels[(string)($admin['status'] ?? '')] ?? 'نامشخص';
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
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>?v=20261107-profile-edit-v1">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>
<body class="admin-settings-page admin-profile-page">
    <a class="admin-skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <main id="main-content" class="admin-settings-main admin-profile-main" tabindex="-1">
        <header class="admin-settings-heading admin-profile-heading">
            <div class="admin-profile-heading-copy">
                <p class="admin-settings-eyebrow">هویت حساب</p>
                <h1>پروفایل مدیر</h1>
                <p>اطلاعات شخصی و امنیت حساب خود را مدیریت کنید. نقش دسترسی از این صفحه قابل تغییر نیست.</p>
            </div>
            <div class="admin-profile-heading-identity">
                <span class="admin-profile-avatar" aria-hidden="true"><?= e($profileInitial) ?></span>
                <span class="admin-settings-current-user admin-profile-current-user" data-profile-display-name><?= e($displayName) ?></span>
            </div>
        </header>

        <div class="admin-profile-feedback" data-profile-feedback role="status" aria-live="polite"></div>

        <section class="admin-settings-section admin-profile-card" aria-labelledby="admin-profile-identity-title">
            <div class="admin-profile-card-intro">
                <div>
                    <p class="admin-profile-section-eyebrow">مشخصات حساب</p>
                    <h2 id="admin-profile-identity-title">اطلاعات شخصی</h2>
                </div>
                <p>برای ذخیره تغییرات، رمز عبور فعلی حساب از شما درخواست می‌شود.</p>
            </div>
            <form class="admin-profile-form" data-admin-profile-form>
                <div class="admin-profile-fields">
                    <div class="admin-profile-field">
                        <label for="admin-profile-first-name">نام</label>
                        <input id="admin-profile-first-name" name="first_name" type="text" maxlength="100" autocomplete="given-name" value="<?= e($firstName) ?>" required>
                    </div>
                    <div class="admin-profile-field">
                        <label for="admin-profile-last-name">نام خانوادگی</label>
                        <input id="admin-profile-last-name" name="last_name" type="text" maxlength="100" autocomplete="family-name" value="<?= e($lastName) ?>" required>
                    </div>
                    <div class="admin-profile-field">
                        <label for="admin-profile-email">ایمیل</label>
                        <input id="admin-profile-email" name="email" type="email" maxlength="190" autocomplete="email" value="<?= e((string)($admin['email'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-profile-field">
                        <label for="admin-profile-username">نام کاربری</label>
                        <input id="admin-profile-username" name="username" type="text" minlength="3" maxlength="100" pattern="[A-Za-z0-9_.-]{3,100}" autocomplete="username" value="<?= e((string)($admin['username'] ?? '')) ?>" required>
                        <small>۳ تا ۱۰۰ نویسه؛ فقط حروف لاتین، عدد و . _ -</small>
                    </div>
                </div>
                <div class="admin-profile-actions">
                    <button class="admin-settings-save" type="submit">ذخیره اطلاعات شخصی</button>
                </div>
            </form>
        </section>

        <section class="admin-settings-section admin-profile-card" aria-labelledby="admin-profile-password-title">
            <div class="admin-profile-card-intro">
                <div>
                    <p class="admin-profile-section-eyebrow">امنیت حساب</p>
                    <h2 id="admin-profile-password-title">تغییر رمز عبور</h2>
                </div>
                <p>رمز عبور جدید باید حداقل ۱۲ نویسه داشته باشد. برای تأیید عملیات، رمز فعلی لازم است.</p>
            </div>
            <form class="admin-profile-form" data-admin-password-form>
                <div class="admin-profile-fields admin-profile-password-fields">
                    <div class="admin-profile-field">
                        <label for="admin-profile-new-password">رمز عبور جدید</label>
                        <input id="admin-profile-new-password" name="new_password" type="password" minlength="12" maxlength="1024" autocomplete="new-password" required>
                    </div>
                    <div class="admin-profile-field">
                        <label for="admin-profile-confirm-password">تکرار رمز عبور جدید</label>
                        <input id="admin-profile-confirm-password" name="confirm_password" type="password" minlength="12" maxlength="1024" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="admin-profile-actions">
                    <button class="admin-settings-save" type="submit">تغییر رمز عبور</button>
                </div>
            </form>
        </section>

        <section class="admin-settings-section admin-profile-card" aria-labelledby="admin-profile-access-title">
            <div class="admin-profile-card-intro">
                <div>
                    <p class="admin-profile-section-eyebrow">دسترسی‌ها</p>
                    <h2 id="admin-profile-access-title">نقش مدیر</h2>
                </div>
                <p>این نقش‌ها فقط برای اطلاع نمایش داده می‌شوند و از پروفایل قابل ویرایش نیستند.</p>
            </div>
            <div class="admin-profile-role-list" aria-label="نقش‌های تخصیص‌یافته">
                <?php if ($roles !== []): ?>
                    <?php foreach ($roles as $roleName): ?>
                        <span class="admin-profile-role"><?= e($roleName) ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="admin-profile-role admin-profile-role-empty">نقش فعالی برای این حساب ثبت نشده است.</span>
                <?php endif; ?>
            </div>
            <dl class="admin-profile-details">
                <div><dt>شناسه مدیر</dt><dd><?= $adminId > 0 ? e((string)$adminId) : 'در دسترس نیست' ?></dd></div>
                <div><dt>وضعیت حساب</dt><dd><?= e($status) ?></dd></div>
                <div><dt>آخرین ورود ثبت‌شده</dt><dd><?= e($lastLoginAt !== '' ? $lastLoginAt : 'ثبت نشده') ?></dd></div>
                <div><dt>تاریخ ایجاد حساب</dt><dd><?= e($createdAt !== '' ? $createdAt : 'در دسترس نیست') ?></dd></div>
            </dl>
        </section>
    </main>
</body>
</html>
