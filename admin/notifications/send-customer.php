<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$adminAuth = new AdminAuthService();
if (!$adminAuth->canAny(['admin.notifications.manage'])) {
    header('Location: /admin/access-denied.php');
    exit;
}
$csrfToken = Csrf::token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
<title>ارسال اعلان به مشتری | نیواد</title>
<style>
body{margin:0;background:#f4f6f9;color:#172033;font:14px/1.8 Tahoma,Arial,sans-serif}
main{max-width:760px;margin:32px auto;padding:18px}.card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:22px}
.field{display:flex;flex-direction:column;gap:7px;margin-bottom:15px}input,textarea,button{font:inherit;border:1px solid #d1d5db;border-radius:8px;padding:10px}
textarea{min-height:110px}button{background:#243b64;color:#fff;cursor:pointer}.message{padding:12px;background:#eef2f7;border-radius:8px;margin-bottom:15px}
</style>
</head>
<body>
<main>
<p><a href="/admin/notifications/">بازگشت به مرکز اعلان‌ها</a></p>
<h1>ارسال اعلان به مشتری</h1>
<div id="message" class="message" role="status" aria-live="polite">اطلاعات اعلان را وارد کنید.</div>
<form id="form" class="card">
<div class="field"><label for="user_id">شناسه مشتری</label><input id="user_id" name="user_id" type="number" min="1" required></div>
<div class="field"><label for="type">نوع اعلان</label><input id="type" name="type" required></div>
<div class="field"><label for="title">عنوان</label><input id="title" name="title" maxlength="255" required></div>
<div class="field"><label for="body">پیام</label><textarea id="body" name="message" maxlength="5000" required></textarea></div>
<div class="field"><label for="data">داده JSON اختیاری</label><textarea id="data" name="data" placeholder='{"key":"value"}'></textarea></div>
<button type="submit">ارسال اعلان</button>
</form>
</main>
<script>
(() => {
 const csrf=document.querySelector('meta[name="csrf-token"]').content;
 const form=document.getElementById('form'),message=document.getElementById('message');
 form.addEventListener('submit',async event=>{
  event.preventDefault();
  const data=Object.fromEntries(new FormData(form).entries());
  if(data.data.trim()!==''){try{data.data=JSON.parse(data.data)}catch(_){message.textContent='فیلد JSON معتبر نیست.';return}}else data.data=null;
  try{
   const response=await fetch('/api/admin/notifications',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(data)});
   const result=await response.json();
   if(!response.ok||result.success!==true)throw new Error(result.message||'ارسال اعلان ناموفق بود.');
   message.textContent='اعلان مشتری با موفقیت ثبت شد.';form.reset();
  }catch(error){message.textContent=error.message}
 });
})();
</script>
</body>
</html>
