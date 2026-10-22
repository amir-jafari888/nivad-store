<?php

declare(strict_types=1);

final class SetupDevice
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;
    public string $name;
    public string $slug;
    public string $deviceType;
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
        string $deviceType,
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
        $this->deviceType = $deviceType;
        $this->description = $description;
        $this->image = $image;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
