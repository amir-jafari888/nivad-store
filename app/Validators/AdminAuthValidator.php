<?php

declare(strict_types=1);

final class AdminAuthValidator
{
    public static function normalizeIdentifier(
        mixed $identifier
    ): string {
        if (!is_string($identifier)) {
            return '';
        }

        return trim($identifier);
    }

    public static function normalizePassword(
        mixed $password
    ): string {
        if (!is_string($password)) {
            return '';
        }

        return $password;
    }

    public static function login(
        mixed $identifier,
        mixed $password
    ): array {
        $errors = [];

        $identifier =
            self::normalizeIdentifier(
                $identifier
            );

        $password =
            self::normalizePassword(
                $password
            );

        if ($identifier === '') {
            $errors['identifier'] =
                'نام کاربری یا ایمیل الزامی است.';
        } elseif (
            mb_strlen($identifier) > 190
        ) {
            $errors['identifier'] =
                'نام کاربری یا ایمیل نامعتبر است.';
        }

        if ($password === '') {
            $errors['password'] =
                'رمز عبور الزامی است.';
        } elseif (
            mb_strlen($password) > 255
        ) {
            $errors['password'] =
                'رمز عبور نامعتبر است.';
        }

        return $errors;
    }
}
