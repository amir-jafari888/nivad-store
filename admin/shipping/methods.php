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

    <title>روش‌های ارسال | نیواد</title>

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
        <strong>مدیریت روش‌های ارسال</strong>
    </header>

    <main>

        <h1>روش‌های ارسال</h1>

        <div id="message" class="message">
            در حال دریافت...
        </div>

        <section class="card">

            <h2>ایجاد روش ارسال</h2>

            <form id="createForm">

                <div class="grid">

                    <input
                        name="name"
                        placeholder="نام"
                        required>

                    <input
                        name="slug"
                        placeholder="Slug"
                        required>

                    <input
                        name="base_cost"
                        placeholder="هزینه پایه"
                        required>

                    <input
                        name="currency"
                        placeholder="واحد پول"
                        value="IRR"
                        required>

                    <input
                        name="estimated_min_days"
                        type="number"
                        min="0"
                        placeholder="حداقل روز">

                    <input
                        name="estimated_max_days"
                        type="number"
                        min="0"
                        placeholder="حداکثر روز">

                    <select name="status">
                        <option value="active">
                            active
                        </option>

                        <option value="inactive">
                            inactive
                        </option>
                    </select>

                    <input
                        name="sort_order"
                        type="number"
                        min="0"
                        value="0"
                        placeholder="ترتیب">

                </div>

                <br>

                <textarea
                    name="description"
                    placeholder="توضیحات"></textarea>

                <br>

                <button type="submit">
                    ایجاد روش ارسال
                </button>

            </form>

        </section>

        <section class="card">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام</th>
                        <th>Slug</th>
                        <th>هزینه</th>
                        <th>واحد</th>
                        <th>بازه ارسال</th>
                        <th>وضعیت</th>
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

        function showMessage(text) {
            document.getElementById('message')
                .textContent = text;
        }

        async function loadMethods() {
            try {
                const response =
                    await fetch(
                        '<?= e(url('/api/admin/shipping/methods')) ?>', {
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
                    result.data?.methods ||
                    result.methods || [];

                document.getElementById('rows')
                    .innerHTML =
                    items.map(item => `
                    <tr>
                        <td>${esc(item.id)}</td>
                        <td>${esc(item.name)}</td>
                        <td>${esc(item.slug)}</td>
                        <td>${esc(item.baseCost ?? item.base_cost)}</td>
                        <td>${esc(item.currency)}</td>
                        <td>
                            ${esc(
                                item.estimatedMinDays
                                ?? item.estimated_min_days
                            )}
                            -
                            ${esc(
                                item.estimatedMaxDays
                                ?? item.estimated_max_days
                            )}
                        </td>
                        <td>${esc(item.status)}</td>
                        <td class="actions">

                            <button
                                type="button"
                                onclick="editMethod(${Number(item.id)})">
                                ویرایش
                            </button>

                            <button
                                type="button"
                                onclick="removeMethod(${Number(item.id)})">
                                حذف
                            </button>

                        </td>
                    </tr>
                `).join('');

                showMessage(
                    `تعداد روش‌های ارسال: ${items.length}`
                );

            } catch (error) {
                showMessage(
                    error.message ||
                    'خطا در دریافت روش‌های ارسال'
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
                            '<?= e(url('/api/admin/shipping/methods')) ?>', {
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
                            result.message ||
                            'ایجاد انجام نشد'
                        );
                    }

                    event.target.reset();

                    showMessage(
                        'روش ارسال ایجاد شد.'
                    );

                    await loadMethods();

                } catch (error) {
                    showMessage(
                        error.message ||
                        'خطا در ایجاد روش ارسال'
                    );
                }
            });

        async function editMethod(id) {
            const name =
                prompt('نام روش ارسال جدید:');

            if (name === null || name.trim() === '') {
                return;
            }

            const slug =
                prompt('Slug روش ارسال جدید:');

            if (slug === null || slug.trim() === '') {
                return;
            }

            const baseCost =
                prompt('هزینه پایه:');

            if (
                baseCost === null ||
                baseCost.trim() === ''
            ) {
                return;
            }

            const currency =
                prompt('واحد پول:', 'IRR');

            if (
                currency === null ||
                currency.trim() === ''
            ) {
                return;
            }

            const status =
                prompt(
                    'وضعیت: active یا inactive',
                    'active'
                );

            if (
                status !== 'active' &&
                status !== 'inactive'
            ) {
                showMessage(
                    'وضعیت نامعتبر است.'
                );
                return;
            }

            const response =
                await fetch(
                    `<?= e(url('/api/admin/shipping/methods/')) ?>${id}`, {
                        method: 'PUT',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name: name.trim(),
                            slug: slug.trim(),
                            description: null,
                            base_cost: baseCost.trim(),
                            currency: currency.trim(),
                            estimated_min_days: null,
                            estimated_max_days: null,
                            status,
                            sort_order: 0
                        })
                    }
                );

            const result =
                await response.json();

            if (!response.ok) {
                showMessage(
                    result.message ||
                    'ویرایش انجام نشد'
                );
                return;
            }

            showMessage(
                'روش ارسال به‌روزرسانی شد.'
            );

            await loadMethods();
        }

        async function removeMethod(id) {
            if (
                !confirm(
                    'آیا حذف این روش ارسال انجام شود؟'
                )
            ) {
                return;
            }

            const response =
                await fetch(
                    `<?= e(url('/api/admin/shipping/methods/')) ?>${id}`, {
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
                showMessage(
                    result.message ||
                    'حذف انجام نشد'
                );
                return;
            }

            showMessage(
                'روش ارسال حذف شد.'
            );

            await loadMethods();
        }

        loadMethods();
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>