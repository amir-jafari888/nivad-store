<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

if (!$adminAuth->can('admin.capabilities.incidents.index')) {
    Response::redirect(url('/admin/access-denied.php'));
}

?><!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title>مدیریت رخدادها | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/incidents.css')) ?>">
</head>
<body class="admin-incidents-page">
<main id="main-content" class="admin-incidents-main" data-admin-incidents data-api-url="<?= e(url('/api/index.php')) ?>">
    <header class="admin-incidents-header">
        <div><p>NIVAD / INCIDENT OPERATIONS</p><h1>مدیریت رخدادها</h1><p>تشخیص، تحلیل فراوانی، شدت، تاریخچه و پایش صف‌های موجود.</p></div>
        <a href="<?= e(url('/admin/capabilities/index.php')) ?>">بازگشت به مرکز عملیات</a>
    </header>
    <section class="admin-incidents-filters" aria-label="فیلتر رخدادها">
        <label>وضعیت
            <select data-filter-status>
                <option value="">همه وضعیت‌ها</option><option value="open">باز</option>
                <option value="acknowledged">در حال بررسی</option><option value="resolved">حل‌شده</option>
                <option value="ignored">نادیده‌گرفته‌شده</option>
            </select>
        </label>
        <label>شدت
            <select data-filter-severity>
                <option value="">همه شدت‌ها</option><option value="critical">بحرانی</option>
                <option value="warning">هشدار</option><option value="info">اطلاع</option>
            </select>
        </label>
        <label>تحلیل بازه
            <select data-filter-hours><option value="24">۲۴ ساعت</option><option value="72">۷۲ ساعت</option><option value="168">۷ روز</option><option value="720">۳۰ روز</option></select>
        </label>
        <button type="button" data-refresh>به‌روزرسانی</button>
    </section>
    <p role="status" aria-live="polite" data-feedback>در حال دریافت رخدادها…</p>
    <section class="admin-incidents-panel"><h2>تحلیل شدت و فراوانی</h2><div data-analysis></div></section>
    <section class="admin-incidents-panel"><h2>رخدادها</h2><div class="admin-incidents-table-wrap"><table><thead><tr><th>عنوان</th><th>منشأ</th><th>شدت</th><th>وضعیت</th><th>تکرار</th><th>اولین مشاهده</th><th>آخرین مشاهده</th><th>Timeline / اقدام</th></tr></thead><tbody data-incidents></tbody></table></div></section>
    <section class="admin-incidents-panel"><h2>پایش صف‌های موجود</h2><div data-jobs></div></section>
</main>
</body>
</html>
