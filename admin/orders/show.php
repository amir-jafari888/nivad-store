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
    <title>جزئیات سفارش | نیواد</title>
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
            max-width: 1200px;
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
        button {
            padding: 10px 12px;
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
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06)
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            background: #f3f4f6;
            margin-bottom: 15px
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px
        }

        .item {
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px
        }

        .item strong {
            display: block;
            margin-bottom: 6px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right
        }
    </style>
</head>

<body>

    <header>
        <strong>جزئیات سفارش</strong>
    </header>

    <main>
        <h1>جزئیات سفارش</h1>

        <div class="toolbar">
            <input id="orderId" type="number" min="1" placeholder="شناسه سفارش">
            <button type="button" id="load">نمایش</button>
        </div>

        <div id="message" class="message">شناسه سفارش را وارد کنید.</div>

        <section id="orderBox"></section>
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

        async function loadOrder() {
            const id = document.getElementById('orderId').value.trim();

            if (!id) {
                document.getElementById('message').textContent =
                    'شناسه سفارش را وارد کنید.';
                return;
            }

            try {
                const response = await fetch(
                    `/api/admin/orders/${encodeURIComponent(id)}`, {
                        credentials: 'same-origin'
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'سفارش پیدا نشد');
                }

                const order = data.data;

                document.getElementById('orderBox').innerHTML = `
            <section class="card">
                <div class="grid">
                    <div class="item">
                        <strong>ID</strong>
                        ${esc(order.id)}
                    </div>

                    <div class="item">
                        <strong>شماره سفارش</strong>
                        ${esc(order.order_number)}
                    </div>

                    <div class="item">
                        <strong>User ID</strong>
                        ${esc(order.user_id)}
                    </div>

                    <div class="item">
                        <strong>وضعیت</strong>
                        ${esc(order.status)}
                    </div>

                    <div class="item">
                        <strong>مبلغ</strong>
                        ${esc(order.total_amount ?? order.amount)}
                    </div>

                    <div class="item">
                        <strong>تاریخ ایجاد</strong>
                        ${esc(order.created_at)}
                    </div>
                </div>
            </section>

            ${
                Array.isArray(order.items)
                ? `
                <section class="card">
                    <h2>اقلام سفارش</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Variant ID</th>
                                <th>تعداد</th>
                                <th>قیمت</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${order.items.map(item => `
                                <tr>
                                    <td>${esc(item.id)}</td>
                                    <td>${esc(item.variant_id)}</td>
                                    <td>${esc(item.quantity)}</td>
                                    <td>${esc(item.price)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </section>
                `
                : ''
            }
        `;

                document.getElementById('message').textContent =
                    'اطلاعات سفارش دریافت شد.';
            } catch (error) {
                document.getElementById('message').textContent =
                    error.message || 'خطا در دریافت سفارش';

                document.getElementById('orderBox').innerHTML = '';
            }
        }

        document.getElementById('load').addEventListener('click', loadOrder);

        const params = new URLSearchParams(window.location.search);
        const queryId = params.get('id');

        if (queryId) {
            document.getElementById('orderId').value = queryId;
            loadOrder();
        }
    </script>

</body>

</html>