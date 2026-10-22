<?php

declare(strict_types=1);

final class CouponService
{
    private CouponRepository $repository;

    public function __construct(
        ?CouponRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new CouponRepository();
    }

    public function findById(
        int $couponId
    ): ?Coupon {
        return $this->repository->findById(
            $couponId
        );
    }

    public function findByCode(
        string $code
    ): ?Coupon {
        return $this->repository->findByCode(
            $code
        );
    }

    public function findActiveByCode(
        string $code
    ): ?Coupon {
        $coupon =
            $this->repository->findActiveByCode(
                $code
            );

        if (
            $coupon === null ||
            !$this->isCurrentlyAvailable(
                $coupon
            )
        ) {
            return null;
        }

        return $coupon;
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function create(
        string $code,
        ?string $title,
        ?string $description,
        string $discountType,
        string $discountValue,
        ?string $minimumOrderAmount,
        ?string $maximumDiscountAmount,
        ?int $usageLimit,
        ?int $usageLimitPerUser,
        ?string $startsAt,
        ?string $expiresAt,
        string $status
    ): ?Coupon {
        return $this->repository->create(
            $code,
            $title,
            $description,
            $discountType,
            $discountValue,
            $minimumOrderAmount,
            $maximumDiscountAmount,
            $usageLimit,
            $usageLimitPerUser,
            $startsAt,
            $expiresAt,
            $status
        );
    }

    public function update(
        int $couponId,
        string $code,
        ?string $title,
        ?string $description,
        string $discountType,
        string $discountValue,
        ?string $minimumOrderAmount,
        ?string $maximumDiscountAmount,
        ?int $usageLimit,
        ?int $usageLimitPerUser,
        ?string $startsAt,
        ?string $expiresAt,
        string $status
    ): ?Coupon {
        return $this->repository->update(
            $couponId,
            $code,
            $title,
            $description,
            $discountType,
            $discountValue,
            $minimumOrderAmount,
            $maximumDiscountAmount,
            $usageLimit,
            $usageLimitPerUser,
            $startsAt,
            $expiresAt,
            $status
        );
    }

    public function delete(
        int $couponId
    ): bool {
        return $this->repository->delete(
            $couponId
        );
    }

    public function restore(
        int $couponId
    ): ?Coupon {
        return $this->repository->restore(
            $couponId
        );
    }

    public function updateStatus(
        int $couponId,
        string $status
    ): ?Coupon {
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
            return null;
        }

        return $this->repository->updateStatus(
            $couponId,
            $status
        );
    }

    public function isCurrentlyAvailable(
        Coupon $coupon
    ): bool {
        if (
            $coupon->status !==
            Coupon::STATUS_ACTIVE
        ) {
            return false;
        }

        $now = time();

        if ($coupon->startsAt !== null) {
            $startsAt =
                strtotime($coupon->startsAt);

            if (
                $startsAt !== false &&
                $now < $startsAt
            ) {
                return false;
            }
        }

        if ($coupon->expiresAt !== null) {
            $expiresAt =
                strtotime($coupon->expiresAt);

            if (
                $expiresAt !== false &&
                $now > $expiresAt
            ) {
                return false;
            }
        }

        return true;
    }

    public function validate(
        array $data
    ): array {
        $validator =
            new CouponValidator();

        return $validator->validate(
            $data
        );
    }

    public function calculateDiscount(
        Coupon $coupon,
        string $subtotal
    ): array {
        $subtotal =
            $this->normalizeMoney(
                $subtotal
            );

        if ($subtotal === '0.0000') {
            return [
                'subtotal' => '0.0000',
                'discount_amount' => '0.0000',
                'final_amount' => '0.0000',
            ];
        }

        if (!$this->isCurrentlyAvailable($coupon)) {
            throw new InvalidArgumentException(
                'کوپن در حال حاضر قابل استفاده نیست.'
            );
        }

        if (
            $coupon->minimumOrderAmount !== null &&
            $coupon->minimumOrderAmount !== '' &&
            $this->compareMoney(
                $subtotal,
                $this->normalizeMoney(
                    $coupon->minimumOrderAmount
                )
            ) < 0
        ) {
            return [
                'subtotal' => $subtotal,
                'discount_amount' => '0.0000',
                'final_amount' => $subtotal,
            ];
        }

        if (
            $coupon->discountType ===
            Coupon::DISCOUNT_TYPE_PERCENTAGE
        ) {
            $discountAmount =
                $this->calculatePercentageDiscount(
                    $subtotal,
                    $coupon->discountValue
                );
        } elseif (
            $coupon->discountType ===
            Coupon::DISCOUNT_TYPE_FIXED
        ) {
            $discountAmount =
                $this->normalizeMoney(
                    $coupon->discountValue
                );
        } else {
            throw new InvalidArgumentException(
                'نوع تخفیف کوپن نامعتبر است.'
            );
        }

        if (
            $coupon->maximumDiscountAmount !== null &&
            $coupon->maximumDiscountAmount !== ''
        ) {
            $maximumDiscount =
                $this->normalizeMoney(
                    $coupon->maximumDiscountAmount
                );

            if (
                $this->compareMoney(
                    $discountAmount,
                    $maximumDiscount
                ) > 0
            ) {
                $discountAmount =
                    $maximumDiscount;
            }
        }

        if (
            $this->compareMoney(
                $discountAmount,
                $subtotal
            ) > 0
        ) {
            $discountAmount =
                $subtotal;
        }

        $finalAmount =
            $this->subtractMoney(
                $subtotal,
                $discountAmount
            );

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ];
    }

    private function calculatePercentageDiscount(
        string $subtotal,
        string $percentage
    ): string {
        $percentage =
            $this->normalizeMoney(
                $percentage
            );

        if (
            $this->compareMoney(
                $percentage,
                '0.0000'
            ) <= 0
        ) {
            return '0.0000';
        }

        if (
            $this->compareMoney(
                $percentage,
                '100.0000'
            ) > 0
        ) {
            throw new InvalidArgumentException(
                'درصد تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.'
            );
        }

        $subtotalFloat =
            (float)$subtotal;

        $percentageFloat =
            (float)$percentage;

        $discount =
            $subtotalFloat *
            $percentageFloat /
            100;

        return number_format(
            $discount,
            4,
            '.',
            ''
        );
    }

    private function normalizeMoney(
        string $value
    ): string {
        $value =
            trim($value);

        if (
            !preg_match(
                '/^\d+(?:\.\d{1,4})?$/',
                $value
            )
        ) {
            throw new InvalidArgumentException(
                'مقدار پولی نامعتبر است.'
            );
        }

        [
            $integer,
            $fraction
        ] =
            array_pad(
                explode(
                    '.',
                    $value,
                    2
                ),
                2,
                ''
            );

        $integer =
            ltrim(
                $integer,
                '0'
            );

        if ($integer === '') {
            $integer = '0';
        }

        return
            $integer .
            '.' .
            str_pad(
                $fraction,
                4,
                '0'
            );
    }

    private function compareMoney(
        string $left,
        string $right
    ): int {
        $left =
            $this->normalizeMoney($left);

        $right =
            $this->normalizeMoney($right);

        [
            $leftInteger,
            $leftFraction
        ] =
            explode('.', $left);

        [
            $rightInteger,
            $rightFraction
        ] =
            explode('.', $right);

        if (
            strlen($leftInteger) !==
            strlen($rightInteger)
        ) {
            return
                strlen($leftInteger) >
                strlen($rightInteger)
                ? 1
                : -1;
        }

        if ($leftInteger !== $rightInteger) {
            return
                $leftInteger >
                $rightInteger
                ? 1
                : -1;
        }

        if ($leftFraction === $rightFraction) {
            return 0;
        }

        return
            $leftFraction >
            $rightFraction
            ? 1
            : -1;
    }

    private function subtractMoney(
        string $left,
        string $right
    ): string {
        $left =
            $this->normalizeMoney($left);

        $right =
            $this->normalizeMoney($right);

        if (
            $this->compareMoney(
                $left,
                $right
            ) < 0
        ) {
            throw new InvalidArgumentException(
                'مبلغ کسرشونده نمی‌تواند بیشتر از مبلغ اصلی باشد.'
            );
        }

        $leftDigits =
            str_replace(
                '.',
                '',
                $left
            );

        $rightDigits =
            str_replace(
                '.',
                '',
                $right
            );

        $result =
            $this->subtractIntegerStrings(
                $leftDigits,
                $rightDigits
            );

        if (strlen($result) <= 4) {
            $result =
                str_repeat(
                    '0',
                    5 - strlen($result)
                ) .
                $result;
        }

        $integer =
            substr(
                $result,
                0,
                -4
            );

        $fraction =
            substr(
                $result,
                -4
            );

        $integer =
            ltrim(
                $integer,
                '0'
            );

        if ($integer === '') {
            $integer = '0';
        }

        return
            $integer .
            '.' .
            $fraction;
    }

    private function subtractIntegerStrings(
        string $left,
        string $right
    ): string {
        $left =
            ltrim(
                $left,
                '0'
            );

        $right =
            ltrim(
                $right,
                '0'
            );

        if ($left === '') {
            $left = '0';
        }

        if ($right === '') {
            $right = '0';
        }

        $i =
            strlen($left) - 1;

        $j =
            strlen($right) - 1;

        $borrow = 0;
        $result = '';

        while ($i >= 0) {
            $digit =
                (int)$left[$i] -
                $borrow;

            $rightDigit =
                $j >= 0
                ? (int)$right[$j]
                : 0;

            if ($digit < $rightDigit) {
                $digit += 10;
                $borrow = 1;
            } else {
                $borrow = 0;
            }

            $result =
                (string)(
                    $digit -
                    $rightDigit
                ) .
                $result;

            $i--;
            $j--;
        }

        $result =
            ltrim(
                $result,
                '0'
            );

        return
            $result === ''
            ? '0'
            : $result;
    }
}
