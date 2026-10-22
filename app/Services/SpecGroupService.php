<?php

declare(strict_types=1);

final class SpecGroupService
{
    private SpecGroupRepository $repository;

    public function __construct(
        ?SpecGroupRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new SpecGroupRepository();
    }

    public function findById(
        int $id
    ): ?SpecGroup {
        return $this->repository->findById($id);
    }

    public function findBySlug(
        string $slug
    ): ?SpecGroup {
        return $this->repository->findBySlug($slug);
    }

    public function exists(
        int $id
    ): bool {
        return $this->repository->exists($id);
    }

    public function existsBySlug(
        string $slug
    ): bool {
        return $this->repository->existsBySlug($slug);
    }

    public function findActiveById(
        int $id
    ): ?SpecGroup {
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
        string $status,
        int $sortOrder
    ): SpecGroup {
        return $this->repository->create(
            $name,
            $slug,
            $status,
            $sortOrder
        );
    }

    public function update(
        int $id,
        string $name,
        string $slug,
        string $status,
        int $sortOrder
    ): ?SpecGroup {
        return $this->repository->update(
            $id,
            $name,
            $slug,
            $status,
            $sortOrder
        );
    }

    public function delete(
        int $id
    ): bool {
        return $this->repository->delete($id);
    }

    public function restore(
        int $id
    ): bool {
        return $this->repository->restore($id);
    }

    public function setStatus(
        int $id,
        string $status
    ): ?SpecGroup {
        return $this->repository->setStatus(
            $id,
            $status
        );
    }
}
