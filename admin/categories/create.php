<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new CategoryService();
$validator = new CategoryValidator();
$parents = $service->findActive();
$error = null;
$errors = [];
$csrfToken = Csrf::token();

$data = [
    'parentId' => '',
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
        $parentValue = Request::post('parentId', '');
        $data['parentId'] = is_string($parentValue) ? trim($parentValue) : '';
        $data['name'] = trim((string)Request::post('name', ''));
        $data['slug'] = trim((string)Request::post('slug', ''));
        $data['description'] = trim((string)Request::post('description', ''));
        $data['status'] = trim((string)Request::post('status', 'active'));
        $data['sortOrder'] = (string)Request::post('sortOrder', '0');

        $payload = [
            'parentId' => $data['parentId'] === '' ? null : (int)$data['parentId'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'status' => $data['status'],
            'sortOrder' => (int)$data['sortOrder'],
        ];

        $errors = $validator->validate($payload);

        if ($errors === [] && $service->findBySlug($payload['slug']) !== null) {
            $errors['slug'] = 'اسلاگ دسته‌بندی تکراری است.';
        }

        if ($errors === [] && $payload['parentId'] !== null && !$service->exists($payload['parentId'])) {
            $errors['parentId'] = 'دسته‌بندی والد نامعتبر است.';
        }

        if ($errors === []) {
            $service->create(
                $payload['parentId'],
                $payload['name'],
                $payload['slug'],
                $payload['description'] === '' ? null : $payload['description'],
                $payload['status'],
                $payload['sortOrder']
            );
            Response::redirect(url('/admin/categories'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>افزودن دسته‌بندی | نیواد</title>
</head>

<body>
    <main class="admin-product-create-page">
        <div class="admin-product-create-content">
            <header class="admin-product-create-heading">
                <div>
                    <a class="admin-product-create-back" href="<?= e(url('/admin/categories')) ?>">← بازگشت به فهرست دسته‌بندی‌ها</a>
                    <p class="admin-product-create-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>افزودن دسته‌بندی جدید</h1>
                    <p>اطلاعات پایه، جایگاه دسته‌بندی در ساختار کاتالوگ و وضعیت نمایش آن را مشخص کنید.</p>
                </div>
            </header>

            <?php if ($error !== null): ?>
                <div class="admin-product-create-alert" role="alert">
                    <strong>دسته‌بندی ثبت نشد</strong>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="admin-product-create-alert" role="alert" aria-labelledby="category-create-errors-title">
                    <strong id="category-create-errors-title">اطلاعات واردشده نیاز به اصلاح دارد</strong>
                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="admin-product-create-form" method="post" action="<?= e(url('/admin/categories/create')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <section class="admin-product-create-section" aria-labelledby="category-create-basic-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۱</span>
                        <div>
                            <h2 id="category-create-basic-title">اطلاعات اصلی</h2>
                            <p>نام قابل نمایش و شناسه یکتای دسته‌بندی</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>نام دسته‌بندی <b aria-hidden="true">*</b></span>
                            <input name="name" value="<?= e($data['name']) ?>" maxlength="255" placeholder="مثلاً لوازم جانبی موبایل" autocomplete="off" required>
                            <small>نامی روشن و قابل تشخیص برای این بخش از کاتالوگ انتخاب کنید.</small>
                        </label>
                        <label class="admin-product-create-field">
                            <span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span>
                            <input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" placeholder="mobile-accessories" autocomplete="off" required>
                            <small>برای آدرس دسته‌بندی استفاده می‌شود؛ بهتر است انگلیسی و بدون فاصله باشد.</small>
                        </label>
                        <label class="admin-product-create-field">
                            <span>دسته‌بندی والد</span>
                            <select name="parentId">
                                <option value="">بدون والد (دسته اصلی)</option>
                                <?php foreach ($parents as $parent): ?>
                                    <option value="<?= e((string)$parent->id) ?>" <?= (string)$data['parentId'] === (string)$parent->id ? 'selected' : '' ?>><?= e($parent->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small>در صورت انتخاب، این دسته‌بندی به‌عنوان زیرمجموعه نمایش داده می‌شود.</small>
                        </label>
                    </div>
                </section>

                <section class="admin-product-create-section" aria-labelledby="category-create-description-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۲</span>
                        <div>
                            <h2 id="category-create-description-title">معرفی دسته‌بندی</h2>
                            <p>توضیحی کوتاه درباره محتوای این دسته‌بندی</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>توضیحات</span>
                            <textarea name="description" rows="5" placeholder="کاربر در این دسته‌بندی چه نوع محصولاتی پیدا می‌کند؟"><?= e($data['description']) ?></textarea>
                            <small>اختیاری؛ توضیحی دقیق و مفید برای شناخت بهتر این دسته‌بندی بنویسید.</small>
                        </label>
                    </div>
                </section>

                <section class="admin-product-create-section" aria-labelledby="category-create-publish-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۳</span>
                        <div>
                            <h2 id="category-create-publish-title">وضعیت و ترتیب نمایش</h2>
                            <p>نحوه فعال‌بودن و اولویت نمایش دسته‌بندی در کاتالوگ</p>
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
                    <a href="<?= e(url('/admin/categories')) ?>">انصراف</a>
                    <button type="submit">ایجاد دسته‌بندی <span aria-hidden="true">←</span></button>
                </footer>
            </form>
        </div>
    </main>
</body>

</html>
