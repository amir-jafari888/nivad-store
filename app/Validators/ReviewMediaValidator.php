<?php

declare(strict_types=1);

final class ReviewMediaValidator
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        $reviewId =
            filter_var(
                $data['reviewId'] ?? null,
                FILTER_VALIDATE_INT
            );

        if (
            $reviewId === false ||
            $reviewId < 1
        ) {
            $errors['reviewId'] =
                'شناسه دیدگاه نامعتبر است.';
        }

        $mediaType =
            trim(
                (string)($data['mediaType'] ?? '')
            );

        if (
            !in_array(
                $mediaType,
                [
                    ReviewMedia::MEDIA_TYPE_IMAGE,
                    ReviewMedia::MEDIA_TYPE_VIDEO,
                ],
                true
            )
        ) {
            $errors['mediaType'] =
                'نوع Media نامعتبر است.';
        }

        $filePath =
            trim(
                (string)($data['filePath'] ?? '')
            );

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
                trim(
                    (string)$data['thumbnailPath']
                );

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
                trim(
                    (string)$data['altText']
                );

            if (mb_strlen($altText) > 255) {
                $errors['altText'] =
                    'Alt Text بیش از حد مجاز است.';
            }
        }

        $sortOrder =
            filter_var(
                $data['sortOrder'] ?? 0,
                FILTER_VALIDATE_INT
            );

        if (
            $sortOrder === false ||
            $sortOrder < 0
        ) {
            $errors['sortOrder'] =
                'Sort Order نامعتبر است.';
        }

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? ReviewMedia::STATUS_PENDING
                )
            );

        if (
            !in_array(
                $status,
                [
                    ReviewMedia::STATUS_PENDING,
                    ReviewMedia::STATUS_APPROVED,
                    ReviewMedia::STATUS_REJECTED,
                    ReviewMedia::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت Media نامعتبر است.';
        }

        return $errors;
    }

    public static function validateStatus(
        string $status
    ): array {
        $status = trim($status);

        if (
            !in_array(
                $status,
                [
                    ReviewMedia::STATUS_PENDING,
                    ReviewMedia::STATUS_APPROVED,
                    ReviewMedia::STATUS_REJECTED,
                    ReviewMedia::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            return [
                'status' =>
                'وضعیت Media نامعتبر است.',
            ];
        }

        return [];
    }
}
