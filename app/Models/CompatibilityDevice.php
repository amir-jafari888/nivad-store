<?php

declare(strict_types=1);

final class CompatibilityDevice
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;
    public string $brandName;
    public string $name;
    public ?string $model;
    public string $deviceType;
    public string $slug;
    public ?string $description;
    public ?string $image;
    public string $status;
    public int $sortOrder;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        string $brandName,
        string $name,
        ?string $model,
        string $deviceType,
        string $slug,
        ?string $description,
        ?string $image,
        string $status,
        int $sortOrder,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $deletedAt
    ) {
        $this->id = $id;
        $this->brandName = $brandName;
        $this->name = $name;
        $this->model = $model;
        $this->deviceType = $deviceType;
        $this->slug = $slug;
        $this->description = $description;
        $this->image = $image;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
