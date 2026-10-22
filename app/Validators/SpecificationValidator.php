<?php

declare(strict_types=1);

final class SpecificationValidator implements ValidatorInterface
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        $specGroupId =
            $data['specGroupId'] ?? null;

        $name =
            trim((string)($data['name'] ?? ''));

        $slug =
            trim((string)($data['slug'] ?? ''));

        $valueType =
            $data['valueType'] ?? 'text';

        $unit =
            $data['unit'] ?? null;

        $status =
            $data['status'] ?? 'active';

        $sortOrder =
            $data['sortOrder'] ?? 0;

        if (
            filter_var(
                $specGroupId,
                FILTER_VALIDATE_INT
            ) === false
            || (int)$specGroupId <= 0
        ) {
            $errors['specGroupId'] =
                'شناسه گروه مشخصات باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        if ($name === '') {
            $errors['name'] =
                'نام مشخصات الزامی است.';
        } elseif (mb_strlen($name) > 150) {
            $errors['name'] =
                'نام مشخصات نباید بیشتر از 150 کاراکتر باشد.';
        }

        if ($slug === '') {
            $errors['slug'] =
                'Slug مشخصات الزامی است.';
        } elseif (mb_strlen($slug) > 180) {
            $errors['slug'] =
                'Slug مشخصات نباید بیشتر از 180 کاراکتر باشد.';
        }

        if (
            !in_array(
                $valueType,
                [
                    'text',
                    'number',
                    'boolean',
                    'date',
                ],
                true
            )
        ) {
            $errors['valueType'] =
                'نوع مقدار مشخصات نامعتبر است.';
        }

        if (
            $unit !== null
            && mb_strlen((string)$unit) > 30
        ) {
            $errors['unit'] =
                'واحد مشخصات نباید بیشتر از 30 کاراکتر باشد.';
        }

        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت مشخصات نامعتبر است.';
        }

        if (
            filter_var(
                $sortOrder,
                FILTER_VALIDATE_INT
            ) === false
            || (int)$sortOrder < 0
        ) {
            $errors['sortOrder'] =
                'ترتیب نمایش باید عدد صحیح بزرگ‌تر یا مساوی صفر باشد.';
        }

        return $errors;
    }
}
