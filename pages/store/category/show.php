<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/Core/Bootstrap.php';
Bootstrap::boot();

$slug = trim((string) ($_GET['slug'] ?? ''));

$category = $slug !== ''
    ? (new CategoryService())->findBySlug($slug)
    : null;

if (
    $category instanceof Category &&
    $category->status === 'active'
) {
    $pageTitle = trim(
        (string) (
            $category->metaTitle ??
            ''
        )
    );

    if ($pageTitle === '') {
        $pageTitle = $category->name . ' | نیواد';
    }

    $metaDescription = trim(
        (string) (
            $category->metaDescription ??
            ''
        )
    );

    if ($metaDescription === '') {
        $metaDescription = trim(
            (string) (
                $category->description ??
                ''
            )
        );
    }

    $canonicalUrl = url(
        '/categories/' .
            rawurlencode($category->slug)
    );
} else {
    $pageTitle = 'دسته‌بندی | نیواد';
    $metaDescription = '';
    $canonicalUrl = null;
}

$robots = 'index,follow';

include_once __DIR__ . '/../../../partials/header.php';
?>

<main
    class="catalog-page category-page"
    data-catalog-page
    data-catalog-page="category"
    data-catalog-page-root
    data-catalog-slug="<?= e($slug) ?>"
    data-category-base="<?= e(url('/categories')) ?>"
    data-api-base="<?= e(url('/api/index.php')) ?>"
    data-product-base="<?= e(url('/products')) ?>">

    <!-- =========================================================
         BREADCRUMB
    ========================================================== -->
    <nav
        class="catalog-breadcrumb category-page__breadcrumb"
        aria-label="مسیر صفحه">

        <a href="<?= e(url('/')) ?>">
            خانه
        </a>

        <span aria-hidden="true">
            /
        </span>

        <span aria-current="page">
            دسته‌بندی
        </span>
    </nav>


    <!-- =========================================================
         PREMIUM CATEGORY HERO
    ========================================================== -->
    <section
        class="category-hero"
        aria-labelledby="category-page-title">

        <div class="category-hero__background" aria-hidden="true">
            <span class="category-hero__orb category-hero__orb--one"></span>
            <span class="category-hero__orb category-hero__orb--two"></span>
            <span class="category-hero__grid"></span>
        </div>

        <div class="category-hero__content">

            <div class="category-hero__eyebrow">
                <span
                    class="category-hero__eyebrow-dot"
                    aria-hidden="true"></span>

                <span>
                    NIVAD CATEGORY
                </span>
            </div>

            <h1
                id="category-page-title"
                class="category-hero__title"
                data-catalog-title>
                دسته‌بندی
            </h1>

            <p
                class="category-hero__description"
                data-catalog-description>
            </p>

            <div class="category-hero__meta">

                <div class="category-hero__meta-item">
                    <span class="category-hero__meta-icon" aria-hidden="true">
                        ◈
                    </span>

                    <span>
                        انتخاب هوشمند محصولات
                    </span>
                </div>

                <div class="category-hero__meta-item">
                    <span class="category-hero__meta-icon" aria-hidden="true">
                        ✓
                    </span>

                    <span>
                        فیلتر دقیق و سریع
                    </span>
                </div>

                <div class="category-hero__meta-item">
                    <span class="category-hero__meta-icon" aria-hidden="true">
                        <span data-catalog-count>۰ محصول</span>
                    </span>
                </div>

            </div>

        </div>

        <div
            class="category-hero__visual"
            aria-hidden="true">

            <div class="category-hero__visual-card">
                <span class="category-hero__visual-label">
                    DISCOVER
                </span>

                <span class="category-hero__visual-line"></span>

                <strong>
                    محصولات مناسب
                    <br>
                    انتخاب شما
                </strong>
            </div>

        </div>

    </section>


    <!-- =========================================================
         FILTER PANEL
         بدون Drawer / Bottom Sheet / Sidebar
    ========================================================== -->
    <section
        class="category-filter-panel"
        data-catalog-filters
        aria-labelledby="category-filter-title">

        <div class="category-filter-panel__header">

            <div class="category-filter-panel__heading">

                <span class="category-filter-panel__eyebrow">
                    FILTER
                </span>

                <h2 id="category-filter-title">
                    فیلتر محصولات
                </h2>

                <p>
                    محصولات را بر اساس معیارهای موردنظر خود محدود کنید.
                </p>

            </div>

            <button
                type="button"
                class="category-filter-panel__reset"
                data-filter-reset>
                حذف همه
            </button>

        </div>


        <div class="category-filter-panel__body">

            <!-- =================================================
                 BASIC SELECT FILTERS
            ================================================== -->
            <div class="category-filter-panel__selects">

                <!-- Category -->
                <div class="category-filter-field">

                    <label
                        class="category-filter-field__label"
                        for="category-filter-category">
                        دسته‌بندی
                    </label>

                    <div class="category-filter-select">
                        <select
                            id="category-filter-category"
                            data-filter-category
                            aria-label="فیلتر دسته‌بندی">

                            <option value="">
                                همه دسته‌بندی‌ها
                            </option>

                        </select>

                        <span
                            class="category-filter-select__icon"
                            aria-hidden="true">
                            ⌄
                        </span>
                    </div>

                </div>


                <!-- Brand -->
                <div class="category-filter-field">

                    <label
                        class="category-filter-field__label"
                        for="category-filter-brand">
                        برند
                    </label>

                    <div class="category-filter-select">

                        <select
                            id="category-filter-brand"
                            data-filter-brand
                            aria-label="فیلتر برند">

                            <option value="">
                                همه برندها
                            </option>

                        </select>

                        <span
                            class="category-filter-select__icon"
                            aria-hidden="true">
                            ⌄
                        </span>

                    </div>

                </div>


                <!-- Product Type -->
                <div class="category-filter-field">

                    <label
                        class="category-filter-field__label"
                        for="category-filter-product-type">
                        نوع محصول
                    </label>

                    <div class="category-filter-select">

                        <select
                            id="category-filter-product-type"
                            data-filter-product-type
                            aria-label="فیلتر نوع محصول">

                            <option value="">
                                همه انواع محصول
                            </option>

                        </select>

                        <span
                            class="category-filter-select__icon"
                            aria-hidden="true">
                            ⌄
                        </span>

                    </div>

                </div>


                <!-- Sort -->
                <div class="category-filter-field">

                    <label
                        class="category-filter-field__label"
                        for="category-filter-sort">
                        مرتب‌سازی
                    </label>

                    <div class="category-filter-select">

                        <select
                            id="category-filter-sort"
                            data-filter-sort
                            aria-label="مرتب‌سازی محصولات">

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
                                منتخب
                            </option>

                        </select>

                        <span
                            class="category-filter-select__icon"
                            aria-hidden="true">
                            ⌄
                        </span>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 PRICE + STOCK
            ================================================== -->
            <div class="category-filter-panel__secondary">

                <div class="category-filter-price">

                    <div class="category-filter-field">

                        <label
                            class="category-filter-field__label"
                            for="category-filter-min-price">
                            حداقل قیمت
                        </label>

                        <div class="category-filter-input">

                            <input
                                id="category-filter-min-price"
                                type="number"
                                inputmode="numeric"
                                min="0"
                                data-filter-min-price
                                placeholder="حداقل قیمت"
                                aria-label="حداقل قیمت">

                            <span>
                                ریال
                            </span>

                        </div>

                    </div>


                    <div
                        class="category-filter-price__separator"
                        aria-hidden="true">
                        تا
                    </div>


                    <div class="category-filter-field">

                        <label
                            class="category-filter-field__label"
                            for="category-filter-max-price">
                            حداکثر قیمت
                        </label>

                        <div class="category-filter-input">

                            <input
                                id="category-filter-max-price"
                                type="number"
                                inputmode="numeric"
                                min="0"
                                data-filter-max-price
                                placeholder="حداکثر قیمت"
                                aria-label="حداکثر قیمت">

                            <span>
                                ریال
                            </span>

                        </div>

                    </div>

                </div>


                <label class="category-filter-stock">

                    <input
                        type="checkbox"
                        data-filter-stock>

                    <span class="category-filter-stock__box">
                        <span aria-hidden="true">✓</span>
                    </span>

                    <span class="category-filter-stock__content">
                        <strong>
                            فقط کالاهای موجود
                        </strong>

                        <small>
                            محصولات قابل سفارش را نمایش بده
                        </small>
                    </span>

                </label>

            </div>


            <!-- =================================================
                 DYNAMIC ATTRIBUTES
            ================================================== -->
            <div class="category-filter-attributes">

                <div class="category-filter-attributes__header">

                    <div>
                        <span>
                            ویژگی‌ها
                        </span>

                        <small>
                            انتخاب ویژگی‌های محصول
                        </small>
                    </div>

                </div>

                <div
                    class="category-filter-attributes__body"
                    data-filter-attributes>
                </div>

            </div>

        </div>


        <!-- =====================================================
             ACTIONS
        ====================================================== -->
        <div class="category-filter-panel__footer">

            <div class="category-filter-panel__hint">
                <span aria-hidden="true">⌕</span>

                <span>
                    فیلترها را انتخاب کنید و نتایج را ببینید.
                </span>
            </div>

            <div class="category-filter-panel__actions">

                <button
                    type="button"
                    class="category-filter-panel__reset category-filter-panel__reset--footer"
                    data-filter-reset>
                    پاک کردن فیلترها
                </button>

                <button
                    type="button"
                    class="category-filter-panel__apply"
                    data-filter-apply>
                    <span>
                        نمایش محصولات
                    </span>

                    <span aria-hidden="true">
                        ←
                    </span>
                </button>

            </div>

        </div>

    </section>


    <!-- =========================================================
         ACTIVE FILTERS
    ========================================================== -->
    <section
        class="category-active-filters"
        data-active-filters
        aria-label="فیلترهای فعال"
        aria-live="polite">
    </section>


    <!-- =========================================================
         CATALOG STATE
    ========================================================== -->
    <div
        class="catalog-state category-page__state"
        data-catalog-state
        aria-live="polite">
    </div>


    <!-- =========================================================
         PRODUCTS
    ========================================================== -->
    <section
        class="category-products"
        aria-label="محصولات دسته‌بندی">

        <div class="category-products__header">

            <div>
                <span class="category-products__eyebrow">
                    PRODUCTS
                </span>

                <h2>
                    محصولات این دسته
                </h2>
            </div>

            <span
                class="category-products__count"
                data-catalog-count>
                ۰ محصول
            </span>

        </div>

        <section
            class="catalog-grid category-page__grid"
            data-catalog-list
            aria-live="polite">
        </section>

    </section>

</main>

<?php include_once __DIR__ . '/../../../partials/footer.php'; ?>