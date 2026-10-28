<?php
/**
 * Nivad Phase 41 — Motion & Interaction source audit.
 * Read-only: this script never writes to project files or the database.
 */

declare(strict_types=1);

$root = __DIR__;
$passed = 0;
$failed = 0;

$add = static function (string $label, bool $condition) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo '<span style="color:green">[PASS]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '<br>' . PHP_EOL;
        return;
    }

    $failed++;
    echo '<span style="color:red">[FAIL]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '<br>' . PHP_EOL;
};

$read = static function (string $relative) use ($root): ?string {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) ? file_get_contents($path) : null;
};

$storeTokens = $read('assets/css/setting.css');
$adminTokens = $read('admin/assets/css/setting.css');
$drawerJs = $read('assets/js/modules/cart-drawer.js');
$mobileDrawer = $read('assets/css/mobile/cart-drawer-mobile.css');
$tabletDrawer = $read('assets/css/tablet/cart-drawer-tablet.css');
$desktopDrawer = $read('assets/css/desktop/cart-drawer-desktop.css');
$storeManifest = $read('assets/css/main.css');
$adminManifest = $read('admin/assets/css/main.css');

$add('41.01 Storefront settings stylesheet exists', $storeTokens !== null);
$add('41.02 Admin settings stylesheet exists', $adminTokens !== null);
$add('41.03 Cart drawer interaction module exists', $drawerJs !== null);
$add('41.04 Mobile cart drawer stylesheet exists', $mobileDrawer !== null);
$add('41.05 Tablet cart drawer stylesheet exists', $tabletDrawer !== null);
$add('41.06 Desktop cart drawer stylesheet exists', $desktopDrawer !== null);
$add('41.07 Storefront and Admin CSS entry points exist', $storeManifest !== null && $adminManifest !== null);

$add('41.08 Storefront has fast/normal/slow transition tokens', $storeTokens !== null
    && str_contains($storeTokens, '--transition-fast:')
    && str_contains($storeTokens, '--transition-normal:')
    && str_contains($storeTokens, '--transition-slow:'));
$add('41.09 Storefront reduced-motion media query exists', $storeTokens !== null
    && str_contains($storeTokens, '@media (prefers-reduced-motion: reduce)'));
$add('41.10 Storefront reduced motion disables smooth scrolling', $storeTokens !== null
    && preg_match('/@media\s*\(prefers-reduced-motion:\s*reduce\)[\s\S]*?scroll-behavior:\s*auto\s*;/', $storeTokens) === 1);
$add('41.11 Admin transition token is Admin-owned', $adminTokens !== null
    && str_contains($adminTokens, '--admin-transition:'));
$add('41.12 Admin reduced-motion media query exists', $adminTokens !== null
    && str_contains($adminTokens, '@media (prefers-reduced-motion: reduce)'));
$add('41.13 Admin reduced motion disables smooth scrolling', $adminTokens !== null
    && preg_match('/@media\s*\(prefers-reduced-motion:\s*reduce\)[\s\S]*?scroll-behavior:\s*auto\s*;/', $adminTokens) === 1);

$add('41.14 Cart drawer tracks pending close cleanup', $drawerJs !== null
    && str_contains($drawerJs, 'let closeTimer = null;')
    && str_contains($drawerJs, 'const finishClose = () =>'));
$add('41.15 Cart drawer has explicit closing state', $drawerJs !== null
    && str_contains($drawerJs, 'drawer.classList.add("is-closing")'));
$add('41.16 Cart drawer exit listens for panel transform transitionend', $drawerJs !== null
    && str_contains($drawerJs, 'event.propertyName === "transform"')
    && str_contains($drawerJs, 'drawer.classList.contains("is-closing")'));
$add('41.17 Cart drawer has fallback cleanup timer', $drawerJs !== null
    && str_contains($drawerJs, 'window.setTimeout(finishClose, 350)'));
$add('41.18 Reopening drawer cancels pending close cleanup', $drawerJs !== null
    && str_contains($drawerJs, 'finishClose();')
    && str_contains($drawerJs, 'drawer.classList.remove("is-closing")'));
$add('41.19 Escape closes drawer through shared close handler', $drawerJs !== null
    && preg_match('/event\.key === "Escape"[\s\S]{0,120}close\(\);/', $drawerJs) === 1);
$add('41.20 Drawer restores focus to previous element', $drawerJs !== null
    && str_contains($drawerJs, 'lastFocusedElement.focus()'));

$add('41.21 Mobile drawer remains rendered while closing', $mobileDrawer !== null
    && preg_match('/\.cart-drawer\.is-closing\s*\{[^}]*visibility:\s*visible;[^}]*pointer-events:\s*none;/s', $mobileDrawer) === 1);
$add('41.22 Tablet drawer remains rendered while closing', $tabletDrawer !== null
    && preg_match('/\.cart-drawer\.is-closing\s*\{[^}]*visibility:\s*visible;[^}]*opacity:\s*1;[^}]*pointer-events:\s*none;/s', $tabletDrawer) === 1);
$add('41.23 Desktop drawer remains rendered while closing', $desktopDrawer !== null
    && preg_match('/\.cart-drawer\.is-closing\s*\{[^}]*visibility:\s*visible;[^}]*opacity:\s*1;[^}]*pointer-events:\s*none;/s', $desktopDrawer) === 1);
$add('41.24 Responsive drawer styles continue to use motion tokens', $mobileDrawer !== null && $tabletDrawer !== null && $desktopDrawer !== null
    && str_contains($mobileDrawer, 'var(--transition-')
    && str_contains($tabletDrawer, 'var(--transition-')
    && str_contains($desktopDrawer, 'var(--transition-'));
$add('41.25 Storefront and Admin entry points remain separate', $storeManifest !== null && $adminManifest !== null
    && !str_contains($storeManifest, 'admin/assets/css/')
    && !str_contains($adminManifest, 'assets/css/setting.css'));

echo '<br><strong>Phase 41 source audit: ' . $passed . ' PASS / ' . $failed . ' FAIL</strong><br>' . PHP_EOL;
echo '<strong>Note:</strong> source assertions only; this output does not substitute for PHP lint, browser interaction, or reduced-motion runtime testing.<br>' . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
