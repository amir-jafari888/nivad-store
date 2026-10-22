<?php
declare(strict_types=1);

/* Canonical read-only static contract suite: Phase 31 + Phase 32. No database writes or API calls. */
$root = __DIR__;
$checks = [];
$add = static function (string $label, bool $ok) use (&$checks): void { $checks[] = [$label, $ok]; };
$read = static function (string $path) use ($root): string {
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $file = $root . DIRECTORY_SEPARATOR . $path;
    return is_file($file) ? (string) file_get_contents($file) : '';
};
$pages = [
 'pages/content/about.php','pages/content/contact.php','pages/content/faq.php',
 'pages/content/privacy.php','pages/content/terms.php','pages/content/shipping.php',
 'pages/content/returns.php','pages/content/buying-guide.php'
];
foreach ($pages as $path) {
    $content = $read($path);
    $add("File exists and is readable: {$path}", $content !== '');
    $add("UTF-8 valid: {$path}", $content !== '' && preg_match('//u', $content) === 1);
    $add("No known mojibake markers: {$path}", $content !== '' && !preg_match('/(?:ط¯ط|ط§ط|ظ…ط|ظˆط|Ã.|Â.)/u', $content));
    $add("Includes shared header/footer: {$path}", str_contains($content, "partials/header.php") && str_contains($content, "partials/footer.php"));
    $add("Has title, description and H1: {$path}", str_contains($content, '$pageTitle') && str_contains($content, '$metaDescription') && preg_match('/<h1\\b/i', $content) === 1);
}
$guide=$read('partials/buying-guide.php');
$footer=$read('partials/footer.php');
$css=$read('assets/css/content.css');
$setting=$read('assets/css/setting.css');
$mobile=$read('assets/css/mobile/mobile.css');
$tablet=$read('assets/css/tablet/tablet.css');
$desktop=$read('assets/css/desktop/desktop.css');
$header=$read('partials/header.php');
$add('Buying guide feature links target real article anchors', str_contains($guide, 'buying-guide.php#charger') && str_contains($guide, 'buying-guide.php#usb-c-thunderbolt') && str_contains($guide, 'buying-guide.php#hdmi'));
$add('View-all buying guide link targets article page', str_contains($guide, 'buying-guide.php') && !str_contains($guide, 'href="#"') && !str_contains($guide, "href='#'"));
$add('Buying guide has no placeholder hash links', !str_contains($guide, 'href="#"') && !str_contains($guide, "href='#'"));
$article=$read('pages/content/buying-guide.php');
$add('Homepage guide anchors exist in the standalone article', str_contains($article, 'id="charger"') && str_contains($article, 'id="usb-c-thunderbolt"') && str_contains($article, 'id="hdmi"'));
$add('Footer has no placeholder hash links', !str_contains($footer, 'href="#"') && !str_contains($footer, "href='#'"));
$add('Footer links content destinations', str_contains($footer, '/pages/content/faq.php') && str_contains($footer, '/pages/content/contact.php') && str_contains($footer, '/pages/content/about.php'));
$add('All CSS breakpoints import content-specific styles', str_contains($mobile, 'content-mobile.css') && str_contains($tablet, 'content-tablet.css') && str_contains($desktop, 'content-desktop.css'));
$add('Content CSS uses design tokens', str_contains($css, 'var(--color-surface)') && str_contains($css, 'var(--spacing-') && str_contains($css, 'var(--radius-') && str_contains($css, 'var(--color-border)'));
$add('Content CSS token names exist in settings', str_contains($setting, '--color-primary:') && str_contains($setting, '--color-surface:') && str_contains($setting, '--spacing-4:') && str_contains($setting, '--radius-lg:'));
$add('Content CSS has visible keyboard focus treatment', str_contains($css, ':focus-visible') && str_contains($css, 'var(--focus-ring)'));
$add('Content CSS includes reduced-motion consideration', str_contains($css, 'prefers-reduced-motion'));
$add('Shared header declares Persian RTL and UTF-8', str_contains($header, 'lang="fa"') && str_contains($header, 'dir="rtl"') && str_contains($header, 'UTF-8'));
$add('FAQ uses native accessible details/summary', str_contains($read('pages/content/faq.php'), '<details>') && str_contains($read('pages/content/faq.php'), '<summary>'));
$add('Contact page does not invent contact channels or fake form success', !preg_match('/@example\\.|555-01|data-contact-success/', $read('pages/content/contact.php')));
$add('Privacy page flags final policy review rather than unverified guarantees', str_contains($read('pages/content/privacy.php'), 'تأیید') && str_contains($read('pages/content/privacy.php'), 'تضمین'));
$add('Shipping page avoids unsupported fixed delivery promises', !preg_match('/[0-9]+\\s*(روز|ساعت)/u', $read('pages/content/shipping.php')));
$add('Returns page does not promise automatic approval/refund', str_contains($read('pages/content/returns.php'), 'به معنی تأیید') && str_contains($read('pages/content/returns.php'), 'بازپرداخت'));
$add('Buying guide covers charger, USB-C/Thunderbolt and HDMI', str_contains($read('pages/content/buying-guide.php'), 'charger') && str_contains($read('pages/content/buying-guide.php'), 'USB-C و Thunderbolt') && str_contains($read('pages/content/buying-guide.php'), 'HDMI'));
$add('Account orders and returns links point to existing page paths', str_contains($read('pages/content/contact.php'), '/pages/account/orders/index.php') && str_contains($read('pages/content/returns.php'), '/pages/account/returns/index.php'));
$add('Legacy phase test scripts were removed', !is_file($root . '/test-phase-29-authentication.php') && !is_file($root . '/test-phase-30-account-experience.php'));

/* Phase 32 commerce-context contracts: static/read-only checks only. */
$context = $read('assets/js/modules/commerce-context.js');
$searchJs = $read('assets/js/modules/search.js');
$finderJs = $read('assets/js/modules/finder.js');
$productJs = $read('assets/js/modules/product-detail.js');
$setupJs = $read('assets/js/modules/setup-builder.js');
$cartJs = $read('assets/js/modules/cart.js');
$drawerJs = $read('assets/js/modules/cart-drawer.js');
$checkoutJs = $read('assets/js/modules/checkout.js');
$mainJs = $read('assets/js/main.js');
$add('Phase 32 shared context module exists', $context !== '');
$add('Context uses tab-scoped sessionStorage', str_contains($context, 'window.sessionStorage') && !str_contains($context, 'window.localStorage'));
$add('Context schema is versioned', str_contains($context, 'const VERSION = 1') && str_contains($context, 'version: VERSION'));
$add('Malformed storage returns safe defaults', str_contains($context, 'JSON.parse') && str_contains($context, 'emptyContext()'));
$add('Storage writes fail safely', str_contains($context, 'sessionStorage.setItem') && str_contains($context, 'return false'));
$add('Return path rejects protocol-relative paths', str_contains($context, 'path.startsWith("/")') && str_contains($context, '!path.startsWith("//")'));
$add('Identifiers are positive safe integers', str_contains($context, 'Number.isSafeInteger(number)') && str_contains($context, 'number > 0'));
$add('Setup steps are allowlisted', str_contains($context, 'SETUP_STEPS.has(value.setup.step)'));
$add('Setup needs are allowlisted', str_contains($context, 'SETUP_NEEDS.has(item)'));
$add('Selected ID collections are capped', str_contains($context, '.slice(0, 30)'));
$add('Context excludes prices and personal-data fields', !preg_match('/(?:unitPrice|unit_price|email|phone|address|password|csrf)/i', $context));
$add('Context can clear all or an allowlisted section', str_contains($context, 'export const clearCommerceContext') && str_contains($context, 'includes(section)'));
$add('Search imports shared context helper', str_contains($searchJs, 'from "./commerce-context.js"'));
$add('Search records submitted query before navigation', str_contains($searchJs, 'query: value') && str_contains($searchJs, 'updateCommerceContext'));
$add('Search return path points to the actual result URL', str_contains($searchJs, 'returnPath: `${baseUrl}/search?q=${encodeURIComponent(value)}`'));
$add('Finder uses shared commerce context', str_contains($finderJs, 'from "./commerce-context.js"'));
$add('Finder restores only allowlisted intent and budget', str_contains($finderJs, 'Object.values(FINDER_INTENTS).includes(savedFinder.intent)') && str_contains($finderJs, 'Object.values(FINDER_BUDGETS).includes(savedFinder.budget)'));
$add('Finder persists choices and clears them on reset', str_contains($finderJs, 'updateCommerceContext({ finder:') && str_contains($finderJs, 'clearCommerceContext("finder")'));
$add('Setup Builder restores only a device returned by live API', str_contains($setupJs, 'state.devices.find((item) => Number(item.id) === Number(initialSetupContext.deviceId))'));
$add('Setup Builder revalidates selected product and variant IDs against live candidates', str_contains($setupJs, 'state.candidates.find((item) => Number(item.id) === Number(productId) && variantId > 0 && Number(item.variantId) === variantId && item.price !== null)'));
$add('Setup Builder uses deployment base URL and is safe on unrelated pages', str_contains($setupJs, 'window.NIVAD_BASE_URL || ""') && str_contains($setupJs, 'if (!root) {\n  return;'));
$add('Setup Builder exports an initializer compatible with main.js', str_contains($setupJs, 'const setupBuilder = () => {') && str_contains($setupJs, 'export default setupBuilder;'));
$add('Cart sync updates server item through CSRF-aware API helper without undefined variables', str_contains($cartJs, 'apiRequest(`/cart-items/${serverItem.id}`') && !str_contains($cartJs, 'csrfToken') && !str_contains($cartJs, '`/cart-items/${item.id}`'));
$add('Product Detail imports shared context helper', str_contains($productJs, 'from "./commerce-context.js"'));
$add('Product and selected variant IDs are recorded', str_contains($productJs, 'id: Number(product.id || productId)') && str_contains($productJs, 'variantId: Number(selectedVariant?.id || 0)'));
$add('Compatibility stores selected device ID, not a result assertion', str_contains($productJs, 'compatibility: { deviceId }'));
$add('Setup Builder imports shared context helper', str_contains($setupJs, 'from "./commerce-context.js"'));
$add('Setup Builder records step and device ID', str_contains($setupJs, 'step: state.currentStep') && str_contains($setupJs, 'deviceId: Number(state.device?.id || 0)'));
$add('Setup Builder records need/product/variant IDs', str_contains($setupJs, 'needs: state.needs') && str_contains($setupJs, 'selectedProductIds:') && str_contains($setupJs, 'selectedVariantIds:'));
$add('Setup Builder persists on step and selection changes', substr_count($setupJs, 'persistSetupContext();') >= 5);
$add('Main entry point initializes key commerce modules', str_contains($mainJs, 'search();') && str_contains($mainJs, 'cart();') && str_contains($mainJs, 'setupBuilder();') && str_contains($mainJs, 'productDetail();') && str_contains($mainJs, 'checkout();'));
$add('Existing cart key and update event are retained', str_contains($cartJs, 'digital-store-cart') && str_contains($cartJs, 'cart:updated'));
$add('Cart drawer uses existing shared cart API', str_contains($drawerJs, 'window.digitalStoreCart'));
$add('Checkout keeps existing API loading and summary flow', str_contains($checkoutJs, 'loadCheckout') && str_contains($checkoutJs, 'request(path)') && str_contains($checkoutJs, 'renderSummary'));

$pass=0; $fail=0;
foreach ($checks as [$label,$ok]) { echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; $ok ? $pass++ : $fail++; }
echo PHP_EOL . "PHASE 31 + 32 COMMERCE CONTEXT — SUMMARY PASS: {$pass} FAIL: {$fail} BLOCKED: 0 LOCK: " . ($fail===0 ? 'NO (browser/business review pending)' : 'NO') . PHP_EOL;
exit($fail===0 ? 0 : 1);
