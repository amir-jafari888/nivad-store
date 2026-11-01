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
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>دستگاه‌های Setup | نیواد</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Tahoma, Arial
        }

        main {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 11px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right
        }

        a {
            color: #2563eb
        }
    </style>
</head>

<body>

    <main>
        <h1>دستگاه‌های Setup</h1>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>عملیات</th>
                    </tr>
                </thead>

                <tbody id="rows"></tbody>
            </table>
        </div>
    </main>

    <script>
        function esc(v) {
            return String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
        }

        async function load() {
            const response = await fetch('/api/setup/devices', {
                credentials: 'same-origin'
            });

            const result = await response.json();

            const items = result.data?.items || result.items || [];

            document.getElementById('rows').innerHTML = items.map(item => `
<tr>
<td>${esc(item.id)}</td>
<td>${esc(item.name)}</td>
<td>${esc(item.slug)}</td>
<td>
<a href="accessories.php?deviceId=${encodeURIComponent(item.id)}">
اکسسوری‌ها
</a>
</td>
</tr>
`).join('');
        }

        load();
    </script>

</body>

</html>