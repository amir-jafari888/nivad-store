<?php

declare(strict_types=1);

final class ProductAttributeRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?ProductAttribute
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attributes
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function findBySlug(string $slug): ?ProductAttribute
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attributes
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => trim($slug),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM attributes
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(int $id): ?ProductAttribute
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attributes
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'status' => 'active',
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function create(
        string $name,
        string $slug,
        string $type,
        string $status = 'active',
        int $sortOrder = 0
    ): ?ProductAttribute {
        $stmt = $this->pdo->prepare(
            'INSERT INTO attributes
            (
                name,
                slug,
                input_type,
                status,
                sort_order,
                created_at
            )
            VALUES
            (
                :name,
                :slug,
                :type,
                :status,
                :sort_order,
                CURRENT_TIMESTAMP
            )'
        );

        $stmt->execute([
            'name' => trim($name),
            'slug' => trim($slug),
            'type' => trim($type),
            'status' => trim($status),
            'sort_order' => $sortOrder,
        ]);

        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function update(
        int $id,
        string $name,
        string $slug,
        string $type,
        string $status,
        int $sortOrder
    ): ?ProductAttribute {
        $stmt = $this->pdo->prepare(
            'UPDATE attributes
             SET name = :name,
                slug = :slug,
                input_type = :type,
                status = :status,
                sort_order = :sort_order,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'name' => trim($name),
            'slug' => trim($slug),
            'type' => trim($type),
            'status' => trim($status),
            'sort_order' => $sortOrder,
        ]);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE attributes
             SET deleted_at = CURRENT_TIMESTAMP,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE attributes
             SET deleted_at = NULL,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setStatus(int $id, string $status): ?ProductAttribute
    {
        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE attributes
             SET status = :status,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        return $this->findById($id);
    }

    /**
     * @return ProductAttribute[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT *
             FROM attributes
             WHERE deleted_at IS NULL
             ORDER BY sort_order ASC, id DESC'
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductAttribute => $this->hydrate($row),
            $rows
        );
    }

    /**
     * @return ProductAttribute[]
     */
    public function findActive(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attributes
             WHERE status = :status
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id DESC'
        );

        $stmt->execute([
            'status' => 'active',
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductAttribute => $this->hydrate($row),
            $rows
        );
    }

    private function hydrate(array $row): ProductAttribute
    {
        return new ProductAttribute(
            (int)$row['id'],
            (string)$row['name'],
            (string)$row['slug'],
            (string)$row['input_type'],
            (string)$row['status'],
            (int)$row['sort_order'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null,
            $row['deleted_at'] !== null
                ? (string)$row['deleted_at']
                : null,
            (bool)$row['is_variant'],
            (bool)$row['is_filterable'],
            (bool)$row['is_required']
        );
    }
}
