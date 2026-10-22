<?php

declare(strict_types=1);

final class WishlistService
{
    private WishlistRepository $repository;

    public function __construct(
        ?WishlistRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new WishlistRepository();
    }

    public function findById(int $id): ?Wishlist
    {
        return $this->repository->findById($id);
    }

    public function findAllByUser(int $userId): array
    {
        return $this->repository->findAllByUser($userId);
    }

    public function findDefaultByUser(
        int $userId
    ): ?Wishlist {
        return $this->repository->findDefaultByUser(
            $userId
        );
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function existsByUserAndName(
        int $userId,
        string $name
    ): bool {
        return $this->repository
            ->existsByUserAndName(
                $userId,
                $name
            );
    }

    public function create(
        int $userId,
        string $name = 'علاقه‌مندی‌ها',
        bool $isDefault = true
    ): Wishlist {
        return $this->repository->create(
            $userId,
            $name,
            $isDefault
        );
    }

    public function update(
        int $id,
        string $name,
        bool $isDefault
    ): ?Wishlist {
        return $this->repository->update(
            $id,
            $name,
            $isDefault
        );
    }

    public function setDefault(
        int $id
    ): ?Wishlist {
        return $this->repository->setDefault($id);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
