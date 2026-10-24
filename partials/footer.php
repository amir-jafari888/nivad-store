</main>

<?php

$footerStoreUrl = url('/');

$footerFaqUrl = url(
    '/pages/content/faq.php'
);

$footerCompatibilityUrl = url(
    '/pages/store/compatibility.php'
);

$footerSupportUrl = url(
    '/pages/content/contact.php'
);

$footerAboutUrl = url(
    '/pages/content/about.php'
);

$footerShippingUrl = url(
    '/pages/content/shipping.php'
);

$footerReturnsUrl = url(
    '/pages/content/returns.php'
);

$footerPrivacyUrl = url(
    '/pages/content/privacy.php'
);

$footerTermsUrl = url(
    '/pages/content/terms.php'
);

?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-brand">
                <a
                    href="<?= e($footerStoreUrl) ?>"
                    class="footer-logo"
                    aria-label="دیجی‌استور - صفحه اصلی">
                    دیجی‌استور
                </a>

                <p>
                    فروشگاه گوشی و تجهیزات دیجیتال، برای انتخاب ساده‌تر و
                    آگاهانه‌تر.
                </p>
            </div>

            <div class="footer-links">
                <div class="footer-column">
                    <h2>فروشگاه</h2>

                    <ul>
                        <li>
                            <a href="<?= e(url('/products')) ?>">
                                گوشی موبایل
                            </a>
                        </li>

                        <li>
                            <a href="<?= e(url('/search')) ?>">
                                شارژ و کابل
                            </a>
                        </li>

                        <li>
                            <a href="<?= e(url('/search')) ?>">
                                قاب و محافظ
                            </a>
                        </li>

                        <li>
                            <a href="<?= e(url('/search')) ?>">
                                پاوربانک
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h2>راهنما</h2>

                    <ul>
                        <li>
                            <a href="<?= e($footerFaqUrl) ?>">
                                سوالات متداول
                            </a>
                        </li>

                        <li>
                            <a href="<?= e($footerCompatibilityUrl) ?>">
                                سازگاری دستگاه
                            </a>
                        </li>

                        <li>
                            <a href="<?= e($footerSupportUrl) ?>">
                                پشتیبانی
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h2>اطلاعات</h2>

                    <ul>
                        <li>
                            <a href="<?= e($footerAboutUrl) ?>">
                                درباره ما
                            </a>
                        </li>

                        <li>
                            <a href="<?= e($footerSupportUrl) ?>">
                                تماس با ما
                            </a>
                        </li>

                        <li>
                            <a href="<?= e($footerShippingUrl) ?>">
                                ارسال
                            </a>
                        </li>

                        <li>
                            <a href="<?= e($footerReturnsUrl) ?>">
                                مرجوعی
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h2>قوانین</h2>

                    <div class="footer-bottom-links">
                        <a href="<?= e($footerPrivacyUrl) ?>">
                            حریم خصوصی
                        </a>

                        <a href="<?= e($footerTermsUrl) ?>">
                            شرایط استفاده
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>
                تمامی حقوق محفوظ است.
            </p>

            <div class="footer-bottom-links">
                <a href="<?= e($footerPrivacyUrl) ?>">
                    حریم خصوصی
                </a>

                <a href="<?= e($footerTermsUrl) ?>">
                    شرایط استفاده
                </a>
            </div>
        </div>
    </div>
</footer>

<div
    class="cart-drawer"
    data-cart-drawer
    aria-hidden="true">

    <div
        class="cart-drawer__overlay"
        data-cart-drawer-close></div>

    <aside
        class="cart-drawer__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cart-drawer-title">

        <div class="cart-drawer__header">
            <div>
                <span class="cart-drawer__eyebrow">
                    سبد خرید
                </span>

                <h2
                    id="cart-drawer-title"
                    class="cart-drawer__title">
                    سبد خرید شما
                </h2>
            </div>

            <button
                class="cart-drawer__close"
                type="button"
                aria-label="بستن سبد خرید"
                data-cart-drawer-close>

                <svg
                    class="icon"
                    viewBox="0 0 24 24"
                    aria-hidden="true">

                    <path
                        d="M6 6l12 12M18 6 6 18"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round" />

                </svg>
            </button>
        </div>

        <div class="cart-drawer__body">
            <div
                class="cart-drawer__items"
                data-cart-drawer-items></div>

            <div
                class="cart-drawer__empty"
                data-cart-drawer-empty
                hidden>

                <div
                    class="cart-drawer__empty-icon"
                    aria-hidden="true">

                    <svg
                        class="icon"
                        viewBox="0 0 24 24">

                        <path
                            d="M6 8h12l1 12H5L6 8Z"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linejoin="round" />

                        <path
                            d="M9 8a3 3 0 0 1 6 0"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round" />

                    </svg>
                </div>

                <strong>
                    سبد خریدت هنوز خالیه
                </strong>

                <span>
                    محصول موردنظرت رو انتخاب کن و به سبد خرید اضافه کن.
                </span>

                <button
                    class="cart-drawer__empty-button"
                    type="button"
                    data-cart-drawer-close>
                    ادامه خرید
                </button>
            </div>
        </div>

        <div
            class="cart-drawer__footer"
            data-cart-drawer-footer>

            <div class="cart-drawer__summary">
                <span>
                    جمع کل
                </span>

                <strong data-cart-drawer-total>
                    ۰ تومان
                </strong>
            </div>

            <button
                class="cart-drawer__checkout"
                type="button"
                data-cart-drawer-checkout>
                مشاهده سبد خرید
            </button>

            <button
                class="cart-drawer__continue"
                type="button"
                data-cart-drawer-close>
                ادامه خرید
            </button>
        </div>
    </aside>
</div>

<script>
    (() => {
        const existingTarget = document.getElementById("main-content");
        if (existingTarget) {
            if (!existingTarget.hasAttribute("tabindex")) existingTarget.tabIndex = -1;
            return;
        }

        const main = document.querySelector("main, [role='main']");
        if (!main) return;

        if (!main.id) {
            main.id = "main-content";
            if (!main.hasAttribute("tabindex")) main.tabIndex = -1;
            return;
        }

        const target = document.createElement("span");
        target.id = "main-content";
        target.tabIndex = -1;
        target.className = "sr-only";
        target.setAttribute("role", "region");
        target.setAttribute("aria-label", "محتوای اصلی");
        target.textContent = "محتوای اصلی";
        main.before(target);
    })();
</script>

<script
    type="module"
    src="<?= e(asset('js/main.js')) ?>"></script>
<script src="<?= e('swiper/swiper-bundle.min.js') ?>"></script>

</body>

</html>