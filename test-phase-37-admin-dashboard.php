<?php

declare(strict_types=1);

/**
 * Nivad Phase 37 — Admin Dashboard static contract regression.
 * Read-only: this test does not write to the database.
 * Run from the project root with PHP/XAMPP.
 */

$root = __DIR__;
$checks = 0;
$passed = 0;
$failed = 0;

function phase37Check(bool $condition, string $label): void
{
    global $checks, $passed, $failed;
    $checks++;
    if ($condition) {
        $passed++;
        echo '<span style="color:green">[PASS] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><br>' . PHP_EOL;
        return;
    }
    $failed++;
    echo '<span style="color:red">[FAIL] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><br>' . PHP_EOL;
}

function phase37Read(string $relativePath): string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    return is_file($path) ? (string) file_get_contents($path) : '';
}

$view = phase37Read('pages/admin/dashboard.php');
$service = phase37Read('app/Services/AdminDashboardMetricsService.php');
$reportService = phase37Read('app/Services/ReportService.php');
$reportRepository = phase37Read('app/Repositories/ReportRepository.php');
$query = phase37Read('app/Models/ReportQuery.php');
$contract = phase37Read('app/Models/ReportContract.php');
$mainCss = phase37Read('admin/assets/css/main.css');
$mainJs = phase37Read('admin/assets/js/main.js');
$tokens = phase37Read('admin/assets/css/setting.css');

$desktopLayout = phase37Read('admin/assets/css/desktop/layout-desktop.css');
$desktopComponents = phase37Read('admin/assets/css/desktop/components-desktop.css');
$tabletLayout = phase37Read('admin/assets/css/tablet/layout-tablet.css');
$tabletComponents = phase37Read('admin/assets/css/tablet/components-tablet.css');
$mobileLayout = phase37Read('admin/assets/css/mobile/layout-mobile.css');
$mobileComponents = phase37Read('admin/assets/css/mobile/components-mobile.css');

/* Batch 01 — audit and existing data contracts: 10 checks. */
phase37Check(str_contains($view, 'url(\'/admin\')') && str_contains($view, 'url(\'/admin/logout\')'), '37.01 Dashboard navigation and logout routes are preserved');
phase37Check(str_contains($view, 'name="csrf_token"') && str_contains($view, 'Csrf::token()'), '37.02 Logout form keeps CSRF contract');
phase37Check(str_contains($service, "'orders'") && str_contains($service, "'payments'"), '37.03 Existing Metrics service keys remain intact');
phase37Check(str_contains($service, "'order_items'") && str_contains($service, "'inventory'"), '37.04 Existing order-item and inventory keys remain intact');
phase37Check(str_contains($reportRepository, 'AS total_orders') && str_contains($reportRepository, 'AS total_amount'), '37.05 Existing order summary fields are present');
phase37Check(str_contains($reportRepository, 'AS total_payments') && str_contains($reportRepository, 'AS average_amount'), '37.06 Existing payment summary fields are present');
phase37Check(str_contains($reportRepository, 'AS total_lines') && str_contains($reportRepository, 'AS total_quantity'), '37.07 Existing order-item summary fields are present');
phase37Check(str_contains($reportRepository, 'AS total_available_quantity') && str_contains($reportRepository, 'AS total_reserved_quantity'), '37.08 Existing inventory summary fields are present');
phase37Check(str_contains($reportService, 'salesRevenueByPeriod') && str_contains($reportRepository, 'AS net_sales'), '37.09 Existing sales trend contract is documented in code');
phase37Check(str_contains($query, 'endDateTimeExclusive') && str_contains($contract, 'GROUP_DAY'), '37.10 Date range and grouping contracts remain available');

/* Batch 02 — layout and KPI implementation: 10 checks. */
phase37Check(str_contains($view, 'admin-dashboard-shell') && str_contains($view, 'admin-dashboard-sidebar'), '37.11 Dashboard shell and sidebar are implemented');
phase37Check(str_contains($view, 'admin-dashboard-page-heading') && str_contains($view, '<h1>داشبورد مدیریت</h1>'), '37.12 Semantic dashboard heading is present');
phase37Check(str_contains($view, 'aria-current="page"') && str_contains($view, 'ناوبری مدیریت'), '37.13 Active navigation and accessible nav label are present');
phase37Check(str_contains($view, 'admin-dashboard-kpi-orders') && str_contains($view, 'admin-dashboard-kpi-payments'), '37.14 Order and payment KPI cards are present');
phase37Check(str_contains($view, 'admin-dashboard-kpi-items') && str_contains($view, 'admin-dashboard-kpi-inventory'), '37.15 Order-item and inventory KPI cards are present');
phase37Check(str_contains($view, '$formatNumber') && str_contains($view, 'number_format'), '37.16 Numeric values use a shared formatting helper');
phase37Check(str_contains($view, 'sales_by_period_status') && str_contains($view, 'admin-dashboard-state'), '37.17 Dashboard defines explicit trend state handling');
phase37Check(str_contains($view, 'هنوز داده') || str_contains($view, 'داده‌ای وجود ندارد') || str_contains($view, 'اتصال داده‌های دوره‌ای'), '37.18 Empty/dependency state has explanatory copy');
phase37Check(str_contains($view, 'admin-dashboard-state-error') && str_contains($view, 'role="status"'), '37.19 Error states are announced semantically');
phase37Check(str_contains($view, 'admin-dashboard-kpi-value') && str_contains($view, 'total_available_quantity'), '37.20 KPI UI binds to existing metrics, not hardcoded totals');

/* Batch 03 — charts, tables, Activity and Alerts: 10 checks. */
phase37Check(str_contains($view, 'admin-dashboard-chart-grid'), '37.21 Chart area is implemented');
phase37Check(str_contains($view, 'sales_by_period') && str_contains($view, 'net_sales'), '37.22 Sales chart consumes the existing sales field contract when provided');
phase37Check(str_contains($view, 'total_orders') && str_contains($view, 'admin-dashboard-chart-bar-secondary'), '37.23 Orders trend consumes the existing total-orders field contract when provided');
phase37Check(str_contains($view, '۱۴ روز اخیر') && str_contains($contract, 'GROUP_DAY'), '37.24 Dashboard declares the intended trend window and daily grouping contract');
phase37Check(str_contains($view, 'role="img"') && str_contains($view, 'aria-label="نمودار'), '37.25 Charts have accessible names');
phase37Check(str_contains($view, 'admin-dashboard-table') && str_contains($view, 'scope="col"'), '37.26 Period summary table uses semantic headers');
phase37Check(str_contains($view, 'قرارداد تأییدشده‌ای برای Activity Feed') || str_contains($view, 'قرارداد داده نیاز دارد'), '37.27 Activity Feed dependency is explicit; no fake activity is rendered');
phase37Check(str_contains($view, 'منبع هشدار هنوز تأیید نشده است') || str_contains($view, 'هشدارهای عملیاتی'), '37.28 Alerts data dependency is explicitly documented');
phase37Check(str_contains($view, 'salesByPeriodStatus === \'error\'') && str_contains($view, 'admin-dashboard-state-error'), '37.29 Trend failure has a dedicated error state');
phase37Check(str_contains($view, 'array_reverse($salesByPeriod)') && str_contains($view, 'admin-dashboard-table-wrap'), '37.30 Period rows are ordered for display and table overflow is contained');

/* Batch 04 — responsive, theme, RTL and integration: 10 checks. */
phase37Check(str_contains($mainCss, './mobile/mobile.css') && str_contains($mainCss, 'max-width: 600px'), '37.31 Mobile breakpoint entry is connected');
phase37Check(str_contains($mainCss, './tablet/tablet.css') && str_contains($mainCss, 'max-width: 1009px'), '37.32 Tablet breakpoint entry is connected');
phase37Check(str_contains($mainCss, './desktop/desktop.css') && str_contains($mainCss, 'min-width: 1010px'), '37.33 Desktop breakpoint entry is connected');
phase37Check(str_contains($mobileLayout, 'admin-dashboard-kpis') && str_contains($mobileComponents, 'admin-dashboard-chart-bars'), '37.34 Mobile dashboard has dedicated layout and chart styles');
phase37Check(str_contains($tabletLayout, 'admin-dashboard-kpis') && str_contains($tabletComponents, 'admin-dashboard-chart-bars'), '37.35 Tablet dashboard has dedicated layout and chart styles');
phase37Check(str_contains($desktopLayout, 'admin-dashboard-kpis') && str_contains($desktopComponents, 'admin-dashboard-chart-bars'), '37.36 Desktop dashboard has dedicated layout and chart styles');
phase37Check(str_contains($tokens, '--admin-color-bg') && str_contains($tokens, ':root[data-admin-theme="dark"]'), '37.37 Dashboard uses Admin-owned Light/Dark tokens');
phase37Check(str_contains($view, 'lang="fa" dir="rtl"') && str_contains($view, 'admin/assets/css/main.css'), '37.38 Persian RTL and Admin stylesheet contract are present');
phase37Check(str_contains($view, 'admin/assets/js/main.js') && str_contains($mainJs, 'adminTheme();'), '37.39 Existing Admin theme module remains connected');
phase37Check(!str_contains($desktopComponents, '@media') && !str_contains($tabletComponents, '@media') && !str_contains($mobileComponents, '@media'), '37.40 Viewport section styles avoid media-query mixing');

echo '<hr><strong>Phase 37 — Admin Dashboard Static Contract Test: '
    . $passed . '/' . $checks . ' PASS, '
    . $failed . ' FAIL</strong><br>' . PHP_EOL;
echo '<strong>Status: ' . ($failed === 0 ? 'STATIC CHECKS PASS — PHP RUNTIME AND BROWSER REVIEW STILL REQUIRED' : 'FAIL — FIX REQUIRED')
    . '</strong><br>' . PHP_EOL;
echo '<strong>LOCK: NO — until the static test is executed and PHP/runtime plus later browser regression are verified.</strong><br>' . PHP_EOL;
