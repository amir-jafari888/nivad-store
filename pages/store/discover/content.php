<section
    class="page-discovery"
    aria-labelledby="discovery-title"
    data-discovery-page
    data-api-base="<?= e(url('/api/index.php')) ?>">

    <div class="page-discovery__container">

        <nav
            class="page-discovery__breadcrumb"
            aria-label="مسیر صفحه">

            <a href="<?= e(url('/')) ?>">
                خانه
            </a>

            <span aria-hidden="true">/</span>

            <span aria-current="page">
                کشف هوشمند
            </span>

        </nav>

        <header class="page-discovery__hero">

            <div class="page-discovery__hero-content">

                <span class="page-discovery__eyebrow">
                    SMART DISCOVERY / 01
                </span>

                <h1
                    id="discovery-title"
                    class="page-discovery__title">
                    محصول را از روی نیازت پیدا کن.
                </h1>

                <p class="page-discovery__description">
                    کاربردت را انتخاب کن؛ نیواد از بین محصولات موجود،
                    گزینه‌هایی را که بیشتر به نیازت نزدیک هستند نمایش می‌دهد.
                </p>

            </div>

            <div
                class="page-discovery__hero-visual"
                aria-hidden="true">

                <span class="page-discovery__orbit page-discovery__orbit--one"></span>
                <span class="page-discovery__orbit page-discovery__orbit--two"></span>

                <span class="page-discovery__hero-core">
                    <small>DISCOVER</small>
                    <strong>NIVAD</strong>
                </span>

            </div>

        </header>

        <section
            class="page-discovery__selector"
            aria-labelledby="discovery-selector-title">

            <div class="page-discovery__selector-heading">

                <div>
                    <span class="page-discovery__section-label">
                        STEP 01
                    </span>

                    <h2 id="discovery-selector-title">
                        برای چه کاری می‌خواهی؟
                    </h2>
                </div>

                <span class="page-discovery__selector-hint">
                    یک کاربرد را انتخاب کن
                </span>

            </div>

            <div
                class="page-discovery__options swiper"
                role="tablist"
                aria-label="انتخاب نیاز"
                data-discovery-options>

                <div class="swiper-wrapper">

                    <button
                        class="page-discovery__option swiper-slide is-active"
                        type="button"
                        role="tab"
                        aria-selected="true"
                        data-discovery="charging"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">01</span>
                        <span class="page-discovery__option-text">شارژ سریع</span>
                    </button>

                    <button
                        class="page-discovery__option swiper-slide"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        data-discovery="gaming"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">02</span>
                        <span class="page-discovery__option-text">گیمینگ</span>
                    </button>

                    <button
                        class="page-discovery__option swiper-slide"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        data-discovery="travel"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">03</span>
                        <span class="page-discovery__option-text">سفر</span>
                    </button>

                    <button
                        class="page-discovery__option swiper-slide"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        data-discovery="work"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">04</span>
                        <span class="page-discovery__option-text">محل کار</span>
                    </button>

                    <button
                        class="page-discovery__option swiper-slide"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        data-discovery="car"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">05</span>
                        <span class="page-discovery__option-text">خودرو</span>
                    </button>

                    <button
                        class="page-discovery__option swiper-slide"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        data-discovery="desk"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">06</span>
                        <span class="page-discovery__option-text">میز کار</span>
                    </button>

                    <button
                        class="page-discovery__option swiper-slide"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        data-discovery="content"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">07</span>
                        <span class="page-discovery__option-text">تولید محتوا</span>
                    </button>

                    <button
                        class="page-discovery__option swiper-slide"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        data-discovery="daily"
                        data-discovery-tags>
                        <span class="page-discovery__option-number">08</span>
                        <span class="page-discovery__option-text">استفاده روزمره</span>
                    </button>

                </div>

            </div>

        </section>

        <section
            class="page-discovery__results-section"
            aria-labelledby="discovery-results-title">

            <header class="page-discovery__results-header">

                <div>

                    <span class="page-discovery__section-label">
                        STEP 02 / MATCHED PRODUCTS
                    </span>

                    <h2 id="discovery-results-title">
                        پیشنهادهای مناسب تو
                    </h2>

                </div>

                <div class="page-discovery__results-meta">

                    <span
                        class="page-discovery__count"
                        data-discovery-count>
                        ۰ محصول
                    </span>

                    <div
                        class="page-discovery__navigation"
                        aria-label="کنترل محصولات">

                        <button
                            type="button"
                            class="discovery-navigation-button discovery-navigation-prev"
                            aria-label="محصول قبلی">

                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path d="m14.5 5-7 7 7 7" />
                            </svg>

                        </button>

                        <button
                            type="button"
                            class="discovery-navigation-button discovery-navigation-next"
                            aria-label="محصول بعدی">

                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path d="m9.5 5 7 7-7 7" />
                            </svg>

                        </button>

                    </div>

                </div>

            </header>

            <div
                class="page-discovery__error"
                data-discovery-error
                role="alert"
                hidden>
            </div>

            <div
                class="page-discovery__results swiper"
                data-discovery-results>

                <div
                    class="swiper-wrapper"
                    data-discovery-results-wrapper>

                    <div class="page-discovery__empty" role="status">
                        در حال دریافت پیشنهادهای واقعی…
                    </div>

                </div>

            </div>

            <button
                type="button"
                class="page-discovery__more"
                data-discovery-load-more
                hidden>
                مشاهده محصولات بیشتر
            </button>

        </section>

    </div>

</section>