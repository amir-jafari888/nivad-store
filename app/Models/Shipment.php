<?php

declare(strict_types=1);

final class Shipment
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_READY = 'ready';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLED = 'cancelled';

    public int $id;
    public int $orderId;
    public int $shippingMethodId;
    public ?string $trackingNumber;
    public string $status;
    public string $shippingCost;
    public string $currency;
    public ?string $shippedAt;
    public ?string $deliveredAt;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $orderId,
        int $shippingMethodId,
        ?string $trackingNumber,
        string $status,
        string $shippingCost,
        string $currency,
        ?string $shippedAt = null,
        ?string $deliveredAt = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->shippingMethodId = $shippingMethodId;
        $this->trackingNumber = $trackingNumber;
        $this->status = $status;
        $this->shippingCost = $shippingCost;
        $this->currency = $currency;
        $this->shippedAt = $shippedAt;
        $this->deliveredAt = $deliveredAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['order_id'],
            (int)$data['shipping_method_id'],
            $data['tracking_number'] !== null
                ? (string)$data['tracking_number']
                : null,
            (string)$data['status'],
            (string)$data['shipping_cost'],
            (string)$data['currency'],
            $data['shipped_at'] !== null
                ? (string)$data['shipped_at']
                : null,
            $data['delivered_at'] !== null
                ? (string)$data['delivered_at']
                : null,
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string)$data['updated_at']
                : null
        );
    }
}
