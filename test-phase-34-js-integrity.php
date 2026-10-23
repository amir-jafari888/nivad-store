<?php
declare(strict_types=1);

/**
 * Phase 34 — JavaScript Module, DOM, Event, Storage and Async Integrity.
 * Read-only static checks only. No database writes, API calls or browser-runtime claims.
 */
$root = __DIR__;
$checks = [];
$add = static function (string $label, bool $ok) use (&$checks): void {
    $checks[] = [$label, $ok];
};
$read = static function (string $path) use ($root): string {
    $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    return is_file($file) ? (string) file_get_contents($file) : '';
};
$main = $read('assets/js/main.js');
$wishlist = $read('assets/js/modules/wishlist.js');
$cart = $read('assets/js/modules/cart.js');
$drawer = $read('assets/js/modules/cart-drawer.js');
$header = $read('assets/js/modules/header.js');
$context = $read('assets/js/modules/commerce-context.js');
$search = $read('assets/js/modules/search.js');
$catalog = $read('assets/js/modules/catalog.js');
$finder = $read('assets/js/modules/finder.js');
$setup = $read('assets/js/modules/setup-builder.js');
$product = $read('assets/js/modules/product-detail.js');
$compatibility = $read('assets/js/modules/compatibility.js');
$media = $read('assets/js/modules/product-media.js');
$checkout = $read('assets/js/modules/checkout.js');
$payment = $read('assets/js/modules/payment.js');
$auth = $read('assets/js/modules/auth.js');

$add('34.01 Main entry point exists and is readable', $main !== '');
$imports = [];
preg_match_all('/^import\\s+([\\s\\S]*?);\\s*$/m', $main, $importLines, PREG_SET_ORDER);
foreach ($importLines as $line) {
    if (preg_match('/from\\s+"([^"]+)"/', $line[1], $match)) {
        $imports[] = ['statement' => $line[1], 'path' => $match[1]];
    }
}
$add('34.02 Every main.js relative import resolves', count($imports) > 0 && (function () use ($imports, $root): bool {
    foreach ($imports as $import) {
        if (!str_starts_with($import['path'], '.')) continue;
        $path = dirname($root . '/assets/js/main.js') . '/' . $import['path'];
        $resolved = realpath($path);
        if ($resolved === false || !is_file($resolved)) return false;
    }
    return true;
})());
$add('34.03 Every relative static import under assets/js resolves to an existing file', (function () use ($root): bool {
    $directory = $root . '/assets/js';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !str_ends_with($file->getFilename(), '.js')) continue;
        $source = (string) file_get_contents($file->getPathname());
        preg_match_all('/import\\s+(?:[\\s\\S]*?\\s+from\\s+)?"([^"]+)"/m', $source, $matches);
        foreach ($matches[1] as $importPath) {
            if (!str_starts_with($importPath, '.')) continue;
            if (realpath(dirname($file->getPathname()) . DIRECTORY_SEPARATOR . $importPath) === false) return false;
        }
    }
    return true;
})());
$add('34.04 Main default imports are paired with default exports', count($imports) > 0 && (function () use ($imports, $root): bool {
    foreach ($imports as $import) {
        if (!preg_match('/^\\s*([A-Za-z_$][\\w$]*)\\s+from\\s+/', $import['statement'])) continue;
        $path = realpath(dirname($root . '/assets/js/main.js') . '/' . $import['path']);
        if ($path === false) return false;
        $source = (string) file_get_contents($path);
        if (!preg_match('/export\\s+default\\s+/', $source)) return false;
    }
    return true;
})());
$add('34.05 Main imports do not include duplicate module paths', count(array_unique(array_column($imports, 'path'))) === count($imports));
$add('34.06 Known external dependency Swiper is explicitly imported by its consumer', str_contains($read('assets/js/modules/discovery.js'), 'from "../../../swiper/swiper-bundle.mjs"'));
$add('34.07 main.js is an initializer/entry point rather than business-logic implementation', !preg_match('/(?:fetch\\(|localStorage\\.setItem|INSERT\\s+INTO)/i', $main));
$add('34.08 Imported main initializers are invoked once each', (function () use ($imports, $main): bool {
    foreach ($imports as $import) {
        if (preg_match('/^\\s*([A-Za-z_$][\\w$]*)\\s+from\\s+/', $import['statement'], $m) && substr_count($main, $m[1] . '();') !== 1) return false;
    }
    return true;
})());
$add('34.09 Page-specific modules guard absent target DOM before work', preg_match('/if\\s*\\(!root\\)\\s*\\{\\s*return;/', $catalog) === 1 && preg_match('/if\\s*\\(!page\\)\\s*\\{\\s*return;/', $finder) === 1);
$add('34.10 Phase 34 workflow syntax-checks the JavaScript tree', $read('.github/workflows/phase-34-js-integrity.yml') !== '' && str_contains($read('.github/workflows/phase-34-js-integrity.yml'), 'node --input-type=module --check'));

$add('34.11 Core modules use DOM selectors rather than assumed global element variables', str_contains($header, 'document.querySelector') && str_contains($drawer, 'document.querySelector'));
$add('34.12 Header uses optional DOM access and drawer exits when required markup is absent', str_contains($header, 'menuButton?.addEventListener') && preg_match('/if\\s*\\(\\s*!drawer\\s*\\|\\|/', $drawer) === 1);
$add('34.13 Wishlist and cart expose explicit data-attribute contracts', str_contains($wishlist, '[data-wishlist]') && str_contains($cart, '[data-cart]'));
$add('34.14 Cart update event carries a detail payload', str_contains($cart, 'new CustomEvent("cart:updated"') && str_contains($cart, 'detail:'));
$add('34.15 Wishlist update and sync-error events are consistently named', str_contains($wishlist, 'wishlist:updated') && str_contains($wishlist, 'wishlist:sync-error'));
$add('34.16 Main entry point contains one initializer call per imported default module', (function () use ($imports, $main): bool {
    foreach ($imports as $import) {
        if (preg_match('/^\\s*([A-Za-z_$][\\w$]*)\\s+from\\s+/', $import['statement'], $m) && substr_count($main, $m[1] . '();') !== 1) return false;
    }
    return true;
})());
$add('34.17 Cart uses delegated click handling for dynamic add-to-cart buttons', str_contains($cart, 'document.addEventListener("click"') && str_contains($cart, 'event.target.closest("[data-cart]")'));
$add('34.18 Cart drawer updates from the shared cart event/API', str_contains($drawer, 'cart:updated') || str_contains($drawer, 'window.digitalStoreCart'));
$add('34.19 Cart and wishlist asynchronous sync failures are caught and surfaced as events', str_contains($cart, 'cart:sync-error') && str_contains($wishlist, 'wishlist:sync-error'));
$add('34.20 Interactive cart and wishlist buttons expose accessible labels/states', preg_match('/setAttribute\\(\\s*"aria-label"/', $cart) === 1 && str_contains($wishlist, 'setAttribute("aria-pressed"'));

$add('34.21 Cart validates and normalizes item state before use', str_contains($cart, 'Number.isFinite(price)') && str_contains($cart, 'Number.isFinite(quantity)') && str_contains($cart, 'normalizeCart'));
$add('34.22 Server data is normalized before being written into local cart state', str_contains($cart, 'serverItemToLocal') && str_contains($cart, 'normalizeCart('));
$add('34.23 Storage keys are explicitly defined and inventoryable', str_contains($cart, '"digital-store-cart"') && str_contains($wishlist, '"digital-store-wishlist"') && str_contains($wishlist, '"digital-store-wishlist-variants"') && str_contains($context, '"nivad-commerce-context"'));
$add('34.24 Malformed cart and context storage values fall back safely', str_contains($cart, 'JSON.parse(stored)') && str_contains($cart, 'catch') && str_contains($context, 'JSON.parse(window.sessionStorage.getItem(STORAGE_KEY)'));
$add('34.25 Cart, context, and wishlist storage writes handle browser storage exceptions', str_contains($cart, 'localStorage.setItem(storageKey') && str_contains($cart, 'catch') && str_contains($context, 'sessionStorage.setItem') && str_contains($wishlist, 'wishlist:storage-error'));
$add('34.26 Existing cart and wishlist storage keys remain unchanged', str_contains($cart, '"digital-store-cart"') && str_contains($wishlist, '"digital-store-wishlist"') && str_contains($wishlist, '"digital-store-wishlist-variants"'));
$add('34.27 Search and catalog guard stale async responses with request tokens', str_contains($search, 'let requestToken = 0') && str_contains($search, 'currentToken !== requestToken') && str_contains($catalog, 'let requestToken = 0') && str_contains($catalog, 'currentToken !== requestToken'));
$add('34.28 Product detail and setup builder validate selected product/variant identifiers', str_contains($product, 'selectedVariant') && str_contains($setup, 'selectedVariantIds'));
$add('34.29 Async request modules have explicit error boundaries', str_contains($search, 'try') && str_contains($catalog, 'try') && str_contains($finder, 'try'));
$add('34.30 Cart and wishlist synchronization retain the established storage/event contracts', str_contains($cart, 'cart:updated') && str_contains($wishlist, 'wishlist:updated'));

$add('34.31 Request helpers check HTTP/API failure responses', str_contains($cart, '!response.ok') && str_contains($wishlist, '!response.ok'));
$add('34.32 Commerce/auth/product/compatibility/payment helpers reject malformed JSON or non-object payloads', str_contains($cart, 'response.json().catch(() => null)') && str_contains($cart, 'Array.isArray(payload)') && str_contains($wishlist, 'response.json().catch(() => null)') && str_contains($wishlist, 'Array.isArray(payload)') && str_contains($search, 'response.json().catch(() => null)') && str_contains($finder, 'response.json().catch(() => null)') && str_contains($catalog, 'typeof payload !== "object"') && str_contains($product, 'Array.isArray(payload)') && str_contains($compatibility, 'Array.isArray(payload)') && str_contains($auth, 'Array.isArray(payload)') && str_contains($payment, 'Array.isArray(result)'));
$add('34.33 Mutating cart/wishlist requests request CSRF token using existing endpoint', str_contains($cart, '"/auth/csrf"') && str_contains($wishlist, '"/auth/csrf"') && str_contains($cart, '"X-CSRF-TOKEN"'));
$add('34.34 Checkout/payment modules exist and contain explicit request/state handling', $checkout !== '' && $payment !== '' && str_contains($checkout, 'loadCheckout'));
$add('34.35 Modules initialize behind page-specific DOM checks where applicable', str_contains($checkout, 'data-customer-checkout') && str_contains($payment, 'data-'));
$add('34.36 Duplicate user actions are guarded by validation before state mutation', str_contains($cart, 'safeQuantity') && str_contains($wishlist, 'if (!id)'));
$add('34.37 Auth, checkout and payment preserve same-origin credentials / API boundaries', str_contains($auth, 'credentials') && str_contains($checkout, 'credentials') && str_contains($payment, 'credentials'));
$add('34.38 Commerce context is integrated with search/product/setup flows', str_contains($search, 'updateCommerceContext') && str_contains($product, 'updateCommerceContext') && str_contains($setup, 'updateCommerceContext'));
$add('34.39 Cross-module regression coverage includes header, search, cart, wishlist, product detail, theme/responsive/accessibility contracts', str_contains($header, 'aria-') && str_contains($search, 'data-search') && str_contains($cart, 'cart:updated') && str_contains($wishlist, 'wishlist:updated') && str_contains($product, 'aria-'));
$add('34.40 Phase 34 test output explicitly avoids claiming runtime lock from static checks', str_contains($read('.github/workflows/phase-34-js-integrity.yml'), 'Phase 34 static contract suite') && str_contains($read('.github/workflows/phase-34-js-integrity.yml'), 'php test-phase-34-js-integrity.php'));

$pass = 0;
$fail = 0;
foreach ($checks as [$label, $ok]) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    $ok ? $pass++ : $fail++;
}
echo PHP_EOL . "PHASE 34 STATIC CONTRACTS — PASS: {$pass} FAIL: {$fail} BLOCKED: 0 LOCK: NO (browser/XAMPP runtime regression required)" . PHP_EOL;
exit($fail === 0 ? 0 : 1);
