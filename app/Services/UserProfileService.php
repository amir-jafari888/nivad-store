<?php

declare(strict_types=1);

final class UserProfileService
{
    private UserRepository $repository;

    public function __construct(
        ?UserRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new UserRepository();
    }

    public function findById(
        int $userId
    ): ?array {
        return $this->repository->findById(
            $userId
        );
    }

    public function update(
        int $userId,
        string $firstName,
        string $lastName
    ): ?array {
        return $this->repository->updateProfile(
            $userId,
            $firstName,
            $lastName
        );
    }
}
