<?php

declare(strict_types=1);

final class CouponUsage
{
    public int $id;
    public int $couponId;
    public int $userId;
    public int $orderId;
    public string $discountAmount;
    public string $currency;
    public ?string $createdAt;

    public function __construct(
        int $id,
        int $couponId,
        int $userId,
        int $orderId,
        string $discountAmount,
        string $currency,
        ?string $createdAt
    ) {
        $this->id = $id;
        $this->couponId = $couponId;
        $this->userId = $userId;
        $this->orderId = $orderId;
        $this->discountAmount = $discountAmount;
        $this->currency = $currency;
        $this->createdAt = $createdAt;
    }
}
