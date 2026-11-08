<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new ProductTypeService();
$errors = [];
$error = null;
$csrfToken = Csrf::token();
$data = ['name' => '', 'slug' => '', 'description' => '', 'status' => 'active', 'sortOrder' => '0'];

if (Request::isPost()) {
    $token = Request::post('csrf_token');
    if (!Csrf::verify(is_string($token) ? $token : null)) {
        $error = 'درخواست نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        $data['name'] = trim((string)Request::post('name', ''));
        $data['slug'] = trim((string)Request::post('slug', ''));
        $data['description'] = trim((string)Request::post('description', ''));
        $data['status'] = trim((string)Request::post('status', 'active'));
        $data['sortOrder'] = trim((string)Request::post('sortOrder', '0'));
        $errors = (new ProductTypeValidator())->validate($data);
        if ($errors === [] && $service->findBySlug($data['slug']) !== null) {
            $errors['slug'] = 'این اسلاگ قبلاً برای یک نوع محصول ثبت شده است.';
        }
        if ($errors === []) {
            $service->create($data['name'], $data['slug'], $data['description'] === '' ? null : $data['description'], $data['status'], (int)$data['sortOrder']);
            Response::redirect(url('/admin/product-types'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ایجاد نوع محصول | نیواد</title>
</head>
<body>
<main class="admin-product-create-page admin-catalog-create-page admin-product-type-create-page">
    <div class="admin-product-create-content">
        <header class="admin-product-create-heading">
            <div>
                <a class="admin-product-create-back" href="<?= e(url('/admin/product-types')) ?>">← بازگشت به فهرست انواع محصول</a>
                <p class="admin-product-create-eyebrow">کاتالوگ فروشگاه</p>
                <h1>ایجاد نوع محصول</h1>
                <p>نوع محصول، ساختار پایه‌ای برای دسته‌بندی و مدیریت محصولات هم‌خانواده ایجاد می‌کند.</p>
            </div>
        </header>

        <?php if ($error !== null || $errors !== []): ?>
            <div class="admin-product-create-alert" role="alert">
                <strong>نوع محصول ثبت نشد</strong>
                <?php if ($error !== null): ?><span><?= e($error) ?></span><?php endif; ?>
                <?php foreach ($errors as $message): ?><span><?= e($message) ?></span><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-product-create-form" method="post" action="<?= e(url('/admin/product-types/create')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <section class="admin-product-create-section" aria-labelledby="product-type-create-basic-title">
                <div class="admin-product-create-section-heading">
                    <span class="admin-product-create-step">۱</span>
                    <div><h2 id="product-type-create-basic-title">اطلاعات پایه</h2><p>نام و شناسه‌ای که نوع محصول با آن در کاتالوگ شناخته می‌شود.</p></div>
                </div>
                <div class="admin-product-create-fields">
                    <label class="admin-product-create-field">
                        <span>نام نوع محصول <b aria-hidden="true">*</b></span>
                        <input name="name" value="<?= e($data['name']) ?>" maxlength="150" placeholder="مثلاً گوشی هوشمند" required>
                    </label>
                    <label class="admin-product-create-field">
                        <span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span>
                        <input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" placeholder="smartphone" autocomplete="off" required>
                        <small>شناسه فنی انگلیسی و بدون فاصله.</small>
                    </label>
                    <label class="admin-product-create-field admin-product-create-field-wide">
                        <span>توضیحات</span>
                        <textarea name="description" rows="4" placeholder="کاربرد و محدوده این نوع محصول را توضیح دهید."><?= e($data['description']) ?></textarea>
                        <small>اختیاری؛ برای راهنمایی مدیران در زمان ثبت محصول.</small>
                    </label>
                </div>
            </section>

            <section class="admin-product-create-section" aria-labelledby="product-type-create-publish-title">
                <div class="admin-product-create-section-heading">
                    <span class="admin-product-create-step">۲</span>
                    <div><h2 id="product-type-create-publish-title">وضعیت و ترتیب نمایش</h2><p>کنترل دسترس‌پذیری نوع محصول در فرایند مدیریت کاتالوگ.</p></div>
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
                        <input type="number" name="sortOrder" min="0" step="1" value="<?= e($data['sortOrder']) ?>">
                        <small>عدد کمتر، اولویت بالاتر.</small>
                    </label>
                </div>
            </section>

            <footer class="admin-product-create-actions">
                <a href="<?= e(url('/admin/product-types')) ?>">انصراف</a>
                <button type="submit">ثبت نوع محصول <span aria-hidden="true">←</span></button>
            </footer>
        </form>
    </div>
</main>
</body>
</html>