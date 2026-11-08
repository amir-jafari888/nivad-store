<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new SpecificationService();
$groupService = new SpecGroupService();
$allSpecifications = $service->findAll();
$groups = $groupService->findAll();
$groupNamesById = [];
foreach ($groups as $group) { $groupNamesById[$group->id] = $group->name; }

$searchValue = Request::query('q', '');
$groupValue = Request::query('group_id', '');
$statusValue = Request::query('status', '');
$typeValue = Request::query('value_type', '');
$search = is_string($searchValue) ? trim($searchValue) : '';
$groupFilter = is_scalar($groupValue) ? trim((string)$groupValue) : '';
$statusFilter = is_string($statusValue) ? trim($statusValue) : '';
$typeFilter = is_string($typeValue) ? trim($typeValue) : '';

$allowedStatuses = ['active', 'inactive'];
$statusLabels = ['active' => 'فعال', 'inactive' => 'غیرفعال'];
$valueTypes = ['text' => 'متنی', 'number' => 'عددی', 'boolean' => 'بله / خیر', 'date' => 'تاریخ'];
if (!in_array($statusFilter, $allowedStatuses, true)) { $statusFilter = ''; }
if (!array_key_exists($typeFilter, $valueTypes)) { $typeFilter = ''; }
if ($groupFilter !== '' && (!ctype_digit($groupFilter) || !isset($groupNamesById[(int)$groupFilter]))) { $groupFilter = ''; }

$specifications = array_values(array_filter(
    $allSpecifications,
    static function (Specification $item) use ($search, $groupFilter, $statusFilter, $typeFilter, $groupNamesById, $valueTypes): bool {
        if ($groupFilter !== '' && $item->specGroupId !== (int)$groupFilter) { return false; }
        if ($statusFilter !== '' && $item->status !== $statusFilter) { return false; }
        if ($typeFilter !== '' && $item->valueType !== $typeFilter) { return false; }
        if ($search === '') { return true; }
        $haystack = implode(' ', [
            (string)$item->id, $item->name, $item->slug, (string)($item->unit ?? ''),
            (string)($groupNamesById[$item->specGroupId] ?? ''), $item->valueType,
            $valueTypes[$item->valueType] ?? '',
        ]);
        return function_exists('mb_stripos')
            ? mb_stripos($haystack, $search, 0, 'UTF-8') !== false
            : stripos($haystack, $search) !== false;
    }
));
$statusCounts = array_fill_keys($allowedStatuses, 0);
foreach ($allSpecifications as $item) {
    if (array_key_exists($item->status, $statusCounts)) { $statusCounts[$item->status]++; }
}
$totalSpecifications = count($allSpecifications);
$visibleSpecifications = count($specifications);
$totalGroups = count($groups);
$activeGroups = count(array_filter($groups, static fn (SpecGroup $group): bool => $group->status === 'active'));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت مشخصات فنی | نیواد</title>
</head>
<body>
<main class="admin-products-page admin-specifications-page">
    <div class="admin-products-content">
        <header class="admin-products-heading">
            <div class="admin-products-heading-copy">
                <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                <h1>مدیریت مشخصات فنی</h1>
                <p>مشخصات قابل ثبت برای محصولات را بر اساس گروه، نوع مقدار و واحد اندازه‌گیری مدیریت کنید.</p>
            </div>
            <div class="admin-specifications-heading-actions">
                <a class="admin-specifications-secondary-action" href="<?= e(url('/admin/specifications/groups')) ?>">مدیریت گروه‌ها</a>
                <a class="admin-products-primary-action" href="<?= e(url('/admin/specifications/create')) ?>"><span aria-hidden="true">＋</span> افزودن مشخصه</a>
            </div>
        </header>

        <section class="admin-products-summary admin-specifications-summary" aria-label="خلاصه مشخصات فنی">
            <article class="admin-products-stat admin-products-stat-total">
                <span class="admin-products-stat-label">کل مشخصات</span>
                <strong><?= e((string)$totalSpecifications) ?></strong>
                <span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span>
            </article>
            <article class="admin-products-stat">
                <span class="admin-products-stat-label">فعال</span>
                <strong><?= e((string)$statusCounts['active']) ?></strong>
                <span class="admin-products-stat-note">قابل استفاده برای محصولات</span>
            </article>
            <article class="admin-products-stat">
                <span class="admin-products-stat-label">غیرفعال</span>
                <strong><?= e((string)$statusCounts['inactive']) ?></strong>
                <span class="admin-products-stat-note">خارج از چرخه استفاده</span>
            </article>
            <article class="admin-products-stat">
                <span class="admin-products-stat-label">گروه‌های فعال</span>
                <strong><?= e((string)$activeGroups) ?></strong>
                <span class="admin-products-stat-note"><?= e((string)$totalGroups) ?> گروه ثبت‌شده</span>
            </article>
        </section>

        <section class="admin-products-panel" aria-labelledby="admin-specifications-list-title">
            <div class="admin-products-panel-heading">
                <div>
                    <h2 id="admin-specifications-list-title">فهرست مشخصات فنی</h2>
                    <p><?= e((string)$visibleSpecifications) ?> مورد از <?= e((string)$totalSpecifications) ?> مشخصه</p>
                </div>
            </div>
            <form class="admin-products-filters admin-specifications-filters" method="get" action="<?= e(url('/admin/specifications')) ?>">
                <label class="admin-products-search">
                    <span>جست‌وجو</span>
                    <input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، شناسه، Slug، واحد یا گروه…" autocomplete="off">
                </label>
                <label class="admin-specifications-group-filter">
                    <span>گروه مشخصات</span>
                    <select name="group_id">
                        <option value="">همه گروه‌ها</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= e((string)$group->id) ?>" <?= $groupFilter === (string)$group->id ? 'selected' : '' ?>><?= e($group->name) ?><?= $group->status === 'inactive' ? ' (غیرفعال)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="admin-products-status-filter">
                    <span>وضعیت</span>
                    <select name="status">
                        <option value="">همه وضعیت‌ها</option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="admin-specifications-type-filter">
                    <span>نوع مقدار</span>
                    <select name="value_type">
                        <option value="">همه نوع‌ها</option>
                        <?php foreach ($valueTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $typeFilter === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="admin-products-filter-actions">
                    <button type="submit">اعمال فیلتر</button>
                    <?php if ($search !== '' || $groupFilter !== '' || $statusFilter !== '' || $typeFilter !== ''): ?>
                        <a href="<?= e(url('/admin/specifications')) ?>">پاک‌کردن فیلترها</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($specifications === []): ?>
                <div class="admin-products-empty">
                    <span class="admin-products-empty-icon" aria-hidden="true">▤</span>
                    <h3><?= $totalSpecifications === 0 ? 'هنوز مشخصه‌ای ثبت نشده است' : 'مشخصه‌ای با این فیلترها پیدا نشد' ?></h3>
                    <p><?= $totalSpecifications === 0 ? 'با تعریف مشخصات فنی مثل وزن، ابعاد یا ظرفیت، اطلاعات محصولات را ساختارمند کنید.' : 'عبارت جست‌وجو یا فیلترهای انتخاب‌شده را تغییر دهید.' ?></p>
                    <?php if ($totalSpecifications === 0): ?>
                        <a class="admin-products-primary-action" href="<?= e(url('/admin/specifications/create')) ?>">افزودن اولین مشخصه</a>
                    <?php else: ?>
                        <a class="admin-products-clear-link" href="<?= e(url('/admin/specifications')) ?>">نمایش همه مشخصات</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="admin-products-table-wrap">
                    <table class="admin-products-table admin-specifications-table">
                        <thead><tr>
                            <th scope="col">مشخصه</th><th scope="col">گروه</th><th scope="col">نوع مقدار</th>
                            <th scope="col">واحد</th><th scope="col">وضعیت</th><th scope="col">ترتیب</th><th scope="col">عملیات</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($specifications as $item): ?>
                            <tr>
                                <td><div class="admin-specifications-name"><strong><?= e($item->name) ?></strong><span><?= e($item->slug) ?> · #<?= e((string)$item->id) ?></span></div></td>
                                <td><?= e($groupNamesById[$item->specGroupId] ?? 'گروه نامشخص') ?></td>
                                <td><span class="admin-specifications-value-type"><?= e($valueTypes[$item->valueType] ?? $item->valueType) ?></span></td>
                                <td><?= $item->unit !== null && $item->unit !== '' ? e($item->unit) : '<span class="admin-specifications-no-unit">—</span>' ?></td>
                                <td><span class="admin-products-status admin-products-status-<?= e($item->status) ?>"><?= e($statusLabels[$item->status] ?? 'نامشخص') ?></span></td>
                                <td><?= e((string)$item->sortOrder) ?></td>
                                <td><a class="admin-products-edit-action" href="<?= e(url('/admin/specifications/edit?id=' . $item->id)) ?>">ویرایش</a></td>
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
