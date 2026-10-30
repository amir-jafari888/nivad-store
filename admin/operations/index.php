<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

$sections = [
    [
        'title' => 'مرکز فرماندهی',
        'description' => 'نمای کلی فروشگاه و میانبرهای عملیاتی.',
        'items' => [
            ['id' => 'dashboard', 'title' => 'داشبورد مدیریتی', 'status' => 'موجود / ناقص', 'detail' => 'KPIهای سفارش، پرداخت، آیتم سفارش و موجودی وجود دارند؛ نمودارها و Activity Feed هنوز کامل متصل نیستند.', 'href' => url('/admin')],
            ['id' => 'action-queue', 'title' => 'صف اقدامات', 'status' => 'نیازمند پیاده‌سازی', 'detail' => 'برای کارهای منتظر بررسی، تأیید یا اقدام مدیر؛ منبع داده فعلی تأیید نشده است.', 'href' => '#action-queue'],
        ],
    ],
    [
        'title' => 'مدیریت و امنیت',
        'description' => 'هویت مدیران، نقش‌ها، مجوزها و نشست‌های مدیریتی.',
        'items' => [
            ['id' => 'admin-management', 'title' => 'مدیریت مدیران', 'status' => 'Backend ناقص / UI مفقود', 'detail' => 'admin_users و admin_user_roles و AdminRepository موجودند؛ CRUD مدیریتی و تخصیص نقش در UI هنوز ساخته نشده است.', 'href' => '#admin-management'],
            ['id' => 'roles-permissions', 'title' => 'نقش‌ها و مجوزها', 'status' => 'زیرساخت موجود / UI مفقود', 'detail' => 'admin_roles، admin_permissions، admin_role_permissions و سرویس Authorization وجود دارند. مجوزهای Route با migration جدید seed می‌شوند.', 'href' => '#roles-permissions'],
            ['id' => 'session-monitoring', 'title' => 'پایش نشست مدیران', 'status' => 'Backend موجود / UI مفقود', 'detail' => 'admin_sessions و AdminSessionService موجودند؛ فهرست نشست‌ها و لغو انتخابی از پنل نیازمند تکمیل است.', 'href' => '#session-monitoring'],
            ['id' => 'audit-logs', 'title' => 'گزارش رویدادها', 'status' => 'موجود / پوشش ناقص', 'detail' => 'audit_logs و صفحه مشاهده وجود دارند؛ ثبت تمام عملیات حساس هنوز اثبات نشده است.', 'href' => url('/admin/audit-logs')],
            ['id' => 'access-denied', 'title' => 'صفحه عدم دسترسی', 'status' => 'UI مفقود', 'detail' => 'کنترل دسترسی سمت سرور وجود دارد؛ تجربه اختصاصی 403 برای پنل باید طراحی و متصل شود.', 'href' => '#access-denied'],
        ],
    ],
    [
        'title' => 'پایش و تحلیل',
        'description' => 'اعلان‌های مدیریتی، رخدادها، سلامت سیستم و رفتار مشتری.',
        'items' => [
            ['id' => 'admin-notifications', 'title' => 'مرکز اعلان مدیران', 'status' => 'Schema در branch / API و UI مفقود', 'detail' => 'notifications فعلی فقط به users متصل است؛ جدول admin_notifications در migration جدید تعریف شده اما هنوز روی دیتابیس اجرا نشده است.', 'href' => '#admin-notifications'],
            ['id' => 'incidents', 'title' => 'مدیریت رخدادها', 'status' => 'Schema در branch / Engine مفقود', 'detail' => 'جدول admin_incidents برای fingerprint، severity و lifecycle تعریف شده؛ جمع‌آوری خطا، deduplication و API هنوز پیاده‌سازی نشده‌اند.', 'href' => '#incidents'],
            ['id' => 'system-health', 'title' => 'سلامت سیستم', 'status' => 'نیازمند پیاده‌سازی', 'detail' => 'باید از probeهای واقعی PHP، دیتابیس و سرویس‌ها تغذیه شود؛ جدول incident به‌تنهایی health check نیست.', 'href' => '#system-health'],
            ['id' => 'product-analytics', 'title' => 'تحلیل رفتار محصول', 'status' => 'Schema در branch / Collector مفقود', 'detail' => 'جدول product_analytics_events در migration جدید تعریف شده اما هنوز اجرا نشده؛ collector رویدادها و گزارش تحلیلی وجود ندارد.', 'href' => '#product-analytics'],
            ['id' => 'global-search', 'title' => 'جست‌وجوی سراسری', 'status' => 'نیازمند پیاده‌سازی', 'detail' => 'جست‌وجوی یکپارچه محصول، سفارش، مشتری و تیکت؛ فقط موجودیت‌های واقعی باید جست‌وجو شوند.', 'href' => '#global-search'],
        ],
    ],
    [
        'title' => 'مشتریان و پشتیبانی',
        'description' => 'رسیدگی به درخواست‌های مشتری و ارتباطات داخل پنل.',
        'items' => [
            ['id' => 'support-center', 'title' => 'مرکز پشتیبانی و تیکت', 'status' => 'Schema در branch / API و UI مفقود', 'detail' => 'مدل Ticket و Message و Assignment و Priority در migration جدید تعریف شده اما هنوز اجرا نشده؛ چرخه API و صفحه پشتیبانی ساخته نشده است.', 'href' => '#support-center'],
            ['id' => 'questions', 'title' => 'پرسش‌های محصول', 'status' => 'موجود', 'detail' => 'از ماژول فعلی پرسش و پاسخ استفاده می‌شود.', 'href' => url('/admin/questions')],
            ['id' => 'reviews', 'title' => 'دیدگاه‌ها', 'status' => 'موجود', 'detail' => 'ماژول فعلی مدیریت دیدگاه و رسانه حفظ می‌شود.', 'href' => url('/admin/reviews')],
        ],
    ],
    [
        'title' => 'عملیات محتوا',
        'description' => 'مدیریت ویترین و چرخه انتشار محتوا.',
        'items' => [
            ['id' => 'storefront-content', 'title' => 'ویترین و محتوای فروشگاه', 'status' => 'موجود / محدود', 'detail' => 'اسلایدر، اسلاید، مجموعه و دیسکاوری موجودند؛ CMS عمومی تمام صفحات و سکشن‌ها وجود ندارد.', 'href' => url('/admin/sliders')],
            ['id' => 'media-library', 'title' => 'کتابخانه رسانه', 'status' => 'نیازمند تکمیل', 'detail' => 'رسانه محصول و اسلاید در مدل‌های مستقل ذخیره می‌شود؛ Media Library یکپارچه وجود ندارد.', 'href' => '#media-library'],
            ['id' => 'revisions', 'title' => 'تاریخچه نسخه محتوا', 'status' => 'Schema در branch / Versioning مفقود', 'detail' => 'جدول admin_content_revisions برای snapshot نسخه‌ها تعریف شده اما هنوز اجرا نشده؛ ثبت revision و restore API وجود ندارد.', 'href' => '#revisions'],
            ['id' => 'scheduled-publishing', 'title' => 'انتشار زمان‌بندی‌شده', 'status' => 'نیازمند تکمیل', 'detail' => 'starts_at و ends_at در برخی مدل‌ها وجود دارند؛ scheduler عمومی و lifecycle انتشار تأیید نشده است.', 'href' => '#scheduled-publishing'],
        ],
    ],
    [
        'title' => 'بهره‌وری مدیر',
        'description' => 'ابزارهای سریع و تجربه کار با داده‌ها.',
        'items' => [
            ['id' => 'command-palette', 'title' => 'Command Palette', 'status' => 'نیازمند پیاده‌سازی', 'detail' => 'دسترسی سریع به مسیرها و عملیات مجاز، با رعایت Permission واقعی.', 'href' => '#command-palette'],
            ['id' => 'saved-views', 'title' => 'فیلترهای ذخیره‌شده', 'status' => 'Schema در branch / API مفقود', 'detail' => 'admin_saved_views در migration جدید تعریف شده اما هنوز اجرا نشده؛ API ذخیره و بازیابی فیلترها وجود ندارد.', 'href' => '#saved-views'],
            ['id' => 'bulk-actions', 'title' => 'عملیات گروهی', 'status' => 'Frontend محدود', 'detail' => 'انتخاب ردیف و CSV وجود دارد؛ mutation گروهی سمت سرور و audit آن وجود ندارد.', 'href' => '#bulk-actions'],
            ['id' => 'admin-preferences', 'title' => 'ترجیحات مدیر', 'status' => 'Browser-only', 'detail' => 'تراکم و اندازه متن در مرورگر ذخیره می‌شوند؛ تنظیمات متصل به حساب مدیر وجود ندارد.', 'href' => url('/admin/settings')],
        ],
    ],
];

?><!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نقشه قابلیت‌های پنل مدیریت | نیواد</title>
</head>
<body class="admin-operations-page">
<main class="admin-operations-main">
    <header class="admin-operations-heading">
        <p class="admin-operations-eyebrow">ADMIN PRODUCT MAP</p>
        <h1>نقشه قابلیت‌های پنل مدیریت</h1>
        <p>این صفحه محل هر قابلیت را برای ممیزی و طراحی مشخص می‌کند. وضعیت‌ها بر اساس کد فعلی و Schema خروجی دیتابیس هستند؛ موارد فاقد Backend یا داده واقعی به‌عنوان قابلیت فعال نمایش داده نمی‌شوند.</p>
    </header>

    <div class="admin-operations-notice" role="note">
        <strong>محدوده این نسخه</strong>
        <span>ارسال ایمیل و Gmail عمداً خارج از محدوده است. این نقشه، جایگزین پیاده‌سازی و تست عملیاتی هر ماژول نیست.</span>
    </div>

    <?php foreach ($sections as $section): ?>
        <section class="admin-operations-section" aria-label="<?= e($section['title']) ?>">
            <header class="admin-operations-section-heading">
                <h2><?= e($section['title']) ?></h2>
                <p><?= e($section['description']) ?></p>
            </header>
            <div class="admin-operations-grid">
                <?php foreach ($section['items'] as $item): ?>
                    <article class="admin-operations-card" id="<?= e($item['id']) ?>">
                        <div class="admin-operations-card-heading">
                            <h3><?= e($item['title']) ?></h3>
                            <span class="admin-operations-status"><?= e($item['status']) ?></span>
                        </div>
                        <p><?= e($item['detail']) ?></p>
                        <a href="<?= e($item['href']) ?>" <?= str_starts_with($item['href'], '#') ? 'aria-disabled="true"' : '' ?>>
                            <?= str_starts_with($item['href'], '#') ? 'جایگاه طراحی' : 'ورود به بخش موجود' ?>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <footer class="admin-operations-footer">
        <strong>قانون وضعیت:</strong>
        موجود بودن جدول یا کلاس به‌تنهایی به معنی تکمیل قابلیت نیست. هر ماژول باید UI، API، Permission، Schema و تست متناسب خودش را داشته باشد.
    </footer>
</main>
</body>
</html>
