<?php

declare(strict_types=1);

/**
 * Static regression checks for the admin UI shell.
 * These checks do not claim browser, database, or live-mail verification.
 */
$checks = [];
$check = static function (string $label, bool $passed) use (&$checks): void {
    $checks[] = ['label' => $label, 'passed' => $passed];
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};

$read = static function (string $path): string {
    $fullPath = __DIR__ . '/' . $path;
    if (!is_file($fullPath)) {
        return '';
    }
    $content = file_get_contents($fullPath);
    return is_string($content) ? $content : '';
};

$shell = $read('admin/includes/auth.php');
$dashboard = $read('pages/admin/dashboard.php');
$tokens = $read('admin/assets/css/setting.css');
$desktop = $read('admin/assets/css/desktop/shared-shell-desktop.css');
$tablet = $read('admin/assets/css/tablet/shared-shell-tablet.css');
$mobile = $read('admin/assets/css/mobile/shared-shell-mobile.css');
$mainCss = $read('admin/assets/css/main.css');
$navigationJs = $read('admin/assets/js/modules/navigation-state.js');

$check('Shared authenticated shell keeps sidebar and header injection', str_contains($shell, 'admin-shared-sidebar') && str_contains($shell, 'admin-shared-topbar'));
$check('Header uses first and last name and initials safely', str_contains($shell, "['first_name']") && str_contains($shell, "['last_name']") && str_contains($shell, '$adminInitials') && str_contains($shell, 'admin-shared-profile-copy'));
$check('Shared shell assigns sanitized route context classes for page-level styling', str_contains($shell, '$adminPageClasses = \'admin-page admin-page-\' . $adminSection'));
$check('Dashboard calculates chart scales after selecting real or demo data', strpos($dashboard, '$isDemoDashboard') < strpos($dashboard, '$maxNetSales = 0.0;'));
$check('Desktop sidebar is anchored to the physical right edge', str_contains($desktop, 'right:0!important') && str_contains($desktop, 'padding-right: calc(var(--admin-sidebar-width)'));
$check('Tablet layout keeps navigation in flow without overlay', str_contains($tablet, 'position:static!important') && str_contains($tablet, 'admin-shared-sidebar'));
$check('Mobile layout keeps navigation in flow without overlay', str_contains($mobile, 'position:static!important') && str_contains($mobile, 'admin-shared-sidebar'));
$check('Dashboard has labelled demo data and 14-day sample series', str_contains($dashboard, '$isDemoDashboard') && str_contains($dashboard, 'داده نمونه') && str_contains($dashboard, '$demoOrders') && str_contains($dashboard, '$demoSales'));
$check('Dashboard charts accept live metrics when status is ready', str_contains($dashboard, '$salesByPeriodStatus = (string)($metrics[\'sales_by_period_status\'] ?? \'dependency\')') && str_contains($dashboard, "['ready', 'demo']"));
$check('Admin typography uses the storefront Persian font family', str_contains($tokens, 'font-family: "Vazirmatn"') && str_contains($tokens, '--admin-font-family: "Vazirmatn"'));
$check('Responsive CSS architecture remains split by breakpoint', str_contains($mainCss, './mobile/mobile.css') && str_contains($mainCss, './tablet/tablet.css') && str_contains($mainCss, './desktop/desktop.css'));
$check('Navigation state continues to expose active page accessibly', str_contains($navigationJs, 'aria-current') && str_contains($navigationJs, 'closest("details")'));

$failed = count(array_filter($checks, static fn (array $item): bool => !$item['passed']));
echo PHP_EOL . 'Summary: ' . (count($checks) - $failed) . ' PASS, ' . $failed . ' FAIL' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
