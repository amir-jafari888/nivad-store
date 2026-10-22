<?php

declare(strict_types=1);

final class CheckoutValidator
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validate(array $data): array
    {
        $errors = [];

        $userId = $data['userId'] ?? null;

        if (
            !is_int($userId) ||
            $userId <= 0
        ) {
            $errors['userId'] =
                'شناسه کاربر نامعتبر است.';
        }

        return $errors;
    }
}
