<?php

declare(strict_types=1);

final class CollectionRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findById(
        int $collectionId
    ): ?Collection {
        if ($collectionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                collection_type,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM collections
             WHERE id = :id
             AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $collectionId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findBySlug(
        string $slug
    ): ?Collection {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                collection_type,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM collections
             WHERE slug = :slug
             AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findActiveById(
        int $collectionId
    ): ?Collection {
        if ($collectionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                collection_type,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM collections
             WHERE id = :id
             AND status = :status
             AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $collectionId,
            'status' => Collection::STATUS_ACTIVE,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findActive(): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                collection_type,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM collections
             WHERE status = :status
             AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'status' => Collection::STATUS_ACTIVE,
        ]);

        return $this->hydrateAll(
            $stmt->fetchAll()
        );
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                name,
                slug,
                description,
                collection_type,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM collections
             ORDER BY sort_order ASC, id ASC'
        );

        return $this->hydrateAll(
            $stmt->fetchAll()
        );
    }

    public function findDeletedById(
        int $collectionId
    ): ?Collection {
        if ($collectionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                collection_type,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM collections
             WHERE id = :id
             AND deleted_at IS NOT NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $collectionId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
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
        $stmt = $this->db->prepare(
            'INSERT INTO collections (
                name,
                slug,
                description,
                collection_type,
                status,
                sort_order,
                starts_at,
                ends_at
             ) VALUES (
                :name,
                :slug,
                :description,
                :collection_type,
                :status,
                :sort_order,
                :starts_at,
                :ends_at
             )'
        );

        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'collection_type' => $collectionType,
            'status' => $status,
            'sort_order' => $sortOrder,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return $this->findById(
            (int)$this->db->lastInsertId()
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
        if ($collectionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE collections
             SET
                name = :name,
                slug = :slug,
                description = :description,
                collection_type = :collection_type,
                status = :status,
                sort_order = :sort_order,
                starts_at = :starts_at,
                ends_at = :ends_at
             WHERE id = :id
             AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $collectionId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'collection_type' => $collectionType,
            'status' => $status,
            'sort_order' => $sortOrder,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return $this->findById(
            $collectionId
        );
    }

    public function delete(
        int $collectionId
    ): bool {
        if ($collectionId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE collections
             SET deleted_at = NOW()
             WHERE id = :id
             AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $collectionId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(
        int $collectionId
    ): ?Collection {
        if ($collectionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE collections
             SET deleted_at = NULL
             WHERE id = :id
             AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $collectionId,
        ]);

        return $this->findById(
            $collectionId
        );
    }

    public function updateStatus(
        int $collectionId,
        string $status
    ): ?Collection {
        if ($collectionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE collections
             SET status = :status
             WHERE id = :id
             AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $collectionId,
            'status' => $status,
        ]);

        return $this->findById(
            $collectionId
        );
    }

    public function findProductIds(
        int $collectionId
    ): array {
        if ($collectionId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT product_id
             FROM collection_products
             WHERE collection_id = :collection_id
             ORDER BY sort_order ASC, product_id ASC'
        );

        $stmt->execute([
            'collection_id' => $collectionId,
        ]);

        return array_map(
            static fn($id): int => (int)$id,
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );
    }

    public function findProductsByCollectionId(
        int $collectionId
    ): array {
        if ($collectionId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT p.*
             FROM collection_products cp
             INNER JOIN products p
                ON p.id = cp.product_id
             WHERE cp.collection_id = :collection_id
             AND p.status = :status
             AND p.deleted_at IS NULL
             ORDER BY cp.sort_order ASC, cp.product_id ASC'
        );

        $stmt->execute([
            'collection_id' => $collectionId,
            'status' => 'active',
        ]);

        return $stmt->fetchAll();
    }

    public function attachProduct(
        int $collectionId,
        int $productId,
        int $sortOrder = 0
    ): bool {
        if (
            $collectionId < 1 ||
            $productId < 1 ||
            $sortOrder < 0
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO collection_products (
                collection_id,
                product_id,
                sort_order
             ) VALUES (
                :collection_id,
                :product_id,
                :sort_order
             )'
        );

        try {
            return $stmt->execute([
                'collection_id' => $collectionId,
                'product_id' => $productId,
                'sort_order' => $sortOrder,
            ]);
        } catch (PDOException $exception) {
            if (
                isset($exception->errorInfo[1]) &&
                (int)$exception->errorInfo[1] === 1062
            ) {
                return false;
            }

            throw $exception;
        }
    }

    public function detachProduct(
        int $collectionId,
        int $productId
    ): bool {
        if (
            $collectionId < 1 ||
            $productId < 1
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM collection_products
             WHERE collection_id = :collection_id
             AND product_id = :product_id'
        );

        $stmt->execute([
            'collection_id' => $collectionId,
            'product_id' => $productId,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(
        array $data
    ): Collection {
        return new Collection(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
            $data['description'] !== null
                ? (string)$data['description']
                : null,
            (string)$data['collection_type'],
            (string)$data['status'],
            (int)$data['sort_order'],
            $data['starts_at'] !== null
                ? (string)$data['starts_at']
                : null,
            $data['ends_at'] !== null
                ? (string)$data['ends_at']
                : null,
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string)$data['updated_at']
                : null,
            $data['deleted_at'] !== null
                ? (string)$data['deleted_at']
                : null
        );
    }

    private function hydrateAll(
        array $rows
    ): array {
        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }
}
