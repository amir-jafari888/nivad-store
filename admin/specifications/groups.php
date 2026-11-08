<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new SpecGroupService();
$validator = new SpecGroupValidator();
$error = null;
$errors = [];
$csrfToken = Csrf::token();
$data = ['name' => '', 'slug' => '', 'status' => 'active', 'sortOrder' => '0'];

if (Request::isPost()) {
    $token = Request::post('csrf_token');
    if (!Csrf::verify(is_string($token) ? $token : null)) {
        $error = 'درخواست نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        $action = trim((string)Request::post('action', 'create'));
        if ($action === 'create') {
            $data['name'] = trim((string)Request::post('name', ''));
            $data['slug'] = trim((string)Request::post('slug', ''));
            $data['status'] = trim((string)Request::post('status', 'active'));
            $data['sortOrder'] = trim((string)Request::post('sortOrder', '0'));
            $errors = $validator->validate($data);
            if ($errors === [] && $service->findBySlug($data['slug']) !== null) {
                $errors['slug'] = 'این اسلاگ قبلاً برای یک گروه مشخصات ثبت شده است.';
            }
            if ($errors === []) {
                $service->create($data['name'], $data['slug'], $data['status'], (int)$data['sortOrder']);
                Response::redirect(url('/admin/specifications/groups'));
            }
        } elseif ($action === 'delete' || $action === 'restore') {
            $id = (int)Request::post('id', 0);
            if ($id > 0) {
                $action === 'delete' ? $service->delete($id) : $service->restore($id);
            }
            Response::redirect(url('/admin/specifications/groups'));
        } elseif ($action === 'status') {
            $id = (int)Request::post('id', 0);
            $status = trim((string)Request::post('status', ''));
            if ($id > 0 && in_array($status, ['active', 'inactive'], true)) {
                $service->setStatus($id, $status);
            }
            Response::redirect(url('/admin/specifications/groups'));
        } else {
            $error = 'عملیات درخواستی معتبر نیست.';
        }
    }
}

$allGroups = $service->findAllIncludingDeleted();
$searchValue = Request::query('q', '');
$statusValue = Request::query('status', '');
$search = is_string($searchValue) ? trim($searchValue) : '';
$statusFilter = is_string($statusValue) ? trim($statusValue) : '';
if (!in_array($statusFilter, ['active', 'inactive', 'deleted'], true)) {
    $statusFilter = '';
}
$groups = array_values(array_filter($allGroups, static function (SpecGroup $group) use ($search, $statusFilter): bool {
    $isDeleted = $group->deletedAt !== null;
    if ($statusFilter === 'deleted' && !$isDeleted) return false;
    if ($statusFilter === 'active' && ($isDeleted || $group->status !== 'active')) return false;
    if ($statusFilter === 'inactive' && ($isDeleted || $group->status !== 'inactive')) return false;
    if ($search === '') return true;
    $haystack = $group->name . ' ' . $group->slug . ' ' . $group->id;
    return function_exists('mb_stripos') ? mb_stripos($haystack, $search, 0, 'UTF-8') !== false : stripos($haystack, $search) !== false;
}));
$totalGroups = count($allGroups);
$activeGroups = count(array_filter($allGroups, static fn(SpecGroup $group): bool => $group->deletedAt === null && $group->status === 'active'));
$inactiveGroups = count(array_filter($allGroups, static fn(SpecGroup $group): bool => $group->deletedAt === null && $group->status === 'inactive'));
$deletedGroups = count(array_filter($allGroups, static fn(SpecGroup $group): bool => $group->deletedAt !== null));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>گروه‌های مشخصات | نیواد</title>
</head>
<body>
<main class="admin-products-page admin-specification-groups-page">
    <div class="admin-products-content">
        <header class="admin-products-heading">
            <div class="admin-products-heading-copy">
                <a class="admin-product-create-back" href="<?= e(url('/admin/specifications')) ?>">← بازگشت به مشخصات فنی</a>
                <p class="admin-products-eyebrow">کاتالوگ فروشگاه</p>
                <h1>گروه‌های مشخصات</h1>
                <p>مشخصات فنی را در گروه‌های معنادار مانند نمایشگر، پردازنده و باتری سازمان‌دهی کنید.</p>
            </div>
            <a class="admin-products-primary-action" href="#create-spec-group"><span aria-hidden="true">＋</span> ایجاد گروه</a>
        </header>

        <section class="admin-products-summary admin-specification-groups-summary" aria-label="خلاصه گروه‌های مشخصات">
            <article class="admin-products-stat admin-products-stat-total"><span class="admin-products-stat-label">کل گروه‌ها</span><strong><?= e((string)$totalGroups) ?></strong><span class="admin-products-stat-note">ثبت‌شده در کاتالوگ</span></article>
            <article class="admin-products-stat"><span class="admin-products-stat-label">فعال</span><strong><?= e((string)$activeGroups) ?></strong><span class="admin-products-stat-note">قابل استفاده برای مشخصات</span></article>
            <article class="admin-products-stat"><span class="admin-products-stat-label">غیرفعال</span><strong><?= e((string)$inactiveGroups) ?></strong><span class="admin-products-stat-note">بدون حذف از کاتالوگ</span></article>
            <article class="admin-products-stat"><span class="admin-products-stat-label">حذف‌شده</span><strong><?= e((string)$deletedGroups) ?></strong><span class="admin-products-stat-note">قابل بازگردانی</span></article>
        </section>

        <?php if ($error !== null || $errors !== []): ?>
            <div class="admin-product-create-alert" role="alert">
                <strong>عملیات گروه مشخصات انجام نشد</strong>
                <?php if ($error !== null): ?><span><?= e($error) ?></span><?php endif; ?>
                <?php foreach ($errors as $message): ?><span><?= e($message) ?></span><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="admin-products-panel admin-specification-groups-create-panel" id="create-spec-group" aria-labelledby="create-spec-group-title">
            <div class="admin-products-panel-heading"><div><h2 id="create-spec-group-title">ایجاد گروه جدید</h2><p>نام و شناسه یکتا را وارد کنید؛ وضعیت و ترتیب نمایش قابل تغییر است.</p></div></div>
            <form class="admin-product-create-form admin-specification-group-form" method="post" action="<?= e(url('/admin/specifications/groups')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="create">
                <div class="admin-product-create-fields">
                    <label class="admin-product-create-field"><span>نام گروه <b aria-hidden="true">*</b></span><input name="name" value="<?= e($data['name']) ?>" maxlength="100" placeholder="مثلاً نمایشگر" required></label>
                    <label class="admin-product-create-field"><span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span><input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" maxlength="120" placeholder="display" required></label>
                    <label class="admin-product-create-field"><span>وضعیت</span><select name="status"><option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>فعال</option><option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>غیرفعال</option></select></label>
                    <label class="admin-product-create-field"><span>اولویت نمایش</span><input name="sortOrder" type="number" min="0" step="1" value="<?= e($data['sortOrder']) ?>"></label>
                </div>
                <div class="admin-specification-group-submit"><button type="submit">ثبت گروه <span aria-hidden="true">←</span></button></div>
            </form>
        </section>

        <section class="admin-products-panel" aria-labelledby="specification-groups-list-title">
            <div class="admin-products-panel-heading"><div><h2 id="specification-groups-list-title">فهرست گروه‌ها</h2><p><?= e((string)count($groups)) ?> مورد از <?= e((string)$totalGroups) ?> گروه</p></div></div>
            <form class="admin-products-filters admin-specification-groups-filters" method="get" action="<?= e(url('/admin/specifications/groups')) ?>">
                <label class="admin-products-search"><span>جست‌وجو</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="نام، شناسه یا Slug گروه…" autocomplete="off"></label>
                <label class="admin-products-filter"><span>وضعیت</span><select name="status"><option value="">همه وضعیت‌ها</option><option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>فعال</option><option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>غیرفعال</option><option value="deleted" <?= $statusFilter === 'deleted' ? 'selected' : '' ?>>حذف‌شده</option></select></label>
                <div class="admin-products-filter-actions"><button type="submit">اعمال فیلتر</button><a href="<?= e(url('/admin/specifications/groups')) ?>">پاک‌کردن</a></div>
            </form>

            <?php if ($groups === []): ?>
                <div class="admin-products-empty" role="status"><strong><?= $totalGroups === 0 ? 'هنوز گروهی ایجاد نشده است.' : 'گروهی با این فیلتر پیدا نشد.' ?></strong><p><?= $totalGroups === 0 ? 'برای شروع، اولین گروه مشخصات را از فرم بالا ثبت کنید.' : 'عبارت جست‌وجو یا فیلتر وضعیت را تغییر دهید.' ?></p></div>
            <?php else: ?>
                <div class="admin-products-table-wrap">
                    <table class="admin-products-table admin-specification-groups-table">
                        <thead><tr><th scope="col">گروه</th><th scope="col">Slug</th><th scope="col">وضعیت</th><th scope="col">ترتیب</th><th scope="col">عملیات</th></tr></thead>
                        <tbody>
                        <?php foreach ($groups as $group): ?>
                            <?php $isDeleted = $group->deletedAt !== null; ?>
                            <tr>
                                <td><strong><?= e($group->name) ?></strong><small>#<?= e((string)$group->id) ?></small></td>
                                <td><code dir="ltr"><?= e($group->slug) ?></code></td>
                                <td><span class="admin-products-status <?= $isDeleted ? 'admin-products-status-inactive' : ($group->status === 'active' ? 'admin-products-status-active' : 'admin-products-status-inactive') ?>"><?= $isDeleted ? 'حذف‌شده' : ($group->status === 'active' ? 'فعال' : 'غیرفعال') ?></span></td>
                                <td><?= e((string)$group->sortOrder) ?></td>
                                <td class="admin-specification-group-actions">
                                    <a class="admin-products-edit-action" href="<?= e(url('/admin/specifications?group_id=' . $group->id)) ?>">مشخصات گروه</a>
                                    <?php if (!$isDeleted): ?>
                                        <form method="post" action="<?= e(url('/admin/specifications/groups')) ?>"><input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= e((string)$group->id) ?>"><input type="hidden" name="status" value="<?= $group->status === 'active' ? 'inactive' : 'active' ?>"><button type="submit"><?= $group->status === 'active' ? 'غیرفعال‌سازی' : 'فعال‌سازی' ?></button></form>
                                        <form method="post" action="<?= e(url('/admin/specifications/groups')) ?>" onsubmit="return confirm('گروه مشخصات حذف شود؟')"><input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$group->id) ?>"><button type="submit" class="is-muted">حذف</button></form>
                                    <?php else: ?>
                                        <form method="post" action="<?= e(url('/admin/specifications/groups')) ?>"><input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="action" value="restore"><input type="hidden" name="id" value="<?= e((string)$group->id) ?>"><button type="submit">بازگردانی</button></form>
                                    <?php endif; ?>
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