<?php
declare(strict_types=1);

/**
 * Nivad Phase 44 — read-only source contract test.
 * Does not connect to the database or issue SQL writes.
 */

$root = __DIR__;
$pass = 0;
$fail = 0;

function source(string $relative): string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        return '';
    }
    return (string) file_get_contents($path);
}

function check(string $name, bool $condition): void
{
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo '<span style="color:green">[PASS]</span> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '<br>' . PHP_EOL;
        return;
    }
    $fail++;
    echo '<span style="color:red">[FAIL]</span> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '<br>' . PHP_EOL;
}

$cta = source('partials/cta.php');
$product = source('partials/product.php');
$newProduct = source('partials/new-product.php');
$categories = source('partials/categories.php');
$storefront = source('assets/js/modules/storefront.js');
$productsPage = source('pages/store/products/index.php');
$finderPage = source('pages/store/finder/index.php');
$plan = source('phase-44-placeholder-hardcoded-content-cleanup-plan.md');

check('44.T01 CTA partial exists', $cta !== '');
check('44.T02 products partial exists', $product !== '');
check('44.T03 new-products partial exists', $newProduct !== '');
check('44.T04 categories partial exists', $categories !== '');
check('44.T05 storefront module exists', $storefront !== '');
check('44.T06 products page exists', $productsPage !== '');
check('44.T07 Finder page exists', $finderPage !== '');
check('44.T08 CTA uses an existing Finder page path', str_contains($cta, '/pages/store/finder/index.php'));
check('44.T09 products CTA uses existing catalog page', str_contains($product, '/pages/store/products/index.php'));
check('44.T10 new-products CTA uses existing catalog page', str_contains($newProduct, '/pages/store/products/index.php'));
check('44.T11 audited CTA has no href="#"', !preg_match("~href\\s*=\\s*['\"]#['\"]~", $cta));
check('44.T12 audited products partial has no href="#"', !preg_match("~href\\s*=\\s*['\"]#['\"]~", $product));
check('44.T13 audited new-products partial has no href="#"', !preg_match("~href\\s*=\\s*['\"]#['\"]~", $newProduct));
check('44.T14 audited category partial has no href="#"', !preg_match("~href\\s*=\\s*['\"]#['\"]~", $categories));
check('44.T15 static category fallback anchors are removed', !str_contains($categories, 'class="category-card"'));
check('44.T16 category API contract is preserved', str_contains($categories, 'data-categories-section') && str_contains($storefront, '/storefront/categories'));
check('44.T17 category route remains API-derived', str_contains($storefront, 'category.slug') && str_contains($storefront, 'encodeURIComponent(category.slug)'));
check('44.T18 live product API contract is preserved', str_contains($storefront, '/storefront/products'));
check('44.T19 live product grid selector is preserved', str_contains($product, 'class="product-grid"') && str_contains($storefront, 'querySelector(".product-grid")'));
check('44.T20 hardcoded iPhone demo card removed', !str_contains($product, 'iPhone 16'));
check('44.T21 hardcoded Galaxy demo card removed', !str_contains($product, 'Galaxy S25'));
check('44.T22 hardcoded Anker demo card removed', !str_contains($product, 'anker-735-charger'));
check('44.T23 hardcoded UGREEN demo card removed', !str_contains($product, 'ugreen-power-bank-20000'));
check('44.T24 hardcoded demo prices removed from product fallback', !str_contains($product, '۵۹,۹۰۰,۰۰۰') && !str_contains($product, '۶۴,۵۰۰,۰۰۰'));
check('44.T25 hardcoded new-arrival prices removed', !str_contains($newProduct, '۵۹,۹۰۰,۰۰۰') && !str_contains($newProduct, '۶۴,۵۰۰,۰۰۰'));
check('44.T26 hardcoded fake cart identifiers removed from new-product fallback', !str_contains($newProduct, 'data-cart='));
check('44.T27 unsupported new-only claim removed', !str_contains($newProduct, 'مشاهده همه محصولات جدید'));
check('44.T28 honest initial category loading state exists', str_contains($categories, 'در حال دریافت دسته‌بندی‌ها'));
check('44.T29 honest initial product loading state exists', str_contains($product, 'در حال دریافت محصولات'));
check('44.T30 category empty state exists', str_contains($storefront, 'دسته‌بندی فعالی برای نمایش وجود ندارد'));
check('44.T31 product empty state exists', str_contains($storefront, 'محصول فعالی برای نمایش وجود ندارد'));
check('44.T32 category failure state exists', str_contains($storefront, 'دریافت دسته‌بندی‌ها ممکن نشد'));
check('44.T33 product failure state exists', str_contains($storefront, 'دریافت محصولات ممکن نشد'));
check('44.T34 empty/error states use status semantics', str_contains($storefront, 'setAttribute("role", "status")') && str_contains($storefront, 'setAttribute("aria-live", "polite")'));
check('44.T35 categories request failure is handled locally', str_contains($storefront, 'Storefront categories failed:'));
check('44.T36 products request failure is handled locally', str_contains($storefront, 'Storefront products failed:'));
check('44.T37 plan exists', $plan !== '');
preg_match_all('/^\| (44\.\d{2}\.\d{2}) \|/m', $plan, $matches);
$ids = $matches[1] ?? [];
check('44.T38 plan has exactly 100 contract rows', count($ids) === 100);
check('44.T39 plan has 100 unique contract IDs', count(array_unique($ids)) === 100);
check('44.T40 plan has ten batches', substr_count($plan, '### Batch 44.') === 10);
check('44.T41 plan does not claim PASS', str_contains($plan, 'PASS = NO'));
check('44.T42 plan does not claim LOCK', str_contains($plan, 'LOCK = NO'));
check('44.T43 plan documents Backend Freeze', str_contains($plan, 'Backend Freeze remains active'));
check('44.T44 plan records runtime/browser validation as pending', str_contains($plan, 'XAMPP PHP execution') && str_contains($plan, 'browser checks'));
check('44.T45 no guessed category slug was added', !preg_match("~href\\s*=\\s*['\"][^'\"]*categories/[^'\"]+['\"]~", $categories));
check('44.T46 valid search/product-form placeholder copy is outside changed files', !str_contains($categories, 'placeholder=') && !str_contains($product, 'placeholder='));
check('44.T47 test is read-only and contains no SQL writes', !preg_match('/\b(INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM|DROP\s+TABLE)\b/i', (string) file_get_contents(__FILE__)));
check('44.T48 changed storefront module retains same-origin credentials', str_contains($storefront, 'credentials: "same-origin"'));
check('44.T49 category and product API calls remain distinct', str_contains($storefront, 'api("/storefront/categories")') && str_contains($storefront, 'api("/storefront/products")'));
check('44.T50 phase plan marks incomplete contracts pending', str_contains($plan, 'full source/runtime evidence not established'));

$total = $pass + $fail;
echo '<hr>' . PHP_EOL;
echo '<strong>Phase 44 source-contract test: ' . $pass . '/' . $total . ' PASS; ' . $fail . ' FAIL.</strong><br>' . PHP_EOL;
echo '<strong>This script has not been executed in XAMPP here. Phase 44 PASS/LOCK remains NO until PHP lint/runtime and browser regression are performed.</strong><br>' . PHP_EOL;
