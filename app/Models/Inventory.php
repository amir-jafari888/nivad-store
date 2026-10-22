<?php

declare(strict_types=1);

final class Inventory
{
    public int $id;
    public int $variantId;
    public int $quantity;
    public int $reservedQuantity;
    public int $lowStockThreshold;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $variantId,
        int $quantity = 0,
        int $reservedQuantity = 0,
        int $lowStockThreshold = 5,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->variantId = $variantId;
        $this->quantity = $quantity;
        $this->reservedQuantity = $reservedQuantity;
        $this->lowStockThreshold = $lowStockThreshold;
        $this->updatedAt = $updatedAt;
    }
}
