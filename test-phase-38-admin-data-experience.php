<?php

declare(strict_types=1);

/**
 * Phase 38 static contract checks.
 * Read-only: no database connection and no INSERT/UPDATE/DELETE.
 */

$root = __DIR__;
$checks = [];
$add = static function (string $name, bool $passed) use (&$checks): void {
    $checks[] = [$name, $passed];
};
$read = static function (string $relative) use ($root): string {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) ? (string) file_get_contents($path) : '';
};
$exists = static function (string $relative) use ($root): bool {
    return is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
};

$modulePath = 'admin/assets/js/modules/data-experience.js';
$module = $read($modulePath);
$mainJs = $read('admin/assets/js/main.js');
$mobileCss = $read('admin/assets/css/mobile/tables-mobile.css');
$tabletCss = $read('admin/assets/css/tablet/tables-tablet.css');
$desktopCss = $read('admin/assets/css/desktop/tables-desktop.css');
$roadmap = $read('front-roadmap.txt');
$authMiddleware = $read('app/Middleware/AdminAuthMiddleware.php');
$csrfMiddleware = $read('app/Middleware/CsrfMiddleware.php');

$add('38.01 data experience module exists', $exists($modulePath));
$add('38.02 main.js imports the data module', str_contains($mainJs, './modules/data-experience.js'));
$add('38.03 main.js invokes the data module', str_contains($mainJs, 'adminDataExperience();'));
$add('38.04 module uses a default export', str_contains($module, 'export default adminDataExperience;'));
$add('38.05 mobile table stylesheet exists', $exists('admin/assets/css/mobile/tables-mobile.css'));
$add('38.06 tablet table stylesheet exists', $exists('admin/assets/css/tablet/tables-tablet.css'));
$add('38.07 desktop table stylesheet exists', $exists('admin/assets/css/desktop/tables-desktop.css'));
$add('38.08 mobile styles use Admin tokens', str_contains($mobileCss, 'var(--admin-space-'));
$add('38.09 tablet styles use Admin tokens', str_contains($tabletCss, 'var(--admin-space-'));
$add('38.10 desktop styles use Admin tokens', str_contains($desktopCss, 'var(--admin-space-'));
$add('38.11 mobile section stylesheet has no media query', !str_contains(strtolower($mobileCss), '@media'));
$add('38.12 tablet section stylesheet has no media query', !str_contains(strtolower($tabletCss), '@media'));
$add('38.13 desktop section stylesheet has no media query', !str_contains(strtolower($desktopCss), '@media'));
$add('38.14 mobile toolbar styling exists', str_contains($mobileCss, '.admin-data-toolbar'));
$add('38.15 tablet toolbar styling exists', str_contains($tabletCss, '.admin-data-toolbar'));
$add('38.16 desktop toolbar styling exists', str_contains($desktopCss, '.admin-data-toolbar'));
$add('38.17 table search control exists', str_contains($module, 'admin-data-search'));
$add('38.18 status filter is conditionally derived from a status column', str_contains($module, 'statusColumn'));
$add('38.19 page-size control exists', str_contains($module, 'admin-data-page-size'));
$add('38.20 client-side pagination calculates page count', str_contains($module, 'Math.ceil(visibleRows.length / size)'));
$add('38.21 search normalizes text before matching', str_contains($module, 'normalize(rowText(row)).includes(query)'));
$add('38.22 rows are filtered by status when available', str_contains($module, 'matchesStatus'));
$add('38.23 non-current rows are hidden', str_contains($module, 'row.hidden = !pageRows.has(row)'));
$add('38.24 table status uses a live region', str_contains($module, 'aria-live'));
$add('38.25 row selection uses labelled checkboxes', str_contains($module, 'انتخاب ردیف'));
$add('38.26 selected rows can be exported as CSV', str_contains($module, 'text/csv;charset=utf-8;'));
$add('38.27 CSV export protects spreadsheet encoding with BOM', str_contains($module, '\\uFEFF'));
$add('38.28 selection can be cleared', str_contains($module, 'selected.clear()'));
$add('38.29 detached rows are removed from selection', str_contains($module, 'if (!realRows.includes(row)) selected.delete(row)'));
$add('38.30 duplicate form submissions are blocked', str_contains($module, 'form.dataset.submitting === "true"'));
$add('38.31 native form validity is checked', str_contains($module, 'form.checkValidity()'));
$add('38.32 submit controls are disabled during submission', str_contains($module, 'button.disabled = true'));
$add('38.33 feedback messages receive live announcements', str_contains($module, 'node.setAttribute("aria-live", "polite")'));
$add('38.34 error feedback can be announced as alert', str_contains($module, 'node.setAttribute("role", "alert")'));
$add('38.35 existing Admin theme module remains registered', str_contains($mainJs, './modules/theme.js'));
$add('38.36 Admin authentication middleware exists', $exists('app/Middleware/AdminAuthMiddleware.php') && str_contains($authMiddleware, 'admin.access'));
$add('38.37 CSRF middleware exists and verifies tokens', $exists('app/Middleware/CsrfMiddleware.php') && str_contains($csrfMiddleware, 'Csrf::verify'));
$add('38.38 declared permission controls use a fail-closed hook', str_contains($module, 'enforceDeclaredPermissions') && str_contains($module, 'data-admin-permission'));
$add('38.39 product list is connected to Admin CSS', str_contains($read('admin/products/index.php'), '/admin/assets/css/main.css'));
$add('38.40 product list is connected to Admin JS', str_contains($read('admin/products/index.php'), '/admin/assets/js/main.js'));

$passed = 0;
foreach ($checks as [$name, $ok]) {
    echo '<span style="color:' . ($ok ? 'green' : 'red') . '">[' . ($ok ? 'PASS' : 'FAIL') . '] ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span><br>' . PHP_EOL;
    if ($ok) {
        $passed++;
    }
}

echo '<br><strong>Phase 38 static checks: ' . $passed . '/' . count($checks) . '</strong><br>' . PHP_EOL;
echo '<strong>Read-only: no database writes were performed.</strong><br>' . PHP_EOL;

if ($passed !== count($checks)) {
    exit(1);
}
