<?php

declare(strict_types=1);

final class CategoryRepository
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
        int $categoryId
    ): ?Category {
        if ($categoryId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                parent_id,
                name,
                slug,
                description,
                status,
                sort_order,
                meta_title,
                meta_description,
                created_at,
                updated_at,
                deleted_at
             FROM categories
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $categoryId,
        ]);

        $category = $stmt->fetch();

        return $category !== false
            ? $this->hydrate($category)
            : null;
    }

    public function findBySlug(
        string $slug
    ): ?Category {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                parent_id,
                name,
                slug,
                description,
                status,
                sort_order,
                meta_title,
                meta_description,
                created_at,
                updated_at,
                deleted_at
             FROM categories
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        $category = $stmt->fetch();

        return $category !== false
            ? $this->hydrate($category)
            : null;
    }

    public function exists(
        int $categoryId
    ): bool {
        if ($categoryId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
             FROM categories
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $categoryId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(
        int $categoryId
    ): ?Category {
        if ($categoryId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                parent_id,
                name,
                slug,
                description,
                status,
                sort_order,
                meta_title,
                meta_description,
                created_at,
                updated_at,
                deleted_at
             FROM categories
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $categoryId,
            'status' => 'active',
        ]);

        $category = $stmt->fetch();

        return $category !== false
            ? $this->hydrate($category)
            : null;
    }

    private function hydrate(array $data): Category
    {
        return new Category(
            (int) $data['id'],
            $data['parent_id'] !== null
                ? (int) $data['parent_id']
                : null,
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
                : null,
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null
        );
    }

    public function create(
        ?int $parentId,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): Category {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (
            parent_id,
            name,
            slug,
            description,
            status,
            sort_order
        ) VALUES (
            :parent_id,
            :name,
            :slug,
            :description,
            :status,
            :sort_order
        )'
        );

        $stmt->execute([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        $categoryId = (int) $this->db->lastInsertId();

        $category = $this->findById($categoryId);

        if (!$category instanceof Category) {
            throw new RuntimeException(
                'Category could not be loaded after creation.'
            );
        }

        return $category;
    }

    public function update(
        int $categoryId,
        ?int $parentId,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): ?Category {
        if ($categoryId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE categories
         SET
            parent_id = :parent_id,
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
            'id' => $categoryId,
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        return $this->findById($categoryId);
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
         FROM categories
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
        int $categoryId
    ): bool {
        if ($categoryId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE categories
         SET deleted_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $categoryId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(
        int $categoryId
    ): bool {
        if ($categoryId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE categories
         SET deleted_at = NULL
         WHERE id = :id
           AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $categoryId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setStatus(
        int $categoryId,
        string $status
    ): ?Category {
        if ($categoryId < 1) {
            return null;
        }

        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE categories
         SET
            status = :status,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $categoryId,
            'status' => $status,
        ]);

        return $this->findById($categoryId);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
            id,
            parent_id,
            name,
            slug,
            description,
            status,
            sort_order,
            meta_title,
            meta_description,
            created_at,
            updated_at,
            deleted_at
         FROM categories
         WHERE deleted_at IS NULL
         ORDER BY id DESC'
        );

        $categories = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $this->hydrate($row);
        }

        return $categories;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT
            id,
            parent_id,
            name,
            slug,
            description,
            status,
            sort_order,
            meta_title,
            meta_description,
            created_at,
            updated_at,
            deleted_at
         FROM categories
         WHERE deleted_at IS NULL
           AND status = \'active\'
         ORDER BY id DESC'
        );

        $categories = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $this->hydrate($row);
        }

        return $categories;
    }
}
