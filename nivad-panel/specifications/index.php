<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';
$service = new SpecificationService();
$groupService = new SpecGroupService();
$groupId = (int)Request::query('group_id', 0);
$specifications = $groupId > 0 ? $service->findAllByGroup($groupId) : $service->findAll();
$groups = $groupService->findActive();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>مشخصات | نیواد</title>
</head>

<body>
    <main>
        <h1>مشخصات</h1>
        <a href="<?= e(url('/admin/specifications/create')) ?>">ایجاد مشخصات</a>
        <a href="<?= e(url('/admin/specifications/groups')) ?>">گروه‌ها</a>
        <table>
            <thead>
                <tr>
                    <th>نام</th>
                    <th>گروه</th>
                    <th>نوع مقدار</th>
                    <th>واحد</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($specifications as $specification): ?>
                    <tr>
                        <td><?= e($specification->name) ?></td>
                        <td><?= e((string)$specification->specGroupId) ?></td>
                        <td><?= e($specification->valueType) ?></td>
                        <td><?= e((string)$specification->unit) ?></td>
                        <td><?= e($specification->status) ?></td>
                        <td><a href="<?= e(url('/admin/specifications/edit?id=' . $specification->id)) ?>">ویرایش</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>