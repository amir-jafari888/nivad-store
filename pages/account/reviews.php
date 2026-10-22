<?php
declare(strict_types=1);
$pageTitle = 'دیدگاه‌های من';
?>
<section class="account-page account-reviews-page">
  <div class="container">
    <header class="account-page__header"><h1>دیدگاه‌های من</h1><p>دیدگاه‌های ثبت‌شده و وضعیت بررسی آن‌ها.</p></header>
    <div data-account-reviews aria-live="polite"><p>در حال دریافت دیدگاه‌ها...</p></div>
    <form class="account-form" data-account-review-form>
      <h2>ثبت دیدگاه</h2>
      <label><span>شناسه محصول</span><input type="number" name="productId" min="1" required inputmode="numeric"></label>
      <label><span>امتیاز از ۱ تا ۵</span><input type="number" name="rating" min="1" max="5" required inputmode="numeric"></label>
      <label><span>عنوان دیدگاه</span><input type="text" name="title" maxlength="255"></label>
      <label><span>متن دیدگاه</span><textarea name="body" rows="5" required></textarea></label>
      <button type="submit">ثبت دیدگاه</button>
      <p data-review-message role="status" aria-live="polite"></p>
    </form>
  </div>
</section>
