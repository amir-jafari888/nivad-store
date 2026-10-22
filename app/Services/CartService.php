<?php

declare(strict_types=1);

final class CartService
{
    private CartRepository $repository;

    public function __construct(
        ?CartRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new CartRepository();
    }

    public function findById(
        int $id
    ): ?Cart {
        return $this->repository->findById($id);
    }

    public function findByUser(
        int $userId
    ): ?Cart {
        return $this->repository->findByUser($userId);
    }

    public function findBySessionTokenHash(
        string $sessionTokenHash
    ): ?Cart {
        return $this->repository
            ->findBySessionTokenHash(
                $sessionTokenHash
            );
    }

    public function exists(
        int $id
    ): bool {
        return $this->repository->exists($id);
    }

    public function existsBySessionTokenHash(
        string $sessionTokenHash
    ): bool {
        return $this->repository
            ->existsBySessionTokenHash(
                $sessionTokenHash
            );
    }

    public function create(
        ?int $userId,
        ?string $sessionTokenHash,
        string $status = 'active',
        string $currency = 'IRR',
        ?string $expiresAt = null
    ): Cart {
        return $this->repository->create(
            $userId,
            $sessionTokenHash,
            $status,
            $currency,
            $expiresAt
        );
    }

    public function update(
        int $id,
        ?int $userId,
        ?string $sessionTokenHash,
        string $status,
        string $currency,
        ?string $expiresAt
    ): ?Cart {
        return $this->repository->update(
            $id,
            $userId,
            $sessionTokenHash,
            $status,
            $currency,
            $expiresAt
        );
    }

    public function updateStatus(
        int $id,
        string $status
    ): ?Cart {
        return $this->repository->updateStatus(
            $id,
            $status
        );
    }
}
