<?php
declare(strict_types=1);

/* Phase 33 read-only static contract suite. No database writes or API calls. */
$root = __DIR__;
$checks = [];
$add = static function (string $label, bool $ok) use (&$checks): void { $checks[] = [$label, $ok]; };
$read = static function (string $path) use ($root): string {
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $file = $root . DIRECTORY_SEPARATOR . $path;
    return is_file($file) ? (string) file_get_contents($file) : '';
};

$productJs = $read('assets/js/modules/product-detail.js');
$mediaJs = $read('assets/js/modules/product-media.js');
$mainJs = $read('assets/js/main.js');
$productPage = $read('pages/store/products/show.php');
$mobile = $read('assets/css/mobile/mobile.css');
$tablet = $read('assets/css/tablet/tablet.css');
$desktop = $read('assets/css/desktop/desktop.css');
$mediaMobile = $read('assets/css/mobile/product-media-mobile.css');
$mediaTablet = $read('assets/css/tablet/product-media-tablet.css');
$mediaDesktop = $read('assets/css/desktop/product-media-desktop.css');

$add('33.01 Product Images consume existing product.media', str_contains($productJs, 'renderGallery(product.media)') && str_contains($mediaJs, '[data-product-main-image]'));
$add('33.02 Gallery keeps primary selection and thumbnail interaction', str_contains($productJs, 'media.find((item) => item?.is_primary) || media[0]') && str_contains($productJs, 'product-detail-gallery__thumb'));
$add('33.03 Thumbnails are native buttons, lazy-loaded, and focusable', str_contains($productJs, 'button.type = "button"') && str_contains($productJs, 'loading="lazy"') && str_contains($mediaMobile, 'product-detail-gallery__thumb:focus-visible'));
$add('33.04 Lazy loading is enforced by the media module', str_contains($mediaJs, 'image.loading = "lazy"') && str_contains($productJs, 'loading="lazy"'));
$add('33.05 Missing media receives a deterministic placeholder', str_contains($mediaJs, 'const PLACEHOLDER =') && str_contains($mediaJs, 'setPlaceholder(image'));
$add('33.06 Broken images transition to an explicit failure placeholder state', str_contains($mediaJs, 'image.addEventListener("error"') && str_contains($mediaJs, 'image.dataset.mediaPlaceholder = "true"'));
$add('33.07 Avatar fallback is dependency-safe without inventing a backend contract', str_contains($mediaJs, '[data-media-avatar] img') && !str_contains($mediaJs, '/avatar/'));
$add('33.08 Responsive image attributes support optional srcset/sizes inputs', str_contains($mediaJs, 'image.srcset') && str_contains($mediaJs, 'image.sizes') && str_contains($mediaJs, 'mediaSrcset') && str_contains($mediaJs, 'mediaSizes'));
$add('33.09 Loading state is exposed in JS and all three breakpoints', str_contains($mediaJs, 'setMediaState(image, "loading")') && str_contains($mediaMobile, 'data-media-state="loading"') && str_contains($mediaTablet, 'data-media-state="loading"') && str_contains($mediaDesktop, 'data-media-state="loading"'));
$add('33.10 Failure state is exposed with accessible fallback text in all breakpoints', str_contains($mediaJs, 'تصویر محصول در دسترس نیست') && str_contains($mediaMobile, 'تصویر در دسترس نیست') && str_contains($mediaTablet, 'تصویر در دسترس نیست') && str_contains($mediaDesktop, 'تصویر در دسترس نیست'));
$add('Media module is initialized by main.js', str_contains($mainJs, 'import productMedia from "./modules/product-media.js"') && str_contains($mainJs, 'productMedia();'));
$add('Dedicated media styles are imported at mobile/tablet/desktop breakpoints', str_contains($mobile, 'product-media-mobile.css') && str_contains($tablet, 'product-media-tablet.css') && str_contains($desktop, 'product-media-desktop.css'));
$add('Product page retains semantic gallery landmark and existing data hook', str_contains($productPage, 'aria-label="تصاویر محصول"') && str_contains($productPage, 'data-product-gallery'));

$pass = 0;
$fail = 0;
foreach ($checks as [$label, $ok]) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    $ok ? $pass++ : $fail++;
}

echo PHP_EOL . "PHASE 33 MEDIA EXPERIENCE — SUMMARY PASS: {$pass} FAIL: {$fail} BLOCKED: 0 LOCK: " . ($fail === 0 ? 'NO (browser/runtime media review pending)' : 'NO') . PHP_EOL;
exit($fail === 0 ? 0 : 1);
