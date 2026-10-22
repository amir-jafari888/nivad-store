<?php

declare(strict_types=1);

final class AdminUserRoleRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    public function hasRole(
        int $adminUserId,
        int $roleId
    ): bool {
        if (
            $adminUserId < 1 ||
            $roleId < 1
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM admin_user_roles AS aur
             INNER JOIN admin_roles AS ar
                 ON ar.id = aur.role_id
                AND ar.status = :status
                AND ar.deleted_at IS NULL
             WHERE aur.admin_user_id = :admin_user_id
               AND aur.role_id = :role_id
             LIMIT 1'
        );

        $stmt->execute([
            'admin_user_id' => $adminUserId,
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function roleIdsForUser(
        int $adminUserId
    ): array {
        if ($adminUserId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT aur.role_id
             FROM admin_user_roles AS aur
             INNER JOIN admin_roles AS ar
                 ON ar.id = aur.role_id
                AND ar.status = :status
                AND ar.deleted_at IS NULL
             WHERE aur.admin_user_id = :admin_user_id'
        );

        $stmt->execute([
            'admin_user_id' => $adminUserId,
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
