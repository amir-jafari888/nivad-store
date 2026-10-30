<?php

declare(strict_types=1);

final class AuditLogRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findAll(
        int $limit = 100
    ): array {
        $limit =
            max(
                1,
                min(500, $limit)
            );

        $stmt =
            $this->db->query(
                'SELECT
                    id,
                    admin_user_id,
                    action,
                    module,
                    entity_type,
                    entity_id,
                    description,
                    old_values,
                    new_values,
                    ip_address,
                    user_agent,
                    created_at
                 FROM audit_logs
                 ORDER BY
                    created_at DESC,
                    id DESC
                 LIMIT ' . $limit
            );

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function recentAdminActivity(int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));
        $stmt = $this->db->query(
            'SELECT
                l.id,
                l.action,
                l.module,
                l.entity_type,
                l.entity_id,
                l.description,
                l.created_at,
                COALESCE(
                    NULLIF(TRIM(CONCAT_WS(" ", a.first_name, a.last_name)), ""),
                    "مدیر"
                ) AS admin_name
             FROM audit_logs l
             LEFT JOIN admin_users a
                ON a.id = l.admin_user_id
             WHERE l.admin_user_id IS NOT NULL
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT ' . $limit
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs
                (admin_user_id, action, module, entity_type, entity_id, description, old_values, new_values, ip_address, user_agent)
             VALUES
                (:admin_user_id, :action, :module, :entity_type, :entity_id, :description, :old_values, :new_values, :ip_address, :user_agent)'
        );
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function count(): int
    {
        return (int)$this->db
            ->query(
                'SELECT COUNT(*)
                 FROM audit_logs'
            )
            ->fetchColumn();
    }
}
