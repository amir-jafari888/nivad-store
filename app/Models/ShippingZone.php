<?php

declare(strict_types=1);

final class ShippingZone
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public string $status;
    public int $sortOrder;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    ) {
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

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
            $data['description'] !== null
                ? (string)$data['description']
                : null,
            (string)$data['status'],
            (int)$data['sort_order'],
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string)$data['updated_at']
                : null,
            $data['deleted_at'] !== null
                ? (string)$data['deleted_at']
                : null
        );
    }
}
