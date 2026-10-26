<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/Bootstrap.php';
Bootstrap::boot();

$pageTitle = 'بررسی سازگاری دستگاه | نیواد';
$metaDescription = 'برند و مدل دستگاهت را انتخاب کن و وضعیت سازگاری محصولات مرتبط را در فروشگاه نیواد بررسی کن.';
$canonicalUrl = url('/pages/store/compatibility.php');
$robots = 'index,follow';

require_once __DIR__ . '/../../partials/header.php';
?>
<main id="compatibility-page" class="compatibility-page">
    <?php require __DIR__ . '/../../partials/compatibilty.php'; ?>
</main>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
