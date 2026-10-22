<?php

declare(strict_types=1);

final class CartItem
{
    public int $id;
    public int $cartId;
    public int $variantId;
    public int $quantity;
    public string $unitPrice;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $cartId,
        int $variantId,
        int $quantity = 1,
        string $unitPrice = '0.0000',
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->cartId = $cartId;
        $this->variantId = $variantId;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
