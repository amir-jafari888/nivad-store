<?php

declare(strict_types=1);
final class ProductType
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public string $status;
    public int $sortOrder;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(int $id, string $name, string $slug, ?string $description, string $status, int $sortOrder, ?string $createdAt, ?string $updatedAt, ?string $deletedAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
