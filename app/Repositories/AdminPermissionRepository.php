<?php

declare(strict_types=1);

final class AdminPermissionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findByName(
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
                module,
                action,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM admin_permissions
             WHERE name = :name
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'name' => $name,
            'status' => 'active',
        ]);

        $permission = $stmt->fetch();

        return $permission !== false
            ? $permission
            : null;
    }
}
