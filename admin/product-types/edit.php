<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';
$service = new ProductTypeService();
$id = (int)Request::query('id', 0);
$item = $service->findById($id);
if ($item === null) {
    Response::redirect(url('/admin/product-types'));
}
$error = null;
$errors = [];
$data = ['name' => $item->name, 'slug' => $item->slug, 'description' => $item->description ?? '', 'status' => $item->status, 'sortOrder' => $item->sortOrder];
if (Request::isPost()) {
    if (!Csrf::verify((string)Request::post('csrf_token', ''))) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $data['name'] = trim((string)Request::post('name', ''));
        $data['slug'] = trim((string)Request::post('slug', ''));
        $data['description'] = Request::post('description', '');
        $data['status'] = (string)Request::post('status', 'active');
        $data['sortOrder'] = (int)Request::post('sortOrder', 0);
        $errors = (new ProductTypeValidator())->validate($data);
        $same = $service->findBySlug($data['slug']);
        if ($errors === [] && $same !== null && $same->id !== $id) {
            $errors['slug'] = 'اسلاگ نوع محصول تکراری است.';
        }
        if ($errors === []) {
            $service->update($id, $data['name'], $data['slug'], $data['description'] === '' ? null : $data['description'], $data['status'], $data['sortOrder']);
            Response::redirect(url('/admin/product-types'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ویرایش نوع محصول | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>
    <h1>ویرایش نوع محصول</h1>
    <?php if ($error !== null): ?><div><?= e($error) ?></div><?php endif; ?>
    <?php if ($errors !== []): ?><div><?php foreach ($errors as $message): ?><p><?= e($message) ?></p><?php endforeach; ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/admin/product-types/edit?id=' . $id)) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
        <label>نام<input name="name" value="<?= e($data['name']) ?>" required></label>
        <label>Slug<input name="slug" value="<?= e($data['slug']) ?>" required></label>
        <label>توضیحات<textarea name="description"><?= e((string)$data['description']) ?></textarea></label>
        <label>وضعیت<select name="status">
                <option value="active" <?= $data['status'] === 'active' ? ' selected' : '' ?>>فعال</option>
                <option value="inactive" <?= $data['status'] === 'inactive' ? ' selected' : '' ?>>غیرفعال</option>
            </select></label>
        <label>ترتیب<input type="number" name="sortOrder" value="<?= e($data['sortOrder']) ?>" min="0"></label>
        <button type="submit">ذخیره تغییرات</button>
    </form>
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>