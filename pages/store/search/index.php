<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/Bootstrap.php';
Bootstrap::boot();

$pageTitle = 'جستجو | نیواد';
$metaDescription = 'جستجو و فیلتر محصولات فروشگاه دیجیتال نیواد.';
$canonicalUrl = url('/search');
$robots = 'index,follow';
$searchQuery = trim(
    (string) Request::query('q', '')
);

include_once __DIR__ . '/../../../partials/header.php';
?>

<?php if ($searchQuery === ''): ?>

    <main
        class="catalog-page catalog-page--search-empty">

        <header class="catalog-page__head">

            <span class="catalog-page__eyebrow">
                PRODUCT DISCOVERY
            </span>

            <h1 class="catalog-page__title">
                جستجوی محصول
            </h1>

            <p class="catalog-page__description">
                نام محصول، برند یا مدل موردنظر خود را جستجو کنید.
            </p>

        </header>

        <div
            class="catalog-state"
            data-catalog-state="empty"
            role="status"
            aria-live="polite">

            برای شروع، عبارت موردنظر خود را
            در کادر جستجو وارد کنید.

        </div>

    </main>

<?php else: ?>

    <!-- محتوای فعلی Search Page اینجا باقی می‌ماند -->



    <section
        class="catalog-filter-panel"
        data-catalog-filters
        aria-label="فیلتر محصولات">

        <div class="catalog-filter-panel__head">
            <div>
                <span class="catalog-filter-panel__eyebrow">
                    FILTERS
                </span>

                <h2 class="catalog-filter-panel__title">
                    فیلتر محصولات
                </h2>
            </div>

            <button
                type="button"
                class="catalog-filter-reset"
                data-filter-reset>
                حذف همه فیلترها
            </button>
        </div>

        <div class="catalog-filter-panel__body">

            <div class="catalog-filter">
                <label
                    class="catalog-filter__label"
                    for="catalog-sort">
                    مرتب‌سازی
                </label>

                <select
                    id="catalog-sort"
                    class="catalog-filter__control"
                    data-filter-sort>

                    <option value="relevance">
                        مرتبط‌ترین
                    </option>

                    <option value="newest">
                        جدیدترین
                    </option>

                    <option value="oldest">
                        قدیمی‌ترین
                    </option>

                    <option value="featured">
                        ویژه‌ترین
                    </option>
                </select>
            </div>

            <div
                class="catalog-filter"
                data-filter-group="category">

                <label
                    class="catalog-filter__label"
                    for="catalog-category">
                    دسته‌بندی
                </label>

                <select
                    id="catalog-category"
                    class="catalog-filter__control"
                    data-filter-category>

                    <option value="">
                        همه دسته‌بندی‌ها
                    </option>
                </select>
            </div>

            <div
                class="catalog-filter"
                data-filter-group="brand">

                <label
                    class="catalog-filter__label"
                    for="catalog-brand">
                    برند
                </label>

                <select
                    id="catalog-brand"
                    class="catalog-filter__control"
                    data-filter-brand>

                    <option value="">
                        همه برندها
                    </option>
                </select>
            </div>

            <div
                class="catalog-filter"
                data-filter-group="product-type">

                <label
                    class="catalog-filter__label"
                    for="catalog-product-type">
                    نوع محصول
                </label>

                <select
                    id="catalog-product-type"
                    class="catalog-filter__control"
                    data-filter-product-type>

                    <option value="">
                        همه انواع محصول
                    </option>
                </select>
            </div>

            <div class="catalog-filter catalog-filter--price">

                <span class="catalog-filter__label">
                    محدوده قیمت
                </span>

                <div class="catalog-filter__price-row">

                    <input
                        id="catalog-min-price"
                        class="catalog-filter__control"
                        type="number"
                        min="0"
                        inputmode="numeric"
                        placeholder="حداقل"
                        data-filter-min-price>

                    <input
                        id="catalog-max-price"
                        class="catalog-filter__control"
                        type="number"
                        min="0"
                        inputmode="numeric"
                        placeholder="حداکثر"
                        data-filter-max-price>

                </div>
            </div>

            <label class="catalog-filter catalog-filter-check">

                <input
                    type="checkbox"
                    data-filter-stock>

                <span>
                    فقط کالاهای موجود
                </span>

            </label>

        </div>

        <div
            class="catalog-filter-attributes"
            data-filter-attributes>
        </div>

        <div
            class="catalog-active-filters"
            data-active-filters
            aria-live="polite">
        </div>

        <div class="catalog-filter-panel__actions">

            <button
                type="button"
                class="catalog-filter-apply"
                data-filter-apply>
                اعمال فیلترها
            </button>

        </div>

    </section>

    <div
        class="catalog-results-bar"
        aria-live="polite">

        <span
            class="catalog-results-count"
            data-catalog-count>
            ۰ محصول
        </span>

        <span
            class="catalog-results-query"
            data-catalog-query>
        </span>

    </div>

    <div
        class="catalog-state"
        data-catalog-state
        role="status"
        aria-live="polite">
        در حال دریافت اطلاعات...
    </div>

    <section
        class="catalog-grid"
        data-catalog-list
        aria-live="polite"
        aria-busy="true">
    </section>

    <nav
        class="catalog-pagination"
        data-catalog-pagination
        aria-label="صفحه‌بندی محصولات"
        hidden>
    </nav>

    </main>
<?php endif; ?>

<?php include_once __DIR__ . '/../../../partials/footer.php'; ?>