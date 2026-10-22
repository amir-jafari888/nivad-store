<?php

declare(strict_types=1);

final class WishlistItemValidator
{
    public function validate(array $data): array
    {
        $errors = [];

        $wishlistId = $data['wishlistId'] ?? null;
        $variantId = $data['variantId'] ?? null;

        if (
            filter_var(
                $wishlistId,
                FILTER_VALIDATE_INT
            ) === false
            || (int)$wishlistId <= 0
        ) {
            $errors['wishlistId'] =
                'شناسه Wishlist باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        if (
            filter_var(
                $variantId,
                FILTER_VALIDATE_INT
            ) === false
            || (int)$variantId <= 0
        ) {
            $errors['variantId'] =
                'شناسه Variant باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        return $errors;
    }
}
