<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$productService = new ProductService();
$products = $productService->adminFindAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>محصولات | نیواد</title>
</head>

<body>
    <header>
        <a href="<?= e(url('/admin')) ?>">نیواد</a>
        <span><?= e((string)($currentAdmin['name'] ?? $currentAdmin['email'] ?? 'مدیر')) ?></span>
        <a href="<?= e(url('/admin/products/create')) ?>">افزودن محصول</a>
    </header>
    <main>
        <h1>محصولات</h1>
        <?php if ($products === []): ?>
            <p>محصولی ثبت نشده است.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام</th>
                        <th>SKU</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= e($product['id']) ?></td>
                            <td><?= e($product['name']) ?></td>
                            <td><?= e($product['sku']) ?></td>
                            <td><?= e($product['status']) ?></td>
                            <td><a href="<?= e(url('/admin/products/edit?id=' . $product['id'])) ?>">ویرایش</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>