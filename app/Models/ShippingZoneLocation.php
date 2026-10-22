<?php

declare(strict_types=1);

final class ShippingZoneLocation
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;
    public int $shippingZoneId;
    public ?string $province;
    public ?string $city;
    public ?string $postalPrefix;
    public string $status;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $shippingZoneId,
        ?string $province,
        ?string $city,
        ?string $postalPrefix,
        string $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->shippingZoneId = $shippingZoneId;
        $this->province = $province;
        $this->city = $city;
        $this->postalPrefix = $postalPrefix;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['shipping_zone_id'],
            $data['province'] !== null
                ? (string)$data['province']
                : null,
            $data['city'] !== null
                ? (string)$data['city']
                : null,
            $data['postal_prefix'] !== null
                ? (string)$data['postal_prefix']
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
