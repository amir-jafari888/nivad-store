<?php

declare(strict_types=1);

final class ProductVariantAttributeValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $variantId = $data['variantId'] ?? null;

        $attributeValueId = $data['attributeValueId'] ?? null;

        if (
            filter_var($variantId, FILTER_VALIDATE_INT) === false
            || (int) $variantId <= 0
        ) {
            $errors['variantId'] =
                'شناسه Variant باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        if (
            filter_var(
                $attributeValueId,
                FILTER_VALIDATE_INT
            ) === false
            || (int) $attributeValueId <= 0
        ) {
            $errors['attributeValueId'] =
                'شناسه مقدار ویژگی باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        return $errors;
    }
}
