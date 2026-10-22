<?php

declare(strict_types=1);

final class Product
{
    public int $id;
    public int $categoryId;
    public ?int $brandId;

    public string $name;
    public string $slug;

    public ?string $shortDescription;
    public ?string $description;

    public string $status;
    public bool $isFeatured;

    public ?string $metaTitle;
    public ?string $metaDescription;

    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $categoryId,
        ?int $brandId,
        string $name,
        string $slug,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $deletedAt,
        ?string $metaTitle = null,
        ?string $metaDescription = null
    ) {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->brandId = $brandId;
        $this->name = $name;
        $this->slug = $slug;
        $this->shortDescription = $shortDescription;
        $this->description = $description;
        $this->status = $status;
        $this->isFeatured = $isFeatured;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
    }
}
