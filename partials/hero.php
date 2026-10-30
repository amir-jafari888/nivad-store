<?php $cmsContent = CmsContentRenderer::activeContent(); ?>
<main>
    <section class="hero section" aria-labelledby="hero-title">
        <div class="container">
            <div class="hero-slider swiper" data-hero-slider>
                <div class="swiper-wrapper">

                    <article class="hero-slide swiper-slide">
                        <img
                            class="hero-slide__visual"
                            src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=1800&q=85"
                            alt="گوشی هوشمند مدرن" />

                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <span class="hero-eyebrow" data-cms-field="eyebrow"><?= e((string)($cmsContent['eyebrow'] ?? 'انتخاب هوشمند')) ?></span>

                            <h1 id="hero-title" class="hero-title" data-cms-field="heading">
                                <?= e((string)($cmsContent['heading'] ?? 'گوشی مناسب تو، همین‌جاست')) ?>
                            </h1>

                            <p class="hero-description" data-cms-field="body">
                                <?= e((string)($cmsContent['body'] ?? 'مدل‌ها و امکانات را مقایسه کن و انتخابت را با خیال راحت انجام بده.')) ?>
                            </p>

                            <div class="hero-actions">
                                <a
                                    href="<?= e(url((string)($cmsContent['button_url'] ?? '/pages/store/products/index.php'))) ?>"
                                    class="hero-button hero-button-primary" data-cms-field="button_label">
                                    <?= e((string)($cmsContent['button_label'] ?? 'مشاهده محصولات')) ?>
                                </a>

                                <a
                                    href="<?= e(url('/pages/store/discover/show.php')) ?>"
                                    class="hero-button hero-button-secondary">
                                    شروع انتخاب هوشمند
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="hero-slide swiper-slide">
                        <img
                            class="hero-slide__visual"
                            src="https://images.unsplash.com/photo-1592286927505-2fdc44c3f7a6?auto=format&fit=crop&w=1800&q=85"
                            alt="گوشی هوشمند پرچمدار" />

                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <span class="hero-eyebrow">گوشی‌های منتخب</span>

                            <h2 class="hero-title">
                                برای هر بودجه، یک انتخاب مطمئن
                            </h2>

                            <p class="hero-description">
                                مدل‌های منتخب را بر اساس قیمت، کاربرد و امکانات پیدا کن.
                            </p>

                            <div class="hero-actions">
                                <a
                                    href="<?= e(url('/pages/store/products/index.php')) ?>"
                                    class="hero-button hero-button-primary">
                                    دیدن گوشی‌ها
                                </a>

                                <a
                                    href="<?= e(url('/pages/store/products/index.php?category=phones')) ?>"
                                    class="hero-button hero-button-secondary">
                                    مقایسه مدل‌ها
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="hero-slide swiper-slide">
                        <img
                            class="hero-slide__visual"
                            src="https://images.unsplash.com/photo-1609592424436-5b8b4f2b3f6c?auto=format&fit=crop&w=1800&q=85"
                            alt="تجهیزات دیجیتال و لوازم جانبی" />

                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <span class="hero-eyebrow">تجهیزات دیجیتال</span>

                            <h2 class="hero-title">
                                جزئیات کوچک، تجربه‌ای بهتر
                            </h2>

                            <p class="hero-description">
                                لوازم جانبی کاربردی برای کامل‌تر کردن تجربه استفاده از دستگاه‌ها.
                            </p>

                            <div class="hero-actions">
                                <a
                                    href="<?= e(url('/pages/store/products/index.php')) ?>"
                                    class="hero-button hero-button-primary">
                                    مشاهده تجهیزات
                                </a>

                                <a
                                    href="<?= e(url('/pages/store/compatibility.php')) ?>"
                                    class="hero-button hero-button-secondary">
                                    بررسی سازگاری
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="hero-slide swiper-slide">
                        <img
                            class="hero-slide__visual"
                            src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1800&q=85"
                            alt="خرید آنلاین محصولات دیجیتال" />

                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <span class="hero-eyebrow">پیشنهاد ویژه</span>

                            <h2 class="hero-title">
                                زمان خوب برای یک انتخاب تازه
                            </h2>

                            <p class="hero-description">
                                پیشنهادهای منتخب را ببین و محصول مناسب نیازت را سریع‌تر پیدا کن.
                            </p>

                            <div class="hero-actions">
                                <a
                                    href="<?= e(url('/pages/store/products/index.php')) ?>"
                                    class="hero-button hero-button-primary">
                                    مشاهده پیشنهادها
                                </a>

                                <a
                                    href="<?= e(url('/pages/store/discover/show.php')) ?>"
                                    class="hero-button hero-button-secondary">
                                    کشف محصولات
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="hero-slide swiper-slide">
                        <img
                            class="hero-slide__visual"
                            src="https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&w=1800&q=85"
                            alt="محصولات دیجیتال جدید" />

                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <span class="hero-eyebrow">لوازم جانبی</span>

                            <h2 class="hero-title">
                                اکسسوری‌هایی که واقعاً به کار می‌آیند
                            </h2>

                            <p class="hero-description">
                                کابل، شارژر و تجهیزات کاربردی را متناسب با دستگاهت پیدا کن.
                            </p>

                            <div class="hero-actions">
                                <a
                                    href="<?= e(url('/pages/store/products/index.php')) ?>"
                                    class="hero-button hero-button-primary">
                                    مشاهده لوازم جانبی
                                </a>

                                <a
                                    href="<?= e(url('/pages/store/compatibility.php')) ?>"
                                    class="hero-button hero-button-secondary">
                                    بررسی سازگاری
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="hero-slide swiper-slide">
                        <img
                            class="hero-slide__visual"
                            src="https://images.unsplash.com/photo-1593642532744-d377ab507dc8?auto=format&fit=crop&w=1800&q=85"
                            alt="محصولات دیجیتال برای کار و زندگی" />

                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <span class="hero-eyebrow">محصولات جدید</span>

                            <h2 class="hero-title">
                                چیزهای تازه برای دنیای دیجیتال
                            </h2>

                            <p class="hero-description">
                                جدیدترین محصولات را ببین و قبل از انتخاب، مشخصاتشان را بررسی کن.
                            </p>

                            <div class="hero-actions">
                                <a
                                    href="<?= e(url('/pages/store/products/index.php')) ?>"
                                    class="hero-button hero-button-primary">
                                    محصولات جدید
                                </a>

                                <a
                                    href="<?= e(url('/pages/store/discover/show.php')) ?>"
                                    class="hero-button hero-button-secondary">
                                    کشف بیشتر
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="hero-slide swiper-slide">
                        <img
                            class="hero-slide__visual"
                            src="https://images.unsplash.com/photo-1517336714739-489689fd1ca8?auto=format&fit=crop&w=1800&q=85"
                            alt="تجهیزات دیجیتال برای کار و سرگرمی" />

                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <span class="hero-eyebrow">پیشنهادهای شخصی</span>

                            <h2 class="hero-title">
                                از نیازت شروع کن، نه از بین صدها محصول
                            </h2>

                            <p class="hero-description">
                                با مسیرهای انتخاب نیواد سریع‌تر به محصولی برس که واقعاً مناسب توست.
                            </p>

                            <div class="hero-actions">
                                <a
                                    href="<?= e(url('/pages/store/discover/show.php')) ?>"
                                    class="hero-button hero-button-primary">
                                    شروع کشف محصول
                                </a>

                                <a
                                    href="<?= e(url('/pages/store/compatibility.php')) ?>"
                                    class="hero-button hero-button-secondary">
                                    بررسی سازگاری
                                </a>
                            </div>
                        </div>
                    </article>

                </div>

                <div class="hero-controls" data-hero-controls>
                    <div
                        class="hero-navigation"
                        aria-label="کنترل اسلایدها">

                        <button
                            class="hero-nav hero-nav-prev"
                            type="button"
                            aria-label="اسلاید قبلی"
                            data-hero-prev>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="m14 6-6 6 6 6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>

                        <button
                            class="hero-nav hero-nav-next"
                            type="button"
                            aria-label="اسلاید بعدی"
                            data-hero-next>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="m10 6 6 6-6 6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>

                    <div
                        class="hero-pagination"
                        data-hero-pagination
                        aria-label="انتخاب اسلاید">
                    </div>
                </div>
            </div>
        </div>
    </section>