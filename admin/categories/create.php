<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$service = new CategoryService();
$error = null;
$errors = [];
$data = [
    'parentId' => '',
    'name' => '',
    'slug' => '',
    'description' => '',
    'status' => 'active',
    'sortOrder' => 0,
];

if (Request::isPost()) {
    if (!Csrf::verify((string)Request::post('csrf_token', ''))) {
        $error = 'درخواست نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        $data['parentId'] = Request::post('parentId', '');
        $data['name'] = trim((string)Request::post('name', ''));
        $data['slug'] = trim((string)Request::post('slug', ''));
        $data['description'] = (string)Request::post('description', '');
        $data['status'] = (string)Request::post('status', 'active');
        $data['sortOrder'] = (int)Request::post('sortOrder', 0);
        $data['parentId'] = $data['parentId'] === '' ? null : (int)$data['parentId'];

        $errors = (new CategoryValidator())->validate($data);

        if ($errors === [] && $service->findBySlug($data['slug']) !== null) {
            $errors['slug'] = 'اسلاگ دسته‌بندی تکراری است.';
        }

        if ($errors === [] && $data['parentId'] !== null && !$service->exists($data['parentId'])) {
            $errors['parentId'] = 'دسته‌بندی والد نامعتبر است.';
        }

        if ($errors === []) {
            $service->create(
                $data['parentId'],
                $data['name'],
                $data['slug'],
                $data['description'] === '' ? null : $data['description'],
                $data['status'],
                $data['sortOrder']
            );
            Response::redirect(url('/admin/categories'));
        }
    }
}

$parents = $service->findActive();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن دسته‌بندی | نیواد</title>
</head>

<body>
    <main class="admin-category-create-page">
        <div class="admin-category-create-content">
            <header class="admin-category-create-heading">
                <div>
                    <a class="admin-category-create-back" href="<?= e(url('/admin/categories')) ?>">← بازگشت به فهرست دسته‌بندی‌ها</a>
                    <p class="admin-category-create-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>افزودن دسته‌بندی جدید</h1>
                    <p>مشخصات دسته‌بندی را وارد کنید؛ ساختار والد و ترتیب نمایش را هم همین‌جا تنظیم کنید.</p>
                </div>
            </header>

            <?php if ($error !== null): ?>
                <div class="admin-category-create-alert" role="alert">
                    <strong>دسته‌بندی ثبت نشد</strong>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="admin-category-create-alert" role="alert" aria-labelledby="category-create-errors-title">
                    <strong id="category-create-errors-title">بعضی از اطلاعات نیاز به اصلاح دارند.</strong>
                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="admin-category-create-form" method="post" action="<?= e(url('/admin/categories/create')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

                <section class="admin-category-create-section" aria-labelledby="category-create-basic-title">
                    <div class="admin-category-create-section-heading">
                        <span class="admin-category-create-step">۱</span>
                        <div>
                            <h2 id="category-create-basic-title">اطلاعات اصلی</h2>
                            <p>نام، شناسه یکتا و جایگاه دسته‌بندی در ساختار کاتالوگ</p>
                        </div>
                    </div>
                    <div class="admin-category-create-fields">
                        <label class="admin-category-create-field admin-category-create-field-wide">
                            <span>نام دسته‌بندی <b aria-hidden="true">*</b></span>
                            <input name="name" value="<?= e($data['name']) ?>" maxlength="255" placeholder="مثلاً لوازم جانبی دیجیتال" autocomplete="off" required <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="category-name-error"' : '' ?>>
                            <small>نامی روشن و قابل تشخیص برای دسته‌بندی انتخاب کنید.</small>
                            <?php if (isset($errors['name'])): ?><small class="admin-category-create-field-error" id="category-name-error"><?= e($errors['name']) ?></small><?php endif; ?>
                        </label>
                        <label class="admin-category-create-field">
                            <span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span>
                            <input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" placeholder="digital-accessories" autocomplete="off" required <?= isset($errors['slug']) ? 'aria-invalid="true" aria-describedby="category-slug-error"' : '' ?>>
                            <small>برای آدرس دسته‌بندی استفاده می‌شود؛ بهتر است انگلیسی و بدون فاصله باشد.</small>
                            <?php if (isset($errors['slug'])): ?><small class="admin-category-create-field-error" id="category-slug-error"><?= e($errors['slug']) ?></small><?php endif; ?>
                        </label>
                        <label class="admin-category-create-field">
                            <span>دسته‌بندی والد</span>
                            <select name="parentId" <?= isset($errors['parentId']) ? 'aria-invalid="true" aria-describedby="category-parent-error"' : '' ?>>
                                <option value="">بدون والد (دسته‌بندی اصلی)</option>
                                <?php foreach ($parents as $parent): ?>
                                    <option value="<?= e((string)$parent->id) ?>" <?= (string)$data['parentId'] === (string)$parent->id ? 'selected' : '' ?>><?= e($parent->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small>در صورت نیاز، این دسته‌بندی را زیرمجموعه یک دسته‌بندی فعال قرار دهید.</small>
                            <?php if (isset($errors['parentId'])): ?><small class="admin-category-create-field-error" id="category-parent-error"><?= e($errors['parentId']) ?></small><?php endif; ?>
                        </label>
                    </div>
                </section>

                <section class="admin-category-create-section" aria-labelledby="category-create-description-title">
                    <div class="admin-category-create-section-heading">
                        <span class="admin-category-create-step">۲</span>
                        <div>
                            <h2 id="category-create-description-title">معرفی دسته‌بندی</h2>
                            <p>توضیحی کوتاه و کاربردی درباره محتوای این دسته‌بندی</p>
                        </div>
                    </div>
                    <div class="admin-category-create-fields">
                        <label class="admin-category-create-field admin-category-create-field-wide">
                            <span>توضیحات</span>
                            <textarea name="description" rows="6" placeholder="محصولاتی که در این دسته‌بندی قرار می‌گیرند را توضیح دهید."><?= e((string)$data['description']) ?></textarea>
                            <small>اختیاری؛ این توضیح به شناخت بهتر موضوع دسته‌بندی کمک می‌کند.</small>
                        </label>
                    </div>
                </section>

                <section class="admin-category-create-section" aria-labelledby="category-create-publish-title">
                    <div class="admin-category-create-section-heading">
                        <span class="admin-category-create-step">۳</span>
                        <div>
                            <h2 id="category-create-publish-title">انتشار و نمایش</h2>
                            <p>وضعیت اولیه و اولویت نمایش دسته‌بندی را مشخص کنید.</p>
                        </div>
                    </div>
                    <div class="admin-category-create-fields">
                        <label class="admin-category-create-field">
                            <span>وضعیت اولیه</span>
                            <select name="status" <?= isset($errors['status']) ? 'aria-invalid="true" aria-describedby="category-status-error"' : '' ?>>
                                <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                                <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                            </select>
                            <?php if (isset($errors['status'])): ?><small class="admin-category-create-field-error" id="category-status-error"><?= e($errors['status']) ?></small><?php endif; ?>
                        </label>
                        <label class="admin-category-create-field">
                            <span>اولویت نمایش</span>
                            <input name="sortOrder" type="number" min="0" step="1" value="<?= e((string)$data['sortOrder']) ?>" <?= isset($errors['sortOrder']) ? 'aria-invalid="true" aria-describedby="category-sort-order-error"' : '' ?>>
                            <small>عدد کمتر، اولویت بالاتر؛ مقدار پیش‌فرض صفر است.</small>
                            <?php if (isset($errors['sortOrder'])): ?><small class="admin-category-create-field-error" id="category-sort-order-error"><?= e($errors['sortOrder']) ?></small><?php endif; ?>
                        </label>
                    </div>
                </section>

                <footer class="admin-category-create-actions">
                    <a href="<?= e(url('/admin/categories')) ?>">انصراف</a>
                    <button type="submit">ایجاد دسته‌بندی <span aria-hidden="true">←</span></button>
                </footer>
            </form>
        </div>
    </main>
</body>

</html>
