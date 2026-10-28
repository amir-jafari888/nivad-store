<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/Bootstrap.php';
Bootstrap::boot();

$pageTitle = 'کشف هوشمند | نیواد';
$metaDescription = 'محصول مناسب را بر اساس نیاز و کاربردت پیدا کن.';
$canonicalUrl = url('/discover');
$robots = 'index,follow';

include_once __DIR__ . '/../../../partials/header.php';
include_once __DIR__ . '/content.php';
include_once __DIR__ . '/../../../partials/footer.php';
