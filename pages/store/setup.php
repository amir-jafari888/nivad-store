<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/Bootstrap.php';
Bootstrap::boot();

$pageTitle = 'ساخت ستاپ هوشمند | نیواد';
$metaDescription = 'نیازت را مشخص کن، بودجه‌ات را تعیین کن و ترکیب محصولات سازگار را برای ستاپ خودت بررسی کن.';
$canonicalUrl = url('/setup');
$robots = 'index,follow';

require_once __DIR__ . '/../../partials/header.php';
?>
<main id="smart-setup-page" class="smart-setup-page">
    <?php require __DIR__ . '/../../partials/build-setup.php'; ?>
</main>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
