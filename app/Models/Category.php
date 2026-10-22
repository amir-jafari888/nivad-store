<?php

declare(strict_types=1);

final class Category
{
    public int $id;
    public ?int $parentId;

    public string $name;
    public string $slug;

    public ?string $description;

    public string $status;
    public int $sortOrder;

    public ?string $metaTitle;
    public ?string $metaDescription;

    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        ?int $parentId,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $deletedAt,
        ?string $metaTitle = null,
        ?string $metaDescription = null
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
    }
}
