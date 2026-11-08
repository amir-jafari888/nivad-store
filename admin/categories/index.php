<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new CategoryService();
$allCategories = $service->findAll();

$categoryNamesById = [];
foreach ($allCategories as $category) {
    $categoryNamesById[$category->id] = $category->name;
}

$searchValue = Request::query('q', '');
$statusValue = Request::query('status', '');
$search = is_string($searchValue) ? trim($searchValue) : '';
$statusFilter = is_string($statusValue) ? trim($statusValue) : '';
$allowedStatuses = ['active', 'inactive'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$categories = array_values(array_filter(
    $allCategories,
    static function ($category) use ($search, $statusFilter, $categoryNamesById): bool {
        $categoryStatus = (string)($category->status ?? '');
        if ($statusFilter !== '' && $categoryStatus !== $statusFilter) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $parentName = $category->parentId !== null
            ? (string)($categoryNamesById[$category->parentId] ?? '')
            : '';

        $haystack = implode(' ', [
            (string)($category->name ?? ''),
            (string)($category->slug ?? ''),
            (string)($category->id ?? ''),
            (string)($category->parentId ?? ''),
            $parentName,
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
foreach ($allCategories as $category) {
    $categoryStatus = (string)($category->status ?? '');
    if (array_key_exists($categoryStatus, $statusCounts)) {
        $statusCounts[$categoryStatus]++;
    }
}

$totalCategories = count($allCategories);
$visibleCategories = count($categories);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت دسته‌بندی‌ها | نیواد</title>
</head>

<body>
    <main class="admin-products-page admin-categories-page">
        <div class="admin-products-content">
            <header class="admin-products-heading">
                <div class="admin-products-heading-copy">
                    <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>مدیریت دسته‌بندی‌ها</h1>
                    <p>ساختار دسته‌بندی فروشگاه را جست‌وجو، بررسی و مدیریت کن.</p>
                </div>
                <a class="admin-products-primary-action" href="<?= e(url('/admin/categories/create')) ?>">
                    <span aria-hidden="true">＋</span>
                    افزودن دسته‌بندی
                </a>
            </header>

            <section class="admin-products-summary admin-categories-summary" aria-label="خلاصه دسته‌بندی‌ها">
                <article class="admin-products-stat admin-products-stat-total">
                    <span class="admin-products-stat-label">کل دسته‌بندی‌ها</span>
                    <strong><?= e((string)$totalCategories) ?></strong>
                    <span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span>
                </article>
                <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                    <article class="admin-products-stat">
                        <span class="admin-products-stat-label"><?= e($statusLabel) ?></span>
                        <strong><?= e((string)$statusCounts[$statusKey]) ?></strong>
                        <span class="admin-products-stat-note">دسته‌بندی</span>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="admin-products-panel" aria-labelledby="admin-categories-list-title">
                <div class="admin-products-panel-heading">
                    <div>
                        <h2 id="admin-categories-list-title">فهرست دسته‌بندی‌ها</h2>
                        <p><?= e((string)$visibleCategories) ?> مورد از <?= e((string)$totalCategories) ?> دسته‌بندی</p>
                    </div>
                </div>

                <form class="admin-products-filters" method="get" action="<?= e(url('/admin/categories')) ?>">
                    <label class="admin-products-search">
                        <span>جست‌وجو</span>
                        <input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، شناسه، والد یا Slug دسته‌بندی…" autocomplete="off">
                    </label>
                    <label class="admin-products-status-filter">
                        <span>وضعیت دسته‌بندی</span>
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
                            <a href="<?= e(url('/admin/categories')) ?>">پاک‌کردن</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($categories === []): ?>
                    <div class="admin-products-empty">
                        <span class="admin-products-empty-icon" aria-hidden="true">▤</span>
                        <h3><?= $totalCategories === 0 ? 'هنوز دسته‌بندی‌ای ثبت نشده است' : 'دسته‌بندی‌ای با این فیلترها پیدا نشد' ?></h3>
                        <p><?= $totalCategories === 0 ? 'اولین دسته‌بندی فروشگاه را ایجاد کن تا ساختار کاتالوگ شکل بگیرد.' : 'عبارت جست‌وجو یا وضعیت انتخاب‌شده را تغییر بده.' ?></p>
                        <?php if ($totalCategories === 0): ?>
                            <a class="admin-products-primary-action" href="<?= e(url('/admin/categories/create')) ?>">افزودن اولین دسته‌بندی</a>
                        <?php else: ?>
                            <a class="admin-products-clear-link" href="<?= e(url('/admin/categories')) ?>">نمایش همه دسته‌بندی‌ها</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-products-table-wrap">
                        <table class="admin-products-table" data-admin-bulk-job="catalog.categories.status" data-admin-bulk-permission="admin.products.manage">
                            <thead>
                                <tr>
                                    <th scope="col">شناسه</th>
                                    <th scope="col">دسته‌بندی</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">دسته والد</th>
                                    <th scope="col">ترتیب نمایش</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    $categoryStatus = (string)($category->status ?? '');
                                    $statusLabel = $statusLabels[$categoryStatus] ?? $categoryStatus;
                                    $statusClass = in_array($categoryStatus, $allowedStatuses, true) ? $categoryStatus : 'unknown';
                                    $parentLabel = $category->parentId === null
                                        ? 'دسته اصلی'
                                        : (string)($categoryNamesById[$category->parentId] ?? ('شناسه #' . $category->parentId));
                                    ?>
                                    <tr>
                                        <td class="admin-products-id">#<?= e((string)$category->id) ?></td>
                                        <td>
                                            <div class="admin-products-name-cell">
                                                <span class="admin-products-product-mark" aria-hidden="true"><?= e(mb_substr((string)$category->name, 0, 1, 'UTF-8')) ?></span>
                                                <span class="admin-products-product-copy">
                                                    <strong><?= e((string)$category->name) ?></strong>
                                                    <small><?= $category->parentId === null ? 'دسته اصلی' : 'زیرمجموعه' ?></small>
                                                </span>
                                            </div>
                                        </td>
                                        <td><code class="admin-products-sku"><?= e((string)$category->slug) ?></code></td>
                                        <td><span class="admin-products-status admin-products-status-<?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                        <td><?= e($parentLabel) ?></td>
                                        <td class="admin-products-id"><?= e((string)$category->sortOrder) ?></td>
                                        <td>
                                            <a class="admin-products-edit-action" href="<?= e(url('/admin/categories/edit?id=' . (int)$category->id)) ?>">ویرایش <span aria-hidden="true">←</span></a>
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
