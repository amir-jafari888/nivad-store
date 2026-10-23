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

    <title>دستگاه‌های سازگار | نیواد</title>

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
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: auto
        }

        .grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px
        }

        input,
        select,
        textarea,
        button {
            width: 100%;
            padding: 9px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box
        }

        textarea {
            min-height: 80px
        }

        button {
            cursor: pointer;
            background: #111827;
            color: #fff
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
            padding: 12px;
            margin-bottom: 15px;
            background: #fff;
            border-radius: 8px
        }

        .actions {
            display: flex;
            gap: 8px
        }

        .actions button {
            width: auto
        }
    </style>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>

    <header>
        <strong>مدیریت دستگاه‌های سازگار</strong>
    </header>

    <main>

        <h1>دستگاه‌ها</h1>

        <div id="message" class="message">
            در حال دریافت...
        </div>

        <section class="card">

            <h2>افزودن دستگاه</h2>

            <form id="createForm">

                <div class="grid">

                    <input
                        name="brand_name"
                        placeholder="نام برند"
                        required>

                    <input
                        name="name"
                        placeholder="نام دستگاه"
                        required>

                    <input
                        name="model"
                        placeholder="مدل">

                    <input
                        name="device_type"
                        placeholder="نوع دستگاه"
                        required>

                    <input
                        name="slug"
                        placeholder="Slug"
                        required>

                    <input
                        name="image"
                        placeholder="تصویر">

                    <input
                        name="sort_order"
                        type="number"
                        min="0"
                        value="0">

                    <select name="status">
                        <option value="active">active</option>
                        <option value="inactive">inactive</option>
                    </select>

                </div>

                <br>

                <textarea
                    name="description"
                    placeholder="توضیحات"></textarea>

                <br>

                <button type="submit">
                    ایجاد دستگاه
                </button>

            </form>

        </section>

        <section class="card">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>برند</th>
                        <th>نام</th>
                        <th>مدل</th>
                        <th>نوع</th>
                        <th>Slug</th>
                        <th>وضعیت</th>
                        <th>ترتیب</th>
                        <th>عملیات</th>
                    </tr>
                </thead>

                <tbody id="rows"></tbody>

            </table>

        </section>

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

        function message(text) {
            document.getElementById('message')
                .textContent = text;
        }

        async function loadDevices() {
            try {
                const response =
                    await fetch(
                        '<?= e(url('/api/admin/compatibility/devices')) ?>', {
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
                        <td>${esc(item.brandName ?? item.brand_name)}</td>
                        <td>${esc(item.name)}</td>
                        <td>${esc(item.model)}</td>
                        <td>${esc(item.deviceType ?? item.device_type)}</td>
                        <td>${esc(item.slug)}</td>
                        <td>${esc(item.status)}</td>
                        <td>${esc(item.sortOrder ?? item.sort_order)}</td>
                        <td class="actions">
                            <button
                                type="button"
                                onclick="toggleStatus(${Number(item.id)}, '${item.status === 'active' ? 'inactive' : 'active'}')">
                                ${item.status === 'active'
                                    ? 'غیرفعال'
                                    : 'فعال'}
                            </button>

                            <button
                                type="button"
                                onclick="removeDevice(${Number(item.id)})">
                                حذف
                            </button>
                        </td>
                    </tr>
                `).join('');

                message(
                    `تعداد دستگاه‌ها: ${items.length}`
                );
            } catch (error) {
                message(
                    error.message ||
                    'خطا در دریافت دستگاه‌ها'
                );
            }
        }

        document
            .getElementById('createForm')
            .addEventListener('submit', async event => {

                event.preventDefault();

                const data =
                    Object.fromEntries(
                        new FormData(event.target)
                    );

                try {
                    const response =
                        await fetch(
                            '<?= e(url('/api/admin/compatibility/devices')) ?>', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify(data)
                            }
                        );

                    const result =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            result.message || 'ایجاد انجام نشد'
                        );
                    }

                    event.target.reset();

                    message('دستگاه ایجاد شد.');

                    await loadDevices();

                } catch (error) {
                    message(
                        error.message ||
                        'خطا در ایجاد دستگاه'
                    );
                }
            });

        async function toggleStatus(id, status) {
            const response =
                await fetch(
                    `<?= e(url('/api/admin/compatibility/devices/')) ?>${id}/status`, {
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
                message(
                    result.message ||
                    'تغییر وضعیت انجام نشد'
                );
                return;
            }

            await loadDevices();
        }

        async function removeDevice(id) {
            if (!confirm('آیا حذف این دستگاه انجام شود؟')) {
                return;
            }

            const response =
                await fetch(
                    `<?= e(url('/api/admin/compatibility/devices/')) ?>${id}`, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }
                );

            const result =
                await response.json();

            if (!response.ok) {
                message(
                    result.message ||
                    'حذف انجام نشد'
                );
                return;
            }

            await loadDevices();
        }

        loadDevices();
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>