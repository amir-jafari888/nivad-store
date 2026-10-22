<?php

declare(strict_types=1);

final class DiscoveryTagRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findById(int $id): ?DiscoveryTag
    {
        if ($id < 1) {
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
             FROM discovery_tags
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findBySlug(string $slug): ?DiscoveryTag
    {
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
             FROM discovery_tags
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
             FROM discovery_tags
             WHERE status = \'active\'
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $rows = $stmt->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function create(
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): DiscoveryTag {
        $name = trim($name);
        $slug = trim($slug);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Discovery tag name is required.'
            );
        }

        if ($slug === '') {
            throw new InvalidArgumentException(
                'Discovery tag slug is required.'
            );
        }

        if (
            !in_array(
                $status,
                [
                    DiscoveryTag::STATUS_ACTIVE,
                    DiscoveryTag::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid discovery tag status.'
            );
        }

        if ($sortOrder < 0) {
            throw new InvalidArgumentException(
                'Invalid discovery tag sort order.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO discovery_tags (
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

        $id = (int)$this->db->lastInsertId();

        $tag = $this->findById($id);

        if (!$tag instanceof DiscoveryTag) {
            throw new RuntimeException(
                'Discovery tag could not be loaded after creation.'
            );
        }

        return $tag;
    }

    public function update(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        int $sortOrder
    ): ?DiscoveryTag {
        if ($id < 1) {
            return null;
        }

        $name = trim($name);
        $slug = trim($slug);

        if ($name === '' || $slug === '') {
            throw new InvalidArgumentException(
                'Discovery tag name and slug are required.'
            );
        }

        if (
            !in_array(
                $status,
                [
                    DiscoveryTag::STATUS_ACTIVE,
                    DiscoveryTag::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid discovery tag status.'
            );
        }

        if ($sortOrder < 0) {
            throw new InvalidArgumentException(
                'Invalid discovery tag sort order.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE discovery_tags
             SET
                name = :name,
                slug = :slug,
                description = :description,
                status = :status,
                sort_order = :sort_order
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE discovery_tags
             SET deleted_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(array $data): DiscoveryTag
    {
        return new DiscoveryTag(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
            $data['description'] !== null
                ? (string)$data['description']
                : null,
            (string)$data['status'],
            (int)$data['sort_order'],
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
