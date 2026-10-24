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
<html
    lang="fa"
    dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        ورود مدیر | نیواد
    </title>


    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script defer src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>

<body>
    <a class="admin-skip-link" href="#main-content">رفتن به محتوای اصلی</a>

    <main id="main-content" tabindex="-1">

        <section>

            <h1>
                ورود به پنل مدیریت
            </h1>

            <?php if ($error !== null): ?>

                <div role="alert" aria-live="assertive" aria-atomic="true">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <form
                method="post"
                action="<?= e(url('/admin/auth/login')) ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($csrfToken) ?>">

                <div>

                    <label for="identifier">
                        ایمیل یا نام کاربری
                    </label>

                    <input
                        id="identifier"
                        name="identifier"
                        type="text"
                        autocomplete="username"
                        value="<?= e(
                                    (string) Request::post(
                                        'identifier',
                                        ''
                                    )
                                ) ?>"
                        required>

                </div>

                <div>

                    <label for="password">
                        رمز عبور
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required>

                </div>

                <button type="submit">
                    ورود
                </button>

            </form>

        </section>

    </main>

</body>

</html>