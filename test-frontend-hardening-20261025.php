<?php

declare(strict_types=1);

$root = __DIR__;
$pass = 0;
$fail = 0;

function check(string $label, bool $condition): void
{
    global $pass, $fail;

    if ($condition) {
        $pass++;
        echo '<span style="color:green">[PASS]</span> ' .
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
            '<br>' . PHP_EOL;
        return;
    }

    $fail++;
    echo '<span style="color:red">[FAIL]</span> ' .
        htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
        '<br>' . PHP_EOL;
}

function readProjectFile(string $root, string $path): string
{
    $file = $root . DIRECTORY_SEPARATOR . $path;
    return is_file($file) ? (string) file_get_contents($file) : '';
}

function hasNoPhpSyntaxErrors(string $root, string $path): bool
{
    $file = $root . DIRECTORY_SEPARATOR . $path;

    if (!is_file($file)) return false;

    $output = [];
    $exitCode = 1;
    exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);

    return $exitCode === 0;
}

function hasNoJsSyntaxErrors(string $root, string $path): bool
{
    $file = $root . DIRECTORY_SEPARATOR . $path;

    if (!is_file($file)) return false;

    $output = [];
    $exitCode = 1;
    exec('node --check ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);

    return $exitCode === 0;
}

echo '<strong>Nivad — Frontend Hardening Regression</strong><br><br>' . PHP_EOL;

$productPartial = readProjectFile($root, 'partials/product.php');
$categoryPartial = readProjectFile($root, 'partials/categories.php');
$mainJs = readProjectFile($root, 'assets/js/main.js');
$headerJs = readProjectFile($root, 'assets/js/modules/header.js');
$themeJs = readProjectFile($root, 'assets/js/modules/theme.js');
$heroJs = readProjectFile($root, 'assets/js/modules/hero.js');
$storefrontJs = readProjectFile($root, 'assets/js/modules/storefront.js');
$productModel = readProjectFile($root, 'app/Models/Product.php');
$categoryPage = readProjectFile($root, 'pages/store/category/show.php');

check(
    'Homepage product mock data removed',
    $productPartial !== '' &&
    !str_contains($productPartial, 'iPhone 16') &&
    !str_contains($productPartial, 'Galaxy S25') &&
    !str_contains($productPartial, 'Anker 735') &&
    !str_contains($productPartial, 'UGREEN Power Bank') &&
    !preg_match('/href\s*=\s*["\']#["\']/', $productPartial)
);

check(
    'Homepage category mock links removed',
    $categoryPartial !== '' &&
    !preg_match('/href\s*=\s*["\']#["\']/', $categoryPartial)
);

check(
    'Homepage product data uses real Storefront module contract',
    $productPartial !== '' && str_contains($productPartial, 'data-home-products-grid')
);

check(
    'Deferred JS loading is capability-aware',
    str_contains($mainJs, 'deferredModuleManifest') &&
    str_contains($mainJs, 'pageCapabilities') &&
    str_contains($mainJs, 'shouldLoad')
);

check(
    'Critical global modules remain eagerly initialized',
    str_contains($mainJs, 'header();') &&
    str_contains($mainJs, 'theme();') &&
    str_contains($mainJs, 'search();') &&
    str_contains($mainJs, 'cartDrawer();')
);

check(
    'Header is separated from theme state management',
    !str_contains($headerJs, 'prefers-color-scheme') &&
    !str_contains($headerJs, 'localStorage.getItem("digital-store-theme")') &&
    !str_contains($headerJs, 'normalizeBrandAndRoutes')
);

check(
    'Mobile menu focus trap and focus restoration exist',
    str_contains($headerJs, 'trapFocus') && str_contains($headerJs, 'lastMenuTrigger')
);

check(
    'Profile menu focus management exists',
    str_contains($headerJs, 'lastProfileTrigger') &&
    str_contains($headerJs, 'profileMenu.querySelector(focusableSelector)')
);

check(
    'Theme module supports persisted and system preferences',
    str_contains($themeJs, 'prefers-color-scheme') &&
    str_contains($themeJs, 'localStorage') &&
    str_contains($themeJs, 'addEventListener')
);

check(
    'Hero non-first images are lazy loaded',
    str_contains($heroJs, 'image.loading = index === 0 ? "eager" : "lazy"') &&
    str_contains($heroJs, 'image.fetchPriority')
);

check(
    'Storefront has explicit empty/error states',
    str_contains($storefrontJs, 'در حال حاضر محصول فعالی') &&
    str_contains($storefrontJs, 'دریافت محصولات با خطا مواجه شد')
);

check(
    'Product variant data is not fabricated on Product entity',
    !str_contains($productModel, 'implements ArrayAccess') &&
    !str_contains($productModel, "'variant_id'") &&
    !str_contains($productModel, "'stock_status'")
);

check(
    'Product model remains a concrete domain entity',
    str_contains($productModel, 'final class Product') &&
    str_contains($productModel, 'public int $id')
);

check(
    'Changed PHP files pass syntax validation',
    hasNoPhpSyntaxErrors($root, 'app/Models/Product.php') &&
    hasNoPhpSyntaxErrors($root, 'partials/product.php') &&
    hasNoPhpSyntaxErrors($root, 'partials/categories.php')
);

check(
    'Changed JavaScript files pass Node syntax validation',
    hasNoJsSyntaxErrors($root, 'assets/js/main.js') &&
    hasNoJsSyntaxErrors($root, 'assets/js/modules/header.js') &&
    hasNoJsSyntaxErrors($root, 'assets/js/modules/theme.js') &&
    hasNoJsSyntaxErrors($root, 'assets/js/modules/hero.js') &&
    hasNoJsSyntaxErrors($root, 'assets/js/modules/storefront.js')
);

check(
    'Known duplicate category data attribute is absent',
    $categoryPage !== '' &&
    substr_count($categoryPage, 'data-catalog-page="category"') === 0
);

echo '<br><strong>PASS: ' . $pass . ' | FAIL: ' . $fail . '</strong><br>' . PHP_EOL;
echo $fail === 0
    ? '<strong>RESULT: PASS</strong><br>' . PHP_EOL
    : '<strong>RESULT: FAIL — review failed checks before lock.</strong><br>' . PHP_EOL;

exit($fail === 0 ? 0 : 1);
