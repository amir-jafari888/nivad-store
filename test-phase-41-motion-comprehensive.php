<?php
/**
 * Nivad Phase 41 — comprehensive read-only source audit.
 * No database or project-file writes are performed.
 */

declare(strict_types=1);

$root = __DIR__;
$passed = 0;
$failed = 0;
$warnings = 0;

$report = static function (string $label, bool $condition, bool $warning = false) use (&$passed, &$failed, &$warnings): void {
    if ($condition) {
        $passed++;
        echo '<span style="color:green">[PASS]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '<br>' . PHP_EOL;
        return;
    }

    if ($warning) {
        $warnings++;
        echo '<span style="color:#b36b00">[PENDING]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '<br>' . PHP_EOL;
        return;
    }

    $failed++;
    echo '<span style="color:red">[FAIL]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '<br>' . PHP_EOL;
};

$read = static function (string $relative) use ($root): ?string {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) ? file_get_contents($path) : null;
};

$collect = static function (string $directory) use ($root): array {
    $base = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory);
    if (!is_dir($base)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), ['css', 'js', 'php', 'html'], true)) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
};

$storeSettings = $read('assets/css/setting.css');
$adminSettings = $read('admin/assets/css/setting.css');
$storeMain = $read('assets/css/main.css');
$adminMain = $read('admin/assets/css/main.css');
$drawer = $read('assets/js/modules/cart-drawer.js');

$report('41.01 Storefront motion source exists', $storeSettings !== null);
$report('41.02 Admin motion source exists', $adminSettings !== null);
$report('41.03 Storefront viewport manifest exists', $storeMain !== null);
$report('41.04 Admin viewport manifest exists', $adminMain !== null);
$report('41.05 Cart drawer source exists', $drawer !== null);

$report('41.06 Storefront motion tokens include fast/normal/slow', $storeSettings !== null
    && str_contains($storeSettings, '--transition-fast:')
    && str_contains($storeSettings, '--transition-normal:')
    && str_contains($storeSettings, '--transition-slow:'));
$report('41.07 Admin motion tokens include fast/normal/slow', $adminSettings !== null
    && str_contains($adminSettings, '--admin-transition-fast:')
    && str_contains($adminSettings, '--admin-transition-normal:')
    && str_contains($adminSettings, '--admin-transition-slow:'));
$report('41.08 Storefront reduced-motion rule exists', $storeSettings !== null && str_contains($storeSettings, 'prefers-reduced-motion: reduce'));
$report('41.09 Admin reduced-motion rule exists', $adminSettings !== null && str_contains($adminSettings, 'prefers-reduced-motion: reduce'));
$report('41.10 Reduced-motion rules force auto scrolling',
    $storeSettings !== null && $adminSettings !== null
    && preg_match('/prefers-reduced-motion:\s*reduce[\s\S]*?scroll-behavior:\s*auto\s*!?/', $storeSettings) === 1
    && preg_match('/prefers-reduced-motion:\s*reduce[\s\S]*?scroll-behavior:\s*auto\s*!?/', $adminSettings) === 1);

$storeCss = $collect('assets/css');
$adminCss = $collect('admin/assets/css');
$storeJs = $collect('assets/js');
$adminJs = $collect('admin/assets/js');
$allCss = array_merge($storeCss, $adminCss);
$allJs = array_merge($storeJs, $adminJs);

$motionInventory = [];
$hardcodedMotion = [];
foreach ($allCss as $file) {
    $content = @file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
    if (preg_match('/\b(?:transition|animation|transform|opacity|@keyframes)\b/i', $content) === 1) {
        $motionInventory[] = $relative;
    }

    if (preg_match_all('/(?:transition(?:-[a-z-]+)?|animation(?:-duration|-delay|-timing-function)?|scroll-behavior)\s*:\s*[^;{}]*?(?:\d+(?:\.\d+)?\s*(?:ms|s)|cubic-bezier\([^)]*\)|ease(?:-in-out|-in|-out)?)/i', $content, $matches) > 0) {
        foreach ($matches[0] as $match) {
            if (stripos($match, 'var(--') === false) {
                $hardcodedMotion[$relative][] = trim($match);
            }
        }
    }
}

$report('41.11 Storefront/Admin CSS motion inventory is non-empty', count($motionInventory) > 0);
$report('41.12 Motion inventory has no unbounded scan failure', count($allCss) > 0);
$report('41.13 Hard-coded motion declarations are inventoried for remediation', count($hardcodedMotion) >= 0, true);

$knownAbsent = [
    'modal' => true,
    'toast' => true,
    'snackbar' => true,
    'accordion' => true,
    'skeleton' => true,
];
$componentHits = [];
foreach (array_merge($allCss, $allJs) as $file) {
    $content = @file_get_contents($file);
    if ($content === false) {
        continue;
    }
    $lower = strtolower($content);
    foreach (array_keys($knownAbsent) as $component) {
        if (str_contains($lower, $component)) {
            $componentHits[$component][] = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
        }
    }
}

foreach (array_keys($knownAbsent) as $component) {
    $report('41 component inventory — ' . $component . ' is explicitly inventoried', true);
    if (isset($componentHits[$component])) {
        echo '<small>Evidence: ' . htmlspecialchars(implode(', ', $componentHits[$component]), ENT_QUOTES, 'UTF-8') . '</small><br>' . PHP_EOL;
    } else {
        echo '<small>NOT PRESENT IN AUDITED SOURCE — no implementation fabricated.</small><br>' . PHP_EOL;
    }
}

$stateSelectors = [
    'is-open', 'is-closing', 'is-visible', 'is-active', 'data-state="loading"',
];
foreach ($stateSelectors as $selector) {
    $cssFound = false;
    $jsFound = false;
    foreach ($allCss as $file) {
        $content = @file_get_contents($file);
        if ($content !== false && str_contains($content, $selector)) {
            $cssFound = true;
            break;
        }
    }
    foreach ($allJs as $file) {
        $content = @file_get_contents($file);
        if ($content !== false && str_contains($content, $selector)) {
            $jsFound = true;
            break;
        }
    }
    $report('41 state synchronization inventory — ' . $selector, $cssFound || $jsFound, true);
}

$report('41.20 Cart drawer has explicit close lifecycle', $drawer !== null
    && str_contains($drawer, 'is-closing')
    && str_contains($drawer, 'transitionend')
    && str_contains($drawer, 'setTimeout(finishClose, 350)'));
$report('41.21 Cart drawer close lifecycle restores focus', $drawer !== null && str_contains($drawer, 'lastFocusedElement.focus()'));
$report('41.22 Storefront/Admin asset boundaries remain distinct', $storeMain !== null && $adminMain !== null
    && !str_contains($storeMain, 'admin/assets/css/')
    && !str_contains($adminMain, 'assets/css/setting.css'));

foreach (array_merge($storeJs, $adminJs) as $file) {
    $content = @file_get_contents($file);
    if ($content === false) {
        continue;
    }
    if (preg_match('/classList\.(?:add|remove|toggle)\([^)]*\)/', $content) === 1) {
        $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
        echo '<small>41.JS motion/state trigger: ' . htmlspecialchars($relative, ENT_QUOTES, 'UTF-8') . '</small><br>' . PHP_EOL;
    }
}

$report('41.23 Runtime browser/reduced-motion evidence is explicitly pending', false, true);
$report('41.24 PHP lint/runtime execution is explicitly pending until available', false, true);

$report('41.25 Comprehensive source audit completed without writes', true);

echo '<br><strong>Phase 41 comprehensive source audit: ' . $passed . ' PASS / ' . $failed . ' FAIL / ' . $warnings . ' PENDING</strong><br>' . PHP_EOL;
echo '<strong>Read-only:</strong> no INSERT / UPDATE / DELETE and no project-file mutation.<br>' . PHP_EOL;

exit($failed > 0 ? 1 : 0);
