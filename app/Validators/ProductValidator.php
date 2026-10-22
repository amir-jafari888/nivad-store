<?php

declare(strict_types=1);

final class ProductValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $categoryId = filter_var(
            $data['categoryId'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($categoryId === false || $categoryId < 1) {
            $errors['categoryId'] =
                'شناسه دسته‌بندی باید یک عدد صحیح معتبر باشد.';
        }

        if (
            array_key_exists('brandId', $data) &&
            $data['brandId'] !== null &&
            $data['brandId'] !== ''
        ) {
            $brandId = filter_var(
                $data['brandId'],
                FILTER_VALIDATE_INT
            );

            if ($brandId === false || $brandId < 1) {
                $errors['brandId'] =
                    'شناسه برند باید یک عدد صحیح معتبر باشد.';
            }
        }

        $name = trim(
            (string)($data['name'] ?? '')
        );

        if ($name === '') {
            $errors['name'] =
                'نام محصول الزامی است.';
        }

        $slug = trim(
            (string)($data['slug'] ?? '')
        );

        if ($slug === '') {
            $errors['slug'] =
                'اسلاگ محصول الزامی است.';
        }

        $status = trim(
            (string)($data['status'] ?? '')
        );

        if ($status === '') {
            $errors['status'] =
                'وضعیت محصول الزامی است.';
        }

        if (array_key_exists('isFeatured', $data)) {
            $value = $data['isFeatured'];

            if (
                !is_bool($value) &&
                $value !== 0 &&
                $value !== 1 &&
                $value !== '0' &&
                $value !== '1'
            ) {
                $errors['isFeatured'] =
                    'وضعیت ویژه بودن محصول نامعتبر است.';
            }
        }

        return $errors;
    }

    public function validateAdmin(array $data): array
    {
        $errors = [];

        $brandId = filter_var(
            $data['brandId'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($brandId === false || $brandId < 1) {
            $errors['brandId'] =
                'شناسه برند باید معتبر باشد.';
        }

        $productTypeId = filter_var(
            $data['productTypeId'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $productTypeId === false ||
            $productTypeId < 1
        ) {
            $errors['productTypeId'] =
                'شناسه نوع محصول باید معتبر باشد.';
        }

        $name = trim(
            (string)($data['name'] ?? '')
        );

        if ($name === '') {
            $errors['name'] =
                'نام محصول الزامی است.';
        }

        $slug = trim(
            (string)($data['slug'] ?? '')
        );

        if ($slug === '') {
            $errors['slug'] =
                'Slug محصول الزامی است.';
        }

        $sku = trim(
            (string)($data['sku'] ?? '')
        );

        if ($sku === '') {
            $errors['sku'] =
                'SKU محصول الزامی است.';
        } elseif (strlen($sku) > 100) {
            $errors['sku'] =
                'SKU نمی‌تواند بیشتر از 100 کاراکتر باشد.';
        }

        $status = trim(
            (string)($data['status'] ?? 'draft')
        );

        if (
            !in_array(
                $status,
                [
                    'draft',
                    'active',
                    'out_of_stock',
                    'discontinued',
                    'archived',
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت محصول نامعتبر است.';
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
                'ترتیب محصول نامعتبر است.';
        }

        return $errors;
    }
}
