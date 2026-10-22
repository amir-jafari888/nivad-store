<?php

declare(strict_types=1);

final class ProductAttributeValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $name = isset($data['name'])
            ? trim((string) $data['name'])
            : '';

        $slug = isset($data['slug'])
            ? trim((string) $data['slug'])
            : '';

        $type = isset($data['type'])
            ? trim((string) $data['type'])
            : '';

        $status = isset($data['status'])
            ? trim((string) $data['status'])
            : '';

        if ($name === '') {
            $errors['name'] = 'نام ویژگی الزامی است.';
        }

        if ($slug === '') {
            $errors['slug'] = 'اسلاگ ویژگی الزامی است.';
        }

        if ($type === '') {
            $errors['type'] = 'نوع ویژگی الزامی است.';
        }

        if ($status === '') {
            $errors['status'] = 'وضعیت ویژگی الزامی است.';
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
