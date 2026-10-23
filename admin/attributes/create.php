<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';
$service = new ProductAttributeService();
$validator = new ProductAttributeValidator();
$error = null;
if (Request::isPost()) {
    if (!Csrf::verify(Request::post('csrf_token'))) {
        $error = 'درخواست نامعتبر است.';
    } else {
        $data = [
            'name' => Request::post('name'),
            'slug' => Request::post('slug'),
            'type' => Request::post('type'),
            'status' => Request::post('status'),
            'sortOrder' => Request::post('sortOrder')
        ];
        $errors = $validator->validate($data);
        if ($errors !== []) {
            $error = implode(' ', array_values($errors));
        } else {
            $service->create(trim((string)$data['name']), trim((string)$data['slug']), trim((string)$data['type']), trim((string)$data['status']), (int)$data['sortOrder']);
            Response::redirect(url('/admin/attributes'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ایجاد ویژگی | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
</head>

<body>
    <main>
        <h1>ایجاد ویژگی</h1>
        <?php if ($error !== null): ?><div role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <label>نام<input name="name" required></label>
            <label>اسلاگ<input name="slug" required></label>
            <label>نوع<select name="type" required>
                    <option value="select">select</option>
                    <option value="text">text</option>
                    <option value="number">number</option>
                    <option value="boolean">boolean</option>
                    <option value="color">color</option>
                </select></label>
            <label>وضعیت<select name="status" required>
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                </select></label>
            <label>ترتیب<input name="sortOrder" type="number" min="0" value="0"></label>
            <button type="submit">ذخیره</button>
            <a href="<?= e(url('/admin/attributes')) ?>">انصراف</a>
        </form>
    </main>
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</body>

</html>