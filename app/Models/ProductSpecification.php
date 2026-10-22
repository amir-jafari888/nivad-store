<?php

declare(strict_types=1);

final class ProductSpecification
{
    public int $productId;

    public int $specificationId;

    public ?string $valueText;

    public ?string $valueNumber;

    public ?bool $valueBoolean;

    public ?string $valueDate;

    public ?string $createdAt;

    public ?string $updatedAt;

    public function __construct(
        int $productId,
        int $specificationId,
        ?string $valueText = null,
        ?string $valueNumber = null,
        ?bool $valueBoolean = null,
        ?string $valueDate = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->productId = $productId;
        $this->specificationId = $specificationId;
        $this->valueText = $valueText;
        $this->valueNumber = $valueNumber;
        $this->valueBoolean = $valueBoolean;
        $this->valueDate = $valueDate;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
