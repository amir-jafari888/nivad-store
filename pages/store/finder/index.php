<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/Bootstrap.php';

Bootstrap::boot();

$pageTitle =
    'پیدا کردن محصول مناسب | نیواد';

$metaDescription =
    'با چند انتخاب ساده، محصول مناسب نیازت را در فروشگاه نیواد پیدا کن.';

$canonicalUrl =
    url('/finder');

$robots =
    'index,follow';

include_once
    __DIR__ . '/../../../partials/header.php';

include_once
    __DIR__ . '/../../../partials/finder.php';

include_once
    __DIR__ . '/../../../partials/footer.php';
