<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$productService = new ProductService();
$variantService = new ProductVariantService();
$productValidator = new ProductValidator();
$variantValidator = new ProductVariantValidator();

$productId = (int)Request::query('id', 0);
$product = $productService->adminFindById($productId);

if ($product === null) {
    Response::redirect(url('/admin/products'));
}

$error = null;
$csrfToken = Csrf::token();

if (Request::isPost()) {
    $token = Request::post('csrf_token');

    if (!Csrf::verify(is_string($token) ? $token : null)) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $action = (string)Request::post('action', '');

        try {
            if ($action === 'product_update') {
                $payload = [
                    'brandId' => (int)Request::post('brandId', 0),
                    'productTypeId' => (int)Request::post('productTypeId', 0),
                    'name' => trim((string)Request::post('name', '')),
                    'slug' => trim((string)Request::post('slug', '')),
                    'sku' => trim((string)Request::post('sku', '')),
                    'shortDescription' => trim((string)Request::post('shortDescription', '')),
                    'description' => trim((string)Request::post('description', '')),
                    'status' => trim((string)Request::post('status', 'draft')),
                    'isFeatured' => Request::post('isFeatured') === '1',
                    'sortOrder' => (int)Request::post('sortOrder', 0),
                    'metaTitle' => trim((string)Request::post('metaTitle', '')),
                    'metaDescription' => trim((string)Request::post('metaDescription', ''))
                ];

                $errors = $productValidator->validateAdmin($payload);

                if ($errors !== []) {
                    $error = implode(' ', array_values($errors));
                } elseif ($productService->adminExistsBySlug($payload['slug'], $productId)) {
                    $error = 'اسلاگ محصول تکراری است.';
                } elseif ($productService->adminExistsBySku($payload['sku'], $productId)) {
                    $error = 'SKU محصول تکراری است.';
                } else {
                    $product = $productService->adminUpdate(
                        $productId,
                        $payload['brandId'],
                        $payload['productTypeId'],
                        $payload['name'],
                        $payload['slug'],
                        $payload['sku'],
                        $payload['shortDescription'] !== '' ? $payload['shortDescription'] : null,
                        $payload['description'] !== '' ? $payload['description'] : null,
                        $payload['status'],
                        $payload['isFeatured'],
                        $payload['sortOrder'],
                        $payload['metaTitle'] !== '' ? $payload['metaTitle'] : null,
                        $payload['metaDescription'] !== '' ? $payload['metaDescription'] : null
                    );
                }
            }

            if ($action === 'variant_create' || $action === 'variant_update') {
                $variantId = (int)Request::post('variantId', 0);

                $payload = [
                    'productId' => $productId,
                    'sku' => trim((string)Request::post('sku', '')),
                    'barcode' => trim((string)Request::post('barcode', '')),
                    'price' => trim((string)Request::post('price', '0')),
                    'compareAtPrice' => trim((string)Request::post('compareAtPrice', '')),
                    'currency' => strtoupper(trim((string)Request::post('currency', 'IRR'))),
                    'status' => trim((string)Request::post('status', 'active')),
                    'isDefault' => Request::post('isDefault') === '1',
                    'sortOrder' => (int)Request::post('sortOrder', 0)
                ];

                $errors = $variantValidator->validateAdmin($payload);

                if ($errors !== []) {
                    $error = implode(' ', array_values($errors));
                } elseif (
                    $variantService->adminExistsBySku(
                        $payload['sku'],
                        $action === 'variant_update' ? $variantId : null
                    )
                ) {
                    $error = 'SKU Variant تکراری است.';
                } elseif (
                    $payload['barcode'] !== '' &&
                    $variantService->adminExistsByBarcode(
                        $payload['barcode'],
                        $action === 'variant_update' ? $variantId : null
                    )
                ) {
                    $error = 'Barcode Variant تکراری است.';
                } elseif ($action === 'variant_create') {
                    $variantService->adminCreate(
                        $payload['productId'],
                        $payload['sku'],
                        $payload['barcode'] !== '' ? $payload['barcode'] : null,
                        $payload['price'],
                        $payload['compareAtPrice'] !== '' ? $payload['compareAtPrice'] : null,
                        $payload['currency'],
                        $payload['status'],
                        $payload['isDefault'],
                        $payload['sortOrder']
                    );
                } else {
                    $variantService->adminUpdate(
                        $variantId,
                        $payload['productId'],
                        $payload['sku'],
                        $payload['barcode'] !== '' ? $payload['barcode'] : null,
                        $payload['price'],
                        $payload['compareAtPrice'] !== '' ? $payload['compareAtPrice'] : null,
                        $payload['currency'],
                        $payload['status'],
                        $payload['isDefault'],
                        $payload['sortOrder']
                    );
                }
            }

            if ($action === 'variant_delete') {
                $variantService->adminDelete(
                    (int)Request::post('variantId', 0)
                );
            }

            if ($action === 'variant_restore') {
                $variantService->adminRestore(
                    (int)Request::post('variantId', 0)
                );
            }

            if ($action === 'variant_default') {
                $variantService->adminSetDefault(
                    (int)Request::post('variantId', 0)
                );
            }

            if ($action === 'variant_clear_default') {
                $variantService->adminClearDefault(
                    (int)Request::post('variantId', 0)
                );
            }

            if ($action === 'variant_status') {
                $variantService->adminSetStatus(
                    (int)Request::post('variantId', 0),
                    trim((string)Request::post('status', 'inactive'))
                );
            }

            if ($error === null) {
                Response::redirect(
                    url('/admin/products/edit?id=' . $productId)
                );
            }
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$product = $productService->adminFindById($productId);
$variants = $variantService->adminFindAllByProductIncludingDeleted($productId);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ویرایش محصول | نیواد</title>
</head>

<body>
    <main>
        <a href="<?= e(url('/admin/products')) ?>">بازگشت</a>
        <h1>ویرایش محصول</h1>
        <?php if ($error !== null): ?>
            <div role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="action" value="product_update">
            <label>شناسه برند<input name="brandId" type="number" min="1" value="<?= e($product['brand_id']) ?>" required></label>
            <label>شناسه نوع محصول<input name="productTypeId" type="number" min="1" value="<?= e($product['product_type_id']) ?>" required></label>
            <label>نام<input name="name" value="<?= e($product['name']) ?>" required></label>
            <label>Slug<input name="slug" value="<?= e($product['slug']) ?>" required></label>
            <label>SKU<input name="sku" value="<?= e($product['sku']) ?>" required></label>
            <label>توضیح کوتاه<textarea name="shortDescription"><?= e((string)$product['short_description']) ?></textarea></label>
            <label>توضیحات<textarea name="description"><?= e((string)$product['description']) ?></textarea></label>
            <label>وضعیت<select name="status">
                    <option value="draft" <?= $product['status'] === 'draft' ? ' selected' : '' ?>>پیش‌نویس</option>
                    <option value="active" <?= $product['status'] === 'active' ? ' selected' : '' ?>>فعال</option>
                    <option value="out_of_stock" <?= $product['status'] === 'out_of_stock' ? ' selected' : '' ?>>ناموجود</option>
                    <option value="discontinued" <?= $product['status'] === 'discontinued' ? ' selected' : '' ?>>متوقف</option>
                    <option value="archived" <?= $product['status'] === 'archived' ? ' selected' : '' ?>>آرشیو</option>
                </select></label>
            <label>ویژه<input name="isFeatured" type="checkbox" value="1" <?= (bool)$product['is_featured'] ? ' checked' : '' ?>></label>
            <label>ترتیب<input name="sortOrder" type="number" min="0" value="<?= e($product['sort_order']) ?>"></label>
            <label>Meta Title<input name="metaTitle" value="<?= e((string)$product['meta_title']) ?>"></label>
            <label>Meta Description<textarea name="metaDescription"><?= e((string)$product['meta_description']) ?></textarea></label>
            <button type="submit">ذخیره محصول</button>
        </form>

        <section>
            <h2>Variantها</h2>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="variant_create">
                <label>SKU<input name="sku" required></label>
                <label>Barcode<input name="barcode"></label>
                <label>قیمت<input name="price" value="0"></label>
                <label>قیمت قبل<input name="compareAtPrice"></label>
                <label>Currency<input name="currency" value="IRR" maxlength="3"></label>
                <label>وضعیت<select name="status">
                        <option value="active">فعال</option>
                        <option value="inactive">غیرفعال</option>
                        <option value="out_of_stock">ناموجود</option>
                        <option value="discontinued">متوقف</option>
                    </select></label>
                <label>پیش‌فرض<input name="isDefault" type="checkbox" value="1"></label>
                <label>ترتیب<input name="sortOrder" type="number" min="0" value="0"></label>
                <button type="submit">افزودن Variant</button>
            </form>

            <?php foreach ($variants as $variant): ?>
                <article>
                    <p>ID: <?= e($variant['id']) ?></p>
                    <p>SKU: <?= e($variant['sku']) ?></p>
                    <p>قیمت: <?= e($variant['price']) ?></p>
                    <p>وضعیت: <?= e($variant['status']) ?></p>
                    <p>پیش‌فرض: <?= (int)$variant['is_default'] === 1 ? 'بله' : 'خیر' ?></p>
                    <p>وضعیت حذف: <?= $variant['deleted_at'] === null ? 'فعال' : 'حذف‌شده' ?></p>

                    <?php if ($variant['deleted_at'] === null): ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="variant_update">
                            <input type="hidden" name="variantId" value="<?= e($variant['id']) ?>">
                            <input type="hidden" name="sku" value="<?= e($variant['sku']) ?>">
                            <input type="hidden" name="barcode" value="<?= e((string)$variant['barcode']) ?>">
                            <input type="hidden" name="price" value="<?= e($variant['price']) ?>">
                            <input type="hidden" name="compareAtPrice" value="<?= e((string)$variant['compare_at_price']) ?>">
                            <input type="hidden" name="currency" value="<?= e($variant['currency']) ?>">
                            <input type="hidden" name="status" value="<?= e($variant['status']) ?>">
                            <input type="hidden" name="sortOrder" value="<?= e($variant['sort_order']) ?>">
                            <input type="hidden" name="isDefault" value="<?= (int)$variant['is_default'] === 1 ? '1' : '0' ?>">
                            <button type="submit">ذخیره Variant</button>
                        </form>

                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="variant_delete">
                            <input type="hidden" name="variantId" value="<?= e($variant['id']) ?>">
                            <button type="submit">حذف</button>
                        </form>

                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="variant_default">
                            <input type="hidden" name="variantId" value="<?= e($variant['id']) ?>">
                            <button type="submit">پیش‌فرض</button>
                        </form>

                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="variant_clear_default">
                            <input type="hidden" name="variantId" value="<?= e($variant['id']) ?>">
                            <button type="submit">لغو پیش‌فرض</button>
                        </form>

                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="variant_status">
                            <input type="hidden" name="variantId" value="<?= e($variant['id']) ?>">
                            <input type="hidden" name="status" value="<?= $variant['status'] === 'active' ? 'inactive' : 'active' ?>">
                            <button type="submit"><?= $variant['status'] === 'active' ? 'غیرفعال کردن' : 'فعال کردن' ?></button>
                        </form>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="variant_restore">
                            <input type="hidden" name="variantId" value="<?= e($variant['id']) ?>">
                            <button type="submit">Restore</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>

</html>