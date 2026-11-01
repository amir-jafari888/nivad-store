<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

$adminAuth =
    new AdminAuthService();

if (
    $adminAuth->validateCurrentSession()
) {
    Response::redirect(
        url('/admin')
    );
}

$error = null;
$csrfToken = Csrf::token();

if (Request::isPost()) {
    $csrfTokenInput =
        Request::post('csrf_token');

    if (
        !Csrf::verify(
            is_string($csrfTokenInput)
                ? $csrfTokenInput
                : null
        )
    ) {
        $error =
            'درخواست نامعتبر است. دوباره تلاش کنید.';
    } else {
        $identifier =
            Request::post('identifier');

        $password =
            Request::post('password');

        $errors =
            AdminAuthValidator::login(
                $identifier,
                $password
            );

        if ($errors !== []) {
            $error =
                'اطلاعات ورود مدیر نامعتبر است.';
        } else {
            try {
                $adminAuth->authenticate(
                    trim(
                        (string) $identifier
                    ),
                    (string) $password
                );

                Response::redirect(
                    url('/admin')
                );
            } catch (
                AdminAuthenticationException $exception
            ) {
                $error =
                    $exception->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود مدیر | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>

<body class="admin-auth-page">

    <main id="main-content" class="admin-auth-main" tabindex="-1">
        <header class="admin-auth-heading">
            <h1>ورود به پنل مدیریت</h1>
            <p>برای ادامه، با حساب مدیر خود وارد شوید.</p>
        </header>

        <section class="admin-auth-card" aria-labelledby="admin-auth-card-title">
            <h2 id="admin-auth-card-title" class="admin-auth-card__title">ورود</h2>

            <?php if ($error !== null): ?>
                <div class="admin-auth-error" role="alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form
                class="admin-auth-form"
                method="post"
                action="<?= e(url('/admin/auth/login.php')) ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($csrfToken) ?>">

                <div class="admin-auth-field">
                    <label for="identifier">ایمیل یا نام کاربری</label>
                    <input
                        id="identifier"
                        name="identifier"
                        type="text"
                        autocomplete="username"
                        aria-describedby="admin-identifier-hint"
                        value="<?= e(
                            (string) Request::post(
                                'identifier',
                                ''
                            )
                        ) ?>"
                        required>
                    <small id="admin-identifier-hint" class="admin-auth-hint">
                        ایمیل یا نام کاربری حساب مدیر را وارد کنید.
                    </small>
                </div>

                <div class="admin-auth-field">
                    <label for="password">رمز عبور</label>
                    <div class="admin-password-field">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required>
                        <button
                            class="admin-password-toggle"
                            type="button"
                            data-password-toggle
                            aria-label="نمایش رمز عبور"
                            aria-controls="password"
>
                            <svg
                                data-password-icon="visible"
                                viewBox="0 0 24 24"
                                width="22"
                                height="22"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                                focusable="false"
                                hidden>
                                <path d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg
                                data-password-icon="hidden"
                                viewBox="0 0 24 24"
                                width="22"
                                height="22"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                                focusable="false">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.58 10.59a2 2 0 0 0 2.83 2.83"></path>
                                <path d="M9.88 5.09A10.8 10.8 0 0 1 12 4.88c6.25 0 9.75 7.12 9.75 7.12a16.4 16.4 0 0 1-3.06 3.82"></path>
                                <path d="M6.61 6.61C3.79 8.48 2.25 12 2.25 12s3.5 7.12 9.75 7.12a10.5 10.5 0 0 0 4.02-.79"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button class="admin-auth-submit" type="submit">ورود</button>
            </form>
        </section>
    </main>

</body>
</html>
