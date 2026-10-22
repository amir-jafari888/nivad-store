<?php

declare(strict_types=1);

final class Collection
{
    public const TYPE_MANUAL = 'manual';
    public const TYPE_AUTOMATIC = 'automatic';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;

    public string $name;
    public string $slug;

    public ?string $description;

    public string $collectionType;
    public string $status;

    public int $sortOrder;

    public ?string $startsAt;
    public ?string $endsAt;

    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        string $collectionType,
        string $status,
        int $sortOrder,
        ?string $startsAt,
        ?string $endsAt,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $deletedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->collectionType = $collectionType;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
