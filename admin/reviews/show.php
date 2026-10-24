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
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>جزئیات دیدگاه | نیواد</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Tahoma, Arial
        }

        main {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px
        }

        textarea,
        select,
        button {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font: inherit
        }

        button {
            background: #111827;
            color: #fff;
            cursor: pointer
        }

        .message {
            padding: 12px;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 15px
        }
    </style>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>

    <main>
        <h1>جزئیات دیدگاه</h1>
        <div id="message" class="message">در حال دریافت...</div>
        <div id="review"></div>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken) ?>;
        const id = new URLSearchParams(location.search).get('id');

        function esc(v) {
            return String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
        }

        async function load() {
            if (!id) return;

            try {
                const response = await fetch(`/api/admin/reviews/${encodeURIComponent(id)}`, {
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (!response.ok) throw new Error(result.message || 'خطا');

                const item = result.data?.review || result.review || result.data;

                document.getElementById('review').innerHTML = `
<div class="card">
<p><strong>ID:</strong> ${esc(item.id)}</p>
<p><strong>Product:</strong> ${esc(item.product_id)}</p>
<p><strong>User:</strong> ${esc(item.user_id)}</p>
<p><strong>Rating:</strong> ${esc(item.rating)}</p>
<p><strong>Title:</strong> ${esc(item.title)}</p>
<p><strong>Body:</strong> ${esc(item.body)}</p>
</div>

<div class="card">
<label>وضعیت</label>
<select id="status">
<option value="pending">pending</option>
<option value="approved">approved</option>
<option value="rejected">rejected</option>
<option value="hidden">hidden</option>
</select>

<br><br>

<label>یادداشت مدیر</label>
<textarea id="note">${esc(item.admin_note)}</textarea>

<br><br>
<button id="save">ذخیره</button>
</div>`;

                document.getElementById('status').value = item.status || 'pending';

                document.getElementById('save').addEventListener('click', save);

                document.getElementById('message').textContent = 'اطلاعات دریافت شد.';
            } catch (error) {
                document.getElementById('message').textContent = error.message;
            }
        }

        async function save() {
            const status = document.getElementById('status').value;
            const admin_note = document.getElementById('note').value;

            for (const [url, body] of [
                    [`/api/admin/reviews/${encodeURIComponent(id)}/status`, {
                        status
                    }],
                    [
                        `/api/admin/reviews/${encodeURIComponent(id)}/note`,
                        {
                            adminNote: admin_note
                        }
                    ]
                ]) {
                const response = await fetch(url, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(body)
                });

                const result = await response.json();

                if (!response.ok) throw new Error(result.message || 'ذخیره انجام نشد');
            }

            document.getElementById('message').textContent = 'تغییرات ذخیره شد.';
        }

        load();
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>