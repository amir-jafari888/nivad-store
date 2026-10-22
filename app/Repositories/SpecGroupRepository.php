<?php

declare(strict_types=1);

final class SpecGroupRepository
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
        int $groupId
    ): ?SpecGroup {
        if ($groupId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM spec_groups
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $groupId,
        ]);

        $group = $stmt->fetch();

        return $group !== false
            ? $this->hydrate($group)
            : null;
    }

    public function findBySlug(
        string $slug
    ): ?SpecGroup {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM spec_groups
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        $group = $stmt->fetch();

        return $group !== false
            ? $this->hydrate($group)
            : null;
    }

    public function exists(
        int $groupId
    ): bool {
        if ($groupId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
             FROM spec_groups
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $groupId,
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
             FROM spec_groups
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
        int $groupId
    ): ?SpecGroup {
        if ($groupId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM spec_groups
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $groupId,
            'status' => 'active',
        ]);

        $group = $stmt->fetch();

        return $group !== false
            ? $this->hydrate($group)
            : null;
    }

    public function create(
        string $name,
        string $slug,
        string $status,
        int $sortOrder
    ): SpecGroup {
        $stmt = $this->db->prepare(
            'INSERT INTO spec_groups (
                name,
                slug,
                sort_order,
                status
            ) VALUES (
                :name,
                :slug,
                :sort_order,
                :status
            )'
        );

        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);

        $groupId = (int)$this->db->lastInsertId();

        $group = $this->findById($groupId);

        if (!$group instanceof SpecGroup) {
            throw new RuntimeException(
                'SpecGroup could not be loaded after creation.'
            );
        }

        return $group;
    }

    public function update(
        int $groupId,
        string $name,
        string $slug,
        string $status,
        int $sortOrder
    ): ?SpecGroup {
        if ($groupId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE spec_groups
             SET
                name = :name,
                slug = :slug,
                sort_order = :sort_order,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $groupId,
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);

        return $this->findById($groupId);
    }

    public function delete(
        int $groupId
    ): bool {
        if ($groupId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE spec_groups
             SET deleted_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $groupId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(
        int $groupId
    ): bool {
        if ($groupId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE spec_groups
             SET deleted_at = NULL
             WHERE id = :id
               AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $groupId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setStatus(
        int $groupId,
        string $status
    ): ?SpecGroup {
        if ($groupId < 1) {
            return null;
        }

        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE spec_groups
             SET
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $groupId,
            'status' => $status,
        ]);

        return $this->findById($groupId);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                name,
                slug,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM spec_groups
             WHERE deleted_at IS NULL
             ORDER BY id DESC'
        );

        $groups = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $groups[] = $this->hydrate($row);
        }

        return $groups;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                name,
                slug,
                sort_order,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM spec_groups
             WHERE deleted_at IS NULL
               AND status = \'active\'
             ORDER BY id DESC'
        );

        $groups = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $groups[] = $this->hydrate($row);
        }

        return $groups;
    }

    private function hydrate(
        array $data
    ): SpecGroup {
        return new SpecGroup(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
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
