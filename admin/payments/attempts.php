<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';

$csrfToken = Csrf::token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تلاش‌های پرداخت | نیواد</title>
    <style>
        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            background: #f5f7fa;
            color: #1f2937;
            font-family: Tahoma, Arial, sans-serif
        }

        header {
            background: #111827;
            color: #fff;
            padding: 18px 24px
        }

        main {
            max-width: 1500px;
            margin: 30px auto;
            padding: 0 16px
        }

        h1 {
            margin: 0 0 20px
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            overflow: auto;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06)
        }

        .message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px;
            background: #f3f4f6
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
            white-space: nowrap
        }

        th {
            background: #f9fafb
        }
    </style>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>

    <header>
        <strong>تلاش‌های پرداخت</strong>
    </header>

    <main>
        <h1>تلاش‌های پرداخت</h1>

        <div id="message" class="message">در حال دریافت اطلاعات...</div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Gateway</th>
                        <th>Gateway Transaction</th>
                        <th>Authority</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody id="attempts"></tbody>
            </table>
        </div>
    </main>

    <script>
        function esc(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function loadAttempts() {
            try {
                const response = await fetch('/api/admin/payments/attempts', {
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'خطا در دریافت تلاش‌های پرداخت');
                }

                const rows = Array.isArray(data.data) ? data.data : [];

                document.getElementById('attempts').innerHTML = rows.map(item => `
            <tr>
                <td>${esc(item.id)}</td>
                <td>${esc(item.order_id)}</td>
                <td>${esc(item.user_id)}</td>
                <td>${esc(item.gateway)}</td>
                <td>${esc(item.gateway_transaction_id)}</td>
                <td>${esc(item.authority)}</td>
                <td>${esc(item.amount)}</td>
                <td>${esc(item.currency)}</td>
                <td>${esc(item.status)}</td>
                <td>${esc(item.created_at)}</td>
            </tr>
        `).join('');

                if (!rows.length) {
                    document.getElementById('attempts').innerHTML =
                        '<tr><td colspan="10">تلاشی یافت نشد.</td></tr>';
                }

                document.getElementById('message').textContent =
                    `${rows.length} تلاش پرداخت دریافت شد.`;
            } catch (error) {
                document.getElementById('message').textContent =
                    error.message || 'خطا در دریافت اطلاعات';
            }
        }

        loadAttempts();
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>