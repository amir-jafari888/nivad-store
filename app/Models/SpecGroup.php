<?php

declare(strict_types=1);

final class SpecGroup
{
    public int $id;

    public string $name;
    public string $slug;

    public int $sortOrder;

    public string $status;

    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        string $name,
        string $slug,
        int $sortOrder,
        string $status,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->sortOrder = $sortOrder;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
