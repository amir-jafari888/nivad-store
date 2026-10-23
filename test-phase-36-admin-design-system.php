<?php

declare(strict_types=1);

/**
 * Nivad Phase 36 — Admin Design System static regression test.
 * Read-only: no database writes and no application state mutation.
 */

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

function phase36Read(string $path): string
{
    global $root;
    $fullPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    return is_file($fullPath) ? (string) file_get_contents($fullPath) : '';
}

$setting = phase36Read('assets/css/setting.css');
$css = phase36Read('assets/css/admin/admin.css');
$js = phase36Read('assets/js/admin-theme.js');
$auth = phase36Read('admin/includes/auth.php');

phase36Check($setting !== '', '36 — setting.css exists');
phase36Check(str_contains($setting, '--admin-color-bg'), '36 — centralized Admin color tokens exist in setting.css');
phase36Check(str_contains($setting, '--admin-control-height'), '36 — Admin control-height token exists in setting.css');
phase36Check(str_contains($setting, ':root[data-admin-theme="dark"]'), '36 — dark-theme tokens exist in setting.css');

phase36Check($css !== '', '36 — shared Admin stylesheet exists');
phase36Check(str_starts_with(ltrim($css), '@import url("../setting.css");'), '36 — Admin stylesheet imports the central token source');
phase36Check(str_contains($css, 'var(--admin-color-bg)'), '36 — Admin stylesheet consumes centralized tokens');
phase36Check(str_contains($css, ':root[data-admin-theme="dark"]'), '36 — dark-theme component rules exist');
phase36Check(str_contains($css, '@media (max-width: 1009px)'), '36 — tablet responsive rules exist');
phase36Check(str_contains($css, '@media (max-width: 600px)'), '36 — mobile responsive rules exist');
phase36Check(str_contains($css, ':focus-visible'), '36 — visible keyboard focus rules exist');
phase36Check(str_contains($css, 'prefers-reduced-motion'), '36 — reduced-motion support exists');
phase36Check(str_contains($css, 'overflow-x: auto'), '36 — responsive table overflow is supported');
phase36Check(str_contains($css, 'body > div:has(> aside)'), '36 — shared Admin sidebar shell styles exist');
phase36Check(str_contains($css, 'main > section'), '36 — responsive dashboard component grid exists');

phase36Check($js !== '', '36 — Admin theme script exists');
phase36Check(str_contains($js, 'localStorage'), '36 — theme preference persistence is implemented');
phase36Check(str_contains($js, 'aria-pressed'), '36 — theme toggle exposes pressed state');
phase36Check(str_contains($js, 'DOMContentLoaded'), '36 — theme toggle waits for DOM readiness');

phase36Check(str_contains($auth, 'ob_start('), '36 — authenticated Admin pages load shared assets');
phase36Check(str_contains($auth, '/assets/css/admin/admin.css'), '36 — shared Admin stylesheet injection exists');
phase36Check(str_contains($auth, '/assets/js/admin-theme.js'), '36 — shared Admin theme script injection exists');

$directAssetPages = [
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

foreach ($directAssetPages as $path) {
    $content = phase36Read($path);
    phase36Check(
        $content !== ''
        && str_contains($content, '/assets/css/admin/admin.css')
        && str_contains($content, '/assets/js/admin-theme.js'),
        '36 — standalone Admin page includes design assets: ' . $path
    );
}

$adminRoot = $root . DIRECTORY_SEPARATOR . 'admin';
if (is_dir($adminRoot)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($adminRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $path = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        if ($relative === 'admin/includes/auth.php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        if (stripos($content, '<!DOCTYPE html') === false) {
            continue;
        }

        $usesSharedAuth = str_contains($content, 'includes/auth.php');
        $hasDirectAssets = str_contains($content, '/assets/css/admin/admin.css')
            && str_contains($content, '/assets/js/admin-theme.js');

        phase36Check(
            $usesSharedAuth || $hasDirectAssets,
            '36 — Admin HTML page is covered by shared design system: ' . $relative
        );
    }
}

echo '<hr>';
echo 'PHASE 36 STATIC RESULT: ' . $passed . ' PASS / ' . $failed . ' FAIL / ' . $checks . ' TOTAL<br>' . PHP_EOL;
echo 'This test validates static contracts only; browser, contrast, PHP runtime and responsive visual tests remain separate.' . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
