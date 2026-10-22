<?php

declare(strict_types=1);

final class ProductAttribute
{
    public int $id;
    public string $name;
    public string $slug;
    public string $type;
    public bool $isVariant;
    public bool $isFilterable;
    public bool $isRequired;
    public string $status;
    public int $sortOrder;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        string $name,
        string $slug,
        string $type,
        string $status,
        int $sortOrder = 0,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null,
        bool $isVariant = false,
        bool $isFilterable = false,
        bool $isRequired = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->type = $type;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->isVariant = $isVariant;
        $this->isFilterable = $isFilterable;
        $this->isRequired = $isRequired;
    }
}