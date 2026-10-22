<?php

declare(strict_types=1);
?>

<section class="account-page">
    <h1>دیدگاه‌های من</h1>

    <div data-account-reviews>
        <p>در حال دریافت دیدگاه‌ها...</p>
    </div>

    <hr>

    <form
        data-account-review-form
        novalidate>
        <h2>ثبت دیدگاه</h2>

        <input
            type="number"
            name="productId"
            min="1"
            required
            placeholder="شناسه محصول">

        <input
            type="number"
            name="rating"
            min="1"
            max="5"
            required
            placeholder="امتیاز">

        <input
            type="text"
            name="title"
            maxlength="255"
            placeholder="عنوان">

        <textarea
            name="body"
            required
            placeholder="متن دیدگاه"></textarea>

        <button type="submit">
            ثبت دیدگاه
        </button>

        <p data-review-message></p>
    </form>
</section>