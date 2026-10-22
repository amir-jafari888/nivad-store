<?php

declare(strict_types=1);

final class InventoryService
{
    private InventoryRepository $repository;

    public function __construct(
        ?InventoryRepository $repository = null
    ) {
        $this->repository =
            $repository ?? new InventoryRepository();
    }

    public function findById(int $id): ?Inventory
    {
        return $this->repository->findById($id);
    }

    public function findByVariant(int $variantId): ?Inventory
    {
        return $this->repository->findByVariant($variantId);
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function existsByVariant(int $variantId): bool
    {
        return $this->repository->existsByVariant($variantId);
    }

    public function create(
        int $variantId,
        int $quantity = 0,
        int $reservedQuantity = 0,
        int $lowStockThreshold = 5
    ): Inventory {
        return $this->repository->create(
            $variantId,
            $quantity,
            $reservedQuantity,
            $lowStockThreshold
        );
    }

    public function reserveQuantity(
        int $variantId,
        int $quantity
    ): bool {
        return $this->repository->reserveQuantity(
            $variantId,
            $quantity
        );
    }

    public function releaseQuantity(
        int $variantId,
        int $quantity
    ): bool {
        return $this->repository->releaseQuantity(
            $variantId,
            $quantity
        );
    }

    public function update(
        int $inventoryId,
        int $quantity,
        int $reservedQuantity,
        int $lowStockThreshold
    ): ?Inventory {
        return $this->repository->update(
            $inventoryId,
            $quantity,
            $reservedQuantity,
            $lowStockThreshold
        );
    }
}
