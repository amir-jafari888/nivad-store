<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new ProductAttributeService();
$allAttributes = $service->findAll();

$searchValue = Request::query('q', '');
$statusValue = Request::query('status', '');
$typeValue = Request::query('type', '');
$capabilityValue = Request::query('capability', '');
$search = is_string($searchValue) ? trim($searchValue) : '';
$statusFilter = is_string($statusValue) ? trim($statusValue) : '';
$typeFilter = is_string($typeValue) ? trim($typeValue) : '';
$capabilityFilter = is_string($capabilityValue) ? trim($capabilityValue) : '';

$allowedStatuses = ['active', 'inactive'];
$attributeTypes = [
    'select' => 'انتخابی',
    'text' => 'متنی',
    'number' => 'عددی',
    'boolean' => 'بله / خیر',
    'color' => 'رنگ',
];
$allowedCapabilities = ['variant', 'filterable', 'required'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}
if (!array_key_exists($typeFilter, $attributeTypes)) {
    $typeFilter = '';
}
if (!in_array($capabilityFilter, $allowedCapabilities, true)) {
    $capabilityFilter = '';
}

$attributes = array_values(array_filter(
    $allAttributes,
    static function (ProductAttribute $attribute) use ($search, $statusFilter, $typeFilter, $capabilityFilter): bool {
        if ($statusFilter !== '' && $attribute->status !== $statusFilter) {
            return false;
        }
        if ($typeFilter !== '' && $attribute->type !== $typeFilter) {
            return false;
        }
        if ($capabilityFilter === 'variant' && !$attribute->isVariant) {
            return false;
        }
        if ($capabilityFilter === 'filterable' && !$attribute->isFilterable) {
            return false;
        }
        if ($capabilityFilter === 'required' && !$attribute->isRequired) {
            return false;
        }
        if ($search === '') {
            return true;
        }

        $haystack = implode(' ', [
            (string)$attribute->id,
            $attribute->name,
            $attribute->slug,
            $attribute->type,
            $attributeTypes[$attribute->type] ?? '',
        ]);

        return function_exists('mb_stripos')
            ? mb_stripos($haystack, $search, 0, 'UTF-8') !== false
            : stripos($haystack, $search) !== false;
    }
));

$statusLabels = ['active' => 'فعال', 'inactive' => 'غیرفعال'];
$statusCounts = array_fill_keys($allowedStatuses, 0);
$filterableCount = 0;
foreach ($allAttributes as $attribute) {
    if (array_key_exists($attribute->status, $statusCounts)) {
        $statusCounts[$attribute->status]++;
    }
    if ($attribute->isFilterable) {
        $filterableCount++;
    }
}

$totalAttributes = count($allAttributes);
$visibleAttributes = count($attributes);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت ویژگی‌ها | نیواد</title>
</head>
<body>
    <main class="admin-products-page admin-attributes-page">
        <div class="admin-products-content">
            <header class="admin-products-heading">
                <div class="admin-products-heading-copy">
                    <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>مدیریت ویژگی‌ها</h1>
                    <p>ویژگی‌های قابل تعریف برای محصولات، تنوع‌ها و فیلترهای فروشگاه را مدیریت کنید.</p>
                </div>
                <a class="admin-products-primary-action" href="<?= e(url('/admin/attributes/create')) ?>">
                    <span aria-hidden="true">＋</span>
                    افزودن ویژگی
                </a>
            </header>

            <section class="admin-products-summary admin-attributes-summary" aria-label="خلاصه ویژگی‌ها">
                <article class="admin-products-stat admin-products-stat-total">
                    <span class="admin-products-stat-label">کل ویژگی‌ها</span>
                    <strong><?= e((string)$totalAttributes) ?></strong>
                    <span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span>
                </article>
                <article class="admin-products-stat">
                    <span class="admin-products-stat-label">فعال</span>
                    <strong><?= e((string)$statusCounts['active']) ?></strong>
                    <span class="admin-products-stat-note">قابل استفاده در محصولات</span>
                </article>
                <article class="admin-products-stat">
                    <span class="admin-products-stat-label">غیرفعال</span>
                    <strong><?= e((string)$statusCounts['inactive']) ?></strong>
                    <span class="admin-products-stat-note">خارج از چرخه استفاده</span>
                </article>
                <article class="admin-products-stat">
                    <span class="admin-products-stat-label">قابل فیلتر</span>
                    <strong><?= e((string)$filterableCount) ?></strong>
                    <span class="admin-products-stat-note">قابل استفاده در فیلتر فروشگاه</span>
                </article>
            </section>

            <section class="admin-products-panel" aria-labelledby="admin-attributes-list-title">
                <div class="admin-products-panel-heading">
                    <div>
                        <h2 id="admin-attributes-list-title">فهرست ویژگی‌ها</h2>
                        <p><?= e((string)$visibleAttributes) ?> مورد از <?= e((string)$totalAttributes) ?> ویژگی</p>
                    </div>
                </div>

                <form class="admin-products-filters admin-attributes-filters" method="get" action="<?= e(url('/admin/attributes')) ?>">
                    <label class="admin-products-search">
                        <span>جست‌وجو</span>
                        <input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، شناسه یا Slug ویژگی…" autocomplete="off">
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
                    <label class="admin-products-status-filter">
                        <span>نوع داده</span>
                        <select name="type">
                            <option value="">همه نوع‌ها</option>
                            <?php foreach ($attributeTypes as $typeKey => $typeLabel): ?>
                                <option value="<?= e($typeKey) ?>" <?= $typeFilter === $typeKey ? 'selected' : '' ?>><?= e($typeLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-products-status-filter">
                        <span>قابلیت</span>
                        <select name="capability">
                            <option value="">همه قابلیت‌ها</option>
                            <option value="variant" <?= $capabilityFilter === 'variant' ? 'selected' : '' ?>>ویژگی تنوع محصول</option>
                            <option value="filterable" <?= $capabilityFilter === 'filterable' ? 'selected' : '' ?>>قابل فیلتر</option>
                            <option value="required" <?= $capabilityFilter === 'required' ? 'selected' : '' ?>>اجباری</option>
                        </select>
                    </label>
                    <div class="admin-products-filter-actions">
                        <button type="submit">اعمال فیلتر</button>
                        <?php if ($search !== '' || $statusFilter !== '' || $typeFilter !== '' || $capabilityFilter !== ''): ?>
                            <a href="<?= e(url('/admin/attributes')) ?>">پاک‌کردن فیلترها</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($attributes === []): ?>
                    <div class="admin-products-empty">
                        <span class="admin-products-empty-icon" aria-hidden="true">▤</span>
                        <h3><?= $totalAttributes === 0 ? 'هنوز ویژگی‌ای ثبت نشده است' : 'ویژگی‌ای با این فیلترها پیدا نشد' ?></h3>
                        <p><?= $totalAttributes === 0 ? 'با تعریف ویژگی‌هایی مثل رنگ، اندازه یا جنس، اطلاعات محصولات را ساختارمند کنید.' : 'عبارت جست‌وجو یا فیلترهای انتخاب‌شده را تغییر دهید.' ?></p>
                        <?php if ($totalAttributes === 0): ?>
                            <a class="admin-products-primary-action" href="<?= e(url('/admin/attributes/create')) ?>">افزودن اولین ویژگی</a>
                        <?php else: ?>
                            <a class="admin-products-clear-link" href="<?= e(url('/admin/attributes')) ?>">نمایش همه ویژگی‌ها</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-products-table-wrap">
                        <table class="admin-products-table admin-attributes-table">
                            <thead>
                                <tr>
                                    <th scope="col">شناسه</th>
                                    <th scope="col">ویژگی</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">نوع داده</th>
                                    <th scope="col">قابلیت‌ها</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">ترتیب نمایش</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attributes as $attribute): ?>
                                    <?php
                                    $attributeStatus = (string)$attribute->status;
                                    $statusLabel = $statusLabels[$attributeStatus] ?? $attributeStatus;
                                    $statusClass = in_array($attributeStatus, $allowedStatuses, true) ? $attributeStatus : 'unknown';
                                    $typeLabel = $attributeTypes[$attribute->type] ?? $attribute->type;
                                    ?>
                                    <tr>
                                        <td class="admin-products-id">#<?= e((string)$attribute->id) ?></td>
                                        <td>
                                            <div class="admin-products-name-cell admin-attributes-name-cell">
                                                <span class="admin-products-product-mark" aria-hidden="true"><?= e(function_exists('mb_substr') ? mb_substr($attribute->name, 0, 1, 'UTF-8') : substr($attribute->name, 0, 1)) ?></span>
                                                <span class="admin-products-product-copy">
                                                    <strong><?= e($attribute->name) ?></strong>
                                                    <small><?= e($typeLabel) ?></small>
                                                </span>
                                            </div>
                                        </td>
                                        <td><code class="admin-products-sku" dir="ltr"><?= e($attribute->slug) ?></code></td>
                                        <td><span class="admin-attributes-type"><?= e($typeLabel) ?></span></td>
                                        <td>
                                            <div class="admin-attributes-flags" aria-label="قابلیت‌های ویژگی">
                                                <?php if ($attribute->isVariant): ?><span class="admin-attributes-flag">تنوع محصول</span><?php endif; ?>
                                                <?php if ($attribute->isFilterable): ?><span class="admin-attributes-flag">قابل فیلتر</span><?php endif; ?>
                                                <?php if ($attribute->isRequired): ?><span class="admin-attributes-flag">اجباری</span><?php endif; ?>
                                                <?php if (!$attribute->isVariant && !$attribute->isFilterable && !$attribute->isRequired): ?><span class="admin-attributes-no-flags">بدون قابلیت ویژه</span><?php endif; ?>
                                            </div>
                                        </td>
                                        <td><span class="admin-products-status admin-products-status-<?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                        <td class="admin-products-id"><?= e((string)$attribute->sortOrder) ?></td>
                                        <td><a class="admin-products-edit-action" href="<?= e(url('/admin/attributes/edit?id=' . $attribute->id)) ?>">ویرایش ←</a></td>
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
