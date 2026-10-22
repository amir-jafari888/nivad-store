<?php

declare(strict_types=1);

final class ProductCompatibility
{
    public const TYPE_COMPATIBLE = 'compatible';
    public const TYPE_PARTIALLY_COMPATIBLE = 'partially_compatible';
    public const TYPE_NOT_COMPATIBLE = 'not_compatible';

    public int $productId;
    public int $compatibilityDeviceId;
    public string $compatibilityType;
    public ?string $note;
    public int $sortOrder;
    public ?string $createdAt;

    public function __construct(
        int $productId,
        int $compatibilityDeviceId,
        string $compatibilityType,
        ?string $note,
        int $sortOrder,
        ?string $createdAt
    ) {
        $this->productId = $productId;
        $this->compatibilityDeviceId = $compatibilityDeviceId;
        $this->compatibilityType = $compatibilityType;
        $this->note = $note;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
    }
}
