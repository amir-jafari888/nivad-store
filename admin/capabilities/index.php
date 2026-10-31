<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

$capabilityPagePermissions = [
    'admin.capabilities.notifications.index',
    'admin.capabilities.incidents.index',
    'admin.capabilities.tickets.index',
    'admin.capabilities.analytics.index',
    'admin.capabilities.health.index',
    'admin.capabilities.preferences.index',
    'admin.capabilities.saved-views.index',
    'admin.capabilities.revisions.index',
];

if (!$adminAuth->canAny($capabilityPagePermissions)) {
    Response::redirect(url('/admin/access-denied.php'));
}

?><!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title>مرکز عملیات ادمین | نیواد</title>
</head>
<body class="admin-capabilities-page">
<main class="admin-capabilities-main"
      data-admin-capabilities
      data-api-url="<?= e(url('/api/index.php')) ?>">
    <header class="admin-capabilities-header">
        <div>
            <p class="admin-capabilities-eyebrow">NIVAD / ADMIN OPERATIONS</p>
            <h1>مرکز عملیات ادمین</h1>
            <p>مدیریت اعلان‌ها، رخدادها، تیکت‌ها، تحلیل محصولات و تنظیمات شخصی مدیر.</p>
        </div>
        <a href="<?= e(url('/admin/operations')) ?>">بازگشت به نقشه قابلیت‌ها</a>
        <a href="<?= e(url('/admin/incidents/index.php')) ?>">مدیریت رخدادها</a>
    </header>

    <p class="admin-capabilities-feedback" data-capability-feedback role="status" aria-live="polite">در حال آماده‌سازی مرکز عملیات…</p>

    <section class="admin-capability-panel" aria-labelledby="admin-notifications-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-notifications-title">مرکز اعلان مدیران</h2><p>اعلان‌های اختصاصی مدیر و اعلان‌های عمومی پنل.</p></div>
            <button type="button" data-capability-refresh="notifications">به‌روزرسانی</button>
        </div>
        <form data-capability-form="notification" class="admin-capability-form">
            <label>عنوان <input name="title" maxlength="255" required></label>
            <label>دسته‌بندی
                <select name="category">
                    <option value="system">سیستم</option><option value="security">امنیت</option>
                    <option value="orders">سفارش‌ها</option><option value="inventory">انبار</option>
                    <option value="customer_support">پشتیبانی</option><option value="content">محتوا</option><option value="errors">خطاها</option>
                </select>
            </label>
            <label>شدت
                <select name="severity"><option value="info">اطلاع‌رسانی</option><option value="warning">هشدار</option><option value="critical">بحرانی</option></select>
            </label>
            <label class="admin-capability-form-wide">متن اعلان <textarea name="message" rows="2" required></textarea></label>
            <button type="submit">ثبت اعلان برای حساب مدیر فعلی</button>
        </form>
        <div class="admin-capability-list" data-capability-list="notifications"></div>
    </section>

    <section class="admin-capability-panel" aria-labelledby="admin-incidents-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-incidents-title">رخدادهای سیستم</h2><p>مشاهده و تغییر وضعیت رخدادهای ثبت‌شده.</p></div>
            <button type="button" data-capability-refresh="incidents">به‌روزرسانی</button>
        </div>
        <div class="admin-capability-list" data-capability-list="incidents"></div>
    </section>

    <section class="admin-capability-panel" aria-labelledby="admin-tickets-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-tickets-title">مرکز تیکت پشتیبانی</h2><p>ثبت تیکت داخلی و مدیریت پیام‌ها و وضعیت آن.</p></div>
            <button type="button" data-capability-refresh="tickets">به‌روزرسانی</button>
        </div>
        <form data-capability-form="ticket" class="admin-capability-form">
            <label>شناسه مشتری (اختیاری) <input name="user_id" type="number" min="1"></label>
            <label>موضوع <input name="subject" maxlength="255" required></label>
            <label>اولویت <select name="priority"><option value="normal">عادی</option><option value="low">کم</option><option value="high">زیاد</option><option value="urgent">فوری</option></select></label>
            <label class="admin-capability-form-wide">پیام اولیه <textarea name="message" rows="2" required></textarea></label>
            <button type="submit">ایجاد تیکت</button>
        </form>
        <form data-support-filters class="admin-capability-form">
            <label>وضعیت
                <select name="status"><option value="">همه وضعیت‌ها</option><option value="open">باز</option><option value="pending_customer">منتظر مشتری</option><option value="pending_admin">منتظر مدیر</option><option value="resolved">حل‌شده</option><option value="closed">بسته</option></select>
            </label>
            <label>اولویت
                <select name="priority"><option value="">همه اولویت‌ها</option><option value="urgent">فوری</option><option value="high">زیاد</option><option value="normal">عادی</option><option value="low">کم</option></select>
            </label>
            <label>شناسه مسئول <input name="assigned_admin_id" type="number" min="1"></label>
            <label>شناسه مشتری <input name="user_id" type="number" min="1"></label>
            <label>از تاریخ <input name="date_from" type="date"></label>
            <label>تا تاریخ <input name="date_to" type="date"></label>
            <label class="admin-capability-form-wide">جستجو در شماره تیکت، موضوع یا شناسه <input name="search" maxlength="120"></label>
            <button type="submit">اعمال فیلترها</button>
            <button type="reset">پاک‌کردن فیلترها</button>
        </form>
        <div class="admin-capability-list" data-capability-list="tickets"></div>
        <section class="admin-capability-panel" aria-labelledby="admin-support-sla-title">
            <div class="admin-capability-panel-heading">
                <div><h3 id="admin-support-sla-title">سیاست‌های SLA</h3><p>زمان‌ها برحسب دقیقه تقویمی هستند. تا زمانی که برای اولویت‌ها مقدار ثبت نشود، وضعیت SLA محاسبه‌شده نیست.</p></div>
                <button type="button" data-capability-refresh="support-sla">بارگذاری سیاست‌ها</button>
            </div>
            <form data-capability-form="support-sla" class="admin-capability-form">
                <label>اولویت <select name="priority"><option value="urgent">فوری</option><option value="high">زیاد</option><option value="normal">عادی</option><option value="low">کم</option></select></label>
                <label>حداکثر زمان پاسخ اولیه (دقیقه) <input name="first_response_minutes" type="number" min="1" max="525600" required></label>
                <label>حداکثر زمان حل (دقیقه) <input name="resolution_minutes" type="number" min="1" max="525600" required></label>
                <button type="submit">ذخیره SLA</button>
            </form>
            <div class="admin-capability-list" data-capability-list="support-sla"></div>
        </section>
        <section class="admin-capability-panel" aria-labelledby="admin-support-metrics-title">
            <div class="admin-capability-panel-heading">
                <div><h3 id="admin-support-metrics-title">شاخص‌های پاسخ‌گویی</h3><p>میانگین پاسخ اولیه فقط از پاسخ عمومی مدیر به تیکت مشتری محاسبه می‌شود؛ یادداشت داخلی پاسخ محسوب نمی‌شود.</p></div>
                <button type="button" data-capability-refresh="support-metrics">به‌روزرسانی شاخص‌ها</button>
            </div>
            <form data-support-range class="admin-capability-form">
                <label>از تاریخ <input name="from" type="date" required></label>
                <label>تا تاریخ <input name="to" type="date" required></label>
                <button type="submit">اعمال بازه</button>
            </form>
            <div class="admin-capability-list" data-capability-list="support-metrics"></div>
        </section>
        <section class="admin-capability-panel" aria-labelledby="admin-support-reports-title">
            <div class="admin-capability-panel-heading">
                <div><h3 id="admin-support-reports-title">گزارش عملکرد پشتیبانی</h3><p>تعداد تیکت و میانگین زمان پاسخ اولیه بر اساس وضعیت و اولویت.</p></div>
                <button type="button" data-capability-refresh="support-reports">بارگذاری گزارش</button>
            </div>
            <div class="admin-capability-list" data-capability-list="support-reports"></div>
        </section>
    </section>

    <section class="admin-capability-panel" aria-labelledby="admin-analytics-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-analytics-title">تحلیل رفتار محصولات</h2><p>گزارش تجمیعی رویدادهای ثبت‌شده؛ در نبود داده، عدد ساختگی نمایش داده نمی‌شود.</p></div>
            <button type="button" data-capability-refresh="analytics">به‌روزرسانی</button>
        </div>
        <div class="admin-capability-list" data-capability-list="analytics"></div>
    </section>

    <section class="admin-capability-panel" aria-labelledby="admin-health-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-health-title">سلامت سیستم</h2><p>Probe فقط‌خواندنی برای PHP و اتصال دیتابیس؛ سرویس‌های خارجی در این گزارش بررسی نمی‌شوند.</p></div>
            <button type="button" data-capability-refresh="health">بررسی مجدد</button>
        </div>
        <div class="admin-capability-list" data-capability-list="health"></div>
    </section>

    <section class="admin-capability-panel" aria-labelledby="admin-preferences-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-preferences-title">تنظیمات شخصی مدیر</h2><p>تنظیمات در حساب مدیر ذخیره می‌شوند، نه فقط در مرورگر.</p></div>
            <button type="button" data-capability-refresh="preferences">بارگذاری</button>
        </div>
        <form data-capability-form="preference" class="admin-capability-form">
            <label>کلید تنظیم <input name="key" value="interface" maxlength="100" required></label>
            <label class="admin-capability-form-wide">مقدار JSON <textarea name="value" rows="3" required>{"density":"comfortable","fontScale":"normal","openFirstGroup":false}</textarea></label>
            <button type="submit">ذخیره در حساب مدیر</button>
        </form>
        <div class="admin-capability-list" data-capability-list="preferences"></div>
    </section>

    <section class="admin-capability-panel" aria-labelledby="admin-saved-views-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-saved-views-title">نماها و فیلترهای ذخیره‌شده</h2><p>فیلترهای دلخواه هر مدیر برای ماژول‌های پنل.</p></div>
            <button type="button" data-capability-refresh="saved-views">به‌روزرسانی</button>
        </div>
        <form data-capability-form="saved-view" class="admin-capability-form">
            <label>ماژول <input name="module" value="orders" maxlength="80" required></label>
            <label>نام نما <input name="name" maxlength="120" required></label>
            <label class="admin-capability-form-wide">فیلترها (JSON) <textarea name="filters" rows="2" required>{"status":"pending"}</textarea></label>
            <label><input type="checkbox" name="is_default" value="1"> نمای پیش‌فرض</label>
            <button type="submit">ذخیره نما</button>
        </form>
        <div class="admin-capability-list" data-capability-list="saved-views"></div>
    </section>

    <section class="admin-capability-panel" aria-labelledby="admin-revisions-title">
        <div class="admin-capability-panel-heading">
            <div><h2 id="admin-revisions-title">تاریخچه نسخه محتوا</h2><p>ثبت و مشاهده Snapshot؛ بازیابی خودکار هنوز به Adapter اختصاصی هر موجودیت نیاز دارد.</p></div>
        </div>
        <form data-capability-form="revision" class="admin-capability-form">
            <label>نوع موجودیت <input name="entity_type" value="product" maxlength="100" required></label>
            <label>شناسه موجودیت <input name="entity_id" type="number" min="1" required></label>
            <label class="admin-capability-form-wide">Snapshot (JSON) <textarea name="snapshot" rows="3" required>{}</textarea></label>
            <label class="admin-capability-form-wide">خلاصه تغییر <input name="change_summary" maxlength="1000"></label>
            <button type="submit">ثبت نسخه</button>
        </form>
        <form data-capability-form="revision-search" class="admin-capability-form">
            <label>نوع موجودیت <input name="entity_type" value="product" required></label>
            <label>شناسه موجودیت <input name="entity_id" type="number" min="1" required></label>
            <button type="submit">مشاهده تاریخچه</button>
        </form>
        <div class="admin-capability-list" data-capability-list="revisions"></div>
    </section>
</main>
</body>
</html>
