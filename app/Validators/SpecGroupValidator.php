<?php

declare(strict_types=1);

final class SpecGroupValidator implements ValidatorInterface
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        $name = trim((string)($data['name'] ?? ''));
        $slug = trim((string)($data['slug'] ?? ''));
        $status = $data['status'] ?? 'active';
        $sortOrder = $data['sortOrder'] ?? 0;

        if ($name === '') {
            $errors['name'] =
                'نام گروه مشخصات الزامی است.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] =
                'نام گروه مشخصات نباید بیشتر از 100 کاراکتر باشد.';
        }

        if ($slug === '') {
            $errors['slug'] =
                'Slug گروه مشخصات الزامی است.';
        } elseif (mb_strlen($slug) > 120) {
            $errors['slug'] =
                'Slug گروه مشخصات نباید بیشتر از 120 کاراکتر باشد.';
        }

        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت گروه مشخصات نامعتبر است.';
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
