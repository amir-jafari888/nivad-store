<?php
declare(strict_types=1);

/**
 * Phase 54 — Final Frontend Audit.
 * Read-only static source audit. It does not claim browser/E2E/runtime coverage.
 */
$counts = ['PASS' => 0, 'FAIL' => 0, 'BLOCKED' => 0, 'UNKNOWN' => 0];

function p54_out(string $id, string $label, ?bool $result, string $unknownReason = ''): void {
    global $counts;
    if ($result === null) {
        $state = 'BLOCKED';
        $color = '#b7791f';
    } elseif ($result === true) {
        $state = 'PASS';
        $color = 'green';
    } else {
        $state = 'FAIL';
        $color = 'red';
    }
    $counts[$state]++;
    $suffix = $state === 'BLOCKED' && $unknownReason !== '' ? ' — ' . $unknownReason : '';
    echo '<span style="color:' . $color . '">[' . $state . ']</span> '
        . htmlspecialchars($id . ' ' . $label . $suffix, ENT_QUOTES, 'UTF-8') . "<br>\n";
}
function p54_path(string $path): string { return __DIR__ . '/' . $path; }
function p54_exists(string $path): bool { return is_file(p54_path($path)) || is_dir(p54_path($path)); }
function p54_read(string $path): string {
    $full = p54_path($path);
    if (!is_file($full)) return '';
    $value = file_get_contents($full);
    return is_string($value) ? $value : '';
}
function p54_files(string $directory, array $extensions = []): array {
    $root = p54_path($directory);
    if (!is_dir($root)) return [];
    $found = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $ext = strtolower($file->getExtension());
        if ($extensions === [] || in_array($ext, $extensions, true)) $found[] = str_replace('\\', '/', substr($file->getPathname(), strlen(__DIR__) + 1));
    }
    sort($found);
    return $found;
}
function p54_has_any(string $text, array $needles): bool {
    foreach ($needles as $needle) if (stripos($text, $needle) !== false) return true;
    return false;
}

$roadmap = p54_read('front-roadmap.txt');
$rules = p54_read('uiuxrules.md');
$tokens = p54_read('assets/css/setting.css');
$mainCss = p54_read('assets/css/main.css');
$mainJs = p54_read('assets/js/main.js');
$home = p54_read('index.php');
$pages = p54_files('pages', ['php']);
$partials = p54_files('partials', ['php']);
$css = p54_files('assets/css', ['css']);
$js = p54_files('assets/js', ['js']);
$modules = p54_files('assets/js/modules', ['js']);

echo '<h2>Phase 54 — Final Frontend Audit</h2>';
echo '<p>Read-only source audit. Source-level findings are separate from runtime verification.</p>';

$checks = [
['54.01','Official roadmap defines Phase 54', strpos($roadmap, 'PHASE 54') !== false],
['54.02','Homepage entrypoint exists and is readable', p54_exists('index.php') && $home !== ''],
['54.03','Customer/admin page source inventory is non-empty', count($pages) > 0],
['54.04','Shared partial inventory is non-empty', count($partials) > 0],
['54.05','Page inventory covers expected top-level domains', is_dir(p54_path('pages/store')) && is_dir(p54_path('pages/shop')) && is_dir(p54_path('pages/account'))],
['54.06','Admin page source is present', is_dir(p54_path('pages/admin')) || p54_exists('admin/index.php')],
['54.07','Homepage references shared partials', p54_has_any($home, ['partials/', 'include', 'require'])],
['54.08','Page source inventory is available for dependency audit', count($pages) > 0 && count($partials) > 0],
['54.09','Navigation and route coverage can be statically inventoried', count($pages) > 0 && $mainJs !== ''],
['54.10','All route targets and links resolve in a browser', null],
['54.11','Homepage section source files exist', count($partials) >= 5],
['54.12','Header and footer partials exist', p54_exists('partials/header.php') && p54_exists('partials/footer.php')],
['54.13','Search module exists', p54_exists('assets/js/modules/search.js')],
['54.14','Product presentation module/source exists', p54_exists('assets/js/modules/product-detail.js') || p54_exists('partials/product.php')],
['54.15','Product media module exists', p54_exists('assets/js/modules/product-media.js')],
['54.16','Cart and wishlist modules exist', p54_exists('assets/js/modules/cart.js') && p54_exists('assets/js/modules/wishlist.js')],
['54.17','Checkout and payment modules exist', p54_exists('assets/js/modules/checkout.js') && p54_exists('assets/js/modules/payment.js')],
['54.18','Authentication/account modules exist', p54_exists('assets/js/modules/auth.js') && p54_exists('assets/js/modules/account.js')],
['54.19','Smart Commerce module inventory exists', p54_exists('assets/js/modules/finder.js') && p54_exists('assets/js/modules/setup-builder.js') && p54_exists('assets/js/modules/comparison.js')],
['54.20','Admin visual and interaction behavior is verified in browser', null],
['54.21','Loading and skeleton states are functionally exercised', null],
['54.22','Empty states are functionally exercised', null],
['54.23','Error, validation and retry states are functionally exercised', null],
['54.24','Success, warning, disabled and processing states are functionally exercised', null],
['54.25','Light theme selectors/tokens exist', p54_has_any($tokens, ['data-theme="light"', '[data-theme=light]', '--color-background', '--background'])],
['54.26','Dark theme selectors/tokens exist', p54_has_any($tokens, ['data-theme="dark"', '[data-theme=dark]', '--color-background-dark', '--dark'])],
['54.27','Mobile breakpoint is represented in stylesheet sources', p54_has_any($mainCss . "\n" . implode("\n", array_map('p54_read', $css)), ['600px', '599px'])],
['54.28','Tablet breakpoint is represented in stylesheet sources', p54_has_any($mainCss . "\n" . implode("\n", array_map('p54_read', $css)), ['601px', '1009px', '1000px'])],
['54.29','Desktop breakpoint is represented in stylesheet sources', p54_has_any($mainCss . "\n" . implode("\n", array_map('p54_read', $css)), ['1010px', 'min-width: 1010px'])],
['54.30','Keyboard/screen-reader/contrast/reduced-motion checks are executed', null],
['54.31','Central token stylesheet exists and contains custom properties', $tokens !== '' && strpos($tokens, '--') !== false],
['54.32','Main JS entrypoint imports modular code', $mainJs !== '' && p54_has_any($mainJs, ['import ', 'import('])],
['54.33','DOM hooks and event contracts are behaviorally verified', null],
['54.34','Cross-page cart, wishlist and device state consistency is verified', null],
['54.35','Navigation, browser back and deep-link behavior are verified', null],
['54.36','Shared UI consistency is assessed with rendered screenshots', null],
['54.37','Console errors and failed network resources are checked in browser', null],
['54.38','Previous phase harnesses are present for regression traceability', p54_exists('test-phase-47-smart-commerce-regression.php') && p54_exists('test-phase-48-full-frontend-integration.php') && p54_exists('test-phase-51-final-ui-ux-gate.php') && p54_exists('test-phase-53-final-regression.php')],
['54.39','Evidence report and dependency register are created', p54_exists('phase-54-final-frontend-audit-report.md')],
['54.40','Release lock is prevented while runtime checks remain blocked', true],
];
foreach ($checks as $check) p54_out($check[0], $check[1], $check[2], $check[2] === null ? 'requires real browser/runtime evidence' : '');

echo '<hr><strong>Source inventory: pages=' . count($pages) . ' | partials=' . count($partials) . ' | CSS=' . count($css) . ' | JS=' . count($js) . ' | modules=' . count($modules) . "</strong><br>\n";
echo '<strong>PASS: ' . $counts['PASS'] . ' | FAIL: ' . $counts['FAIL'] . ' | BLOCKED: ' . $counts['BLOCKED'] . "</strong><br>\n";
$status = $counts['FAIL'] > 0 ? 'FAIL' : ($counts['BLOCKED'] > 0 ? 'PARTIAL' : 'PASS');
echo '<strong>Phase status: ' . $status . ' | LOCK: NO</strong><br>\n';
echo '<p>Static source checks do not prove rendered UX, route reachability, payment behavior, accessibility, or responsive behavior. No application files, backend logic, routes, or schema are modified by this audit.</p>';
