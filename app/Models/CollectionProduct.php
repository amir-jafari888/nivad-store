<?php

declare(strict_types=1);

final class CollectionProduct
{
    public int $collectionId;
    public int $productId;
    public int $sortOrder;
    public ?string $createdAt;

    public function __construct(
        int $collectionId,
        int $productId,
        int $sortOrder,
        ?string $createdAt
    ) {
        $this->collectionId = $collectionId;
        $this->productId = $productId;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
    }
}
