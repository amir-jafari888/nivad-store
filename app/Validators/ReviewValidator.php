<?php

declare(strict_types=1);

final class ReviewValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $productId = filter_var(
            $data['productId'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($productId === false || $productId < 1) {
            $errors['productId'] =
                'شناسه محصول باید یک عدد صحیح معتبر باشد.';
        }

        $userId = filter_var(
            $data['userId'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($userId === false || $userId < 1) {
            $errors['userId'] =
                'شناسه کاربر باید یک عدد صحیح معتبر باشد.';
        }

        if (
            array_key_exists('orderItemId', $data) &&
            $data['orderItemId'] !== null &&
            $data['orderItemId'] !== ''
        ) {
            $orderItemId = filter_var(
                $data['orderItemId'],
                FILTER_VALIDATE_INT
            );

            if (
                $orderItemId === false ||
                $orderItemId < 1
            ) {
                $errors['orderItemId'] =
                    'شناسه آیتم سفارش نامعتبر است.';
            }
        }

        $rating = filter_var(
            $data['rating'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $rating === false ||
            $rating < 1 ||
            $rating > 5
        ) {
            $errors['rating'] =
                'امتیاز باید عددی بین ۱ تا ۵ باشد.';
        }

        $body = trim(
            (string)($data['body'] ?? '')
        );

        if ($body === '') {
            $errors['body'] =
                'متن دیدگاه الزامی است.';
        }

        if (array_key_exists('title', $data)) {
            $title = trim(
                (string)$data['title']
            );

            if (mb_strlen($title) > 255) {
                $errors['title'] =
                    'عنوان دیدگاه نباید بیشتر از ۲۵۵ کاراکتر باشد.';
            }
        }

        $status = trim(
            (string)(
                $data['status']
                ?? Review::STATUS_PENDING
            )
        );

        if (
            !in_array(
                $status,
                [
                    Review::STATUS_PENDING,
                    Review::STATUS_APPROVED,
                    Review::STATUS_REJECTED,
                    Review::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت دیدگاه نامعتبر است.';
        }

        if (array_key_exists('isVerifiedPurchase', $data)) {
            $verified = $data['isVerifiedPurchase'];

            if (
                !is_bool($verified) &&
                $verified !== 0 &&
                $verified !== 1 &&
                $verified !== '0' &&
                $verified !== '1'
            ) {
                $errors['isVerifiedPurchase'] =
                    'وضعیت خرید تأییدشده نامعتبر است.';
            }
        }

        if (array_key_exists('adminNote', $data)) {
            $adminNote = $data['adminNote'];

            if ($adminNote !== null) {
                $adminNote = trim(
                    (string)$adminNote
                );

                if (mb_strlen($adminNote) > 1000) {
                    $errors['adminNote'] =
                        'یادداشت مدیر نباید بیشتر از ۱۰۰۰ کاراکتر باشد.';
                }
            }
        }

        return $errors;
    }

    public static function validateStatus(
        string $status
    ): array {
        $errors = [];

        $status = trim($status);

        if (
            !in_array(
                $status,
                [
                    Review::STATUS_PENDING,
                    Review::STATUS_APPROVED,
                    Review::STATUS_REJECTED,
                    Review::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت دیدگاه نامعتبر است.';
        }

        return $errors;
    }

    public static function validateAdminNote(
        ?string $adminNote
    ): array {
        $errors = [];

        if (
            $adminNote !== null &&
            mb_strlen(trim($adminNote)) > 1000
        ) {
            $errors['adminNote'] =
                'یادداشت مدیر نباید بیشتر از ۱۰۰۰ کاراکتر باشد.';
        }

        return $errors;
    }
}
