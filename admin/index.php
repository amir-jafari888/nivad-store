<?php

declare(strict_types=1);

require_once dirname(__DIR__)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/includes/auth.php';
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        داشبورد مدیریت | نیواد
    </title>

    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>
    <header>
        <div>
            <a
                href="<?= e(url('/admin')) ?>">
                نیواد
            </a>
        </div>
        <div>
            <?php if ($currentAdmin !== null): ?>
                <span>
                    <?= e(
                        (string) (
                            $currentAdmin['name']
                            ?? $currentAdmin['email']
                            ?? 'مدیر'
                        )
                    ) ?>
                </span>
            <?php endif; ?>
            <form
                method="post"
                action="<?= e(
                            url('/admin/logout')
                        ) ?>">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(Csrf::token()) ?>">
                <button type="submit">
                    خروج
                </button>
            </form>
        </div>
    </header>
    <div>
        <aside>
            <nav>
                <a
                    href="<?= e(url('/admin')) ?>">
                    داشبورد
                </a>
                <a
                    href="<?= e(url('/admin/products')) ?>">
                    محصولات
                </a>
                <a
                    href="<?= e(url('/admin/categories')) ?>">
                    دسته‌بندی‌ها
                </a>
                <a
                    href="<?= e(url('/admin/brands')) ?>">
                    برندها
                </a>
                <a
                    href="<?= e(url('/admin/orders')) ?>">
                    سفارش‌ها
                </a>
                <a
                    href="<?= e(url('/admin/inventory')) ?>">
                    موجودی
                </a>
                <a
                    href="<?= e(url('/admin/payments')) ?>">
                    پرداخت‌ها
                </a>
                <a
                    href="<?= e(url('/admin/reports')) ?>">
                    گزارش‌ها
                </a>
            </nav>
        </aside>
        <main>
            <header>
                <h1>
                    داشبورد مدیریت
                </h1>
                <p>
                    خوش آمدید.
                </p>
            </header>
            <section>
                <article>
                    <h2>
                        محصولات
                    </h2>
                    <p>
                        مدیریت محصولات فروشگاه
                    </p>
                </article>
                <article>
                    <h2>
                        سفارش‌ها
                    </h2>
                    <p>
                        مدیریت سفارش‌های مشتریان
                    </p>
                </article>
                <article>
                    <h2>
                        موجودی
                    </h2>
                    <p>
                        مدیریت موجودی و انبار
                    </p>
                </article>
                <article>
                    <h2>
                        گزارش‌ها
                    </h2>
                    <p>
                        مشاهده گزارش‌های فروش و عملکرد
                    </p>
                </article>
            </section>
        </main>
    </div>
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>