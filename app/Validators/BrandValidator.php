<?php

declare(strict_types=1);

final class BrandValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $name = trim(
            (string)($data['name'] ?? '')
        );

        if ($name === '') {
            $errors['name'] =
                'نام برند الزامی است.';
        }

        $slug = trim(
            (string)($data['slug'] ?? '')
        );

        if ($slug === '') {
            $errors['slug'] =
                'اسلاگ برند الزامی است.';
        }

        $status = trim(
            (string)($data['status'] ?? 'active')
        );

        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت برند نامعتبر است.';
        }

        $sortOrder = filter_var(
            $data['sortOrder'] ?? 0,
            FILTER_VALIDATE_INT
        );

        if (
            $sortOrder === false ||
            $sortOrder < 0
        ) {
            $errors['sortOrder'] =
                'ترتیب برند نامعتبر است.';
        }

        return $errors;
    }
}
