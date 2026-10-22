<?php

declare(strict_types=1);

final class WishlistItem
{
    public int $id;
    public int $wishlistId;
    public int $variantId;
    public ?string $createdAt;

    public function __construct(
        int $id,
        int $wishlistId,
        int $variantId,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->wishlistId = $wishlistId;
        $this->variantId = $variantId;
        $this->createdAt = $createdAt;
    }
}
