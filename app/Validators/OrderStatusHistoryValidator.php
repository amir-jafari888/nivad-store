<?php

declare(strict_types=1);

final class OrderStatusHistoryValidator
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
        int $orderId,
        ?string $fromStatus,
        string $toStatus,
        ?int $changedByUserId = null,
        ?string $note = null
    ): array {
        $errors = [];

        if ($orderId <= 0) {
            $errors['order_id'] =
                'شناسه سفارش نامعتبر است.';
        }

        if (
            $fromStatus !== null
            && !in_array(
                $fromStatus,
                self::STATUSES,
                true
            )
        ) {
            $errors['from_status'] =
                'وضعیت قبلی سفارش نامعتبر است.';
        }

        if (
            !in_array(
                $toStatus,
                self::STATUSES,
                true
            )
        ) {
            $errors['to_status'] =
                'وضعیت جدید سفارش نامعتبر است.';
        }

        if (
            $changedByUserId !== null
            && $changedByUserId <= 0
        ) {
            $errors['changed_by_user_id'] =
                'شناسه کاربر تغییر‌دهنده نامعتبر است.';
        }

        if (
            $note !== null
            && mb_strlen($note) > 1000
        ) {
            $errors['note'] =
                'یادداشت نمی‌تواند بیشتر از 1000 کاراکتر باشد.';
        }

        return $errors;
    }
}
