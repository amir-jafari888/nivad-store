<?php

declare(strict_types=1);
require_once __DIR__ . '/../../../app/Core/Bootstrap.php';
Bootstrap::boot();
$slug = trim((string)($_GET['slug'] ?? ''));
$pageTitle = 'مجموعه محصولات | نیواد';
$metaDescription = 'مشاهده محصولات منتخب این مجموعه.';
$canonicalUrl = $slug !== ''
    ? url('/collections/' . rawurlencode($slug))
    : url('/collections');
$robots = 'index,follow';
include_once __DIR__ . '/../../../partials/header.php';
?>
<main class="collection-page" data-collection-page data-api-base="<?= e(url('/api/index.php')) ?>" data-collection-slug="<?= e($slug) ?>" aria-labelledby="collection-title">
    <div class="collection-page__container">
        <header class="collection-page__head">
            <span class="collection-page__eyebrow">COLLECTION</span>
            <h1 id="collection-title" class="collection-page__title" data-collection-title>مجموعه محصولات</h1>
            <p class="collection-page__description" data-collection-description>در حال دریافت اطلاعات مجموعه...</p>
        </header>
        <div class="collection-state" data-collection-state role="status" aria-live="polite">
            <span class="collection-state__loading" data-collection-loading>در حال دریافت محصولات...</span>
            <span class="collection-state__empty" data-collection-empty hidden>محصولی برای این مجموعه پیدا نشد.</span>
            <span class="collection-state__error" data-collection-error hidden>دریافت اطلاعات مجموعه با خطا مواجه شد.</span>
            <button type="button" class="collection-state__retry" data-collection-retry hidden>تلاش دوباره</button>
        </div>
        <section class="collection-products" aria-labelledby="collection-products-title">
            <div class="collection-products__head">
                <h2 id="collection-products-title">محصولات این مجموعه</h2>
                <span class="collection-products__count" data-collection-count>۰ محصول</span>
            </div>
            <div class="collection-products__grid" data-collection-products aria-live="polite" aria-busy="true"></div>
        </section>
    </div>
</main>
<?php include_once __DIR__ . '/../../../partials/footer.php'; ?>