<?php

declare(strict_types=1);

final class CouponValidator implements ValidatorInterface
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        $code =
            trim(
                (string)($data['code'] ?? '')
            );

        $title =
            isset($data['title'])
            ? trim(
                (string)$data['title']
            )
            : null;

        $description =
            isset($data['description'])
            ? trim(
                (string)$data['description']
            )
            : null;

        $discountType =
            trim(
                (string)(
                    $data['discount_type']
                    ?? ''
                )
            );

        $discountValue =
            $data['discount_value']
            ?? null;

        $minimumOrderAmount =
            $data['minimum_order_amount']
            ?? null;

        $maximumDiscountAmount =
            $data['maximum_discount_amount']
            ?? null;

        $usageLimit =
            $data['usage_limit']
            ?? null;

        $usageLimitPerUser =
            $data['usage_limit_per_user']
            ?? null;

        $startsAt =
            isset($data['starts_at']) &&
            $data['starts_at'] !== ''
            ? trim(
                (string)$data['starts_at']
            )
            : null;

        $expiresAt =
            isset($data['expires_at']) &&
            $data['expires_at'] !== ''
            ? trim(
                (string)$data['expires_at']
            )
            : null;

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? Coupon::STATUS_ACTIVE
                )
            );

        if ($code === '') {
            $errors['code'] =
                'کد تخفیف الزامی است.';
        } elseif (mb_strlen($code) > 100) {
            $errors['code'] =
                'کد تخفیف نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.';
        }

        if (
            $title !== null &&
            mb_strlen($title) > 150
        ) {
            $errors['title'] =
                'عنوان کوپن نمی‌تواند بیشتر از ۱۵۰ کاراکتر باشد.';
        }

        if (
            $description !== null &&
            mb_strlen($description) > 500
        ) {
            $errors['description'] =
                'توضیحات کوپن نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.';
        }

        if (
            !in_array(
                $discountType,
                [
                    Coupon::DISCOUNT_TYPE_PERCENTAGE,
                    Coupon::DISCOUNT_TYPE_FIXED,
                ],
                true
            )
        ) {
            $errors['discount_type'] =
                'نوع تخفیف نامعتبر است.';
        }

        if (
            !$this->isValidDecimal(
                $discountValue
            )
        ) {
            $errors['discount_value'] =
                'مقدار تخفیف نامعتبر است.';
        } elseif (
            (float)$discountValue <= 0
        ) {
            $errors['discount_value'] =
                'مقدار تخفیف باید بیشتر از صفر باشد.';
        }

        if (
            $discountType ===
            Coupon::DISCOUNT_TYPE_PERCENTAGE &&
            $this->isValidDecimal($discountValue) &&
            (
                (float)$discountValue > 100
            )
        ) {
            $errors['discount_value'] =
                'تخفیف درصدی نمی‌تواند بیشتر از ۱۰۰ باشد.';
        }

        if (
            $minimumOrderAmount !== null &&
            $minimumOrderAmount !== '' &&
            (
                !$this->isValidDecimal(
                    $minimumOrderAmount
                ) ||
                (float)$minimumOrderAmount < 0
            )
        ) {
            $errors['minimum_order_amount'] =
                'حداقل مبلغ سفارش نامعتبر است.';
        }

        if (
            $maximumDiscountAmount !== null &&
            $maximumDiscountAmount !== '' &&
            (
                !$this->isValidDecimal(
                    $maximumDiscountAmount
                ) ||
                (float)$maximumDiscountAmount < 0
            )
        ) {
            $errors['maximum_discount_amount'] =
                'حداکثر مبلغ تخفیف نامعتبر است.';
        }

        if (
            $usageLimit !== null &&
            $usageLimit !== '' &&
            (
                !filter_var(
                    $usageLimit,
                    FILTER_VALIDATE_INT
                ) &&
                (string)$usageLimit !== '0'
                ||
                (int)$usageLimit < 0
            )
        ) {
            $errors['usage_limit'] =
                'محدودیت استفاده نامعتبر است.';
        }

        if (
            $usageLimitPerUser !== null &&
            $usageLimitPerUser !== '' &&
            (
                !filter_var(
                    $usageLimitPerUser,
                    FILTER_VALIDATE_INT
                ) &&
                (string)$usageLimitPerUser !== '0'
                ||
                (int)$usageLimitPerUser < 0
            )
        ) {
            $errors['usage_limit_per_user'] =
                'محدودیت استفاده هر کاربر نامعتبر است.';
        }

        if (
            !in_array(
                $status,
                [
                    Coupon::STATUS_ACTIVE,
                    Coupon::STATUS_INACTIVE,
                    Coupon::STATUS_EXPIRED,
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت کوپن نامعتبر است.';
        }

        if (
            $startsAt !== null &&
            !$this->isValidDateTime($startsAt)
        ) {
            $errors['starts_at'] =
                'تاریخ شروع کوپن نامعتبر است.';
        }

        if (
            $expiresAt !== null &&
            !$this->isValidDateTime($expiresAt)
        ) {
            $errors['expires_at'] =
                'تاریخ پایان کوپن نامعتبر است.';
        }

        if (
            $startsAt !== null &&
            $expiresAt !== null &&
            $this->isValidDateTime($startsAt) &&
            $this->isValidDateTime($expiresAt) &&
            strtotime($expiresAt) <
            strtotime($startsAt)
        ) {
            $errors['expires_at'] =
                'تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.';
        }

        return $errors;
    }

    private function isValidDecimal(
        mixed $value
    ): bool {
        if (
            $value === null ||
            $value === ''
        ) {
            return false;
        }

        return preg_match(
            '/^\d+(?:\.\d{1,4})?$/',
            (string)$value
        ) === 1;
    }

    private function isValidDateTime(
        string $value
    ): bool {
        $date =
            DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $value
            );

        return
            $date !== false &&
            $date->format(
                'Y-m-d H:i:s'
            ) === $value;
    }
}
