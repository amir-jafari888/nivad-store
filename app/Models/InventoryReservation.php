<?php

declare(strict_types=1);

final class InventoryReservation
{
    public int $id;
    public int $variantId;
    public ?int $cartId;
    public ?int $orderId;
    public int $quantity;
    public string $status;
    public ?string $expiresAt;
    public ?string $releasedAt;
    public ?string $convertedAt;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $variantId,
        ?int $cartId = null,
        ?int $orderId = null,
        int $quantity = 0,
        string $status = 'active',
        ?string $expiresAt = null,
        ?string $releasedAt = null,
        ?string $convertedAt = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->variantId = $variantId;
        $this->cartId = $cartId;
        $this->orderId = $orderId;
        $this->quantity = $quantity;
        $this->status = $status;
        $this->expiresAt = $expiresAt;
        $this->releasedAt = $releasedAt;
        $this->convertedAt = $convertedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
