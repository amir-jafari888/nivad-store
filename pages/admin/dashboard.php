<?php

declare(strict_types=1);

$metrics = $metrics ?? [];

$orders = $metrics['orders'] ?? [];
$payments = $metrics['payments'] ?? [];
$orderItems = $metrics['order_items'] ?? [];
$inventory = $metrics['inventory'] ?? [];
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
</head>

<body>

    <header>
        <div>
            <a href="<?= e(url('/admin')) ?>">
                نیواد
            </a>
        </div>

        <<?php
            $admin = $admin ?? null;
            ?>

            <?php if ($admin !== null) { ?>
            <span>
            <?= htmlspecialchars(
                    (string)($admin['name'] ?? $admin['username'] ?? 'مدیر'),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>
        <?php } ?>

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
                <a href="<?= e(url('/admin')) ?>">
                    داشبورد
                </a>

                <a href="<?= e(url('/admin/products')) ?>">
                    محصولات
                </a>

                <a href="<?= e(url('/admin/categories')) ?>">
                    دسته‌بندی‌ها
                </a>

                <a href="<?= e(url('/admin/brands')) ?>">
                    برندها
                </a>

                <a href="<?= e(url('/admin/orders')) ?>">
                    سفارش‌ها
                </a>

                <a href="<?= e(url('/admin/inventory')) ?>">
                    موجودی
                </a>

                <a href="<?= e(url('/admin/payments')) ?>">
                    پرداخت‌ها
                </a>

                <a href="<?= e(url('/admin/reports')) ?>">
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
                        سفارش‌ها
                    </h2>

                    <strong>
                        <?= e(
                            (string)(
                                $orders['total_orders']
                                ?? 0
                            )
                        ) ?>
                    </strong>

                    <p>
                        مبلغ کل:
                        <?= e(
                            (string)(
                                $orders['total_amount']
                                ?? 0
                            )
                        ) ?>
                    </p>
                </article>

                <article>
                    <h2>
                        پرداخت‌ها
                    </h2>

                    <strong>
                        <?= e(
                            (string)(
                                $payments['total_payments']
                                ?? 0
                            )
                        ) ?>
                    </strong>

                    <p>
                        مبلغ کل:
                        <?= e(
                            (string)(
                                $payments['total_amount']
                                ?? 0
                            )
                        ) ?>
                    </p>
                </article>

                <article>
                    <h2>
                        آیتم‌های سفارش
                    </h2>

                    <strong>
                        <?= e(
                            (string)(
                                $orderItems['total_quantity']
                                ?? 0
                            )
                        ) ?>
                    </strong>

                    <p>
                        تعداد خطوط:
                        <?= e(
                            (string)(
                                $orderItems['total_lines']
                                ?? 0
                            )
                        ) ?>
                    </p>
                </article>

                <article>
                    <h2>
                        موجودی
                    </h2>

                    <strong>
                        <?= e(
                            (string)(
                                $inventory['total_quantity']
                                ?? 0
                            )
                        ) ?>
                    </strong>

                    <p>
                        موجودی قابل فروش:
                        <?= e(
                            (string)(
                                $inventory['total_available_quantity']
                                ?? 0
                            )
                        ) ?>
                    </p>
                </article>

            </section>

        </main>

    </div>

</body>

</html>