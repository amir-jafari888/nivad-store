<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';

$csrfToken = Csrf::token();

$id =
    (int)($_GET['id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width,initial-scale=1">
    <title>ویرایش مجموعه | نیواد</title>
</head>

<body>

    <main>

        <h1>ویرایش مجموعه</h1>

        <form id="collection-form">

            <input name="name" placeholder="نام">

            <input name="slug" placeholder="Slug">

            <textarea
                name="description"
                placeholder="توضیحات"></textarea>

            <select name="collection_type">
                <option value="manual">manual</option>
                <option value="automatic">automatic</option>
            </select>

            <select name="status">
                <option value="active">active</option>
                <option value="inactive">inactive</option>
            </select>

            <input
                name="sort_order"
                type="number">

            <input name="starts_at">
            <input name="ends_at">

            <button type="submit">
                ذخیره
            </button>

        </form>

        <p id="message"></p>

    </main>

    <script>
        const id = <?= $id ?>;

        const form =
            document.getElementById(
                'collection-form'
            );

        fetch(
                '<?= e(url('/api/admin/collections/')) ?>' +
                id
            )
            .then(response => response.json())
            .then(result => {

                const item =
                    result.data?.collection;

                if (!item) {
                    return;
                }

                form.elements.name.value =
                    item.name ?? '';

                form.elements.slug.value =
                    item.slug ?? '';

                form.elements.description.value =
                    item.description ?? '';

                form.elements.collection_type.value =
                    item.collectionType ?? '';

                form.elements.status.value =
                    item.status ?? '';

                form.elements.sort_order.value =
                    item.sortOrder ?? 0;

                form.elements.starts_at.value =
                    item.startsAt ?? '';

                form.elements.ends_at.value =
                    item.endsAt ?? '';
            });

        form.addEventListener(
            'submit',
            async event => {

                event.preventDefault();

                const body =
                    Object.fromEntries(
                        new FormData(form)
                    );

                const response =
                    await fetch(
                        '<?= e(url('/api/admin/collections/')) ?>' +
                        id, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?= e($csrfToken) ?>'
                            },
                            body: JSON.stringify(body)
                        }
                    );

                const result =
                    await response.json();

                document.getElementById(
                        'message'
                    ).textContent =
                    result.message || '';
            }
        );
    </script>

</body>

</html>