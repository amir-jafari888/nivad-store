<?php

declare(strict_types=1);

final class CollectionValidator
{
    public static function validate(
        array $data
    ): array {
        $errors = [];

        $name =
            trim((string)($data['name'] ?? ''));

        $slug =
            trim((string)($data['slug'] ?? ''));

        $type =
            trim(
                (string)(
                    $data['collection_type']
                    ?? ''
                )
            );

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? ''
                )
            );

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        if ($name === '') {
            $errors['name'] =
                'نام مجموعه الزامی است.';
        }

        if ($slug === '') {
            $errors['slug'] =
                'نامک مجموعه الزامی است.';
        } elseif (
            !preg_match(
                '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $slug
            )
        ) {
            $errors['slug'] =
                'نامک مجموعه نامعتبر است.';
        }

        if (
            !in_array(
                $type,
                [
                    Collection::TYPE_MANUAL,
                    Collection::TYPE_AUTOMATIC,
                ],
                true
            )
        ) {
            $errors['collection_type'] =
                'نوع مجموعه نامعتبر است.';
        }

        if (
            !in_array(
                $status,
                [
                    Collection::STATUS_ACTIVE,
                    Collection::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت مجموعه نامعتبر است.';
        }

        if ($sortOrder < 0) {
            $errors['sort_order'] =
                'ترتیب مجموعه نامعتبر است.';
        }

        return $errors;
    }

    public static function validateProductMapping(
        int $collectionId,
        int $productId,
        int $sortOrder = 0
    ): array {
        $errors = [];

        if ($collectionId <= 0) {
            $errors['collection_id'] =
                'شناسه مجموعه نامعتبر است.';
        }

        if ($productId <= 0) {
            $errors['product_id'] =
                'شناسه محصول نامعتبر است.';
        }

        if ($sortOrder < 0) {
            $errors['sort_order'] =
                'ترتیب محصول نامعتبر است.';
        }

        return $errors;
    }
}
