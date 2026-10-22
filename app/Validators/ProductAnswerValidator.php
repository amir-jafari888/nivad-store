<?php

declare(strict_types=1);

final class ProductAnswerValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $questionId = (int)($data['questionId'] ?? 0);
        $answer = trim((string)($data['answer'] ?? ''));

        if ($questionId < 1) {
            $errors['questionId'] = 'شناسه سوال نامعتبر است.';
        }

        if ($answer === '') {
            $errors['answer'] = 'متن پاسخ الزامی است.';
        }

        $status = $data['status'] ?? ProductAnswer::STATUS_PENDING;

        $statusErrors = self::validateStatus((string)$status);

        if ($statusErrors !== []) {
            $errors['status'] = $statusErrors['status'];
        }

        if (
            array_key_exists('adminUserId', $data)
            && $data['adminUserId'] !== null
            && (int)$data['adminUserId'] < 1
        ) {
            $errors['adminUserId'] =
                'شناسه مدیر نامعتبر است.';
        }

        return $errors;
    }

    public static function validateStatus(
        string $status
    ): array {
        $allowed = [
            ProductAnswer::STATUS_PENDING,
            ProductAnswer::STATUS_APPROVED,
            ProductAnswer::STATUS_REJECTED,
            ProductAnswer::STATUS_HIDDEN,
        ];

        if (!in_array($status, $allowed, true)) {
            return [
                'status' => 'وضعیت پاسخ نامعتبر است.',
            ];
        }

        return [];
    }
}
