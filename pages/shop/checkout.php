<?php

$pageTitle = 'تکمیل سفارش | نیواد';
include_once __DIR__ . '/../../partials/header.php';
?>

<main
    class="customer-shop checkout-page"
    data-customer-page="checkout"
    data-customer-checkout
    data-api-base="<?= htmlspecialchars($baseUrl . '/api/index.php', ENT_QUOTES, 'UTF-8') ?>"
    data-login-url="<?= htmlspecialchars($baseUrl . '/auth/login.php', ENT_QUOTES, 'UTF-8') ?>">

    <header class="checkout-page__head">
        <span class="checkout-page__eyebrow">خرید امن</span>
        <h1>تکمیل سفارش</h1>
        <p
            data-checkout-state
            role="status"
            aria-live="polite"
            aria-busy="true">
            در حال آماده‌سازی اطلاعات سفارش...
        </p>
    </header>

    <nav
        class="checkout-steps"
        aria-label="مراحل خرید">
        <ol>
            <li data-checkout-step="cart">سبد خرید</li>
            <li data-checkout-step="address">آدرس</li>
            <li data-checkout-step="shipping">ارسال</li>
            <li data-checkout-step="review">بازبینی</li>
            <li data-checkout-step="payment">پرداخت</li>
        </ol>
    </nav>

    <div class="checkout-page__layout">
        <section
            class="checkout-page__main"
            aria-label="اطلاعات Checkout">
            <div data-checkout-address
                aria-live="polite"
                aria-busy="true"></div>
            <div data-checkout-shipping
                aria-live="polite"
                aria-busy="true"></div>
            <div data-checkout-review
                aria-live="polite"
                aria-busy="true"></div>
            <div
                data-checkout-error
                role="alert"
                aria-live="assertive">
            </div>
        </section>

        <aside
            class="checkout-page__summary"
            aria-label="خلاصه سفارش">
            <div data-checkout-summary></div>
        </aside>
    </div>
</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>