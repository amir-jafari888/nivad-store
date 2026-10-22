<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';
$service = new SpecificationService();
$groupService = new SpecGroupService();
$validator = new SpecificationValidator();
$groups = $groupService->findActive();
$error = null;
if (Request::isPost()) {
    if (!Csrf::verify(Request::post('csrf_token'))) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $data = ['specGroupId' => Request::post('specGroupId'), 'name' => Request::post('name'), 'slug' => Request::post('slug'), 'valueType' => Request::post('valueType'), 'unit' => Request::post('unit'), 'status' => Request::post('status'), 'sortOrder' => Request::post('sortOrder')];
        $errors = $validator->validate($data);
        if ($errors !== []) {
            $error = implode(' ', array_values($errors));
        } else {
            $service->create((int)$data['specGroupId'], trim((string)$data['name']), trim((string)$data['slug']), trim((string)$data['valueType']), trim((string)$data['unit']), (int)$data['sortOrder'], trim((string)$data['status']));
            Response::redirect(url('/admin/specifications'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ایجاد مشخصات | نیواد</title>
</head>

<body>
    <main>
        <h1>ایجاد مشخصات</h1>
        <?php if ($error !== null): ?><div role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <select name="specGroupId" required><?php foreach ($groups as $group): ?><option value="<?= e((string)$group->id) ?>"><?= e($group->name) ?></option><?php endforeach; ?></select>
            <input name="name" placeholder="نام" required>
            <input name="slug" placeholder="اسلاگ" required>
            <select name="valueType">
                <option value="text">text</option>
                <option value="number">number</option>
                <option value="boolean">boolean</option>
                <option value="date">date</option>
            </select>
            <input name="unit" placeholder="واحد">
            <select name="status">
                <option value="active">active</option>
                <option value="inactive">inactive</option>
            </select>
            <input name="sortOrder" type="number" min="0" value="0">
            <button type="submit">ذخیره</button>
        </form>
    </main>
</body>

</html>