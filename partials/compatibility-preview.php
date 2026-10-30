<?php
/**
 * Homepage teaser for the dedicated Compatibility Finder.
 * This visual preview does not request or imply live compatibility results.
 */
$compatibilityFinderUrl = url('/pages/store/compatibility.php');
$cmsContent = CmsContentRenderer::activeContent();
?>
<section class="home-compatibility-preview" aria-labelledby="home-compatibility-preview-title">
  <div class="home-compatibility-preview__inner">
    <div class="home-compatibility-preview__copy">
      <span class="home-compatibility-preview__eyebrow">
        <span class="home-compatibility-preview__eyebrow-mark" aria-hidden="true">✦</span>
        <?= e((string)($cmsContent['eyebrow'] ?? 'NIVAD COMPATIBILITY FINDER')) ?>
      </span>

      <h2 id="home-compatibility-preview-title"><?= e((string)($cmsContent['heading'] ?? 'قبل از خرید، از سازگاری مطمئن شو.')) ?></h2>
      <p class="home-compatibility-preview__description">
        <?= e((string)($cmsContent['body'] ?? 'مدل دستگاهت رو انتخاب کن و لوازم جانبی مرتبط باهاش رو پیدا کن؛ با اطلاعات شفاف‌تر، انتخاب مطمئن‌تری داشته باش.')) ?>
      </p>

      <ul class="home-compatibility-preview__benefits" aria-label="مزیت‌های بررسی سازگاری">
        <li>
          <span class="home-compatibility-preview__benefit-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="6" y="2.8" width="12" height="18.4" rx="2.4"/><path d="M10 6h4m-3 11h2"/></svg>
          </span>
          <span><strong>از دستگاه خودت شروع کن</strong><small>برند و مدل را مشخص کن</small></span>
        </li>
        <li>
          <span class="home-compatibility-preview__benefit-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M8 7V4h8v3M5 7h14v13H5z"/><path d="m8 13 2.5 2.5L16 10"/></svg>
          </span>
          <span><strong>وضعیت سازگاری را ببین</strong><small>توضیح هر مورد را بررسی کن</small></span>
        </li>
        <li>
          <span class="home-compatibility-preview__benefit-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m16 16 4.5 4.5M8 11h6m-3-3v6"/></svg>
          </span>
          <span><strong>آگاهانه‌تر انتخاب کن</strong><small>قبل از تصمیم نهایی، جزئیات را بخوان</small></span>
        </li>
      </ul>

      <div class="home-compatibility-preview__actions">
        <a class="home-compatibility-preview__cta" href="<?= e(url((string)($cmsContent['button_url'] ?? '/pages/store/compatibility.php'))) ?>">
          <span><?= e((string)($cmsContent['button_label'] ?? 'بررسی سازگاری دستگاه من')) ?></span>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
        </a>
        <span class="home-compatibility-preview__action-note">از انتخاب دستگاه تا پیدا کردن محصولات مرتبط، در یک مسیر ساده.</span>
      </div>
    </div>

    <div class="home-compatibility-preview__visual" role="img" aria-label="تصویر مفهومی یک گوشی هوشمند در کنار لوازم جانبی و کارت‌های نمونه وضعیت سازگاری">
      <div class="home-compatibility-preview__visual-glow" aria-hidden="true"></div>
      <div class="home-compatibility-preview__visual-topline">
        <div>
          <span class="home-compatibility-preview__visual-kicker">DEVICE MATCHING</span>
          <h3>سازگاری، پیش از خرید</h3>
        </div>
        <span class="home-compatibility-preview__visual-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6 2.1 2.1m0-12.8-2.1 2.1m-8.6 8.6-2.1 2.1"/><circle cx="12" cy="12" r="4"/></svg>
        </span>
      </div>

      <div class="home-compatibility-preview__stage" aria-hidden="true">
        <div class="home-compatibility-preview__orbit home-compatibility-preview__orbit--outer"></div>
        <div class="home-compatibility-preview__orbit home-compatibility-preview__orbit--inner"></div>
        <div class="home-compatibility-preview__phone">
          <div class="home-compatibility-preview__phone-frame">
            <div class="home-compatibility-preview__phone-camera"></div>
            <div class="home-compatibility-preview__phone-screen">
              <span class="home-compatibility-preview__screen-time">9:41</span>
              <span class="home-compatibility-preview__screen-orb"></span>
              <span class="home-compatibility-preview__screen-line home-compatibility-preview__screen-line--long"></span>
              <span class="home-compatibility-preview__screen-line"></span>
              <span class="home-compatibility-preview__screen-chip">DEVICE</span>
            </div>
          </div>
          <span class="home-compatibility-preview__phone-shadow"></span>
        </div>

        <div class="home-compatibility-preview__accessory home-compatibility-preview__accessory--cable">
          <svg viewBox="0 0 70 70"><path d="M18 16v10a17 17 0 0 0 34 0V16"/><rect x="12" y="7" width="12" height="15" rx="3"/><rect x="46" y="7" width="12" height="15" rx="3"/><path d="M15 12h6m28 0h6"/></svg>
        </div>
        <div class="home-compatibility-preview__accessory home-compatibility-preview__accessory--charger">
          <svg viewBox="0 0 70 70"><rect x="17" y="22" width="36" height="37" rx="8"/><path d="M27 22V12m16 10V12M28 40h14m-7-7v14"/></svg>
        </div>

        <div class="home-compatibility-preview__float-card home-compatibility-preview__float-card--device">
          <span class="home-compatibility-preview__mini-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="6" y="2.8" width="12" height="18.4" rx="2.4"/><path d="M10 6h4"/></svg>
          </span>
          <span><small>دستگاه انتخابی</small><strong>گوشی هوشمند</strong></span>
        </div>
        <div class="home-compatibility-preview__float-card home-compatibility-preview__float-card--status">
          <span class="home-compatibility-preview__status-dot" aria-hidden="true"></span>
          <span><small>نمونه وضعیت</small><strong>سازگار / مشروط</strong></span>
        </div>
      </div>

      <div class="home-compatibility-preview__visual-footer">
        <span class="home-compatibility-preview__footer-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="m12 3 2.3 5.7L20 11l-5.7 2.3L12 19l-2.3-5.7L4 11l5.7-2.3L12 3Z"/></svg>
        </span>
        <span><strong>انتخاب با اطمینان بیشتر</strong><small>این تصویر صرفاً نمایشی است؛ نتیجه‌ها در ابزار اختصاصی بررسی می‌شوند.</small></span>
      </div>
    </div>
  </div>
</section>
