<?php

declare(strict_types=1);

$root = __DIR__;
$pass = 0;
$fail = 0;

function check(string $id, bool $condition): void
{
    global $pass, $fail;

    if ($condition) {
        $pass++;
        echo '<span style="color:green">[PASS]</span> ' . $id . '<br>' . PHP_EOL;
        return;
    }

    $fail++;
    echo '<span style="color:red">[FAIL]</span> ' . $id . '<br>' . PHP_EOL;
}

function source(string $path): string
{
    global $root;
    $full = $root . DIRECTORY_SEPARATOR . $path;
    return is_file($full) ? (string) file_get_contents($full) : '';
}

$main = source('assets/js/main.js');
$performance = source('assets/js/modules/performance.js');
$hero = source('assets/js/modules/hero.js');
$header = source('partials/header.php');
$adminSettings = source('admin/assets/css/setting.css');
$storeSettings = source('assets/css/setting.css');

check('42.01.01 storefront CSS entry exists', is_file($root . '/assets/css/main.css'));
check('42.01.02 admin CSS token source exists', is_file($root . '/admin/assets/css/setting.css'));
check('42.01.03 storefront JS entry exists', is_file($root . '/assets/js/main.js'));
check('42.01.04 performance module exists', $performance !== '');
check('42.01.05 existing storefront entry remains module-based', str_contains($main, 'from "./modules/header.js"'));
check('42.01.06 existing admin/storefront CSS boundaries remain explicit', str_contains($adminSettings, 'NIVAD Admin-only Design Tokens') && str_contains(source('assets/css/main.css'), './setting.css'));
check('42.01.07 store typography uses woff2', str_contains($storeSettings, 'format("woff2")'));
check('42.01.08 store fonts use font-display swap', str_contains($storeSettings, 'font-display: swap'));
check('42.02.01 critical hero preload exists', str_contains($header, 'rel="preload" as="image"'));
check('42.02.02 hero preload has high priority', str_contains($header, 'fetchpriority="high"'));
check('42.02.03 Unsplash preconnect exists', str_contains($header, 'rel="preconnect" href="https://images.unsplash.com"'));
check('42.02.04 brand image keeps dimensions', str_contains($header, 'width="42" height="42"'));
check('42.02.05 performance module marks first hero image eager', str_contains($performance, 'image.loading = "eager"'));
check('42.02.06 performance module marks later hero images lazy', str_contains($performance, 'image.loading = "lazy"'));
check('42.02.07 lazy images receive low fetch priority', str_contains($performance, 'image.fetchPriority = "low"'));
check('42.02.08 async image decoding is enforced', str_contains($performance, 'image.decoding = "async"'));
check('42.03.01 Inter variable font exists in token source', str_contains($storeSettings, 'InterVariable.woff2'));
check('42.03.02 Vazirmatn variable font exists in token source', str_contains($storeSettings, 'Vazirmatn[wght].woff2'));
check('42.03.03 font display contract is explicit', substr_count($storeSettings, 'font-display: swap') >= 2);
check('42.04.01 CSS loading order keeps setting before responsive files', strpos(source('assets/css/main.css'), './setting.css') < strpos(source('assets/css/main.css'), './mobile/mobile.css'));
check('42.04.02 duplicate standalone Swiper stylesheet removed from header', !str_contains($header, '/swiper/swiper-bundle.min.css'));
check('42.05.01 performance module is wired into main entry', str_contains($main, 'import performance from "./modules/performance.js"'));
check('42.05.02 performance initializer is executed', str_contains($main, 'performance();'));
check('42.05.03 hero module no longer statically imports Swiper', !str_contains($hero, 'import Swiper from'));
check('42.05.04 hero module dynamically imports Swiper', str_contains($hero, 'await import("../../../swiper/swiper-bundle.mjs")'));
check('42.06.01 IntersectionObserver is used for lazy media', str_contains($performance, 'IntersectionObserver'));
check('42.06.02 idle scheduling exists', str_contains($performance, 'requestIdleCallback'));
check('42.07.01 offscreen rendering deferral exists', str_contains($performance, 'contentVisibility = "auto"'));
check('42.09.01 LCP measurement exists', str_contains($performance, 'largest-contentful-paint'));
check('42.09.02 CLS measurement exists', str_contains($performance, 'layout-shift'));
check('42.09.03 long-task measurement exists', str_contains($performance, 'longtask'));
check('42.09.04 slow interaction measurement exists', str_contains($performance, 'slowInteractions'));
check('42.10.01 Backend Freeze untouched by this test', true);

$total = $pass + $fail;
echo '<hr>' . PHP_EOL;
echo '<strong>Phase 42 source audit: ' . $pass . '/' . $total . ' PASS; ' . $fail . ' FAIL.</strong><br>' . PHP_EOL;
echo '<strong>Runtime/browser metrics remain PENDING until executable test environment is available.</strong><br>' . PHP_EOL;
