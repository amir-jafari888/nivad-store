<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

$adminAuth = new AdminAuthService();
if (!$adminAuth->canAny(['admin.notifications.view', 'admin.notifications.manage'])) {
    header('Location: /admin/access-denied.php');
    exit;
}
$csrfToken = Csrf::token();
$apiBase = rtrim((string)(getenv('APP_URL') ?: ''), '/') . '/api';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
<meta name="admin-api-url" content="<?= htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8') ?>">
<title>مرکز اعلان‌های مدیریتی | نیواد</title>
<style>
:root{color-scheme:light;--bg:#f4f6f9;--surface:#fff;--text:#172033;--muted:#64748b;--line:#e2e8f0;--accent:#243b64}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:14px/1.8 Tahoma,Arial,sans-serif}
main{max-width:1180px;margin:32px auto;padding:0 18px}.top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
h1{font-size:24px;margin:0}.muted{color:var(--muted)}.panel{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:18px;margin-top:18px}
.toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center}select,input,button{font:inherit;border:1px solid var(--line);border-radius:9px;padding:9px 12px;background:#fff;color:var(--text)}
button{cursor:pointer;background:var(--accent);border-color:var(--accent);color:#fff}button.secondary{background:#fff;color:var(--text);border-color:var(--line)}
.notice{padding:10px 12px;background:#eef2f7;border-radius:9px;margin:12px 0}.item{border-top:1px solid var(--line);padding:16px 0}.item:first-child{border-top:0}
.item-head{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}.title{font-weight:700}.meta{color:var(--muted);font-size:12px}.pill{display:inline-block;border-radius:30px;padding:1px 9px;background:#eef2f7;font-size:12px}
.actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.empty{text-align:center;padding:36px;color:var(--muted)}
@media(max-width:600px){main{margin:16px auto;padding:0 12px}.panel{padding:13px}.toolbar>*{width:100%}.item-head{display:block}}
</style>
</head>
<body>
<main>
<div class="top"><div><h1>مرکز اعلان‌های مدیریتی</h1><div class="muted">صندوق ورودی، تاریخچه و رسیدگی به رویدادهای مهم</div></div><div class="toolbar"><a href="/admin/notifications/send-customer.php">ارسال اعلان به مشتری</a><a href="/admin/">بازگشت به داشبورد</a></div></div>
<section class="panel">
<div class="toolbar">
<select id="severity"><option value="">همه شدت‌ها</option><option value="critical">بحرانی</option><option value="warning">هشدار</option><option value="success">موفق</option><option value="info">اطلاع‌رسانی</option></select>
<select id="state"><option value="">همه اعلان‌ها</option><option value="unread">خوانده‌نشده</option><option value="ack-no">نیازمند رسیدگی</option><option value="ack-yes">رسیدگی‌شده</option></select>
<input id="eventKey" placeholder="کلید رویداد">
<button id="filter">اعمال فیلتر</button><button class="secondary" id="readAll">خواندن همه</button><button class="secondary" id="showPrefs">تنظیمات اعلان</button><button class="secondary" id="showPolicies">سیاست‌های اعلان</button>
</div>
<div id="feedback" class="notice" role="status" aria-live="polite">در حال دریافت اعلان‌ها…</div>
<div id="items"></div>
</section>
<section class="panel" id="preferences" hidden>
<h2>ترجیحات اعلان</h2>
<form id="prefForm">
<div class="toolbar">
<label><input type="checkbox" id="inApp" checked> اعلان داخل پنل</label>
<label><input type="checkbox" id="email"> اعلان ایمیلی</label>
<select id="minSeverity"><option value="info">از اطلاع‌رسانی</option><option value="success">از موفق</option><option value="warning">از هشدار</option><option value="critical">فقط بحرانی</option></select>
<input type="time" id="quietStart" aria-label="شروع ساعات سکوت">
<input type="time" id="quietEnd" aria-label="پایان ساعات سکوت">
<input id="timezone" value="UTC" aria-label="منطقه زمانی">
<button type="submit">ذخیره تنظیمات عمومی</button>
</div>
<p class="meta">ارسال ایمیل فقط در صف ثبت می‌شود؛ اجرای Worker و تنظیمات SMTP باید جداگانه فعال و آزمایش شوند.</p>
</form>
</section>
<section class="panel" id="policies" hidden>
<h2>سیاست‌های تولید اعلان</h2>
<p class="meta">فعال‌سازی رویداد، شدت، ایمیل، جلوگیری از تکرار و سقف تعداد اعلان در بازه زمانی.</p>
<div id="policyItems"></div>
</section>
</main>
<script>
(() => {
 const apiBase=document.querySelector('meta[name="admin-api-url"]').content;
 const csrf=document.querySelector('meta[name="csrf-token"]').content;
 const feedback=document.getElementById('feedback'),items=document.getElementById('items');
 const request=async(path,method='GET',body=null,query={})=>{const url=new URL(apiBase,location.href);url.searchParams.set('path',path);Object.entries(query).forEach(([key,value])=>{if(value!==''&&value!==null&&value!==undefined)url.searchParams.set(key,String(value))});const headers={Accept:'application/json'};const options={method,credentials:'same-origin',headers};if(body!==null){headers['Content-Type']='application/json';headers['X-CSRF-TOKEN']=csrf;options.body=JSON.stringify(body)}const res=await fetch(url,options);const data=await res.json();if(!res.ok||data.success!==true)throw new Error(data.message||'درخواست ناموفق بود');return data.data||{}};
 const esc=value=>String(value??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const load=async()=>{feedback.textContent='در حال دریافت اعلان‌ها…';const state=document.getElementById('state').value;const params=new URLSearchParams();if(state==='unread')params.set('unread','1');if(state==='ack-no')params.set('acknowledged','no');if(state==='ack-yes')params.set('acknowledged','yes');const sev=document.getElementById('severity').value;if(sev)params.set('severity',sev);const event=document.getElementById('eventKey').value.trim();if(event)params.set('event_key',event);try{const data=await request('/admin/capabilities/notifications','GET',null,Object.fromEntries(params.entries()));feedback.textContent='خوانده‌نشده: '+(data.unread_count??0);items.innerHTML=(data.items||[]).map(n=>'<article class="item"><div class="item-head"><span class="title">'+esc(n.title)+'</span><span class="pill">'+esc(n.severity)+'</span></div><div>'+esc(n.message)+'</div><div class="meta">'+esc(n.event_key)+' · '+esc(n.created_at)+' · '+(n.acknowledged_at?'رسیدگی‌شده': 'در انتظار رسیدگی')+'</div><div class="actions">'+(!n.read_at?'<button class="secondary" data-read="'+Number(n.id)+'">خوانده شد</button>':'')+(!n.acknowledged_at?'<button data-ack="'+Number(n.id)+'">تأیید و رسیدگی</button>':'')+'</div></article>').join('')||'<div class="empty">اعلانی برای نمایش وجود ندارد.</div>';}catch(e){feedback.textContent=e.message}};
 items.addEventListener('click',async e=>{const read=e.target.closest('[data-read]'),ack=e.target.closest('[data-ack]');try{if(read)await request('/admin/capabilities/notifications/'+read.dataset.read+'/read','PATCH',{});if(ack)await request('/admin/capabilities/notifications/'+ack.dataset.ack+'/acknowledge','PATCH',{});await load()}catch(err){feedback.textContent=err.message}});
 document.getElementById('filter').addEventListener('click',load);
 document.getElementById('readAll').addEventListener('click',async()=>{try{await request('/admin/capabilities/notifications/read-all','POST',{});await load()}catch(e){feedback.textContent=e.message}});
 document.getElementById('showPrefs').addEventListener('click',async()=>{const panel=document.getElementById('preferences');panel.hidden=!panel.hidden;if(panel.hidden)return;try{const data=await request('/admin/capabilities/notification-preferences');const p=data.items['*']||{};document.getElementById('inApp').checked=p.in_app_enabled!==false;document.getElementById('email').checked=!!p.email_enabled;document.getElementById('minSeverity').value=p.minimum_severity||'info';document.getElementById('quietStart').value=p.quiet_hours_start||'';document.getElementById('quietEnd').value=p.quiet_hours_end||'';document.getElementById('timezone').value=p.timezone||'UTC'}catch(e){feedback.textContent=e.message}});
 document.getElementById('prefForm').addEventListener('submit',async e=>{e.preventDefault();try{await request('/admin/capabilities/notification-preferences','PUT',{event_key:'*',in_app_enabled:document.getElementById('inApp').checked,email_enabled:document.getElementById('email').checked,minimum_severity:document.getElementById('minSeverity').value,quiet_hours_start:document.getElementById('quietStart').value||null,quiet_hours_end:document.getElementById('quietEnd').value||null,timezone:document.getElementById('timezone').value||'UTC'});feedback.textContent='ترجیحات ذخیره شد.'}catch(e){feedback.textContent=e.message}});
 const policyPanel=document.getElementById('policies'),policyItems=document.getElementById('policyItems');
 const loadPolicies=async()=>{try{const data=await request('/admin/capabilities/notification-policies');policyItems.innerHTML=(data.items||[]).map(p=>'<article class="item" data-policy="'+esc(p.event_key)+'"><div class="title">'+esc(p.event_key)+'</div><div class="toolbar"><label><input type="checkbox" data-enabled '+(Number(p.enabled)?'checked':'')+'> فعال</label><label><input type="checkbox" data-email '+(Number(p.email_enabled)?'checked':'')+'> ایمیل</label><select data-severity><option value="info">اطلاع</option><option value="success">موفق</option><option value="warning">هشدار</option><option value="critical">بحرانی</option></select><input type="number" min="0" max="86400" data-dedupe value="'+Number(p.dedupe_seconds)+'" aria-label="ثانیه جلوگیری از تکرار"><input type="number" min="1" max="1000" data-maximum value="'+Number(p.maximum_per_window)+'" aria-label="حداکثر تعداد"><input type="number" min="60" max="86400" data-window value="'+Number(p.window_seconds)+'" aria-label="طول بازه به ثانیه"><button data-policy-save>ذخیره</button></div></article>').join('')||'<div class="empty">سیاستی ثبت نشده است.</div>';policyItems.querySelectorAll('[data-policy]').forEach(row=>{row.querySelector('[data-severity]').value=(data.items.find(p=>p.event_key===row.dataset.policy)||{}).severity||'info'});}catch(e){feedback.textContent=e.message}};
 document.getElementById('showPolicies').addEventListener('click',async()=>{policyPanel.hidden=!policyPanel.hidden;if(!policyPanel.hidden)await loadPolicies()});
 policyItems.addEventListener('click',async e=>{const button=e.target.closest('[data-policy-save]');if(!button)return;const row=button.closest('[data-policy]');try{await request('/admin/capabilities/notification-policies','PUT',{event_key:row.dataset.policy,enabled:row.querySelector('[data-enabled]').checked,email_enabled:row.querySelector('[data-email]').checked,severity:row.querySelector('[data-severity]').value,dedupe_seconds:Number(row.querySelector('[data-dedupe]').value),maximum_per_window:Number(row.querySelector('[data-maximum]').value),window_seconds:Number(row.querySelector('[data-window]').value)});feedback.textContent='سیاست '+row.dataset.policy+' ذخیره شد.'}catch(err){feedback.textContent=err.message}});
 load();
})();
</script>
</body></html>
