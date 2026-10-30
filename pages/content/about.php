<?php
declare(strict_types=1);

$pageTitle = 'درباره ما | نیواد';
$metaDescription = 'با فروشگاه نیواد و رویکرد ما برای کمک به انتخاب آگاهانه محصولات دیجیتال آشنا شوید.';
include_once __DIR__ . '/../../partials/header.php';

if (CmsContentRenderer::renderPage('about')) {
    include_once __DIR__ . '/../../partials/footer.php';
    return;
}
?>

<main class="content-page" id="main-content">
    <header class="content-page__head">
        <span class="content-page__eyebrow">درباره نیواد</span>
        <h1>درباره ما</h1>
        <p>نیواد برای ساده‌تر کردن بررسی و انتخاب گوشی و تجهیزات دیجیتال طراحی شده است. هدف این صفحات، ارائه اطلاعات روشن و کمک به مقایسه گزینه‌ها پیش از خرید است.</p>
    </header>
    <section class="content-page__section" aria-labelledby="about-focus">
        <h2 id="about-focus">چه چیزی در فروشگاه پیدا می‌کنید؟</h2>
        <p>در فروشگاه می‌توانید محصولات دیجیتال و لوازم جانبی را بررسی کنید، اطلاعات موجود را مقایسه کنید و از ابزارهای راهنمای انتخاب برای شناخت بهتر سازگاری و کاربرد محصولات کمک بگیرید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="about-principles">
        <h2 id="about-principles">رویکرد ما</h2>
        <p>اطلاعات محصول باید تا حد امکان واضح و قابل بررسی باشد. مشخصات و موجودی نمایش‌داده‌شده در صفحه محصول و مراحل خرید را پیش از ثبت سفارش بررسی کنید؛ در صورت ابهام، از مسیرهای پشتیبانی موجود در سایت کمک بگیرید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="about-links">
        <h2 id="about-links">از کجا شروع کنید؟</h2>
        <ul><li><a href="<?= e(url('/products')) ?>">مشاهده محصولات</a></li><li><a href="<?= e(url('/pages/content/buying-guide.php')) ?>">راهنمای خرید</a></li><li><a href="<?= e(url('/pages/content/faq.php')) ?>">سؤالات متداول</a></li></ul>
    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
