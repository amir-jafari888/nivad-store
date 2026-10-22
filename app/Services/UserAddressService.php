<?php

declare(strict_types=1);

final class UserAddressService
{
    private UserAddressRepository $repository;

    public function __construct(
        ?UserAddressRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new UserAddressRepository();
    }

    public function findByIdAndUser(
        int $id,
        int $userId
    ): ?UserAddress {
        return $this->repository->findByIdAndUser(
            $id,
            $userId
        );
    }

    public function findDefaultByUser(
        int $userId
    ): ?UserAddress {
        return $this->repository->findDefaultByUser(
            $userId
        );
    }

    public function findAllByUser(
        int $userId
    ): array {
        return $this->repository->findAllByUser(
            $userId
        );
    }

    public function create(
        int $userId,
        array $data
    ): UserAddress {
        return $this->repository->create(
            $userId,
            $data
        );
    }

    public function update(
        int $id,
        int $userId,
        array $data
    ): ?UserAddress {
        return $this->repository->update(
            $id,
            $userId,
            $data
        );
    }

    public function delete(
        int $id,
        int $userId
    ): bool {
        return $this->repository->delete(
            $id,
            $userId
        );
    }

    public function setDefault(
        int $id,
        int $userId
    ): ?UserAddress {
        return $this->repository->setDefault(
            $id,
            $userId
        );
    }
}
