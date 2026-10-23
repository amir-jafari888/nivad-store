<?php

declare(strict_types=1);

/** Nivad Phase 36 — read-only Admin asset architecture regression. */
$root = __DIR__;
$checks = 0;
$passed = 0;
$failed = 0;

function phase36Check(bool $condition, string $label): void
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

function phase36Read(string $relativePath): string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    return is_file($path) ? (string) file_get_contents($path) : '';
}

$adminCss = 'admin/assets/css/';
$adminJs = 'admin/assets/js/';
$mainCss = phase36Read($adminCss . 'main.css');
$tokens = phase36Read($adminCss . 'setting.css');
$mainJs = phase36Read($adminJs . 'main.js');
$themeModule = phase36Read($adminJs . 'modules/theme.js');
$authInclude = phase36Read('admin/includes/auth.php');
$storefrontTokens = phase36Read('assets/css/setting.css');

phase36Check($mainCss !== '', '36 — Admin owns a separate CSS main entry');
phase36Check($tokens !== '', '36 — Admin owns an isolated token file');
phase36Check(str_contains($mainCss, './setting.css'), '36 — Admin main CSS imports Admin tokens');
phase36Check(str_contains($mainCss, './mobile/mobile.css') && str_contains($mainCss, 'max-width: 600px'), '36 — mobile stylesheet uses project import architecture');
phase36Check(str_contains($mainCss, './tablet/tablet.css') && str_contains($mainCss, 'min-width: 601px') && str_contains($mainCss, 'max-width: 1009px'), '36 — tablet stylesheet uses project import architecture');
phase36Check(str_contains($mainCss, './desktop/desktop.css') && str_contains($mainCss, 'min-width: 1010px'), '36 — desktop stylesheet uses project import architecture');
phase36Check(str_contains($tokens, '--admin-color-bg') && str_contains($tokens, '--admin-control-height'), '36 — Admin semantic design tokens exist');
phase36Check(str_contains($tokens, ':root[data-admin-theme="dark"]'), '36 — Admin dark theme tokens exist');
phase36Check(!str_contains($tokens, 'assets/css/setting.css'), '36 — Admin tokens do not import storefront token file');
phase36Check($mainJs !== '' && str_contains($mainJs, './modules/theme.js'), '36 — Admin JS main imports theme module');
phase36Check(str_contains($mainJs, 'adminTheme();'), '36 — Admin JS main initializes imported module');
phase36Check(str_contains($themeModule, 'const adminTheme = () =>') && str_contains($themeModule, 'export default adminTheme;'), '36 — module behavior is wrapped in one exported function');
phase36Check(str_contains($themeModule, 'localStorage') && str_contains($themeModule, 'aria-pressed'), '36 — theme persistence and ARIA state exist');
phase36Check(str_contains($authInclude, '/admin/assets/css/main.css') && str_contains($authInclude, '/admin/assets/js/main.js'), '36 — authenticated pages load isolated Admin assets');

$deviceFiles = [
    'mobile/layout-mobile.css', 'mobile/components-mobile.css', 'mobile/forms-mobile.css', 'mobile/tables-mobile.css', 'mobile/auth-mobile.css',
    'tablet/layout-tablet.css', 'tablet/components-tablet.css', 'tablet/forms-tablet.css', 'tablet/tables-tablet.css', 'tablet/auth-tablet.css',
    'desktop/layout-desktop.css', 'desktop/components-desktop.css', 'desktop/forms-desktop.css', 'desktop/tables-desktop.css', 'desktop/auth-desktop.css',
];
foreach ($deviceFiles as $file) {
    $content = phase36Read($adminCss . $file);
    phase36Check($content !== '', '36 — device stylesheet exists: ' . $file);
    phase36Check(!preg_match('/@media\\s*\\(/i', $content), '36 — no @media rule in ' . $file);
}

$legacyFiles = [
    'admin/auth/login.php',
    'admin/reports/index.php',
    'admin/reports/sales.php',
    'admin/reports/orders.php',
    'admin/reports/products.php',
    'admin/reports/categories.php',
    'admin/returns/index.php',
    'admin/returns/show.php',
    'admin/refunds/index.php',
    'admin/refunds/show.php',
];
foreach ($legacyFiles as $file) {
    $content = phase36Read($file);
    phase36Check($content !== '', '36 — standalone Admin page exists: ' . $file);
    phase36Check(str_contains($content, '/admin/assets/css/main.css') && str_contains($content, '/admin/assets/js/main.js'), '36 — standalone page uses isolated assets: ' . $file);
}

phase36Check(!str_contains($storefrontTokens, 'NIVAD ADMIN DESIGN SYSTEM — Phase 36'), '36 — Admin tokens removed from storefront setting.css');

echo '<hr><strong>Phase 36 Admin Asset Architecture: '
    . $passed . '/' . $checks . ' PASS, '
    . $failed . ' FAIL</strong><br>' . PHP_EOL;
echo '<strong>Status: ' . ($failed === 0 ? 'STATIC CHECKS PASS — RUNTIME/VISUAL TESTS STILL REQUIRED' : 'FAIL — FIX REQUIRED')
    . '</strong><br>' . PHP_EOL;
echo '<strong>LOCK: NO — until PHP/XAMPP and browser regression checks are completed.</strong><br>' . PHP_EOL;
