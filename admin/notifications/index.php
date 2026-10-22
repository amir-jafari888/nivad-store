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
    <title>ارسال اعلان | نیواد</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Tahoma, Arial
        }

        main {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 14px
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 15px
        }

        input,
        textarea,
        button {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font: inherit
        }

        textarea {
            min-height: 130px
        }

        button {
            background: #111827;
            color: #fff;
            cursor: pointer
        }

        .message {
            padding: 12px;
            background: #f3f4f6;
            border-radius: 8px;
            margin-bottom: 15px
        }
    </style>
</head>

<body>

    <main>
        <h1>ارسال اعلان برای کاربر</h1>

        <div id="message" class="message">
            اطلاعات اعلان را وارد کنید.
        </div>

        <form id="form" class="card">

            <div class="field">
                <label>User ID</label>
                <input name="user_id" type="number" min="1" required>
            </div>

            <div class="field">
                <label>Type</label>
                <input name="type" required>
            </div>

            <div class="field">
                <label>عنوان</label>
                <input name="title" required>
            </div>

            <div class="field">
                <label>پیام</label>
                <textarea name="message" required></textarea>
            </div>

            <div class="field">
                <label>Data JSON اختیاری</label>
                <textarea name="data" placeholder='{"key":"value"}'></textarea>
            </div>

            <button type="submit">ارسال اعلان</button>

        </form>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken) ?>;

        document.getElementById('form').addEventListener('submit', async e => {
            e.preventDefault();

            const form = e.currentTarget;
            const data = Object.fromEntries(new FormData(form).entries());

            if (data.data.trim() !== '') {
                try {
                    data.data = JSON.parse(data.data);
                } catch {
                    document.getElementById('message').textContent =
                        'فیلد Data باید JSON معتبر باشد.';
                    return;
                }
            } else {
                data.data = null;
            }

            try {
                const response = await fetch('/api/admin/notifications', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'ایجاد اعلان انجام نشد');
                }

                document.getElementById('message').textContent =
                    'اعلان با موفقیت ایجاد شد.';

                form.reset();
            } catch (error) {
                document.getElementById('message').textContent = error.message;
            }
        });
    </script>

</body>

</html>