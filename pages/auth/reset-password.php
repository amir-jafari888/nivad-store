<?php

$pageTitle = 'طھط؛غŒغŒط± ط±ظ…ط² ط¹ط¨ظˆط± | ظ†غŒظˆط§ط¯';
include_once __DIR__ . '/../../partials/header.php';
?>

<main class="auth-page" data-auth-page>

    <header class="auth-page__head">
        <h1>طھط؛غŒغŒط± ط±ظ…ط² ط¹ط¨ظˆط±</h1>
        <p>
            طھظˆع©ظ† ط¨ط§ط²غŒط§ط¨غŒ ظˆ ط±ظ…ط² ط¹ط¨ظˆط± ط¬ط¯غŒط¯ ط±ط§ ظˆط§ط±ط¯ ع©ظ†غŒط¯.
        </p>
    </header>

    <section class="auth-card">

        <form
            class="auth-form"
            data-auth-reset-form>

            <label>
                طھظˆع©ظ† ط¨ط§ط²غŒط§ط¨غŒ
                <input
                    type="text"
                    name="token"
                    autocomplete="off"
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

            <button type="submit">
                طھط؛غŒغŒط± ط±ظ…ط² ط¹ط¨ظˆط±
            </button>

            <p
                class="auth-message"
                data-auth-message
                aria-live="polite"></p>

        </form>

        <div class="auth-links">
            <a href="/nivad-store/pages/auth/login.php">
                ط¨ط§ط²ع¯ط´طھ ط¨ظ‡ ظˆط±ظˆط¯
            </a>
        </div>

    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
