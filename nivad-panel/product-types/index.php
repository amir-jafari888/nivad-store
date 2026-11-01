<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';
$service = new ProductTypeService();
$items = $service->findAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>انواع محصول | نیواد</title>
</head>

<body>
    <header><a href="<?= e(url('/admin')) ?>">نیواد</a><span><?= e((string)($currentAdmin['name'] ?? $currentAdmin['email'] ?? 'مدیر')) ?></span><a href="<?= e(url('/admin/product-types/create')) ?>">افزودن نوع محصول</a></header>
    <main>
        <h1>انواع محصول</h1>
        <?php if ($items === []): ?><p>نوع محصولی ثبت نشده است.</p>
        <?php else: ?><table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام</th>
                        <th>Slug</th>
                        <th>وضعیت</th>
                        <th>ترتیب</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?><tr>
                            <td><?= e($item->id) ?></td>
                            <td><?= e($item->name) ?></td>
                            <td><?= e($item->slug) ?></td>
                            <td><?= e($item->status) ?></td>
                            <td><?= e($item->sortOrder) ?></td>
                            <td><a href="<?= e(url('/admin/product-types/edit?id=' . $item->id)) ?>">ویرایش</a></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table><?php endif; ?>
    </main>
</body>

</html>