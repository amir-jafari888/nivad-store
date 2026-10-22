<?php

$pageTitle = 'ط«ط¨طھâ€Œظ†ط§ظ… | ظ†غŒظˆط§ط¯';

include_once __DIR__ . '/../../partials/header.php';
?>

<main class="auth-page" data-auth-page>

    <header class="auth-page__head">
        <h1>ط³ط§ط®طھ ط­ط³ط§ط¨ ع©ط§ط±ط¨ط±غŒ</h1>
        <p>
            ط§ط·ظ„ط§ط¹ط§طھ ط®ظˆط¯ ط±ط§ ط¨ط±ط§غŒ ط§غŒط¬ط§ط¯ ط­ط³ط§ط¨ ظˆط§ط±ط¯ ع©ظ†غŒط¯.
        </p>
    </header>

    <section class="auth-card">

        <form
            class="auth-form"
            data-auth-register-form>

            <label>
                ظ†ط§ظ…
                <input
                    type="text"
                    name="first_name"
                    autocomplete="given-name"
                    required>
            </label>

            <label>
                ظ†ط§ظ… ط®ط§ظ†ظˆط§ط¯ع¯غŒ
                <input
                    type="text"
                    name="last_name"
                    autocomplete="family-name"
                    required>
            </label>

            <label>
                ط§غŒظ…غŒظ„
                <input
                    type="email"
                    name="email"
                    autocomplete="email"
                    required>
            </label>

            <label>
                ط´ظ…ط§ط±ظ‡ ظ…ظˆط¨ط§غŒظ„
                <input
                    type="tel"
                    name="phone"
                    autocomplete="tel"
                    inputmode="numeric"
                    required>
            </label>

            <label>
                ط±ظ…ط² ط¹ط¨ظˆط±
                <input
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    required>
            </label>

            <label>
                طھع©ط±ط§ط± ط±ظ…ط² ط¹ط¨ظˆط±
                <input
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    required>
            </label>

            <button type="submit">
                ط«ط¨طھâ€Œظ†ط§ظ…
            </button>

            <p
                class="auth-message"
                data-auth-message
                aria-live="polite"></p>

        </form>

        <div class="auth-links">
            <a href="/nivad-store/pages/auth/login.php">
                ظ‚ط¨ظ„ط§ظ‹ ط­ط³ط§ط¨ ط¯ط§ط±غŒط¯طں ظˆط±ظˆط¯
            </a>
        </div>

    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
