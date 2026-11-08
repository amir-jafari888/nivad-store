<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new ProductTypeService();
$allProductTypes = $service->findAll();

$searchValue = Request::query('q', '');
$statusValue = Request::query('status', '');
$search = is_string($searchValue) ? trim($searchValue) : '';
$statusFilter = is_string($statusValue) ? trim($statusValue) : '';
$allowedStatuses = ['active', 'inactive'];
$statusLabels = ['active' => 'فعال', 'inactive' => 'غیرفعال'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$productTypes = array_values(array_filter(
    $allProductTypes,
    static function (ProductType $item) use ($search, $statusFilter): bool {
        if ($statusFilter !== '' && $item->status !== $statusFilter) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $haystack = implode(' ', [
            (string)$item->id,
            $item->name,
            $item->slug,
            (string)($item->description ?? ''),
        ]);

        return function_exists('mb_stripos')
            ? mb_stripos($haystack, $search, 0, 'UTF-8') !== false
            : stripos($haystack, $search) !== false;
    }
));

$statusCounts = array_fill_keys($allowedStatuses, 0);
foreach ($allProductTypes as $item) {
    if (array_key_exists($item->status, $statusCounts)) {
        $statusCounts[$item->status]++;
    }
}

$totalProductTypes = count($allProductTypes);
$visibleProductTypes = count($productTypes);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت انواع محصول | نیواد</title>
</head>
<body>
    <main class="admin-products-page admin-product-types-page">
        <div class="admin-products-content">
            <header class="admin-products-heading">
                <div class="admin-products-heading-copy">
                    <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>مدیریت انواع محصول</h1>
                    <p>ساختار نوع‌بندی محصولات را بررسی و مدیریت کنید؛ از نام و شناسه تا وضعیت و ترتیب نمایش.</p>
                </div>
                <a class="admin-products-primary-action" href="<?= e(url('/admin/product-types/create')) ?>">
                    <span aria-hidden="true">＋</span>
                    افزودن نوع محصول
                </a>
            </header>

            <section class="admin-products-summary admin-product-types-summary" aria-label="خلاصه انواع محصول">
                <article class="admin-products-stat admin-products-stat-total">
                    <span class="admin-products-stat-label">کل انواع محصول</span>
                    <strong><?= e((string)$totalProductTypes) ?></strong>
                    <span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span>
                </article>
                <article class="admin-products-stat">
                    <span class="admin-products-stat-label">فعال</span>
                    <strong><?= e((string)$statusCounts['active']) ?></strong>
                    <span class="admin-products-stat-note">قابل انتخاب برای محصولات</span>
                </article>
                <article class="admin-products-stat">
                    <span class="admin-products-stat-label">غیرفعال</span>
                    <strong><?= e((string)$statusCounts['inactive']) ?></strong>
                    <span class="admin-products-stat-note">خارج از چرخه استفاده</span>
                </article>
            </section>

            <section class="admin-products-panel" aria-labelledby="admin-product-types-list-title">
                <div class="admin-products-panel-heading">
                    <div>
                        <h2 id="admin-product-types-list-title">فهرست انواع محصول</h2>
                        <p><?= e((string)$visibleProductTypes) ?> مورد از <?= e((string)$totalProductTypes) ?> نوع محصول</p>
                    </div>
                </div>

                <form class="admin-products-filters admin-product-types-filters" method="get" action="<?= e(url('/admin/product-types')) ?>">
                    <label class="admin-products-search">
                        <span>جست‌وجو</span>
                        <input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، شناسه، توضیحات یا Slug نوع محصول…" autocomplete="off">
                    </label>
                    <label class="admin-products-status-filter">
                        <span>وضعیت</span>
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
                            <a href="<?= e(url('/admin/product-types')) ?>">پاک‌کردن فیلترها</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($productTypes === []): ?>
                    <div class="admin-products-empty">
                        <span class="admin-products-empty-icon" aria-hidden="true">▤</span>
                        <h3><?= $totalProductTypes === 0 ? 'هنوز نوع محصولی ثبت نشده است' : 'نوع محصولی با این فیلترها پیدا نشد' ?></h3>
                        <p><?= $totalProductTypes === 0 ? 'با تعریف نوع‌هایی مثل کالای فیزیکی یا محصول دیجیتال، ساختار کاتالوگ را منظم کنید.' : 'عبارت جست‌وجو یا وضعیت انتخاب‌شده را تغییر دهید.' ?></p>
                        <?php if ($totalProductTypes === 0): ?>
                            <a class="admin-products-primary-action" href="<?= e(url('/admin/product-types/create')) ?>">افزودن اولین نوع محصول</a>
                        <?php else: ?>
                            <a class="admin-products-clear-link" href="<?= e(url('/admin/product-types')) ?>">نمایش همه انواع محصول</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-products-table-wrap">
                        <table class="admin-products-table admin-product-types-table">
                            <thead>
                                <tr>
                                    <th scope="col">شناسه</th>
                                    <th scope="col">نوع محصول</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">توضیحات</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">ترتیب نمایش</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productTypes as $item): ?>
                                    <?php
                                    $itemStatus = (string)$item->status;
                                    $statusLabel = $statusLabels[$itemStatus] ?? $itemStatus;
                                    $statusClass = in_array($itemStatus, $allowedStatuses, true) ? $itemStatus : 'unknown';
                                    $initial = function_exists('mb_substr') ? mb_substr($item->name, 0, 1, 'UTF-8') : substr($item->name, 0, 1);
                                    ?>
                                    <tr>
                                        <td class="admin-products-id">#<?= e((string)$item->id) ?></td>
                                        <td>
                                            <div class="admin-products-name-cell admin-product-types-name-cell">
                                                <span class="admin-products-product-mark" aria-hidden="true"><?= e($initial) ?></span>
                                                <span class="admin-products-product-copy">
                                                    <strong><?= e($item->name) ?></strong>
                                                    <small>نوع محصول</small>
                                                </span>
                                            </div>
                                        </td>
                                        <td><code class="admin-products-sku" dir="ltr"><?= e($item->slug) ?></code></td>
                                        <td class="admin-product-types-description">
                                            <?= $item->description !== null && trim($item->description) !== '' ? e($item->description) : '<span class="admin-product-types-muted">—</span>' ?>
                                        </td>
                                        <td><span class="admin-products-status admin-products-status-<?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                        <td class="admin-products-id"><?= e((string)$item->sortOrder) ?></td>
                                        <td><a class="admin-products-edit-action" href="<?= e(url('/admin/product-types/edit?id=' . $item->id)) ?>">ویرایش ←</a></td>
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
