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
    <title>ایجاد کوپن | نیواد</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Tahoma, Arial;
            color: #1f2937
        }

        main {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px
        }

        .card {
            background: #fff;
            padding: 24px;
            border-radius: 14px
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px
        }

        .field.full {
            grid-column: 1/-1
        }

        input,
        select,
        textarea,
        button {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font: inherit
        }

        textarea {
            min-height: 100px
        }

        button {
            cursor: pointer;
            background: #111827;
            color: #fff
        }

        .message {
            margin-bottom: 15px;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 8px
        }

        @media(max-width:700px) {
            .grid {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>

    <main>
        <h1>ایجاد کوپن</h1>

        <div id="message" class="message">اطلاعات کوپن را وارد کنید.</div>

        <form id="form" class="card">
            <div class="grid">

                <div class="field">
                    <label>کد کوپن</label>
                    <input name="code" required>
                </div>

                <div class="field">
                    <label>عنوان</label>
                    <input name="title">
                </div>

                <div class="field full">
                    <label>توضیحات</label>
                    <textarea name="description"></textarea>
                </div>

                <div class="field">
                    <label>نوع تخفیف</label>
                    <select name="discount_type" required>
                        <option value="">انتخاب کنید</option>
                        <option value="fixed">fixed</option>
                        <option value="percentage">percentage</option>
                    </select>
                </div>

                <div class="field">
                    <label>مقدار تخفیف</label>
                    <input name="discount_value" required>
                </div>

                <div class="field">
                    <label>حداقل مبلغ سفارش</label>
                    <input name="minimum_order_amount">
                </div>

                <div class="field">
                    <label>حداکثر مبلغ تخفیف</label>
                    <input name="maximum_discount_amount">
                </div>

                <div class="field">
                    <label>محدودیت استفاده</label>
                    <input name="usage_limit" type="number" min="0">
                </div>

                <div class="field">
                    <label>محدودیت هر کاربر</label>
                    <input name="usage_limit_per_user" type="number" min="0">
                </div>

                <div class="field">
                    <label>شروع</label>
                    <input name="starts_at" type="datetime-local">
                </div>

                <div class="field">
                    <label>انقضا</label>
                    <input name="expires_at" type="datetime-local">
                </div>

                <div class="field">
                    <label>وضعیت</label>
                    <select name="status">
                        <option value="active">active</option>
                        <option value="inactive">inactive</option>
                    </select>
                </div>

            </div>

            <br>
            <button type="submit">ایجاد کوپن</button>
        </form>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken) ?>;

        document.getElementById('form').addEventListener('submit', async e => {
            e.preventDefault();

            const form = new FormData(e.currentTarget);
            const data = Object.fromEntries(form.entries());

            try {
                const response = await fetch('/api/admin/coupons', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) throw new Error(result.message || 'ایجاد کوپن انجام نشد');

                document.getElementById('message').textContent = 'کوپن با موفقیت ایجاد شد.';
                e.currentTarget.reset();
            } catch (error) {
                document.getElementById('message').textContent = error.message;
            }
        });
    </script>

</body>

</html>