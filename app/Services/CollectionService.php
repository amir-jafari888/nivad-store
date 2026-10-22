<?php

declare(strict_types=1);

final class CollectionService
{
    private CollectionRepository $repository;

    public function __construct(
        ?CollectionRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new CollectionRepository();
    }

    public function findById(
        int $collectionId
    ): ?Collection {
        return $this->repository->findById(
            $collectionId
        );
    }

    public function findBySlug(
        string $slug
    ): ?Collection {
        return $this->repository->findBySlug(
            $slug
        );
    }

    public function findActiveById(
        int $collectionId
    ): ?Collection {
        $collection =
            $this->repository->findActiveById(
                $collectionId
            );

        if (
            $collection === null ||
            !$this->isCurrentlyAvailable(
                $collection
            )
        ) {
            return null;
        }

        return $collection;
    }

    public function findActive(): array
    {
        return array_values(
            array_filter(
                $this->repository->findActive(),
                fn(Collection $collection): bool =>
                $this->isCurrentlyAvailable(
                    $collection
                )
            )
        );
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findProductIds(
        int $collectionId
    ): array {
        return $this->repository->findProductIds(
            $collectionId
        );
    }

    public function findProducts(
        int $collectionId
    ): array {
        return $this->repository
            ->findProductsByCollectionId(
                $collectionId
            );
    }

    public function create(
        string $name,
        string $slug,
        ?string $description,
        string $collectionType,
        string $status,
        int $sortOrder,
        ?string $startsAt,
        ?string $endsAt
    ): ?Collection {
        return $this->repository->create(
            $name,
            $slug,
            $description,
            $collectionType,
            $status,
            $sortOrder,
            $startsAt,
            $endsAt
        );
    }

    public function update(
        int $collectionId,
        string $name,
        string $slug,
        ?string $description,
        string $collectionType,
        string $status,
        int $sortOrder,
        ?string $startsAt,
        ?string $endsAt
    ): ?Collection {
        return $this->repository->update(
            $collectionId,
            $name,
            $slug,
            $description,
            $collectionType,
            $status,
            $sortOrder,
            $startsAt,
            $endsAt
        );
    }

    public function delete(
        int $collectionId
    ): bool {
        return $this->repository->delete(
            $collectionId
        );
    }

    public function restore(
        int $collectionId
    ): ?Collection {
        return $this->repository->restore(
            $collectionId
        );
    }

    public function updateStatus(
        int $collectionId,
        string $status
    ): ?Collection {
        if (
            !in_array(
                $status,
                [
                    Collection::STATUS_ACTIVE,
                    Collection::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            return null;
        }

        return $this->repository->updateStatus(
            $collectionId,
            $status
        );
    }

    public function attachProduct(
        int $collectionId,
        int $productId,
        int $sortOrder = 0
    ): bool {
        return $this->repository->attachProduct(
            $collectionId,
            $productId,
            $sortOrder
        );
    }

    public function detachProduct(
        int $collectionId,
        int $productId
    ): bool {
        return $this->repository->detachProduct(
            $collectionId,
            $productId
        );
    }

    public function isCurrentlyAvailable(
        Collection $collection
    ): bool {
        if (
            $collection->status !==
            Collection::STATUS_ACTIVE
        ) {
            return false;
        }

        $now = time();

        if ($collection->startsAt !== null) {
            $startsAt =
                strtotime($collection->startsAt);

            if (
                $startsAt !== false &&
                $now < $startsAt
            ) {
                return false;
            }
        }

        if ($collection->endsAt !== null) {
            $endsAt =
                strtotime($collection->endsAt);

            if (
                $endsAt !== false &&
                $now > $endsAt
            ) {
                return false;
            }
        }

        return true;
    }

    public function validate(
        array $data
    ): array {
        return CollectionValidator::validate(
            $data
        );
    }

    public function validateProductMapping(
        int $collectionId,
        int $productId,
        int $sortOrder = 0
    ): array {
        return CollectionValidator::validateProductMapping(
            $collectionId,
            $productId,
            $sortOrder
        );
    }

    public function attachProductValidated(
        int $collectionId,
        int $productId,
        int $sortOrder = 0
    ): bool {
        $errors =
            $this->validateProductMapping(
                $collectionId,
                $productId,
                $sortOrder
            );

        if ($errors !== []) {
            return false;
        }

        if (
            $this->repository->findById(
                $collectionId
            ) === null
        ) {
            return false;
        }

        return $this->repository->attachProduct(
            $collectionId,
            $productId,
            $sortOrder
        );
    }

    public function detachProductValidated(
        int $collectionId,
        int $productId
    ): bool {
        $errors =
            $this->validateProductMapping(
                $collectionId,
                $productId
            );

        if ($errors !== []) {
            return false;
        }

        return $this->repository->detachProduct(
            $collectionId,
            $productId
        );
    }
}
