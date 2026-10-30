<?php
/**
 * Landing-page introduction to Smart Discovery.
 * Product data is loaded from the existing Discovery API; no demo products/prices are hard-coded.
 */
$cmsContent = CmsContentRenderer::activeContent();
?>
<section
    class="home-discovery"
    aria-labelledby="home-discovery-title"
    data-discovery-preview
    data-api-base="<?= e(url('/api/index.php')) ?>">

    <div class="home-discovery__inner">
        <div class="home-discovery__copy">
            <span class="home-discovery__eyebrow"><?= e((string)($cmsContent['eyebrow'] ?? 'NIVAD SMART DISCOVERY')) ?></span>
            <h2 id="home-discovery-title"><?= e((string)($cmsContent['heading'] ?? 'نمی‌دونی کدوم محصول به کارت میاد؟')) ?></h2>
            <p class="home-discovery__description"><?= e((string)($cmsContent['body'] ?? 'نیازت رو انتخاب کن؛ محصولات مناسب خودت رو پیدا کن.')) ?></p>

            <ul class="home-discovery__benefits" aria-label="مزیت‌های کشف هوشمند">
                <li>
                    <span class="home-discovery__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="9"/></svg>
                    </span>
                    <span>پیشنهادها بر اساس نیاز تو</span>
                </li>
                <li>
                    <span class="home-discovery__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="m19 15 .9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9L19 15Z"/></svg>
                    </span>
                    <span>انتخاب ساده‌تر و سریع‌تر</span>
                </li>
                <li>
                    <span class="home-discovery__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="7.2"/><path d="m16 16 5 5"/></svg>
                    </span>
                    <span>پیدا کردن گزینه‌های مرتبط</span>
                </li>
            </ul>

            <a class="home-discovery__cta" href="<?= e(url('/discover')) ?>">
                <span>پیدا کردن محصول مناسب من</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
            </a>
            <a class="home-discovery__help" href="<?= e(url('/discover')) ?>">
                چطور کار می‌کنه؟
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.7 9a2.4 2.4 0 0 1 4.6.9c0 1.7-2.3 2-2.3 3.7M12 17h.01"/></svg>
            </a>
        </div>

        <div class="home-discovery__showcase" aria-label="پیش‌نمایش کشف هوشمند">
            <div class="home-discovery__showcase-glow" aria-hidden="true"></div>
            <div class="home-discovery__showcase-top">
                <div>
                    <span class="home-discovery__showcase-kicker">SMART MATCHING</span>
                    <h3>پیشنهادهای مناسب تو <span aria-hidden="true">✦</span></h3>
                </div>
                <span class="home-discovery__result-count" data-discovery-preview-count aria-live="polite">در حال بررسی…</span>
            </div>

            <div class="home-discovery__filters-shell">
                <button type="button" class="home-discovery__filter-nav home-discovery__filter-nav--prev" aria-label="کاربرد قبلی" data-preview-filter-prev>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                </button>
                <div class="home-discovery__filters swiper" data-preview-filters aria-label="فیلتر کاربرد محصولات">
                    <div class="swiper-wrapper">
                        <button type="button" class="home-discovery__filter swiper-slide is-active" data-preview-category="work" aria-pressed="true">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V4h8v3M3 12h18m-11 0v2h4v-2"/></svg>
                            <span><strong>کار</strong><small>Work</small></span>
                        </button>
                        <button type="button" class="home-discovery__filter swiper-slide" data-preview-category="gaming" aria-pressed="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9h12a4 4 0 0 1 3.8 5.2l-1 3a2 2 0 0 1-3.2 1l-3-2.2h-5l-3 2.2a2 2 0 0 1-3.2-1l-1-3A4 4 0 0 1 6 9Z"/><path d="M7 12v4m-2-2h4m7-1h.01M18 15h.01"/></svg>
                            <span><strong>گیمینگ</strong><small>Gaming</small></span>
                        </button>
                        <button type="button" class="home-discovery__filter swiper-slide" data-preview-category="travel" aria-pressed="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 3-7 18-3.5-7.5L3 10l18-7Z"/><path d="m10.5 13.5 5-5"/></svg>
                            <span><strong>سفر</strong><small>Travel</small></span>
                        </button>
                        <button type="button" class="home-discovery__filter swiper-slide" data-preview-category="content" aria-pressed="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="12" cy="12" r="3.5"/><path d="M7 5l1.5-2h7L17 5"/></svg>
                            <span><strong>محتوا</strong><small>Content</small></span>
                        </button>
                    </div>
                </div>
                <button type="button" class="home-discovery__filter-nav home-discovery__filter-nav--next" aria-label="کاربرد بعدی" data-preview-filter-next>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                </button>
            </div>

            <div class="home-discovery__products-head">
                <h4>محصولاتی که به کارت میان</h4>
                <a href="<?= e(url('/discover')) ?>">همه پیشنهادها <span aria-hidden="true">←</span></a>
            </div>

            <div class="home-discovery__state" data-preview-state role="status" aria-live="polite" hidden></div>
            <div class="home-discovery__products swiper" data-preview-products aria-label="محصولات پیشنهادی">
                <div class="swiper-wrapper" data-preview-products-wrapper></div>
            </div>
            <div class="home-discovery__product-controls">
                <button type="button" class="home-discovery__product-nav" data-preview-product-prev aria-label="محصول قبلی">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                </button>
                <div class="home-discovery__pagination" data-preview-pagination aria-label="موقعیت اسلایدها"></div>
                <button type="button" class="home-discovery__product-nav" data-preview-product-next aria-label="محصول بعدی">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                </button>
            </div>
            <a class="home-discovery__mobile-cta" href="<?= e(url('/discover')) ?>">
                پیدا کردن محصول مناسب من
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
            </a>
        </div>
    </div>
</section>
