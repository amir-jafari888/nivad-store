<?php

declare(strict_types=1);

final class ProductAttributeValueValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $attributeId = $data['attributeId'] ?? null;

        $value = isset($data['value'])
            ? trim((string) $data['value'])
            : '';

        $slug = isset($data['slug'])
            ? trim((string) $data['slug'])
            : '';

        $status = isset($data['status'])
            ? trim((string) $data['status'])
            : '';

        if (
            filter_var($attributeId, FILTER_VALIDATE_INT) === false
            || (int) $attributeId <= 0
        ) {
            $errors['attributeId'] =
                'شناسه ویژگی باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        if ($value === '') {
            $errors['value'] = 'مقدار ویژگی الزامی است.';
        }

        if ($slug === '') {
            $errors['slug'] = 'اسلاگ مقدار ویژگی الزامی است.';
        }

        if ($status === '') {
            $errors['status'] = 'وضعیت مقدار ویژگی الزامی است.';
        }

        if (array_key_exists('sortOrder', $data)) {
            $sortOrder = filter_var(
                $data['sortOrder'],
                FILTER_VALIDATE_INT
            );

            if ($sortOrder === false || $sortOrder < 0) {
                $errors['sortOrder'] =
                    'ترتیب نمایش باید یک عدد صحیح صفر یا بیشتر باشد.';
            }
        }

        return $errors;
    }
}
