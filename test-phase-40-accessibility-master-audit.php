<?php

declare(strict_types=1);

/**
 * Phase 40 — Accessibility Master Audit (read-only static checks).
 * No database connection and no data mutation.
 * Run from project root: php test-phase-40-accessibility-master-audit.php
 *
 * Static assertions do not replace browser, keyboard, contrast, zoom,
 * or screen-reader verification. Missing files are reported as failures.
 */

$root = __DIR__;
$checks = [];
$add = static function (string $name, bool $passed, string $detail = '') use (&$checks): void {
    $checks[] = [$name, $passed, $detail];
};
$read = static function (string $relative) use ($root): string {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) ? (string) file_get_contents($path) : '';
};
$exists = static function (string $relative) use ($root): bool {
    return is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
};

$header = $read('partials/header.php');
$storeTokens = $read('assets/css/setting.css');
$adminTokens = $read('admin/assets/css/setting.css');
$search = $read('assets/js/modules/search.js');
$cartDrawer = $read('assets/js/modules/cart-drawer.js');
$storeMain = $read('assets/css/main.css');
$adminMain = $read('admin/assets/css/main.css');

$add('40.01 phase plan exists', $exists('phase-40-accessibility-master-audit-plan.md'));
$testSource = $read('test-phase-40-accessibility-master-audit.php');
$add('40.02 test is read-only and contains no database connection or mutation calls',
    !str_contains($testSource, 'new ' . 'PDO(')
    && !str_contains($testSource, 'mysqli_' . 'connect(')
    && !preg_match('~\\b(?:INSERT|UPDATE|DELETE)\\s+INTO?\\b~i', $testSource));
$add('40.03 storefront shared header declares Persian RTL', str_contains($header, '<html lang="fa" dir="rtl">'));
$add('40.04 storefront shared header includes skip navigation', str_contains($header, 'class="skip-link" href="#main-content"'));
$add('40.05 storefront token file styles skip navigation', str_contains($storeTokens, '.skip-link:focus-visible'));
$add('40.06 storefront skip-link styles use existing design tokens', str_contains($storeTokens, 'var(--color-primary)') && str_contains($storeTokens, 'var(--spacing-3)'));
$add('40.07 global storefront focus-visible indicator exists', str_contains($storeTokens, ':focus-visible'));
$add('40.08 reduced-motion preference is handled by storefront tokens', str_contains($storeTokens, 'prefers-reduced-motion: reduce'));
$add('40.09 Admin skip navigation has dedicated styles', str_contains($adminTokens, '.admin-skip-link:focus-visible'));
$add('40.10 Admin skip navigation styles use Admin-only tokens', str_contains($adminTokens, 'var(--admin-color-primary)') && str_contains($adminTokens, 'var(--admin-space-3)'));
$add('40.11 storefront and Admin stylesheets remain separate', str_contains($storeMain, './setting.css') && str_contains($adminMain, './setting.css') && !str_contains($storeMain, 'admin/assets/css'));

foreach ([
    'pages/shop/cart.php',
    'pages/shop/checkout.php',
    'pages/auth/login.php',
    'pages/auth/register.php',
    'pages/store/search/index.php',
    'pages/store/products/show.php',
] as $path) {
    $source = $read($path);
    $add('40.12 main-content skip target exists: ' . $path, str_contains($source, 'id="main-content"') && str_contains($source, 'tabindex="-1"'));
}

foreach ([
    'admin/auth/login.php',
    'admin/index.php',
    'pages/admin/dashboard.php',
] as $path) {
    $source = $read($path);
    $add('40.13 Admin skip target exists: ' . $path, str_contains($source, 'class="admin-skip-link" href="#main-content"') && str_contains($source, 'id="main-content"'));
}
$add('40.14 Admin login errors use assertive live feedback', str_contains($read('admin/auth/login.php'), 'role="alert" aria-live="assertive"'));
$add('40.15 storefront search input has a visible programmatic label', str_contains($header, '<label class="sr-only" for="search-input">') && str_contains($header, 'id="search-input"'));
$add('40.16 search suggestions expose combobox semantics', str_contains($search, 'setAttribute("role", "combobox")') && str_contains($search, 'setAttribute("aria-autocomplete", "list")'));
$add('40.17 search combobox references the suggestions listbox', str_contains($search, 'setAttribute("aria-controls", "search-suggestions")') && str_contains($search, 'suggestionBox.id = "search-suggestions"'));
$add('40.18 search options expose selected state and active descendant', str_contains($search, 'role="option"') && str_contains($search, 'aria-selected') && str_contains($search, 'aria-activedescendant'));
$add('40.19 search supports Escape and arrow-key navigation', str_contains($search, 'event.key === "Escape"') && str_contains($search, 'event.key === "ArrowDown"') && str_contains($search, 'event.key === "ArrowUp"'));
$add('40.20 search suggestions are not nested interactive buttons in listbox options', !preg_match('~<button[^>]+role="option"~', $search));
$add('40.21 cart drawer has named quantity/remove controls', str_contains($cartDrawer, 'aria-label="حذف ${escapeHTML(name)}"') && str_contains($cartDrawer, 'aria-label="افزایش تعداد ${escapeHTML(name)}"'));
$add('40.22 customer cart has a polite status region', str_contains($read('pages/shop/cart.php'), 'role="status"') && str_contains($read('pages/shop/cart.php'), 'aria-live="polite"'));
$add('40.23 checkout has named progress navigation', str_contains($read('pages/shop/checkout.php'), 'aria-label="مراحل خرید"'));
$add('40.24 checkout has an alert region for errors', str_contains($read('pages/shop/checkout.php'), 'role="alert"') && str_contains($read('pages/shop/checkout.php'), 'aria-live="assertive"'));
$add('40.25 customer login fields are associated with labels', str_contains($read('pages/auth/login.php'), 'for="auth-identifier"') && str_contains($read('pages/auth/login.php'), 'for="auth-password"'));
$add('40.26 registration inputs use labels and native constraints', str_contains($read('pages/auth/register.php'), 'for="auth-register-email"') && str_contains($read('pages/auth/register.php'), 'type="email"') && str_contains($read('pages/auth/register.php'), 'required'));
$add('40.27 product detail main landmark has a skip target', str_contains($read('pages/store/products/show.php'), 'id="main-content"'));
$add('40.28 Admin dashboard main landmark has a skip target', str_contains($read('pages/admin/dashboard.php'), 'id="main-content"'));
$add('40.29 legacy Admin navigation has an accessible name', str_contains($read('admin/index.php'), '<nav aria-label="ناوبری مدیریت">'));
$add('40.30 Admin and storefront token namespaces are distinct', str_contains($adminTokens, '--admin-color-bg:') && !str_contains($adminTokens, '--color-primary:'));

$failed = 0;
foreach ($checks as [$name, $passed, $detail]) {
    echo '<span style="color:' . ($passed ? 'green' : 'red') . '">[' . ($passed ? 'PASS' : 'FAIL') . ']</span> '
        . htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if ($detail !== '') {
        echo ' — ' . htmlspecialchars($detail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    echo '<br>' . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

echo '<hr><strong>Phase 40 static accessibility checks: '
    . (count($checks) - $failed) . '/' . count($checks) . ' PASS; '
    . $failed . ' FAIL.</strong><br>' . PHP_EOL;
echo '<strong>Static source checks only. Browser, keyboard, contrast, zoom, and screen-reader verification remain separate.</strong><br>' . PHP_EOL;
echo '<strong>PASS/LOCK: not granted by this script.</strong><br>' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
