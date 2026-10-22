<?php

declare(strict_types=1);

final class UserAddressValidator
{
    public function validateIds(
        int $userId,
        ?int $addressId
    ): array {
        $errors = [];

        if ($userId <= 0) {
            $errors['userId'] = 'شناسه کاربر نامعتبر است.';
        }

        if ($addressId !== null && $addressId <= 0) {
            $errors['addressId'] = 'شناسه آدرس نامعتبر است.';
        }

        return $errors;
    }

    public function validateForCheckout(
        UserAddress $address
    ): array {
        $errors = [];

        if ($address->id <= 0) {
            $errors['id'] = 'شناسه آدرس نامعتبر است.';
        }

        if ($address->userId <= 0) {
            $errors['userId'] = 'شناسه کاربر آدرس نامعتبر است.';
        }

        $requiredFields = [
            'title' => $address->title,
            'recipientName' => $address->recipientName,
            'phone' => $address->phone,
            'province' => $address->province,
            'city' => $address->city,
            'address' => $address->address,
            'postalCode' => $address->postalCode,
        ];

        foreach ($requiredFields as $field => $value) {
            if (trim((string)$value) === '') {
                $errors[$field] =
                    'فیلد آدرس نمی‌تواند خالی باشد.';
            }
        }

        return $errors;
    }

    public function validateForAccount(
        array $data
    ): array {
        $errors = [];

        $required = [
            'title',
            'recipient_name',
            'phone',
            'province',
            'city',
            'address',
            'postal_code',
        ];

        foreach ($required as $field) {
            if (
                !isset($data[$field]) ||
                trim((string)$data[$field]) === ''
            ) {
                $errors[$field] =
                    'این فیلد الزامی است.';
            }
        }

        return $errors;
    }
}
