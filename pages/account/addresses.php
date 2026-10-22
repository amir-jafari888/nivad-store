<?php
$pageTitle = 'آدرس‌های من';
$baseUrl = $baseUrl ?? '';
?>

<section class="account-page account-addresses-page">
    <div class="container">
        <div class="account-page__header">
            <h1>آدرس‌های من</h1>
            <p>آدرس‌های ارسال خود را مدیریت کنید.</p>
        </div>

        <div
            class="account-addresses"
            data-account-addresses></div>

        <form
            class="account-form"
            data-account-address-form>
            <input
                type="hidden"
                name="id"
                value="">

            <div class="account-form__grid">
                <label>
                    <span>عنوان</span>
                    <input name="title" required>
                </label>

                <label>
                    <span>نام گیرنده</span>
                    <input
                        name="recipient_name"
                        required>
                </label>

                <label>
                    <span>شماره تماس</span>
                    <input
                        name="phone"
                        required>
                </label>

                <label>
                    <span>استان</span>
                    <input
                        name="province"
                        required>
                </label>

                <label>
                    <span>شهر</span>
                    <input
                        name="city"
                        required>
                </label>

                <label>
                    <span>کد پستی</span>
                    <input
                        name="postal_code"
                        required>
                </label>

                <label>
                    <span>شماره ساختمان</span>
                    <input name="building_number">
                </label>

                <label>
                    <span>واحد</span>
                    <input name="unit">
                </label>

                <label class="account-form__full">
                    <span>آدرس</span>
                    <textarea
                        name="address"
                        rows="4"
                        required></textarea>
                </label>
            </div>

            <label class="account-form__checkbox">
                <input
                    type="checkbox"
                    name="is_default"
                    value="1">
                <span>آدرس پیش‌فرض</span>
            </label>

            <div
                class="account-form__message"
                data-account-address-message></div>

            <button type="submit">
                ذخیره آدرس
            </button>

            <button
                type="button"
                data-account-address-cancel>
                انصراف
            </button>
        </form>
    </div>
</section>