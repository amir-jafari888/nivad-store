<?php

$pageTitle = 'طھط£غŒغŒط¯ ط§غŒظ…غŒظ„ | ظ†غŒظˆط§ط¯';
include_once __DIR__ . '/../../partials/header.php';
?>

<main class="auth-page" data-auth-page>

    <header class="auth-page__head">
        <h1>طھط£غŒغŒط¯ ط§غŒظ…غŒظ„</h1>
        <p>
            طھظˆع©ظ† طھط£غŒغŒط¯ ط§غŒظ…غŒظ„ ط±ط§ ظˆط§ط±ط¯ ع©ظ†غŒط¯.
        </p>
    </header>

    <section class="auth-card">

        <form
            class="auth-form"
            data-auth-verify-form>

            <label>
                طھظˆع©ظ† طھط£غŒغŒط¯
                <input
                    type="text"
                    name="token"
                    autocomplete="off"
                    required>
            </label>

            <button type="submit">
                طھط£غŒغŒط¯ ط§غŒظ…غŒظ„
            </button>

            <p
                class="auth-message"
                data-auth-message
                aria-live="polite"></p>

        </form>

        <form
            class="auth-form"
            data-auth-resend-form
            style="margin-top:24px;">

            <label>
                ط§غŒظ…غŒظ„ ط¨ط±ط§غŒ ط§ط±ط³ط§ظ„ ظ…ط¬ط¯ط¯
                <input
                    type="email"
                    name="email"
                    autocomplete="email"
                    required>
            </label>

            <button type="submit">
                ط§ط±ط³ط§ظ„ ظ…ط¬ط¯ط¯ طھط£غŒغŒط¯ ط§غŒظ…غŒظ„
            </button>

        </form>

        <div class="auth-links">
            <a href="/nivad-store/pages/auth/login.php">
                ط¨ط§ط²ع¯ط´طھ ط¨ظ‡ ظˆط±ظˆط¯
            </a>
        </div>

    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
