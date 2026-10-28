<?php

$pageTitle = 'سبد خرید | نیواد';

include_once __DIR__ . '/../../partials/header.php';

$cartApiUrl = $baseUrl . '/api/index.php';
$loginUrl = $baseUrl . '/pages/auth/login.php';
?>

<main id="main-content" tabindex="-1"
    class="customer-shop cart-page"
    data-customer-page="cart"
    data-customer-cart
    data-api-base="<?= htmlspecialchars(
                        $cartApiUrl,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
    data-login-url="<?= htmlspecialchars(
                        $loginUrl,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>">

    <header class="customer-shop__head cart-page__head">
        <span class="customer-shop__eyebrow">
            فروشگاه
        </span>

        <h1>
            سبد خرید
        </h1>

        <p
            data-customer-state
            role="status"
            aria-live="polite"
            aria-busy="true"
            aria-atomic="true">
            در حال دریافت اطلاعات...
        </p>

        <strong
            data-customer-summary
            aria-live="polite">
        </strong>
    </header>

    <section
        class="customer-shop__list cart-page__list"
        data-customer-list
        aria-label="اقلام سبد خرید"
        aria-live="polite"
        aria-busy="true">
    </section>

    <div
        class="customer-shop__action cart-page__action"
        data-customer-action>
    </div>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>