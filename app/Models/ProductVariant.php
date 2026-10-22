<?php

declare(strict_types=1);

final class ProductVariant
{
    public int $id;
    public int $productId;
    public string $sku;
    public ?string $barcode;
    public string $price;
    public ?string $compareAtPrice;
    public string $currency;
    public string $status;
    public ?string $name;
    public bool $isDefault;
    public int $sortOrder;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status,
        ?string $name,
        bool $isDefault = false,
        int $sortOrder = 0,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->sku = $sku;
        $this->barcode = $barcode;
        $this->price = $price;
        $this->compareAtPrice = $compareAtPrice;
        $this->currency = $currency;
        $this->status = $status;
        $this->name = $name;
        $this->isDefault = $isDefault;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
