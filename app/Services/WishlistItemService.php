<?php

declare(strict_types=1);

final class WishlistItemService
{
    private WishlistItemRepository $repository;

    public function __construct(
        ?WishlistItemRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new WishlistItemRepository();
    }

    public function findById(
        int $id
    ): ?WishlistItem {
        return $this->repository->findById($id);
    }

    public function findByWishlist(
        int $wishlistId
    ): array {
        return $this->repository->findByWishlist(
            $wishlistId
        );
    }

    public function findByWishlistAndVariant(
        int $wishlistId,
        int $variantId
    ): ?WishlistItem {
        return $this->repository
            ->findByWishlistAndVariant(
                $wishlistId,
                $variantId
            );
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function existsByWishlistAndVariant(
        int $wishlistId,
        int $variantId
    ): bool {
        return $this->repository
            ->existsByWishlistAndVariant(
                $wishlistId,
                $variantId
            );
    }

    public function create(
        int $wishlistId,
        int $variantId
    ): WishlistItem {
        return $this->repository->create(
            $wishlistId,
            $variantId
        );
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function deleteByWishlistAndVariant(
        int $wishlistId,
        int $variantId
    ): bool {
        return $this->repository
            ->deleteByWishlistAndVariant(
                $wishlistId,
                $variantId
            );
    }
}
