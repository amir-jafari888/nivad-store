<?php
declare(strict_types=1);

include_once __DIR__ . '/partials/header.php';

if (!CmsContentRenderer::renderPage('home')) {
    include_once __DIR__ . '/partials/hero.php';
    include_once __DIR__ . '/partials/categories.php';
    include_once __DIR__ . '/partials/discovery.php';
    include_once __DIR__ . '/partials/product.php';
    include_once __DIR__ . '/partials/setup-preview.php';
    include_once __DIR__ . '/partials/compatibility-preview.php';
    include_once __DIR__ . '/partials/new-product.php';
    include_once __DIR__ . '/partials/value-proposition.php';
    include_once __DIR__ . '/partials/buying-guide.php';
    include_once __DIR__ . '/partials/cta.php';
    include_once __DIR__ . '/partials/smart-bundle.php';
}

include_once __DIR__ . '/partials/footer.php';
