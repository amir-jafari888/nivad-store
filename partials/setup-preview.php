<?php
/**
 * Homepage introduction to the unified NIVAD Smart Setup experience.
 * This is a teaser only; the interactive builder lives on its dedicated page.
 */
$smartSetupUrl = url('/setup');
?>
<section class="home-setup-preview" aria-labelledby="home-setup-preview-title">
    <div class="home-setup-preview__inner">
        <div class="home-setup-preview__copy">
            <span class="home-setup-preview__eyebrow">
                <span class="home-setup-preview__eyebrow-mark" aria-hidden="true">✦</span>
                NIVAD SMART SETUP
            </span>

            <h2 id="home-setup-preview-title">
                ستاپ تو،<br class="home-setup-preview__desktop-break">
                انتخاب تو.
            </h2>

            <p class="home-setup-preview__description">
                نیازت رو مشخص کن، بودجه‌ات رو تعیین کن و محصولات سازگار رو کنار هم بچین؛
                قبل از خرید، ترکیب نهایی و هزینه‌اش رو بررسی کن.
            </p>

            <ul class="home-setup-preview__benefits" aria-label="مزیت‌های ساخت ستاپ هوشمند">
                <li>
                    <span class="home-setup-preview__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="9"/></svg>
                    </span>
                    <span>انتخاب بر اساس نیاز واقعی تو</span>
                </li>
                <li>
                    <span class="home-setup-preview__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h10M7 13h6"/></svg>
                    </span>
                    <span>بررسی بودجه و هزینه ترکیب</span>
                </li>
                <li>
                    <span class="home-setup-preview__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m12 3 2.3 5.7L20 11l-5.7 2.3L12 19l-2.3-5.7L4 11l5.7-2.3L12 3Z"/><path d="m19 16 .8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8L19 16Z"/></svg>
                    </span>
                    <span>گزینه‌های مناسب و سازگار</span>
                </li>
            </ul>

            <div class="home-setup-preview__actions">
                <a class="home-setup-preview__cta" href="<?= e($smartSetupUrl) ?>">
                    <span>ستاپ خودت رو بساز</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
                </a>
                <span class="home-setup-preview__action-note">از انتخاب نیاز تا بررسی هزینه</span>
            </div>
        </div>

        <div class="home-setup-preview__visual" role="img" aria-label="نمای مفهومی مراحل ساخت ستاپ، مدیریت بودجه و بررسی ترکیب محصولات">
            <div class="home-setup-preview__visual-glow" aria-hidden="true"></div>
            <div class="home-setup-preview__visual-heading">
                <div>
                    <span class="home-setup-preview__visual-kicker">YOUR PERSONAL BUILD</span>
                    <h3>یک ستاپ؛ متناسب با تو</h3>
                </div>
                <span class="home-setup-preview__visual-spark" aria-hidden="true">✦</span>
            </div>

            <div class="home-setup-preview__device-stage" aria-hidden="true">
                <div class="home-setup-preview__device-orbit home-setup-preview__device-orbit--outer"></div>
                <div class="home-setup-preview__device-orbit home-setup-preview__device-orbit--inner"></div>
                <div class="home-setup-preview__device home-setup-preview__device--screen">
                    <svg viewBox="0 0 120 92"><rect x="12" y="8" width="96" height="62" rx="5"/><path d="M47 83h26m-13-13v13M7 84h106"/><path d="M22 19h76v39H22z" class="home-setup-preview__screen-fill"/><path d="m36 44 12-12 10 9 14-14 18 17" class="home-setup-preview__screen-line"/></svg>
                </div>
                <div class="home-setup-preview__device home-setup-preview__device--keyboard">
                    <svg viewBox="0 0 120 48"><path d="M8 8h104l6 28a4 4 0 0 1-4 5H6a4 4 0 0 1-4-5L8 8Z"/><path d="M15 16h8m7 0h8m7 0h8m7 0h8m7 0h8m7 0h8m7 0h8M13 24h8m7 0h8m7 0h8m7 0h8m7 0h8m7 0h8m7 0h8M30 32h60"/></svg>
                </div>
                <div class="home-setup-preview__device home-setup-preview__device--mouse">
                    <svg viewBox="0 0 48 68"><path d="M24 3C12 3 6 12 6 25v18c0 13 7 22 18 22s18-9 18-22V25C42 12 36 3 24 3Z"/><path d="M24 4v17m0-17v9"/></svg>
                </div>

                <div class="home-setup-preview__floating-card home-setup-preview__floating-card--need">
                    <span class="home-setup-preview__floating-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 9h8m-8 4h5"/></svg></span>
                    <span><strong>نیاز تو</strong><small>انتخاب شخصی</small></span>
                    <span class="home-setup-preview__floating-check" aria-hidden="true">✓</span>
                </div>
                <div class="home-setup-preview__floating-card home-setup-preview__floating-card--budget">
                    <span class="home-setup-preview__floating-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18m-5 4h2"/></svg></span>
                    <span><strong>بودجه تو</strong><small>قابل بررسی</small></span>
                </div>
                <div class="home-setup-preview__floating-card home-setup-preview__floating-card--match">
                    <span class="home-setup-preview__match-mark" aria-hidden="true">✓</span>
                    <span><strong>ترکیب هماهنگ</strong><small>نیاز + بودجه</small></span>
                </div>
            </div>

            <div class="home-setup-preview__flow" aria-label="مراحل Smart Setup">
                <div class="home-setup-preview__flow-step">
                    <span>۰۱</span><strong>نیاز</strong>
                </div>
                <span class="home-setup-preview__flow-connector" aria-hidden="true"></span>
                <div class="home-setup-preview__flow-step">
                    <span>۰۲</span><strong>بودجه</strong>
                </div>
                <span class="home-setup-preview__flow-connector" aria-hidden="true"></span>
                <div class="home-setup-preview__flow-step">
                    <span>۰۳</span><strong>ستاپ نهایی</strong>
                </div>
            </div>
        </div>
    </div>
</section>
