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
    <title>مناطق ارسال | نیواد</title>
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

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: auto
        }

        button,
        select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff;
            cursor: pointer
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

        .status {
            font-weight: bold
        }

        .message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px;
            background: #f3f4f6
        }
    </style>
</head>

<body>

    <header>
        <strong>مدیریت مناطق ارسال</strong>
    </header>

    <main>
        <h1>مناطق ارسال</h1>

        <div id="message" class="message">در حال دریافت اطلاعات...</div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>نام</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="zones"></tbody>
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

        function setMessage(message) {
            document.getElementById('message').textContent = message;
        }

        async function loadZones() {
            try {
                const response = await fetch('/api/admin/shipping/zones', {
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'خطا در دریافت مناطق ارسال');
                }

                const rows = Array.isArray(data.data) ? data.data : [];

                document.getElementById('zones').innerHTML = rows.map(zone => `
            <tr>
                <td>${esc(zone.id)}</td>
                <td>${esc(zone.name)}</td>
                <td class="status">${esc(zone.status)}</td>
                <td>
                    <select data-status="${esc(zone.id)}">
                        <option value="active" ${zone.status === 'active' ? 'selected' : ''}>فعال</option>
                        <option value="inactive" ${zone.status === 'inactive' ? 'selected' : ''}>غیرفعال</option>
                    </select>
                </td>
            </tr>
        `).join('');

                if (!rows.length) {
                    document.getElementById('zones').innerHTML =
                        '<tr><td colspan="4">موردی یافت نشد.</td></tr>';
                }

                setMessage(`${rows.length} منطقه دریافت شد.`);
            } catch (error) {
                setMessage(error.message || 'خطا در دریافت اطلاعات');
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
                    `/api/admin/shipping/zones/${encodeURIComponent(id)}/status`, {
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
                    throw new Error(data.message || 'تغییر وضعیت انجام نشد');
                }

                await loadZones();
            } catch (error) {
                setMessage(error.message || 'خطا در تغییر وضعیت');
                await loadZones();
            }
        });

        loadZones();
    </script>

</body>

</html>