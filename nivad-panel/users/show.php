<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';

$csrfToken = Csrf::token();
$userId =
    (int)($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width,initial-scale=1">

    <title>جزئیات کاربر | نیواد</title>

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
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 16px
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06)
        }

        .grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px
        }

        .item {
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px
        }

        .label {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 7px
        }

        .value {
            font-weight: 600
        }

        select,
        button {
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px
        }

        button {
            cursor: pointer
        }

        .message {
            margin-bottom: 15px;
            padding: 12px;
            background: #fff;
            border-radius: 8px
        }
    </style>
</head>

<body>

    <header>
        <strong>جزئیات کاربر</strong>
    </header>

    <main>

        <h1>جزئیات کاربر</h1>

        <div id="message" class="message">
            در حال دریافت...
        </div>

        <section class="card">

            <div
                id="user"
                class="grid">
            </div>

            <br>

            <div>
                <label>
                    وضعیت:
                </label>

                <select id="status">
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                    <option value="blocked">blocked</option>
                    <option value="pending">pending</option>
                </select>

                <button
                    type="button"
                    id="saveStatus">
                    ذخیره وضعیت
                </button>
            </div>

        </section>

    </main>

    <script>
        const userId =
            <?= (int)$userId ?>;

        const csrfToken =
            <?= json_encode(
                $csrfToken,
                JSON_UNESCAPED_UNICODE
            ) ?>;

        let currentUser = null;

        function esc(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function loadUser() {
            const message =
                document.getElementById('message');

            if (userId < 1) {
                message.textContent =
                    'شناسه کاربر نامعتبر است.';
                return;
            }

            try {
                const response =
                    await fetch(
                        `<?= e(url('/api/admin/users/')) ?>${userId}`, {
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

                currentUser =
                    result.data?.user ||
                    result.user ||
                    null;

                if (!currentUser) {
                    throw new Error(
                        'اطلاعات کاربر دریافت نشد.'
                    );
                }

                document.getElementById('status')
                    .value =
                    currentUser.status || 'pending';

                document.getElementById('user')
                    .innerHTML = `
                    <div class="item">
                        <div class="label">شناسه</div>
                        <div class="value">
                            ${esc(currentUser.id)}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">نام</div>
                        <div class="value">
                            ${esc(currentUser.first_name)}
                            ${esc(currentUser.last_name)}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">ایمیل</div>
                        <div class="value">
                            ${esc(currentUser.email)}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">تلفن</div>
                        <div class="value">
                            ${esc(currentUser.phone)}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">تأیید ایمیل</div>
                        <div class="value">
                            ${esc(currentUser.email_verified_at)}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">تأیید تلفن</div>
                        <div class="value">
                            ${esc(currentUser.phone_verified_at)}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">آخرین ورود</div>
                        <div class="value">
                            ${esc(currentUser.last_login_at)}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label">تاریخ ایجاد</div>
                        <div class="value">
                            ${esc(currentUser.created_at)}
                        </div>
                    </div>
                `;

                message.textContent =
                    'اطلاعات کاربر دریافت شد.';

            } catch (error) {
                message.textContent =
                    error.message ||
                    'خطا در دریافت کاربر';
            }
        }

        document
            .getElementById('saveStatus')
            .addEventListener('click', async () => {

                const status =
                    document.getElementById('status')
                    .value;

                const response =
                    await fetch(
                        `<?= e(url('/api/admin/users/')) ?>${userId}/status`, {
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

                document.getElementById('message')
                    .textContent =
                    response.ok ?
                    'وضعیت کاربر ذخیره شد.' :
                    (
                        result.message ||
                        'تغییر وضعیت انجام نشد.'
                    );

                if (response.ok) {
                    await loadUser();
                }
            });

        loadUser();
    </script>

</body>

</html>