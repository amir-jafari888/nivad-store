<?php

declare(strict_types=1);

final class ProductVariantAttributeService
{
    private ProductVariantAttributeRepository $repository;

    public function __construct(
        ?ProductVariantAttributeRepository $repository = null
    ) {
        $this->repository =
            $repository
            ?? new ProductVariantAttributeRepository();
    }

    public function exists(
        int $variantId,
        int $attributeValueId
    ): bool {
        return $this->repository->exists(
            $variantId,
            $attributeValueId
        );
    }

    public function existsByVariantAndAttribute(
        int $variantId,
        int $attributeId
    ): bool {
        return $this->repository
            ->existsByVariantAndAttribute(
                $variantId,
                $attributeId
            );
    }

    public function create(
        int $variantId,
        int $attributeValueId
    ): ?ProductVariantAttribute {
        return $this->repository->create(
            $variantId,
            $attributeValueId
        );
    }

    public function delete(
        int $variantId,
        int $attributeValueId
    ): bool {
        return $this->repository->delete(
            $variantId,
            $attributeValueId
        );
    }

    public function findAllByVariant(
        int $variantId
    ): array {
        return $this->repository
            ->findAllByVariant($variantId);
    }
}
