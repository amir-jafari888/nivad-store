<?php
$pageTitle = 'درخواست‌های مرجوعی';
$baseUrl = $baseUrl ?? '';
?>
<section class="account-page account-returns-page">
  <div class="container">
    <header class="account-page__header"><h1>درخواست‌های مرجوعی</h1><p>درخواست‌های مرجوعی ثبت‌شده و ثبت درخواست جدید</p></header>
    <form class="account-form" data-account-return-form>
      <h2>ثبت درخواست مرجوعی</h2>
      <label><span>شناسه سفارش</span><input type="number" name="orderId" min="1" required inputmode="numeric"></label>
      <label><span>شماره پیگیری مرجوعی</span><input type="text" name="returnNumber" maxlength="50" required></label>
      <label><span>شناسه قلم سفارش</span><input type="number" name="orderItemId" min="1" required inputmode="numeric"></label>
      <label><span>تعداد مورد درخواست</span><input type="number" name="quantity" min="1" required inputmode="numeric"></label>
      <label><span>دلیل مرجوعی</span><input type="text" name="reason" maxlength="500"></label>
      <label><span>توضیحات تکمیلی</span><textarea name="customerNote" maxlength="1000" rows="3"></textarea></label>
      <button type="submit">ثبت درخواست</button>
      <p data-account-return-message role="status" aria-live="polite"></p>
    </form>
    <div class="account-returns" data-account-returns aria-live="polite">در حال دریافت درخواست‌ها...</div>
  </div>
</section>