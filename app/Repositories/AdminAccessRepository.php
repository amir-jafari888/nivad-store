<?php

declare(strict_types=1);

final class AdminAccessRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function admins(int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->db->query(
            'SELECT a.id, a.username, a.email, a.first_name, a.last_name, a.status,
                    a.last_login_at, a.created_at,
                    GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ", ") AS role_names,
                    GROUP_CONCAT(DISTINCT r.slug ORDER BY r.slug SEPARATOR ",") AS role_slugs
             FROM admin_users a
             LEFT JOIN admin_user_roles ur ON ur.admin_user_id = a.id
             LEFT JOIN admin_roles r ON r.id = ur.role_id AND r.status = "active" AND r.deleted_at IS NULL
             WHERE a.deleted_at IS NULL
             GROUP BY a.id
             ORDER BY a.id DESC
             LIMIT ' . $limit
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function roles(int $limit = 200): array
    {
        $limit = max(1, min(300, $limit));
        $stmt = $this->db->query(
            'SELECT r.id, r.name, r.slug, r.description, r.status, r.created_at,
                    COUNT(DISTINCT rp.permission_id) AS permission_count,
                    COUNT(DISTINCT ur.admin_user_id) AS admin_count
             FROM admin_roles r
             LEFT JOIN admin_role_permissions rp ON rp.role_id = r.id
             LEFT JOIN admin_user_roles ur ON ur.role_id = r.id
             WHERE r.deleted_at IS NULL
             GROUP BY r.id
             ORDER BY r.id ASC
             LIMIT ' . $limit
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function permissions(int $limit = 500): array
    {
        $limit = max(1, min(1000, $limit));
        $stmt = $this->db->query(
            'SELECT id, name, slug, description, module, action, status
             FROM admin_permissions
             WHERE status = "active" AND deleted_at IS NULL
             ORDER BY module, action, name
             LIMIT ' . $limit
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function permissionsForRole(int $roleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.name, p.slug, p.module, p.action, p.description
             FROM admin_role_permissions rp
             INNER JOIN admin_permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id
               AND p.status = "active"
               AND p.deleted_at IS NULL
             ORDER BY p.module, p.action'
        );
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAdmin(array $data, array $roleIds): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO admin_users (username, email, password_hash, first_name, last_name, status)
                 VALUES (:username, :email, :password_hash, :first_name, :last_name, :status)'
            );
            $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => $data['password_hash'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'status' => 'active',
            ]);
            $adminId = (int)$this->db->lastInsertId();
            $this->replaceAdminRoles($adminId, $roleIds);
            $this->db->commit();
            return $adminId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function updateAdmin(int $id, array $data): bool
    {
        $sql = 'UPDATE admin_users
                SET email = :email, first_name = :first_name, last_name = :last_name, status = :status';
        if (isset($data['password_hash'])) {
            $sql .= ', password_hash = :password_hash';
        }
        $sql .= ' WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $params = [
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'status' => $data['status'],
            'id' => $id,
        ];
        if (isset($data['password_hash'])) $params['password_hash'] = $data['password_hash'];
        $stmt->execute($params);
        $check = $this->db->prepare('SELECT 1 FROM admin_users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $check->execute(['id' => $id]);
        return $check->fetchColumn() !== false;
    }

    public function replaceAdminRoles(int $adminId, array $roleIds): void
    {
        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $delete = $this->db->prepare('DELETE FROM admin_user_roles WHERE admin_user_id = :admin_id');
            $delete->execute(['admin_id' => $adminId]);
            if ($roleIds !== []) {
                $insert = $this->db->prepare('INSERT INTO admin_user_roles (admin_user_id, role_id) VALUES (:admin_id, :role_id)');
                foreach ($roleIds as $roleId) {
                    $insert->execute(['admin_id' => $adminId, 'role_id' => $roleId]);
                }
            }
            if ($ownsTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function adminHasRoleSlug(int $adminId, string $slug): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM admin_user_roles ur
             INNER JOIN admin_roles r ON r.id = ur.role_id
             WHERE ur.admin_user_id = :admin_id
               AND r.slug = :slug
               AND r.status = "active"
               AND r.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['admin_id' => $adminId, 'slug' => $slug]);
        return $stmt->fetchColumn() !== false;
    }

    public function createRole(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_roles (name, slug, description, status)
             VALUES (:name, :slug, :description, :status)'
        );
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function updateRole(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_roles SET name = :name, description = :description, status = :status
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'],
            'id' => $id,
        ]);
        $check = $this->db->prepare('SELECT 1 FROM admin_roles WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $check->execute(['id' => $id]);
        return $check->fetchColumn() !== false;
    }

    public function replaceRolePermissions(int $roleId, array $permissionIds): void
    {
        $this->db->beginTransaction();
        try {
            $delete = $this->db->prepare('DELETE FROM admin_role_permissions WHERE role_id = :role_id');
            $delete->execute(['role_id' => $roleId]);
            if ($permissionIds !== []) {
                $insert = $this->db->prepare('INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
                foreach ($permissionIds as $permissionId) {
                    $insert->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
                }
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function roleById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, slug, description, status, deleted_at FROM admin_roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function adminById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, status, deleted_at FROM admin_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function validRoleIds(array $ids): array
    {
        if ($ids === []) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare('SELECT id, slug FROM admin_roles WHERE id IN (' . $placeholders . ') AND status = "active" AND deleted_at IS NULL');
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validPermissionIds(array $ids): array
    {
        if ($ids === []) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare('SELECT id FROM admin_permissions WHERE id IN (' . $placeholders . ') AND status = "active" AND deleted_at IS NULL');
        $stmt->execute($ids);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function roleAssignedToActiveAdmin(int $roleId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM admin_user_roles ur
             INNER JOIN admin_users a ON a.id = ur.admin_user_id
             WHERE ur.role_id = :role_id
               AND a.status = "active"
               AND a.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchColumn() !== false;
    }

    public function softDeleteAdmin(int $adminId): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE admin_users
                 SET status = "inactive", deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $stmt->execute(['id' => $adminId]);
            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return false;
            }
            $sessions = $this->db->prepare(
                'UPDATE admin_sessions
                 SET revoked_at = CURRENT_TIMESTAMP
                 WHERE admin_user_id = :admin_id AND revoked_at IS NULL'
            );
            $sessions->execute(['admin_id' => $adminId]);
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function hasOtherActiveSuperAdmin(int $excludingAdminId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT a.id)
             FROM admin_users a
             INNER JOIN admin_user_roles ur ON ur.admin_user_id = a.id
             INNER JOIN admin_roles r ON r.id = ur.role_id
             WHERE a.status = "active" AND a.deleted_at IS NULL
               AND r.slug = "super-admin" AND r.status = "active" AND r.deleted_at IS NULL
               AND a.id <> :admin_id'
        );
        $stmt->execute(['admin_id' => $excludingAdminId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
