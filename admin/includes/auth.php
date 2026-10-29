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

/*
 * Shared authenticated-admin shell.
 * Dashboard keeps its own shell; all other admin pages receive this RTL shell.
 */
ob_start(static function (string $html) use ($currentAdmin): string {
    if (stripos($html, '</head>') !== false) {
        $assets = '';

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
        stripos($html, 'admin-dashboard-page') !== false ||
        stripos($html, 'admin-shared-sidebar') !== false
    ) {
        return $html;
    }

    /*
     * Normalize legacy pages into the shared content region without changing
     * their controller/service flow. A page that already has <main> is left
     * structurally intact; a legacy form-only view gets a semantic main wrapper.
     */
    if (preg_match('/<main\\b/i', $html) !== 1) {
        $html = preg_replace_callback(
            '/(<body\\b[^>]*>)([\\s\\S]*?)(<\\/body>)/i',
            static function (array $matches): string {
                return $matches[1]
                    . '<main id="main-content" class="admin-page-content">'
                    . $matches[2]
                    . '</main>'
                    . $matches[3];
            },
            $html,
            1
        ) ?? $html;
    }

    $mainId = 'main-content';
    $mainSeen = false;
    $html = preg_replace_callback(
        '/<main\\b([^>]*)>/i',
        static function (array $matches) use (&$mainId, &$mainSeen): string {
            if ($mainSeen) {
                return $matches[0];
            }
            $mainSeen = true;
            $attributes = $matches[1];
            if (preg_match('/\\bid\\s*=\\s*(["\\\'])(.*?)\\1/i', $attributes, $idMatch) === 1) {
                $mainId = $idMatch[2];
                return $matches[0];
            }
            $mainId = 'main-content';
            return '<main id="main-content"' . $attributes . '>';
        },
        $html,
        1
    ) ?? $html;

    if (stripos($html, 'admin-skip-link') === false) {
        $skipLink = '<a class="admin-skip-link" href="#' . e($mainId) . '">رفتن به محتوای اصلی</a>';
        $html = preg_replace('/(<body\\b[^>]*>)/i', '$1' . $skipLink, $html, 1) ?? $html;
    }

    $firstName = trim((string)($currentAdmin['first_name'] ?? ''));
    $lastName = trim((string)($currentAdmin['last_name'] ?? ''));
    $adminName = trim($firstName . ' ' . $lastName);
    if ($adminName === '') {
        $adminName = 'مدیر سیستم';
    }
    $adminInitial = mb_substr($firstName !== '' ? $firstName : $adminName, 0, 1, 'UTF-8')
        . ($lastName !== '' ? mb_substr($lastName, 0, 1, 'UTF-8') : '');

    $topbar = '
    <header class="admin-shared-topbar">
        <button class="admin-shared-mobile-menu" type="button" data-admin-menu-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="باز کردن منوی مدیریت"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <a class="admin-shared-topbar-brand" href="' . e(url('/admin')) . '">نیواد <span>پنل مدیریت</span></a>
        <div class="admin-shared-topbar-profile" aria-label="حساب مدیر واردشده: ' . e($adminName) . '">
            <span class="admin-shared-avatar" aria-hidden="true">' . e($adminInitial) . '</span>
            <span class="admin-shared-account-name"><span class="admin-shared-first-name">' . e($firstName !== '' ? $firstName : 'مدیر') . '</span><span class="admin-shared-last-name">' . e($lastName) . '</span></span>
        </div>
    </header>';

    $nav = '
    <aside class="admin-shared-sidebar" id="admin-sidebar" data-admin-sidebar aria-label="ناوبری پنل مدیریت">
        <a class="admin-shared-brand" href="' . e(url('/admin')) . '">
            <span class="admin-shared-brand-mark" aria-hidden="true">N</span>
            <span>نیواد <small>مدیریت فروشگاه</small></span>
        </a>
        <nav class="admin-shared-nav">
            <a class="admin-shared-nav-link" href="' . e(url('/admin')) . '">داشبورد</a>
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
                    <a href="' . e(url('/admin/inventory')) . '">موجودی</a>
                    <a href="' . e(url('/admin/inventory/movements.php')) . '">گردش موجودی</a>
                    <a href="' . e(url('/admin/inventory/reservations.php')) . '">رزروهای موجودی</a>
                    <a href="' . e(url('/admin/shipping/shipments')) . '">مرسوله‌ها</a>
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
                    <a href="' . e(url('/admin/reports')) . '">نمای کلی گزارش‌ها</a>
                    <a href="' . e(url('/admin/reports/sales')) . '">گزارش فروش</a>
                    <a href="' . e(url('/admin/reports/orders')) . '">گزارش سفارش‌ها</a>
                    <a href="' . e(url('/admin/reports/products')) . '">گزارش محصولات</a>
                    <a href="' . e(url('/admin/reports/categories')) . '">گزارش دسته‌بندی‌ها</a>
                    <a href="' . e(url('/admin/audit-logs')) . '">گزارش رویدادها</a>
                </div>
            </details>
            <a class="admin-shared-nav-link" href="' . e(url('/admin/settings')) . '">تنظیمات رابط کاربری</a>
        </nav>
        <div class="admin-shared-account">
            <span class="admin-shared-avatar" aria-hidden="true">' . e($adminInitial) . '</span>
            <span class="admin-shared-account-name">' . e($adminName) . '</span>
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
        static function (array $matches) use ($nav, $topbar): string {
            return $matches[0] . $topbar . $nav;
        },
        $html,
        1
    ) ?? $html;
});
