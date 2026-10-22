<?php

declare(strict_types=1);

final class ProductMediaService
{
    private ProductMediaRepository $repository;

    public function __construct(
        ?ProductMediaRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new ProductMediaRepository();
    }

    public function findById(
        int $id
    ): ?ProductMedia {
        return $this->repository->findById($id);
    }

    public function exists(
        int $id
    ): bool {
        return $this->repository->exists($id);
    }

    public function findAllByProduct(
        int $productId
    ): array {
        return $this->repository->findAllByProduct(
            $productId
        );
    }

    public function findAllByVariant(
        int $variantId
    ): array {
        return $this->repository->findAllByVariant(
            $variantId
        );
    }

    public function create(
        int $productId,
        ?int $variantId,
        string $mediaType,
        string $filePath,
        ?string $thumbnailPath,
        ?string $altText,
        int $sortOrder,
        bool $isPrimary,
        string $status
    ): ?ProductMedia {
        return $this->repository->create(
            $productId,
            $variantId,
            $mediaType,
            $filePath,
            $thumbnailPath,
            $altText,
            $sortOrder,
            $isPrimary,
            $status
        );
    }

    public function update(
        int $id,
        int $productId,
        ?int $variantId,
        string $mediaType,
        string $filePath,
        ?string $thumbnailPath,
        ?string $altText,
        int $sortOrder,
        bool $isPrimary,
        string $status
    ): ?ProductMedia {
        return $this->repository->update(
            $id,
            $productId,
            $variantId,
            $mediaType,
            $filePath,
            $thumbnailPath,
            $altText,
            $sortOrder,
            $isPrimary,
            $status
        );
    }

    public function delete(
        int $id
    ): bool {
        return $this->repository->delete($id);
    }
}
