<?php

declare(strict_types=1);

final class DiscoveryService
{
    private DiscoveryTagRepository $tagRepository;
    private DiscoveryProductRepository $productRepository;

    public function __construct(
        ?DiscoveryTagRepository $tagRepository = null,
        ?DiscoveryProductRepository $productRepository = null
    ) {
        $this->tagRepository =
            $tagRepository ?? new DiscoveryTagRepository();

        $this->productRepository =
            $productRepository ?? new DiscoveryProductRepository();
    }

    public function getActiveTags(): array
    {
        return $this->tagRepository->findActive();
    }

    public function getProductsByTagId(
        int $tagId,
        int $limit = 20,
        int $offset = 0
    ): array {
        if ($tagId <= 0) {
            throw new InvalidArgumentException(
                'Invalid discovery tag id.'
            );
        }

        if ($limit <= 0 || $limit > 100) {
            throw new InvalidArgumentException(
                'Invalid discovery result limit.'
            );
        }

        if ($offset < 0) {
            throw new InvalidArgumentException(
                'Invalid discovery result offset.'
            );
        }

        $tag = $this->tagRepository->findById($tagId);

        if ($tag === null) {
            throw new RuntimeException(
                'Discovery tag not found.'
            );
        }

        if ($tag->status !== DiscoveryTag::STATUS_ACTIVE) {
            return [];
        }

        return $this->productRepository
            ->findAvailableProductsByTagId(
                $tagId,
                $limit,
                $offset
            );
    }

    public function getProductsByTagSlug(
        string $slug,
        int $limit = 20,
        int $offset = 0
    ): array {
        $slug = trim($slug);

        if ($slug === '') {
            throw new InvalidArgumentException(
                'Discovery tag slug is required.'
            );
        }

        $tag = $this->tagRepository->findBySlug($slug);

        if ($tag === null) {
            throw new RuntimeException(
                'Discovery tag not found.'
            );
        }

        return $this->getProductsByTagId(
            $tag->id,
            $limit,
            $offset
        );
    }

    public function getPaginatedProductsByTagId(
        int $tagId,
        int $limit = 20,
        int $offset = 0
    ): array {
        $items = $this->getProductsByTagId(
            $tagId,
            $limit,
            $offset
        );

        return [
            'items' => $items,
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($items),
            'hasMore' => count($items) === $limit,
        ];
    }

    public function isTagActive(int $tagId): bool
    {
        if ($tagId <= 0) {
            return false;
        }

        $tag = $this->tagRepository->findById($tagId);

        return $tag !== null &&
            $tag->status === DiscoveryTag::STATUS_ACTIVE;
    }

    public function createTag(
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder = 0
    ): DiscoveryTag {
        return $this->tagRepository->create(
            trim($name),
            trim($slug),
            $description !== null
                ? trim($description)
                : null,
            trim($status),
            $sortOrder
        );
    }

    public function updateTag(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder = 0
    ): ?DiscoveryTag {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Invalid discovery tag id.'
            );
        }

        return $this->tagRepository->update(
            $id,
            trim($name),
            trim($slug),
            $description !== null
                ? trim($description)
                : null,
            trim($status),
            $sortOrder
        );
    }

    public function deleteTag(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return $this->tagRepository->delete($id);
    }
}
