<?php

declare(strict_types=1);

final class InventoryMovementValidator
implements ValidatorInterface
{
    private const MOVEMENT_TYPES = [
        'purchase',
        'sale',
        'reservation',
        'release',
        'return',
        'adjustment',
        'damage',
        'transfer',
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
            !isset($data['movementType']) ||
            !is_string($data['movementType']) ||
            !in_array(
                $data['movementType'],
                self::MOVEMENT_TYPES,
                true
            )
        ) {
            $errors['movementType'] =
                'Invalid movementType.';
        }

        if (
            !isset($data['quantity']) ||
            !is_numeric($data['quantity']) ||
            !is_int((int) $data['quantity'])
        ) {
            $errors['quantity'] =
                'quantity must be an integer.';
        }

        if (
            isset($data['referenceId']) &&
            $data['referenceId'] !== null &&
            (
                !is_numeric($data['referenceId']) ||
                (int) $data['referenceId'] <= 0
            )
        ) {
            $errors['referenceId'] =
                'referenceId must be a positive integer.';
        }

        if (
            isset($data['createdBy']) &&
            $data['createdBy'] !== null &&
            (
                !is_numeric($data['createdBy']) ||
                (int) $data['createdBy'] <= 0
            )
        ) {
            $errors['createdBy'] =
                'createdBy must be a positive integer.';
        }

        return $errors;
    }
}
