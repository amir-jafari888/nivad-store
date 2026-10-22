<?php

declare(strict_types=1);

final class ProductMediaValidator
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        $productId =
            (int)($data['productId'] ?? 0);

        if ($productId <= 0) {
            $errors['productId'] =
                'شناسه محصول نامعتبر است.';
        }

        if (
            array_key_exists('variantId', $data) &&
            $data['variantId'] !== null &&
            (int)$data['variantId'] <= 0
        ) {
            $errors['variantId'] =
                'شناسه Variant نامعتبر است.';
        }

        $mediaType =
            trim((string)($data['mediaType'] ?? ''));

        if (
            !in_array(
                $mediaType,
                ['image', 'video', '360'],
                true
            )
        ) {
            $errors['mediaType'] =
                'نوع Media نامعتبر است.';
        }

        $filePath =
            trim((string)($data['filePath'] ?? ''));

        if ($filePath === '') {
            $errors['filePath'] =
                'مسیر فایل الزامی است.';
        } elseif (mb_strlen($filePath) > 500) {
            $errors['filePath'] =
                'مسیر فایل بیش از حد مجاز است.';
        }

        if (
            array_key_exists(
                'thumbnailPath',
                $data
            ) &&
            $data['thumbnailPath'] !== null
        ) {
            $thumbnailPath =
                trim((string)$data['thumbnailPath']);

            if (
                mb_strlen($thumbnailPath) > 500
            ) {
                $errors['thumbnailPath'] =
                    'مسیر Thumbnail بیش از حد مجاز است.';
            }
        }

        if (
            array_key_exists(
                'altText',
                $data
            ) &&
            $data['altText'] !== null
        ) {
            $altText =
                trim((string)$data['altText']);

            if (mb_strlen($altText) > 255) {
                $errors['altText'] =
                    'Alt Text بیش از حد مجاز است.';
            }
        }

        $sortOrder =
            (int)($data['sortOrder'] ?? 0);

        if ($sortOrder < 0) {
            $errors['sortOrder'] =
                'Sort Order نامعتبر است.';
        }

        if (
            array_key_exists(
                'isPrimary',
                $data
            ) &&
            !in_array(
                $data['isPrimary'],
                [true, false, 0, 1, '0', '1'],
                true
            )
        ) {
            $errors['isPrimary'] =
                'مقدار Primary نامعتبر است.';
        }

        $status =
            trim((string)($data['status'] ?? ''));

        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت Media نامعتبر است.';
        }

        return $errors;
    }
}
