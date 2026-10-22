<?php

declare(strict_types=1);

final class ProductSpecificationService
{
    private ProductSpecificationRepository $repository;

    public function __construct(
        ?ProductSpecificationRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new ProductSpecificationRepository();
    }

    public function find(
        int $productId,
        int $specificationId
    ): ?ProductSpecification {
        return $this->repository->find(
            $productId,
            $specificationId
        );
    }

    public function exists(
        int $productId,
        int $specificationId
    ): bool {
        return $this->repository->exists(
            $productId,
            $specificationId
        );
    }

    public function findAllByProduct(
        int $productId
    ): array {
        return $this->repository->findAllByProduct(
            $productId
        );
    }

    public function findAllBySpecification(
        int $specificationId
    ): array {
        return $this->repository->findAllBySpecification(
            $specificationId
        );
    }

    public function create(
        int $productId,
        int $specificationId,
        ?string $valueText = null,
        ?string $valueNumber = null,
        ?bool $valueBoolean = null,
        ?string $valueDate = null
    ): ?ProductSpecification {
        return $this->repository->create(
            $productId,
            $specificationId,
            $valueText,
            $valueNumber,
            $valueBoolean,
            $valueDate
        );
    }

    public function update(
        int $productId,
        int $specificationId,
        ?string $valueText = null,
        ?string $valueNumber = null,
        ?bool $valueBoolean = null,
        ?string $valueDate = null
    ): ?ProductSpecification {
        return $this->repository->update(
            $productId,
            $specificationId,
            $valueText,
            $valueNumber,
            $valueBoolean,
            $valueDate
        );
    }

    public function delete(
        int $productId,
        int $specificationId
    ): bool {
        return $this->repository->delete(
            $productId,
            $specificationId
        );
    }
}
