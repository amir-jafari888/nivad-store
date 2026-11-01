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
    <title>حرکت‌های موجودی | نیواد</title>
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
            max-width: 1300px;
            margin: 30px auto;
            padding: 0 16px
        }

        h1 {
            margin: 0 0 20px
        }

        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px
        }

        input,
        button {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff
        }

        button {
            cursor: pointer
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: auto
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            background: #f3f4f6;
            margin-bottom: 15px
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word;
            margin: 0
        }
    </style>
</head>

<body>

    <header>
        <strong>حرکت‌های موجودی</strong>
    </header>

    <main>
        <h1>حرکت‌های موجودی Variant</h1>

        <div class="toolbar">
            <input id="variantId" type="number" min="1" placeholder="Variant ID">
            <button type="button" id="load">نمایش حرکت‌ها</button>
        </div>

        <div id="message" class="message">شناسه Variant را وارد کنید.</div>

        <div class="card">
            <pre id="result"></pre>
        </div>
    </main>

    <script>
        async function loadMovements() {
            const variantId = document.getElementById('variantId').value.trim();

            if (!variantId) {
                document.getElementById('message').textContent =
                    'Variant ID را وارد کنید.';
                return;
            }

            try {
                const response = await fetch(
                    `/api/admin/variants/${encodeURIComponent(variantId)}/inventory-movements`, {
                        credentials: 'same-origin'
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'خطا در دریافت حرکت‌های موجودی');
                }

                document.getElementById('result').textContent =
                    JSON.stringify(data.data ?? data, null, 2);

                document.getElementById('message').textContent =
                    'حرکت‌های موجودی دریافت شد.';
            } catch (error) {
                document.getElementById('message').textContent =
                    error.message || 'خطا در دریافت اطلاعات';

                document.getElementById('result').textContent = '';
            }
        }

        document.getElementById('load').addEventListener('click', loadMovements);
    </script>

</body>

</html>