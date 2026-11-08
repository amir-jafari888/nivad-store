<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new BrandService();
$allBrands = $service->findAll();

$searchValue = Request::query('q', '');
$statusValue = Request::query('status', '');
$search = is_string($searchValue) ? trim($searchValue) : '';
$statusFilter = is_string($statusValue) ? trim($statusValue) : '';
$allowedStatuses = ['active', 'inactive'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$brands = array_values(array_filter(
    $allBrands,
    static function ($brand) use ($search, $statusFilter): bool {
        $brandStatus = (string)($brand->status ?? '');
        if ($statusFilter !== '' && $brandStatus !== $statusFilter) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $haystack = implode(' ', [
            (string)($brand->name ?? ''),
            (string)($brand->slug ?? ''),
            (string)($brand->id ?? ''),
        ]);

        return function_exists('mb_stripos')
            ? mb_stripos($haystack, $search, 0, 'UTF-8') !== false
            : stripos($haystack, $search) !== false;
    }
));

$statusLabels = [
    'active' => 'فعال',
    'inactive' => 'غیرفعال',
];

$statusCounts = array_fill_keys($allowedStatuses, 0);
foreach ($allBrands as $brand) {
    $brandStatus = (string)($brand->status ?? '');
    if (array_key_exists($brandStatus, $statusCounts)) {
        $statusCounts[$brandStatus]++;
    }
}

$totalBrands = count($allBrands);
$visibleBrands = count($brands);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت برندها | نیواد</title>
</head>

<body>
    <main class="admin-products-page admin-brands-page">
        <div class="admin-products-content">
            <header class="admin-products-heading">
                <div class="admin-products-heading-copy">
                    <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>مدیریت برندها</h1>
                    <p>برندهای فروشگاه را جست‌وجو، بررسی و مدیریت کن.</p>
                </div>
                <a class="admin-products-primary-action" href="<?= e(url('/admin/brands/create')) ?>">
                    <span aria-hidden="true">＋</span>
                    افزودن برند
                </a>
            </header>

            <section class="admin-products-summary admin-brands-summary" aria-label="خلاصه برندها">
                <article class="admin-products-stat admin-products-stat-total">
                    <span class="admin-products-stat-label">کل برندها</span>
                    <strong><?= e((string)$totalBrands) ?></strong>
                    <span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span>
                </article>
                <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                    <article class="admin-products-stat">
                        <span class="admin-products-stat-label"><?= e($statusLabel) ?></span>
                        <strong><?= e((string)$statusCounts[$statusKey]) ?></strong>
                        <span class="admin-products-stat-note">برند</span>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="admin-products-panel" aria-labelledby="admin-brands-list-title">
                <div class="admin-products-panel-heading">
                    <div>
                        <h2 id="admin-brands-list-title">فهرست برندها</h2>
                        <p><?= e((string)$visibleBrands) ?> مورد از <?= e((string)$totalBrands) ?> برند</p>
                    </div>
                </div>

                <form class="admin-products-filters" method="get" action="<?= e(url('/admin/brands')) ?>">
                    <label class="admin-products-search">
                        <span>جست‌وجو</span>
                        <input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، شناسه یا Slug برند…" autocomplete="off">
                    </label>
                    <label class="admin-products-status-filter">
                        <span>وضعیت برند</span>
                        <select name="status">
                            <option value="">همه وضعیت‌ها</option>
                            <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                                <option value="<?= e($statusKey) ?>" <?= $statusFilter === $statusKey ? 'selected' : '' ?>><?= e($statusLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="admin-products-filter-actions">
                        <button type="submit">اعمال فیلتر</button>
                        <?php if ($search !== '' || $statusFilter !== ''): ?>
                            <a href="<?= e(url('/admin/brands')) ?>">پاک‌کردن</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($brands === []): ?>
                    <div class="admin-products-empty">
                        <span class="admin-products-empty-icon" aria-hidden="true">▤</span>
                        <h3><?= $totalBrands === 0 ? 'هنوز برندی ثبت نشده است' : 'برندی با این فیلترها پیدا نشد' ?></h3>
                        <p><?= $totalBrands === 0 ? 'اولین برند فروشگاه را ایجاد کن تا در این فهرست نمایش داده شود.' : 'عبارت جست‌وجو یا وضعیت انتخاب‌شده را تغییر بده.' ?></p>
                        <?php if ($totalBrands === 0): ?>
                            <a class="admin-products-primary-action" href="<?= e(url('/admin/brands/create')) ?>">افزودن اولین برند</a>
                        <?php else: ?>
                            <a class="admin-products-clear-link" href="<?= e(url('/admin/brands')) ?>">نمایش همه برندها</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-products-table-wrap">
                        <table class="admin-products-table">
                            <thead>
                                <tr>
                                    <th scope="col">شناسه</th>
                                    <th scope="col">برند</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">ترتیب نمایش</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($brands as $brand): ?>
                                    <?php
                                    $brandStatus = (string)($brand->status ?? '');
                                    $statusLabel = $statusLabels[$brandStatus] ?? $brandStatus;
                                    $statusClass = in_array($brandStatus, $allowedStatuses, true) ? $brandStatus : 'unknown';
                                    ?>
                                    <tr>
                                        <td class="admin-products-id">#<?= e((string)$brand->id) ?></td>
                                        <td>
                                            <div class="admin-products-name-cell">
                                                <span class="admin-products-product-mark" aria-hidden="true"><?= e(mb_substr((string)$brand->name, 0, 1, 'UTF-8')) ?></span>
                                                <span class="admin-products-product-copy">
                                                    <strong><?= e((string)$brand->name) ?></strong>
                                                    <small>برند فروشگاه</small>
                                                </span>
                                            </div>
                                        </td>
                                        <td><code class="admin-products-sku"><?= e((string)$brand->slug) ?></code></td>
                                        <td><span class="admin-products-status admin-products-status-<?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                        <td class="admin-products-id"><?= e((string)$brand->sortOrder) ?></td>
                                        <td>
                                            <a class="admin-products-edit-action" href="<?= e(url('/admin/brands/edit?id=' . (int)$brand->id)) ?>">ویرایش <span aria-hidden="true">←</span></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>

</html>
