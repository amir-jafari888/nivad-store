<?php
$pageTitle = 'پروفایل کاربری';
$baseUrl = $baseUrl ?? '';
?>

<section class="account-page account-profile-page">
    <div class="container">
        <div class="account-page__header">
            <h1>پروفایل کاربری</h1>
            <p>اطلاعات حساب کاربری خود را مدیریت کنید.</p>
        </div>

        <form
            class="account-form"
            data-account-profile-form>
            <div class="account-form__grid">
                <label>
                    <span>نام</span>
                    <input
                        type="text"
                        name="first_name"
                        autocomplete="given-name"
                        required>
                </label>

                <label>
                    <span>نام خانوادگی</span>
                    <input
                        type="text"
                        name="last_name"
                        autocomplete="family-name"
                        required>
                </label>

                <label>
                    <span>ایمیل</span>
                    <input
                        type="email"
                        name="email"
                        readonly>
                </label>

                <label>
                    <span>شماره موبایل</span>
                    <input
                        type="text"
                        name="phone"
                        readonly>
                </label>
            </div>

            <div
                class="account-form__message"
                data-account-profile-message></div>

            <button type="submit">
                ذخیره تغییرات
            </button>
        </form>
    </div>
</section>