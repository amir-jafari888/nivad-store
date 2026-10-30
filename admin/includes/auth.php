<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

$adminAuth = new AdminAuthService();

if (!$adminAuth->validateCurrentSession()) {
    Response::redirect(url('/admin/auth/login.php'));
}

$currentAdmin = $adminAuth->currentSafe();

if ($currentAdmin === null) {
    Response::redirect(url('/admin/auth/login.php'));
}

$canUseGlobalSearch = $adminAuth->can('admin.capabilities.search.index');
$canUseCapabilities = $adminAuth->canAny([
    'admin.capabilities.notifications.index',
    'admin.capabilities.incidents.index',
    'admin.capabilities.tickets.index',
    'admin.capabilities.analytics.index',
    'admin.capabilities.health.index',
    'admin.capabilities.preferences.index',
    'admin.capabilities.saved-views.index',
    'admin.capabilities.revisions.index',
]);
$canManageContent = $adminAuth->canAny(['admin.content.manage', 'admin.media.manage']);
$canManageAccess = $adminAuth->canAny([
    'admin.access.admins.index',
    'admin.access.roles.index',
    'admin.access.permissions.index',
    'admin.access.roles.permissions.show',
    'admin.access.sessions.index',
]);

/*
 * Shared authenticated-admin shell for dashboard and internal admin pages.
 */
ob_start(static function (string $html) use ($currentAdmin, $canUseGlobalSearch, $canUseCapabilities, $canManageAccess, $canManageContent): string {
    if (stripos($html, '</head>') !== false) {
        $assets = '';

        if (stripos($html, 'name="csrf-token"') === false) {
            $csrfToken = e(Csrf::token());
            $assets .= "\n<meta name=\"csrf-token\" content=\"{$csrfToken}\">";
        }

        if (stripos($html, 'name="admin-api-url"') === false) {
            $apiUrl = e(url('/api/index.php'));
            $assets .= "\n<meta name=\"admin-api-url\" content=\"{$apiUrl}\">";
        }

        if (stripos($html, '/admin/assets/css/main.css') === false) {
            $stylesheet = e(url('/admin/assets/css/main.css'));
            $assets .= "\n<link rel=\"stylesheet\" href=\"{$stylesheet}\">";
        }

        if (stripos($html, '/admin/assets/js/main.js') === false) {
            $script = e(url('/admin/assets/js/main.js'));
            $assets .= "\n<script type=\"module\" src=\"{$script}\"></script>";
        }

        if ($assets !== '') {
            $updated = preg_replace('/<\/head\s*>/i', $assets . "\n</head>", $html, 1);
            if (is_string($updated)) {
                $html = $updated;
            }
        }
    }

    if (
        stripos($html, '<body') === false ||
        stripos($html, 'admin-shared-sidebar') !== false
    ) {
        return $html;
    }

    $firstName = trim((string)($currentAdmin['first_name'] ?? ''));
    $lastName = trim((string)($currentAdmin['last_name'] ?? ''));
    $adminName = trim($firstName . ' ' . $lastName);
    if ($adminName === '') {
        $adminName = 'مدیر سیستم';
    }
    $adminInitial = mb_substr($firstName !== '' ? $firstName : $adminName, 0, 1, 'UTF-8');
    $firstNameDisplay = $firstName !== '' ? $firstName : ($lastName === '' ? 'مدیر سیستم' : '');
    $lastNameDisplay = $lastName;

    $skipLink = '';
    if (stripos($html, 'admin-skip-link') === false && preg_match('/<main\\b([^>]*)>/i', $html, $mainMatch) === 1) {
        $mainAttributes = $mainMatch[1];
        if (preg_match('/\\bid\\s*=\\s*"([^"]+)"/i', $mainAttributes, $idMatch) === 1 && $idMatch[1] !== '') {
            $mainTarget = $idMatch[1];
        } else {
            $mainTarget = 'main-content';
            if (preg_match('/\\bid\\s*=/i', $mainAttributes) !== 1) {
                $updatedHtml = preg_replace('/<main\\b/i', '<main id="main-content"', $html, 1);
                if (is_string($updatedHtml)) {
                    $html = $updatedHtml;
                }
            }
        }
        $skipLink = '<a class="admin-skip-link" href="#' . e($mainTarget) . '">رفتن به محتوای اصلی</a>';
    }

    $search = $canUseGlobalSearch ? '
        <div class="admin-global-search" data-admin-global-search>
            <label class="admin-global-search-label" for="admin-global-search-input">جست‌وجوی سراسری</label>
            <input id="admin-global-search-input" type="search" placeholder="جست‌وجوی محصول، سفارش، مشتری یا تیکت…" autocomplete="off" aria-autocomplete="list" aria-controls="admin-global-search-results">
            <ul id="admin-global-search-results" class="admin-global-search-results" role="list" hidden></ul>
            <span class="admin-global-search-shortcut" aria-hidden="true">Ctrl K</span>
        </div>' : '';

    $topbar = '
    <header class="admin-shared-topbar">
        <button type="button" class="admin-shared-mobile-menu" data-admin-menu-toggle aria-controls="admin-shared-sidebar" aria-expanded="false" aria-label="باز کردن منوی مدیریت">☰</button>
        <a class="admin-shared-topbar-brand" href="' . e(url('/admin')) . '">نیواد <span>پنل مدیریت</span></a>
        ' . $search . '
        <div class="admin-shared-topbar-profile" aria-label="حساب مدیر واردشده">
            <span class="admin-shared-avatar" aria-hidden="true">' . e($adminInitial) . '</span>
            <span class="admin-shared-account-name"><span class="admin-shared-first-name">' . e($firstNameDisplay) . '</span><span class="admin-shared-last-name">' . e($lastNameDisplay) . '</span></span>
        </div>
    </header>';

    $capabilityLink = $canUseCapabilities
        ? '<a class="admin-shared-nav-link" href="' . e(url('/admin/capabilities/index.php')) . '">مرکز عملیات ادمین</a>'
        : '';
    $contentLink = $canManageContent
        ? '<a class="admin-shared-nav-link" href="' . e(url('/admin/content/index.php')) . '">استودیوی مدیریت محتوا</a>'
        : '';
    $accessLink = $canManageAccess
        ? '<a class="admin-shared-nav-link" href="' . e(url('/admin/access/index.php')) . '">مدیریت مدیران و دسترسی‌ها</a>'
        : '';

    $nav = '
    <aside id="admin-shared-sidebar" class="admin-shared-sidebar" data-admin-sidebar aria-label="ناوبری پنل مدیریت">
        <a class="admin-shared-brand" href="' . e(url('/admin')) . '">
            <span class="admin-shared-brand-mark" aria-hidden="true">N</span>
            <span>نیواد <small>مدیریت فروشگاه</small></span>
        </a>
        <nav class="admin-shared-nav">
            <a class="admin-shared-nav-link" href="' . e(url('/admin')) . '">داشبورد</a>
            <a class="admin-shared-nav-link" href="' . e(url('/admin/profile/index.php')) . '">پروفایل مدیر</a>
            <details class="admin-shared-nav-group">
                <summary>کاتالوگ محصولات</summary>
                <div class="admin-shared-submenu">
                    <a href="' . e(url('/admin/products')) . '">محصولات</a>
                    <a href="' . e(url('/admin/categories')) . '">دسته‌بندی‌ها</a>
                    <a href="' . e(url('/admin/brands')) . '">برندها</a>
                    <a href="' . e(url('/admin/collections')) . '">مجموعه‌ها</a>
                    <a href="' . e(url('/admin/attributes')) . '">ویژگی‌ها</a>
                    <a href="' . e(url('/admin/product-types')) . '">نوع محصول</a>
                    <a href="' . e(url('/admin/specifications')) . '">مشخصات فنی</a>
                    <a href="' . e(url('/admin/specifications/groups')) . '">گروه‌های مشخصات</a>
                </div>
            </details>
            <details class="admin-shared-nav-group">
                <summary>ویترین و دیسکاوری</summary>
                <div class="admin-shared-submenu">
                    <a href="' . e(url('/admin/sliders')) . '">اسلایدرها</a>
                    <a href="' . e(url('/admin/discovery')) . '">دیسکاوری</a>
                    <a href="' . e(url('/admin/compatibility')) . '">سازگاری</a>
                    <a href="' . e(url('/admin/compatibility/devices')) . '">دستگاه‌های سازگاری</a>
                    <a href="' . e(url('/admin/setups')) . '">ستاپ‌ها</a>
                    <a href="' . e(url('/admin/setups/devices.php')) . '">دستگاه‌های Setup</a>
                    <a href="' . e(url('/admin/setups/accessories.php')) . '">اکسسوری‌های Setup</a>
                </div>
            </details>
            <details class="admin-shared-nav-group">
                <summary>فروش و انبار</summary>
                <div class="admin-shared-submenu">
                    <a href="' . e(url('/admin/orders')) . '">سفارش‌ها</a>
                    <a href="' . e(url('/admin/payments')) . '">پرداخت‌ها</a>
                    <a href="' . e(url('/admin/payments/attempts.php')) . '">تلاش‌های پرداخت</a>
                    <a href="' . e(url('/admin/coupons')) . '">کدهای تخفیف</a>
                    <a href="' . e(url('/admin/inventory')) . '">موجودی و گردش انبار</a>
                    <a href="' . e(url('/admin/inventory/movements.php')) . '">گردش موجودی</a>
                    <a href="' . e(url('/admin/inventory/reservations.php')) . '">رزروهای موجودی</a>
                    <a href="' . e(url('/admin/shipping/shipments')) . '">ارسال</a>
                    <a href="' . e(url('/admin/shipping/methods.php')) . '">روش‌های ارسال</a>
                    <a href="' . e(url('/admin/shipping/zones.php')) . '">مناطق ارسال</a>
                </div>
            </details>
            <details class="admin-shared-nav-group">
                <summary>مشتریان و پشتیبانی</summary>
                <div class="admin-shared-submenu">
                    <a href="' . e(url('/admin/users')) . '">کاربران</a>
                    <a href="' . e(url('/admin/questions')) . '">پرسش‌ها</a>
                    <a href="' . e(url('/admin/reviews')) . '">دیدگاه‌ها</a>
                    <a href="' . e(url('/admin/returns')) . '">مرجوعی‌ها</a>
                    <a href="' . e(url('/admin/refunds')) . '">بازپرداخت‌ها</a>
                    <a href="' . e(url('/admin/notifications')) . '">اعلان‌ها</a>
                </div>
            </details>
            <details class="admin-shared-nav-group">
                <summary>گزارش و ممیزی</summary>
                <div class="admin-shared-submenu">
                    <a href="' . e(url('/admin/reports')) . '">گزارش‌ها</a>
                    <a href="' . e(url('/admin/reports/sales')) . '">گزارش فروش</a>
                    <a href="' . e(url('/admin/reports/orders')) . '">گزارش سفارش‌ها</a>
                    <a href="' . e(url('/admin/reports/products')) . '">گزارش محصولات</a>
                    <a href="' . e(url('/admin/reports/categories')) . '">گزارش دسته‌بندی‌ها</a>
                    <a href="' . e(url('/admin/audit-logs')) . '">گزارش رویدادها</a>
                </div>
            </details>
            <a class="admin-shared-nav-link" href="' . e(url('/admin/operations')) . '">نقشه قابلیت‌های پنل</a>
            ' . $contentLink . '
            ' . $capabilityLink . '
            ' . $accessLink . '
            <a class="admin-shared-nav-link" href="' . e(url('/admin/settings')) . '">تنظیمات رابط کاربری</a>
        </nav>
        <div class="admin-shared-account">
            <span class="admin-shared-avatar" aria-hidden="true">' . e($adminInitial) . '</span>
            <span class="admin-shared-account-name"><span class="admin-shared-first-name">' . e($firstNameDisplay) . '</span><span class="admin-shared-last-name">' . e($lastNameDisplay) . '</span></span>
            <form method="post" action="' . e(url('/admin/logout')) . '">
                <input type="hidden" name="csrf_token" value="' . e(Csrf::token()) . '">
                <button type="submit" class="admin-shared-logout">خروج از پنل</button>
            </form>
        </div>
    </aside>';

    $html = preg_replace_callback(
        '/<body\b([^>]*)>/i',
        static function (array $matches): string {
            $attributes = $matches[1];
            if (preg_match('/\bclass\s*=\s*(["\'])(.*?)\1/i', $attributes, $classMatch) === 1) {
                $classes = trim($classMatch[2] . ' admin-shared-shell');
                $attributes = str_replace($classMatch[0], 'class="' . $classes . '"', $attributes);
            } else {
                $attributes .= ' class="admin-shared-shell"';
            }
            return '<body' . $attributes . '>';
        },
        $html,
        1
    ) ?? $html;

    return preg_replace_callback(
        '/<body\b[^>]*>/i',
        static function (array $matches) use ($nav, $topbar, $skipLink): string {
            return $matches[0] . $skipLink . $topbar . $nav;
        },
        $html,
        1
    ) ?? $html;
});
