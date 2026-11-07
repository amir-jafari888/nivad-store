<?php

declare(strict_types=1);

final class AdminRepository
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
        int $adminUserId
    ): ?array {
        if ($adminUserId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                username,
                email,
                password_hash,
                first_name,
                last_name,
                status,
                last_login_at,
                created_at,
                updated_at,
                deleted_at
             FROM admin_users
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $adminUserId,
        ]);

        $admin = $stmt->fetch();

        return $admin !== false
            ? $admin
            : null;
    }

    public function findByIdentifier(
        string $identifier
    ): ?array {
        $identifier =
            trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                username,
                email,
                password_hash,
                first_name,
                last_name,
                status,
                last_login_at,
                created_at,
                updated_at,
                deleted_at
             FROM admin_users
             WHERE deleted_at IS NULL
               AND (
                    username = :username
                    OR email = :email
               )
             LIMIT 1'
        );

        $stmt->execute([
            'username' => $identifier,
            'email' =>
            mb_strtolower(
                $identifier
            ),
        ]);

        $admin = $stmt->fetch();

        return $admin !== false
            ? $admin
            : null;
    }

    public function isActive(
        array $admin
    ): bool {
        return ($admin['status'] ?? null)
            === 'active'
            &&
            empty($admin['deleted_at']);
    }

    public function updateLastLogin(
        int $adminUserId
    ): bool {
        if ($adminUserId < 1) {
            throw new InvalidArgumentException(
                'Invalid admin user ID.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE admin_users
             SET last_login_at =
                 CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $adminUserId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function existsActive(
        int $adminUserId
    ): bool {
        if ($adminUserId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
         FROM admin_users
         WHERE id = :id
           AND status = :status
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $adminUserId,
            'status' => 'active',
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(
        int $adminUserId
    ): ?array {
        if ($adminUserId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
            id,
            username,
            email,
            password_hash,
            first_name,
            last_name,
            status,
            last_login_at,
            created_at,
            updated_at,
            deleted_at
         FROM admin_users
         WHERE id = :id
           AND status = :status
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $adminUserId,
            'status' => 'active',
        ]);

        $admin = $stmt->fetch();

        return $admin !== false
            ? $admin
            : null;
    }

    /**
     * Returns active role names for the authenticated administrator.
     */
    public function roleNamesForAdmin(int $adminUserId): array
    {
        if ($adminUserId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT r.name
             FROM admin_user_roles ur
             INNER JOIN admin_roles r ON r.id = ur.role_id
             WHERE ur.admin_user_id = :admin_id
               AND r.status = "active"
               AND r.deleted_at IS NULL
             ORDER BY r.name'
        );
        $stmt->execute(['admin_id' => $adminUserId]);
        return array_values(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
    }

    /**
     * Updates only the authenticated administrator's own profile fields.
     * Role, status and account lifecycle fields are intentionally excluded.
     */
    public function updateOwnProfile(int $adminUserId, array $data): bool
    {
        if ($adminUserId < 1) {
            return false;
        }

        $sql = 'UPDATE admin_users
                SET username = :username,
                    email = :email,
                    first_name = :first_name,
                    last_name = :last_name,
                    updated_at = CURRENT_TIMESTAMP';
        if (isset($data['password_hash'])) {
            $sql .= ', password_hash = :password_hash';
        }
        $sql .= ' WHERE id = :id AND status = "active" AND deleted_at IS NULL';

        $params = [
            'username' => $data['username'],
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'id' => $adminUserId,
        ];
        if (isset($data['password_hash'])) {
            $params['password_hash'] = $data['password_hash'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() === 1 || $this->findActiveById($adminUserId) !== null;
    }

}
