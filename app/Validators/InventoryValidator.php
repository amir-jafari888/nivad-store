<?php

declare(strict_types=1);

final class InventoryValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
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
            !isset($data['quantity']) ||
            !is_numeric($data['quantity']) ||
            (int) $data['quantity'] < 0
        ) {
            $errors['quantity'] =
                'quantity must be a non-negative integer.';
        }

        if (
            !isset($data['reservedQuantity']) ||
            !is_numeric($data['reservedQuantity']) ||
            (int) $data['reservedQuantity'] < 0
        ) {
            $errors['reservedQuantity'] =
                'reservedQuantity must be a non-negative integer.';
        }

        if (
            !isset($data['lowStockThreshold']) ||
            !is_numeric($data['lowStockThreshold']) ||
            (int) $data['lowStockThreshold'] < 0
        ) {
            $errors['lowStockThreshold'] =
                'lowStockThreshold must be a non-negative integer.';
        }

        return $errors;
    }
}
