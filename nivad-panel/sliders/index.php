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
    <title>اسلایدرها | نیواد</title>
</head>

<body>

    <main>

        <h1>اسلایدرها</h1>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>نام</th>
                    <th>Slug</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>

            <tbody id="rows"></tbody>
        </table>

    </main>

    <script>
        fetch('<?= e(url('/api/admin/sliders')) ?>')
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
                    <td>${item.status}</td>
                    <td>
                        <a
                            href="<?= e(url('/admin/sliders/edit?id=')) ?>${item.id}">
                            ویرایش
                        </a>
                    </td>
                </tr>
            `).join('');
            });
    </script>

</body>

</html>