<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';
$id = (int)Request::query('id', 0);
$service = new ProductAttributeService();
$valueService = new ProductAttributeValueService();
$validator = new ProductAttributeValidator();
$valueValidator = new ProductAttributeValueValidator();
$attribute = $service->findById($id);
if ($attribute === null) {
    Response::redirect(url('/admin/attributes'));
}
$error = null;
$valueError = null;
if (Request::isPost()) {
    if (!Csrf::verify(Request::post('csrf_token'))) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $action = (string)Request::post('action', 'update');
        if ($action === 'update') {
            $data = ['name' => Request::post('name'), 'slug' => Request::post('slug'), 'type' => Request::post('type'), 'status' => Request::post('status'), 'sortOrder' => Request::post('sortOrder')];
            $errors = $validator->validate($data);
            if ($errors !== []) {
                $error = implode(' ', array_values($errors));
            } else {
                $service->update($id, trim((string)$data['name']), trim((string)$data['slug']), trim((string)$data['type']), trim((string)$data['status']), (int)$data['sortOrder']);
                Response::redirect(url('/admin/attributes/edit?id=' . $id));
            }
        }
        if ($action === 'delete') {
            $service->delete($id);
            Response::redirect(url('/admin/attributes'));
        }
        if ($action === 'restore') {
            $service->restore($id);
            Response::redirect(url('/admin/attributes/edit?id=' . $id));
        }
        if ($action === 'add_value') {
            $data = ['attributeId' => $id, 'value' => Request::post('value'), 'slug' => Request::post('valueSlug'), 'status' => Request::post('valueStatus'), 'sortOrder' => Request::post('valueSortOrder')];
            $errors = $valueValidator->validate($data);
            if ($errors !== []) {
                $valueError = implode(' ', array_values($errors));
            } else {
                $valueService->create($id, trim((string)$data['value']), trim((string)$data['slug']), trim((string)$data['status']), (int)$data['sortOrder']);
                Response::redirect(url('/admin/attributes/edit?id=' . $id));
            }
        }
        if ($action === 'delete_value') {
            $valueService->delete((int)Request::post('valueId'));
            Response::redirect(url('/admin/attributes/edit?id=' . $id));
        }
        if ($action === 'restore_value') {
            $valueService->restore((int)Request::post('valueId'));
            Response::redirect(url('/admin/attributes/edit?id=' . $id));
        }
    }
}
$values = $valueService->findAllByAttribute($id);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ویرایش ویژگی | نیواد</title>
</head>

<body>
    <main>
        <h1>ویرایش ویژگی</h1>
        <?php if ($error !== null): ?><div role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="action" value="update">
            <label>نام<input name="name" value="<?= e($attribute->name) ?>" required></label>
            <label>اسلاگ<input name="slug" value="<?= e($attribute->slug) ?>" required></label>
            <label>نوع<select name="type" required><?php foreach (['select', 'text', 'number', 'boolean', 'color'] as $type): ?><option value="<?= e($type) ?>" <?= $attribute->type === $type ? ' selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
            <label>وضعیت<select name="status">
                    <option value="active" <?= $attribute->status === 'active' ? ' selected' : '' ?>>active</option>
                    <option value="inactive" <?= $attribute->status === 'inactive' ? ' selected' : '' ?>>inactive</option>
                </select></label>
            <label>ترتیب<input name="sortOrder" type="number" min="0" value="<?= e((string)$attribute->sortOrder) ?>"></label>
            <button type="submit">ذخیره</button>
        </form>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><input type="hidden" name="action" value="delete"><button type="submit">حذف</button></form>
        <section>
            <h2>مقادیر ویژگی</h2>
            <?php if ($valueError !== null): ?><div role="alert"><?= e($valueError) ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="action" value="add_value">
                <input name="value" placeholder="مقدار" required>
                <input name="valueSlug" placeholder="اسلاگ" required>
                <select name="valueStatus">
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                </select>
                <input name="valueSortOrder" type="number" min="0" value="0">
                <button type="submit">افزودن مقدار</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>مقدار</th>
                        <th>اسلاگ</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($values as $value): ?>
                        <tr>
                            <td><?= e($value->value) ?></td>
                            <td><?= e($value->slug) ?></td>
                            <td><?= e($value->status) ?></td>
                            <td>
                                <form method="post"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><input type="hidden" name="valueId" value="<?= e((string)$value->id) ?>"><input type="hidden" name="action" value="<?= $value->deletedAt === null ? 'delete_value' : 'restore_value' ?>"><button type="submit"><?= $value->deletedAt === null ? 'حذف' : 'بازگردانی' ?></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>