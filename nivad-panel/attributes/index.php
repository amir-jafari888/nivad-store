<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';
$service = new ProductAttributeService();
$attributes = $service->findAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ویژگی‌ها | نیواد</title>
</head>

<body>
    <header><a href="<?= e(url('/admin')) ?>">نیواد</a><a href="<?= e(url('/admin/attributes/create')) ?>">ایجاد ویژگی</a></header>
    <main>
        <h1>ویژگی‌ها</h1>
        <table>
            <thead>
                <tr>
                    <th>نام</th>
                    <th>اسلاگ</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attributes as $attribute): ?>
                    <tr>
                        <td><?= e($attribute->name) ?></td>
                        <td><?= e($attribute->slug) ?></td>
                        <td><?= e($attribute->type) ?></td>
                        <td><?= e($attribute->status) ?></td>
                        <td><a href="<?= e(url('/admin/attributes/edit?id=' . $attribute->id)) ?>">ویرایش</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>