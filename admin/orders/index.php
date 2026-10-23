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
    <title>سفارش‌ها | نیواد</title>
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

        h1 {
            margin: 0 0 20px
        }

        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px
        }

        input,
        select,
        button {
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff
        }

        button {
            cursor: pointer
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

        a {
            color: #2563eb;
            text-decoration: none
        }
    </style>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>

    <header>
        <strong>مدیریت سفارش‌ها</strong>
    </header>

    <main>
        <h1>سفارش‌ها</h1>

        <div class="toolbar">
            <select id="status">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending">pending</option>
                <option value="processing">processing</option>
                <option value="completed">completed</option>
                <option value="cancelled">cancelled</option>
            </select>

            <button type="button" id="load">دریافت</button>
        </div>

        <div id="message" class="message">برای دریافت اطلاعات دکمه را بزنید.</div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>شماره سفارش</th>
                        <th>User ID</th>
                        <th>وضعیت</th>
                        <th>مبلغ</th>
                        <th>تاریخ</th>
                        <th>جزئیات</th>
                    </tr>
                </thead>
                <tbody id="orders"></tbody>
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

        async function loadOrders() {
            const status = document.getElementById('status').value;

            const query = status ?
                `?status=${encodeURIComponent(status)}` :
                '';

            try {
                const response = await fetch(`/api/admin/orders${query}`, {
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'خطا در دریافت سفارش‌ها');
                }

                const rows = Array.isArray(data.data) ? data.data : [];

                document.getElementById('orders').innerHTML = rows.map(order => `
            <tr>
                <td>${esc(order.id)}</td>
                <td>${esc(order.order_number)}</td>
                <td>${esc(order.user_id)}</td>
                <td>${esc(order.status)}</td>
                <td>${esc(order.total_amount ?? order.amount)}</td>
                <td>${esc(order.created_at)}</td>
                <td>
                    <a href="show.php?id=${encodeURIComponent(order.id)}">
                        مشاهده
                    </a>
                </td>
            </tr>
        `).join('');

                if (!rows.length) {
                    document.getElementById('orders').innerHTML =
                        '<tr><td colspan="7">سفارشی یافت نشد.</td></tr>';
                }

                document.getElementById('message').textContent =
                    `${rows.length} سفارش دریافت شد.`;
            } catch (error) {
                document.getElementById('message').textContent =
                    error.message || 'خطا در دریافت اطلاعات';
            }
        }

        document.getElementById('load').addEventListener('click', loadOrders);

        loadOrders();
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>