<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new SpecificationService();
$groupService = new SpecGroupService();
$validator = new SpecificationValidator();
$groups = $groupService->findActive();
$errors = [];
$error = null;
$csrfToken = Csrf::token();
$data = ['specGroupId' => '', 'name' => '', 'slug' => '', 'valueType' => 'text', 'unit' => '', 'status' => 'active', 'sortOrder' => '0'];

if (Request::isPost()) {
    $token = Request::post('csrf_token');
    if (!Csrf::verify(is_string($token) ? $token : null)) {
        $error = 'درخواست نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        foreach (['specGroupId', 'name', 'slug', 'valueType', 'unit', 'status', 'sortOrder'] as $field) {
            $value = Request::post($field, $data[$field]);
            $data[$field] = is_scalar($value) ? trim((string)$value) : $data[$field];
        }
        $payload = $data;
        $payload['unit'] = $data['unit'] === '' ? null : $data['unit'];
        $errors = $validator->validate($payload);
        if ($errors === [] && $groupService->findActiveById((int)$data['specGroupId']) === null) {
            $errors['specGroupId'] = 'گروه انتخاب‌شده معتبر یا فعال نیست.';
        }
        if ($errors === [] && $service->existsBySlug($data['slug'])) {
            $errors['slug'] = 'این اسلاگ قبلاً برای یک مشخصه ثبت شده است.';
        }
        if ($errors === []) {
            $service->create((int)$data['specGroupId'], $data['name'], $data['slug'], $data['valueType'], $payload['unit'], (int)$data['sortOrder'], $data['status']);
            Response::redirect(url('/admin/specifications'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ایجاد مشخصه فنی | نیواد</title>
</head>
<body>
<main class="admin-product-create-page admin-catalog-create-page admin-specification-create-page">
    <div class="admin-product-create-content">
        <header class="admin-product-create-heading">
            <div>
                <a class="admin-product-create-back" href="<?= e(url('/admin/specifications')) ?>">← بازگشت به فهرست مشخصات فنی</a>
                <p class="admin-product-create-eyebrow">کاتالوگ فروشگاه</p>
                <h1>ایجاد مشخصه فنی</h1>
                <p>یک مشخصه قابل‌اندازه‌گیری یا توصیفی تعریف کنید و آن را به گروه مناسب متصل کنید.</p>
            </div>
        </header>

        <?php if ($error !== null || $errors !== []): ?>
            <div class="admin-product-create-alert" role="alert">
                <strong>مشخصه فنی ثبت نشد</strong>
                <?php if ($error !== null): ?><span><?= e($error) ?></span><?php endif; ?>
                <?php foreach ($errors as $message): ?><span><?= e($message) ?></span><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($groups === []): ?>
            <aside class="admin-product-create-prerequisite" role="status">
                <strong>ابتدا یک گروه مشخصات فعال ایجاد کنید.</strong>
                <p>هر مشخصه باید به یک گروه معتبر تعلق داشته باشد.</p>
                <div><a href="<?= e(url('/admin/specifications/groups')) ?>">مدیریت گروه‌های مشخصات</a></div>
            </aside>
        <?php endif; ?>

        <form class="admin-product-create-form" method="post" action="<?= e(url('/admin/specifications/create')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <section class="admin-product-create-section" aria-labelledby="specification-create-basic-title">
                <div class="admin-product-create-section-heading">
                    <span class="admin-product-create-step">۱</span>
                    <div><h2 id="specification-create-basic-title">تعریف مشخصه</h2><p>مشخصه را به یک گروه متصل کنید و نام و شناسه فنی آن را مشخص کنید.</p></div>
                </div>
                <div class="admin-product-create-fields">
                    <label class="admin-product-create-field">
                        <span>گروه مشخصات <b aria-hidden="true">*</b></span>
                        <select name="specGroupId" required <?= $groups === [] ? 'disabled' : '' ?>>
                            <option value="">انتخاب گروه</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= e((string)$group->id) ?>" <?= $data['specGroupId'] === (string)$group->id ? 'selected' : '' ?>><?= e($group->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-product-create-field">
                        <span>نام مشخصه <b aria-hidden="true">*</b></span>
                        <input name="name" value="<?= e($data['name']) ?>" maxlength="150" placeholder="مثلاً ظرفیت حافظه" required>
                    </label>
                    <label class="admin-product-create-field">
                        <span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span>
                        <input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" maxlength="180" placeholder="storage-capacity" required>
                    </label>
                </div>
            </section>

            <section class="admin-product-create-section" aria-labelledby="specification-create-value-title">
                <div class="admin-product-create-section-heading">
                    <span class="admin-product-create-step">۲</span>
                    <div><h2 id="specification-create-value-title">نوع مقدار و واحد</h2><p>نوع داده با مقدارهایی که برای محصولات ذخیره می‌شوند هماهنگ باشد.</p></div>
                </div>
                <div class="admin-product-create-fields">
                    <label class="admin-product-create-field">
                        <span>نوع مقدار <b aria-hidden="true">*</b></span>
                        <select name="valueType" required>
                            <option value="text" <?= $data['valueType'] === 'text' ? 'selected' : '' ?>>متنی</option>
                            <option value="number" <?= $data['valueType'] === 'number' ? 'selected' : '' ?>>عددی</option>
                            <option value="boolean" <?= $data['valueType'] === 'boolean' ? 'selected' : '' ?>>بله / خیر</option>
                            <option value="date" <?= $data['valueType'] === 'date' ? 'selected' : '' ?>>تاریخ</option>
                        </select>
                    </label>
                    <label class="admin-product-create-field">
                        <span>واحد اندازه‌گیری</span>
                        <input name="unit" value="<?= e($data['unit']) ?>" maxlength="30" placeholder="مثلاً GB، mm یا kg">
                        <small>برای مقدارهای عددی؛ حداکثر ۳۰ کاراکتر.</small>
                    </label>
                </div>
            </section>

            <section class="admin-product-create-section" aria-labelledby="specification-create-display-title">
                <div class="admin-product-create-section-heading">
                    <span class="admin-product-create-step">۳</span>
                    <div><h2 id="specification-create-display-title">وضعیت و ترتیب نمایش</h2><p>تنظیمات اولیه استفاده از مشخصه در کاتالوگ.</p></div>
                </div>
                <div class="admin-product-create-fields">
                    <label class="admin-product-create-field">
                        <span>وضعیت</span>
                        <select name="status">
                            <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                            <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                        </select>
                    </label>
                    <label class="admin-product-create-field">
                        <span>اولویت نمایش</span>
                        <input name="sortOrder" type="number" min="0" step="1" value="<?= e($data['sortOrder']) ?>">
                    </label>
                </div>
            </section>

            <footer class="admin-product-create-actions">
                <a href="<?= e(url('/admin/specifications')) ?>">انصراف</a>
                <button type="submit" <?= $groups === [] ? 'disabled' : '' ?>>ثبت مشخصه فنی <span aria-hidden="true">←</span></button>
            </footer>
        </form>
    </div>
</main>
</body>
</html>