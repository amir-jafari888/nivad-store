<?php

declare(strict_types=1);

final class DiscoveryProduct
{
    public int $productId;
    public int $discoveryTagId;
    public int $sortOrder;
    public ?string $createdAt;

    public function __construct(
        int $productId,
        int $discoveryTagId,
        int $sortOrder,
        ?string $createdAt
    ) {
        $this->productId = $productId;
        $this->discoveryTagId = $discoveryTagId;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
    }
}
