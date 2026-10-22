<?php

declare(strict_types=1);

final class InventoryReservationService
{
    private InventoryReservationRepository $repository;

    public function __construct(
        ?InventoryReservationRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new InventoryReservationRepository();
    }

    public function findById(
        int $id
    ): ?InventoryReservation {
        return $this->repository->findById($id);
    }

    public function findAllByVariant(
        int $variantId
    ): array {
        return $this->repository->findAllByVariant(
            $variantId
        );
    }

    public function findAllByCart(
        int $cartId
    ): array {
        return $this->repository->findAllByCart(
            $cartId
        );
    }

    public function findAllByOrder(
        int $orderId
    ): array {
        return $this->repository->findAllByOrder(
            $orderId
        );
    }

    public function findAllActiveByCart(
        int $cartId
    ): array {
        return $this->repository
            ->findAllActiveByCart(
                $cartId
            );
    }

    public function findAllActiveByVariant(
        int $variantId
    ): array {
        return $this->repository->findAllActiveByVariant(
            $variantId
        );
    }

    public function create(
        int $variantId,
        ?int $cartId,
        ?int $orderId,
        int $quantity,
        string $status = 'active',
        ?string $expiresAt = null
    ): InventoryReservation {
        return $this->repository->create(
            $variantId,
            $cartId,
            $orderId,
            $quantity,
            $status,
            $expiresAt
        );
    }

    public function attachToOrder(
        int $id,
        int $orderId
    ): ?InventoryReservation {
        return $this->repository
            ->attachToOrder(
                $id,
                $orderId
            );
    }

    public function releaseActive(
        int $id
    ): ?InventoryReservation {
        return $this->repository->releaseActive($id);
    }

    public function updateStatus(
        int $id,
        string $status
    ): ?InventoryReservation {
        return $this->repository->updateStatus(
            $id,
            $status
        );
    }
}
