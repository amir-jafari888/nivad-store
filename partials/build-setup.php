<section
    class="setup-builder"
    id="setup-builder"
    aria-labelledby="setup-builder-title">

    <header class="setup-builder__header">
        <p class="setup-builder__eyebrow">Smart Setup</p>

        <h1 id="setup-builder-title">
            ساخت یک ترکیب مناسب
        </h1>

        <p class="setup-builder__description">
            دستگاهت را انتخاب کن، نیازهایت را مشخص کن و ترکیب مناسب را بساز.
        </p>
    </header>

    <nav
        class="setup-builder__steps"
        aria-label="مراحل ساخت ستاپ">

        <ol id="setup-step-list">
            <li>
                <button
                    type="button"
                    data-setup-step="device"
                    aria-current="step">
                    <span>۱</span>
                    <strong>دستگاه</strong>
                </button>
            </li>

            <li>
                <button
                    type="button"
                    data-setup-step="needs"
                    disabled>
                    <span>۲</span>
                    <strong>نیازها</strong>
                </button>
            </li>

            <li>
                <button
                    type="button"
                    data-setup-step="products"
                    disabled>
                    <span>۳</span>
                    <strong>محصولات</strong>
                </button>
            </li>

            <li>
                <button
                    type="button"
                    data-setup-step="setup"
                    disabled>
                    <span>۴</span>
                    <strong>ستاپ</strong>
                </button>
            </li>

            <li>
                <button
                    type="button"
                    data-setup-step="budget"
                    disabled>
                    <span>۵</span>
                    <strong>بودجه</strong>
                </button>
            </li>

            <li>
                <button
                    type="button"
                    data-setup-step="final"
                    disabled>
                    <span>۶</span>
                    <strong>نتیجه نهایی</strong>
                </button>
            </li>
        </ol>
    </nav>

    <div
        id="setup-global-error"
        class="setup-builder__state setup-builder__state--error"
        role="alert"
        aria-live="assertive"
        hidden>
    </div>

    <!-- DEVICE -->

    <section
        class="setup-builder__step"
        data-setup-panel="device"
        aria-labelledby="setup-device-title">

        <div class="setup-builder__section-header">
            <h2 id="setup-device-title">
                دستگاه خود را انتخاب کنید
            </h2>

            <p>
                دستگاه اصلی خود را مشخص کنید تا پیشنهادهای مرتبط را بررسی کنیم.
            </p>
        </div>

        <div
            id="setup-device-loading"
            class="setup-builder__state setup-builder__state--loading"
            role="status"
            aria-live="polite"
            hidden>
            در حال دریافت دستگاه‌ها...
        </div>

        <div
            id="setup-device-empty"
            class="setup-builder__state setup-builder__state--empty"
            role="status"
            aria-live="polite"
            hidden>
            دستگاهی برای انتخاب وجود ندارد.
        </div>

        <div
            id="setup-device-error"
            class="setup-builder__state setup-builder__state--error"
            role="alert"
            aria-live="assertive"
            hidden>
        </div>

        <button
            type="button"
            id="setup-device-retry"
            class="setup-builder__retry"
            hidden>
            تلاش مجدد
        </button>

        <div
            id="setup-device-options"
            class="setup-builder__options"
            role="list"
            aria-label="دستگاه‌های موجود">
        </div>
    </section>

    <!-- NEEDS -->

    <section
        class="setup-builder__step"
        data-setup-panel="needs"
        aria-labelledby="setup-needs-title"
        hidden>

        <div class="setup-builder__section-header">
            <h2 id="setup-needs-title">
                چه چیزی نیاز داری؟
            </h2>

            <p>
                نیازهای اصلی خود را مشخص کن تا کاندیداهای مناسب را بررسی کنیم.
            </p>
        </div>

        <div
            id="setup-needs-options"
            class="setup-builder__options"
            role="list"
            aria-label="نیازهای ستاپ">
        </div>

        <div
            id="setup-needs-empty"
            class="setup-builder__state setup-builder__state--empty"
            role="status"
            hidden>
            برای این دستگاه نیاز مشخصی ثبت نشده است.
        </div>
    </section>

    <!-- PRODUCTS -->

    <section
        class="setup-builder__step"
        data-setup-panel="products"
        aria-labelledby="setup-products-title"
        hidden>

        <div class="setup-builder__section-header">
            <h2 id="setup-products-title">
                محصولات مناسب
            </h2>

            <p>
                محصولات بر اساس رابطه Setup و اطلاعات سازگاری موجود بررسی می‌شوند.
            </p>
        </div>

        <div
            id="setup-products-checking"
            class="setup-builder__state setup-builder__state--loading"
            role="status"
            aria-live="polite"
            hidden>
            در حال بررسی محصولات...
        </div>

        <div
            id="setup-products-error"
            class="setup-builder__state setup-builder__state--error"
            role="alert"
            aria-live="assertive"
            hidden>
        </div>

        <button
            type="button"
            id="setup-products-retry"
            class="setup-builder__retry"
            hidden>
            بررسی مجدد
        </button>

        <div
            id="setup-product-options"
            class="setup-builder__options"
            role="list"
            aria-label="محصولات پیشنهادی">
        </div>
    </section>

    <!-- SETUP -->

    <section
        class="setup-builder__step"
        data-setup-panel="setup"
        aria-labelledby="setup-composition-title"
        hidden>

        <div class="setup-builder__section-header">
            <h2 id="setup-composition-title">
                ترکیب ستاپ
            </h2>

            <p>
                اقلام انتخاب‌شده را بررسی، حذف یا جایگزین کن.
            </p>
        </div>

        <div
            id="setup-composition"
            class="setup-builder__composition"
            aria-live="polite">
        </div>

        <div
            id="setup-composition-state"
            class="setup-builder__state"
            role="status"
            hidden>
        </div>
    </section>

    <!-- BUDGET -->

    <section
        class="setup-builder__step"
        data-setup-panel="budget"
        aria-labelledby="setup-budget-title"
        hidden>

        <div class="setup-builder__section-header">
            <h2 id="setup-budget-title">
                بودجه
            </h2>

            <p>
                سقف بودجه خود را مشخص کن تا وضعیت مالی ترکیب فعلی را ببینی.
            </p>
        </div>

        <label
            class="setup-builder__field"
            for="setup-budget-input">

            <span>
                بودجه
            </span>

            <input
                id="setup-budget-input"
                type="number"
                min="0"
                step="1"
                inputmode="numeric"
                autocomplete="off">
        </label>

        <div
            id="setup-budget-summary"
            class="setup-builder__budget-summary"
            aria-live="polite">
        </div>
    </section>

    <!-- FINAL -->

    <section
        class="setup-builder__step"
        data-setup-panel="final"
        aria-labelledby="setup-final-title"
        hidden>

        <div class="setup-builder__section-header">
            <h2 id="setup-final-title">
                ستاپ نهایی
            </h2>

            <p>
                قبل از افزودن به سبد، اطلاعات نهایی را بررسی کن.
            </p>
        </div>

        <div
            id="setup-final-summary"
            class="setup-builder__final-summary"
            aria-live="polite">
        </div>

        <div
            id="setup-final-error"
            class="setup-builder__state setup-builder__state--error"
            role="alert"
            aria-live="assertive"
            hidden>
        </div>

        <button
            type="button"
            id="setup-add-to-cart"
            class="setup-builder__add-to-cart"
            disabled>
            افزودن ستاپ به سبد خرید
        </button>
    </section>

    <footer class="setup-builder__navigation">

        <button
            type="button"
            id="setup-back"
            class="setup-builder__navigation-button"
            hidden>
            مرحله قبل
        </button>

        <button
            type="button"
            id="setup-next"
            class="setup-builder__navigation-button setup-builder__navigation-button--primary">
            ادامه
        </button>

    </footer>

</section>