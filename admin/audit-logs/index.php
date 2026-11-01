<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';

if (!$adminAuth->can('admin.audit.view')) {
    Response::redirect(url('/admin/access-denied.php'));
}
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

        <section class="card" style="margin-bottom:20px">
            <h2>تاریخچه یک موجودیت</h2>
            <form id="entity-timeline-form">
                <label>نوع موجودیت <input name="entity_type" required maxlength="100" pattern="[a-z][a-z0-9_]{1,99}" placeholder="product"></label>
                <label>شناسه موجودیت <input name="entity_id" required maxlength="100" pattern="[a-zA-Z0-9_-]+" placeholder="123"></label>
                <button type="submit">نمایش تاریخچه</button>
            </form>
            <div id="entity-timeline" aria-live="polite"></div>
        </section>

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

        document.getElementById('entity-timeline-form').addEventListener('submit', async event => {
            event.preventDefault();
            const data = new FormData(event.currentTarget);
            const type = String(data.get('entity_type') || '').trim();
            const id = String(data.get('entity_id') || '').trim();
            const target = document.getElementById('entity-timeline');
            target.textContent = 'در حال دریافت تاریخچه...';
            try {
                const api = new URL('<?= e(url('/api/index.php')) ?>', window.location.href);
                api.searchParams.set('path', '/admin/audit/entities/' + encodeURIComponent(type) + '/' + encodeURIComponent(id));
                const response = await fetch(api, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                const result = await response.json();
                if (!response.ok || result.success !== true) throw new Error(result.message || 'دریافت تاریخچه ناموفق بود.');
                const items = result.data?.items || [];
                if (!items.length) { target.textContent = 'برای این موجودیت رویدادی ثبت نشده است.'; return; }
                const table = document.createElement('table');
                const head = document.createElement('thead');
                head.innerHTML = '<tr><th>عامل</th><th>عملیات</th><th>ماژول</th><th>شرح</th><th>قبل</th><th>بعد</th><th>زمان</th></tr>';
                const body = document.createElement('tbody');
                items.forEach(item => {
                    const row = document.createElement('tr');
                    [item.actor_name, item.action, item.module, item.description, item.old_values, item.new_values, item.created_at].forEach(value => {
                        const cell = document.createElement('td');
                        cell.textContent = value == null ? '—' : String(value);
                        row.append(cell);
                    });
                    body.append(row);
                });
                table.append(head, body);
                target.replaceChildren(table);
            } catch (error) { target.textContent = error.message || 'خطا در دریافت تاریخچه'; }
        });

        loadLogs();
    </script>

</body>

</html>