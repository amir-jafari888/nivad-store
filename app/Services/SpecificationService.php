<?php

declare(strict_types=1);

final class SpecificationService
{
    private SpecificationRepository $repository;

    public function __construct(
        ?SpecificationRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new SpecificationRepository();
    }

    public function findById(
        int $id
    ): ?Specification {
        return $this->repository->findById($id);
    }

    public function findBySlug(
        string $slug
    ): ?Specification {
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
    ): ?Specification {
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

    public function findAllByGroup(
        int $specGroupId
    ): array {
        return $this->repository->findAllByGroup(
            $specGroupId
        );
    }

    public function findActiveByGroup(
        int $specGroupId
    ): array {
        return $this->repository->findActiveByGroup(
            $specGroupId
        );
    }

    public function create(
        int $specGroupId,
        string $name,
        string $slug,
        string $valueType,
        ?string $unit,
        int $sortOrder,
        string $status
    ): Specification {
        return $this->repository->create(
            $specGroupId,
            $name,
            $slug,
            $valueType,
            $unit,
            $sortOrder,
            $status
        );
    }

    public function update(
        int $id,
        int $specGroupId,
        string $name,
        string $slug,
        string $valueType,
        ?string $unit,
        int $sortOrder,
        string $status
    ): ?Specification {
        return $this->repository->update(
            $id,
            $specGroupId,
            $name,
            $slug,
            $valueType,
            $unit,
            $sortOrder,
            $status
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
    ): ?Specification {
        return $this->repository->setStatus(
            $id,
            $status
        );
    }
}
