<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

if (!$adminAuth->can('admin.system_health.view')) {
    Response::redirect(url('/admin/access-denied.php'));
}

?><!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>پایش بهره‌وری و سلامت | نیواد</title>
</head>
<body class="admin-productivity-page">
<main class="admin-productivity-main" data-admin-productivity-diagnostics data-api-url="<?= e(url('/api/index.php')) ?>">
    <header class="admin-productivity-header">
        <div>
            <p>NIVAD / PRODUCTIVITY &amp; OPERATIONS</p>
            <h1>پایش بهره‌وری و سلامت عملیاتی</h1>
            <p>وضعیت صف‌ها، شواهد پشتیبان‌گیری، تله‌متری درگاه، Webhook و ناهنجاری موجودی.</p>
        </div>
        <button type="button" data-productivity-refresh>به‌روزرسانی وضعیت</button>
    </header>
    <p class="admin-productivity-feedback" data-productivity-feedback role="status" aria-live="polite">در حال دریافت وضعیت…</p>
    <div class="admin-productivity-grid" data-productivity-results></div>
</main>
</body>
</html>
