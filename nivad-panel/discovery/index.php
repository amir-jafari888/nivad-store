<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت Discovery | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>
<body class="admin-discovery-page">
    <main id="main-content" class="admin-discovery-page-main" data-admin-discovery-page>
        <header class="admin-discovery-heading">
            <div>
                <p class="admin-dashboard-eyebrow">ویترین و کشف محصول</p>
                <h1>مدیریت Discovery</h1>
                <p class="admin-discovery-description">مدیریت تگ‌ها و ارتباط آن‌ها با محصولات، با پیش‌نمایش روشن و بدون نمایش آمار ساختگی.</p>
            </div>
            <a class="admin-discovery-storefront-link" href="<?= e(url('/discover')) ?>" target="_blank" rel="noopener noreferrer">
                مشاهده تجربه مشتری
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 5h5v5M19 5l-8 8"/><path d="M17 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h5"/></svg>
            </a>
        </header>

        <div class="admin-discovery-preview-note" role="note">
            <span class="admin-discovery-note-mark" aria-hidden="true">i</span>
            <p><strong>حالت پیش‌نمایش</strong><span>تغییرات این صفحه فقط در حافظه همین مرورگر نگه داشته می‌شوند و در دیتابیس ذخیره نمی‌شوند.</span></p>
        </div>

        <section class="admin-discovery-summary" aria-label="خلاصه Discovery">
            <article class="admin-discovery-summary-card">
                <span class="admin-discovery-summary-label">تگ‌های مدیریت‌شده</span>
                <strong data-discovery-count>۰</strong>
                <span class="admin-discovery-summary-caption">در این پیش‌نمایش</span>
            </article>
            <article class="admin-discovery-summary-card">
                <span class="admin-discovery-summary-label">محصولات متصل</span>
                <strong>—</strong>
                <span class="admin-discovery-summary-caption">پس از اتصال داده نمایش داده می‌شود</span>
            </article>
            <article class="admin-discovery-summary-card">
                <span class="admin-discovery-summary-label">وضعیت منبع داده</span>
                <strong class="admin-discovery-source-state">متصل نیست</strong>
                <span class="admin-discovery-summary-caption">هیچ آمار واقعی ادعا نمی‌شود</span>
            </article>
        </section>

        <div class="admin-discovery-tabs" role="tablist" aria-label="بخش‌های مدیریت Discovery">
            <button class="admin-discovery-tab is-active" id="discovery-overview-tab" type="button" role="tab" aria-selected="true" aria-controls="discovery-overview-panel" data-discovery-tab="overview">نمای کلی</button>
            <button class="admin-discovery-tab" id="discovery-tags-tab" type="button" role="tab" aria-selected="false" aria-controls="discovery-tags-panel" data-discovery-tab="tags" tabindex="-1">مدیریت تگ‌ها</button>
        </div>

        <section class="admin-discovery-tab-panel" id="discovery-overview-panel" role="tabpanel" aria-labelledby="discovery-overview-tab" data-discovery-panel="overview">
            <article class="admin-discovery-empty">
                <div class="admin-discovery-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="21" cy="21" r="12"/><path d="m30 30 9 9M16 21h10M21 16v10"/></svg>
                </div>
                <h2>نمای کلی آماده اتصال است</h2>
                <p>پس از اتصال منبع داده واقعی، تگ‌های فعال، محصولات مرتبط و وضعیت انتشار در این بخش نمایش داده می‌شوند.</p>
                <button type="button" class="admin-discovery-secondary-action" data-discovery-open-tags>رفتن به مدیریت تگ‌ها</button>
            </article>
        </section>

        <section class="admin-discovery-tab-panel" id="discovery-tags-panel" role="tabpanel" aria-labelledby="discovery-tags-tab" data-discovery-panel="tags" hidden>
            <?php
            define('NIVAD_ADMIN_DISCOVERY_FRAGMENT', true);
            require __DIR__ . '/tags.php';
            ?>
        </section>
    </main>
</body>
</html>
