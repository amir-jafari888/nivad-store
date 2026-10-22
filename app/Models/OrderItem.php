<?php

declare(strict_types=1);

final class OrderItem
{
    public int $id;
    public int $orderId;
    public ?int $productId;
    public ?int $variantId;

    public string $productName;
    public string $productSku;
    public ?string $variantSku;
    public ?string $variantTitle;

    public int $quantity;

    public string $unitPrice;
    public string $discountAmount;
    public string $taxAmount;
    public string $totalAmount;

    public ?string $createdAt;

    public function __construct(
        int $id,
        int $orderId,
        ?int $productId,
        ?int $variantId,
        string $productName,
        string $productSku,
        ?string $variantSku,
        ?string $variantTitle,
        int $quantity,
        string $unitPrice,
        string $discountAmount,
        string $taxAmount,
        string $totalAmount,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->variantId = $variantId;

        $this->productName = $productName;
        $this->productSku = $productSku;
        $this->variantSku = $variantSku;
        $this->variantTitle = $variantTitle;

        $this->quantity = $quantity;

        $this->unitPrice = $unitPrice;
        $this->discountAmount = $discountAmount;
        $this->taxAmount = $taxAmount;
        $this->totalAmount = $totalAmount;

        $this->createdAt = $createdAt;
    }
}
