<?php

declare(strict_types=1);

final class CartValidator
{
    private const STATUSES = [
        'active',
        'converted',
        'abandoned',
        'expired',
    ];

    public function validate(
        array $data
    ): array {
        $errors = [];

        if (
            array_key_exists('userId', $data) &&
            $data['userId'] !== null &&
            (
                !is_int($data['userId']) ||
                $data['userId'] <= 0
            )
        ) {
            $errors['userId'] =
                'شناسه User نامعتبر است.';
        }

        if (
            array_key_exists(
                'sessionTokenHash',
                $data
            ) &&
            $data['sessionTokenHash'] !== null
        ) {
            if (
                !is_string(
                    $data['sessionTokenHash']
                ) ||
                trim(
                    $data['sessionTokenHash']
                ) === ''
            ) {
                $errors['sessionTokenHash'] =
                    'Session token hash نامعتبر است.';
            } elseif (
                strlen(
                    trim(
                        $data['sessionTokenHash']
                    )
                ) !== 64
            ) {
                $errors['sessionTokenHash'] =
                    'Session token hash باید 64 کاراکتر باشد.';
            }
        }

        if (
            array_key_exists('status', $data) &&
            !in_array(
                $data['status'],
                self::STATUSES,
                true
            )
        ) {
            $errors['status'] =
                'Status Cart نامعتبر است.';
        }

        if (
            array_key_exists('currency', $data)
        ) {
            if (
                !is_string($data['currency']) ||
                strlen(
                    trim($data['currency'])
                ) !== 3
            ) {
                $errors['currency'] =
                    'Currency باید دقیقاً 3 کاراکتر باشد.';
            }
        }

        if (
            array_key_exists('expiresAt', $data) &&
            $data['expiresAt'] !== null &&
            (
                !is_string($data['expiresAt']) ||
                trim($data['expiresAt']) === ''
            )
        ) {
            $errors['expiresAt'] =
                'تاریخ انقضا نامعتبر است.';
        }

        return $errors;
    }
}
