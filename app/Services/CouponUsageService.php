<?php

declare(strict_types=1);

final class CouponUsageService
{
    private CouponUsageRepository $repository;

    private CouponService $couponService;

    public function __construct(
        ?CouponUsageRepository $repository = null,
        ?CouponService $couponService = null
    ) {
        $this->repository =
            $repository
            ?? new CouponUsageRepository();

        $this->couponService =
            $couponService
            ?? new CouponService();
    }

    public function findById(
        int $usageId
    ): ?CouponUsage {
        return $this->repository->findById(
            $usageId
        );
    }

    public function findByCouponId(
        int $couponId
    ): array {
        return $this->repository->findByCouponId(
            $couponId
        );
    }

    public function findByUserId(
        int $userId
    ): array {
        return $this->repository->findByUserId(
            $userId
        );
    }

    public function findByOrderId(
        int $orderId
    ): array {
        return $this->repository->findByOrderId(
            $orderId
        );
    }

    public function countByCouponId(
        int $couponId
    ): int {
        return $this->repository->countByCouponId(
            $couponId
        );
    }

    public function countByUserAndCoupon(
        int $userId,
        int $couponId
    ): int {
        return $this->repository->countByUserAndCoupon(
            $userId,
            $couponId
        );
    }

    public function canUse(
        Coupon $coupon,
        int $userId
    ): bool {
        if ($userId < 1) {
            return false;
        }

        if (!$this->couponService->isCurrentlyAvailable($coupon)) {
            return false;
        }

        if (
            $coupon->usageLimit !== null &&
            $this->countByCouponId($coupon->id)
            >= $coupon->usageLimit
        ) {
            return false;
        }

        if (
            $coupon->usageLimitPerUser !== null &&
            $this->countByUserAndCoupon(
                $userId,
                $coupon->id
            ) >= $coupon->usageLimitPerUser
        ) {
            return false;
        }

        return true;
    }

    public function createForOrder(
        Coupon $coupon,
        int $userId,
        int $orderId,
        string $discountAmount,
        string $currency = 'IRR'
    ): ?CouponUsage {
        if (
            $userId < 1 ||
            $orderId < 1
        ) {
            throw new InvalidArgumentException(
                'شناسه کاربر یا سفارش نامعتبر است.'
            );
        }

        if (
            !$this->canUse(
                $coupon,
                $userId
            )
        ) {
            throw new RuntimeException(
                'امکان استفاده از این کوپن وجود ندارد.'
            );
        }

        $discountAmount =
            trim($discountAmount);

        if (
            $discountAmount === '' ||
            !preg_match(
                '/^\d+(?:\.\d{1,4})?$/',
                $discountAmount
            )
        ) {
            throw new InvalidArgumentException(
                'مبلغ تخفیف نامعتبر است.'
            );
        }

        $currency =
            strtoupper(
                trim($currency)
            );

        if (
            !preg_match(
                '/^[A-Z]{3}$/',
                $currency
            )
        ) {
            throw new InvalidArgumentException(
                'واحد پول نامعتبر است.'
            );
        }

        return $this->repository->create(
            $coupon->id,
            $userId,
            $orderId,
            $discountAmount,
            $currency
        );
    }
}
