<?php

declare(strict_types=1);

final class OrderValidator
{
    private const STATUSES = [
        'pending',
        'confirmed',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
        'returned',
        'refunded',
    ];

    public function validate(
        int $userId,
        string $orderNumber,
        string $status,
        string $currency,
        string $subtotal,
        string $discountAmount,
        string $shippingAmount,
        string $taxAmount,
        string $totalAmount,
        string $recipientName,
        string $recipientPhone,
        string $province,
        string $city,
        string $address,
        string $postalCode,
        ?string $buildingNumber = null,
        ?string $unit = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $customerNote = null
    ): array {
        $errors = [];

        if ($userId <= 0) {
            $errors['user_id'] = 'شناسه کاربر نامعتبر است.';
        }

        if (trim($orderNumber) === '') {
            $errors['order_number'] = 'شماره سفارش الزامی است.';
        } elseif (mb_strlen($orderNumber) > 50) {
            $errors['order_number'] =
                'شماره سفارش نمی‌تواند بیشتر از 50 کاراکتر باشد.';
        }

        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'وضعیت سفارش نامعتبر است.';
        }

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            $errors['currency'] = 'واحد پول نامعتبر است.';
        }

        foreach (
            [
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ] as $field => $value
        ) {
            if (!preg_match('/^\d+(\.\d{1,4})?$/', $value)) {
                $errors[$field] =
                    'مبلغ سفارش باید یک مقدار Decimal معتبر باشد.';
            }
        }

        foreach (
            [
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'province' => $province,
                'city' => $city,
                'address' => $address,
                'postal_code' => $postalCode,
            ] as $field => $value
        ) {
            if (trim($value) === '') {
                $errors[$field] = 'این فیلد الزامی است.';
            }
        }

        if (
            $buildingNumber !== null
            && mb_strlen($buildingNumber) > 30
        ) {
            $errors['building_number'] =
                'شماره ساختمان نامعتبر است.';
        }

        if (
            $unit !== null
            && mb_strlen($unit) > 30
        ) {
            $errors['unit'] =
                'واحد نامعتبر است.';
        }

        if (
            $customerNote !== null
            && mb_strlen($customerNote) > 1000
        ) {
            $errors['customer_note'] =
                'یادداشت مشتری نمی‌تواند بیشتر از 1000 کاراکتر باشد.';
        }

        return $errors;
    }
}
