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
    <title>پرداخت‌ها | نیواد</title>
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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 16px
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            overflow: auto;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06)
        }

        h1 {
            margin: 0 0 20px
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

        a {
            color: #2563eb;
            text-decoration: none
        }
    </style>
</head>

<body>

    <header>
        <strong>مدیریت پرداخت‌ها</strong>
    </header>

    <main>
        <h1>پرداخت‌ها</h1>

        <div id="message" class="message">در حال دریافت اطلاعات...</div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Gateway</th>
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Paid At</th>
                    </tr>
                </thead>
                <tbody id="payments"></tbody>
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

        async function loadPayments() {
            try {
                const response = await fetch('/api/admin/payments', {
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'خطا در دریافت پرداخت‌ها');
                }

                const rows = Array.isArray(data.data) ? data.data : [];

                document.getElementById('payments').innerHTML = rows.map(payment => `
            <tr>
                <td>${esc(payment.id)}</td>
                <td>${esc(payment.order_id)}</td>
                <td>${esc(payment.user_id)}</td>
                <td>${esc(payment.gateway)}</td>
                <td>${esc(payment.transaction_id)}</td>
                <td>${esc(payment.amount)}</td>
                <td>${esc(payment.currency)}</td>
                <td>${esc(payment.status)}</td>
                <td>${esc(payment.paid_at)}</td>
            </tr>
        `).join('');

                if (!rows.length) {
                    document.getElementById('payments').innerHTML =
                        '<tr><td colspan="9">پرداختی یافت نشد.</td></tr>';
                }

                document.getElementById('message').textContent =
                    `${rows.length} پرداخت دریافت شد.`;
            } catch (error) {
                document.getElementById('message').textContent =
                    error.message || 'خطا در دریافت اطلاعات';
            }
        }

        loadPayments();
    </script>

</body>

</html>