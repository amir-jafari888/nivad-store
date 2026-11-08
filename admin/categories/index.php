<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new CategoryService();
$allCategories = $service->findAll();

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
    static function (Category $category) use ($search, $statusFilter): bool {
        if ($statusFilter !== '' && $category->status !== $statusFilter) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $haystack = implode(' ', [
            (string)$category->name,
            (string)$category->slug,
            (string)$category->id,
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
    if (array_key_exists($category->status, $statusCounts)) {
        $statusCounts[$category->status]++;
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
    <main class="admin-categories-page">
        <div class="admin-categories-content">
            <header class="admin-categories-heading">
                <div class="admin-categories-heading-copy">
                    <p class="admin-categories-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>مدیریت دسته‌بندی‌ها</h1>
                    <p>دسته‌بندی‌های فروشگاه را جست‌وجو، بررسی و مدیریت کن.</p>
                </div>
                <a class="admin-categories-primary-action" href="<?= e(url('/admin/categories/create')) ?>">
                    <span aria-hidden="true">＋</span>
                    افزودن دسته‌بندی
                </a>
            </header>

            <section class="admin-categories-summary" aria-label="خلاصه دسته‌بندی‌ها">
                <article class="admin-categories-stat admin-categories-stat-total">
                    <span class="admin-categories-stat-label">کل دسته‌بندی‌ها</span>
                    <strong><?= e((string)$totalCategories) ?></strong>
                    <span class="admin-categories-stat-note">ثبت‌شده در کاتالوگ</span>
                </article>
                <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                    <article class="admin-categories-stat">
                        <span class="admin-categories-stat-label"><?= e($statusLabel) ?></span>
                        <strong><?= e((string)$statusCounts[$statusKey]) ?></strong>
                        <span class="admin-categories-stat-note">دسته‌بندی</span>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="admin-categories-panel" aria-labelledby="admin-categories-list-title">
                <div class="admin-categories-panel-heading">
                    <div>
                        <h2 id="admin-categories-list-title">فهرست دسته‌بندی‌ها</h2>
                        <p><?= e((string)$visibleCategories) ?> مورد از <?= e((string)$totalCategories) ?> دسته‌بندی</p>
                    </div>
                </div>

                <form class="admin-categories-filters" method="get" action="<?= e(url('/admin/categories')) ?>">
                    <label class="admin-categories-search">
                        <span>جست‌وجو</span>
                        <input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، Slug یا شناسه دسته‌بندی…" autocomplete="off">
                    </label>
                    <label class="admin-categories-status-filter">
                        <span>وضعیت دسته‌بندی</span>
                        <select name="status">
                            <option value="">همه وضعیت‌ها</option>
                            <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                                <option value="<?= e($statusKey) ?>" <?= $statusFilter === $statusKey ? 'selected' : '' ?>><?= e($statusLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="admin-categories-filter-actions">
                        <button type="submit">اعمال فیلتر</button>
                        <?php if ($search !== '' || $statusFilter !== ''): ?>
                            <a href="<?= e(url('/admin/categories')) ?>">پاک‌کردن</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($categories === []): ?>
                    <div class="admin-categories-empty">
                        <span class="admin-categories-empty-icon" aria-hidden="true">▤</span>
                        <h3><?= $totalCategories === 0 ? 'هنوز دسته‌بندی‌ای ثبت نشده است' : 'دسته‌بندی‌ای با این فیلترها پیدا نشد' ?></h3>
                        <p><?= $totalCategories === 0 ? 'اولین دسته‌بندی فروشگاه را ایجاد کن تا در این فهرست نمایش داده شود.' : 'عبارت جست‌وجو یا وضعیت انتخاب‌شده را تغییر بده.' ?></p>
                        <?php if ($totalCategories === 0): ?>
                            <a class="admin-categories-primary-action" href="<?= e(url('/admin/categories/create')) ?>">افزودن اولین دسته‌بندی</a>
                        <?php else: ?>
                            <a class="admin-categories-clear-link" href="<?= e(url('/admin/categories')) ?>">نمایش همه دسته‌بندی‌ها</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-categories-table-wrap">
                        <table class="admin-categories-table" data-admin-bulk-job="catalog.categories.status" data-admin-bulk-permission="admin.products.manage">
                            <thead>
                                <tr>
                                    <th scope="col">شناسه</th>
                                    <th scope="col">دسته‌بندی</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">دسته‌بندی والد</th>
                                    <th scope="col">ترتیب نمایش</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    $categoryStatus = $category->status;
                                    $statusLabel = $statusLabels[$categoryStatus] ?? $categoryStatus;
                                    $statusClass = in_array($categoryStatus, $allowedStatuses, true) ? $categoryStatus : 'unknown';
                                    ?>
                                    <tr>
                                        <td class="admin-categories-id">#<?= e((string)$category->id) ?></td>
                                        <td>
                                            <div class="admin-categories-name-cell">
                                                <span class="admin-categories-category-mark" aria-hidden="true"><?= e(mb_substr($category->name, 0, 1, 'UTF-8')) ?></span>
                                                <span class="admin-categories-category-copy">
                                                    <strong><?= e($category->name) ?></strong>
                                                    <small>دسته‌بندی فروشگاه</small>
                                                </span>
                                            </div>
                                        </td>
                                        <td><code class="admin-categories-slug"><?= e($category->slug) ?></code></td>
                                        <td><span class="admin-categories-status admin-categories-status-<?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                        <td><?= $category->parentId === null ? 'بدون والد' : '#' . e((string)$category->parentId) ?></td>
                                        <td class="admin-categories-sort-order"><?= e((string)$category->sortOrder) ?></td>
                                        <td>
                                            <a class="admin-categories-edit-action" href="<?= e(url('/admin/categories/edit?id=' . $category->id)) ?>">ویرایش <span aria-hidden="true">←</span></a>
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
