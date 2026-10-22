<?php

declare(strict_types=1);

final class CategoryService
{
    private CategoryRepository $repository;

    public function __construct(?CategoryRepository $repository = null)
    {
        $this->repository = $repository ?? new CategoryRepository();
    }

    public function findById(int $id): ?Category
    {
        return $this->repository->findById($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->repository->findBySlug($slug);
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function findActiveById(int $id): ?Category
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
        ?int $parentId,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): Category {
        return $this->repository->create(
            $parentId,
            $name,
            $slug,
            $description,
            $status,
            $sortOrder
        );
    }

    public function update(
        int $categoryId,
        ?int $parentId,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): ?Category {
        return $this->repository->update(
            $categoryId,
            $parentId,
            $name,
            $slug,
            $description,
            $status,
            $sortOrder
        );
    }

    public function delete(
        int $categoryId
    ): bool {
        return $this->repository->delete(
            $categoryId
        );
    }

    public function restore(
        int $categoryId
    ): bool {
        return $this->repository->restore(
            $categoryId
        );
    }

    public function setStatus(
        int $categoryId,
        string $status
    ): ?Category {
        return $this->repository->setStatus(
            $categoryId,
            $status
        );
    }
}
