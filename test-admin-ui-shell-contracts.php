<?php

declare(strict_types=1);

/**
 * Admin UI shell contract regression — read-only static test.
 * Does not boot the application, connect to the database, or mutate data.
 */

$root = __DIR__;
$passed = 0;
$failed = 0;

$read = static function (string $relativePath) use ($root): string {
    $path = $root . DIRECTORY_SEPARATOR
        . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }
    $content = file_get_contents($path);
    return is_string($content) ? $content : '';
};

$check = static function (string $label, bool $condition) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "[PASS] {$label}\n";
        return;
    }
    $failed++;
    echo "[FAIL] {$label}\n";
};

$tokens = $read('admin/assets/css/setting.css');
$storeTokens = $read('assets/css/setting.css');
$desktop = $read('admin/assets/css/desktop/shared-shell-desktop.css');
$tablet = $read('admin/assets/css/tablet/shared-shell-tablet.css');
$mobile = $read('admin/assets/css/mobile/shared-shell-mobile.css');
$mainCss = $read('admin/assets/css/main.css');
$mainJs = $read('admin/assets/js/main.js');
$navigationJs = $read('admin/assets/js/modules/navigation-state.js');
$sharedAuth = $read('admin/includes/auth.php');
$dashboard = $read('pages/admin/dashboard.php');

$check('Admin design token file exists', $tokens !== '');
$check('Admin palette stays aligned with storefront primary/background/surface tokens',
    preg_match('/--admin-color-primary:\s*(#[0-9a-f]{6})/i', $tokens, $adminPrimary) === 1
    && preg_match('/--color-primary:\s*(#[0-9a-f]{6})/i', $storeTokens, $storePrimary) === 1
    && strtolower($adminPrimary[1]) === strtolower($storePrimary[1])
    && preg_match('/--admin-color-bg:\s*(#[0-9a-f]{6})/i', $tokens, $adminBg) === 1
    && preg_match('/--color-background:\s*(#[0-9a-f]{6})/i', $storeTokens, $storeBg) === 1
    && strtolower($adminBg[1]) === strtolower($storeBg[1])
);
$check('Admin uses the existing Persian/Latin font assets', str_contains($tokens, 'font-family: "Vazirmatn"') && str_contains($tokens, 'font-family: "Inter"'));
$check('Desktop sidebar is anchored to the physical right in RTL', str_contains($desktop, 'inset-inline-start:0;inset-inline-end:auto'));
$check('Desktop content reserves space for the fixed sidebar', str_contains($desktop, 'margin-inline-start:264px') && str_contains($desktop, '.admin-shared-shell main{'));
$check('Tablet navigation uses an accessible right-side drawer', str_contains($tablet, 'transform:translateX(110%)') && str_contains($tablet, 'inset-inline-start:0'));
$check('Mobile navigation uses an accessible right-side drawer', str_contains($mobile, 'transform:translateX(110%)') && str_contains($mobile, '.admin-nav-open .admin-shared-sidebar'));
$check('Responsive navigation supports toggle, Escape, backdrop and inert content', str_contains($navigationJs, 'data-admin-menu-toggle') && str_contains($navigationJs, 'event.key === "Escape"') && str_contains($navigationJs, 'event.key !== "Tab"') && str_contains($navigationJs, 'admin-nav-close') && str_contains($navigationJs, 'sidebar.inert') && str_contains($navigationJs, 'admin-nav-backdrop'));
$check('Responsive navigation module is wired through main.js', str_contains($mainJs, 'navigation-state.js') && str_contains($mainJs, 'adminNavigationState()'));
$check('Legacy form-only pages receive a semantic main wrapper and skip link', str_contains($sharedAuth, 'admin-page-content') && str_contains($sharedAuth, 'admin-skip-link') && str_contains($sharedAuth, 'main-content'));
$check('Shared shell profile exposes first and last name separately', str_contains($sharedAuth, 'admin-shared-first-name') && str_contains($sharedAuth, 'admin-shared-last-name'));
$check('Dashboard includes both an accessible SVG trend chart and a compact bar chart', str_contains($dashboard, 'admin-dashboard-line-chart') && str_contains($dashboard, '<polyline') && str_contains($dashboard, 'admin-dashboard-chart-bar'));
$check('Dashboard profile exposes first and last name separately', str_contains($dashboard, '<span><?= e($firstName !==') && str_contains($dashboard, '<span><?= e($lastName) ?></span>'));
$check('Dashboard charts use static data marked as demonstration data', str_contains($dashboard, '$dashboardChartPeriods') && str_contains($dashboard, 'داده نمایشی · آماده اتصال') && str_contains($dashboard, 'واحد نمایشی'));
$check('Dashboard chart values are computed from the demo series, not hard-coded CSS heights', str_contains($dashboard, '$maxDemoNetSales = max(array_column($dashboardChartPeriods') && str_contains($dashboard, '$maxDemoOrders = max(array_column($dashboardChartPeriods'));
$check('Responsive CSS imports preserve the established 600/601–1009/1010 breakpoints', str_contains($mainCss, 'max-width: 600px') && str_contains($mainCss, 'min-width: 601px') && str_contains($mainCss, 'max-width: 1009px') && str_contains($mainCss, 'min-width: 1010px'));
$check('No database or destructive operation is introduced by this UI contract test', !preg_match('/\\b(INSERT|UPDATE|DELETE|DROP|ALTER)\\b/i', $dashboard));

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
exit($failed === 0 ? 0 : 1);
