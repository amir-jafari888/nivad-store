<?php $cmsContent = CmsContentRenderer::activeContent(); ?>
<section
    class="categories section"
    aria-labelledby="categories-title"
    data-categories-section
    data-api-base="<?= e(url('/api/index.php')) ?>"
    data-category-base="<?= e(url('/categories')) ?>">

    <div class="container">

        <div class="section-heading">
            <span class="section-eyebrow" data-cms-field="eyebrow">
                <?= e((string)($cmsContent['eyebrow'] ?? 'CATEGORIES')) ?>
            </span>

            <h2 id="categories-title" data-cms-field="heading">
                <?= e((string)($cmsContent['heading'] ?? 'دسته‌بندی‌ها')) ?>
            </h2>

            <p>
                <?= e((string)($cmsContent['body'] ?? 'دسته‌بندی‌های فعال از قرارداد واقعی Storefront دریافت می‌شوند.')) ?>
            </p>
        </div>

        <div
            class="categories-list"
            aria-live="polite">
            <div class="categories-list__state">
                در حال دریافت دسته‌بندی‌ها...
            </div>
        </div>
    </div>
</section>