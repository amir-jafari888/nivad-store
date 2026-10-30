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
            ['id' => 'action-queue', 'title' => 'صف اقدامات', 'status' => 'API و Command Palette اضافه شده / تست داده واقعی باقی', 'detail' => 'برای کارهای منتظر بررسی، تأیید یا اقدام مدیر؛ منبع داده فعلی تأیید نشده است.', 'href' => '#action-queue'],
        ],
    ],
    [
        'title' => 'مدیریت و امنیت',
        'description' => 'هویت مدیران، نقش‌ها، مجوزها و نشست‌های مدیریتی.',
        'items' => [
            ['id' => 'admin-management', 'title' => 'مدیریت مدیران', 'status' => 'CRUD پایه و soft delete اضافه شده / تست اجرایی باقی', 'detail' => 'فهرست، ایجاد، ویرایش وضعیت، تخصیص نقش، غیرفعال‌سازی حساب و لغو نشست‌های آن پیاده‌سازی شده؛ تست دیتابیس واقعی باقی است.', 'href' => url('/admin/access/index.php')],
            ['id' => 'roles-permissions', 'title' => 'نقش‌ها و مجوزها', 'status' => 'مدیریت پایه پیاده‌سازی شده / تست اجرایی باقی', 'detail' => 'فهرست نقش و مجوز، ایجاد نقش و تخصیص مجوز اضافه شده؛ Super Admin محافظت شده و تست عملیاتی باقی است.', 'href' => url('/admin/access/index.php')],
            ['id' => 'session-monitoring', 'title' => 'پایش نشست مدیران', 'status' => 'Backend و UI اضافه شده / تست اجرایی باقی', 'detail' => 'فهرست نشست‌های دیگر و لغو نشست انتخابی اضافه شده؛ نشست جاری از فهرست و عملیات لغو مستثناست.', 'href' => url('/admin/access/index.php')],
            ['id' => 'audit-logs', 'title' => 'گزارش رویدادها', 'status' => 'ثبت رویدادهای جدید اضافه شده / پوشش کلی ناقص', 'detail' => 'مدیریت مدیران، نقش‌ها، نشست‌ها و قابلیت‌های جدید ثبت می‌شوند؛ CRUDهای قدیمی محصولات، سفارش‌ها، موجودی و CMS هنوز باید پوشش داده شوند.', 'href' => url('/admin/audit-logs')],
            ['id' => 'access-denied', 'title' => 'صفحه عدم دسترسی', 'status' => 'پاسخ 403 سمت API موجود / صفحه اختصاصی پنل باقی', 'detail' => 'کنترل دسترسی سمت سرور وجود دارد؛ تجربه اختصاصی 403 برای پنل باید طراحی و متصل شود.', 'href' => '#access-denied'],
        ],
    ],
    [
        'title' => 'پایش و تحلیل',
        'description' => 'اعلان‌های مدیریتی، رخدادها، سلامت سیستم و رفتار مشتری.',
        'items' => [
            ['id' => 'admin-notifications', 'title' => 'مرکز اعلان مدیران', 'status' => 'API و UI پایه اضافه شده / تست دیتابیس واقعی باقی', 'detail' => 'اعلان شخصی مدیر، فهرست، خواندن و ایجاد دستی فعال شده؛ رخدادهای بحرانی نیز اعلان deduplicated برای مدیران فعال تولید می‌کنند. رویدادهای فروش هنوز متصل نیستند.', 'href' => url('/admin/capabilities/index.php#admin-notifications')],
            ['id' => 'incidents', 'title' => 'مدیریت رخدادها', 'status' => 'ثبت رخدادهای بحرانی پایه اضافه شده / پوشش ناقص', 'detail' => 'Unhandled Server Exception با deduplication ثبت می‌شود و وضعیت قابل تغییر است؛ Error Handlerهای مدیریت‌شده، notification خودکار و تست runtime باقی است.', 'href' => url('/admin/capabilities/index.php#admin-incidents-title')],
            ['id' => 'system-health', 'title' => 'سلامت سیستم', 'status' => 'Probe پایه PHP و دیتابیس اضافه شده / ناقص', 'detail' => 'وضعیت PHP، اتصال دیتابیس و timezone بررسی می‌شود؛ دیسک، صف‌ها، cron، درگاه پرداخت و سرویس‌های خارجی هنوز probe ندارند.', 'href' => '#system-health'],
            ['id' => 'product-analytics', 'title' => 'تحلیل رفتار محصول', 'status' => 'Collector و گزارش پایه اضافه شده / پوشش ناقص', 'detail' => 'بازدید، انتخاب مدل، افزودن به سبد و علاقه‌مندی در صفحه جزئیات ثبت می‌شود؛ سایر صفحات، checkout، تبدیل و funnel هنوز پوشش داده نشده‌اند.', 'href' => url('/admin/capabilities/index.php#admin-analytics-title')],
            ['id' => 'global-search', 'title' => 'جست‌وجوی سراسری', 'status' => 'API و UI اضافه شده / گسترش دامنه باقی', 'detail' => 'محصول، سفارش، مشتری و تیکت با Ctrl+K جست‌وجو می‌شوند؛ فیلتر مجوزهای موجودیت‌محور و سایر ماژول‌ها باید تکمیل شوند.', 'href' => url('/admin')],
        ],
    ],
    [
        'title' => 'مشتریان و پشتیبانی',
        'description' => 'رسیدگی به درخواست‌های مشتری و ارتباطات داخل پنل.',
        'items' => [
            ['id' => 'support-center', 'title' => 'مرکز پشتیبانی و تیکت', 'status' => 'Schema در دیتابیس طبق گزارش اجرا شده / API و UI در حال اتصال', 'detail' => 'ایجاد تیکت، پیام داخلی/پاسخ مدیر و تغییر وضعیت اضافه شده؛ اتصال به کانال ورودی مشتری و اعلان خودکار باقی است.', 'href' => url('/admin/capabilities/index.php#admin-tickets-title')],
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
            ['id' => 'revisions', 'title' => 'تاریخچه نسخه محتوا', 'status' => 'Snapshot دستی و خودکار محصول اضافه شده / ناقص', 'detail' => 'ثبت و مشاهده Snapshot عمومی و snapshot خودکار ایجاد/ویرایش محصول فعال شده؛ اتصال سایر CMSها و بازیابی امن نسخه هنوز باقی است.', 'href' => url('/admin/capabilities/index.php#admin-revisions-title')],
            ['id' => 'scheduled-publishing', 'title' => 'انتشار زمان‌بندی‌شده', 'status' => 'نیازمند تکمیل', 'detail' => 'starts_at و ends_at در برخی مدل‌ها وجود دارند؛ scheduler عمومی و lifecycle انتشار تأیید نشده است.', 'href' => '#scheduled-publishing'],
        ],
    ],
    [
        'title' => 'بهره‌وری مدیر',
        'description' => 'ابزارهای سریع و تجربه کار با داده‌ها.',
        'items' => [
            ['id' => 'command-palette', 'title' => 'Command Palette', 'status' => 'نسخه جست‌وجومحور اضافه شده / عملیات سریع باقی', 'detail' => 'میانبر Ctrl+K و جست‌وجوی موجودیت‌ها فعال شده؛ اجرای فرمان‌های مدیریتی و فهرست میانبرهای Permission-aware باقی است.', 'href' => url('/admin')],
            ['id' => 'saved-views', 'title' => 'فیلترهای ذخیره‌شده', 'status' => 'Schema در دیتابیس طبق گزارش اجرا شده / API اضافه شده', 'detail' => 'ذخیره، فهرست و حذف نماهای شخصی از طریق API و UI اضافه شده است.', 'href' => url('/admin/capabilities/index.php#admin-saved-views-title')],
            ['id' => 'bulk-actions', 'title' => 'عملیات گروهی', 'status' => 'Frontend محدود', 'detail' => 'انتخاب ردیف و CSV وجود دارد؛ mutation گروهی سمت سرور و audit آن وجود ندارد.', 'href' => '#bulk-actions'],
            ['id' => 'admin-preferences', 'title' => 'ترجیحات مدیر', 'status' => 'ذخیره دیتابیسی و همگام‌سازی اضافه شده', 'detail' => 'تراکم، اندازه متن و بازبودن گروه اول ناوبری در حساب مدیر ذخیره می‌شوند؛ سایر ترجیحات هنوز تعریف نشده‌اند.', 'href' => url('/admin/capabilities/index.php#admin-preferences-title')],
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
