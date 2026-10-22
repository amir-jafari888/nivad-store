<?php

declare(strict_types=1);

final class ProductQuestionValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $productId = (int)($data['productId'] ?? 0);
        $userId = (int)($data['userId'] ?? 0);
        $question = trim((string)($data['question'] ?? ''));

        if ($productId < 1) {
            $errors['productId'] = 'شناسه محصول نامعتبر است.';
        }

        if ($userId < 1) {
            $errors['userId'] = 'شناسه کاربر نامعتبر است.';
        }

        if ($question === '') {
            $errors['question'] = 'متن سوال الزامی است.';
        }

        $status = $data['status'] ?? ProductQuestion::STATUS_PENDING;

        $statusErrors = self::validateStatus((string)$status);

        if ($statusErrors !== []) {
            $errors['status'] = $statusErrors['status'];
        }

        if (array_key_exists('adminNote', $data)) {
            $noteErrors = self::validateAdminNote(
                $data['adminNote']
            );

            if ($noteErrors !== []) {
                $errors['adminNote'] = $noteErrors['adminNote'];
            }
        }

        return $errors;
    }

    public static function validateStatus(
        string $status
    ): array {
        $allowed = [
            ProductQuestion::STATUS_PENDING,
            ProductQuestion::STATUS_APPROVED,
            ProductQuestion::STATUS_REJECTED,
            ProductQuestion::STATUS_HIDDEN,
        ];

        if (!in_array($status, $allowed, true)) {
            return [
                'status' => 'وضعیت سوال نامعتبر است.',
            ];
        }

        return [];
    }

    public static function validateAdminNote(
        mixed $adminNote
    ): array {
        if ($adminNote === null) {
            return [];
        }

        if (!is_string($adminNote)) {
            return [
                'adminNote' => 'یادداشت مدیر نامعتبر است.',
            ];
        }

        if (mb_strlen($adminNote) > 1000) {
            return [
                'adminNote' => 'یادداشت مدیر نباید بیشتر از 1000 کاراکتر باشد.',
            ];
        }

        return [];
    }
}
