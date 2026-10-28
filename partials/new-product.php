<section
    class="new-products section"
    aria-labelledby="new-products-title">
    <div class="container">
        <div class="section-heading new-products-heading">
            <span class="section-eyebrow">STORE CATALOG</span>
            <h2 id="new-products-title">محصولات فروشگاه</h2>
            <p>برای مشاهده نام، مشخصات و قیمت‌های به‌روز، فهرست محصولات فروشگاه را ببین.</p>
        </div>

        <div class="new-products-list" aria-live="polite">
            <p class="catalog-state" role="status">
                اطلاعات محصولات در فهرست اصلی فروشگاه نمایش داده می‌شود.
            </p>
        </div>

        <a href="<?= e(url('/products')) ?>" class="new-products-more">
            مشاهده همه محصولات
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 12h14" />
                <path d="m13 6 6 6-6 6" />
            </svg>
        </a>
    </div>
</section>
