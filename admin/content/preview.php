<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
$adminAuth = new AdminAuthService();
if (!$adminAuth->validateCurrentSession() || !$adminAuth->can('admin.content.manage')) { http_response_code(403); exit('Forbidden'); }
?><!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="<?= e(url('/assets/css/main.css')) ?>">
<style>html,body{margin:0;min-height:100%;background:#fff}.cms-preview-root{min-height:100vh}.cms-preview-placeholder{display:grid;place-items:center;min-height:60vh;padding:32px;color:#64748b;text-align:center;font:16px/2 Tahoma,Arial,sans-serif}</style>
</head><body><div class="cms-preview-root" data-preview-root><div class="cms-preview-placeholder">با انتخاب یا ویرایش سکشن، پیش‌نمایش زنده در این قسمت نمایش داده می‌شود.</div></div>
<script>
(() => {
 const root=document.querySelector('[data-preview-root]');
 const safeUrl=value=>{const v=String(value||'').trim();return v.startsWith('/')&&!v.startsWith('//')?v:(/^https:\/\/[a-z0-9.-]+(?::[0-9]+)?(?:\/[^\s]*)?$/i.test(v)?v:'#')};
 const el=(tag,cls,text)=>{const n=document.createElement(tag);if(cls)n.className=cls;if(text!==undefined)n.textContent=String(text);return n};
 const image=(id,alt)=>{if(!Number.isInteger(Number(id))||Number(id)<1)return null;const n=el('img','cms-live-section__image');n.src='<?= e(url('/api/admin/media/')) ?>'+Number(id);n.alt=String(alt||'');return n};
 const render=data=>{
  const section=el('section','cms-live-section cms-live-section--'+String(data.section_type||'custom').replace(/[^a-z0-9_-]/g,''));
  const style=data.style||{}, vars={background:'--cms-background',gradient_from:'--cms-gradient-from',gradient_to:'--cms-gradient-to',color:'--cms-color',muted_color:'--cms-muted',accent_color:'--cms-accent'};
  Object.entries(vars).forEach(([key,name])=>{if(/^#[0-9a-f]{3,8}$/i.test(String(style[key]||'')))section.style.setProperty(name,style[key])});
  if(style.background_type==='gradient'){section.style.setProperty('--cms-gradient-angle',Math.max(0,Math.min(360,Number(style.gradient_angle)||135))+'deg')}
  ['padding_top','padding_bottom','radius','container_width'].forEach(k=>{if(Number.isFinite(Number(style[k])))section.style.setProperty('--cms-'+({padding_top:'padding-top',padding_bottom:'padding-bottom',radius:'radius',container_width:'container-width'}[k]),Math.max(0,Math.min(1600,Number(style[k])))+'px')});
  if(['right','center','left'].includes(style.text_align))section.style.setProperty('--cms-text-align',style.text_align);
  if(style.background_type==='solid'&&style.background)section.style.setProperty('--cms-background',style.background);
  const inner=el('div','cms-live-section__inner'),c=data.content||{};
  if(c.eyebrow)inner.append(el('p','cms-live-section__eyebrow',c.eyebrow));
  if(c.heading||data.title)inner.append(el('h2','cms-live-section__heading',c.heading||data.title));
  if(c.body)inner.append(el('p','cms-live-section__body',c.body));
  const mainImage=image(c.image_id,c.image_alt||c.heading);if(mainImage)inner.append(mainImage);
  if(Array.isArray(c.items)&&c.items.length){const grid=el('div','cms-live-section__grid');c.items.forEach(item=>{const card=el('article','cms-live-card');const img=image(item.image_id,item.image_alt||item.title);if(img)card.append(img);if(item.eyebrow)card.append(el('span','cms-live-card__eyebrow',item.eyebrow));if(item.title)card.append(el('h3','',item.title));if(item.body)card.append(el('p','',item.body));if(item.link_label){const a=el('a','',item.link_label);a.href=safeUrl(item.url);card.append(a)}grid.append(card)});inner.append(grid)}
  if(c.button_label&&c.button_url){const a=el('a','cms-live-section__button',c.button_label);a.href=safeUrl(c.button_url);inner.append(a)}
  section.append(inner);root.replaceChildren(section);
 };
 window.addEventListener('message',event=>{if(event.origin!==location.origin||event.source!==parent||event.data?.type!=='cms-preview')return;render(event.data.section||{})});
})();
</script></body></html>
