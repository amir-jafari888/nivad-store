<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$productService = new ProductService();
$brandService = new BrandService();
$productTypeService = new ProductTypeService();
$validator = new ProductValidator();
$brands = $brandService->findActive();
$productTypes = $productTypeService->findActive();
$error = null;
$csrfToken = Csrf::token();

$data = [
    'brandId' => '',
    'productTypeId' => '',
    'name' => '',
    'slug' => '',
    'sku' => '',
    'shortDescription' => '',
    'description' => '',
    'status' => 'draft',
    'isFeatured' => false,
    'sortOrder' => '0',
    'metaTitle' => '',
    'metaDescription' => ''
];

if (Request::isPost()) {
    $token = Request::post('csrf_token');

    if (!Csrf::verify(is_string($token) ? $token : null)) {
        $error = 'درخواست نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        $data['brandId'] = (string)Request::post('brandId', '');
        $data['productTypeId'] = (string)Request::post('productTypeId', '');
        $data['name'] = trim((string)Request::post('name', ''));
        $data['slug'] = trim((string)Request::post('slug', ''));
        $data['sku'] = trim((string)Request::post('sku', ''));
        $data['shortDescription'] = trim((string)Request::post('shortDescription', ''));
        $data['description'] = trim((string)Request::post('description', ''));
        $data['status'] = trim((string)Request::post('status', 'draft'));
        $data['isFeatured'] = Request::post('isFeatured') === '1';
        $data['sortOrder'] = (string)Request::post('sortOrder', '0');
        $data['metaTitle'] = trim((string)Request::post('metaTitle', ''));
        $data['metaDescription'] = trim((string)Request::post('metaDescription', ''));

        $payload = [
            'brandId' => (int)$data['brandId'],
            'productTypeId' => (int)$data['productTypeId'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'sku' => $data['sku'],
            'shortDescription' => $data['shortDescription'] !== '' ? $data['shortDescription'] : null,
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'status' => $data['status'],
            'isFeatured' => $data['isFeatured'],
            'sortOrder' => (int)$data['sortOrder'],
            'metaTitle' => $data['metaTitle'] !== '' ? $data['metaTitle'] : null,
            'metaDescription' => $data['metaDescription'] !== '' ? $data['metaDescription'] : null
        ];

        $errors = $validator->validateAdmin($payload);

        if ($errors !== []) {
            $error = implode(' ', array_values($errors));
        } elseif ($brandService->findActiveById($payload['brandId']) === null) {
            $error = 'برند انتخاب‌شده معتبر یا فعال نیست.';
        } elseif (!array_filter($productTypes, static fn($type): bool => $type->id === $payload['productTypeId'])) {
            $error = 'نوع محصول انتخاب‌شده معتبر نیست.';
        } elseif ($productService->adminExistsBySlug($payload['slug'])) {
            $error = 'اسلاگ محصول تکراری است.';
        } elseif ($productService->adminExistsBySku($payload['sku'])) {
            $error = 'SKU محصول تکراری است.';
        } else {
            try {
                $product = $productService->adminCreate(
                    $payload['brandId'],
                    $payload['productTypeId'],
                    $payload['name'],
                    $payload['slug'],
                    $payload['sku'],
                    $payload['shortDescription'],
                    $payload['description'],
                    $payload['status'],
                    $payload['isFeatured'],
                    $payload['sortOrder'],
                    $payload['metaTitle'],
                    $payload['metaDescription']
                );

                Response::redirect(url('/admin/products/edit?id=' . $product['id']));
            } catch (Throwable $exception) {
                $error = 'ثبت محصول انجام نشد. اطلاعات را بررسی کنید و دوباره تلاش کنید.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>افزودن محصول | نیواد</title>
</head>

<body>
    <main class="admin-product-create-page">
        <div class="admin-product-create-content">
            <header class="admin-product-create-heading">
                <div>
                    <a class="admin-product-create-back" href="<?= e(url('/admin/products')) ?>">← بازگشت به فهرست محصولات</a>
                    <p class="admin-product-create-eyebrow">کاتالوگ فروشگاه</p>
                    <h1>افزودن محصول جدید</h1>
                    <p>اطلاعات پایه را وارد کنید؛ جزئیات تکمیلی و سئو را هم می‌توانید همین‌جا تنظیم کنید.</p>
                </div>
            </header>

            <?php if ($error !== null): ?>
                <div class="admin-product-create-alert" role="alert">
                    <strong>محصول ثبت نشد</strong>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($brands === [] || $productTypes === []): ?>
                <aside class="admin-product-create-prerequisite" role="status">
                    <strong>برای ثبت محصول، اطلاعات پایه کاتالوگ لازم است.</strong>
                    <p>ابتدا حداقل یک برند فعال و یک نوع محصول ایجاد کنید؛ سپس به این فرم برگردید.</p>
                    <div>
                        <?php if ($brands === []): ?><a href="<?= e(url('/admin/brands')) ?>">مدیریت برندها</a><?php endif; ?>
                        <?php if ($productTypes === []): ?><a href="<?= e(url('/admin/product-types')) ?>">مدیریت انواع محصول</a><?php endif; ?>
                    </div>
                </aside>
            <?php endif; ?>

            <form class="admin-product-create-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <section class="admin-product-create-section" aria-labelledby="product-create-basic-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۱</span>
                        <div>
                            <h2 id="product-create-basic-title">اطلاعات اصلی</h2>
                            <p>مشخصات شناسایی محصول و جایگاه آن در کاتالوگ</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>نام محصول <b aria-hidden="true">*</b></span>
                            <input name="name" value="<?= e($data['name']) ?>" maxlength="255" placeholder="مثلاً هدفون بی‌سیم مدل X" autocomplete="off" required>
                            <small>نامی واضح و قابل تشخیص برای محصول انتخاب کنید.</small>
                        </label>
                        <label class="admin-product-create-field">
                            <span>برند <b aria-hidden="true">*</b></span>
                            <select name="brandId" required <?= $brands === [] ? 'disabled' : '' ?>>
                                <option value="">انتخاب برند</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?= e((string)$brand->id) ?>" <?= $data['brandId'] === (string)$brand->id ? 'selected' : '' ?>><?= e($brand->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="admin-product-create-field">
                            <span>نوع محصول <b aria-hidden="true">*</b></span>
                            <select name="productTypeId" required <?= $productTypes === [] ? 'disabled' : '' ?>>
                                <option value="">انتخاب نوع محصول</option>
                                <?php foreach ($productTypes as $productType): ?>
                                    <option value="<?= e((string)$productType->id) ?>" <?= $data['productTypeId'] === (string)$productType->id ? 'selected' : '' ?>><?= e($productType->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="admin-product-create-field">
                            <span>شناسه یکتا (Slug) <b aria-hidden="true">*</b></span>
                            <input name="slug" value="<?= e($data['slug']) ?>" dir="ltr" placeholder="wireless-headphones-x" autocomplete="off" required>
                            <small>برای آدرس محصول استفاده می‌شود؛ بهتر است انگلیسی و بدون فاصله باشد.</small>
                        </label>
                        <label class="admin-product-create-field">
                            <span>کد محصول (SKU) <b aria-hidden="true">*</b></span>
                            <input name="sku" value="<?= e($data['sku']) ?>" dir="ltr" maxlength="100" placeholder="WH-X-001" autocomplete="off" required>
                            <small>کد داخلی منحصربه‌فرد محصول.</small>
                        </label>
                    </div>
                </section>

                <section class="admin-product-create-section" aria-labelledby="product-create-description-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۲</span>
                        <div>
                            <h2 id="product-create-description-title">معرفی محصول</h2>
                            <p>متنی کوتاه برای معرفی سریع و توضیح کامل‌تر برای صفحه محصول</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>توضیح کوتاه</span>
                            <textarea name="shortDescription" rows="3" maxlength="500" placeholder="در یک یا دو جمله، مهم‌ترین ویژگی محصول را بنویسید."><?= e($data['shortDescription']) ?></textarea>
                            <small>اختیاری؛ متن مختصر و کاربردی بنویسید.</small>
                        </label>
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>توضیحات کامل</span>
                            <textarea name="description" rows="6" placeholder="جزئیات، کاربردها و ویژگی‌های محصول را توضیح دهید."><?= e($data['description']) ?></textarea>
                        </label>
                    </div>
                </section>

                <section class="admin-product-create-section" aria-labelledby="product-create-publish-title">
                    <div class="admin-product-create-section-heading">
                        <span class="admin-product-create-step">۳</span>
                        <div>
                            <h2 id="product-create-publish-title">انتشار و نمایش</h2>
                            <p>وضعیت اولیه و ترتیب نمایش محصول را مشخص کنید.</p>
                        </div>
                    </div>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field">
                            <span>وضعیت اولیه</span>
                            <select name="status">
                                <option value="draft" <?= $data['status'] === 'draft' ? 'selected' : '' ?>>پیش‌نویس (پیشنهادی)</option>
                                <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                                <option value="out_of_stock" <?= $data['status'] === 'out_of_stock' ? 'selected' : '' ?>>ناموجود</option>
                                <option value="discontinued" <?= $data['status'] === 'discontinued' ? 'selected' : '' ?>>متوقف</option>
                                <option value="archived" <?= $data['status'] === 'archived' ? 'selected' : '' ?>>آرشیو</option>
                            </select>
                        </label>
                        <label class="admin-product-create-field">
                            <span>اولویت نمایش</span>
                            <input name="sortOrder" type="number" min="0" step="1" value="<?= e($data['sortOrder']) ?>">
                            <small>عدد کمتر، اولویت بالاتر؛ مقدار پیش‌فرض صفر است.</small>
                        </label>
                        <label class="admin-product-create-featured">
                            <input name="isFeatured" type="checkbox" value="1" <?= $data['isFeatured'] ? 'checked' : '' ?>>
                            <span><strong>محصول ویژه</strong><small>این محصول به‌عنوان محصول ویژه علامت‌گذاری شود.</small></span>
                        </label>
                    </div>
                </section>

                <details class="admin-product-create-seo">
                    <summary>
                        <span><strong>تنظیمات سئو</strong><small>اختیاری؛ برای عنوان و توضیحات موتورهای جست‌وجو</small></span>
                        <span aria-hidden="true">＋</span>
                    </summary>
                    <div class="admin-product-create-fields">
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>عنوان سئو (Meta Title)</span>
                            <input name="metaTitle" value="<?= e($data['metaTitle']) ?>" maxlength="255" placeholder="عنوان مناسب برای نتایج جست‌وجو">
                        </label>
                        <label class="admin-product-create-field admin-product-create-field-wide">
                            <span>توضیحات سئو (Meta Description)</span>
                            <textarea name="metaDescription" rows="3" maxlength="500" placeholder="خلاصه‌ای دقیق از محتوای این صفحه برای موتورهای جست‌وجو."><?= e($data['metaDescription']) ?></textarea>
                        </label>
                    </div>
                </details>

                <footer class="admin-product-create-actions">
                    <a href="<?= e(url('/admin/products')) ?>">انصراف</a>
                    <button type="submit" <?= ($brands === [] || $productTypes === []) ? 'disabled' : '' ?>>ایجاد محصول و ادامه ویرایش <span aria-hidden="true">←</span></button>
                </footer>
            </form>
        </div>
    </main>
</body>

</html>
