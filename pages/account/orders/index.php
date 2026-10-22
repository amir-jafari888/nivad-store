<?php

$pageTitle = 'سفارش‌های من | ظ†غŒظˆط§ط¯';

include_once __DIR__ . '/../../../partials/header.php';
?>

<main
    class="customer-shop"
    data-customer-page
    data-customer-page="orders"
    data-api-base="<?= $baseUrl ?>/api/index.php"
    data-login-url="../../auth/login.php">

    <header class="customer-shop__head">
        <span class="customer-shop__eyebrow">
            ط­ط³ط§ط¨ ع©ط§ط±ط¨ط±غŒ
        </span>

        <h1>سفارش‌های من</h1>

        <p data-customer-state>
            ط¯ط± ط­ط§ظ„ ط¯ط±غŒط§ظپطھ ط§ط·ظ„ط§ط¹ط§طھ...
        </p>

        <strong data-customer-summary></strong>
    </header>

    <section
        class="customer-shop__list"
        data-customer-list
        aria-live="polite">
    </section>

    <div
        class="customer-shop__action"
        data-customer-action>
    </div>

</main>

<?php include_once __DIR__ . '/../../../partials/footer.php'; ?>
