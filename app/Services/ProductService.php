<?php

declare(strict_types=1);

final class ProductService
{
    private ProductRepository $repository;

    public function __construct(?ProductRepository $repository = null)
    {
        $this->repository = $repository ?? new ProductRepository();
    }

    public function findById(int $id): ?Product
    {
        return $this->repository->findById($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->repository->findBySlug($slug);
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function findActiveById(int $id): ?Product
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
        int $categoryId,
        ?int $brandId,
        string $name,
        string $slug,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured
    ): Product {
        return $this->repository->create(
            $categoryId,
            $brandId,
            $name,
            $slug,
            $shortDescription,
            $description,
            $status,
            $isFeatured
        );
    }

    public function update(
        int $productId,
        int $categoryId,
        ?int $brandId,
        string $name,
        string $slug,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured
    ): ?Product {
        return $this->repository->update(
            $productId,
            $categoryId,
            $brandId,
            $name,
            $slug,
            $shortDescription,
            $description,
            $status,
            $isFeatured
        );
    }

    public function delete(
        int $productId
    ): bool {
        return $this->repository->delete(
            $productId
        );
    }

    public function restore(
        int $productId
    ): bool {
        return $this->repository->restore(
            $productId
        );
    }

    public function setStatus(
        int $productId,
        string $status
    ): ?Product {
        return $this->repository->setStatus(
            $productId,
            $status
        );
    }

    public function adminFindAll(): array
    {
        return $this->repository->adminFindAll();
    }

    public function adminFindById(
        int $productId
    ): ?array {
        return $this->repository->adminFindById(
            $productId
        );
    }

    public function adminExistsBySlug(
        string $slug,
        ?int $exceptId = null
    ): bool {
        return $this->repository->adminExistsBySlug(
            $slug,
            $exceptId
        );
    }

    public function adminExistsBySku(
        string $sku,
        ?int $exceptId = null
    ): bool {
        return $this->repository->adminExistsBySku(
            $sku,
            $exceptId
        );
    }

    public function adminCreate(
        int $brandId,
        int $productTypeId,
        string $name,
        string $slug,
        string $sku,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured,
        int $sortOrder,
        ?string $metaTitle,
        ?string $metaDescription
    ): array {
        return $this->repository->adminCreate(
            $brandId,
            $productTypeId,
            $name,
            $slug,
            $sku,
            $shortDescription,
            $description,
            $status,
            $isFeatured,
            $sortOrder,
            $metaTitle,
            $metaDescription
        );
    }

    public function adminUpdate(
        int $productId,
        int $brandId,
        int $productTypeId,
        string $name,
        string $slug,
        string $sku,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured,
        int $sortOrder,
        ?string $metaTitle,
        ?string $metaDescription
    ): ?array {
        return $this->repository->adminUpdate(
            $productId,
            $brandId,
            $productTypeId,
            $name,
            $slug,
            $sku,
            $shortDescription,
            $description,
            $status,
            $isFeatured,
            $sortOrder,
            $metaTitle,
            $metaDescription
        );
    }
}
