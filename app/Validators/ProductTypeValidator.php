<?php

declare(strict_types=1);
final class ProductTypeValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = 'نام نوع محصول الزامی است.';
        }
        $slug = trim((string)($data['slug'] ?? ''));
        if ($slug === '') {
            $errors['slug'] = 'اسلاگ نوع محصول الزامی است.';
        }
        $status = trim((string)($data['status'] ?? 'active'));
        if (!in_array($status, ['active', 'inactive'], true)) {
            $errors['status'] = 'وضعیت نوع محصول نامعتبر است.';
        }
        $sortOrder = filter_var($data['sortOrder'] ?? 0, FILTER_VALIDATE_INT);
        if ($sortOrder === false || $sortOrder < 0) {
            $errors['sortOrder'] = 'ترتیب نوع محصول نامعتبر است.';
        }
        return $errors;
    }
}
