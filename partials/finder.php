<section
    class="finder-page section"
    aria-labelledby="finder-title"
    data-finder-page
    data-api-base="<?= e(url('/api/index.php')) ?>">

    <div class="finder-page__container container">

        <nav
            class="finder-page__breadcrumb"
            aria-label="مسیر صفحه">

            <a href="<?= e(url('/')) ?>">
                خانه
            </a>

            <span aria-hidden="true">
                /
            </span>

            <span aria-current="page">
                پیدا کردن محصول
            </span>

        </nav>

        <header class="finder-page__hero">

            <div>

                <span class="finder-page__eyebrow">
                    SMART PRODUCT FINDER
                </span>

                <h1 id="finder-title">
                    محصول مناسب را از روی نیازت پیدا کن.
                </h1>

                <p>
                    نیازت، حدود بودجه و توضیح کوتاهت را بگو؛
                    نیواد محصولات فعال فروشگاه را بررسی می‌کند
                    و گزینه‌های نزدیک‌تر را رتبه‌بندی می‌کند.
                </p>

            </div>

        </header>

        <form
            class="finder-form"
            data-finder-form
            novalidate>

            <section
                class="finder-step"
                aria-labelledby="finder-intent-title">

                <div class="finder-step__heading">

                    <span>
                        01
                    </span>

                    <div>

                        <span class="finder-step__eyebrow">
                            NEED
                        </span>

                        <h2 id="finder-intent-title">
                            برای چه کاری می‌خواهی؟
                        </h2>

                    </div>

                </div>

                <input
                    type="hidden"
                    name="intent"
                    value="daily">

                <div
                    class="finder-intents"
                    role="group"
                    aria-label="نوع نیاز">

                    <button
                        type="button"
                        data-finder-intent="charging"
                        aria-pressed="false">
                        شارژ
                    </button>

                    <button
                        type="button"
                        data-finder-intent="gaming"
                        aria-pressed="false">
                        گیمینگ
                    </button>

                    <button
                        type="button"
                        data-finder-intent="travel"
                        aria-pressed="false">
                        سفر
                    </button>

                    <button
                        type="button"
                        data-finder-intent="work"
                        aria-pressed="false">
                        کار
                    </button>

                    <button
                        type="button"
                        data-finder-intent="car"
                        aria-pressed="false">
                        خودرو
                    </button>

                    <button
                        type="button"
                        data-finder-intent="desk"
                        aria-pressed="false">
                        میز کار
                    </button>

                    <button
                        type="button"
                        data-finder-intent="content"
                        aria-pressed="false">
                        تولید محتوا
                    </button>

                    <button
                        type="button"
                        data-finder-intent="daily"
                        aria-pressed="true"
                        class="is-active">
                        روزمره
                    </button>

                </div>

            </section>

            <section
                class="finder-step"
                aria-labelledby="finder-budget-title">

                <div class="finder-step__heading">

                    <span>
                        02
                    </span>

                    <div>

                        <span class="finder-step__eyebrow">
                            BUDGET
                        </span>

                        <h2 id="finder-budget-title">
                            حدود بودجه‌ات چقدر است؟
                        </h2>

                    </div>

                </div>

                <label for="finder-budget">
                    بودجه
                </label>

                <select
                    id="finder-budget"
                    name="budget">

                    <option value="any">
                        فرقی ندارد
                    </option>

                    <option value="low">
                        تا ۱ میلیون تومان
                    </option>

                    <option value="mid">
                        ۱ تا ۵ میلیون تومان
                    </option>

                    <option value="high">
                        بیشتر از ۵ میلیون تومان
                    </option>

                </select>

            </section>

            <section
                class="finder-step"
                aria-labelledby="finder-query-title">

                <div class="finder-step__heading">

                    <span>
                        03
                    </span>

                    <div>

                        <span class="finder-step__eyebrow">
                            CONTEXT
                        </span>

                        <h2 id="finder-query-title">
                            اگر توضیح بیشتری داری، بگو.
                        </h2>

                    </div>

                </div>

                <label for="finder-query">
                    توضیح نیاز
                </label>

                <input
                    id="finder-query"
                    name="query"
                    type="text"
                    maxlength="160"
                    autocomplete="off"
                    placeholder="مثلاً برای لپ‌تاپ و میز کارم یک هاب و کابل مناسب می‌خواهم.">

            </section>

            <div class="finder-form__actions">

                <button
                    type="submit"
                    class="finder-submit">
                    پیدا کردن محصول مناسب
                </button>

                <button
                    type="button"
                    class="finder-reset"
                    data-finder-reset>
                    پاک کردن انتخاب‌ها
                </button>

            </div>

        </form>

        <section
            class="finder-results-section"
            aria-labelledby="finder-results-title">

            <header class="finder-results__header">

                <div>

                    <span class="finder-step__eyebrow">
                        RECOMMENDATION
                    </span>

                    <h2 id="finder-results-title">
                        پیشنهادهای مناسب تو
                    </h2>

                </div>

                <div class="finder-results__meta">

                    <span data-finder-count>
                        ۰ پیشنهاد
                    </span>

                    <strong data-finder-confidence>
                        هنوز بررسی نشده
                    </strong>

                </div>

            </header>

            <p
                class="finder-status"
                data-finder-status
                role="status"
                aria-live="polite">
                نیازت را انتخاب کن تا پیشنهاد مناسب ساخته شود.
            </p>

            <div
                class="finder-results"
                data-finder-results>
            </div>

        </section>

    </div>

</section>