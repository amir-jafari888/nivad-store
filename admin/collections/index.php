<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';

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
    <title>مجموعه‌ها | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>

    <main>

        <h1>مجموعه‌ها</h1>

        <a href="<?= e(url('/admin')) ?>">
            داشبورد
        </a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>نام</th>
                    <th>Slug</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>

            <tbody id="rows"></tbody>
        </table>

    </main>

    <script>
        fetch('<?= e(url('/api/admin/collections')) ?>')
            .then(response => response.json())
            .then(result => {
                const items =
                    result.data?.items || [];

                document.getElementById('rows')
                    .innerHTML =
                    items.map(item => `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td>${item.slug}</td>
                    <td>${item.collectionType}</td>
                    <td>${item.status}</td>
                    <td>
                        <a
                            href="<?= e(url('/admin/collections/edit?id=')) ?>${item.id}">
                            ویرایش
                        </a>
                    </td>
                </tr>
            `).join('');
            });
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>