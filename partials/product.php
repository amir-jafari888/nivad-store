<?php $cmsContent = CmsContentRenderer::activeContent(); ?>
<section
    class="products section"
    aria-labelledby="products-title"
    data-home-products
    data-api-base="<?= e(url('/api/index.php')) ?>"
    data-product-base="<?= e(url('/products')) ?>">

    <div class="container products-shell">
        <header class="products-heading">
            <h2 id="products-title" data-cms-field="heading"><?= e((string)($cmsContent['heading'] ?? 'محصولات منتخب')) ?></h2>
            <div class="products-navigation" aria-label="کنترل اسلایدر محصولات">
                <button type="button" class="products-nav-button" data-products-prev aria-label="محصولات قبلی" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 5-7 7 7 7"></path></svg>
                </button>
                <button type="button" class="products-nav-button" data-products-next aria-label="محصولات بعدی">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 5 7 7-7 7"></path></svg>
                </button>
            </div>
        </header>

        <div class="products-slider swiper" data-home-products-slider aria-label="محصولات منتخب">
            <div class="product-grid swiper-wrapper" data-home-products-grid aria-live="polite" aria-atomic="false">
                <div class="product-grid-state swiper-slide">در حال دریافت محصولات منتخب...</div>
            </div>
        </div>

        <footer class="products-footer">
            <div class="products-pagination" data-products-pagination aria-label="صفحه‌های محصولات"></div>
            <a href="<?= e(url('/products')) ?>" class="products-more">مشاهده همه محصولات</a>
        </footer>
    </div>
</section>