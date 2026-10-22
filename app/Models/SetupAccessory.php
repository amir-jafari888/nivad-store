<?php

declare(strict_types=1);

final class SetupAccessory
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;
    public string $name;
    public string $slug;
    public string $accessoryType;
    public ?string $description;
    public ?string $image;
    public string $status;
    public int $sortOrder;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        string $name,
        string $slug,
        string $accessoryType,
        ?string $description,
        ?string $image,
        string $status,
        int $sortOrder,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $deletedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->accessoryType = $accessoryType;
        $this->description = $description;
        $this->image = $image;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
