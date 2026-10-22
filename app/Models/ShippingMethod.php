<?php

declare(strict_types=1);

final class ShippingMethod
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public string $baseCost;
    public string $currency;
    public ?int $estimatedMinDays;
    public ?int $estimatedMaxDays;
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
        string $baseCost,
        string $currency,
        ?int $estimatedMinDays,
        ?int $estimatedMaxDays,
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
        $this->baseCost = $baseCost;
        $this->currency = $currency;
        $this->estimatedMinDays = $estimatedMinDays;
        $this->estimatedMaxDays = $estimatedMaxDays;
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
            (string)$data['base_cost'],
            (string)$data['currency'],
            $data['estimated_min_days'] !== null
                ? (int)$data['estimated_min_days']
                : null,
            $data['estimated_max_days'] !== null
                ? (int)$data['estimated_max_days']
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
