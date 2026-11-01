<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';
$service = new CategoryService();
$id = (int)Request::query('id', 0);
$category = $service->findById($id);
if ($category === null) {
    Response::redirect(url('/admin/categories'));
}
$error = null;
$errors = [];
$data = ['parentId' => $category->parentId, 'name' => $category->name, 'slug' => $category->slug, 'description' => $category->description ?? '', 'status' => $category->status, 'sortOrder' => $category->sortOrder];
if (Request::isPost()) {
    if (!Csrf::verify((string)Request::post('csrf_token', ''))) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $data['parentId'] = Request::post('parentId', '');
        $data['name'] = trim((string)Request::post('name', ''));
        $data['slug'] = trim((string)Request::post('slug', ''));
        $data['description'] = Request::post('description', '');
        $data['status'] = (string)Request::post('status', 'active');
        $data['sortOrder'] = (int)Request::post('sortOrder', 0);
        $data['parentId'] = $data['parentId'] === '' ? null : (int)$data['parentId'];
        $errors = (new CategoryValidator())->validate($data);
        $sameSlug = $service->findBySlug($data['slug']);
        if ($errors === [] && $sameSlug !== null && $sameSlug->id !== $id) {
            $errors['slug'] = 'اسلاگ دسته‌بندی تکراری است.';
        }
        if ($errors === [] && $data['parentId'] !== null && $data['parentId'] === $id) {
            $errors['parentId'] = 'دسته‌بندی نمی‌تواند والد خودش باشد.';
        }
        if ($errors === [] && $data['parentId'] !== null && !$service->exists($data['parentId'])) {
            $errors['parentId'] = 'دسته‌بندی والد نامعتبر است.';
        }
        if ($errors === []) {
            $service->update($id, $data['parentId'], $data['name'], $data['slug'], $data['description'] === '' ? null : $data['description'], $data['status'], $data['sortOrder']);
            Response::redirect(url('/admin/categories'));
        }
    }
}
$parents = $service->findActive();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ویرایش دسته‌بندی | نیواد</title>
</head>

<body>
    <h1>ویرایش دسته‌بندی</h1>
    <?php if ($error !== null): ?><div><?= e($error) ?></div><?php endif; ?>
    <?php if ($errors !== []): ?><div><?php foreach ($errors as $message): ?><p><?= e($message) ?></p><?php endforeach; ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/admin/categories/edit?id=' . $id)) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
        <label>نام<input name="name" value="<?= e($data['name']) ?>" required></label>
        <label>Slug<input name="slug" value="<?= e($data['slug']) ?>" required></label>
        <label>والد<select name="parentId">
                <option value="">بدون والد</option><?php foreach ($parents as $parent): ?><?php if ($parent->id !== $id): ?><option value="<?= e($parent->id) ?>" <?= (string)$data['parentId'] === (string)$parent->id ? ' selected' : '' ?>><?= e($parent->name) ?></option><?php endif; ?><?php endforeach; ?>
            </select></label>
        <label>توضیحات<textarea name="description"><?= e((string)$data['description']) ?></textarea></label>
        <label>وضعیت<select name="status">
                <option value="active" <?= $data['status'] === 'active' ? ' selected' : '' ?>>فعال</option>
                <option value="inactive" <?= $data['status'] === 'inactive' ? ' selected' : '' ?>>غیرفعال</option>
            </select></label>
        <label>ترتیب<input type="number" name="sortOrder" value="<?= e($data['sortOrder']) ?>" min="0"></label>
        <button type="submit">ذخیره تغییرات</button>
    </form>
</body>

</html>