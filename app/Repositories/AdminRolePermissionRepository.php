<?php

declare(strict_types=1);

final class AdminRolePermissionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    public function hasPermission(
        int $roleId,
        int $permissionId
    ): bool {
        if (
            $roleId < 1 ||
            $permissionId < 1
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM admin_role_permissions AS arp
             INNER JOIN admin_permissions AS ap
                 ON ap.id = arp.permission_id
                AND ap.status = :status
                AND ap.deleted_at IS NULL
             WHERE arp.role_id = :role_id
               AND arp.permission_id = :permission_id
             LIMIT 1'
        );

        $stmt->execute([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'status' => 'active',
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function permissionIdsForRole(
        int $roleId
    ): array {
        if ($roleId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT arp.permission_id
             FROM admin_role_permissions AS arp
             INNER JOIN admin_permissions AS ap
                 ON ap.id = arp.permission_id
                AND ap.status = :status
                AND ap.deleted_at IS NULL
             WHERE arp.role_id = :role_id'
        );

        $stmt->execute([
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        return array_map(
            'intval',
            $stmt->fetchAll(
                PDO::FETCH_COLUMN
            )
        );
    }
}
