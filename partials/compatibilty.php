<section
    class="compatibility section"
    aria-labelledby="compatibility-title">

    <div class="container">

        <div class="section-heading compatibility-heading">
            <span class="section-eyebrow">COMPATIBILITY</span>

            <h2 id="compatibility-title">
                چی به دستگاه من می‌خوره؟
            </h2>

            <p>
                دستگاهت رو انتخاب کن تا محصولات سازگار، سازگاری جزئی و توضیحات مربوط به هر مورد رو ببینی.
            </p>
        </div>

        <div class="compatibility-card" data-compatibility-card>

            <div class="compatibility-step">
                <span
                    class="compatibility-step-number"
                    aria-hidden="true">
                    01
                </span>

                <div class="compatibility-step-content">

                    <label for="device-brand">
                        برند دستگاه
                    </label>

                    <div class="compatibility-select">

                        <select
                            id="device-brand"
                            data-compatibility-brand
                            autocomplete="organization"
                            aria-describedby="compatibility-selection-help">

                            <option value="">
                                انتخاب برند
                            </option>

                        </select>

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>

                    </div>

                </div>
            </div>

            <div
                class="compatibility-divider"
                aria-hidden="true">
            </div>

            <div class="compatibility-step">

                <span
                    class="compatibility-step-number"
                    aria-hidden="true">
                    02
                </span>

                <div class="compatibility-step-content">

                    <label for="device-model">
                        مدل دستگاه
                    </label>

                    <div class="compatibility-select">

                        <select
                            id="device-model"
                            data-compatibility-model
                            disabled
                            aria-describedby="compatibility-selection-help">

                            <option value="">
                                ابتدا برند را انتخاب کن
                            </option>

                        </select>

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>

                    </div>

                </div>
            </div>

            <p
                id="compatibility-selection-help"
                class="compatibility-selection-help">

                ابتدا برند و سپس مدل دستگاه را انتخاب کن.

            </p>

            <button
                class="compatibility-submit"
                type="button"
                data-compatibility-submit
                disabled
                aria-busy="false">

                <span>
                    نمایش محصولات سازگار
                </span>

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true">

                    <path d="M5 12h14" />
                    <path d="m13 6 6 6-6 6" />

                </svg>

            </button>

        </div>

        <div
            class="compatibility-result"
            data-compatibility-result
            aria-live="polite"
            aria-busy="false"
            hidden>

            <div class="compatibility-result-header">

                <div>

                    <span>
                        نتیجه سازگاری
                    </span>

                    <strong data-compatibility-result-title>
                        محصولات سازگار
                    </strong>

                    <small data-compatibility-device-summary>
                    </small>

                </div>

                <button
                    class="compatibility-reset"
                    type="button"
                    data-compatibility-reset>

                    تغییر دستگاه

                </button>

            </div>

            <div
                class="compatibility-summary"
                data-compatibility-summary
                hidden>
            </div>

            <div
                class="compatibility-filters"
                data-compatibility-filters
                role="group"
                aria-label="فیلتر نوع سازگاری"
                hidden>
            </div>

            <div
                class="compatibility-products"
                data-compatibility-products>
            </div>

        </div>

    </div>

</section>