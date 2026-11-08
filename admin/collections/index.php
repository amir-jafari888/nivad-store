<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$collectionsApiUrl = url('/api/admin/collections');
$collectionEditUrl = url('/admin/collections/edit?id=');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت مجموعه‌ها | نیواد</title>
</head>
<body>
    <main class="admin-products-page admin-collections-page">
        <div class="admin-products-content">
            <header class="admin-products-heading">
                <div class="admin-products-heading-copy">
                    <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>مدیریت مجموعه‌ها</h1>
                    <p>مجموعه‌های منتخب و خودکار فروشگاه را بررسی و مدیریت کن.</p>
                </div>
                <button class="admin-products-primary-action" type="button" id="collections-refresh">
                    <span aria-hidden="true">↻</span>
                    بارگذاری مجدد
                </button>
            </header>

            <section class="admin-products-summary admin-collections-summary" aria-label="خلاصه مجموعه‌ها">
                <article class="admin-products-stat admin-products-stat-total">
                    <span class="admin-products-stat-label">کل مجموعه‌ها</span>
                    <strong id="collections-total" aria-live="polite">—</strong>
                    <span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span>
                </article>
                <article class="admin-products-stat">
                    <span class="admin-products-stat-label">فعال</span>
                    <strong id="collections-active" aria-live="polite">—</strong>
                    <span class="admin-products-stat-note">مجموعه فعال</span>
                </article>
                <article class="admin-products-stat">
                    <span class="admin-products-stat-label">غیرفعال</span>
                    <strong id="collections-inactive" aria-live="polite">—</strong>
                    <span class="admin-products-stat-note">مجموعه غیرفعال</span>
                </article>
            </section>

            <section class="admin-products-panel" aria-labelledby="admin-collections-list-title">
                <div class="admin-products-panel-heading">
                    <div>
                        <h2 id="admin-collections-list-title">فهرست مجموعه‌ها</h2>
                        <p id="collections-result-count" aria-live="polite">در حال دریافت مجموعه‌ها…</p>
                    </div>
                </div>

                <div class="admin-products-filters admin-collections-filters">
                    <label class="admin-products-search" for="collections-search">
                        <span>جست‌وجو</span>
                        <input id="collections-search" type="search" placeholder="نام، شناسه یا Slug مجموعه…" autocomplete="off" disabled>
                    </label>
                    <label class="admin-products-status-filter" for="collections-status">
                        <span>وضعیت مجموعه</span>
                        <select id="collections-status" disabled>
                            <option value="">همه وضعیت‌ها</option>
                            <option value="active">فعال</option>
                            <option value="inactive">غیرفعال</option>
                        </select>
                    </label>
                    <div class="admin-products-filter-actions">
                        <button type="button" id="collections-clear-filters" disabled>پاک‌کردن فیلترها</button>
                    </div>
                </div>

                <div id="collections-feedback" class="admin-collections-feedback" role="status" aria-live="polite">
                    <span class="admin-collections-loading-indicator" aria-hidden="true"></span>
                    <span>در حال دریافت فهرست مجموعه‌ها…</span>
                </div>

                <div class="admin-products-empty" id="collections-empty" hidden>
                    <span class="admin-products-empty-icon" aria-hidden="true">▤</span>
                    <h3 id="collections-empty-title">هنوز مجموعه‌ای ثبت نشده است</h3>
                    <p id="collections-empty-description">پس از ثبت مجموعه‌های فروشگاه، اطلاعات آن‌ها در این فهرست نمایش داده می‌شود.</p>
                </div>

                <div class="admin-products-table-wrap admin-collections-table-wrap" id="collections-table-wrap" hidden>
                    <table class="admin-products-table admin-collections-table">
                        <thead>
                            <tr>
                                <th scope="col">شناسه</th>
                                <th scope="col">مجموعه</th>
                                <th scope="col">Slug</th>
                                <th scope="col">نوع مجموعه</th>
                                <th scope="col">وضعیت</th>
                                <th scope="col">اولویت</th>
                                <th scope="col">بازه نمایش</th>
                                <th scope="col">عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="collections-rows"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script>
        (() => {
            const apiUrl = <?= json_encode($collectionsApiUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
            const editUrl = <?= json_encode($collectionEditUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
            const el = {
                total: document.getElementById('collections-total'),
                active: document.getElementById('collections-active'),
                inactive: document.getElementById('collections-inactive'),
                count: document.getElementById('collections-result-count'),
                search: document.getElementById('collections-search'),
                status: document.getElementById('collections-status'),
                clear: document.getElementById('collections-clear-filters'),
                refresh: document.getElementById('collections-refresh'),
                feedback: document.getElementById('collections-feedback'),
                empty: document.getElementById('collections-empty'),
                emptyTitle: document.getElementById('collections-empty-title'),
                emptyDescription: document.getElementById('collections-empty-description'),
                table: document.getElementById('collections-table-wrap'),
                rows: document.getElementById('collections-rows')
            };
            let collections = [];
            let isLoading = false;

            const value = item => item === null || item === undefined || item === '' ? '—' : String(item);
            const statusText = status => status === 'active' ? 'فعال' : status === 'inactive' ? 'غیرفعال' : 'نامشخص';
            const typeText = type => type === 'manual' ? 'دستی' : type === 'automatic' ? 'خودکار' : 'نامشخص';

            function cell(content, className = '') {
                const td = document.createElement('td');
                td.textContent = value(content);
                if (className) td.className = className;
                return td;
            }

            function renderRows(items) {
                const fragment = document.createDocumentFragment();
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.append(cell('#' + value(item.id), 'admin-products-id'));

                    const nameTd = document.createElement('td');
                    const nameWrap = document.createElement('div');
                    nameWrap.className = 'admin-products-name-cell';
                    const mark = document.createElement('span');
                    mark.className = 'admin-products-product-mark';
                    mark.setAttribute('aria-hidden', 'true');
                    mark.textContent = Array.from(value(item.name))[0] || 'م';
                    const copy = document.createElement('span');
                    copy.className = 'admin-products-product-copy';
                    const name = document.createElement('strong');
                    name.textContent = value(item.name);
                    const description = document.createElement('small');
                    description.textContent = value(item.description);
                    copy.append(name, description);
                    nameWrap.append(mark, copy);
                    nameTd.append(nameWrap);
                    tr.append(nameTd);

                    const slugTd = document.createElement('td');
                    const slug = document.createElement('code');
                    slug.className = 'admin-products-sku';
                    slug.dir = 'ltr';
                    slug.textContent = value(item.slug);
                    slugTd.append(slug);
                    tr.append(slugTd, cell(typeText(item.collectionType)));

                    const statusTd = document.createElement('td');
                    const badge = document.createElement('span');
                    const knownStatus = item.status === 'active' || item.status === 'inactive';
                    badge.className = 'admin-products-status admin-products-status-' + (knownStatus ? item.status : 'unknown');
                    badge.textContent = statusText(item.status);
                    statusTd.append(badge);
                    tr.append(statusTd, cell(item.sortOrder, 'admin-products-id'));

                    const dateRange = [item.startsAt, item.endsAt].filter(Boolean);
                    tr.append(cell(dateRange.length ? dateRange.join(' تا ') : 'بدون محدودیت زمانی'));

                    const actionTd = document.createElement('td');
                    const edit = document.createElement('a');
                    edit.className = 'admin-products-edit-action';
                    edit.href = editUrl + encodeURIComponent(String(item.id));
                    edit.textContent = 'ویرایش ←';
                    edit.setAttribute('aria-label', 'ویرایش مجموعه ' + value(item.name));
                    actionTd.append(edit);
                    tr.append(actionTd);
                    fragment.append(tr);
                });
                el.rows.replaceChildren(fragment);
            }

            function render() {
                const query = el.search.value.trim().toLocaleLowerCase('fa');
                const selectedStatus = el.status.value;
                const filtered = collections.filter(item => {
                    if (selectedStatus && item.status !== selectedStatus) return false;
                    if (!query) return true;
                    const haystack = [item.id, item.name, item.slug, item.collectionType, item.description]
                        .map(part => value(part).toLocaleLowerCase('fa')).join(' ');
                    return haystack.includes(query);
                });

                el.total.textContent = String(collections.length);
                el.active.textContent = String(collections.filter(item => item.status === 'active').length);
                el.inactive.textContent = String(collections.filter(item => item.status === 'inactive').length);
                el.count.textContent = filtered.length + ' مورد از ' + collections.length + ' مجموعه';

                const hasRows = filtered.length > 0;
                el.table.hidden = !hasRows;
                el.empty.hidden = hasRows || isLoading;
                if (!hasRows && !isLoading) {
                    const hasFilters = Boolean(query || selectedStatus);
                    el.emptyTitle.textContent = hasFilters ? 'مجموعه‌ای با این فیلترها پیدا نشد' : 'هنوز مجموعه‌ای ثبت نشده است';
                    el.emptyDescription.textContent = hasFilters
                        ? 'عبارت جست‌وجو یا وضعیت انتخاب‌شده را تغییر بده یا فیلترها را پاک کن.'
                        : 'پس از ثبت مجموعه‌های فروشگاه، اطلاعات آن‌ها در این فهرست نمایش داده می‌شود.';
                }
                renderRows(filtered);
            }

            function showFeedback(message, state = 'info', visible = true) {
                el.feedback.hidden = !visible;
                el.feedback.dataset.state = state;
                el.feedback.replaceChildren();
                if (state === 'loading') {
                    const spinner = document.createElement('span');
                    spinner.className = 'admin-collections-loading-indicator';
                    spinner.setAttribute('aria-hidden', 'true');
                    el.feedback.append(spinner);
                }
                const label = document.createElement('span');
                label.textContent = message;
                el.feedback.append(label);
                if (state === 'error') {
                    const retry = document.createElement('button');
                    retry.type = 'button';
                    retry.className = 'admin-products-clear-link';
                    retry.textContent = 'تلاش دوباره';
                    retry.addEventListener('click', loadCollections);
                    el.feedback.append(retry);
                }
            }

            async function loadCollections() {
                if (isLoading) return;
                isLoading = true;
                el.refresh.disabled = true;
                el.search.disabled = true;
                el.status.disabled = true;
                el.clear.disabled = true;
                el.table.hidden = true;
                el.empty.hidden = true;
                showFeedback('در حال دریافت فهرست مجموعه‌ها…', 'loading');

                try {
                    const response = await fetch(apiUrl, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const result = await response.json();
                    if (!result || result.success !== true || !Array.isArray(result.data?.items)) {
                        throw new Error('Invalid collection response');
                    }
                    collections = result.data.items;
                    el.search.disabled = false;
                    el.status.disabled = false;
                    el.clear.disabled = false;
                    showFeedback('', 'info', false);
                    render();
                } catch (error) {
                    collections = [];
                    el.total.textContent = '—';
                    el.active.textContent = '—';
                    el.inactive.textContent = '—';
                    el.count.textContent = 'دریافت اطلاعات ناموفق بود';
                    showFeedback('دریافت مجموعه‌ها با خطا مواجه شد. اتصال را بررسی و دوباره تلاش کن.', 'error');
                } finally {
                    isLoading = false;
                    el.refresh.disabled = false;
                }
            }

            el.search.addEventListener('input', render);
            el.status.addEventListener('change', render);
            el.clear.addEventListener('click', () => {
                el.search.value = '';
                el.status.value = '';
                render();
                el.search.focus();
            });
            el.refresh.addEventListener('click', loadCollections);
            loadCollections();
        })();
    </script>
</body>
</html>
