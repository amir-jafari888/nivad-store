<?php

declare(strict_types=1);

function input_string(
    mixed $value,
    int $maxLength = 255
): string {
    if (!is_string($value)) {
        return '';
    }

    $value = trim($value);

    if ($value === '') {
        return '';
    }

    return mb_substr($value, 0, $maxLength);
}

function input_int(
    mixed $value,
    ?int $default = null
): ?int {
    if (
        filter_var(
            $value,
            FILTER_VALIDATE_INT
        ) === false
    ) {
        return $default;
    }

    return (int) $value;
}

function input_email(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);

    if (
        $value === '' ||
        filter_var($value, FILTER_VALIDATE_EMAIL) === false
    ) {
        return null;
    }

    return $value;
}
