<?php

declare(strict_types=1);

final class InventoryMovementService
{
    private InventoryMovementRepository $repository;

    public function __construct(
        ?InventoryMovementRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new InventoryMovementRepository();
    }

    public function findById(
        int $id
    ): ?InventoryMovement {
        return $this->repository->findById($id);
    }

    public function findAllByVariant(
        int $variantId
    ): array {
        return $this->repository->findAllByVariant(
            $variantId
        );
    }

    public function create(
        int $variantId,
        string $movementType,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $createdBy = null
    ): InventoryMovement {
        return $this->repository->create(
            $variantId,
            $movementType,
            $quantity,
            $referenceType,
            $referenceId,
            $note,
            $createdBy
        );
    }
}
