<?php

declare(strict_types=1);

final class Validator
{
    private array $errors = [];

    public function required(
        string $field,
        mixed $value
    ): self {
        if (
            $value === null ||
            (is_string($value) && trim($value) === '')
        ) {
            $this->errors[$field][] = 'این فیلد الزامی است.';
        }

        return $this;
    }

    public function string(
        string $field,
        mixed $value
    ): self {
        if ($value !== null && !is_string($value)) {
            $this->errors[$field][] = 'مقدار باید متنی باشد.';
        }

        return $this;
    }

    public function minLength(
        string $field,
        mixed $value,
        int $min
    ): self {
        if (
            is_string($value) &&
            mb_strlen($value) < $min
        ) {
            $this->errors[$field][] =
                "حداقل طول این فیلد {$min} کاراکتر است.";
        }

        return $this;
    }

    public function maxLength(
        string $field,
        mixed $value,
        int $max
    ): self {
        if (
            is_string($value) &&
            mb_strlen($value) > $max
        ) {
            $this->errors[$field][] =
                "حداکثر طول این فیلد {$max} کاراکتر است.";
        }

        return $this;
    }

    public function email(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            (
                !is_string($value) ||
                filter_var(
                    $value,
                    FILTER_VALIDATE_EMAIL
                ) === false
            )
        ) {
            $this->errors[$field][] =
                'فرمت ایمیل صحیح نیست.';
        }

        return $this;
    }

    public function integer(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            filter_var(
                $value,
                FILTER_VALIDATE_INT
            ) === false
        ) {
            $this->errors[$field][] =
                'مقدار باید عدد صحیح باشد.';
        }

        return $this;
    }

    public function in(
        string $field,
        mixed $value,
        array $allowed
    ): self {
        if (
            $value !== null &&
            !in_array($value, $allowed, true)
        ) {
            $this->errors[$field][] =
                'مقدار انتخاب‌شده معتبر نیست.';
        }

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
