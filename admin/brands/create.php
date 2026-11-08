<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new BrandService();
$validator = new BrandValidator();
$error = null;
$errors = [];
$csrfToken = Csrf::token();

$data = [
    'name' => '',
    'slug' => '',
    'description' => '',
    'status' => 'active',
    'sortOrder' => '0',
];

if (Request::isPost()) {
    $token = Request::post('csrf_token');

    if (!Csrf::verify(is_string($token) ? $token : null)) {
        $error = 'درخواست نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        $data['name'] = trim((string)Request::post('name', ''));
        $data['slug'] = trim((string)Request::post('slug', ''));
        $data['description'] = trim((string)Request::post('description', ''));
        $data['status'] = trim((string)Request::post('status', 'active'));
        $data['sortOrder'] = (string)Request::post('sortOrder', '0');

        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'status' => $data['status'],
            'sortOrder' => (int)$data['sortOrder'],
        ];

        $errors = $validator->validate($payload);

        if ($errors === [] && $service->findBySlug($payload['slug']) !== null) {
            $errors['slug'] = 'اسلاگ برند تکراری است.';
        }

        if ($errors === []) {
            $service->create(
                $payload['name'],
                $payload['slug'],
                $payload['description'] === '' ? null : $payload['description'],
                $payload['status'],
                $payload['sortOrder']
            );
            Response::redirect(url('/admin/brands'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>افزودن برند | نیواد</title>
</head>

<body>
    <main class="admin-product-create-page">
        <div class="admin-product-create-content">
            <header class="admin-product-create-heading">
                <div>
                    <a class="admin-product-create-back" href="<?= e(url('/admin/brands')) ?>">← بازگشت به فهرست برندها</a>
                    <p class="admin-product-create-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>افزودن برند جدید</h1>
                    <p>نام، شناسه یکتا و اطلاعات نمایشی برند را ثبت و وضعیت انتشار آن را مشخص کنید.</p>
                </div>
            </header>

            <?php if ($error !== null): ?>
                <div class="admin-product-create-alert" role="alert">
                    <strong>برند ثبت نشد</strong>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="admin-product-create-alert" role="alert" aria-labelledby="brand-create-errors-title">
                    <strong id="brand-create-errors-title">اطلاعات واردشده نیاز به اصلاح دارد</strong>
                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="admin-product-create-form" method="post" action="<?= e(url('/admin/brands/create')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <section class="admin-product-create-section" aria-labelledby="brand-create-basic-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۱</span>
                        <div>
                            <h2 id="brand-create-basic-title">اطلاعات اصلی</h2>
                            <p>نام قابل نمایش و شناسه یکتای برند</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>نام برند <b aria-hidden="true">*</b></span>
                            <input name="name" value="<?= e($data['name']) ?>" maxlength="255" placeholder="مثلاً سامسونگ" autocomplete="off" required>
                            <small>نام رسمی و قابل تشخیص برند را وارد کنید.</small>
                        </label>
                        <label class="admin-product-create-field">
                            <span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span>
                            <input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" placeholder="samsung" autocomplete="off" required>
                            <small>برای آدرس اختصاصی برند استفاده می‌شود؛ بهتر است انگلیسی و بدون فاصله باشد.</small>
                        </label>
                    </div>
                </section>

                <section class="admin-product-create-section" aria-labelledby="brand-create-description-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۲</span>
                        <div>
                            <h2 id="brand-create-description-title">معرفی برند</h2>
                            <p>توضیحی درباره برند و محصولات آن</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>توضیحات</span>
                            <textarea name="description" rows="5" placeholder="درباره این برند و حوزه فعالیت آن بنویسید…"><?= e($data['description']) ?></textarea>
                            <small>اختیاری؛ توضیحی مختصر و دقیق برای آشنایی بهتر با برند بنویسید.</small>
                        </label>
                    </div>
                </section>

                <section class="admin-product-create-section" aria-labelledby="brand-create-publish-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۳</span>
                        <div>
                            <h2 id="brand-create-publish-title">وضعیت و ترتیب نمایش</h2>
                            <p>نحوه فعال‌بودن و اولویت نمایش برند در کاتالوگ</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field">
                            <span>وضعیت اولیه</span>
                            <select name="status">
                                <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                                <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                            </select>
                        </label>
                        <label class="admin-product-create-field">
                            <span>اولویت نمایش</span>
                            <input name="sortOrder" type="number" min="0" step="1" value="<?= e($data['sortOrder']) ?>">
                            <small>عدد کمتر، اولویت بالاتر؛ مقدار پیش‌فرض صفر است.</small>
                        </label>
                    </div>
                </section>

                <footer class="admin-product-create-actions">
                    <a href="<?= e(url('/admin/brands')) ?>">انصراف</a>
                    <button type="submit">ایجاد برند <span aria-hidden="true">←</span></button>
                </footer>
            </form>
        </div>
    </main>
</body>

</html>
