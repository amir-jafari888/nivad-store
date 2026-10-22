<?php

declare(strict_types=1);

final class WishlistValidator
{
    public function validate(array $data): array
    {
        $errors = [];

        $userId = $data['userId'] ?? null;

        $name = isset($data['name'])
            ? trim((string)$data['name'])
            : '';

        $isDefault = $data['isDefault'] ?? true;

        if (
            filter_var(
                $userId,
                FILTER_VALIDATE_INT
            ) === false
            || (int)$userId <= 0
        ) {
            $errors['userId'] =
                'شناسه User باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        if ($name === '') {
            $errors['name'] =
                'نام Wishlist الزامی است.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] =
                'نام Wishlist نمی‌تواند بیشتر از 100 کاراکتر باشد.';
        }

        if (
            !is_bool($isDefault)
            && !in_array(
                $isDefault,
                [0, 1, '0', '1'],
                true
            )
        ) {
            $errors['isDefault'] =
                'مقدار isDefault باید Boolean باشد.';
        }

        return $errors;
    }
}
