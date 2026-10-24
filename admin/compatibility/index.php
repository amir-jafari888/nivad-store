<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>سازگاری محصولات | نیواد</title>

    <style>
        body {
            margin: 0;
            font-family: Tahoma, sans-serif;
            background: #f5f7fa;
            color: #1f2937;
        }

        header {
            padding: 24px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        header a {
            color: inherit;
            text-decoration: none;
        }

        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 24px;
        }

        .summary-card {
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
        }

        .summary-card h2 {
            margin: 0 0 12px;
            font-size: 16px;
        }

        .summary-value {
            font-size: 32px;
            font-weight: 700;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .action {
            display: inline-block;
            padding: 12px 18px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            color: #111827;
            text-decoration: none;
        }

        .status {
            margin-top: 20px;
            color: #6b7280;
        }
    </style>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>

    <header>
        <h1>مدیریت سازگاری محصولات</h1>

        <a href="<?= e(url('/admin')) ?>">
            بازگشت به داشبورد
        </a>
    </header>

    <main>

        <p>
            مدیریت دستگاه‌ها و ارتباط سازگاری آن‌ها با محصولات.
        </p>

        <section
            class="summary-grid"
            aria-label="خلاصه سازگاری">

            <article class="summary-card">
                <h2>دستگاه‌های فعال</h2>

                <div
                    id="active-devices"
                    class="summary-value">
                    —
                </div>
            </article>

            <article class="summary-card">
                <h2>ارتباط‌های سازگاری</h2>

                <div
                    id="compatibility-mappings"
                    class="summary-value">
                    —
                </div>
            </article>

        </section>

        <div class="actions">
            <a
                class="action"
                href="<?= e(
                            url('/admin/compatibility/devices')
                        ) ?>">
                مدیریت دستگاه‌ها
            </a>

            <a
                class="action"
                href="#compatibility-mappings">
                مدیریت ارتباط‌ها
            </a>

        </div>

        <section
            id="compatibility-mappings"
            class="card"
            aria-labelledby="compatibility-mappings-title">

            <h2 id="compatibility-mappings-title">
                ارتباط محصولات و دستگاه‌ها
            </h2>

            <form
                id="mapping-form">

                <div class="grid">

                    <input
                        name="product_id"
                        type="number"
                        min="1"
                        placeholder="شناسه محصول"
                        required>

                    <select
                        name="compatibility_device_id"
                        id="mapping-device"
                        required>

                        <option value="">
                            انتخاب دستگاه
                        </option>

                    </select>

                    <select
                        name="compatibility_type"
                        required>

                        <option value="compatible">
                            سازگار
                        </option>

                        <option value="partially_compatible">
                            سازگاری جزئی
                        </option>

                        <option value="not_compatible">
                            ناسازگار
                        </option>

                    </select>

                    <input
                        name="sort_order"
                        type="number"
                        min="0"
                        value="0"
                        placeholder="ترتیب">

                </div>

                <textarea
                    name="note"
                    placeholder="توضیحات">
        </textarea>

                <br>

                <button type="submit">
                    ایجاد ارتباط
                </button>

            </form>

            <br>

            <table>

                <thead>
                    <tr>
                        <th>محصول</th>
                        <th>دستگاه</th>
                        <th>نوع</th>
                        <th>ترتیب</th>
                        <th>عملیات</th>
                    </tr>
                </thead>

                <tbody id="mapping-rows"></tbody>

            </table>

        </section>

        <p
            id="status"
            class="status">
            در حال دریافت اطلاعات...
        </p>

    </main>

    <script>
        (async function() {
            const status =
                document.getElementById('status');

            const activeDevices =
                document.getElementById('active-devices');

            const mappings =
                document.getElementById(
                    'compatibility-mappings'
                );

            try {
                const response =
                    await fetch(
                        '<?= e(url('/api/admin/compatibility')) ?>', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );

                const payload =
                    await response.json();

                if (
                    !response.ok ||
                    !payload.success
                ) {
                    throw new Error(
                        'دریافت اطلاعات انجام نشد.'
                    );
                }

                const summary =
                    payload.data?.summary ?? {};

                activeDevices.textContent =
                    Number(
                        summary.activeDevices ?? 0
                    );

                mappings.textContent =
                    Number(
                        summary.mappings ?? 0
                    );

                status.textContent =
                    'اطلاعات با موفقیت دریافت شد.';
            } catch (error) {
                activeDevices.textContent = '—';
                mappings.textContent = '—';

                status.textContent =
                    'دریافت اطلاعات با خطا مواجه شد.';
            }
            
            const mappingForm =
    document.getElementById(
        'mapping-form'
    );

const mappingDevice =
    document.getElementById(
        'mapping-device'
    );

const mappingRows =
    document.getElementById(
        'mapping-rows'
    );

async function loadMappingDevices() {
    const response =
        await fetch(
            '<?= e(
                url('/api/admin/compatibility/devices')
            ) ?>'
            ,{
                credentials:
                    'same-origin',

                headers: {
                    'Accept':
                        'application/json'
                }
            }
        );

    const payload =
        await response.json();

    if (!response.ok) {
        throw new Error(
            payload.message ||
            'دریافت دستگاه‌ها ناموفق بود.'
        );
    }

    const items =
        payload.data?.items ||
        payload.items ||
        [];

    mappingDevice.innerHTML = `
        <option value="">
            انتخاب دستگاه
        </option>
    `;

    items.forEach(device => {
        const option =
            document.createElement(
                'option'
            );

        option.value =
            String(device.id);

        option.textContent =
            [
                device.brandName ||
                    device.brand_name,

                device.name,

                device.model
            ]
                .filter(Boolean)
                .join(' - ');

        mappingDevice.appendChild(
            option
        );
    });
}

async function loadMappings() {
    const response =
        await fetch(
            '<?= e(
                url('/api/admin/compatibility/mappings')
            ) ?>'
            ,{
                credentials:
                    'same-origin',

                headers: {
                    'Accept':
                        'application/json'
                }
            }
        );

    const payload =
        await response.json();

    if (!response.ok) {
        throw new Error(
            payload.message ||
            'دریافت ارتباط‌ها ناموفق بود.'
        );
    }

    const items =
        payload.data?.items ||
        [];

    mappingRows.innerHTML =
        items.map(item => `
            <tr>
                <td>
                    ${esc(
                        item.product_name
                    )}
                    <small>
                        #${esc(
                            item.product_id
                        )}
                    </small>
                </td>

                <td>
                    ${esc(
                        item.brand_name
                    )}
                    -
                    ${esc(
                        item.device_name
                    )}

                    ${
                        item.device_model
                            ? ` - ${esc(
                                item.device_model
                            )}`
                            : ''
                    }
                </td>

                <td>
                    ${esc(
                        item.compatibility_type
                    )}
                </td>

                <td>
                    ${esc(
                        item.sort_order
                    )}
                </td>

                <td class="actions">

                    <button
                        type="button"
                        onclick="removeMapping(
                            ${Number(
                                item.product_id
                            )},
                            ${Number(
                                item.compatibility_device_id
                            )}
                        )">
                        حذف
                    </button>

                </td>
            </tr>
        `).join('');
}

window.removeMapping =
    async function(
        productId,
        deviceId
    ) {
        if (
            !window.confirm(
                'این ارتباط حذف شود؟'
            )
        ) {
            return;
        }

        const response =
            await fetch(
                '<?= e(
                    url('/api/admin/compatibility/mappings')
                ) ?>',
               , {
                    method: 'DELETE',

                    credentials:
                        'same-origin',

                    headers: {
                        'Accept':
                            'application/json',

                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken
                    },

                    body:
                        JSON.stringify({
                            product_id:
                                productId,

                            compatibility_device_id:
                                deviceId
                        })
                }
            );

        const payload =
            await response.json();

        if (!response.ok) {
            throw new Error(
                payload.message ||
                'حذف ارتباط ناموفق بود.'
            );
        }

        await loadMappings();

        message(
            'ارتباط حذف شد.'
        );
    };

mappingForm?.addEventListener(
    'submit',
    async event => {
        event.preventDefault();

        const data =
            Object.fromEntries(
                new FormData(
                    event.target
                )
            );

        try {
            const response =
                await fetch(
                    '<?= e(
                        url('/api/admin/compatibility/mappings')
                    ) ?>',
                   , {
                        method: 'POST',

                        credentials:
                            'same-origin',

                        headers: {
                            'Accept':
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken
                        },

                        body:
                            JSON.stringify(
                                data
                            )
                    }
                );

            const payload =
                await response.json();

            if (!response.ok) {
                throw new Error(
                    payload.message ||
                    'ایجاد ارتباط ناموفق بود.'
                );
            }

            event.target.reset();

            await loadMappings();

            message(
                'ارتباط با موفقیت ایجاد شد.'
            );
        } catch (error) {
            message(
                error.message ||
                'ایجاد ارتباط ناموفق بود.'
            );
        }
    }
);

try {
    await loadMappingDevices();
    await loadMappings();
} catch (error) {
    message(
        error.message ||
        'خطا در بارگذاری Mappingها.'
    );
}
            
        }

        
    )();
    </script>

    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>