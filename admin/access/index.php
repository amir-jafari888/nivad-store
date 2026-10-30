<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

?><!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مدیریت دسترسی‌ها | نیواد</title>
</head>
<body class="admin-access-page">
<main class="admin-access-main" data-admin-access data-api-url="<?= e(url('/api/index.php')) ?>">
    <header class="admin-access-header">
        <div><p>ADMIN SECURITY / RBAC</p><h1>مدیریت مدیران و دسترسی‌ها</h1><p>ساخت مدیر، تعریف نقش و تخصیص مجوزهای موجود. نقش Super Admin از تغییرات مخرب محافظت شده است.</p></div>
        <a href="<?= e(url('/admin/operations')) ?>">نقشه قابلیت‌ها</a>
    </header>
    <p class="admin-access-feedback" data-access-feedback role="status" aria-live="polite">در حال بارگذاری اطلاعات…</p>

    <section class="admin-access-panel">
        <h2>ایجاد مدیر جدید</h2>
        <form data-access-form="admin" class="admin-access-form">
            <label>نام کاربری <input name="username" minlength="3" maxlength="100" required></label>
            <label>ایمیل <input name="email" type="email" maxlength="190" required></label>
            <label>رمز عبور اولیه <input name="password" type="password" minlength="12" required autocomplete="new-password"></label>
            <label>نام <input name="first_name" maxlength="100"></label>
            <label>نام خانوادگی <input name="last_name" maxlength="100"></label>
            <label>نقش‌ها <select name="role_ids[]" multiple required data-access-role-options></select></label>
            <button type="submit">ایجاد مدیر</button>
        </form>
        <div data-access-list="admins" class="admin-access-list"></div>
    </section>

    <section class="admin-access-panel">
        <h2>مدیریت نقش‌ها</h2>
        <form data-access-form="role" class="admin-access-form">
            <label>نام نقش <input name="name" maxlength="100" required></label>
            <label>شناسه یکتا (Slug) <input name="slug" maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" required></label>
            <label>توضیحات <input name="description" maxlength="500"></label>
            <button type="submit">ایجاد نقش</button>
        </form>
        <div data-access-list="roles" class="admin-access-list"></div>
    </section>

    <section class="admin-access-panel">
        <h2>تخصیص مجوز به نقش</h2>
        <form data-access-form="permissions" class="admin-access-form">
            <label>انتخاب نقش <select name="role_id" required data-access-role-select></select></label>
            <div class="admin-access-permissions" data-access-permissions></div>
            <button type="submit">ذخیره مجوزهای نقش</button>
        </form>
        <div data-access-list="permissions" class="admin-access-list"></div>
    </section>
</main>
</body>
</html>
