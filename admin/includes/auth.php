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

    $firstName = trim((string)($currentAdmin['first_name'] ?? ''));
    $lastName = trim((string)($currentAdmin['last_name'] ?? ''));
    $adminName = trim($firstName . ' ' . $lastName);
    if ($adminName === '') {
        $adminName = 'مدیر سیستم';
    }
    $adminInitials = mb_substr($firstName !== '' ? $firstName : $adminName, 0, 1, 'UTF-8')
        . mb_substr($lastName, 0, 1, 'UTF-8');
    if ($adminInitials === '') {
        $adminInitials = 'N';
    }

    $topbar = '
    <header class="admin-shared-topbar">
        <a class="admin-shared-topbar-brand" href="' . e(url('/admin')) . '">نیواد <span>پنل مدیریت</span></a>
        <div class="admin-shared-topbar-profile" aria-label="حساب مدیر واردشده">
            <span class="admin-shared-avatar" aria-hidden="true">' . e($adminInitials) . '</span>
            <span class="admin-shared-profile-copy"><span class="admin-shared-account-name">' . e($adminName) . '</span><small>مدیر پنل</small></span>
        </div>
    </header>';

    $nav = '
    <aside class="admin-shared-sidebar" aria-label="ناوبری پنل مدیریت">
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
                </div>
            </details>
            <details class="admin-shared-nav-group">
                <summary>ویترین و دیسکاوری</summary>
                <div class="admin-shared-submenu">
                    <a href="' . e(url('/admin/sliders')) . '">اسلایدرها</a>
                    <a href="' . e(url('/admin/discovery')) . '">دیسکاوری</a>
                    <a href="' . e(url('/admin/compatibility')) . '">سازگاری</a>
                    <a href="' . e(url('/admin/setups')) . '">ستاپ‌ها</a>
                </div>
            </details>
            <details class="admin-shared-nav-group">
                <summary>فروش و انبار</summary>
                <div class="admin-shared-submenu">
                    <a href="' . e(url('/admin/orders')) . '">سفارش‌ها</a>
                    <a href="' . e(url('/admin/payments')) . '">پرداخت‌ها</a>
                    <a href="' . e(url('/admin/coupons')) . '">کدهای تخفیف</a>
                    <a href="' . e(url('/admin/inventory')) . '">موجودی و گردش انبار</a>
                    <a href="' . e(url('/admin/shipping/shipments')) . '">ارسال</a>
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
                    <a href="' . e(url('/admin/audit-logs')) . '">گزارش رویدادها</a>
                </div>
            </details>
            <a class="admin-shared-nav-link" href="' . e(url('/admin/settings')) . '">تنظیمات رابط کاربری</a>
        </nav>
        <div class="admin-shared-account">
            <span class="admin-shared-avatar" aria-hidden="true">' . e($adminInitials) . '</span>
            <span class="admin-shared-account-name">' . e($adminName) . '</span>
            <form method="post" action="' . e(url('/admin/logout')) . '">
                <input type="hidden" name="csrf_token" value="' . e(Csrf::token()) . '">
                <button type="submit" class="admin-shared-logout">خروج از پنل</button>
            </form>
        </div>
    </aside>';

    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $pathParts = array_values(array_filter(
        explode('/', trim(is_string($requestPath) ? $requestPath : '', '/')),
        static fn (string $part): bool => $part !== ''
    ));
    $adminIndex = array_search('admin', $pathParts, true);
    $adminIndex = is_int($adminIndex) ? $adminIndex : 0;
    $adminSection = strtolower((string)($pathParts[$adminIndex + 1] ?? 'dashboard'));
    $adminSubpage = strtolower((string)($pathParts[$adminIndex + 2] ?? 'index'));
    $adminSection = preg_replace('/[^a-z0-9-]/', '-', $adminSection) ?: 'dashboard';
    $adminSubpage = preg_replace('/\.php$/i', '', $adminSubpage);
    $adminSubpage = preg_replace('/[^a-z0-9-]/', '-', (string)$adminSubpage) ?: 'index';
    $adminPageClasses = 'admin-page admin-page-' . $adminSection . ' admin-page-' . $adminSection . '-' . $adminSubpage;

    $html = preg_replace_callback(
        '/<body\b([^>]*)>/i',
        static function (array $matches) use ($adminPageClasses): string {
            $attributes = $matches[1];
            if (preg_match('/\bclass\s*=\s*(["\'])(.*?)\1/i', $attributes, $classMatch) === 1) {
                $classes = trim($classMatch[2] . ' admin-shared-shell ' . $adminPageClasses);
                $attributes = str_replace($classMatch[0], 'class="' . $classes . '"', $attributes);
            } else {
                $attributes .= ' class="admin-shared-shell ' . $adminPageClasses . '"';
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
