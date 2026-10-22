<section
    class="budget-optimizer"
    data-budget-optimizer
    aria-labelledby="budget-optimizer-title">
    <div class="budget-optimizer__container">

        <header class="budget-optimizer__header">
            <span class="budget-optimizer__eyebrow">
                Smart Commerce
            </span>

            <h1 id="budget-optimizer-title">
                بهترین ترکیب با بودجه شما
            </h1>

            <p>
                بودجه خود را مشخص کنید تا گزینه‌های سازگار
                و قابل خرید در محدوده بودجه بررسی شوند.
            </p>
        </header>

        <form
            class="budget-optimizer__form"
            data-budget-optimizer-form
            novalidate>
            <div class="budget-optimizer__field">
                <label for="budget-optimizer-device">
                    دستگاه
                </label>

                <select
                    id="budget-optimizer-device"
                    name="device_id"
                    data-budget-optimizer-device
                    required>
                    <option value="">
                        انتخاب دستگاه
                    </option>
                </select>
            </div>

            <div class="budget-optimizer__field">
                <label for="budget-optimizer-budget">
                    بودجه
                </label>

                <input
                    id="budget-optimizer-budget"
                    name="budget"
                    type="number"
                    min="0"
                    step="0.01"
                    inputmode="decimal"
                    autocomplete="off"
                    data-budget-optimizer-budget
                    required>
            </div>

            <button
                type="submit"
                class="budget-optimizer__submit"
                data-budget-optimizer-submit>
                محاسبه بهترین ترکیب
            </button>
        </form>

        <div
            class="budget-optimizer__state"
            data-budget-optimizer-state
            role="status"
            aria-live="polite"
            aria-busy="false"></div>

        <section
            class="budget-optimizer__result"
            data-budget-optimizer-result
            hidden
            aria-labelledby="budget-optimizer-result-title">
            <header class="budget-optimizer__result-header">
                <div>
                    <span class="budget-optimizer__eyebrow">
                        Optimized Setup
                    </span>

                    <h2 id="budget-optimizer-result-title">
                        ترکیب پیشنهادی
                    </h2>
                </div>

                <div
                    class="budget-optimizer__summary"
                    data-budget-optimizer-summary></div>
            </header>

            <div
                class="budget-optimizer__items"
                data-budget-optimizer-items></div>

            <div
                class="budget-optimizer__tradeoff"
                data-budget-optimizer-tradeoff
                hidden></div>

            <div class="budget-optimizer__actions">
                <button
                    type="button"
                    class="budget-optimizer__cart"
                    data-budget-optimizer-cart
                    disabled>
                    افزودن ترکیب به سبد
                </button>

                <button
                    type="button"
                    class="budget-optimizer__reset"
                    data-budget-optimizer-reset>
                    محاسبه مجدد
                </button>
            </div>
        </section>
    </div>
</section>