<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once dirname(__DIR__) . '/includes/auth.php';
if (!$adminAuth->can('admin.content.manage')) Response::redirect(url('/admin/access-denied.php'));
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
<title>مدیریت محتوا | نیواد</title>
<link rel="stylesheet" href="<?= e(url('/admin/assets/css/content-editor.css')) ?>">
</head>
<body class="admin-content-editor-page">
<main id="main-content" class="admin-content-editor" data-admin-content-editor data-api-url="<?= e(url('/api')) ?>" data-preview-url="<?= e(url('/admin/content/preview.php')) ?>">
<header class="cms-editor-header">
 <div><p class="cms-editor-kicker">NIVAD / CONTENT STUDIO</p><h1>استودیوی مدیریت محتوا</h1><p>ساختار صفحات، سکشن‌ها، رسانه، نسخه‌ها و انتشار را از یک محیط مدیریت کن.</p></div>
 <div class="cms-editor-header-actions"><a href="<?= e(url('/admin')) ?>">بازگشت به پنل</a><button type="button" data-open-media>کتابخانه رسانه</button></div>
</header>
<div class="cms-editor-workspace">
 <aside class="cms-editor-sidebar">
  <label class="cms-field"><span>صفحه موردنظر</span><select data-page-select><option value="home">صفحه اصلی</option></select></label>
  <div class="cms-section-list-head"><h2>ساختار صفحه</h2><button type="button" data-add-section>+ سکشن جدید</button></div>
  <ol class="cms-section-list" data-section-list><li class="cms-empty">در حال دریافت ساختار صفحه…</li></ol>
  <button type="button" class="cms-secondary-button" data-show-deleted>سکشن‌های آرشیوشده</button>
 </aside>
 <section class="cms-editor-panel">
  <div class="cms-panel-top"><div><span data-section-status class="cms-status">انتخاب نشده</span><h2 data-editor-title>یک سکشن انتخاب کن</h2></div><div class="cms-panel-actions"><button type="button" data-show-history disabled>تاریخچه نسخه‌ها</button><button type="button" data-publish disabled>انتشار</button></div></div>
  <form data-section-form>
   <input type="hidden" name="id"><div class="cms-editor-tabs" role="tablist"><button type="button" class="is-active" data-tab="content">محتوا</button><button type="button" data-tab="design">طراحی</button><button type="button" data-tab="items">آیتم‌ها</button></div>
   <section class="cms-tab-panel is-active" data-tab-panel="content">
    <div class="cms-form-grid">
     <label class="cms-field"><span>نام سکشن در پنل</span><input name="title" maxlength="160" required></label>
     <label class="cms-field"><span>Eyebrow</span><input name="eyebrow" maxlength="180"></label>
     <label class="cms-field cms-field-wide"><span>عنوان اصلی</span><input name="heading" maxlength="300"></label>
     <label class="cms-field cms-field-wide"><span>متن توضیحی</span><textarea name="body" rows="4" maxlength="12000"></textarea></label>
     <label class="cms-field"><span>متن دکمه</span><input name="button_label" maxlength="120"></label>
     <label class="cms-field"><span>لینک دکمه</span><input name="button_url" maxlength="500" placeholder="/products"></label>
     <div class="cms-field cms-field-wide"><span>تصویر اصلی سکشن</span><div class="cms-image-field"><input name="image_id" inputmode="numeric" placeholder="شناسه رسانه"><button type="button" data-pick-image>انتخاب تصویر</button><button type="button" data-clear-image>حذف تصویر</button></div><small data-image-name>تصویری انتخاب نشده است.</small></div>
     <label class="cms-field cms-field-wide"><span>متن جایگزین تصویر</span><input name="image_alt" maxlength="500"></label>
     <label class="cms-field"><span>نمایش سکشن</span><select name="is_visible"><option value="1">نمایش داده شود</option><option value="0">مخفی باشد</option></select></label>
    </div>
   </section>
   <section class="cms-tab-panel" data-tab-panel="design">
    <div class="cms-form-grid">
     <label class="cms-field"><span>نوع پس‌زمینه</span><select name="background_type"><option value="transparent">شفاف</option><option value="solid">رنگ یکپارچه</option><option value="gradient">گرادیان</option></select></label>
     <label class="cms-field"><span>رنگ پس‌زمینه</span><input name="background" type="color" value="#ffffff"></label>
     <label class="cms-field"><span>رنگ شروع گرادیان</span><input name="gradient_from" type="color" value="#101827"></label>
     <label class="cms-field"><span>رنگ پایان گرادیان</span><input name="gradient_to" type="color" value="#36506d"></label>
     <label class="cms-field"><span>زاویه گرادیان</span><input name="gradient_angle" type="number" min="0" max="360" value="135"></label>
     <label class="cms-field"><span>رنگ متن</span><input name="color" type="color" value="#111827"></label>
     <label class="cms-field"><span>رنگ متن فرعی</span><input name="muted_color" type="color" value="#64748b"></label>
     <label class="cms-field"><span>رنگ تأکیدی / دکمه</span><input name="accent_color" type="color" value="#2563eb"></label>
     <label class="cms-field"><span>فاصله بالا (px)</span><input name="padding_top" type="number" min="0" max="500" value="64"></label>
     <label class="cms-field"><span>فاصله پایین (px)</span><input name="padding_bottom" type="number" min="0" max="500" value="64"></label>
     <label class="cms-field"><span>گردی گوشه (px)</span><input name="radius" type="number" min="0" max="160" value="0"></label>
     <label class="cms-field"><span>عرض محتوا (px)</span><input name="container_width" type="number" min="320" max="1600" value="1200"></label>
     <label class="cms-field"><span>تراز متن</span><select name="text_align"><option value="right">راست</option><option value="center">وسط</option><option value="left">چپ</option></select></label>
    </div>
   </section>
   <section class="cms-tab-panel" data-tab-panel="items">
    <div class="cms-items-heading"><div><h3>کارت‌ها و آیتم‌های سکشن</h3><p>برای سکشن‌های چندآیتمی، کارت اضافه کن و محتوای هرکدام را مستقل ویرایش کن.</p></div><button type="button" data-add-item>+ افزودن آیتم</button></div>
    <div class="cms-items-list" data-items-list></div>
   </section>
   <footer class="cms-editor-footer">
    <span data-save-feedback role="status" aria-live="polite">تغییرات فقط در پیش‌نویس ذخیره می‌شوند.</span>
    <div><label class="cms-schedule-field">زمان‌بندی <input type="datetime-local" data-schedule-at></label><button type="button" data-schedule>زمان‌بندی انتشار</button><button type="submit" class="cms-primary-button" data-save disabled>ذخیره پیش‌نویس</button></div>
   </footer>
  </form>
 </section>
 <section class="cms-preview-panel">
  <div class="cms-preview-head"><div><h2>پیش‌نمایش زنده</h2><p>تغییرات فرم بدون بارگذاری مجدد در این قاب دیده می‌شوند.</p></div><span class="cms-live-indicator">LIVE</span></div>
  <iframe title="پیش‌نمایش زنده سکشن" data-preview-frame src="<?= e(url('/admin/content/preview.php')) ?>"></iframe>
 </section>
</div>
<section class="cms-history-panel" data-history-panel hidden><div class="cms-modal-head"><h2>تاریخچه نسخه‌های سکشن</h2><button type="button" data-close-history>بستن</button></div><div data-history-list></div></section>
<section class="cms-media-panel" data-media-panel hidden><div class="cms-modal-head"><h2>کتابخانه رسانه</h2><button type="button" data-close-media>بستن</button></div><form data-upload-form><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><input type="file" name="file" accept="image/jpeg,image/png,image/webp" required><input type="text" name="alt_text" maxlength="500" placeholder="متن جایگزین تصویر"><button type="submit">بارگذاری تصویر</button></form><div class="cms-media-grid" data-media-grid></div></section>
</main>
<script type="module" src="<?= e(url('/admin/assets/js/modules/admin-content-editor.js')) ?>"></script>
</body></html>
