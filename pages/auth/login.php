<?php

$pageTitle = 'ظˆط±ظˆط¯ | ظ†غŒظˆط§ط¯';
include_once __DIR__ . '/../../partials/header.php';
?>

<main
    class="auth-page"
    data-auth-page
    data-redirect="/nivad-store/">

    <header class="auth-page__head">
        <h1>ظˆط±ظˆط¯ ط¨ظ‡ ط­ط³ط§ط¨</h1>
        <p>
            ط¨ط±ط§غŒ ط§ط¯ط§ظ…ظ‡ ظˆط§ط±ط¯ ط­ط³ط§ط¨ ع©ط§ط±ط¨ط±غŒ ط®ظˆط¯ ط´ظˆغŒط¯.
        </p>
    </header>

    <section class="auth-card">

        <form
            class="auth-form"
            data-auth-login-form>

            <label>
                ط§غŒظ…غŒظ„ غŒط§ ط´ظ…ط§ط±ظ‡ ظ…ظˆط¨ط§غŒظ„
                <input
                    type="text"
                    name="identifier"
                    autocomplete="username"
                    required>
            </label>

            <label>
                ط±ظ…ط² ط¹ط¨ظˆط±
                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required>
            </label>

            <button type="submit">
                ظˆط±ظˆط¯
            </button>

            <p
                class="auth-message"
                data-auth-message
                aria-live="polite"></p>

        </form>

        <div class="auth-links">
            <a href="/nivad-store/pages/auth/register.php">
                ط«ط¨طھâ€Œظ†ط§ظ…
            </a>

            <a href="/nivad-store/pages/auth/forgot-password.php">
                ظپط±ط§ظ…ظˆط´غŒ ط±ظ…ط² ط¹ط¨ظˆط±
            </a>

            <a href="/nivad-store/pages/auth/verify-email.php">
                طھط£غŒغŒط¯ ط§غŒظ…غŒظ„
            </a>
        </div>

    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
