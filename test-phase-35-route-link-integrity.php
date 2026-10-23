<?php

declare(strict_types=1);

/**
 * Phase 35 — Route & Link Integrity.
 * Read-only static checks. Does not issue HTTP requests or mutate the database.
 */

$root = __DIR__;
$checks = [];

$add = static function (string $label, bool $passed) use (&$checks): void {
    $checks[] = [$label, $passed];
};

$read = static function (string $path) use ($root): string {
    $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    return is_file($file) ? (string) file_get_contents($file) : '';
};

$exists = static function (string $path) use ($root): bool {
    return is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
};

$htaccess = $read('.htaccess');
$webRoutes = $read('routes/web.php');
$apiRoutes = $read('routes/api.php');
$mainJs = $read('assets/js/main.js');
$checkout = $read('pages/shop/checkout.php');
$wishlist = $read('pages/shop/wishlist.php');
$cart = $read('pages/shop/cart.php');
$authPages = [
    'pages/auth/login.php',
    'pages/auth/register.php',
    'pages/auth/forgot-password.php',
    'pages/auth/verify-email.php',
];

$add('35.01 .htaccess exists and contains rewrite rules', $htaccess !== '' && str_contains($htaccess, 'RewriteRule'));
$rewriteTargets = [];
preg_match_all('/^\s*RewriteRule\s+\S+\s+([^\s]+)\s+/m', $htaccess, $rewriteMatches);
foreach ($rewriteMatches[1] as $target) {
    $target = strtok($target, '?');
    if ($target !== '' && !str_starts_with($target, 'http://') && !str_starts_with($target, 'https://')) {
        $rewriteTargets[] = $target;
    }
}
$missingRewriteTargets = array_values(array_filter(
    $rewriteTargets,
    static fn(string $target): bool => !is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target))
));
$add('35.02 Every static .htaccess rewrite target exists', count($rewriteTargets) > 0 && $missingRewriteTargets === []);

$add('35.03 Web route definitions exist', $webRoutes !== '' && str_contains($webRoutes, 'return static function'));
$add('35.04 API route definitions exist', $apiRoutes !== '');
$add('35.05 All referenced customer auth page files exist', count(array_filter($authPages, $exists)) === count($authPages));

$expectedLoginPath = '/pages/auth/login.php';
$add('35.06 Cart login destination points to the existing login page', str_contains($cart, $expectedLoginPath));
$add('35.07 Checkout login destination points to the existing login page', str_contains($checkout, $expectedLoginPath));
$add('35.08 Wishlist login destination points to the existing login page', str_contains($wishlist, $expectedLoginPath));
$add('35.09 Checkout and wishlist no longer reference the unresolved /auth/login.php path', !str_contains($checkout, "'/auth/login.php'") && !str_contains($wishlist, "'/auth/login.php'"));

$authLinkPages = [
    'pages/auth/login.php',
    'pages/auth/register.php',
    'pages/auth/forgot-password.php',
    'pages/auth/verify-email.php',
];
$authLinksValid = true;
$authLinkCount = 0;
foreach ($authLinkPages as $page) {
    $source = $read($page);
    preg_match_all('/url\(\s*[\'"]([^\'"]+\.php)[\'"]\s*\)/', $source, $matches);
    foreach ($matches[1] as $urlPath) {
        $authLinkCount++;
        $relativePath = ltrim($urlPath, '/');
        if (!$exists($relativePath)) {
            $authLinksValid = false;
        }
    }
}
$add('35.10 Local PHP links declared by auth pages resolve to existing files', $authLinkCount > 0 && $authLinksValid);

$imports = [];
preg_match_all('/^import\s+[\s\S]*?from\s+["\']([^"\']+)["\'];?\s*$/m', $mainJs, $importMatches);
foreach ($importMatches[1] as $path) {
    if (str_starts_with($path, '.')) {
        $imports[] = dirname($root . '/assets/js/main.js') . '/' . $path;
    }
}
$importsValid = count($imports) > 0;
foreach ($imports as $path) {
    if (!is_file($path)) {
        $importsValid = false;
        break;
    }
}
$add('35.11 Main JavaScript entry imports resolve to existing modules', $importsValid);

$add('35.12 Customer-facing cart, wishlist and checkout page files exist', $exists('pages/shop/cart.php') && $exists('pages/shop/wishlist.php') && $exists('pages/shop/checkout.php'));

$failed = 0;
foreach ($checks as [$label, $passed]) {
    echo $passed
        ? '<span style="color:green">[PASS] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</span><br>\n"
        : '<span style="color:red">[FAIL] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</span><br>\n";
    if (!$passed) {
        $failed++;
    }
}

echo '<br><strong>Phase 35 static checks: ' . (count($checks) - $failed) . '/' . count($checks) . ' PASS</strong><br>' . PHP_EOL;
echo $failed === 0
    ? '<strong>Result: STATIC PASS — browser/HTTP runtime remains unverified.</strong><br>' . PHP_EOL
    : '<strong>Result: FAIL — investigate failed checks before locking.</strong><br>' . PHP_EOL;

exit($failed === 0 ? 0 : 1);
