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
    <title>کوپن‌ها | نیواد</title>
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

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: auto
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

        select {
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff
        }
    </style>
</head>

<body>

    <header>
        <strong>مدیریت کوپن‌ها</strong>
    </header>

    <main>
        <h1>کوپن‌ها</h1>

        <div id="message" class="message">در حال دریافت اطلاعات...</div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>کد</th>
                        <th>نوع تخفیف</th>
                        <th>مقدار</th>
                        <th>وضعیت</th>
                        <th>تغییر وضعیت</th>
                    </tr>
                </thead>
                <tbody id="coupons"></tbody>
            </table>
        </div>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>;

        function esc(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function loadCoupons() {
            try {
                const response = await fetch('/api/admin/coupons', {
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'خطا در دریافت کوپن‌ها');
                }

                const rows = Array.isArray(data.data) ? data.data : [];

                document.getElementById('coupons').innerHTML = rows.map(coupon => `
            <tr>
                <td>${esc(coupon.id)}</td>
                <td>${esc(coupon.code)}</td>
                <td>${esc(coupon.discount_type)}</td>
                <td>${esc(coupon.discount_value ?? coupon.value)}</td>
                <td>${esc(coupon.status)}</td>
                <td>
                    <select data-status="${esc(coupon.id)}">
                        <option value="active"
                            ${coupon.status === 'active' ? 'selected' : ''}>
                            active
                        </option>
                        <option value="inactive"
                            ${coupon.status === 'inactive' ? 'selected' : ''}>
                            inactive
                        </option>
                    </select>
                </td>
            </tr>
        `).join('');

                if (!rows.length) {
                    document.getElementById('coupons').innerHTML =
                        '<tr><td colspan="6">کوپنی یافت نشد.</td></tr>';
                }

                document.getElementById('message').textContent =
                    `${rows.length} کوپن دریافت شد.`;
            } catch (error) {
                document.getElementById('message').textContent =
                    error.message || 'خطا در دریافت اطلاعات';
            }
        }

        document.addEventListener('change', async event => {
            const select = event.target.closest('[data-status]');

            if (!select) {
                return;
            }

            const id = select.dataset.status;

            try {
                const response = await fetch(
                    `/api/admin/coupons/${encodeURIComponent(id)}/status`, {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            status: select.value
                        })
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'تغییر وضعیت کوپن انجام نشد');
                }

                await loadCoupons();
            } catch (error) {
                document.getElementById('message').textContent =
                    error.message || 'خطا در تغییر وضعیت';

                await loadCoupons();
            }
        });

        loadCoupons();
    </script>

</body>

</html>