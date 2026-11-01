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
    <title>جزئیات پرسش | نیواد</title>
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

        button:disabled {
            opacity: .6;
            cursor: not-allowed
        }

        .message {
            padding: 12px;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 15px
        }

        .answer {
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
            margin-top: 15px
        }
    </style>
</head>

<body>

    <main>
        <h1>جزئیات پرسش</h1>

        <div
            id="message"
            class="message"
            aria-live="polite">
            در حال دریافت...
        </div>

        <div id="question"></div>

        <div id="answers"></div>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken) ?>;

        const id =
            new URLSearchParams(
                location.search
            ).get('id');

        function esc(v) {
            return String(v ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function api(
            url,
            options = {}
        ) {
            const response =
                await fetch(url, {
                    credentials: 'same-origin',
                    ...options,
                    headers: {
                        Accept: 'application/json',
                        ...(options.headers || {})
                    }
                });

            const result =
                await response
                .json()
                .catch(() => ({}));

            if (!response.ok) {
                throw new Error(
                    result.message ||
                    'درخواست ناموفق بود.'
                );
            }

            return result;
        }

        async function loadAnswers() {
            const answers =
                document.getElementById(
                    'answers'
                );

            answers.innerHTML =
                '<div class="card">در حال دریافت پاسخ‌ها...</div>';

            try {
                const result =
                    await api(
                        `/api/questions/${encodeURIComponent(
                            id
                        )}/answers`
                    );

                const items =
                    result.data?.items ||
                    result.items || [];

                answers.innerHTML = `
                    <div class="card">
                        <h2>پاسخ‌ها</h2>

                        <div id="answer-list">
                            ${
                                items.length
                                    ? items
                                          .map(
                                              (
                                                  item
                                              ) => `
                                    <article class="answer">

                                        <p>
                                            ${esc(
                                                item.answer ||
                                                item.body
                                            )}
                                        </p>

                                        <p>
                                            وضعیت:
                                            ${esc(
                                                item.status
                                            )}
                                        </p>

                                        <select
                                            data-answer-status="${esc(
                                                item.id
                                            )}">

                                            <option
                                                value="pending"
                                                ${
                                                    item.status ===
                                                    'pending'
                                                        ? 'selected'
                                                        : ''
                                                }>
                                                pending
                                            </option>

                                            <option
                                                value="approved"
                                                ${
                                                    item.status ===
                                                    'approved'
                                                        ? 'selected'
                                                        : ''
                                                }>
                                                approved
                                            </option>

                                            <option
                                                value="rejected"
                                                ${
                                                    item.status ===
                                                    'rejected'
                                                        ? 'selected'
                                                        : ''
                                                }>
                                                rejected
                                            </option>

                                            <option
                                                value="hidden"
                                                ${
                                                    item.status ===
                                                    'hidden'
                                                        ? 'selected'
                                                        : ''
                                                }>
                                                hidden
                                            </option>

                                        </select>

                                    </article>
                                `
                                          )
                                          .join('')
                                    : '<p>هنوز پاسخی ثبت نشده است.</p>'
                            }
                        </div>
                    </div>
                `;
            } catch (error) {
                answers.innerHTML = `
                    <div
                        class="card"
                        aria-live="polite">
                        ${esc(error.message)}
                    </div>
                `;
            }
        }

        async function load() {
            if (!id) {
                document.getElementById(
                        'message'
                    ).textContent =
                    'شناسه پرسش نامعتبر است.';

                return;
            }

            try {
                const result =
                    await api(
                        `/api/admin/questions/${encodeURIComponent(
                            id
                        )}`
                    );

                const item =
                    result.data?.question ||
                    result.question ||
                    result.data;

                document.getElementById(
                    'question'
                ).innerHTML = `
                    <div class="card">

                        <p>
                            <strong>ID:</strong>
                            ${esc(item.id)}
                        </p>

                        <p>
                            <strong>Product:</strong>
                            ${esc(item.product_id)}
                        </p>

                        <p>
                            <strong>User:</strong>
                            ${esc(item.user_id)}
                        </p>

                        <p>
                            <strong>Question:</strong>
                            ${esc(item.question)}
                        </p>

                    </div>

                    <div class="card">

                        <label for="status">
                            وضعیت
                        </label>

                        <select id="status">

                            <option value="pending">
                                pending
                            </option>

                            <option value="approved">
                                approved
                            </option>

                            <option value="rejected">
                                rejected
                            </option>

                            <option value="hidden">
                                hidden
                            </option>

                        </select>

                        <br><br>

                        <label for="note">
                            یادداشت مدیر
                        </label>

                        <textarea id="note">${esc(
                            item.admin_note
                        )}</textarea>

                        <br><br>

                        <button
                            id="save"
                            type="button">
                            ذخیره تغییرات
                        </button>

                    </div>

                    <div class="card">

                        <h2>
                            ثبت پاسخ مدیر
                        </h2>

                        <textarea
                            id="answer"
                            rows="5"
                            placeholder="پاسخ مدیر را وارد کنید"></textarea>

                        <br><br>

                        <button
                            id="answer-save"
                            type="button">
                            ثبت پاسخ
                        </button>

                    </div>
                `;

                document.getElementById(
                        'status'
                    ).value =
                    item.status ||
                    'pending';

                document.getElementById(
                    'save'
                ).addEventListener(
                    'click',
                    save
                );

                document.getElementById(
                    'answer-save'
                ).addEventListener(
                    'click',
                    createAnswer
                );

                document.getElementById(
                        'message'
                    ).textContent =
                    'اطلاعات دریافت شد.';

                await loadAnswers();
            } catch (error) {
                document.getElementById(
                        'message'
                    ).textContent =
                    error.message;
            }
        }

        async function save() {
            const button =
                document.getElementById(
                    'save'
                );

            button.disabled = true;

            try {
                const requests = [
                    [
                        `/api/admin/questions/${encodeURIComponent(
                            id
                        )}/status`,
                        {
                            status: document.getElementById(
                                'status'
                            ).value
                        }
                    ],
                    [
                        `/api/admin/questions/${encodeURIComponent(
                            id
                        )}/note`,
                        {
                            admin_note: document.getElementById(
                                'note'
                            ).value
                        }
                    ]
                ];

                for (
                    const [
                        url,
                        body
                    ] of requests
                ) {
                    await api(
                        url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(
                                body
                            )
                        }
                    );
                }

                document.getElementById(
                        'message'
                    ).textContent =
                    'تغییرات ذخیره شد.';
            } catch (error) {
                document.getElementById(
                        'message'
                    ).textContent =
                    error.message;
            } finally {
                button.disabled = false;
            }
        }

        async function createAnswer() {
            const button =
                document.getElementById(
                    'answer-save'
                );

            const answer =
                document.getElementById(
                    'answer'
                ).value.trim();

            if (!answer) {
                document.getElementById(
                        'message'
                    ).textContent =
                    'متن پاسخ الزامی است.';

                return;
            }

            button.disabled = true;

            try {
                await api(
                    `/api/admin/questions/${encodeURIComponent(
                        id
                    )}/answers`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            answer
                        })
                    }
                );

                document.getElementById(
                    'answer'
                ).value = '';

                document.getElementById(
                        'message'
                    ).textContent =
                    'پاسخ با موفقیت ثبت شد.';

                await loadAnswers();
            } catch (error) {
                document.getElementById(
                        'message'
                    ).textContent =
                    error.message;
            } finally {
                button.disabled = false;
            }
        }

        document.addEventListener(
            'change',
            async (event) => {
                const select =
                    event.target.closest(
                        '[data-answer-status]'
                    );

                if (!select) {
                    return;
                }

                try {
                    await api(
                        `/api/admin/answers/${encodeURIComponent(
                            select.dataset
                                .answerStatus
                        )}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                status: select.value
                            })
                        }
                    );

                    document.getElementById(
                            'message'
                        ).textContent =
                        'وضعیت پاسخ به‌روزرسانی شد.';
                } catch (error) {
                    document.getElementById(
                            'message'
                        ).textContent =
                        error.message;

                    await loadAnswers();
                }
            }
        );

        load();
    </script>

</body>

</html>