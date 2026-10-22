<?php

declare(strict_types=1);

final class ShippingMethodZone
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;
    public int $shippingMethodId;
    public int $shippingZoneId;
    public string $cost;
    public string $currency;
    public ?string $minOrderAmount;
    public ?string $freeShippingThreshold;
    public ?int $estimatedMinDays;
    public ?int $estimatedMaxDays;
    public string $status;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $shippingMethodId,
        int $shippingZoneId,
        string $cost,
        string $currency,
        ?string $minOrderAmount,
        ?string $freeShippingThreshold,
        ?int $estimatedMinDays,
        ?int $estimatedMaxDays,
        string $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->shippingMethodId = $shippingMethodId;
        $this->shippingZoneId = $shippingZoneId;
        $this->cost = $cost;
        $this->currency = $currency;
        $this->minOrderAmount = $minOrderAmount;
        $this->freeShippingThreshold = $freeShippingThreshold;
        $this->estimatedMinDays = $estimatedMinDays;
        $this->estimatedMaxDays = $estimatedMaxDays;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['shipping_method_id'],
            (int)$data['shipping_zone_id'],
            (string)$data['cost'],
            (string)$data['currency'],
            $data['min_order_amount'] !== null
                ? (string)$data['min_order_amount']
                : null,
            $data['free_shipping_threshold'] !== null
                ? (string)$data['free_shipping_threshold']
                : null,
            $data['estimated_min_days'] !== null
                ? (int)$data['estimated_min_days']
                : null,
            $data['estimated_max_days'] !== null
                ? (int)$data['estimated_max_days']
                : null,
            (string)$data['status'],
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string)$data['updated_at']
                : null
        );
    }
}
