<?php

declare(strict_types=1);

final class Specification
{
    public int $id;

    public int $specGroupId;

    public string $name;
    public string $slug;
    public string $valueType;

    public ?string $unit;

    public int $sortOrder;

    public string $status;

    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $specGroupId,
        string $name,
        string $slug,
        string $valueType,
        ?string $unit,
        int $sortOrder,
        string $status,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    ) {
        $this->id = $id;
        $this->specGroupId = $specGroupId;
        $this->name = $name;
        $this->slug = $slug;
        $this->valueType = $valueType;
        $this->unit = $unit;
        $this->sortOrder = $sortOrder;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
