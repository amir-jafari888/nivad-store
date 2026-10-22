<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';
$service = new CategoryService();
$categories = $service->findAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>دسته‌بندی‌ها | نیواد</title>
</head>

<body>
    <header><a href="<?= e(url('/admin')) ?>">نیواد</a><span><?= e((string)($currentAdmin['name'] ?? $currentAdmin['email'] ?? 'مدیر')) ?></span><a href="<?= e(url('/admin/categories/create')) ?>">افزودن دسته‌بندی</a></header>
    <main>
        <h1>دسته‌بندی‌ها</h1>
        <?php if ($categories === []): ?><p>دسته‌بندی‌ای ثبت نشده است.</p>
        <?php else: ?><table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام</th>
                        <th>Slug</th>
                        <th>وضعیت</th>
                        <th>والد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?><tr>
                            <td><?= e($category->id) ?></td>
                            <td><?= e($category->name) ?></td>
                            <td><?= e($category->slug) ?></td>
                            <td><?= e($category->status) ?></td>
                            <td><?= e($category->parentId ?? '-') ?></td>
                            <td><a href="<?= e(url('/admin/categories/edit?id=' . $category->id)) ?>">ویرایش</a></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table><?php endif; ?>
    </main>
</body>

</html>