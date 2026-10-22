<?php

declare(strict_types=1);

final class Validation
{
    public static function validate(
        array $errors
    ): ValidationResult {
        return new ValidationResult(
            $errors
        );
    }

    public static function validateWith(
        ValidatorInterface $validator,
        array $data
    ): ValidationResult {
        $data = ValidationData::normalize(
            $data
        );

        return self::validate(
            $validator->validate($data)
        );
    }

    public static function request(
        ValidatorInterface $validator
    ): ValidationResult {
        $data = [];

        if (Request::isGet()) {
            $data = $_GET;
        } elseif (Request::isJson()) {
            $data = Request::json();
        } else {
            $data = $_POST;
        }

        return self::validateWith(
            $validator,
            is_array($data)
                ? $data
                : []
        );
    }

    public static function require(
        ValidationResult $result
    ): void {
        if ($result->isValid()) {
            return;
        }

        throw new ValidationException(
            $result->errors()
        );
    }

    public static function requireValid(
        ValidatorInterface $validator,
        array $data
    ): void {
        self::require(
            self::validateWith(
                $validator,
                $data
            )
        );
    }
}
