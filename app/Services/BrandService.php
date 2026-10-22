<?php

declare(strict_types=1);

final class BrandService
{
    private BrandRepository $repository;

    public function __construct(?BrandRepository $repository = null)
    {
        $this->repository = $repository ?? new BrandRepository();
    }

    public function findById(int $id): ?Brand
    {
        return $this->repository->findById($id);
    }

    public function findBySlug(string $slug): ?Brand
    {
        return $this->repository->findBySlug($slug);
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function findActiveById(int $id): ?Brand
    {
        return $this->repository->findActiveById($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findActive(): array
    {
        return $this->repository->findActive();
    }

    public function create(
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): Brand {
        return $this->repository->create(
            $name,
            $slug,
            $description,
            $status,
            $sortOrder
        );
    }

    public function update(
        int $brandId,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): ?Brand {
        return $this->repository->update(
            $brandId,
            $name,
            $slug,
            $description,
            $status,
            $sortOrder
        );
    }

    public function delete(
        int $brandId
    ): bool {
        return $this->repository->delete(
            $brandId
        );
    }

    public function restore(
        int $brandId
    ): bool {
        return $this->repository->restore(
            $brandId
        );
    }

    public function setStatus(
        int $brandId,
        string $status
    ): ?Brand {
        return $this->repository->setStatus(
            $brandId,
            $status
        );
    }
}
