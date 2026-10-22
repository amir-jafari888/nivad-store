<?php

declare(strict_types=1);
final class ProductTypeService
{
    private ProductTypeRepository $repository;

    public function __construct(?ProductTypeRepository $repository = null)
    {
        $this->repository = $repository ?? new ProductTypeRepository();
    }

    public function findById(int $id): ?ProductType
    {
        return $this->repository->findById($id);
    }
    public function findBySlug(string $slug): ?ProductType
    {
        return $this->repository->findBySlug($slug);
    }
    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }
    public function findAll(): array
    {
        return $this->repository->findAll();
    }
    public function findActive(): array
    {
        return $this->repository->findActive();
    }
    public function create(string $name, string $slug, ?string $description, string $status, int $sortOrder): ProductType
    {
        return $this->repository->create($name, $slug, $description, $status, $sortOrder);
    }
    public function update(int $id, string $name, string $slug, ?string $description, string $status, int $sortOrder): ?ProductType
    {
        return $this->repository->update($id, $name, $slug, $description, $status, $sortOrder);
    }
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
    public function restore(int $id): bool
    {
        return $this->repository->restore($id);
    }
    public function setStatus(int $id, string $status): ?ProductType
    {
        return $this->repository->setStatus($id, $status);
    }
}
