<?php

declare(strict_types=1);

final class SpecificationRepository
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
        int $specificationId
    ): ?Specification {
        if ($specificationId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM specifications
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $specificationId,
        ]);

        $specification = $stmt->fetch();

        return $specification !== false
            ? $this->hydrate($specification)
            : null;
    }

    public function findBySlug(
        string $slug
    ): ?Specification {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM specifications
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        $specification = $stmt->fetch();

        return $specification !== false
            ? $this->hydrate($specification)
            : null;
    }

    public function exists(
        int $specificationId
    ): bool {
        if ($specificationId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
             FROM specifications
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $specificationId,
        ]);

        return $stmt->fetchColumn() !== false;
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
             FROM specifications
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(
        int $specificationId
    ): ?Specification {
        if ($specificationId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM specifications
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $specificationId,
            'status' => 'active',
        ]);

        $specification = $stmt->fetch();

        return $specification !== false
            ? $this->hydrate($specification)
            : null;
    }

    public function create(
        int $specGroupId,
        string $name,
        string $slug,
        string $valueType,
        ?string $unit,
        int $sortOrder,
        string $status
    ): Specification {
        $stmt = $this->db->prepare(
            'INSERT INTO specifications (
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status
            ) VALUES (
                :spec_group_id,
                :name,
                :slug,
                :value_type,
                :unit,
                :sort_order,
                :status
            )'
        );

        $stmt->execute([
            'spec_group_id' => $specGroupId,
            'name' => $name,
            'slug' => $slug,
            'value_type' => $valueType,
            'unit' => $unit,
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);

        $specificationId =
            (int)$this->db->lastInsertId();

        $specification =
            $this->findById($specificationId);

        if (
            !$specification
                instanceof Specification
        ) {
            throw new RuntimeException(
                'Specification could not be loaded after creation.'
            );
        }

        return $specification;
    }

    public function update(
        int $specificationId,
        int $specGroupId,
        string $name,
        string $slug,
        string $valueType,
        ?string $unit,
        int $sortOrder,
        string $status
    ): ?Specification {
        if ($specificationId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE specifications
             SET
                spec_group_id = :spec_group_id,
                name = :name,
                slug = :slug,
                value_type = :value_type,
                unit = :unit,
                sort_order = :sort_order,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $specificationId,
            'spec_group_id' => $specGroupId,
            'name' => $name,
            'slug' => $slug,
            'value_type' => $valueType,
            'unit' => $unit,
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);

        return $this->findById($specificationId);
    }

    public function delete(
        int $specificationId
    ): bool {
        if ($specificationId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE specifications
             SET deleted_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $specificationId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(
        int $specificationId
    ): bool {
        if ($specificationId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE specifications
             SET deleted_at = NULL
             WHERE id = :id
               AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $specificationId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setStatus(
        int $specificationId,
        string $status
    ): ?Specification {
        if ($specificationId < 1) {
            return null;
        }

        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE specifications
             SET
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $specificationId,
            'status' => $status,
        ]);

        return $this->findById($specificationId);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM specifications
             WHERE deleted_at IS NULL
             ORDER BY id DESC'
        );

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM specifications
             WHERE deleted_at IS NULL
               AND status = \'active\'
             ORDER BY id DESC'
        );

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function findAllByGroup(
        int $specGroupId
    ): array {
        if ($specGroupId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM specifications
             WHERE spec_group_id = :spec_group_id
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'spec_group_id' => $specGroupId,
        ]);

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function findActiveByGroup(
        int $specGroupId
    ): array {
        if ($specGroupId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                spec_group_id,
                name,
                slug,
                value_type,
                unit,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM specifications
             WHERE spec_group_id = :spec_group_id
               AND status = \'active\'
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'spec_group_id' => $specGroupId,
        ]);

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    private function hydrate(
        array $data
    ): Specification {
        return new Specification(
            (int)$data['id'],
            (int)$data['spec_group_id'],
            (string)$data['name'],
            (string)$data['slug'],
            (string)$data['value_type'],
            $data['unit'] !== null
                ? (string)$data['unit']
                : null,
            (int)$data['sort_order'],
            (string)$data['status'],
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
}
