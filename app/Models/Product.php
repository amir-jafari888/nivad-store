<?php

declare(strict_types=1);

/**
 * Product domain entity.
 *
 * ArrayAccess is intentionally limited to the legacy storefront keys that
 * still exist in presentation-layer contracts. New code must use properties.
 */
final class Product implements ArrayAccess
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

    public function offsetExists(mixed $offset): bool
    {
        return in_array(
            $offset,
            [
                'id',
                'category_id',
                'brand_id',
                'name',
                'slug',
                'short_description',
                'description',
                'status',
                'is_featured',
                'meta_title',
                'meta_description',
                'created_at',
                'updated_at',
                'deleted_at',
                'variant_id',
                'price',
                'compare_at_price',
                'stock_status',
            ],
            true
        );
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'brand_id' => $this->brandId,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->shortDescription,
            'description' => $this->description,
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
            'variant_id', 'price', 'compare_at_price', 'stock_status' => null,
            default => null,
        };
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Product is a read-only domain entity.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Product is a read-only domain entity.');
    }
}
