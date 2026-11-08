<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new ProductAttributeService();
$validator = new ProductAttributeValidator();
$error = null;
$errors = [];
$csrfToken = Csrf::token();
$data = ['name' => '', 'slug' => '', 'type' => 'select', 'status' => 'active', 'sortOrder' => '0'];

if (Request::isPost()) {
    $token = Request::post('csrf_token');
    if (!Csrf::verify(is_string($token) ? $token : null)) {
        $error = 'درخواست نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        foreach (['name', 'slug', 'type', 'status', 'sortOrder'] as $field) {
            $value = Request::post($field, $data[$field]);
            $data[$field] = is_scalar($value) ? trim((string)$value) : $data[$field];
        }
        $errors = $validator->validate($data);
        if ($errors === [] && $service->findBySlug($data['slug']) !== null) {
            $errors['slug'] = 'این اسلاگ قبلاً برای یک ویژگی ثبت شده است.';
        }
        if ($errors === []) {
            $service->create($data['name'], $data['slug'], $data['type'], $data['status'], (int)$data['sortOrder']);
            Response::redirect(url('/admin/attributes'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ایجاد ویژگی | نیواد</title>
</head>
<body>
<main class="admin-product-create-page admin-catalog-create-page admin-attribute-create-page">
    <div class="admin-product-create-content">
        <header class="admin-product-create-heading">
            <div>
                <a class="admin-product-create-back" href="<?= e(url('/admin/attributes')) ?>">← بازگشت به فهرست ویژگی‌ها</a>
                <p class="admin-product-create-eyebrow">کاتالوگ فروشگاه</p>
                <h1>ایجاد ویژگی جدید</h1>
                <p>ویژگی‌هایی مانند رنگ، ظرفیت یا اندازه را تعریف کنید تا در انتخاب و فیلتر محصولات استفاده شوند.</p>
            </div>
        </header>

        <?php if ($error !== null || $errors !== []): ?>
            <div class="admin-product-create-alert" role="alert">
                <strong>ویژگی ثبت نشد</strong>
                <?php if ($error !== null): ?><span><?= e($error) ?></span><?php endif; ?>
                <?php foreach ($errors as $message): ?><span><?= e($message) ?></span><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-product-create-form" method="post" action="<?= e(url('/admin/attributes/create')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <section class="admin-product-create-section" aria-labelledby="attribute-create-basic-title">
                <div class="admin-product-create-section-heading">
                    <span class="admin-product-create-step">۱</span>
                    <div><h2 id="attribute-create-basic-title">اطلاعات ویژگی</h2><p>نام، شناسه و نوع داده‌ای که برای این ویژگی ذخیره می‌شود.</p></div>
                </div>
                <div class="admin-product-create-fields">
                    <label class="admin-product-create-field">
                        <span>نام ویژگی <b aria-hidden="true">*</b></span>
                        <input name="name" value="<?= e($data['name']) ?>" maxlength="150" placeholder="مثلاً رنگ" required>
                        <small>نام قابل نمایش در پنل و صفحه محصول.</small>
                    </label>
                    <label class="admin-product-create-field">
                        <span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span>
                        <input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" placeholder="color" autocomplete="off" required>
                        <small>شناسه فنی بدون فاصله و ترجیحاً با حروف انگلیسی.</small>
                    </label>
                    <label class="admin-product-create-field">
                        <span>نوع ویژگی <b aria-hidden="true">*</b></span>
                        <select name="type" required>
                            <?php foreach (['select' => 'انتخابی', 'text' => 'متنی', 'number' => 'عددی', 'boolean' => 'بله / خیر', 'color' => 'رنگ'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $data['type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small>نوع داده باید با نحوه دریافت مقدار ویژگی هماهنگ باشد.</small>
                    </label>
                </div>
            </section>

            <section class="admin-product-create-section" aria-labelledby="attribute-create-display-title">
                <div class="admin-product-create-section-heading">
                    <span class="admin-product-create-step">۲</span>
                    <div><h2 id="attribute-create-display-title">وضعیت و ترتیب نمایش</h2><p>تعیین کنید ویژگی از ابتدا در دسترس باشد یا خیر.</p></div>
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
                        <small>عدد کمتر، اولویت بالاتر.</small>
                    </label>
                </div>
            </section>

            <footer class="admin-product-create-actions">
                <a href="<?= e(url('/admin/attributes')) ?>">انصراف</a>
                <button type="submit">ثبت ویژگی <span aria-hidden="true">←</span></button>
            </footer>
        </form>
    </div>
</main>
</body>
</html>