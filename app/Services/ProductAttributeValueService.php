<?php

declare(strict_types=1);

final class ProductAttributeValueService
{
    private ProductAttributeValueRepository $repository;

    public function __construct(
        ?ProductAttributeValueRepository $repository = null
    ) {
        $this->repository = $repository
            ?? new ProductAttributeValueRepository();
    }

    public function findById(int $id): ?ProductAttributeValue
    {
        return $this->repository->findById($id);
    }

    public function findBySlug(
        int $attributeId,
        string $slug
    ): ?ProductAttributeValue {
        return $this->repository->findBySlug(
            $attributeId,
            $slug
        );
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function findActiveById(
        int $id
    ): ?ProductAttributeValue {
        return $this->repository->findActiveById($id);
    }

    public function create(
        int $attributeId,
        string $value,
        string $slug,
        string $status = 'active',
        int $sortOrder = 0
    ): ProductAttributeValue {
        return $this->repository->create(
            $attributeId,
            $value,
            $slug,
            $status,
            $sortOrder
        );
    }

    public function update(
        int $id,
        int $attributeId,
        string $value,
        string $slug,
        string $status,
        int $sortOrder
    ): ?ProductAttributeValue {
        return $this->repository->update(
            $id,
            $attributeId,
            $value,
            $slug,
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
    ): ?ProductAttributeValue {
        return $this->repository->setStatus($id, $status);
    }

    public function findAllByAttribute(
        int $attributeId
    ): array {
        return $this->repository->findAllByAttribute(
            $attributeId
        );
    }

    public function findActiveByAttribute(
        int $attributeId
    ): array {
        return $this->repository->findActiveByAttribute(
            $attributeId
        );
    }
}
