<?php

declare(strict_types=1);

final class AdminRoleRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findById(
        int $roleId
    ): ?array {
        if ($roleId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM admin_roles
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $roleId,
        ]);

        $role = $stmt->fetch();

        return $role !== false
            ? $role
            : null;
    }

    public function findActiveByName(
        string $name
    ): ?array {
        if ($name === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM admin_roles
             WHERE name = :name
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'name' => $name,
            'status' => 'active',
        ]);

        $role = $stmt->fetch();

        return $role !== false
            ? $role
            : null;
    }
}