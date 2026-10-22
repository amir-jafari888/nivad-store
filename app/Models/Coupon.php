<?php

declare(strict_types=1);

final class Coupon
{
    public const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    public const DISCOUNT_TYPE_FIXED = 'fixed';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';

    public int $id;

    public string $code;
    public ?string $title;
    public ?string $description;

    public string $discountType;
    public string $discountValue;

    public ?string $minimumOrderAmount;
    public ?string $maximumDiscountAmount;

    public ?int $usageLimit;
    public ?int $usageLimitPerUser;

    public ?string $startsAt;
    public ?string $expiresAt;

    public string $status;

    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
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
        string $status,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $deletedAt
    ) {
        $this->id = $id;

        $this->code = $code;
        $this->title = $title;
        $this->description = $description;

        $this->discountType = $discountType;
        $this->discountValue = $discountValue;

        $this->minimumOrderAmount = $minimumOrderAmount;
        $this->maximumDiscountAmount = $maximumDiscountAmount;

        $this->usageLimit = $usageLimit;
        $this->usageLimitPerUser = $usageLimitPerUser;

        $this->startsAt = $startsAt;
        $this->expiresAt = $expiresAt;

        $this->status = $status;

        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
