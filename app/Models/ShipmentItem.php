<?php

declare(strict_types=1);

final class ShipmentItem
{
    public int $id;
    public int $shipmentId;
    public int $orderItemId;
    public int $quantity;
    public ?string $createdAt;

    public function __construct(
        int $id,
        int $shipmentId,
        int $orderItemId,
        int $quantity,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->shipmentId = $shipmentId;
        $this->orderItemId = $orderItemId;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['shipment_id'],
            (int)$data['order_item_id'],
            (int)$data['quantity'],
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null
        );
    }
}
