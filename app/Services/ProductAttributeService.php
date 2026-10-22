<?php

declare(strict_types=1);

final class ProductAttributeService
{
    private ProductAttributeRepository $repository;

    public function __construct(
        ?ProductAttributeRepository $repository = null
    ) {
        $this->repository = $repository ?? new ProductAttributeRepository();
    }

    public function findById(int $id): ?ProductAttribute
    {
        return $this->repository->findById($id);
    }

    public function findBySlug(string $slug): ?ProductAttribute
    {
        return $this->repository->findBySlug($slug);
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function findActiveById(int $id): ?ProductAttribute
    {
        return $this->repository->findActiveById($id);
    }

    public function create(
        string $name,
        string $slug,
        string $type,
        string $status = 'active',
        int $sortOrder = 0
    ): ProductAttribute {
        return $this->repository->create(
            $name,
            $slug,
            $type,
            $status,
            $sortOrder
        );
    }

    public function update(
        int $id,
        string $name,
        string $slug,
        string $type,
        string $status,
        int $sortOrder
    ): ?ProductAttribute {
        return $this->repository->update(
            $id,
            $name,
            $slug,
            $type,
            $status,
            $sortOrder
        );
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function restore(int $id): bool
    {
        return $this->repository->restore($id);
    }

    public function setStatus(
        int $id,
        string $status
    ): ?ProductAttribute {
        return $this->repository->setStatus($id, $status);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findActive(): array
    {
        return $this->repository->findActive();
    }
}
