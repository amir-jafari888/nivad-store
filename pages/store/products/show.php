<?php

declare(strict_types=1);

$slug = trim(
    (string)($_GET['slug'] ?? '')
);

$product =
    $slug !== ''
    ? (new ProductService())->findBySlug($slug)
    : null;

$watchVariant =
    $product instanceof Product && $product->status === 'active'
        ? (new ProductVariantService())->findDefaultByProduct($product->id)
        : null;

if (
    $product instanceof Product &&
    $product->status === 'active'
) {
    $pageTitle =
        trim(
            (string)(
                $product->metaTitle ??
                ''
            )
        );

    if ($pageTitle === '') {
        $pageTitle =
            $product->name .
            ' | نیواد';
    }

    $metaDescription =
        trim(
            (string)(
                $product->metaDescription ??
                ''
            )
        );

    if ($metaDescription === '') {
        $metaDescription =
            trim(
                (string)(
                    $product->shortDescription ??
                    ''
                )
            );
    }

    $canonicalUrl =
        url(
            '/products/' .
                rawurlencode(
                    $product->slug
                )
        );
} else {
    $pageTitle =
        'جزئیات محصول | نیواد';

    $metaDescription = '';

    $canonicalUrl = null;
}

$robots = 'index,follow';

include_once __DIR__ .
    '/../../../partials/header.php';
?>

<main id="main-content" tabindex="-1"
    class="product-detail-page"
    data-product-detail
    data-product-slug="<?= e(
                            $product instanceof Product
                                ? $product->slug
                                : $slug
                        ) ?>"
    data-api-base="<?= e(
                        $baseUrl . '/api/index.php'
                    ) ?>"
    data-product-id="<?= e(
                            $product instanceof Product
                                ? (string)$product->id
                                : '0'
                        ) ?>">

    <div
        class="product-detail-shell">

        <div
            class="product-detail-state"
            data-product-detail-state
            aria-live="polite">
            در حال دریافت اطلاعات محصول...
        </div>

        <section
            class="product-detail-content"
            data-product-detail-content
            hidden>

            <div class="product-detail-main">

                <section
                    class="product-detail-gallery"
                    aria-label="تصاویر محصول">

                    <div
                        class="product-detail-gallery__main">

                        <img
                            data-product-main-image
                            src=""
                            alt=""
                            decoding="async"
                            loading="eager">

                    </div>

                    <div
                        class="product-detail-gallery__thumbs"
                        data-product-gallery
                        role="list"
                        aria-label="تصاویر محصول">
                    </div>

                </section>

                <section
                    class="product-detail-info"
                    aria-labelledby="product-detail-title">

                    <div
                        class="product-detail-meta">

                        <span
                            class="product-detail-brand"
                            data-product-brand>
                        </span>

                        <span
                            class="product-detail-category"
                            data-product-category>
                        </span>

                    </div>

                    <h1
                        id="product-detail-title"
                        class="product-detail-title"
                        data-product-title>
                        جزئیات محصول
                    </h1>

                    <p
                        class="product-detail-short"
                        data-product-short>
                    </p>

                    <div
                        class="product-detail-price"
                        data-product-price>
                    </div>

                    <div
                        class="product-detail-availability"
                        data-product-availability
                        aria-live="polite">
                    </div>

                    <section
                        class="product-detail-variants"
                        data-product-variants-section
                        hidden>

                        <h2>
                            انتخاب مدل
                        </h2>

                        <div
                            class="product-detail-variants__list"
                            data-product-variants>
                        </div>

                    </section>

                    <div
                        class="product-detail-actions">

                        <button
                            type="button"
                            class="product-detail-cart"
                            data-product-add-cart
                            disabled>
                            افزودن به سبد خرید
                        </button>

                        <button
                            type="button"
                            class="product-detail-wishlist"
                            data-product-wishlist
                            data-wishlist-variant-id=""
                            aria-pressed="false"
                            disabled>
                            افزودن به علاقه‌مندی
                        </button>

                    </div>

                </section>

            </div>

            <section
                class="product-detail-description"
                data-product-description-section
                hidden>

                <h2>
                    توضیحات محصول
                </h2>

                <div
                    data-product-description>
                </div>

            </section>

            <section
                class="product-detail-compatibility"
                data-product-compatibility-section
                hidden>

                <div class="product-detail-section-heading">

                    <span>
                        سازگاری
                    </span>

                    <h2>
                        سازگاری محصول
                    </h2>

                </div>

                <div
                    class="product-detail-compatibility__list"
                    data-product-compatibility>
                </div>

            </section>

            <section
                class="product-detail-smart"
                data-product-smart-section
                hidden>

                <div class="product-detail-section-heading">

                    <span>
                        هوشمند
                    </span>

                    <h2>
                        پیشنهادهای تکمیل خرید
                    </h2>

                    <p>
                        محصولاتی که می‌توانند ست خرید شما را کامل‌تر کنند.
                    </p>

                </div>

                <section
                    class="product-detail-complete-setup"
                    data-complete-setup
                    aria-labelledby="complete-setup-title">

                    <div
                        class="complete-setup__state"
                        data-complete-setup-state
                        aria-live="polite">
                        در حال بررسی پیشنهادهای تکمیل خرید...
                    </div>

                    <div
                        class="complete-setup__content"
                        data-complete-setup-content
                        hidden>

                        <div
                            class="complete-setup__candidates"
                            data-complete-setup-candidates>
                        </div>

                        <div
                            class="complete-setup__budget">

                            <label
                                for="complete-setup-budget">
                                بودجه اختیاری
                            </label>

                            <input
                                id="complete-setup-budget"
                                type="number"
                                min="0"
                                inputmode="decimal"
                                data-complete-setup-budget
                                placeholder="مثلاً ۵۰۰۰۰۰۰۰">

                            <span
                                data-complete-setup-budget-state
                                aria-live="polite">
                            </span>

                        </div>

                        <div
                            class="complete-setup__summary"
                            data-complete-setup-summary
                            aria-live="polite">
                        </div>

                        <div
                            class="complete-setup__actions">

                            <button
                                type="button"
                                class="complete-setup__cart"
                                data-complete-setup-cart
                                disabled>
                                افزودن پیشنهادهای انتخاب‌شده به سبد
                            </button>

                            <button
                                type="button"
                                class="complete-setup__reset"
                                data-complete-setup-reset>
                                پاک کردن انتخاب‌ها
                            </button>

                        </div>

                        <div
                            class="complete-setup__message"
                            data-complete-setup-message
                            aria-live="polite">
                        </div>

                    </div>

                    <div
                        class="complete-setup__error"
                        data-complete-setup-error
                        role="alert"
                        hidden>
                    </div>

                    <button
                        type="button"
                        class="complete-setup__retry"
                        data-complete-setup-retry
                        hidden>
                        تلاش مجدد
                    </button>

                </section>

                <div
                    class="product-detail-related"
                    data-product-related>
                </div>

            </section>

            <section
                class="product-detail-feedback">

                <div
                    class="product-detail-section-heading">

                    <span>
                        اعتماد کاربران
                    </span>

                    <h2>
                        دیدگاه‌ها و پرسش‌ها
                    </h2>

                </div>

                <div class="product-detail-feedback-grid">

                    <section
                        class="product-detail-feedback-card">

                        <h3>
                            دیدگاه کاربران
                        </h3>

                        <div
                            data-product-review-summary
                            aria-live="polite">
                            در حال محاسبه امتیاز...
                        </div>

                        <div
                            class="product-detail-rating-summary"
                            data-product-review-summary
                            aria-live="polite">
                            <strong>۰</strong>
                            <span>از ۵ · ۰ دیدگاه</span>
                        </div>

                        <div
                            data-product-reviews
                            aria-live="polite">
                            در حال دریافت دیدگاه‌ها...
                        </div>

                        <form
                            data-product-review-form
                            novalidate>

                            <input
                                type="number"
                                name="rating"
                                min="1"
                                max="5"
                                required
                                placeholder="امتیاز ۱ تا ۵">

                            <input
                                type="text"
                                name="title"
                                maxlength="255"
                                placeholder="عنوان دیدگاه">

                            <textarea
                                name="body"
                                required
                                placeholder="دیدگاه شما">
                            </textarea>

                            <button
                                type="submit"
                                data-review-submit>
                                ثبت دیدگاه
                            </button>

                            <p
                                data-review-message
                                aria-live="polite">
                            </p>

                        </form>

                    </section>

                    <section
                        class="product-detail-feedback-card">

                        <h3>
                            پرسش‌های محصول
                        </h3>

                        <div
                            data-product-questions
                            aria-live="polite">
                            در حال دریافت پرسش‌ها...
                        </div>

                        <form
                            data-product-question-form
                            novalidate>

                            <textarea
                                name="question"
                                required
                                placeholder="سؤال خود را بنویسید">
                            </textarea>

                            <button
                                type="submit"
                                data-question-submit>
                                ثبت سؤال
                            </button>

                            <p
                                data-question-message
                                aria-live="polite">
                            </p>

                        </form>

                    </section>

                </div>

            </section>

        </section>

    </div>

    <section
        data-recent-device
        data-recent-device-state="empty"
        aria-labelledby="recent-device-title">
        <div data-recent-device-content hidden>
            <span id="recent-device-title">
                دستگاه انتخاب‌شده
            </span>

            <strong data-recent-device-name></strong>

            <button
                type="button"
                data-recent-device-change>
                تغییر دستگاه
            </button>
        </div>

        <div
            data-recent-device-empty
            role="status">
            دستگاهی انتخاب نشده است.
        </div>
    </section>

    <section
        class="product-alternative"
        data-alternative
        data-product-id="<?= e(
                                $product instanceof Product
                                    ? (string)$product->id
                                    : '0'
                            ) ?>"
        data-api-base="<?= e(
                            url('/api/index.php')
                        ) ?>"
        data-product-base="<?= e(
                                url('/products')
                            ) ?>"
        aria-labelledby="product-alternative-title">

        <div
            class="product-detail-alternative__header">

            <div>
                <span
                    class="product-detail-alternative__eyebrow">
                    انتخاب هوشمند
                </span>

                <h2
                    id="product-alternative-title">
                    اگر این محصول مناسب شما نیست
                </h2>

                <p>
                    گزینه‌های جایگزین سازگار را بررسی کنید.
                </p>
            </div>

        </div>

        <div
            class="product-alternative__state"
            data-alternative-state
            aria-live="polite">
            در حال بررسی گزینه‌های جایگزین...
        </div>

        <button
            type="button"
            class="product-alternative__retry"
            data-alternative-retry
            hidden>
            تلاش دوباره
        </button>

        <div
            class="product-alternative__content"
            data-alternative-content
            hidden>
            <div
                class="product-alternative__list"
                data-alternative-list></div>
        </div>

        </div>

    </section>

</main>

<div
    class="product-watch-actions"
    data-price-stock-watch
    data-product-id="<?= e($product instanceof Product ? (string)$product->id : '') ?>"
    data-variant-id="<?= e($watchVariant instanceof ProductVariant ? (string)$watchVariant->id : '') ?>"
    data-current-price="<?= e($watchVariant instanceof ProductVariant ? $watchVariant->price : '') ?>"
    data-previous-price="<?= e($watchVariant instanceof ProductVariant && $watchVariant->compareAtPrice !== null ? $watchVariant->compareAtPrice : '') ?>"
    data-stock-status="unknown">
    <button
        type="button"
        class="product-watch-actions__button"
        data-price-drop-watch
        hidden>
        کاهش قیمت را به من اطلاع بده
    </button>

    <button
        type="button"
        class="product-watch-actions__button"
        data-back-in-stock-watch
        hidden>
        موجود شدن را به من اطلاع بده
    </button>

    <p
        class="product-watch-actions__status"
        data-watch-status
        aria-live="polite"
        hidden></p>
</div>

<?php
include_once __DIR__ .
    '/../../../partials/footer.php';
?>