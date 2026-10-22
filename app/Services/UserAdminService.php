<?php

declare(strict_types=1);

final class UserAdminService
{
    private UserRepository $repository;

    public function __construct(
        ?UserRepository $repository = null
    ) {
        $this->repository =
            $repository ?? new UserRepository();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(
        int $userId
    ): ?array {
        return $this->repository->findById(
            $userId
        );
    }

    public function updateStatus(
        int $userId,
        string $status
    ): bool {
        $result =
            $this->repository->updateStatus(
                $userId,
                $status
            );

        return $result !== null;
    }
}
