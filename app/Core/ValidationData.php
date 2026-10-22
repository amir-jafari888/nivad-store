<?php

declare(strict_types=1);

final class ValidationData
{
    public static function normalize(
        array $data
    ): array {
        $normalized = [];

        foreach ($data as $key => $value) {
            $key = trim((string)$key);

            if ($key === '') {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
