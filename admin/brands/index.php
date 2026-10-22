<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';
$service = new BrandService();
$brands = $service->findAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>برندها | نیواد</title>
</head>

<body>
    <header><a href="<?= e(url('/admin')) ?>">نیواد</a><span><?= e((string)($currentAdmin['name'] ?? $currentAdmin['email'] ?? 'مدیر')) ?></span><a href="<?= e(url('/admin/brands/create')) ?>">افزودن برند</a></header>
    <main>
        <h1>برندها</h1>
        <?php if ($brands === []): ?><p>برندی ثبت نشده است.</p>
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
                    <?php foreach ($brands as $brand): ?><tr>
                            <td><?= e($brand->id) ?></td>
                            <td><?= e($brand->name) ?></td>
                            <td><?= e($brand->slug) ?></td>
                            <td><?= e($brand->status) ?></td>
                            <td><?= e($brand->sortOrder) ?></td>
                            <td><a href="<?= e(url('/admin/brands/edit?id=' . $brand->id)) ?>">ویرایش</a></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table><?php endif; ?>
    </main>
</body>

</html>