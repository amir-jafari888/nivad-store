<?php

declare(strict_types=1);

final class ProductVariantService
{
    private ProductVariantRepository $repository;

    public function __construct(
        ?ProductVariantRepository $repository = null
    ) {
        $this->repository = $repository
            ?? new ProductVariantRepository();
    }

    public function findById(
        int $id
    ): ?ProductVariant {
        return $this->repository->findById($id);
    }

    public function findBySku(
        string $sku
    ): ?ProductVariant {
        return $this->repository->findBySku($sku);
    }

    public function findByBarcode(
        string $barcode
    ): ?ProductVariant {
        return $this->repository->findByBarcode($barcode);
    }

    public function exists(
        int $id
    ): bool {
        return $this->repository->exists($id);
    }

    public function findActiveById(
        int $id
    ): ?ProductVariant {
        return $this->repository->findActiveById($id);
    }

    public function create(
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status = 'active',
        bool $isDefault = false,
        int $sortOrder = 0
    ): ?ProductVariant {
        return $this->repository->create(
            $productId,
            $sku,
            $barcode,
            $price,
            $compareAtPrice,
            $currency,
            $status,
            $isDefault,
            $sortOrder
        );
    }

    public function update(
        int $id,
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status,
        bool $isDefault,
        int $sortOrder
    ): ?ProductVariant {
        return $this->repository->update(
            $id,
            $productId,
            $sku,
            $barcode,
            $price,
            $compareAtPrice,
            $currency,
            $status,
            $isDefault,
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
    ): ?ProductVariant {
        return $this->repository->setStatus(
            $id,
            $status
        );
    }

    public function setDefault(
        int $id
    ): ?ProductVariant {
        return $this->repository->setDefault($id);
    }

    public function clearDefault(
        int $id
    ): ?ProductVariant {
        return $this->repository->clearDefault($id);
    }

    public function findAllByProduct(
        int $productId
    ): array {
        return $this->repository->findAllByProduct(
            $productId
        );
    }

    public function findActiveByProduct(
        int $productId
    ): array {
        return $this->repository->findActiveByProduct(
            $productId
        );
    }

    public function findDefaultByProduct(
        int $productId
    ): ?ProductVariant {
        return $this->repository->findDefaultByProduct(
            $productId
        );
    }

    public function adminFindAllByProductIncludingDeleted(
        int $productId
    ): array {
        return $this->repository
            ->adminFindAllByProductIncludingDeleted($productId);
    }

    public function adminFindById(
        int $id
    ): ?array {
        return $this->repository->adminFindById($id);
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

    public function adminExistsByBarcode(
        string $barcode,
        ?int $exceptId = null
    ): bool {
        return $this->repository->adminExistsByBarcode(
            $barcode,
            $exceptId
        );
    }

    public function adminCreate(
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status,
        bool $isDefault,
        int $sortOrder
    ): array {
        return $this->repository->adminCreate(
            $productId,
            $sku,
            $barcode,
            $price,
            $compareAtPrice,
            $currency,
            $status,
            $isDefault,
            $sortOrder
        );
    }

    public function adminUpdate(
        int $id,
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status,
        bool $isDefault,
        int $sortOrder
    ): ?array {
        return $this->repository->adminUpdate(
            $id,
            $productId,
            $sku,
            $barcode,
            $price,
            $compareAtPrice,
            $currency,
            $status,
            $isDefault,
            $sortOrder
        );
    }

    public function adminDelete(
        int $id
    ): bool {
        return $this->repository->adminDelete($id);
    }

    public function adminRestore(
        int $id
    ): bool {
        return $this->repository->adminRestore($id);
    }

    public function adminSetDefault(
        int $id
    ): ?array {
        return $this->repository->adminSetDefault($id);
    }

    public function adminClearDefault(
        int $id
    ): ?array {
        return $this->repository->adminClearDefault($id);
    }

    public function adminSetStatus(
        int $id,
        string $status
    ): ?array {
        return $this->repository->adminSetStatus(
            $id,
            $status
        );
    }
}
