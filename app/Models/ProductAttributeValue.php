<?php

declare(strict_types=1);

final class ProductAttributeValue
{
    public int $id;
    public int $attributeId;
    public string $value;
    public string $slug;
    public ?string $valueCode;
    public ?string $colorHex;
    public string $status;
    public int $sortOrder;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $attributeId,
        string $value,
        string $slug,
        string $status,
        int $sortOrder = 0,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null,
        ?string $valueCode = null,
        ?string $colorHex = null
    ) {
        $this->id = $id;
        $this->attributeId = $attributeId;
        $this->value = $value;
        $this->slug = $slug;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->valueCode = $valueCode;
        $this->colorHex = $colorHex;
    }
}
