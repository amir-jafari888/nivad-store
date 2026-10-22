<?php

declare(strict_types=1);

final class BrandRepository
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
        int $brandId
    ): ?Brand {
        if ($brandId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM brands
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $brandId,
        ]);

        $brand = $stmt->fetch();

        return $brand !== false
            ? $this->hydrate($brand)
            : null;
    }

    public function findBySlug(
        string $slug
    ): ?Brand {
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
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM brands
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        $brand = $stmt->fetch();

        return $brand !== false
            ? $this->hydrate($brand)
            : null;
    }

    public function exists(
        int $brandId
    ): bool {
        if ($brandId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
             FROM brands
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $brandId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(
        int $brandId
    ): ?Brand {
        if ($brandId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM brands
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $brandId,
            'status' => 'active',
        ]);

        $brand = $stmt->fetch();

        return $brand !== false
            ? $this->hydrate($brand)
            : null;
    }

    private function hydrate(array $data): Brand
    {
        return new Brand(
            (int) $data['id'],
            (string) $data['name'],
            (string) $data['slug'],
            $data['description'] !== null
                ? (string) $data['description']
                : null,
            (string) $data['status'],
            (int) $data['sort_order'],
            $data['created_at'] !== null
                ? (string) $data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string) $data['updated_at']
                : null,
            $data['deleted_at'] !== null
                ? (string) $data['deleted_at']
                : null
        );
    }

    public function create(
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): Brand {
        $stmt = $this->db->prepare(
            'INSERT INTO brands (
            name,
            slug,
            description,
            status,
            sort_order
        ) VALUES (
            :name,
            :slug,
            :description,
            :status,
            :sort_order
        )'
        );

        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        $brandId = (int) $this->db->lastInsertId();

        $brand = $this->findById($brandId);

        if (!$brand instanceof Brand) {
            throw new RuntimeException(
                'Brand could not be loaded after creation.'
            );
        }

        return $brand;
    }

    public function update(
        int $brandId,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): ?Brand {
        if ($brandId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE brands
         SET
            name = :name,
            slug = :slug,
            description = :description,
            status = :status,
            sort_order = :sort_order,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $brandId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        return $this->findById($brandId);
    }

    public function existsBySlug(
        string $slug
    ): bool {
        $slug = trim($slug);

        if ($slug === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
         FROM brands
         WHERE slug = :slug
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function delete(
        int $brandId
    ): bool {
        if ($brandId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE brands
         SET deleted_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $brandId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(
        int $brandId
    ): bool {
        if ($brandId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE brands
         SET deleted_at = NULL
         WHERE id = :id
           AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $brandId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setStatus(
        int $brandId,
        string $status
    ): ?Brand {
        if ($brandId < 1) {
            return null;
        }

        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE brands
         SET
            status = :status,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $brandId,
            'status' => $status,
        ]);

        return $this->findById($brandId);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
            id,
            name,
            slug,
            description,
            status,
            sort_order,
            created_at,
            updated_at,
            deleted_at
         FROM brands
         WHERE deleted_at IS NULL
         ORDER BY id DESC'
        );

        $brands = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $brands[] = $this->hydrate($row);
        }

        return $brands;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT
            id,
            name,
            slug,
            description,
            status,
            sort_order,
            created_at,
            updated_at,
            deleted_at
         FROM brands
         WHERE deleted_at IS NULL
           AND status = \'active\'
         ORDER BY id DESC'
        );

        $brands = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $brands[] = $this->hydrate($row);
        }

        return $brands;
    }
}
