<?php
declare(strict_types=1);

/**
 * Phase 47 — Smart Commerce Regression
 * Read-only static checks. No database writes or API mutations.
 * Run from project root: php test-phase-47-smart-commerce-regression.php
 */

$checks = 0;
$failures = 0;
$blocked = 0;

function phase47Check(string $label, bool $passed): void
{
    global $checks, $failures;
    $checks++;
    echo '<span style="color:' . ($passed ? 'green' : 'red') . '">[' . ($passed ? 'PASS' : 'FAIL') . ']</span> '
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
    if (!$passed) {
        $failures++;
    }
}

function phase47Blocked(string $label): void
{
    global $blocked;
    $blocked++;
    echo '<span style="color:#b7791f">[BLOCKED]</span> '
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

function phase47Read(string $path): string
{
    if (!is_file(__DIR__ . '/' . $path)) {
        return '';
    }
    $content = file_get_contents(__DIR__ . '/' . $path);
    return is_string($content) ? $content : '';
}

$main = phase47Read('assets/js/main.js');
$context = phase47Read('assets/js/modules/commerce-context.js');
$finder = phase47Read('assets/js/modules/finder.js');
$finderEngine = phase47Read('assets/js/modules/finder-engine.js');
$compatibility = phase47Read('assets/js/modules/compatibility.js');
$compatibilityEngine = phase47Read('assets/js/modules/compatibility-engine.js');
$setup = phase47Read('assets/js/modules/setup-builder.js');
$completeSetup = phase47Read('assets/js/modules/complete-your-setup.js');
$comparison = phase47Read('assets/js/modules/comparison.js');
$alternative = phase47Read('assets/js/modules/alternative.js');
$budget = phase47Read('assets/js/modules/budget-optimizer.js');
$bundle = phase47Read('assets/js/modules/smart-bundle.js');
$priceUi = phase47Read('assets/js/modules/price-stock-watch-ui.js');
$priceWatch = phase47Read('assets/js/modules/price-watch.js');
$stockWatch = phase47Read('assets/js/modules/stock-watch.js');
$recentUi = phase47Read('assets/js/modules/recent-device-ui.js');
$recentDevice = phase47Read('assets/js/modules/recent-device.js');
$routes = phase47Read('routes/api.php');
$bundleController = phase47Read('app/Controllers/SmartBundleController.php');
$bundleService = phase47Read('app/Services/SmartBundleService.php');
$budgetController = phase47Read('app/Controllers/BudgetOptimizerController.php');

$required = [
    'assets/js/main.js',
    'assets/js/modules/commerce-context.js',
    'assets/js/modules/finder.js',
    'assets/js/modules/finder-engine.js',
    'assets/js/modules/compatibility.js',
    'assets/js/modules/compatibility-engine.js',
    'assets/js/modules/setup-builder.js',
    'assets/js/modules/complete-your-setup.js',
    'assets/js/modules/comparison.js',
    'assets/js/modules/alternative.js',
    'assets/js/modules/budget-optimizer.js',
    'assets/js/modules/smart-bundle.js',
    'assets/js/modules/price-stock-watch-ui.js',
    'assets/js/modules/price-watch.js',
    'assets/js/modules/stock-watch.js',
    'assets/js/modules/recent-device-ui.js',
    'assets/js/modules/recent-device.js',
    'routes/api.php',
    'app/Controllers/SmartBundleController.php',
    'app/Services/SmartBundleService.php',
    'app/Controllers/BudgetOptimizerController.php',
];
foreach ($required as $path) {
    phase47Check("47 file exists: {$path}", is_file(__DIR__ . '/' . $path));
}

phase47Check('47.01 Main entry imports Finder and commerce modules', str_contains($main, 'import finder from "./modules/finder.js";') && str_contains($main, 'import setupBuilder from "./modules/setup-builder.js";'));
phase47Check('47.02 Main entry initializes smart commerce modules', str_contains($main, 'finder();') && str_contains($main, 'setupBuilder();') && str_contains($main, 'budgetOptimizer();') && str_contains($main, 'smartBundle();'));
phase47Check('47.03 Context normalizes versioned state', str_contains($context, 'const VERSION = 1;') && str_contains($context, 'value.version !== VERSION'));
phase47Check('47.04 Context validates return paths against protocol-relative paths', str_contains($context, 'path.startsWith("/") && !path.startsWith("//")'));
phase47Check('47.05 Context limits selected product and variant arrays', str_contains($context, 'selectedProductIds') && str_contains($context, '.slice(0, 30)'));
phase47Check('47.06 Context handles storage exceptions', str_contains($context, 'catch { return emptyContext(); }') && str_contains($context, 'catch { return false; }'));
phase47Check('47.07 Finder uses the dedicated ranking engine', str_contains($finder, 'rankFinderProducts') && str_contains($finderEngine, 'export function rankFinderProducts') || str_contains($finderEngine, 'export const rankFinderProducts'));
phase47Check('47.08 Compatibility engine constrains supported statuses', str_contains($compatibilityEngine, 'partially_compatible') && str_contains($compatibilityEngine, 'not_compatible'));
phase47Check('47.09 Compatibility requests use the registered API path', str_contains($compatibility, 'request("/compatibility/devices")') && str_contains($routes, "'/compatibility/devices'"));
phase47Check('47.10 Setup builder uses registered setup endpoints', str_contains($setup, 'request("/setup/devices")') && str_contains($routes, "'/setup/devices'"));
phase47Check('47.11 Complete Setup builds an API URL and checks response status', str_contains($completeSetup, 'new URL(apiBase') && str_contains($completeSetup, 'response.ok'));
phase47Check('47.12 Comparison persists selections under its existing key', str_contains($comparison, 'digital-store-comparison') && str_contains($comparison, 'JSON.parse'));
phase47Check('47.13 Alternative module validates product identifier', str_contains($alternative, 'productId') && str_contains($alternative, 'Number.isSafeInteger'));
phase47Check('47.14 Budget optimizer builds API front-controller URLs', str_contains($budget, '/api/index.php') && str_contains($budget, 'url.searchParams.set("path", path)'));
phase47Check('47.15 Budget optimizer uses the registered device endpoint', str_contains($budget, 'apiUrl("/compatibility/devices")') && str_contains($routes, "'/compatibility/devices'"));
phase47Check('47.16 Budget optimizer uses the registered optimize endpoint', str_contains($budget, 'apiUrl("/storefront/budget-optimizer"') && str_contains($routes, "'/storefront/budget-optimizer'"));
phase47Check('47.17 Budget optimizer rejects invalid negative/non-numeric budgets', str_contains($budget, '!Number.isFinite(Number(budget))') && str_contains($budget, 'Number(budget) < 0'));
phase47Check('47.18 Budget optimizer fails closed on malformed JSON/API failure', str_contains($budget, 'پاسخ سرور قابل پردازش نیست.') && str_contains($budget, 'payload?.success !== true'));
phase47Check('47.19 Budget optimizer disables controls while submitting', str_contains($budget, 'submitButton.disabled = busy') && str_contains($budget, 'state.busy'));
phase47Check('47.20 Smart Bundle URL separates route from query parameters', str_contains($bundle, 'url.searchParams.set("path", path)') && str_contains($bundle, 'url.searchParams.set(key, String(value))'));
phase47Check('47.21 Smart Bundle handles malformed JSON and API error envelopes', str_contains($bundle, 'INVALID_JSON') && str_contains($bundle, 'payload.success === false'));
phase47Check('47.22 Smart Bundle request uses registered endpoint', str_contains($bundle, 'api("/storefront/smart-bundles"') && str_contains($routes, "'/storefront/smart-bundles'"));
phase47Check('47.23 Smart Bundle route resolves to existing controller method', str_contains($routes, 'SmartBundleController::class') && str_contains($bundleController, 'function index()') && str_contains($bundleService, 'findBundle'));
phase47Check('47.24 Smart Bundle product selector uses existing storefront products route', str_contains($bundle, 'api("/storefront/products")') && str_contains($routes, "'/storefront/products'"));
phase47Check('47.25 Price normalization rejects non-finite values', str_contains($priceWatch, 'Number.isFinite(number)'));
phase47Check('47.26 Stock normalization distinguishes unknown state', str_contains($stockWatch, 'return STOCK_STATUS.UNKNOWN'));
phase47Check('47.27 Price/stock UI uses textContent for status messaging', str_contains($priceUi, 'element.textContent = message'));
phase47Check('47.28 Recent-device module does not invent a persistence key', str_contains($recentDevice, 'USER_STATE_CONTRACT_REQUIRED'));
phase47Check('47.29 Recent-device UI safely handles absent roots', str_contains($recentUi, 'if (!roots') && str_contains($recentUi, 'return'));
phase47Check('47.30 Budget API controller accepts the expected query contract', str_contains($budgetController, "'device_id'") && str_contains($budgetController, "'budget'"));
phase47Check('47.31 Smart Bundle route is registered only once', substr_count($routes, "'/storefront/smart-bundles'") === 1);
phase47Check('47.32 Existing storefront and setup routes remain registered', str_contains($routes, "'/storefront/products/complete-setup'") && str_contains($routes, "'/setup/devices/{deviceId}/products'"));

phase47Blocked('Recent-device persistence remains blocked until the real User State contract is established.');
phase47Blocked('Price Drop and Back-in-Stock subscribe/unsubscribe are UI/domain scaffolding only; no corresponding customer API routes/controllers were found in this audit.');

echo "<hr>Phase 47 static checks: {$checks}; failures: {$failures}; blocked dependencies: {$blocked}.<br>\n";
echo $failures === 0
    ? '<strong style="color:green">STATIC CHECKS PASS — PHASE STATUS PARTIAL; runtime/API/browser regression and blocked dependencies remain before LOCK.</strong>'
    : '<strong style="color:red">STATIC CHECKS FAIL — LOCK = NO.</strong>';
echo "<br>\n";
exit($failures === 0 ? 0 : 1);
