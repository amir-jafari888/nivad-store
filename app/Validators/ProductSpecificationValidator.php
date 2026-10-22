<?php

declare(strict_types=1);

final class ProductSpecificationValidator implements ValidatorInterface
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        $productId =
            $data['productId'] ?? null;

        $specificationId =
            $data['specificationId'] ?? null;

        if (
            filter_var(
                $productId,
                FILTER_VALIDATE_INT
            ) === false
            || (int)$productId <= 0
        ) {
            $errors['productId'] =
                'شناسه Product باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        if (
            filter_var(
                $specificationId,
                FILTER_VALIDATE_INT
            ) === false
            || (int)$specificationId <= 0
        ) {
            $errors['specificationId'] =
                'شناسه Specification باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        if (
            array_key_exists(
                'valueBoolean',
                $data
            )
            && $data['valueBoolean'] !== null
            && !in_array(
                $data['valueBoolean'],
                [true, false, 0, 1, '0', '1'],
                true
            )
        ) {
            $errors['valueBoolean'] =
                'مقدار Boolean نامعتبر است.';
        }

        if (
            array_key_exists(
                'valueDate',
                $data
            )
            && $data['valueDate'] !== null
            && trim((string)$data['valueDate']) === ''
        ) {
            $errors['valueDate'] =
                'مقدار تاریخ نمی‌تواند خالی باشد.';
        }

        return $errors;
    }
}
