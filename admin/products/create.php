<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$productService = new ProductService();
$validator = new ProductValidator();
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
        $error = 'درخواست نامعتبر است.';
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

                Response::redirect(
                    url('/admin/products/edit?id=' . $product['id'])
                );
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
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
    <main>
        <a href="<?= e(url('/admin/products')) ?>">بازگشت</a>
        <h1>افزودن محصول</h1>
        <?php if ($error !== null): ?>
            <div role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <label>شناسه برند<input name="brandId" type="number" min="1" value="<?= e($data['brandId']) ?>" required></label>
            <label>شناسه نوع محصول<input name="productTypeId" type="number" min="1" value="<?= e($data['productTypeId']) ?>" required></label>
            <label>نام<input name="name" value="<?= e($data['name']) ?>" required></label>
            <label>Slug<input name="slug" value="<?= e($data['slug']) ?>" required></label>
            <label>SKU<input name="sku" value="<?= e($data['sku']) ?>" required></label>
            <label>توضیح کوتاه<textarea name="shortDescription"><?= e($data['shortDescription']) ?></textarea></label>
            <label>توضیحات<textarea name="description"><?= e($data['description']) ?></textarea></label>
            <label>وضعیت<select name="status">
                    <option value="draft" <?= $data['status'] === 'draft' ? ' selected' : '' ?>>پیش‌نویس</option>
                    <option value="active" <?= $data['status'] === 'active' ? ' selected' : '' ?>>فعال</option>
                    <option value="out_of_stock" <?= $data['status'] === 'out_of_stock' ? ' selected' : '' ?>>ناموجود</option>
                    <option value="discontinued" <?= $data['status'] === 'discontinued' ? ' selected' : '' ?>>متوقف</option>
                    <option value="archived" <?= $data['status'] === 'archived' ? ' selected' : '' ?>>آرشیو</option>
                </select></label>
            <label>ویژه<input name="isFeatured" type="checkbox" value="1" <?= $data['isFeatured'] ? ' checked' : '' ?>></label>
            <label>ترتیب<input name="sortOrder" type="number" min="0" value="<?= e($data['sortOrder']) ?>"></label>
            <label>Meta Title<input name="metaTitle" value="<?= e($data['metaTitle']) ?>"></label>
            <label>Meta Description<textarea name="metaDescription"><?= e($data['metaDescription']) ?></textarea></label>
            <button type="submit">ایجاد محصول</button>
        </form>
    </main>
</body>

</html>