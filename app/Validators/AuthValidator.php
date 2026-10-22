<?php

declare(strict_types=1);

final class AuthValidator
{
    public static function login(
        mixed $identifier,
        mixed $password
    ): array {
        $errors = [];

        if (!is_string($identifier)) {
            $errors['identifier'] =
                'شناسه ورود الزامی است.';
        } else {
            $identifier = trim($identifier);

            if ($identifier === '') {
                $errors['identifier'] =
                    'شناسه ورود الزامی است.';
            } elseif (mb_strlen($identifier) > 255) {
                $errors['identifier'] =
                    'شناسه ورود نامعتبر است.';
            }
        }

        if (!is_string($password)) {
            $errors['password'] =
                'رمز عبور الزامی است.';
        } elseif ($password === '') {
            $errors['password'] =
                'رمز عبور الزامی است.';
        } elseif (mb_strlen($password) > 255) {
            $errors['password'] =
                'رمز عبور نامعتبر است.';
        }

        return $errors;
    }

    public static function register(
        mixed $firstName,
        mixed $lastName,
        mixed $email,
        mixed $phone,
        mixed $password,
        mixed $passwordConfirmation
    ): array {
        $errors = [];

        if (!is_string($firstName)) {
            $errors['first_name'] =
                'نام الزامی است.';
        } else {
            $firstName = trim($firstName);

            if ($firstName === '') {
                $errors['first_name'] =
                    'نام الزامی است.';
            } elseif (mb_strlen($firstName) > 100) {
                $errors['first_name'] =
                    'نام نامعتبر است.';
            }
        }

        if (!is_string($lastName)) {
            $errors['last_name'] =
                'نام خانوادگی الزامی است.';
        } else {
            $lastName = trim($lastName);

            if ($lastName === '') {
                $errors['last_name'] =
                    'نام خانوادگی الزامی است.';
            } elseif (mb_strlen($lastName) > 100) {
                $errors['last_name'] =
                    'نام خانوادگی نامعتبر است.';
            }
        }

        if (!is_string($email)) {
            $errors['email'] =
                'ایمیل الزامی است.';
        } else {
            $email = trim($email);

            if ($email === '') {
                $errors['email'] =
                    'ایمیل الزامی است.';
            } elseif (
                mb_strlen($email) > 255 ||
                filter_var($email, FILTER_VALIDATE_EMAIL) === false
            ) {
                $errors['email'] =
                    'ایمیل نامعتبر است.';
            }
        }

        if (!is_string($phone)) {
            $errors['phone'] =
                'شماره موبایل الزامی است.';
        } else {
            $phone = trim($phone);

            if ($phone === '') {
                $errors['phone'] =
                    'شماره موبایل الزامی است.';
            } elseif (!preg_match(
                '/^09\d{9}$/',
                $phone
            )) {
                $errors['phone'] =
                    'شماره موبایل نامعتبر است.';
            }
        }

        if (!is_string($password)) {
            $errors['password'] =
                'رمز عبور الزامی است.';
        } elseif ($password === '') {
            $errors['password'] =
                'رمز عبور الزامی است.';
        } elseif (mb_strlen($password) < 8) {
            $errors['password'] =
                'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        } elseif (mb_strlen($password) > 255) {
            $errors['password'] =
                'رمز عبور نامعتبر است.';
        }

        if (!is_string($passwordConfirmation)) {
            $errors['password_confirmation'] =
                'تکرار رمز عبور الزامی است.';
        } elseif ($passwordConfirmation === '') {
            $errors['password_confirmation'] =
                'تکرار رمز عبور الزامی است.';
        } elseif (
            is_string($password) &&
            $passwordConfirmation !== $password
        ) {
            $errors['password_confirmation'] =
                'تکرار رمز عبور با رمز عبور یکسان نیست.';
        }

        return $errors;
    }

    public static function changePassword(
        mixed $currentPassword,
        mixed $newPassword,
        mixed $newPasswordConfirmation
    ): array {
        $errors = [];

        if (!is_string($currentPassword) || $currentPassword === '') {
            $errors['current_password'] =
                'رمز عبور فعلی الزامی است.';
        } elseif (mb_strlen($currentPassword) > 255) {
            $errors['current_password'] =
                'رمز عبور فعلی نامعتبر است.';
        }

        if (!is_string($newPassword) || $newPassword === '') {
            $errors['new_password'] =
                'رمز عبور جدید الزامی است.';
        } elseif (mb_strlen($newPassword) < 8) {
            $errors['new_password'] =
                'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.';
        } elseif (mb_strlen($newPassword) > 255) {
            $errors['new_password'] =
                'رمز عبور جدید نامعتبر است.';
        }

        if (
            !is_string($newPasswordConfirmation) ||
            $newPasswordConfirmation === ''
        ) {
            $errors['new_password_confirmation'] =
                'تکرار رمز عبور جدید الزامی است.';
        } elseif (
            is_string($newPassword) &&
            $newPasswordConfirmation !== $newPassword
        ) {
            $errors['new_password_confirmation'] =
                'تکرار رمز عبور جدید با رمز عبور یکسان نیست.';
        }

        if (
            is_string($currentPassword) &&
            is_string($newPassword) &&
            $currentPassword !== '' &&
            $currentPassword === $newPassword
        ) {
            $errors['new_password'] =
                'رمز عبور جدید باید با رمز فعلی متفاوت باشد.';
        }

        return $errors;
    }

    public static function forgotPassword(
        mixed $email
    ): array {
        $errors = [];

        if (!is_string($email)) {
            $errors['email'] =
                'ایمیل الزامی است.';
            return $errors;
        }

        $email = trim($email);

        if ($email === '') {
            $errors['email'] =
                'ایمیل الزامی است.';
        } elseif (
            mb_strlen($email) > 255 ||
            filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            $errors['email'] =
                'ایمیل نامعتبر است.';
        }

        return $errors;
    }

    public static function resetPassword(
        mixed $token,
        mixed $newPassword,
        mixed $newPasswordConfirmation
    ): array {
        $errors = [];

        if (
            !is_string($token) ||
            trim($token) === ''
        ) {
            $errors['token'] =
                'توکن بازیابی الزامی است.';
        } elseif (
            mb_strlen($token) > 255
        ) {
            $errors['token'] =
                'توکن بازیابی نامعتبر است.';
        }

        if (
            !is_string($newPassword) ||
            $newPassword === ''
        ) {
            $errors['new_password'] =
                'رمز عبور جدید الزامی است.';
        } elseif (
            mb_strlen($newPassword) < 8
        ) {
            $errors['new_password'] =
                'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.';
        } elseif (
            mb_strlen($newPassword) > 255
        ) {
            $errors['new_password'] =
                'رمز عبور جدید نامعتبر است.';
        }

        if (
            !is_string($newPasswordConfirmation) ||
            $newPasswordConfirmation === ''
        ) {
            $errors['new_password_confirmation'] =
                'تکرار رمز عبور جدید الزامی است.';
        } elseif (
            is_string($newPassword) &&
            $newPasswordConfirmation !== $newPassword
        ) {
            $errors['new_password_confirmation'] =
                'تکرار رمز عبور جدید با رمز عبور یکسان نیست.';
        }

        return $errors;
    }

    public static function verifyEmail(
        mixed $token
    ): array {
        $errors = [];

        if (
            !is_string($token) ||
            trim($token) === ''
        ) {
            $errors['token'] =
                'توکن تأیید ایمیل الزامی است.';
        } elseif (
            mb_strlen($token) > 255
        ) {
            $errors['token'] =
                'توکن تأیید ایمیل نامعتبر است.';
        }

        return $errors;
    }

    public static function resendEmailVerification(mixed $email): array
    {
        $errors = [];

        if (!is_string($email) || trim($email) === '') {
            $errors['email'] = 'ایمیل الزامی است.';
            return $errors;
        }

        $email = trim($email);

        if (mb_strlen($email) > 190) {
            $errors['email'] = 'ایمیل نامعتبر است.';
            return $errors;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'فرمت ایمیل نامعتبر است.';
        }

        return $errors;
    }
}
