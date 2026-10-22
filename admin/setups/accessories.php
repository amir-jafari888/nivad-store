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
    <title>Setup Accessories | نیواد</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Tahoma, Arial
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px
        }

        input,
        button {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px
        }

        button {
            cursor: pointer;
            background: #111827;
            color: #fff
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word
        }
    </style>
</head>

<body>

    <main>
        <h1>اکسسوری‌های Setup</h1>

        <input id="deviceId" type="number" min="1" placeholder="Device ID">

        <button id="load">دریافت</button>

        <div class="card">
            <pre id="result"></pre>
        </div>
    </main>

    <script>
        document.getElementById('load').addEventListener('click', async () => {
            const id = document.getElementById('deviceId').value;

            if (!id) return;

            const response = await fetch(
                `/api/setup/devices/${encodeURIComponent(id)}/accessories`, {
                    credentials: 'same-origin'
                }
            );

            const result = await response.json();

            document.getElementById('result').textContent =
                JSON.stringify(result.data ?? result, null, 2);
        });
    </script>

</body>

</html>