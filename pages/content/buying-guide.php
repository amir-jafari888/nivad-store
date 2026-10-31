<?php
declare(strict_types=1);

$pageTitle = 'راهنمای خرید | نیواد';
$metaDescription = 'راهنمای انتخاب شارژر، کابل USB-C و Thunderbolt و کابل HDMI بر اساس سازگاری و نیاز واقعی دستگاه.';
include_once __DIR__ . '/../../partials/header.php';

if (CmsContentRenderer::renderPage('buying-guide')) {
    include_once __DIR__ . '/../../partials/footer.php';
    return;
}
?>

<main class="content-page" id="main-content">
    <header class="content-page__head">
        <span class="content-page__eyebrow">راهنمای انتخاب</span>
        <h1>راهنمای خرید</h1>
        <p>پیش از خرید، مشخصات دستگاه، کاربرد موردنظر و استانداردهای پشتیبانی‌شده را بررسی کنید. شکل ظاهری یک درگاه یا کابل به‌تنهایی تضمین‌کننده سازگاری یا سرعت مشخص نیست.</p>
    </header>
    <section class="content-page__section" aria-labelledby="charger">
        <h2 id="charger">چطور شارژر مناسب انتخاب کنم؟</h2>
        <p>توان موردنیاز دستگاه و استانداردهای شارژ پشتیبانی‌شده را در مشخصات سازنده بررسی کنید. شارژر با توان بالاتر لزوماً سرعت بیشتری ایجاد نمی‌کند؛ سرعت نهایی به سازگاری دستگاه، شارژر و کابل بستگی دارد.</p><ul><li>مدل دقیق دستگاه را بررسی کنید.</li><li>پروتکل‌های شارژ پشتیبانی‌شده را با مشخصات شارژر مقایسه کنید.</li><li>توان و مشخصات کابل را نیز در نظر بگیرید.</li></ul>
    </section>

    <section class="content-page__section" aria-labelledby="usb-c-thunderbolt">
        <h2 id="usb-c-thunderbolt">USB-C و Thunderbolt چه تفاوتی دارند؟</h2>
        <p>USB-C شکل کانکتور است؛ Thunderbolt یک فناوری ارتباطی با قابلیت‌ها و الزامات مشخص است. همه درگاه‌ها و کابل‌های USB-C از قابلیت‌ها و سرعت‌های Thunderbolt پشتیبانی نمی‌کنند. مشخصات هر دو دستگاه و گواهی/مشخصات کابل را بررسی کنید.</p>
    </section>

    <section class="content-page__section" aria-labelledby="hdmi">
        <h2 id="hdmi">برای انتخاب کابل HDMI چه نکاتی مهم است؟</h2>
        <p>رزولوشن، نرخ نوسازی، قابلیت‌های تصویری موردنیاز و استانداردهای پشتیبانی‌شده در هر دو دستگاه را مشخص کنید. طول کابل و کیفیت ساخت نیز در انتخاب مؤثرند؛ نام نسخه یا شکل کانکتور به‌تنهایی عملکرد نهایی را تضمین نمی‌کند.</p>
    </section>

    <section class="content-page__section" aria-labelledby="compatibility">
        <h2 id="compatibility">قبل از خرید سازگاری را بررسی کنید</h2>
        <p>مدل دقیق دستگاه و مشخصات ورودی/خروجی آن را بررسی کنید. در صورت تردید، از اطلاعات سازنده کمک بگیرید و تنها بر اساس شباهت ظاهری کانکتورها تصمیم نگیرید.</p><p><a href="<?= e(url('/pages/store/compatibility.php')) ?>">رفتن به ابزار سازگاری</a> · <a href="<?= e(url('/pages/content/faq.php')) ?>">سؤالات متداول</a></p>
    </section>

</main>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
