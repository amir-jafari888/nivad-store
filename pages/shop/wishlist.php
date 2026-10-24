<?php

declare(strict_types=1);

$pageTitle = 'علاقه‌مندی‌ها | NIVAD';

include_once __DIR__ . '/../../partials/header.php';
?>

<main
    class="customer-shop"
    data-customer-page="wishlist"
    data-customer-wishlist
    data-api-base="<?= htmlspecialchars(
                        $baseUrl . '/api/index.php',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
    data-login-url="<?= htmlspecialchars(
                        $baseUrl . '/pages/auth/login.php',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>">

    <header class="customer-shop__head">
        <span class="customer-shop__eyebrow">
            حساب کاربری
        </span>

        <h1>
            علاقه‌مندی‌ها
        </h1>

        <p
            data-customer-state
            aria-live="polite">
            در حال دریافت اطلاعات...
        </p>

        <strong
            data-customer-summary
            aria-live="polite">
        </strong>
    </header>

    <section
        class="customer-shop__list"
        data-customer-list
        aria-live="polite"
        aria-busy="true">
    </section>

    <div
        class="customer-shop__action"
        data-customer-action>
    </div>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>