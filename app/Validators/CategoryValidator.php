<?php

declare(strict_types=1);

final class CategoryValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        if (
            array_key_exists('parentId', $data) &&
            $data['parentId'] !== null &&
            $data['parentId'] !== ''
        ) {
            $parentId = filter_var(
                $data['parentId'],
                FILTER_VALIDATE_INT
            );

            if ($parentId === false || $parentId < 1) {
                $errors['parentId'] =
                    'شناسه والد دسته‌بندی نامعتبر است.';
            }
        }

        $name = trim(
            (string)($data['name'] ?? '')
        );

        if ($name === '') {
            $errors['name'] =
                'نام دسته‌بندی الزامی است.';
        }

        $slug = trim(
            (string)($data['slug'] ?? '')
        );

        if ($slug === '') {
            $errors['slug'] =
                'اسلاگ دسته‌بندی الزامی است.';
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
                'وضعیت دسته‌بندی نامعتبر است.';
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
                'ترتیب دسته‌بندی نامعتبر است.';
        }

        return $errors;
    }
}
