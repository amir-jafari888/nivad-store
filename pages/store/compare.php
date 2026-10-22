<?php

$pageTitle = 'مقایسه محصولات | نیواد';

include_once __DIR__ . '/../../../partials/header.php';
?>

<main
    class="comparison-page"
    data-comparison-page
    data-api-base="<?= e(url('/api/index.php')) ?>"
    data-product-base="<?= e(url('/products')) ?>">

    <div class="comparison-shell">

        <header class="comparison-header">

            <div>

                <span class="comparison-eyebrow">
                    مقایسه هوشمند
                </span>

                <h1>
                    محصولات را کنار هم مقایسه کنید
                </h1>

                <p>
                    مشخصات، قیمت، موجودی و سازگاری
                    محصولات انتخاب‌شده را در یک نگاه بررسی کنید.
                </p>

            </div>

            <a
                class="comparison-back"
                href="<?= e(
                            url(
                                '/pages/store/products/index.php'
                            )
                        ) ?>">
                بازگشت به محصولات
            </a>

        </header>

        <section
            class="comparison-state"
            data-comparison-state
            aria-live="polite">
            در حال آماده‌سازی مقایسه...
        </section>

        <section
            class="comparison-empty"
            data-comparison-empty
            hidden
            aria-labelledby="comparison-empty-title">

            <h2 id="comparison-empty-title">
                هنوز محصولی برای مقایسه انتخاب نشده است
            </h2>

            <p>
                از صفحه محصولات یا جزئیات محصول،
                گزینه «مقایسه» را انتخاب کنید.
            </p>

            <a
                href="<?= e(
                            url(
                                '/pages/store/products/index.php'
                            )
                        ) ?>">
                مشاهده محصولات
            </a>

        </section>

        <section
            class="comparison-content"
            data-comparison-content
            hidden>

            <div class="comparison-toolbar">

                <strong data-comparison-count>
                    ۰ محصول
                </strong>

                <button
                    type="button"
                    class="comparison-clear"
                    data-comparison-clear>
                    پاک کردن مقایسه
                </button>

            </div>

            <div
                class="comparison-products"
                data-comparison-products
                aria-label="محصولات انتخاب‌شده">
            </div>

            <section
                class="comparison-decision"
                aria-labelledby="comparison-decision-title">

                <div>

                    <span class="comparison-section-kicker">
                        Decision Support
                    </span>

                    <h2 id="comparison-decision-title">
                        جمع‌بندی تصمیم
                    </h2>

                </div>

                <div data-comparison-verdict></div>

            </section>

            <section
                class="comparison-specifications"
                aria-labelledby="comparison-specifications-title">

                <div class="comparison-section-head">

                    <div>

                        <span class="comparison-section-kicker">
                            Specifications
                        </span>

                        <h2 id="comparison-specifications-title">
                            مقایسه مشخصات
                        </h2>

                    </div>

                    <span
                        data-comparison-difference-summary>
                    </span>

                </div>

                <div
                    class="comparison-table-wrap"
                    role="region"
                    aria-label="جدول مقایسه مشخصات"
                    tabindex="0">

                    <table class="comparison-table">

                        <thead
                            data-comparison-table-head>
                        </thead>

                        <tbody
                            data-comparison-table-body>
                        </tbody>

                    </table>

                </div>

            </section>

            <section
                class="comparison-compatibility"
                aria-labelledby="comparison-compatibility-title">

                <div class="comparison-section-head">

                    <div>

                        <span class="comparison-section-kicker">
                            Compatibility
                        </span>

                        <h2 id="comparison-compatibility-title">
                            سازگاری
                        </h2>

                    </div>

                    <p>
                        شناسه دستگاه را برای بررسی سازگاری وارد کنید.
                    </p>

                </div>

                <form
                    data-comparison-compatibility-form
                    novalidate>

                    <label
                        for="comparison-device-id">
                        شناسه دستگاه
                    </label>

                    <input
                        id="comparison-device-id"
                        name="device_id"
                        inputmode="numeric"
                        pattern="[0-9]+"
                        data-comparison-device-id>

                    <button type="submit">
                        بررسی سازگاری
                    </button>

                </form>

                <div
                    class="comparison-compatibility-grid"
                    data-comparison-compatibility-grid
                    aria-live="polite">
                </div>

            </section>

        </section>

    </div>

</main>

<?php
include_once __DIR__ .
    '/../../../partials/footer.php';
?>