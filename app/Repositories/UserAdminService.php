<?php

declare(strict_types=1);

final class UserAdminService
{
    private UserRepository $repository;

    public function __construct(
        ?UserRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new UserRepository();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(
        int $id
    ): ?array {
        return $this->repository->findById($id);
    }

    public function updateStatus(
        int $id,
        string $status
    ): ?array {
        return $this->repository->updateStatus(
            $id,
            $status
        );
    }
}
