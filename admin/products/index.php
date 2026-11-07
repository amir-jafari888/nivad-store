<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$productService = new ProductService();
$allProducts = $productService->adminFindAll();

$search = trim((string)Request::query('q', ''));
$statusFilter = trim((string)Request::query('status', ''));
$allowedStatuses = ['active', 'draft', 'out_of_stock', 'discontinued', 'archived'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$products = array_values(array_filter(
    $allProducts,
    static function (array $product) use ($search, $statusFilter): bool {
        if ($statusFilter !== '' && (string)($product['status'] ?? '') !== $statusFilter) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $haystack = implode(' ', [
            (string)($product['name'] ?? ''),
            (string)($product['sku'] ?? ''),
            (string)($product['slug'] ?? ''),
            (string)($product['id'] ?? ''),
        ]);

        return function_exists('mb_stripos')
            ? mb_stripos($haystack, $search, 0, 'UTF-8') !== false
            : stripos($haystack, $search) !== false;
    }
));

$statusLabels = [
    'active' => 'فعال',
    'draft' => 'پیش‌نویس',
    'out_of_stock' => 'ناموجود',
    'discontinued' => 'متوقف',
    'archived' => 'آرشیو',
];

$statusCounts = array_fill_keys($allowedStatuses, 0);
foreach ($allProducts as $product) {
    $productStatus = (string)($product['status'] ?? '');
    if (array_key_exists($productStatus, $statusCounts)) {
        $statusCounts[$productStatus]++;
    }
}

$totalProducts = count($allProducts);
$visibleProducts = count($products);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت محصولات | نیواد</title>
</head>

<body>
    <main class="admin-products-page">
        <div class="admin-products-content">
            <header class="admin-products-heading">
                <div class="admin-products-heading-copy">
                    <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>مدیریت محصولات</h1>
                    <p>محصولات فروشگاه را جست‌وجو، بررسی و مدیریت کن.</p>
                </div>
                <a class="admin-products-primary-action" href="<?= e(url('/admin/products/create')) ?>">
                    <span aria-hidden="true">＋</span>
                    افزودن محصول
                </a>
            </header>

            <section class="admin-products-summary" aria-label="خلاصه محصولات">
                <article class="admin-products-stat admin-products-stat-total">
                    <span class="admin-products-stat-label">کل محصولات</span>
                    <strong><?= e((string)$totalProducts) ?></strong>
                    <span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span>
                </article>
                <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                    <article class="admin-products-stat">
                        <span class="admin-products-stat-label"><?= e($statusLabel) ?></span>
                        <strong><?= e((string)$statusCounts[$statusKey]) ?></strong>
                        <span class="admin-products-stat-note">محصول</span>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="admin-products-panel" aria-labelledby="admin-products-list-title">
                <div class="admin-products-panel-heading">
                    <div>
                        <h2 id="admin-products-list-title">فهرست محصولات</h2>
                        <p><?= e((string)$visibleProducts) ?> مورد از <?= e((string)$totalProducts) ?> محصول</p>
                    </div>
                </div>

                <form class="admin-products-filters" method="get" action="<?= e(url('/admin/products')) ?>">
                    <label class="admin-products-search">
                        <span>جست‌وجو</span>
                        <input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، SKU، شناسه یا Slug محصول…" autocomplete="off">
                    </label>
                    <label class="admin-products-status-filter">
                        <span>وضعیت محصول</span>
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
                            <a href="<?= e(url('/admin/products')) ?>">پاک‌کردن</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($products === []): ?>
                    <div class="admin-products-empty">
                        <span class="admin-products-empty-icon" aria-hidden="true">▤</span>
                        <h3><?= $totalProducts === 0 ? 'هنوز محصولی ثبت نشده است' : 'محصولی با این فیلترها پیدا نشد' ?></h3>
                        <p><?= $totalProducts === 0 ? 'اولین محصول فروشگاه را ایجاد کن تا در این فهرست نمایش داده شود.' : 'عبارت جست‌وجو یا وضعیت انتخاب‌شده را تغییر بده.' ?></p>
                        <?php if ($totalProducts === 0): ?>
                            <a class="admin-products-primary-action" href="<?= e(url('/admin/products/create')) ?>">افزودن اولین محصول</a>
                        <?php else: ?>
                            <a class="admin-products-clear-link" href="<?= e(url('/admin/products')) ?>">نمایش همه محصولات</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-products-table-wrap">
                        <table class="admin-products-table" data-admin-bulk-job="catalog.products.status" data-admin-bulk-permission="admin.products.manage">
                            <thead>
                                <tr>
                                    <th scope="col">شناسه</th>
                                    <th scope="col">محصول</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">نوع</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $productStatus = (string)($product['status'] ?? '');
                                    $statusLabel = $statusLabels[$productStatus] ?? $productStatus;
                                    $statusClass = in_array($productStatus, $allowedStatuses, true) ? $productStatus : 'unknown';
                                    ?>
                                    <tr>
                                        <td class="admin-products-id">#<?= e((string)$product['id']) ?></td>
                                        <td>
                                            <div class="admin-products-name-cell">
                                                <span class="admin-products-product-mark" aria-hidden="true"><?= e(mb_substr((string)$product['name'], 0, 1, 'UTF-8')) ?></span>
                                                <span class="admin-products-product-copy">
                                                    <strong><?= e((string)$product['name']) ?></strong>
                                                    <small><?= e((string)$product['slug']) ?></small>
                                                </span>
                                                <?php if ((bool)($product['is_featured'] ?? false)): ?>
                                                    <span class="admin-products-featured">ویژه</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><code class="admin-products-sku"><?= e((string)$product['sku']) ?></code></td>
                                        <td><span class="admin-products-status admin-products-status-<?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                        <td><?= e((string)($product['product_type_id'] ?? '—')) ?></td>
                                        <td>
                                            <a class="admin-products-edit-action" href="<?= e(url('/admin/products/edit?id=' . (int)$product['id'])) ?>">ویرایش <span aria-hidden="true">←</span></a>
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
