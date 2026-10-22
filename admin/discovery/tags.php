<?php

declare(strict_types=1);
?>

<section
    class="admin-discovery-tags"
    data-admin-discovery-tags-page>
    <div class="container">
        <h1>مدیریت تگ‌های Discovery</h1>

        <form
            method="post"
            data-discovery-tag-form>
            <input
                type="text"
                name="name"
                placeholder="نام تگ"
                required>

            <input
                type="text"
                name="slug"
                placeholder="Slug"
                required>

            <textarea
                name="description"
                placeholder="توضیحات"></textarea>

            <select name="status">
                <option value="active">فعال</option>
                <option value="inactive">غیرفعال</option>
            </select>

            <input
                type="number"
                name="sortOrder"
                value="0"
                min="0">

            <button type="submit">
                ایجاد تگ
            </button>
        </form>

        <div
            data-discovery-tag-list></div>
    </div>
</section>