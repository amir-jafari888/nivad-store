<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';

$csrfToken = Csrf::token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width,initial-scale=1">

    <title>کاربران | نیواد</title>

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

        select {
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 8px
        }

        .message {
            padding: 12px;
            margin-bottom: 15px;
            background: #fff;
            border-radius: 8px
        }
    </style>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>

    <header>
        <strong>مدیریت کاربران</strong>
    </header>

    <main>

        <h1>کاربران</h1>

        <div id="message" class="message">
            در حال دریافت...
        </div>

        <div class="card">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام</th>
                        <th>ایمیل</th>
                        <th>تلفن</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>

                <tbody id="rows"></tbody>

            </table>

        </div>

    </main>

    <script>
        const csrfToken =
            <?= json_encode(
                $csrfToken,
                JSON_UNESCAPED_UNICODE
            ) ?>;

        function esc(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function loadUsers() {
            const message =
                document.getElementById('message');

            try {
                const response =
                    await fetch(
                        '<?= e(url('/api/admin/users')) ?>', {
                            credentials: 'same-origin'
                        }
                    );

                const result =
                    await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'خطا'
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
                        <td>
                            ${esc(item.first_name)}
                            ${esc(item.last_name)}
                        </td>
                        <td>${esc(item.email)}</td>
                        <td>${esc(item.phone)}</td>
                        <td>
                            <select
                                onchange="changeStatus(
                                    ${Number(item.id)},
                                    this.value
                                )">
                                <option
                                    value="active"
                                    ${item.status === 'active'
                                        ? 'selected'
                                        : ''}>
                                    active
                                </option>

                                <option
                                    value="inactive"
                                    ${item.status === 'inactive'
                                        ? 'selected'
                                        : ''}>
                                    inactive
                                </option>

                                <option
                                    value="blocked"
                                    ${item.status === 'blocked'
                                        ? 'selected'
                                        : ''}>
                                    blocked
                                </option>

                                <option
                                    value="pending"
                                    ${item.status === 'pending'
                                        ? 'selected'
                                        : ''}>
                                    pending
                                </option>
                            </select>
                        </td>
                        <td>
                            <a
                                href="<?= e(
                                            url('/admin/users/show?id=')
                                        ) ?>${Number(item.id)}">
                                مشاهده
                            </a>
                        </td>
                    </tr>
                `).join('');

                message(
                    `تعداد کاربران: ${items.length}`
                );

            } catch (error) {
                message(
                    error.message ||
                    'خطا در دریافت کاربران'
                );
            }
        }

        async function changeStatus(id, status) {
            const response =
                await fetch(
                    `<?= e(url('/api/admin/users/')) ?>${id}/status`, {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            status
                        })
                    }
                );

            const result =
                await response.json();

            if (!response.ok) {
                document.getElementById('message')
                    .textContent =
                    result.message ||
                    'تغییر وضعیت انجام نشد';

                return;
            }

            loadUsers();
        }

        loadUsers();
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>