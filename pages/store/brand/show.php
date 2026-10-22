<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/Bootstrap.php';
Bootstrap::boot();

$slug = trim((string) ($_GET['slug'] ?? ''));

$brand = $slug !== ''
    ? (new BrandService())->findBySlug($slug)
    : null;

$pageTitle =
    $brand instanceof Brand &&
    $brand->status === 'active'
    ? trim((string) ($brand->name ?? 'برند')) . ' | نیواد'
    : 'برند | نیواد';

$metaDescription =
    $brand instanceof Brand &&
    $brand->status === 'active'
    ? trim((string) ($brand->description ?? ''))
    : '';

$canonicalUrl =
    $brand instanceof Brand &&
    $brand->status === 'active'
    ? url('/brands/' . rawurlencode($brand->slug))
    : null;

$robots = 'index,follow';

include_once __DIR__ . '/../../../partials/header.php';
?>

<main
    class="catalog-page page-brand"
    data-catalog-page="brand"
    data-catalog-slug="<?= e($slug) ?>"
    data-api-base="<?= e(url('/api/index.php')) ?>"
    data-product-base="<?= e(url('/products')) ?>">

    <div class="page-brand__container">

        <nav
            class="page-brand__breadcrumb"
            aria-label="مسیر صفحه">

            <a href="<?= e(url('/')) ?>">
                خانه
            </a>

            <span aria-hidden="true">/</span>

            <span aria-current="page">
                برند
            </span>

        </nav>

        <section
            class="page-brand__hero"
            aria-labelledby="brand-title">

            <div class="page-brand__hero-main">

                <div class="page-brand__identity">

                    <span class="page-brand__eyebrow">
                        OFFICIAL BRAND
                    </span>

                    <h1
                        id="brand-title"
                        class="page-brand__title"
                        data-catalog-title>
                        برند
                    </h1>

                    <p
                        class="page-brand__description"
                        data-catalog-description>
                    </p>

                </div>

                <div class="page-brand__brand-code">
                    <span>BRAND</span>
                    <strong>01</strong>
                </div>

            </div>

            <div class="page-brand__hero-footer">

                <span>
                    CURATED BY NIVAD
                </span>

                <span class="page-brand__hero-rule"></span>

                <span>
                    ORIGINAL COLLECTION
                </span>

            </div>

            <div
                class="page-brand__visual"
                aria-hidden="true">

                <span class="page-brand__visual-frame"></span>
                <span class="page-brand__visual-orbit"></span>

                <span class="page-brand__visual-mark">
                    N
                </span>

                <span class="page-brand__visual-label">
                    EST.
                    <strong>2026</strong>
                </span>

            </div>

        </section>

        <section
            class="page-brand__products"
            aria-labelledby="brand-products-title">

            <header class="page-brand__products-header">

                <div>

                    <span class="page-brand__section-label">
                        BRAND COLLECTION
                    </span>

                    <h2
                        id="brand-products-title"
                        class="page-brand__section-title">
                        محصولات این برند
                    </h2>

                </div>

                <div class="page-brand__section-mark">
                    <span>01</span>
                    <span>/</span>
                    <span>COLLECTION</span>
                </div>

            </header>

            <div
                class="catalog-state page-brand__state"
                data-catalog-state
                aria-live="polite">

                <span class="catalog-state__title">
                    در حال دریافت محصولات...
                </span>

            </div>

            <section
                class="catalog-grid page-brand__grid"
                data-catalog-list
                aria-label="محصولات برند">
            </section>

        </section>

    </div>

</main>

<?php include_once __DIR__ . '/../../../partials/footer.php'; ?>