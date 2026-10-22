<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width,initial-scale=1">
    <title>لاگ‌های حسابرسی | نیواد</title>

    <style>
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

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            overflow: auto;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06)
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

        th {
            background: #f9fafb
        }

        .message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px;
            background: #fff
        }
    </style>
</head>

<body>

    <header>
        <strong>مدیریت لاگ‌های حسابرسی</strong>
    </header>

    <main>

        <h1>لاگ‌های حسابرسی</h1>

        <div id="message" class="message">
            در حال دریافت اطلاعات...
        </div>

        <div class="card">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Entity</th>
                        <th>Entity ID</th>
                        <th>Description</th>
                        <th>IP</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody id="rows"></tbody>
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

        async function loadLogs() {
            const message =
                document.getElementById('message');

            try {
                const response =
                    await fetch(
                        '<?= e(url('/api/admin/audit-logs')) ?>', {
                            credentials: 'same-origin'
                        }
                    );

                const result =
                    await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'خطا در دریافت لاگ‌ها'
                    );
                }

                const items =
                    result.data?.items ||
                    result.items || [];

                document.getElementById('rows')
                    .innerHTML =
                    items.map(item => `
                    <tr>
                        <td>${esc(item.id)}</td>
                        <td>${esc(item.admin_user_id)}</td>
                        <td>${esc(item.action)}</td>
                        <td>${esc(item.module)}</td>
                        <td>${esc(item.entity_type)}</td>
                        <td>${esc(item.entity_id)}</td>
                        <td>${esc(item.description)}</td>
                        <td>${esc(item.ip_address)}</td>
                        <td>${esc(item.created_at)}</td>
                    </tr>
                `).join('');

                message.textContent =
                    `تعداد لاگ‌ها: ${
                    result.data?.count ??
                    result.count ??
                    items.length
                }`;
            } catch (error) {
                message.textContent =
                    error.message ||
                    'خطا در دریافت اطلاعات';
            }
        }

        loadLogs();
    </script>

</body>

</html>