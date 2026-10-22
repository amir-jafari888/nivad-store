<?php

declare(strict_types=1);

final class CartItemValidator
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        if (
            !isset($data['cartId']) ||
            !is_int($data['cartId']) ||
            $data['cartId'] <= 0
        ) {
            $errors['cartId'] =
                'شناسه Cart نامعتبر است.';
        }

        if (
            !isset($data['variantId']) ||
            !is_int($data['variantId']) ||
            $data['variantId'] <= 0
        ) {
            $errors['variantId'] =
                'شناسه Variant نامعتبر است.';
        }

        if (
            !isset($data['quantity']) ||
            !is_int($data['quantity']) ||
            $data['quantity'] <= 0
        ) {
            $errors['quantity'] =
                'Quantity باید بزرگ‌تر از صفر باشد.';
        }

        if (
            !isset($data['unitPrice']) ||
            (
                !is_string($data['unitPrice']) &&
                !is_int($data['unitPrice']) &&
                !is_float($data['unitPrice'])
            )
        ) {
            $errors['unitPrice'] =
                'Unit price نامعتبر است.';
        }

        return $errors;
    }
}
