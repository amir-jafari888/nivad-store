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

    public function create(array $event): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs (
                admin_user_id,
                action,
                module,
                entity_type,
                entity_id,
                description,
                old_values,
                new_values,
                ip_address,
                user_agent
            ) VALUES (
                :admin_user_id,
                :action,
                :module,
                :entity_type,
                :entity_id,
                :description,
                :old_values,
                :new_values,
                :ip_address,
                :user_agent
            )'
        );

        $stmt->execute([
            'admin_user_id' => $event['admin_user_id'] ?? null,
            'action' => (string) ($event['action'] ?? ''),
            'module' => (string) ($event['module'] ?? ''),
            'entity_type' => $event['entity_type'] ?? null,
            'entity_id' => isset($event['entity_id'])
                ? (string) $event['entity_id']
                : null,
            'description' => (string) ($event['description'] ?? ''),
            'old_values' => $event['old_values'] ?? null,
            'new_values' => $event['new_values'] ?? null,
            'ip_address' => $event['ip_address'] ?? null,
            'user_agent' => $event['user_agent'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
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
