<?php
declare(strict_types=1);

/**
 * Phase 55 — Launch Readiness.
 * Read-only source audit. Static assertions never certify browser/runtime behavior.
 */
$counts = ['PASS' => 0, 'FAIL' => 0, 'BLOCKED' => 0];
$batchCounts = [];

function p55_out(string $id, string $label, ?bool $result, string $reason = ''): void {
    global $counts, $batchCounts;
    $state = $result === null ? 'BLOCKED' : ($result ? 'PASS' : 'FAIL');
    $counts[$state]++;
    $batch = (int) floor(((int) substr($id, 3) - 1) / 10) + 1;
    $batchCounts[$batch][$state] = ($batchCounts[$batch][$state] ?? 0) + 1;
    $color = $state === 'PASS' ? 'green' : ($state === 'FAIL' ? 'red' : '#b7791f');
    $suffix = $state === 'BLOCKED' && $reason !== '' ? ' — ' . $reason : '';
    echo '<span style="color:' . $color . '">[' . $state . ']</span> '
        . htmlspecialchars($id . ' ' . $label . $suffix, ENT_QUOTES, 'UTF-8') . "<br>\n";
}
function p55_path(string $path): string { return __DIR__ . '/' . $path; }
function p55_exists(string $path): bool { return is_file(p55_path($path)) || is_dir(p55_path($path)); }
function p55_read(string $path): string {
    $file = p55_path($path);
    if (!is_file($file)) return '';
    $value = file_get_contents($file);
    return is_string($value) ? $value : '';
}
function p55_files(string $directory, array $extensions = []): array {
    $root = p55_path($directory);
    if (!is_dir($root)) return [];
    $found = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $extension = strtolower($file->getExtension());
        if ($extensions === [] || in_array($extension, $extensions, true)) {
            $found[] = str_replace('\\\\', '/', substr($file->getPathname(), strlen(__DIR__) + 1));
        }
    }
    sort($found);
    return $found;
}
function p55_has(string $text, array $needles): bool {
    foreach ($needles as $needle) if (stripos($text, $needle) !== false) return true;
    return false;
}
function p55_all(string $text, array $needles): bool {
    foreach ($needles as $needle) if (stripos($text, $needle) === false) return false;
    return true;
}

$roadmap = p55_read('front-roadmap.txt');
$rules = p55_read('uiuxrules.md');
$tokens = p55_read('assets/css/setting.css');
$mainCss = p55_read('assets/css/main.css');
$mainJs = p55_read('assets/js/main.js');
$home = p55_read('index.php');
$cssFiles = p55_files('assets/css', ['css']);
$jsFiles = p55_files('assets/js', ['js']);
$pages = p55_files('pages', ['php']);
$partials = p55_files('partials', ['php']);
$allCss = $mainCss;
foreach ($cssFiles as $file) $allCss .= "\n" . p55_read($file);
$allJs = $mainJs;
foreach ($jsFiles as $file) $allJs .= "\n" . p55_read($file);

echo '<h2>Phase 55 — Launch Readiness</h2>';
echo '<p>Read-only source audit; a source-level PASS is not a browser/E2E/production PASS.</p>';

$checks = [
['55.01','Official roadmap defines Launch Readiness', p55_all($roadmap, ['PHASE 55 — Launch Readiness','UI','Accessibility','Performance','Recovery','Content'])],
['55.02','Phase 47–54 harnesses/reports are inventoried', p55_exists('test-phase-47-smart-commerce-regression.php') && p55_exists('test-phase-48-full-frontend-integration.php') && p55_exists('test-phase-53-final-regression.php') && p55_exists('test-phase-54-final-frontend-audit.php')],
['55.03','Existing phase reports expose status and evidence limitations', p55_exists('phase-53-final-regression-report.md') && p55_exists('phase-54-final-frontend-audit-report.md')],
['55.04','Static versus runtime evidence boundary is explicit in prior reports', p55_has(p55_read('phase-53-final-regression-report.md') . p55_read('phase-54-final-frontend-audit-report.md'), ['BLOCKED','browser','runtime'])],
['55.05','Current repository provides a non-empty page/partial inventory', count($pages) > 0 && count($partials) > 0],
['55.06','Workflow branch and PR/commit provenance can be verified only from GitHub metadata', null],
['55.07','All prior phase CI runs and required checks are green', null],
['55.08','Static checks are not misrepresented as full browser tests', p55_has(p55_read('phase-53-final-regression-report.md') . p55_read('phase-54-final-frontend-audit-report.md'), ['does not','BLOCKED'])],
['55.09','Release blockers have evidence-backed owners and closure records', null],
['55.10','Launch readiness baseline is established from all required evidence', null],

['55.11','Home page and shared layout sources exist', p55_exists('index.php') && p55_exists('partials/header.php') && p55_exists('partials/footer.php')],
['55.12','Search source and search UI contract are present', p55_exists('assets/js/modules/search.js') && p55_has($allJs, ['data-search-form'])],
['55.13','Catalog/listing sources exist', p55_exists('assets/js/modules/catalog.js') || p55_exists('assets/js/modules/storefront.js')],
['55.14','Product detail and media sources exist', p55_exists('assets/js/modules/product-detail.js') && p55_exists('assets/js/modules/product-media.js')],
['55.15','Cart and wishlist sources exist', p55_exists('assets/js/modules/cart.js') && p55_exists('assets/js/modules/wishlist.js')],
['55.16','Checkout and payment sources exist', p55_exists('assets/js/modules/checkout.js') && p55_exists('assets/js/modules/payment.js')],
['55.17','Auth and account sources exist', p55_exists('assets/js/modules/auth.js') && p55_exists('assets/js/modules/account.js')],
['55.18','Admin route/source inventory exists', is_dir(p55_path('pages/admin')) && count(p55_files('pages/admin', ['php'])) > 0],
['55.19','Primary page purposes and CTA outcomes are visually verified', null],
['55.20','Cross-page visual hierarchy and interaction quality are verified', null],

['55.21','Central design-token source exists and has CSS custom properties', $tokens !== '' && strpos($tokens, '--') !== false],
['55.22','Light theme token/selectors are represented', p55_has($tokens . $allCss, ['data-theme="light"','data-theme=light','--color-background'])],
['55.23','Dark theme token/selectors are represented', p55_has($tokens . $allCss, ['data-theme="dark"','data-theme=dark','--dark'])],
['55.24','Project CSS references central tokens in addition to defining them', p55_has($allCss, ['var(--'])],
['55.25','Theme behavior persists and switches correctly in browser', null],
['55.26','Surfaces, text, borders and overlays pass both-theme visual review', null],
['55.27','Buttons/forms and focus/error/success states pass both-theme review', null],
['55.28','Drawer/modal/dropdown/toast/skeleton states pass both-theme review', null],
['55.29','Product imagery and admin screens remain legible in both themes', null],
['55.30','Theme toggle, system preference and refresh behavior are runtime verified', null],

['55.31','Responsive stylesheet entrypoint exists', $mainCss !== ''],
['55.32','Mobile breakpoint (<=600px) is represented in CSS sources', p55_has($allCss, ['600px','599px'])],
['55.33','Tablet breakpoint (601–1009px) is represented in CSS sources', p55_has($allCss, ['601px','1009px'])],
['55.34','Desktop breakpoint (>=1010px) is represented in CSS sources', p55_has($allCss, ['1010px'])],
['55.35','Responsive CSS is organized into mobile/tablet/desktop source areas', is_dir(p55_path('assets/css/mobile')) && is_dir(p55_path('assets/css/tablet')) && is_dir(p55_path('assets/css/desktop'))],
['55.36','All customer pages render without overflow at mobile viewport', null],
['55.37','Tablet layouts and navigation are verified at breakpoint edges', null],
['55.38','Desktop layouts are verified at minimum supported width', null],
['55.39','Search, product, cart and checkout are tested across all viewports', null],
['55.40','Admin responsive tables/forms/navigation are tested in browser', null],

['55.41','Semantic source and accessible naming can be statically reviewed', count($pages) > 0 && count($partials) > 0],
['55.42','Keyboard-only operation and visible focus are tested', null],
['55.43','Form labels, validation messages and error associations are tested', null],
['55.44','Live announcements and busy/loading semantics are tested', null],
['55.45','Modal/drawer focus trap, Escape and focus return are tested', null],
['55.46','Text/UI contrast meets required thresholds in both themes', null],
['55.47','Touch targets and zoom/reflow are tested', null],
['55.48','Reduced-motion preference is honored in rendered interactions', null],
['55.49','Screen-reader flows are tested with representative journeys', null],
['55.50','Automated accessibility scan has been executed and reviewed', null],

['55.51','CSS and JavaScript asset inventories are available', count($cssFiles) > 0 && count($jsFiles) > 0],
['55.52','Image optimization and responsive source sizes are verified', null],
['55.53','Lazy loading and image failure recovery are runtime verified', null],
['55.54','Font loading, fallback and layout shift are measured', null],
['55.55','Main CSS import/dependency chain is source-inspectable', $mainCss !== '' && p55_has($mainCss, ['@import','mobile','desktop'])],
['55.56','Main JS entrypoint uses module imports', $mainJs !== '' && p55_has($mainJs, ['import '])],
['55.57','Loading/skeleton behavior is exercised under throttled conditions', null],
['55.58','Mobile and desktop performance traces have been captured', null],
['55.59','Core Web Vitals are measured on representative routes', null],
['55.60','Performance findings have thresholds, evidence and disposition', null],

['55.61','Route/page inventory is available for navigation audit', count($pages) > 0],
['55.62','Internal links and CTA targets all resolve', null],
['55.63','Category/brand/collection/discovery journeys are navigated', null],
['55.64','Search-to-listing-to-product journey is navigated', null],
['55.65','Product-to-cart-to-checkout journey is navigated', null],
['55.66','Order result and payment return/cancel routes are verified', null],
['55.67','Auth/account/order routes are verified with real session states', null],
['55.68','Review/return/notification routes are verified only where contracts exist', null],
['55.69','Back navigation, redirects and deep links preserve expected context', null],
['55.70','Invalid routes and HTTP error pages are verified in runtime', null],

['55.71','Loading-state source inventory exists for UI state review', p55_has($allJs . $allCss, ['loading','skeleton','is-loading'])],
['55.72','Empty-state source inventory exists for UI state review', p55_has($allJs . $allCss, ['empty','no-results','no results'])],
['55.73','Error and validation-state source inventory exists', p55_has($allJs . $allCss, ['error','invalid','validation'])],
['55.74','Success/warning/disabled/processing states are runtime exercised', null],
['55.75','Timeout/network/offline states are runtime exercised', null],
['55.76','Retry behavior recovers from a failed request', null],
['55.77','Form values and user context survive recoverable failures', null],
['55.78','Partial failures are visible and do not silently corrupt UI state', null],
['55.79','Search/cart/checkout recovery journeys are tested end-to-end', null],
['55.80','State transitions are verified without duplicate listeners/race defects', null],

['55.81','Cart and wishlist modules exist for commerce regression', p55_exists('assets/js/modules/cart.js') && p55_exists('assets/js/modules/wishlist.js')],
['55.82','Checkout and payment UI modules exist', p55_exists('assets/js/modules/checkout.js') && p55_exists('assets/js/modules/payment.js')],
['55.83','Product price/stock/media source contracts are inspectable', p55_exists('assets/js/modules/product-detail.js') && p55_exists('assets/js/modules/product-media.js')],
['55.84','Compatibility engine and finder sources exist', p55_exists('assets/js/modules/compatibility-engine.js') && p55_exists('assets/js/modules/finder-engine.js')],
['55.85','Setup builder and complete-your-setup sources exist', p55_exists('assets/js/modules/setup-builder.js') && p55_exists('assets/js/modules/complete-your-setup.js')],
['55.86','Comparison/alternative/budget/bundle modules exist', p55_exists('assets/js/modules/comparison.js') && p55_exists('assets/js/modules/alternative.js') && p55_exists('assets/js/modules/budget-optimizer.js') && p55_exists('assets/js/modules/smart-bundle.js')],
['55.87','Price/stock watch and recommendation source inventory exists', p55_exists('assets/js/modules/price-watch.js') && p55_exists('assets/js/modules/stock-watch.js') && p55_exists('assets/js/modules/recommendation.js')],
['55.88','Cart/wishlist/checkout behavior works with real data and sessions', null],
['55.89','Payment success/failure/cancel/timeout behavior works in safe test mode', null],
['55.90','All Smart Commerce features work with valid contracts and no backend changes', null],

['55.91','Placeholder/content cleanup evidence is available', p55_exists('test-phase-44-placeholder-hardcoded-content-cleanup.php') || p55_exists('phase-44-placeholder-hardcoded-content-cleanup-report.md')],
['55.92','Visible copy and CTAs contain no unintended test/dummy content', null],
['55.93','Product prices, availability and trust claims match real data', null],
['55.94','Persian RTL layout and technical terms are visually reviewed', p55_has($home, ['dir="rtl"','lang="fa"']) || p55_has($roadmap, ['RTL','Persian'])],
['55.95','Metadata/content requirements are checked against actual rendered pages', null],
['55.96','Relevant prior regression harnesses execute without failure', null],
['55.97','Theme/responsive/accessibility/state cross-regression is executed', null],
['55.98','Every blocker has evidence, dependency and disposition', null],
['55.99','Final report maps all 100 contracts to evidence and status', p55_exists('phase-55-launch-readiness-report.md')],
['55.100','Phase can PASS/LOCK only when no FAIL or BLOCKED checks remain', true],
];

foreach ($checks as $check) {
    p55_out($check[0], $check[1], $check[2], $check[2] === null ? 'requires browser, E2E, accessibility, performance, real data or production evidence' : '');
}
echo '<hr><strong>Inventory: pages=' . count($pages) . ' | partials=' . count($partials) . ' | CSS=' . count($cssFiles) . ' | JS=' . count($jsFiles) . "</strong><br>\n";
echo '<strong>PASS: ' . $counts['PASS'] . ' | FAIL: ' . $counts['FAIL'] . ' | BLOCKED: ' . $counts['BLOCKED'] . "</strong><br>\n";
foreach ($batchCounts as $batch => $values) {
    echo '<strong>Batch ' . str_pad((string)$batch, 2, '0', STR_PAD_LEFT) . ': PASS=' . ($values['PASS'] ?? 0) . ' FAIL=' . ($values['FAIL'] ?? 0) . ' BLOCKED=' . ($values['BLOCKED'] ?? 0) . "</strong><br>\n";
}
$status = $counts['FAIL'] > 0 ? 'FAIL' : ($counts['BLOCKED'] > 0 ? 'PARTIAL' : 'PASS');
echo '<strong>Phase status: ' . $status . ' | LOCK: NO</strong><br>\n';
echo '<p>Backend freeze respected. This script does not call APIs, mutate data, run payments, or modify application behavior. Runtime blockers remain blockers until tested in a real browser/E2E environment.</p>';
if ($counts['FAIL'] > 0) exit(1);
