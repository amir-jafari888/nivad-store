<?php
declare(strict_types=1);

/**
 * Phase 48 — Full Frontend Integration Audit
 * Read-only static integration checks. No database writes or network calls.
 * Run from project root: php test-phase-48-full-frontend-integration.php
 */

$checks = 0;
$failures = 0;
$blocked = 0;

function phase48Check(string $label, bool $passed): void
{
    global $checks, $failures;
    $checks++;
    echo '<span style="color:' . ($passed ? 'green' : 'red') . '">[' . ($passed ? 'PASS' : 'FAIL') . ']</span> '
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
    if (!$passed) {
        $failures++;
    }
}

function phase48Blocked(string $label): void
{
    global $blocked;
    $blocked++;
    echo '<span style="color:#b7791f">[BLOCKED]</span> '
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

function phase48Read(string $path): string
{
    $full = __DIR__ . '/' . $path;
    if (!is_file($full)) {
        return '';
    }
    $content = file_get_contents($full);
    return is_string($content) ? $content : '';
}

function phase48Exists(string $path): bool
{
    return is_file(__DIR__ . '/' . $path);
}

function phase48Has(string $path, string $needle): bool
{
    return str_contains(phase48Read($path), $needle);
}

$mainCss = phase48Read('assets/css/main.css');
$settingCss = phase48Read('assets/css/setting.css');
$mobileCss = phase48Read('assets/css/mobile/mobile.css');
$tabletCss = phase48Read('assets/css/tablet/tablet.css');
$desktopCss = phase48Read('assets/css/desktop/desktop.css');
$mainJs = phase48Read('assets/js/main.js');
$header = phase48Read('partials/header.php');
$footer = phase48Read('partials/footer.php');
$apiFront = phase48Read('api/index.php');
$routes = phase48Read('routes/api.php');
$context = phase48Read('assets/js/modules/commerce-context.js');
$cart = phase48Read('assets/js/modules/cart.js');
$wishlist = phase48Read('assets/js/modules/wishlist.js');

$requiredFiles = [
    'index.php', 'partials/header.php', 'partials/footer.php',
    'assets/css/main.css', 'assets/css/setting.css',
    'assets/css/mobile/mobile.css', 'assets/css/tablet/tablet.css',
    'assets/css/desktop/desktop.css', 'assets/js/main.js',
    'api/index.php', 'routes/api.php',
    'assets/js/modules/header.js', 'assets/js/modules/search.js',
    'assets/js/modules/cart.js', 'assets/js/modules/cart-drawer.js',
    'assets/js/modules/wishlist.js', 'assets/js/modules/commerce-context.js',
    'assets/js/modules/finder.js', 'assets/js/modules/compatibility.js',
    'assets/js/modules/setup-builder.js', 'assets/js/modules/complete-your-setup.js',
    'assets/js/modules/comparison.js', 'assets/js/modules/alternative.js',
    'assets/js/modules/budget-optimizer.js', 'assets/js/modules/smart-bundle.js',
    'assets/js/modules/price-stock-watch-ui.js', 'assets/js/modules/recent-device-ui.js',
    'assets/js/modules/checkout.js', 'assets/js/modules/payment.js',
    'assets/js/modules/account.js', 'assets/js/modules/auth.js',
];
foreach ($requiredFiles as $path) {
    phase48Check("Integration source exists: {$path}", phase48Exists($path));
}

/* Batch 01 — CSS dependency and asset integration (48.01–48.10). */
phase48Check('48.01 Main stylesheet imports the Design System', str_contains($mainCss, './setting.css'));
phase48Check('48.02 Design System declares root tokens', str_contains($settingCss, ':root') && str_contains($settingCss, '--color-primary'));
phase48Check('48.03 Mobile breakpoint is capped at 600px', str_contains($mainCss, 'max-width: 600px'));
phase48Check('48.04 Tablet breakpoint spans 601–1009px', str_contains($mainCss, 'min-width: 601px') && str_contains($mainCss, 'max-width: 1009px'));
phase48Check('48.05 Desktop breakpoint starts at 1010px', str_contains($mainCss, 'min-width: 1010px'));
phase48Check('48.06 Mobile Smart Commerce styles use sibling-relative paths', !str_contains($mobileCss, './mobile/budget-optimizer-mobile.css') && !str_contains($mobileCss, './mobile/price-stock-watch-mobile.css') && !str_contains($mobileCss, './mobile/recent-device-mobile.css'));
phase48Check('48.07 Tablet Smart Commerce styles use sibling-relative paths', !str_contains($tabletCss, './tablet/budget-optimizer-tablet.css') && !str_contains($tabletCss, './tablet/price-stock-watch-tablet.css') && !str_contains($tabletCss, './tablet/recent-device-tablet.css'));
phase48Check('48.08 Desktop Smart Commerce styles use sibling-relative paths', !str_contains($desktopCss, './desktop/budget-optimizer-desktop.css') && !str_contains($desktopCss, './desktop/price-stock-watch-desktop.css') && !str_contains($desktopCss, './desktop/recent-device-desktop.css'));
phase48Check('48.09 Mobile stylesheet references the Budget Optimizer stylesheet', str_contains($mobileCss, 'budget-optimizer-mobile.css'));
phase48Check('48.10 Tablet and desktop stylesheets reference their Budget Optimizer styles', str_contains($tabletCss, 'budget-optimizer-tablet.css') && str_contains($desktopCss, 'budget-optimizer-desktop.css'));

/* Resolve every local CSS @import against the importing stylesheet. */
$cssFiles = [];
$cssRoot = __DIR__ . '/assets/css';
if (is_dir($cssRoot)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cssRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'css') {
            $cssFiles[] = substr($file->getPathname(), strlen(__DIR__) + 1);
        }
    }
}
$brokenCssImports = [];
foreach ($cssFiles as $path) {
    $source = phase48Read($path);
    if (preg_match_all('/@import\s+(?:url\()?\s*["\']?([^"\')\s;]+)["\']?\s*\)?[^;]*;/i', $source, $matches)) {
        foreach ($matches[1] as $target) {
            if (preg_match('/^(?:https?:|data:|\/\/)/i', $target)) {
                continue;
            }
            $resolved = realpath(__DIR__ . '/' . dirname($path) . '/' . $target);
            if ($resolved === false || !is_file($resolved)) {
                $brokenCssImports[] = $path . ' -> ' . $target;
            }
        }
    }
}
phase48Check('CSS import resolver finds no missing local @import targets', count($brokenCssImports) === 0);
if ($brokenCssImports !== []) {
    foreach (array_slice($brokenCssImports, 0, 30) as $broken) {
        phase48Check('Broken CSS import: ' . $broken, false);
    }
}

/* Batch 02 — JavaScript module integration (48.11–48.20). */
phase48Check('48.11 Main JS imports Header module', str_contains($mainJs, 'import header from "./modules/header.js";'));
phase48Check('48.12 Main JS imports Search module', str_contains($mainJs, 'import search from "./modules/search.js";'));
phase48Check('48.13 Main JS initializes Cart and Drawer', str_contains($mainJs, 'cart();') && str_contains($mainJs, 'cartDrawer();'));
phase48Check('48.14 Main JS initializes Wishlist', str_contains($mainJs, 'wishlist();'));
phase48Check('48.15 Main JS initializes Smart Finder and Setup Builder', str_contains($mainJs, 'finder();') && str_contains($mainJs, 'setupBuilder();'));
phase48Check('48.16 Main JS initializes Comparison and Alternative', str_contains($mainJs, 'comparison();') && str_contains($mainJs, 'alternative();'));
phase48Check('48.17 Main JS initializes Budget Optimizer and Smart Bundle', str_contains($mainJs, 'budgetOptimizer();') && str_contains($mainJs, 'smartBundle();'));
phase48Check('48.18 Main JS initializes Price/Stock and Recent Device UI', str_contains($mainJs, 'initializePriceStockWatch();') && str_contains($mainJs, 'initializeRecentDevice();'));
phase48Check('48.19 Main JS initializes checkout and payment modules', str_contains($mainJs, 'checkout();') && str_contains($mainJs, 'payment();'));
phase48Check('48.20 Main JS contains no duplicate static import lines', count(array_unique(array_filter(explode("\n", $mainJs), static fn($line) => str_starts_with(trim($line), 'import ')))) === count(array_filter(explode("\n", $mainJs), static fn($line) => str_starts_with(trim($line), 'import '))));

$jsFiles = [];
$jsRoot = __DIR__ . '/assets/js';
if (is_dir($jsRoot)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($jsRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'js') {
            $jsFiles[] = substr($file->getPathname(), strlen(__DIR__) + 1);
        }
    }
}
$brokenJsImports = [];
foreach ($jsFiles as $path) {
    $source = phase48Read($path);
    if (preg_match_all('/\bimport\s+(?:[^;]*?\s+from\s*)?["\']([^"\']+)["\']\s*;?/m', $source, $matches)) {
        foreach ($matches[1] as $target) {
            if (!str_starts_with($target, '.')) {
                continue;
            }
            $resolved = realpath(__DIR__ . '/' . dirname($path) . '/' . $target);
            if ($resolved === false || !is_file($resolved)) {
                $brokenJsImports[] = $path . ' -> ' . $target;
            }
        }
    }
}
phase48Check('JavaScript import resolver finds no missing relative module targets', count($brokenJsImports) === 0);
if ($brokenJsImports !== []) {
    foreach (array_slice($brokenJsImports, 0, 30) as $broken) {
        phase48Check('Broken JS import: ' . $broken, false);
    }
}

/* Batch 03 — HTML, DOM and component contracts (48.21–48.30). */
phase48Check('48.21 Homepage entry exists and composes shared header', phase48Exists('index.php') && str_contains(phase48Read('index.php'), 'partials/header.php'));
phase48Check('48.22 Header defines mobile-menu controls', str_contains($header, 'data-menu-toggle') && str_contains($header, 'data-mobile-menu'));
phase48Check('48.23 Header defines theme toggle controls', str_contains($header, 'data-theme-toggle'));
phase48Check('48.24 Header exposes wishlist and cart controls', str_contains($header, 'data-cart-toggle') && str_contains($header, 'wishlist-count'));
phase48Check('48.25 Footer defines navigation destinations', str_contains($footer, 'footer-links') && str_contains($footer, 'footerCompatibilityUrl'));
phase48Check('48.26 Cart Drawer module guards required DOM elements', str_contains(phase48Read('assets/js/modules/cart-drawer.js'), 'if (') && str_contains(phase48Read('assets/js/modules/cart-drawer.js'), 'data-cart-drawer'));
phase48Check('48.27 Wishlist module preserves existing storage contract', str_contains($wishlist, 'digital-store-wishlist'));
phase48Check('48.28 Search module uses the existing search form contract', str_contains(phase48Read('assets/js/modules/search.js'), 'data-search-form'));
phase48Check('48.29 Header uses aria-expanded state for menu controls', str_contains(phase48Read('assets/js/modules/header.js'), 'aria-expanded'));
phase48Check('48.30 Header declares Persian RTL document direction', str_contains($header, 'lang="fa" dir="rtl"'));

/* Batch 04 — Route, URL and API integration (48.31–48.40). */
phase48Check('48.31 API front controller boots the application', str_contains($apiFront, 'Bootstrap::boot()'));
phase48Check('48.32 API front controller loads the registered route file', str_contains($apiFront, "routes/api.php"));
phase48Check('48.33 API front controller validates route registrar', str_contains($apiFront, 'is_callable($registerRoutes)'));
phase48Check('48.34 API front controller normalizes the path', str_contains($apiFront, "ltrim(" . '$path' . ", '/')"));
phase48Check('48.35 API front controller preserves query parameters', str_contains($apiFront, 'http_build_query($query)'));
phase48Check('48.36 API routes contain compatibility devices endpoint', str_contains($routes, "'/compatibility/devices'"));
phase48Check('48.37 API routes contain setup devices endpoint', str_contains($routes, "'/setup/devices'"));
phase48Check('48.38 API routes contain Budget Optimizer endpoint', str_contains($routes, "'/storefront/budget-optimizer'"));
phase48Check('48.39 API routes contain Smart Bundle endpoint', str_contains($routes, "'/storefront/smart-bundles'"));
phase48Check('48.40 Budget Optimizer constructs API URL with URLSearchParams', str_contains(phase48Read('assets/js/modules/budget-optimizer.js'), 'searchParams.set("path", path)'));

/* Batch 05 — Shared state and commerce integration (48.41–48.50). */
phase48Check('48.41 Commerce Context declares a versioned storage key', str_contains($context, 'nivad-commerce-context') && str_contains($context, 'const VERSION = 1'));
phase48Check('48.42 Commerce Context rejects unknown schema versions', str_contains($context, 'value.version !== VERSION'));
phase48Check('48.43 Commerce Context validates internal return paths', str_contains($context, '!path.startsWith("//")'));
phase48Check('48.44 Commerce Context constrains positive identifiers', str_contains($context, 'Number.isSafeInteger(number)') && str_contains($context, 'number > 0'));
phase48Check('48.45 Cart module validates normalized item price and quantity', str_contains($cart, 'Number.isFinite(price)') && str_contains($cart, 'quantity < 1'));
phase48Check('48.46 Cart module uses stable product/variant identity', str_contains($cart, 'variant:') && str_contains($cart, 'product:'));
phase48Check('48.47 Wishlist module deduplicates stored product identifiers', str_contains($wishlist, 'new Set('));
phase48Check('48.48 Wishlist module publishes update events', str_contains($wishlist, 'wishlist:updated'));
phase48Check('48.49 Cart Drawer consumes the shared cart API', str_contains(phase48Read('assets/js/modules/cart-drawer.js'), 'window.digitalStoreCart'));
phase48Check('48.50 Context persistence contract remains explicitly versioned', str_contains($context, 'STORAGE_KEY') && str_contains($context, 'VERSION'));

/* Batch 06 — Smart Commerce cross-feature integration (48.51–48.60). */
phase48Check('48.51 Finder module and ranking engine exist', phase48Exists('assets/js/modules/finder.js') && phase48Exists('assets/js/modules/finder-engine.js'));
phase48Check('48.52 Compatibility module and engine exist', phase48Exists('assets/js/modules/compatibility.js') && phase48Exists('assets/js/modules/compatibility-engine.js'));
phase48Check('48.53 Setup Builder module exists and is initialized', phase48Exists('assets/js/modules/setup-builder.js') && str_contains($mainJs, 'setupBuilder();'));
phase48Check('48.54 Complete Your Setup module is initialized', phase48Exists('assets/js/modules/complete-your-setup.js') && str_contains($mainJs, 'completeYourSetup();'));
phase48Check('48.55 Comparison module is initialized', phase48Exists('assets/js/modules/comparison.js') && str_contains($mainJs, 'comparison();'));
phase48Check('48.56 Alternative module is initialized', phase48Exists('assets/js/modules/alternative.js') && str_contains($mainJs, 'alternative();'));
phase48Check('48.57 Budget Optimizer module is initialized', phase48Exists('assets/js/modules/budget-optimizer.js') && str_contains($mainJs, 'budgetOptimizer();'));
phase48Check('48.58 Smart Bundle module is initialized', phase48Exists('assets/js/modules/smart-bundle.js') && str_contains($mainJs, 'smartBundle();'));
phase48Check('48.59 Price/Stock UI remains separated from domain normalizers', str_contains(phase48Read('assets/js/modules/price-stock-watch-ui.js'), './price-watch.js') && str_contains(phase48Read('assets/js/modules/price-stock-watch-ui.js'), './stock-watch.js'));
phase48Check('48.60 Recently selected device does not invent missing User State', str_contains(phase48Read('assets/js/modules/recent-device.js'), 'USER_STATE_CONTRACT_REQUIRED'));

/* Batch 07 — Theme, responsive and accessibility integration (48.61–48.70). */
phase48Check('48.61 Header exposes theme toggle hooks', str_contains($header, 'data-theme-toggle'));
phase48Check('48.62 Design System contains a dark-theme selector', str_contains($settingCss, '[data-theme="dark"]'));
phase48Check('48.63 Design System defines default root tokens and a dark-theme override', str_contains($settingCss, ':root') && str_contains($settingCss, '[data-theme="dark"]'));
phase48Check('48.64 Design System contains focus-visible styling', str_contains($settingCss, ':focus-visible'));
phase48Check('48.65 Mobile CSS entry imports feature-specific files', str_contains($mobileCss, 'header-mobile.css') && str_contains($mobileCss, 'product-detail-mobile.css'));
phase48Check('48.66 Tablet CSS entry imports feature-specific files', str_contains($tabletCss, 'header-tablet.css') && str_contains($tabletCss, 'product-detail-tablet.css'));
phase48Check('48.67 Desktop CSS entry imports feature-specific files', str_contains($desktopCss, 'header-desktop.css') && str_contains($desktopCss, 'product-detail-desktop.css'));
phase48Check('48.68 Header menu toggles aria-hidden state', str_contains(phase48Read('assets/js/modules/header.js'), 'aria-hidden'));
phase48Check('48.69 Header menu buttons expose aria-expanded state', str_contains(phase48Read('assets/js/modules/header.js'), 'setAttribute("aria-expanded"'));
phase48Check('48.70 CSS imports are constrained to existing local files', count($brokenCssImports) === 0);

/* Batch 08 — cross-page regression and integration gate (48.71–48.80). */
phase48Check('48.71 Homepage root exists', phase48Exists('index.php'));
phase48Check('48.72 Search module is imported by the entry point', str_contains($mainJs, 'import search from "./modules/search.js";'));
phase48Check('48.73 Product detail module is imported and initialized', str_contains($mainJs, 'import productDetail from "./modules/product-detail.js";') && str_contains($mainJs, 'productDetail();'));
phase48Check('48.74 Cart module is imported and initialized', str_contains($mainJs, 'import cart from "./modules/cart.js";') && str_contains($mainJs, 'cart();'));
phase48Check('48.75 Checkout module is imported and initialized', str_contains($mainJs, 'import checkout from "./modules/checkout.js";') && str_contains($mainJs, 'checkout();'));
phase48Check('48.76 Payment module is imported and initialized', str_contains($mainJs, 'import payment from "./modules/payment.js";') && str_contains($mainJs, 'payment();'));
phase48Check('48.77 Authentication module is imported and initialized', str_contains($mainJs, 'import auth from "./modules/auth.js";') && str_contains($mainJs, 'auth();'));
phase48Check('48.78 Account module is imported and initialized', str_contains($mainJs, 'import account from "./modules/account.js";') && str_contains($mainJs, 'account();'));
phase48Check('48.79 Regression script performs no database operations', !str_contains(phase48Read('test-phase-48-full-frontend-integration.php'), 'PDO::') && !str_contains(phase48Read('test-phase-48-full-frontend-integration.php'), 'mysqli_') && !str_contains(phase48Read('test-phase-48-full-frontend-integration.php'), '$pdo->'));
phase48Check('48.80 Integration gate records actual unresolved runtime dependencies', str_contains(phase48Read('assets/js/modules/recent-device.js'), 'USER_STATE_CONTRACT_REQUIRED') && !str_contains($routes, "'/price-watch/subscribe'"));

phase48Blocked('Browser-based visual/responsive/accessibility regression requires a running browser and application environment.');
phase48Blocked('Authenticated API, database-backed cart/checkout/payment and end-to-end customer journeys are not executed by this read-only static test.');
phase48Blocked('Price Drop / Back-in-Stock subscription remains blocked until a real backend contract is confirmed.');
phase48Blocked('Recently Selected Device persistence remains blocked until the actual User State contract is confirmed.');

echo '<br><strong>Phase 48 result: ' . $checks . ' checks, ' . $failures . ' failures, ' . $blocked . ' blocked dependencies.</strong><br>';
echo '<strong>LOCK=' . (($failures === 0 && $blocked === 0) ? 'YES' : 'NO') . '</strong><br>';
exit($failures === 0 ? 0 : 1);
