<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';
$service = new SpecGroupService();
$validator = new SpecGroupValidator();
$error = null;
if (Request::isPost()) {
    if (!Csrf::verify(Request::post('csrf_token'))) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $action = (string)Request::post('action', 'create');
        if ($action === 'create') {
            $data = ['name' => Request::post('name'), 'slug' => Request::post('slug'), 'status' => Request::post('status'), 'sortOrder' => Request::post('sortOrder')];
            $errors = $validator->validate($data);
            if ($errors !== []) {
                $error = implode(' ', array_values($errors));
            } else {
                $service->create(trim((string)$data['name']), trim((string)$data['slug']), trim((string)$data['status']), (int)$data['sortOrder']);
                Response::redirect(url('/admin/specifications/groups'));
            }
        }
        if ($action === 'delete') {
            $service->delete((int)Request::post('id'));
            Response::redirect(url('/admin/specifications/groups'));
        }
        if ($action === 'restore') {
            $service->restore((int)Request::post('id'));
            Response::redirect(url('/admin/specifications/groups'));
        }
        if ($action === 'status') {
            $service->setStatus((int)Request::post('id'), trim((string)Request::post('status')));
            Response::redirect(url('/admin/specifications/groups'));
        }
    }
}
$groups = $service->findAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>گروه مشخصات | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>
    <main>
        <h1>گروه مشخصات</h1>
        <?php if ($error !== null): ?><div role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="action" value="create">
            <input name="name" placeholder="نام" required>
            <input name="slug" placeholder="اسلاگ" required>
            <select name="status">
                <option value="active">active</option>
                <option value="inactive">inactive</option>
            </select>
            <input name="sortOrder" type="number" min="0" value="0">
            <button type="submit">ایجاد</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>نام</th>
                    <th>اسلاگ</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?= e($group->name) ?></td>
                        <td><?= e($group->slug) ?></td>
                        <td><?= e($group->status) ?></td>
                        <td><a href="<?= e(url('/admin/specifications/index?group_id=' . $group->id)) ?>">مشخصات</a>
                            <form method="post"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><input type="hidden" name="id" value="<?= e((string)$group->id) ?>"><input type="hidden" name="action" value="<?= $group->deletedAt === null ? 'delete' : 'restore' ?>"><button type="submit"><?= $group->deletedAt === null ? 'حذف' : 'بازگردانی' ?></button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>