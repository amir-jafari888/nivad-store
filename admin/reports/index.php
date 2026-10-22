<?php

declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>گزارش‌ها | نیواد</title>

    <style>
        body {
            font-family: Tahoma, sans-serif;
            margin: 0;
            background: #f5f7fa;
            color: #1f2937;
        }

        header {
            padding: 24px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit,
                    minmax(220px, 1fr));
            gap: 20px;
        }

        .report-card {
            display: block;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            text-decoration: none;
            color: inherit;
        }

        .report-card:hover {
            border-color: #9ca3af;
        }

        .report-card h2 {
            margin-top: 0;
        }
    </style>
</head>

<body>

    <header>
        <h1>گزارش‌های مدیریت</h1>
        <a href="<?= e(url('/admin')) ?>">
            بازگشت به داشبورد
        </a>
    </header>

    <main>

        <div class="reports-grid">

            <a
                class="report-card"
                href="<?= e(url('/admin/reports/sales')) ?>">
                <h2>گزارش فروش</h2>
                <p>
                    مشاهده درآمد، فروش و وضعیت مالی سفارش‌ها.
                </p>
            </a>

            <a
                class="report-card"
                href="<?= e(url('/admin/reports/orders')) ?>">
                <h2>گزارش سفارش‌ها</h2>
                <p>
                    بررسی تعداد و وضعیت سفارش‌ها.
                </p>
            </a>

            <a
                class="report-card"
                href="<?= e(url('/admin/reports/products')) ?>">
                <h2>گزارش محصولات</h2>
                <p>
                    بررسی عملکرد محصولات و تنوع محصولات.
                </p>
            </a>

            <a
                class="report-card"
                href="<?= e(url('/admin/reports/categories')) ?>">
                <h2>گزارش دسته‌بندی‌ها</h2>
                <p>
                    بررسی عملکرد دسته‌بندی‌های فروشگاه.
                </p>
            </a>

        </div>

    </main>

</body>

</html>