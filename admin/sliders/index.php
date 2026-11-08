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
    <title>اسلایدرها | نیواد</title>
</head>
<body>
<main id="sliders-main" class="admin-products-page admin-sliders-page">
    <div class="admin-products-content">
        <header class="admin-products-heading">
            <div class="admin-products-heading-copy">
                <p class="admin-products-eyebrow">ویترین و دیسکاوری</p>
                <h1>اسلایدرها</h1>
                <p>مدیریت اسلایدرهای فروشگاه، وضعیت انتشار و بازه نمایش هر اسلایدر.</p>
            </div>
        </header>
        <section class="admin-products-summary admin-sliders-summary" aria-label="خلاصه اسلایدرها">
            <button class="admin-products-stat admin-sliders-stat is-selected" type="button" data-slider-filter="all" aria-pressed="true">
                <span class="admin-products-stat-label">همه اسلایدرها</span><strong data-slider-count="all">—</strong><span class="admin-products-stat-note">فهرست قابل مدیریت</span>
            </button>
            <button class="admin-products-stat admin-sliders-stat" type="button" data-slider-filter="active" aria-pressed="false">
                <span class="admin-products-stat-label">فعال</span><strong data-slider-count="active">—</strong><span class="admin-products-stat-note">وضعیت فعال در سیستم</span>
            </button>
            <button class="admin-products-stat admin-sliders-stat" type="button" data-slider-filter="inactive" aria-pressed="false">
                <span class="admin-products-stat-label">غیرفعال</span><strong data-slider-count="inactive">—</strong><span class="admin-products-stat-note">در حال حاضر غیرفعال</span>
            </button>
        </section>
        <section class="admin-products-panel admin-sliders-list-panel" aria-labelledby="sliders-list-title">
            <div class="admin-products-panel-heading admin-sliders-panel-heading">
                <div><h2 id="sliders-list-title">فهرست اسلایدرها</h2><p id="sliders-result-count" aria-live="polite">در حال دریافت اطلاعات…</p></div>
                <button type="button" class="admin-sliders-refresh" id="sliders-refresh">تازه‌سازی فهرست</button>
            </div>
            <div class="admin-products-filters admin-sliders-filters">
                <label class="admin-products-search" for="sliders-search"><span>جست‌وجو</span><input id="sliders-search" type="search" placeholder="نام، توضیحات یا Slug اسلایدر…" autocomplete="off"></label>
                <label class="admin-products-filter" for="sliders-status"><span>وضعیت</span><select id="sliders-status"><option value="">همه وضعیت‌ها</option><option value="active">فعال</option><option value="inactive">غیرفعال</option></select></label>
                <button type="button" class="admin-sliders-reset" id="sliders-reset">پاک‌کردن فیلترها</button>
            </div>
            <div class="admin-products-table-wrap">
                <table class="admin-products-table admin-sliders-table">
                    <thead><tr><th scope="col">اسلایدر</th><th scope="col">شناسه (Slug)</th><th scope="col">وضعیت</th><th scope="col">بازه نمایش</th><th scope="col">ترتیب</th><th scope="col">عملیات</th></tr></thead>
                    <tbody id="sliders-rows" aria-live="polite" aria-busy="true"><tr><td colspan="6" class="admin-sliders-feedback">در حال دریافت فهرست اسلایدرها…</td></tr></tbody>
                </table>
            </div>
            <div id="sliders-feedback" class="admin-sliders-feedback-panel" role="status" hidden></div>
        </section>
    </div>
</main>
<script>
(() => {
    const endpoint = <?= json_encode(url('/api/admin/sliders'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const editBase = <?= json_encode(url('/admin/sliders/edit?id='), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const rows = document.getElementById('sliders-rows');
    const search = document.getElementById('sliders-search');
    const status = document.getElementById('sliders-status');
    const countLabel = document.getElementById('sliders-result-count');
    const feedback = document.getElementById('sliders-feedback');
    const refreshButton = document.getElementById('sliders-refresh');
    const resetButton = document.getElementById('sliders-reset');
    const countElements = [...document.querySelectorAll('[data-slider-count]')];
    const filterButtons = [...document.querySelectorAll('[data-slider-filter]')];
    let sliders = [];
    let selectedStatus = 'all';
    let isLoading = false;

    const make = (tag, className, value) => {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (value !== undefined && value !== null) element.textContent = String(value);
        return element;
    };
    const formatDate = value => {
        if (!value) return 'بدون محدودیت';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return 'تاریخ نامشخص';
        return new Intl.DateTimeFormat('fa-IR', { dateStyle: 'medium' }).format(date);
    };
    const updateCounts = () => {
        const totals = {
            all: sliders.length,
            active: sliders.filter(item => item.status === 'active').length,
            inactive: sliders.filter(item => item.status === 'inactive').length
        };
        countElements.forEach(element => {
            element.textContent = String(totals[element.dataset.sliderCount] ?? 0);
        });
    };
    const showFeedback = (message, kind = 'info', canRetry = false) => {
        feedback.replaceChildren();
        feedback.hidden = false;
        feedback.dataset.kind = kind;
        feedback.append(make('p', '', message));
        if (canRetry) {
            const retry = make('button', 'admin-sliders-retry', 'تلاش دوباره');
            retry.type = 'button';
            retry.addEventListener('click', loadSliders);
            feedback.append(retry);
        }
    };
    const renderRows = () => {
        const query = search.value.trim().toLocaleLowerCase('fa');
        const activeStatus = status.value || selectedStatus;
        const filtered = sliders.filter(item => {
            const haystack = [item.name, item.slug, item.description, item.id]
                .filter(value => value !== null && value !== undefined).join(' ').toLocaleLowerCase('fa');
            return (query === '' || haystack.includes(query))
                && (activeStatus === 'all' || item.status === activeStatus);
        });
        rows.replaceChildren();
        rows.setAttribute('aria-busy', 'false');
        countLabel.textContent = filtered.length.toLocaleString('fa-IR') + ' مورد از ' + sliders.length.toLocaleString('fa-IR') + ' اسلایدر';
        if (filtered.length === 0) {
            const row = make('tr');
            const cell = make('td', 'admin-sliders-feedback', sliders.length === 0
                ? 'هنوز اسلایدری ثبت نشده است.'
                : 'اسلایدری با این جست‌وجو یا فیلتر پیدا نشد.');
            cell.colSpan = 6;
            row.append(cell);
            rows.append(row);
            return;
        }
        filtered.forEach(item => {
            const row = make('tr');
            const identity = make('td', 'admin-sliders-identity');
            identity.append(make('strong', '', item.name || 'بدون نام'));
            identity.append(make('small', '', 'شناسه #' + item.id));
            if (item.description) identity.append(make('span', 'admin-sliders-description', item.description));
            const slug = make('td', 'admin-sliders-slug');
            slug.append(make('code', '', item.slug || '—'));
            const statusCell = make('td');
            statusCell.append(make('span',
                'admin-products-status ' + (item.status === 'active' ? 'admin-products-status-active' : 'admin-products-status-inactive'),
                item.status === 'active' ? 'فعال' : 'غیرفعال'));
            const schedule = make('td', 'admin-sliders-schedule');
            schedule.append(make('span', '', formatDate(item.startsAt)));
            schedule.append(make('small', '', 'تا ' + formatDate(item.endsAt)));
            const order = make('td', 'admin-sliders-order',
                Number.isFinite(Number(item.sortOrder)) ? item.sortOrder : '—');
            const actions = make('td');
            const edit = make('a', 'admin-products-edit-action', 'ویرایش');
            edit.href = editBase + encodeURIComponent(String(item.id));
            edit.setAttribute('aria-label', 'ویرایش اسلایدر ' + (item.name || item.id));
            actions.append(edit);
            row.append(identity, slug, statusCell, schedule, order, actions);
            rows.append(row);
        });
    };
    async function loadSliders() {
        if (isLoading) return;
        isLoading = true;
        refreshButton.disabled = true;
        refreshButton.textContent = 'در حال دریافت…';
        feedback.hidden = true;
        rows.setAttribute('aria-busy', 'true');
        rows.replaceChildren();
        const loadingRow = make('tr');
        const loadingCell = make('td', 'admin-sliders-feedback', 'در حال دریافت فهرست اسلایدرها…');
        loadingCell.colSpan = 6;
        loadingRow.append(loadingCell);
        rows.append(loadingRow);
        try {
            const response = await fetch(endpoint, {
                method: 'GET', credentials: 'same-origin',
                headers: { Accept: 'application/json' }, cache: 'no-store'
            });
            let result;
            try { result = await response.json(); }
            catch { throw new Error('پاسخ سرور قابل خواندن نیست. لطفاً دوباره تلاش کنید.'); }
            if (!response.ok) {
                throw new Error(result.message || ('دریافت اسلایدرها ناموفق بود (' + response.status + ').'));
            }
            const items = result.data?.items;
            if (!Array.isArray(items)) {
                throw new Error('ساختار اطلاعات دریافتی با قرارداد API اسلایدرها مطابقت ندارد.');
            }
            sliders = items;
            updateCounts();
            renderRows();
        } catch (error) {
            sliders = [];
            updateCounts();
            rows.replaceChildren();
            rows.setAttribute('aria-busy', 'false');
            countLabel.textContent = 'دریافت اطلاعات ناموفق بود';
            showFeedback(error instanceof Error ? error.message : 'خطای پیش‌بینی‌نشده در دریافت اسلایدرها.', 'error', true);
        } finally {
            isLoading = false;
            refreshButton.disabled = false;
            refreshButton.textContent = 'تازه‌سازی فهرست';
        }
    }
    search.addEventListener('input', renderRows);
    status.addEventListener('change', () => {
        selectedStatus = 'all';
        filterButtons.forEach(button => {
            const selected = button.dataset.sliderFilter === 'all';
            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
        renderRows();
    });
    filterButtons.forEach(button => button.addEventListener('click', () => {
        selectedStatus = button.dataset.sliderFilter;
        status.value = '';
        filterButtons.forEach(candidate => {
            const selected = candidate === button;
            candidate.classList.toggle('is-selected', selected);
            candidate.setAttribute('aria-pressed', String(selected));
        });
        renderRows();
    }));
    resetButton.addEventListener('click', () => {
        search.value = '';
        status.value = '';
        selectedStatus = 'all';
        filterButtons.forEach(button => {
            const selected = button.dataset.sliderFilter === 'all';
            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
        renderRows();
        search.focus();
    });
    refreshButton.addEventListener('click', loadSliders);
    loadSliders();
})();
</script>
</body>
</html>
