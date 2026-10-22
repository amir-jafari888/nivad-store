<?php

declare(strict_types=1);

final class InventoryReservationValidator
implements ValidatorInterface
{
    private const STATUSES = [
        'active',
        'released',
        'converted',
        'expired',
        'cancelled',
    ];

    public function validate(
        array $data
    ): array {
        $errors = [];

        if (
            !isset($data['variantId']) ||
            !is_numeric($data['variantId']) ||
            (int) $data['variantId'] <= 0
        ) {
            $errors['variantId'] =
                'variantId must be a positive integer.';
        }

        if (
            isset($data['cartId']) &&
            $data['cartId'] !== null &&
            (
                !is_numeric($data['cartId']) ||
                (int) $data['cartId'] <= 0
            )
        ) {
            $errors['cartId'] =
                'cartId must be a positive integer.';
        }

        if (
            isset($data['orderId']) &&
            $data['orderId'] !== null &&
            (
                !is_numeric($data['orderId']) ||
                (int) $data['orderId'] <= 0
            )
        ) {
            $errors['orderId'] =
                'orderId must be a positive integer.';
        }

        if (
            !isset($data['quantity']) ||
            !is_numeric($data['quantity']) ||
            (int) $data['quantity'] < 0
        ) {
            $errors['quantity'] =
                'quantity must be a non-negative integer.';
        }

        if (
            isset($data['status']) &&
            (
                !is_string($data['status']) ||
                !in_array(
                    $data['status'],
                    self::STATUSES,
                    true
                )
            )
        ) {
            $errors['status'] =
                'Invalid status.';
        }

        return $errors;
    }
}
