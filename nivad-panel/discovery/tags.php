<?php

declare(strict_types=1);

/*
 * This view is intentionally a fragment included by admin/discovery/index.php.
 * It is not a standalone route and must not be served without the authenticated shell.
 */
if (!defined('NIVAD_ADMIN_DISCOVERY_FRAGMENT')) {
    http_response_code(404);
    exit;
}
?>

<div class="admin-discovery-tags" data-admin-discovery-tags-page>
    <div class="admin-discovery-tags-heading">
        <div>
            <h2>تگ‌های Discovery</h2>
            <p>تگ‌ها را در پیش‌نمایش بساز و ساختار رابط را بررسی کن؛ ذخیره‌سازی پایدار هنوز فعال نیست.</p>
        </div>
        <span class="admin-discovery-preview-badge">پیش‌نمایش محلی</span>
    </div>

    <div class="admin-discovery-tag-layout">
        <form class="admin-discovery-tag-form" method="post" data-discovery-tag-form>
            <div class="admin-discovery-field">
                <label for="discovery-tag-name">نام تگ</label>
                <input id="discovery-tag-name" type="text" name="name" autocomplete="off" placeholder="مثلاً لوازم شارژ" required maxlength="100">
            </div>
            <div class="admin-discovery-field">
                <label for="discovery-tag-slug">Slug</label>
                <input id="discovery-tag-slug" type="text" name="slug" autocomplete="off" dir="ltr" placeholder="charging-accessories" required maxlength="120" pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                <small>فقط حروف انگلیسی کوچک، عدد و خط تیره.</small>
            </div>
            <div class="admin-discovery-field admin-discovery-field-wide">
                <label for="discovery-tag-description">توضیحات <span>اختیاری</span></label>
                <textarea id="discovery-tag-description" name="description" rows="3" maxlength="500" placeholder="این تگ چه محصولاتی را گروه‌بندی می‌کند؟"></textarea>
            </div>
            <div class="admin-discovery-field">
                <label for="discovery-tag-status">وضعیت پیش‌نمایش</label>
                <select id="discovery-tag-status" name="status">
                    <option value="active">فعال</option>
                    <option value="inactive">غیرفعال</option>
                </select>
            </div>
            <div class="admin-discovery-field">
                <label for="discovery-tag-order">ترتیب نمایش</label>
                <input id="discovery-tag-order" type="number" name="sortOrder" value="0" min="0" max="9999">
            </div>
            <div class="admin-discovery-form-actions">
                <button class="admin-discovery-primary-action" type="submit">افزودن به پیش‌نمایش</button>
                <button class="admin-discovery-secondary-action" type="reset">پاک‌کردن فرم</button>
            </div>
        </form>

        <section class="admin-discovery-tag-list-card" aria-labelledby="discovery-tag-list-title">
            <div class="admin-discovery-tag-list-heading">
                <h3 id="discovery-tag-list-title">تگ‌های این پیش‌نمایش</h3>
                <span data-discovery-count-label>۰ مورد</span>
            </div>
            <div class="admin-discovery-tag-list" data-discovery-tag-list aria-live="polite">
                <div class="admin-discovery-list-empty" data-discovery-empty>
                    <strong>هنوز تگی اضافه نشده</strong>
                    <span>با فرم کنار صفحه یک تگ نمونه بساز تا نمایش فهرست را بررسی کنی.</span>
                </div>
            </div>
            <p class="admin-discovery-list-footnote">مواردی که اینجا اضافه می‌شوند با خروج یا بارگذاری مجدد صفحه از بین می‌روند.</p>
        </section>
    </div>
    <p class="admin-discovery-form-feedback" data-discovery-feedback role="status" aria-live="polite" hidden></p>
</div>
