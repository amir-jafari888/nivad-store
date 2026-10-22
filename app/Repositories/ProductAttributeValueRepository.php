<?php

declare(strict_types=1);

final class ProductAttributeValueRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?ProductAttributeValue
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attribute_values
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

    public function findBySlug(
        int $attributeId,
        string $slug
    ): ?ProductAttributeValue {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attribute_values
             WHERE attribute_id = :attribute_id
               AND slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'attribute_id' => $attributeId,
            'slug' => trim($slug),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM attribute_values
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(
        int $id
    ): ?ProductAttributeValue {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attribute_values
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
        int $attributeId,
        string $value,
        string $slug,
        string $status = 'active',
        int $sortOrder = 0
    ): ProductAttributeValue {
        $stmt = $this->pdo->prepare(
            'INSERT INTO attribute_values
            (
                attribute_id,
                value,
                slug,
                sort_order,
                status,
                created_at
            )
            VALUES
            (
                :attribute_id,
                :value,
                :slug,
                :sort_order,
                :status,
                CURRENT_TIMESTAMP
            )'
        );

        $stmt->execute([
            'attribute_id' => $attributeId,
            'value' => trim($value),
            'slug' => trim($slug),
            'sort_order' => $sortOrder,
            'status' => trim($status),
        ]);

        $created =
            $this->findById(
                (int)$this->pdo->lastInsertId()
            );

        if (!$created instanceof ProductAttributeValue) {
            throw new RuntimeException(
                'Attribute value could not be loaded after creation.'
            );
        }

        return $created;
    }

    public function update(
        int $id,
        int $attributeId,
        string $value,
        string $slug,
        string $status,
        int $sortOrder
    ): ?ProductAttributeValue {
        $stmt = $this->pdo->prepare(
            'UPDATE attribute_values
             SET attribute_id = :attribute_id,
                 value = :value,
                 slug = :slug,
                 status = :status,
                 sort_order = :sort_order,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'attribute_id' => $attributeId,
            'value' => trim($value),
            'slug' => trim($slug),
            'status' => trim($status),
            'sort_order' => $sortOrder,
        ]);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE attribute_values
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
            'UPDATE attribute_values
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

    public function setStatus(
        int $id,
        string $status
    ): ?ProductAttributeValue {
        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE attribute_values
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
     * @return ProductAttributeValue[]
     */
    public function findAllByAttribute(
        int $attributeId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attribute_values
             WHERE attribute_id = :attribute_id
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id DESC'
        );

        $stmt->execute([
            'attribute_id' => $attributeId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductAttributeValue =>
            $this->hydrate($row),
            $rows
        );
    }

    /**
     * @return ProductAttributeValue[]
     */
    public function findActiveByAttribute(
        int $attributeId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM attribute_values
             WHERE attribute_id = :attribute_id
               AND status = :status
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id DESC'
        );

        $stmt->execute([
            'attribute_id' => $attributeId,
            'status' => 'active',
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductAttributeValue =>
            $this->hydrate($row),
            $rows
        );
    }

    private function hydrate(
        array $row
    ): ProductAttributeValue {
        return new ProductAttributeValue(
            (int)$row['id'],
            (int)$row['attribute_id'],
            (string)$row['value'],
            (string)$row['slug'],
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
            $row['value_code'] !== null
                ? (string)$row['value_code']
                : null,
            $row['color_hex'] !== null
                ? (string)$row['color_hex']
                : null
        );
    }
}
