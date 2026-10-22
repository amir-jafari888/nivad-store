<?php

$pageTitle = 'ط­ط³ط§ط¨ ع©ط§ط±ط¨ط±غŒ | ظ†غŒظˆط§ط¯';


include_once __DIR__ . '/../../partials/header.php';
?>

<main
    class="customer-shop account-page"
    data-account-page
    data-api-base="<?= $baseUrl ?>/api/index.php">

    <header class="customer-shop__head">
        <span class="customer-shop__eyebrow">
            ط­ط³ط§ط¨ ع©ط§ط±ط¨ط±غŒ
        </span>

        <h1>ط­ط³ط§ط¨ ظ…ظ†</h1>

        <p data-account-state>
            ط¯ط± ط­ط§ظ„ ط¯ط±غŒط§ظپطھ ط§ط·ظ„ط§ط¹ط§طھ...
        </p>
    </header>

    <section class="account-grid">

        <article
            class="customer-shop__panel"
            data-account-security>
        </article>

        <article
            class="customer-shop__panel"
            data-account-verification>
        </article>

    </section>

    <section class="customer-shop__panel">

        <div class="account-section-head">
            <div>
                <h2>ظ†ط´ط³طھâ€Œظ‡ط§غŒ ظپط¹ط§ظ„</h2>
                <p>
                    ظ†ط´ط³طھâ€Œظ‡ط§غŒ ظپط¹ط§ظ„ ط­ط³ط§ط¨ ط®ظˆط¯ ط±ط§ ظ…ط¯غŒط±غŒطھ ع©ظ†غŒط¯.
                </p>
            </div>

            <button
                type="button"
                data-logout-other-sessions>
                ط¨ط³طھظ† ظ†ط´ط³طھâ€Œظ‡ط§غŒ ط¯غŒع¯ط±
            </button>
        </div>

        <div
            data-account-sessions
            class="account-sessions">
        </div>

    </section>

    <section class="customer-shop__panel">

        <h2>طھط؛غŒغŒط± ط±ظ…ط² ط¹ط¨ظˆط±</h2>

        <form
            data-password-form
            class="account-password-form">

            <label>
                ط±ظ…ط² ط¹ط¨ظˆط± ظپط¹ظ„غŒ
                <input
                    type="password"
                    name="current_password"
                    autocomplete="current-password"
                    required>
            </label>

            <label>
                ط±ظ…ط² ط¹ط¨ظˆط± ط¬ط¯غŒط¯
                <input
                    type="password"
                    name="new_password"
                    autocomplete="new-password"
                    required>
            </label>

            <label>
                طھع©ط±ط§ط± ط±ظ…ط² ط¹ط¨ظˆط± ط¬ط¯غŒط¯
                <input
                    type="password"
                    name="new_password_confirmation"
                    autocomplete="new-password"
                    required>
            </label>

            <button
                type="submit"
                class="customer-shop__button">
                طھط؛غŒغŒط± ط±ظ…ط² ط¹ط¨ظˆط±
            </button>

        </form>

        <p
            data-account-message
            aria-live="polite">
        </p>

    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
