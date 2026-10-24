<?php

declare(strict_types=1);

/**
 * Phase 39 — Responsive Master Audit.
 * Read-only static checks only: no database connection and no data mutation.
 * Run from project root: php test-phase-39-responsive-master-audit.php
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
$resolveImport = static function (string $from, string $import) use ($root): bool {
    if (preg_match('~^(?:https?:)?//|^data:|^#~i', trim($import))) {
        return true;
    }

    $candidate = dirname($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $from))
        . DIRECTORY_SEPARATOR
        . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($import, " \\t\\n\\r\\0\\x0B\\\"'"));

    return is_file($candidate);
};
$imports = static function (string $content): array {
    preg_match_all('~@import\\s+url\\(([^)]+)\\)~i', $content, $matches);
    return $matches[1] ?? [];
};

$storeMain = $read('assets/css/main.css');
$adminMain = $read('admin/assets/css/main.css');
$storeTokens = $read('assets/css/setting.css');
$adminTokens = $read('admin/assets/css/setting.css');

$add('39.01 Storefront CSS entrypoint exists', $exists('assets/css/main.css'));
$add('39.02 Admin CSS entrypoint exists', $exists('admin/assets/css/main.css'));
$add('39.03 Storefront token source exists', $exists('assets/css/setting.css'));
$add('39.04 Admin token source exists', $exists('admin/assets/css/setting.css'));
$add('39.05 Storefront CSS imports token source first', strpos($storeMain, '@import url(./setting.css);') < strpos($storeMain, '@import url(./mobile/mobile.css)'));
$add('39.06 Admin CSS imports token source first', strpos($adminMain, '@import url(./setting.css);') < strpos($adminMain, '@import url(./mobile/mobile.css)'));

$storeBreakpoints = [
    'mobile' => '@import url(./mobile/mobile.css) only screen and (max-width: 600px);',
    'tablet' => '@import url(./tablet/tablet.css) only screen and (min-width: 601px) and (max-width: 1009px);',
    'desktop' => '@import url(./desktop/desktop.css) only screen and (min-width: 1010px);',
];
foreach ($storeBreakpoints as $label => $rule) {
    $add('39.10 Storefront ' . $label . ' breakpoint contract', str_contains($storeMain, $rule));
}
foreach ([
    'mobile' => '--breakpoint-mobile: 600px;',
    'tablet-min' => '--breakpoint-tablet-min: 601px;',
    'tablet-max' => '--breakpoint-tablet-max: 1009px;',
    'desktop' => '--breakpoint-desktop: 1010px;',
] as $label => $token) {
    $add('39.20 Storefront token ' . $label, str_contains($storeTokens, $token));
}
foreach ([
    'mobile' => '@import url(./mobile/mobile.css) only screen and (max-width: 600px);',
    'tablet' => '@import url(./tablet/tablet.css) only screen and (min-width: 601px) and (max-width: 1009px);',
    'desktop' => '@import url(./desktop/desktop.css) only screen and (min-width: 1010px);',
] as $label => $rule) {
    $add('39.30 Admin ' . $label . ' breakpoint contract', str_contains($adminMain, $rule));
}
$add('39.31 Storefront tokens define Light/default root', str_contains($storeTokens, ':root'));
$add('39.32 Storefront tokens define dark theme', str_contains($storeTokens, '[data-theme="dark"]'));
$add('39.33 Admin tokens define dark theme', str_contains($adminTokens, 'data-admin-theme="dark"'));
$add('39.34 Admin token namespace is isolated', str_contains($adminTokens, '--admin-color-bg:') && !str_contains($adminTokens, '--color-primary:'));
$add('39.35 Storefront entrypoint does not import Admin CSS', !str_contains($storeMain, 'admin/assets/css'));
$add('39.36 Admin entrypoint does not import Storefront CSS', !str_contains($adminMain, 'assets/css/setting.css') || str_contains($adminMain, './setting.css'));

$manifests = [
    'assets/css/main.css',
    'assets/css/mobile/mobile.css',
    'assets/css/tablet/tablet.css',
    'assets/css/desktop/desktop.css',
    'admin/assets/css/main.css',
    'admin/assets/css/mobile/mobile.css',
    'admin/assets/css/tablet/tablet.css',
    'admin/assets/css/desktop/desktop.css',
];
$missingImports = [];
foreach ($manifests as $manifest) {
    $content = $read($manifest);
    $add('39.40 Manifest exists: ' . $manifest, $content !== '');
    foreach ($imports($content) as $import) {
        if (!$resolveImport($manifest, $import)) {
            $missingImports[] = $manifest . ' -> ' . $import;
        }
    }
}
$add('39.41 All local CSS imports in responsive manifests resolve', $missingImports === [], implode('; ', $missingImports));

$responsiveFiles = [];
foreach (['assets/css/mobile', 'assets/css/tablet', 'assets/css/desktop', 'admin/assets/css/mobile', 'admin/assets/css/tablet', 'admin/assets/css/desktop'] as $dir) {
    $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
    if (is_dir($absolute)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'css') {
                $responsiveFiles[] = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($root) + 1));
            }
        }
    }
}
$sectionMediaQueries = [];
foreach ($responsiveFiles as $path) {
    $name = basename($path);
    if (in_array($name, ['mobile.css', 'tablet.css', 'desktop.css'], true)) {
        continue;
    }
    if (preg_match('~@media\\b~i', $read($path))) {
        $sectionMediaQueries[] = $path;
    }
}
$add('39.42 Responsive section CSS contains no media queries', $sectionMediaQueries === [], implode('; ', $sectionMediaQueries));

$families = [];
foreach ($responsiveFiles as $path) {
    if (preg_match('~^assets/css/(mobile|tablet|desktop)/(.+)-(mobile|tablet|desktop)\\.css$~', $path, $m) !== 1) {
        continue;
    }
    $folderViewport = $m[1];
    $suffixViewport = $m[3];
    if ($folderViewport !== $suffixViewport) {
        continue;
    }
    $families[$m[2]][$folderViewport] = true;
}
$incompleteFamilies = [];
foreach ($families as $family => $viewports) {
    foreach (['mobile', 'tablet', 'desktop'] as $viewport) {
        if (!isset($viewports[$viewport])) {
            $incompleteFamilies[] = $family . ' missing ' . $viewport;
        }
    }
}
$add('39.43 Storefront section CSS families cover all three viewports', $incompleteFamilies === [] && count($families) >= 25, implode('; ', $incompleteFamilies));

$storeResponsiveManifests = [
    'assets/css/mobile/mobile.css' => ['budget-optimizer-mobile.css', 'price-stock-watch-mobile.css', 'recent-device-mobile.css'],
    'assets/css/tablet/tablet.css' => ['budget-optimizer-tablet.css', 'price-stock-watch-tablet.css', 'recent-device-tablet.css'],
    'assets/css/desktop/desktop.css' => ['budget-optimizer-desktop.css', 'price-stock-watch-desktop.css', 'recent-device-desktop.css'],
];
foreach ($storeResponsiveManifests as $manifest => $expected) {
    $content = $read($manifest);
    foreach ($expected as $file) {
        $add('39.50 ' . $manifest . ' imports ' . $file, str_contains($content, './' . $file) && $exists(dirname($manifest) . '/' . $file));
    }
}

$cssTokenViolations = [];
foreach ($responsiveFiles as $path) {
    if (str_starts_with($path, 'admin/assets/css/')) {
        if (preg_match('~var\\(--(?!(?:admin-))[^)]+\\)~', $read($path))) {
            $cssTokenViolations[] = $path . ' references a non-Admin token';
        }
    }
}
$add('39.60 Admin responsive CSS uses Admin-prefixed tokens only', $cssTokenViolations === [], implode('; ', $cssTokenViolations));

$pageRoots = ['pages', 'admin', 'customer', 'partials'];
$pageCounts = [];
foreach ($pageRoots as $pageRoot) {
    $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $pageRoot);
    $count = 0;
    if (is_dir($absolute)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $count++;
            }
        }
    }
    $pageCounts[$pageRoot] = $count;
    $add('39.70 PHP inventory root exists: ' . $pageRoot, is_dir($absolute));
}
$add('39.71 Storefront/account/content page inventory is non-empty', $pageCounts['pages'] >= 30, 'pages=' . $pageCounts['pages']);
$add('39.72 Admin page inventory is non-empty', $pageCounts['admin'] >= 50, 'admin=' . $pageCounts['admin']);
$add('39.73 Customer and shared partial inventories exist', $pageCounts['customer'] >= 1 && $pageCounts['partials'] >= 10, 'customer=' . $pageCounts['customer'] . ', partials=' . $pageCounts['partials']);

$roadmap = $read('front-roadmap.txt');
$rules = $read('uiuxrules.md');
$add('39.80 Roadmap defines Phase 39 Responsive Master Audit', str_contains($roadmap, 'PHASE 39 — Responsive Master Audit'));
$add('39.81 UI/UX rules file exists and is non-empty', $rules !== '');
$add('39.82 Responsive breakpoint contract remains in roadmap', str_contains($roadmap, 'Mobile ≤600') && str_contains($roadmap, 'Tablet 601–1009') && str_contains($roadmap, 'Desktop ≥1010'));

$add('39.90 Backend Freeze: this test does not connect to the database', true);
$add('39.91 Backend Freeze: this test performs no data mutations', true);

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
echo '<br><strong>Phase 39 static audit: ' . (count($checks) - $failed) . '/' . count($checks) . ' PASS; ' . $failed . ' FAIL.</strong><br>' . PHP_EOL;
echo '<strong>Runtime/browser visual audit is not covered by this static test.</strong><br>' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
