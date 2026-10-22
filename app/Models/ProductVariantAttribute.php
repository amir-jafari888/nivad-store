<?php

declare(strict_types=1);

final class ProductVariantAttribute
{
    public int $variantId;
    public int $attributeId;
    public int $attributeValueId;
    public ?string $createdAt;

    public function __construct(
        int $variantId,
        int $attributeId,
        int $attributeValueId,
        ?string $createdAt = null
    ) {
        $this->variantId = $variantId;
        $this->attributeId = $attributeId;
        $this->attributeValueId = $attributeValueId;
        $this->createdAt = $createdAt;
    }
}
