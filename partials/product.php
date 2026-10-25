<section
    class="products section"
    aria-labelledby="products-title"
    data-home-products>

    <div class="container">

        <div class="section-heading products-heading">
            <span class="section-eyebrow">CURATED PRODUCTS</span>

            <h2 id="products-title">محصولات منتخب</h2>

            <p>محصولات فعال فروشگاه با قیمت و قرارداد واقعی نمایش داده می‌شوند.</p>
        </div>

        <div
            class="product-grid"
            data-home-products-grid
            aria-live="polite">
            <div class="product-grid-state">
                در حال دریافت محصولات منتخب...
            </div>
        </div>

        <a
            href="<?= e(url('/products')) ?>"
            class="products-more">
            مشاهده همه محصولات
        </a>
    </div>
</section>