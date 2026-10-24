<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';

$id = (int)Request::query('id', 0);
$service = new SpecificationService();
$groupService = new SpecGroupService();
$validator = new SpecificationValidator();

$specification = $service->findById($id);

if ($specification === null) {
    Response::redirect(url('/admin/specifications'));
}

$groups = $groupService->findActive();
$error = null;

if (Request::isPost()) {
    if (!Csrf::verify(Request::post('csrf_token'))) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $action = (string)Request::post('action', 'update');

        if ($action === 'update') {
            $data = [
                'specGroupId' => Request::post('specGroupId'),
                'name' => Request::post('name'),
                'slug' => Request::post('slug'),
                'valueType' => Request::post('valueType'),
                'unit' => Request::post('unit'),
                'sortOrder' => Request::post('sortOrder'),
                'status' => Request::post('status')
            ];

            $errors = $validator->validate($data);

            if ($errors !== []) {
                $error = implode(' ', array_values($errors));
            } else {
                $service->update(
                    $id,
                    (int)$data['specGroupId'],
                    trim((string)$data['name']),
                    trim((string)$data['slug']),
                    trim((string)$data['valueType']),
                    $data['unit'] !== null ? trim((string)$data['unit']) : null,
                    (int)$data['sortOrder'],
                    trim((string)$data['status'])
                );

                Response::redirect(url('/admin/specifications/edit?id=' . $id));
            }
        }

        if ($action === 'delete') {
            $service->delete($id);
            Response::redirect(url('/admin/specifications'));
        }

        if ($action === 'restore') {
            $service->restore($id);
            Response::redirect(url('/admin/specifications/edit?id=' . $id));
        }

        if ($action === 'status') {
            $service->setStatus(
                $id,
                trim((string)Request::post('status'))
            );
            Response::redirect(url('/admin/specifications/edit?id=' . $id));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ویرایش مشخصات | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>
    <main>
        <h1>ویرایش مشخصات</h1>
        <?php if ($error !== null): ?>
            <div role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="action" value="update">
            <label>گروه مشخصات
                <select name="specGroupId" required>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= e((string)$group->id) ?>" <?= $specification->specGroupId === $group->id ? ' selected' : '' ?>><?= e($group->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>نام
                <input name="name" value="<?= e($specification->name) ?>" required>
            </label>
            <label>اسلاگ
                <input name="slug" value="<?= e($specification->slug) ?>" required>
            </label>
            <label>نوع مقدار
                <select name="valueType" required>
                    <?php foreach (['text', 'number', 'boolean', 'date'] as $valueType): ?>
                        <option value="<?= e($valueType) ?>" <?= $specification->valueType === $valueType ? ' selected' : '' ?>><?= e($valueType) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>واحد
                <input name="unit" value="<?= e((string)$specification->unit) ?>">
            </label>
            <label>ترتیب
                <input name="sortOrder" type="number" min="0" value="<?= e((string)$specification->sortOrder) ?>">
            </label>
            <label>وضعیت
                <select name="status">
                    <option value="active" <?= $specification->status === 'active' ? ' selected' : '' ?>>active</option>
                    <option value="inactive" <?= $specification->status === 'inactive' ? ' selected' : '' ?>>inactive</option>
                </select>
            </label>
            <button type="submit">ذخیره</button>
        </form>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="action" value="<?= $specification->deletedAt === null ? 'delete' : 'restore' ?>">
            <button type="submit"><?= $specification->deletedAt === null ? 'حذف' : 'بازگردانی' ?></button>
        </form>
        <a href="<?= e(url('/admin/specifications')) ?>">بازگشت</a>
    </main>
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>