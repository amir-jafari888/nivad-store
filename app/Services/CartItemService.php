<?php

declare(strict_types=1);

final class CartItemService
{
    private CartItemRepository $repository;

    public function __construct(
        ?CartItemRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new CartItemRepository();
    }

    public function findById(
        int $id
    ): ?CartItem {
        return $this->repository->findById($id);
    }

    public function findByCart(
        int $cartId
    ): array {
        return $this->repository->findByCart($cartId);
    }

    public function findByCartAndVariant(
        int $cartId,
        int $variantId
    ): ?CartItem {
        return $this->repository
            ->findByCartAndVariant(
                $cartId,
                $variantId
            );
    }

    public function exists(
        int $id
    ): bool {
        return $this->repository->exists($id);
    }

    public function existsByCartAndVariant(
        int $cartId,
        int $variantId
    ): bool {
        return $this->repository
            ->existsByCartAndVariant(
                $cartId,
                $variantId
            );
    }

    public function create(
        int $cartId,
        int $variantId,
        int $quantity,
        string $unitPrice
    ): CartItem {
        return $this->repository->create(
            $cartId,
            $variantId,
            $quantity,
            $unitPrice
        );
    }

    public function update(
        int $id,
        int $quantity,
        string $unitPrice
    ): ?CartItem {
        return $this->repository->update(
            $id,
            $quantity,
            $unitPrice
        );
    }

    public function delete(
        int $id
    ): bool {
        return $this->repository->delete($id);
    }
}
