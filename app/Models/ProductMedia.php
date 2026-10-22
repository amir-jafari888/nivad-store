<?php

declare(strict_types=1);

final class ProductMedia
{
    public int $id;
    public int $productId;
    public ?int $variantId;
    public string $mediaType;
    public string $filePath;
    public ?string $thumbnailPath;
    public ?string $altText;
    public int $sortOrder;
    public bool $isPrimary;
    public string $status;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $productId,
        ?int $variantId,
        string $mediaType,
        string $filePath,
        ?string $thumbnailPath = null,
        ?string $altText = null,
        int $sortOrder = 0,
        bool $isPrimary = false,
        string $status = 'active',
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->mediaType = $mediaType;
        $this->filePath = $filePath;
        $this->thumbnailPath = $thumbnailPath;
        $this->altText = $altText;
        $this->sortOrder = $sortOrder;
        $this->isPrimary = $isPrimary;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
