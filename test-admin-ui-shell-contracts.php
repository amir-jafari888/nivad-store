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
$metricsService = $read('app/Services/AdminDashboardMetricsService.php');
$overviewService = $read('app/Services/AdminDashboardOverviewService.php');
$healthService = $read('app/Services/AdminSystemHealthService.php');

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
$check('Shared shell emits the real mobile toggle and sidebar hooks', str_contains($sharedAuth, 'data-admin-menu-toggle') && str_contains($sharedAuth, 'data-admin-sidebar') && str_contains($sharedAuth, 'aria-controls="admin-shared-sidebar"'));
$check('Responsive navigation supports toggle, Escape, backdrop and inert content', str_contains($navigationJs, 'data-admin-menu-toggle') && str_contains($navigationJs, 'event.key === "Escape"') && str_contains($navigationJs, 'event.key !== "Tab"') && str_contains($navigationJs, 'admin-nav-close') && str_contains($navigationJs, 'sidebar.inert') && str_contains($navigationJs, 'admin-nav-backdrop'));
$check('Responsive navigation module is wired through main.js', str_contains($mainJs, 'navigation-state.js') && str_contains($mainJs, 'adminNavigationState()'));
$check('Legacy form-only pages receive a semantic main wrapper and skip link', str_contains($sharedAuth, 'admin-page-content') && str_contains($sharedAuth, 'admin-skip-link') && str_contains($sharedAuth, 'main-content'));
$check('Shared shell profile exposes first and last name separately', str_contains($sharedAuth, 'admin-shared-first-name') && str_contains($sharedAuth, 'admin-shared-last-name'));
$check('Dashboard includes both an accessible SVG trend chart and a compact bar chart', str_contains($dashboard, 'admin-dashboard-line-chart') && str_contains($dashboard, '<polyline') && str_contains($dashboard, 'admin-dashboard-chart-bar'));
$check('Dashboard profile exposes first and last name separately', str_contains($dashboard, '<span><?= e($firstName !==') && str_contains($dashboard, '<span><?= e($lastName) ?></span>'));
$check('Dashboard charts use real report rows and the current 14-day query', str_contains($dashboard, '$salesByPeriod') && str_contains($metricsService, 'salesRevenueByPeriod($currentQuery)') && str_contains($dashboard, '۱۴ روز اخیر'));
$check('Dashboard separates all-time KPI data from current and previous 14-day comparisons', str_contains($metricsService, 'period_comparison') && str_contains($metricsService, "modify('-13 days')") && str_contains($metricsService, "modify('-27 days')") && str_contains($metricsService, "unset($allTimeData['from_date'], $allTimeData['to_date'])"));
$check('Dashboard view displays period comparisons and audit event references', str_contains($dashboard, 'admin-dashboard-period-comparison') && str_contains($dashboard, 'شناسه رویداد: #'));
$check('Action queue sorts items by explicit priority', str_contains($overviewService, "'critical' => 0") && str_contains($overviewService, 'usort($queueItems'));
$check('System health checks required PHP extensions and database connectivity', str_contains($healthService, "['pdo', 'pdo_mysql', 'json', 'mbstring']") && str_contains($healthService, "query('SELECT 1')"));
$check('Responsive CSS imports preserve the established 600/601–1009/1010 breakpoints', str_contains($mainCss, 'max-width: 600px') && str_contains($mainCss, 'min-width: 601px') && str_contains($mainCss, 'max-width: 1009px') && str_contains($mainCss, 'min-width: 1010px'));
$check('No database or destructive operation is introduced by this UI contract test', !preg_match('/\\b(INSERT|UPDATE|DELETE|DROP|ALTER)\\b/i', $dashboard));

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
exit($failed === 0 ? 0 : 1);
