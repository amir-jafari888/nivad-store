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
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>پرسش‌ها | نیواد</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Tahoma, Arial
        }

        main {
            max-width: 1300px;
            margin: 30px auto;
            padding: 20px
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            overflow: auto
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 11px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
            white-space: nowrap
        }

        select {
            padding: 8px
        }

        .message {
            padding: 12px;
            background: #fff;
            margin-bottom: 15px;
            border-radius: 8px
        }
    </style>
</head>

<body>

    <main>
        <h1>مدیریت پرسش‌ها</h1>
        <div id="message" class="message">در حال دریافت...</div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>User</th>
                        <th>Question</th>
                        <th>Status</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="rows"></tbody>
            </table>
        </div>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken) ?>;

        function esc(v) {
            return String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
        }

        async function load() {
            try {
                const response = await fetch('/api/admin/questions', {
                    credentials: 'same-origin'
                });
                const result = await response.json();

                if (!response.ok) throw new Error(result.message || 'خطا');

                const items = result.data?.items || result.items || [];

                document.getElementById('rows').innerHTML = items.map(item => `
<tr>
<td>${esc(item.id)}</td>
<td>${esc(item.product_id)}</td>
<td>${esc(item.user_id)}</td>
<td>${esc(item.question)}</td>
<td>${esc(item.status)}</td>
<td>
<select data-id="${esc(item.id)}">
<option value="pending" ${item.status==='pending'?'selected':''}>pending</option>
<option value="approved" ${item.status==='approved'?'selected':''}>approved</option>
<option value="rejected" ${item.status==='rejected'?'selected':''}>rejected</option>
<option value="hidden" ${item.status==='hidden'?'selected':''}>hidden</option>
</select>
<a href="show.php?id=${encodeURIComponent(item.id)}">مشاهده</a>
</td>
</tr>`).join('');

                if (!items.length) document.getElementById('rows').innerHTML = '<tr><td colspan="6">پرسشی یافت نشد.</td></tr>';

                document.getElementById('message').textContent = `${items.length} پرسش دریافت شد.`;
            } catch (error) {
                document.getElementById('message').textContent = error.message;
            }
        }

        document.addEventListener('change', async e => {
            const select = e.target.closest('[data-id]');
            if (!select) return;

            try {
                const response = await fetch(`/api/admin/questions/${encodeURIComponent(select.dataset.id)}/status`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        status: select.value
                    })
                });

                const result = await response.json();

                if (!response.ok) throw new Error(result.message || 'تغییر وضعیت انجام نشد');

                await load();
            } catch (error) {
                document.getElementById('message').textContent = error.message;
            }
        });

        load();
    </script>

</body>

</html>