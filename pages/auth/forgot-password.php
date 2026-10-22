<?php

$pageTitle = 'ط¨ط§ط²غŒط§ط¨غŒ ط±ظ…ط² ط¹ط¨ظˆط± | ظ†غŒظˆط§ط¯';

include_once __DIR__ . '/../../partials/header.php';
?>

<main class="auth-page" data-auth-page>

    <header class="auth-page__head">
        <h1>ط¨ط§ط²غŒط§ط¨غŒ ط±ظ…ط² ط¹ط¨ظˆط±</h1>
        <p>
            ط§غŒظ…غŒظ„ ط­ط³ط§ط¨ ط®ظˆط¯ ط±ط§ ظˆط§ط±ط¯ ع©ظ†غŒط¯.
        </p>
    </header>

    <section class="auth-card">

        <form
            class="auth-form"
            data-auth-forgot-form>

            <label>
                ط§غŒظ…غŒظ„
                <input
                    type="email"
                    name="email"
                    autocomplete="email"
                    required>
            </label>

            <button type="submit">
                ط§ط±ط³ط§ظ„ ط¯ط±ط®ظˆط§ط³طھ ط¨ط§ط²غŒط§ط¨غŒ
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

            <a href="/nivad-store/pages/auth/reset-password.php">
                ظˆط±ظˆط¯ طھظˆع©ظ† ط¨ط§ط²غŒط§ط¨غŒ
            </a>
        </div>

    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
