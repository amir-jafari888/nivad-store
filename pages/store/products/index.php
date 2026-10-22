<?php

$pageTitle = 'محصولات | نیواد';

include_once __DIR__ . '/../../../partials/header.php';
?>

<main
    class="catalog-page"
    data-catalog-page="products"
    data-api-base="<?= $baseUrl ?>/api/index.php"
    data-product-base="<?= $baseUrl ?>/products">

    <header class="catalog-page__head">
        <h1
            class="catalog-page__title"
            data-catalog-title>
            محصولات
        </h1>

        <p
            class="catalog-page__description"
            data-catalog-description>
            محصولات فعال فروشگاه
        </p>
    </header>

    <div
        class="catalog-page__toolbar"
        aria-label="خلاصه محصولات">

        <span
            class="catalog-page__count"
            data-catalog-count
            aria-live="polite">
            ۰ محصول
        </span>

    </div>

    <p
        class="catalog-state"
        data-catalog-state>
        در حال دریافت اطلاعات...
    </p>

    <section
        class="catalog-grid"
        data-catalog-list
        aria-live="polite">
    </section>

</main>

<?php include_once __DIR__ . '/../../../partials/footer.php'; ?>