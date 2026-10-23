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

    <title>گزارش محصولات | نیواد</title>

    <style>
        body {
            font-family: Tahoma, sans-serif;
            margin: 0;
            background: #f5f7fa;
            color: #1f2937;
        }

        main {
            max-width: 1100px;
            margin: auto;
            padding: 30px 20px;
        }

        .filters,
        .result {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit,
                    minmax(180px, 1fr));
            gap: 14px;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        input,
        button {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }

        button {
            cursor: pointer;
        }

        pre {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .error {
            color: #b91c1c;
        }
    </style>

    <link rel="stylesheet" href="<?= e(url('/assets/css/admin/admin.css')) ?>">
    <script defer src="<?= e(url('/assets/js/admin-theme.js')) ?>"></script>
</head>

<body>

    <main>

        <h1>گزارش محصولات</h1>

        <p>
            <a href="<?= e(url('/admin/reports')) ?>">
                بازگشت به گزارش‌ها
            </a>
        </p>

        <form
            id="report-form"
            class="filters">

            <label>
                از تاریخ
                <input type="date" name="from_date">
            </label>

            <label>
                تا تاریخ
                <input type="date" name="to_date">
            </label>

            <label>
                ارز
                <input
                    type="text"
                    name="currency"
                    placeholder="IRR">
            </label>

            <div>
                <button type="submit">
                    دریافت گزارش
                </button>
            </div>

        </form>

        <section class="result">

            <h2>نتیجه</h2>

            <div id="report-status">
                آماده دریافت گزارش
            </div>

            <pre id="report-result"></pre>

        </section>

    </main>

    <script>
        (() => {
            const form =
                document.getElementById('report-form');

            const status =
                document.getElementById('report-status');

            const result =
                document.getElementById('report-result');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                status.textContent =
                    'در حال دریافت گزارش...';

                result.textContent = '';

                const params =
                    new URLSearchParams(
                        new FormData(form)
                    );

                for (const [
                        key,
                        value
                    ] of [...params.entries()]) {
                    if (value === '') {
                        params.delete(key);
                    }
                }

                try {
                    const response =
                        await fetch(
                            '<?= e(url('/api/admin/reports/product-performance-summary')) ?>' +
                            '?' +
                            params.toString(), {
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json'
                                }
                            }
                        );

                    const data =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.message ||
                            'دریافت گزارش ناموفق بود.'
                        );
                    }

                    status.textContent =
                        'گزارش با موفقیت دریافت شد.';

                    result.textContent =
                        JSON.stringify(
                            data,
                            null,
                            2
                        );
                } catch (error) {
                    status.innerHTML =
                        '<span class="error">' +
                        error.message +
                        '</span>';
                }
            });
        })();
    </script>

</body>

</html>