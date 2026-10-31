<?php

declare(strict_types=1);

final class AdminSupportRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function tickets(array $filters = []): array
    {
        $where = [];
        $params = [];
        $map = [
            'status' => 't.status',
            'priority' => 't.priority',
            'assigned_admin_id' => 't.assigned_admin_id',
            'user_id' => 't.user_id',
        ];
        foreach ($map as $key => $column) {
            if (($filters[$key] ?? '') !== '') {
                $where[] = $column . ' = :' . $key;
                $params[$key] = $filters[$key];
            }
        }
        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 't.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 't.created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)';
            $params['date_to'] = $filters['date_to'] . ' 00:00:00';
        }
        if (($filters['search'] ?? '') !== '') {
            $where[] = '(t.ticket_number LIKE :search_number OR t.subject LIKE :search_subject OR CAST(t.id AS CHAR) = :search_id)';
            $needle = '%' . $filters['search'] . '%';
            $params['search_number'] = $needle;
            $params['search_subject'] = $needle;
            $params['search_id'] = $filters['search'];
        }

        $sql = 'SELECT t.id, t.ticket_number, t.user_id, t.category, t.priority, t.status, t.subject,
                       t.assigned_admin_id, t.last_message_at, t.resolved_at, t.closed_at, t.created_at, t.updated_at,
                       a.first_name AS assigned_first_name, a.last_name AS assigned_last_name,
                       (SELECT MIN(m.created_at) FROM admin_support_messages m
                        WHERE m.ticket_id = t.id AND m.sender_type = "admin" AND m.is_internal = 0
                          AND t.user_id IS NOT NULL AND m.created_at >= t.created_at) AS first_response_at
                FROM admin_support_tickets t
                LEFT JOIN admin_users a ON a.id = t.assigned_admin_id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $limit = max(1, min(200, (int)($filters['limit'] ?? 100)));
        $sql .= ' ORDER BY FIELD(t.priority, "urgent", "high", "normal", "low"), t.updated_at DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ticket(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, a.first_name AS assigned_first_name, a.last_name AS assigned_last_name,
                    (SELECT MIN(m.created_at) FROM admin_support_messages m
                     WHERE m.ticket_id = t.id AND m.sender_type = "admin" AND m.is_internal = 0
                       AND t.user_id IS NOT NULL AND m.created_at >= t.created_at) AS first_response_at
             FROM admin_support_tickets t
             LEFT JOIN admin_users a ON a.id = t.assigned_admin_id
             WHERE t.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) return null;

        $messages = $this->db->prepare(
            'SELECT id, sender_type, sender_user_id, sender_admin_id, message, is_internal, created_at
             FROM admin_support_messages WHERE ticket_id = :ticket_id ORDER BY created_at ASC, id ASC'
        );
        $messages->execute(['ticket_id' => $id]);
        $ticket['messages'] = $messages->fetchAll(PDO::FETCH_ASSOC);
        $ticket['status_history'] = $this->statusHistory($id);
        return $ticket;
    }

    public function statusHistory(int $ticketId): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.id, h.from_status, h.to_status, h.actor_type, h.actor_admin_id,
                    h.note, h.changed_at, a.first_name, a.last_name
             FROM admin_support_status_history h
             LEFT JOIN admin_users a ON a.id = h.actor_admin_id
             WHERE h.ticket_id = :ticket_id
             ORDER BY h.changed_at ASC, h.id ASC'
        );
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function slaPolicies(): array
    {
        return $this->db->query(
            'SELECT priority, first_response_minutes, resolution_minutes, is_active, updated_at
             FROM admin_support_sla_policies
             ORDER BY FIELD(priority, "urgent", "high", "normal", "low")'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveSlaPolicy(string $priority, int $firstResponseMinutes, int $resolutionMinutes, int $adminId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_support_sla_policies
                (priority, first_response_minutes, resolution_minutes, is_active, updated_by_admin_id)
             VALUES (:priority, :first_response, :resolution, 1, :admin_id)
             ON DUPLICATE KEY UPDATE first_response_minutes = VALUES(first_response_minutes),
                 resolution_minutes = VALUES(resolution_minutes), is_active = 1,
                 updated_by_admin_id = VALUES(updated_by_admin_id), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'priority' => $priority,
            'first_response' => $firstResponseMinutes,
            'resolution' => $resolutionMinutes,
            'admin_id' => $adminId,
        ]);
    }

    public function metrics(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total_tickets,
                    SUM(t.status IN ("open", "pending_admin", "pending_customer")) AS active_tickets,
                    SUM(t.status = "resolved" OR t.status = "closed") AS completed_tickets,
                    AVG(CASE WHEN t.user_id IS NOT NULL AND first_reply.first_response_at IS NOT NULL
                        THEN TIMESTAMPDIFF(SECOND, t.created_at, first_reply.first_response_at) / 60 END) AS avg_first_response_minutes,
                    SUM(CASE WHEN t.user_id IS NOT NULL AND first_reply.first_response_at IS NULL
                        AND t.status IN ("open", "pending_admin") THEN 1 ELSE 0 END) AS awaiting_first_response
             FROM admin_support_tickets t
             LEFT JOIN (
                SELECT ticket_id, MIN(created_at) AS first_response_at
                FROM admin_support_messages
                WHERE sender_type = "admin" AND is_internal = 0
                GROUP BY ticket_id
             ) first_reply ON first_reply.ticket_id = t.id
             WHERE t.created_at >= :date_from AND t.created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)'
        );
        $stmt->execute(['date_from' => $from . ' 00:00:00', 'date_to' => $to . ' 00:00:00']);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $policyRows = $this->slaPolicies();
        $policies = [];
        foreach ($policyRows as $policy) $policies[$policy['priority']] = $policy;
        $sla = $this->db->prepare(
            'SELECT t.priority, COUNT(*) AS eligible,
                    SUM(CASE WHEN first_reply.first_response_at IS NOT NULL
                        AND TIMESTAMPDIFF(MINUTE, t.created_at, first_reply.first_response_at) <= p.first_response_minutes THEN 1
                        WHEN first_reply.first_response_at IS NULL AND t.status NOT IN ("resolved", "closed")
                        AND TIMESTAMPDIFF(MINUTE, t.created_at, CURRENT_TIMESTAMP) <= p.first_response_minutes THEN 1
                        ELSE 0 END) AS response_within_sla,
                    SUM(CASE WHEN first_reply.first_response_at IS NOT NULL
                        AND TIMESTAMPDIFF(MINUTE, t.created_at, first_reply.first_response_at) > p.first_response_minutes THEN 1
                        WHEN first_reply.first_response_at IS NULL AND (t.status IN ("resolved", "closed")
                        OR TIMESTAMPDIFF(MINUTE, t.created_at, CURRENT_TIMESTAMP) > p.first_response_minutes) THEN 1
                        ELSE 0 END) AS response_breached,
                    SUM(CASE WHEN COALESCE(t.resolved_at, t.closed_at) IS NOT NULL
                        AND TIMESTAMPDIFF(MINUTE, t.created_at, COALESCE(t.resolved_at, t.closed_at)) <= p.resolution_minutes THEN 1
                        WHEN COALESCE(t.resolved_at, t.closed_at) IS NULL
                        AND TIMESTAMPDIFF(MINUTE, t.created_at, CURRENT_TIMESTAMP) <= p.resolution_minutes THEN 1
                        ELSE 0 END) AS resolution_within_sla,
                    SUM(CASE WHEN COALESCE(t.resolved_at, t.closed_at) IS NOT NULL
                        AND TIMESTAMPDIFF(MINUTE, t.created_at, COALESCE(t.resolved_at, t.closed_at)) > p.resolution_minutes THEN 1
                        WHEN COALESCE(t.resolved_at, t.closed_at) IS NULL
                        AND TIMESTAMPDIFF(MINUTE, t.created_at, CURRENT_TIMESTAMP) > p.resolution_minutes THEN 1
                        ELSE 0 END) AS resolution_breached
             FROM admin_support_tickets t
             INNER JOIN admin_support_sla_policies p ON p.priority = t.priority AND p.is_active = 1
             LEFT JOIN (
                SELECT ticket_id, MIN(created_at) AS first_response_at
                FROM admin_support_messages
                WHERE sender_type = "admin" AND is_internal = 0
                GROUP BY ticket_id
             ) first_reply ON first_reply.ticket_id = t.id
             WHERE t.user_id IS NOT NULL AND t.created_at >= :date_from
               AND t.created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)
             GROUP BY t.priority'
        );
        $sla->execute(['date_from' => $from . ' 00:00:00', 'date_to' => $to . ' 00:00:00']);
        $slaRows = $sla->fetchAll(PDO::FETCH_ASSOC);

        return [
            'summary' => $summary,
            'sla' => $slaRows,
            'policies_configured' => count(array_filter($policies, static fn(array $p): bool => (int)$p['is_active'] === 1)),
        ];
    }

    public function reports(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT status, priority, COUNT(*) AS ticket_count,
                    AVG(CASE WHEN first_reply.first_response_at IS NOT NULL
                        THEN TIMESTAMPDIFF(SECOND, t.created_at, first_reply.first_response_at) / 60 END) AS avg_first_response_minutes
             FROM admin_support_tickets t
             LEFT JOIN (
                SELECT ticket_id, MIN(created_at) AS first_response_at
                FROM admin_support_messages
                WHERE sender_type = "admin" AND is_internal = 0
                GROUP BY ticket_id
             ) first_reply ON first_reply.ticket_id = t.id
             WHERE t.created_at >= :date_from AND t.created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)
             GROUP BY status, priority
             ORDER BY FIELD(priority, "urgent", "high", "normal", "low"), status'
        );
        $stmt->execute(['date_from' => $from . ' 00:00:00', 'date_to' => $to . ' 00:00:00']);
        $byStatusPriority = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $agents = $this->db->prepare(
            'SELECT t.assigned_admin_id,
                    CONCAT(COALESCE(a.first_name, ""), " ", COALESCE(a.last_name, "")) AS assigned_admin_name,
                    COUNT(*) AS ticket_count,
                    SUM(t.status IN ("resolved", "closed")) AS completed_count,
                    AVG(CASE WHEN t.user_id IS NOT NULL AND first_reply.first_response_at IS NOT NULL
                        THEN TIMESTAMPDIFF(SECOND, t.created_at, first_reply.first_response_at) / 60 END) AS avg_first_response_minutes,
                    AVG(CASE WHEN COALESCE(t.resolved_at, t.closed_at) IS NOT NULL
                        THEN TIMESTAMPDIFF(SECOND, t.created_at, COALESCE(t.resolved_at, t.closed_at)) / 60 END) AS avg_resolution_minutes
             FROM admin_support_tickets t
             LEFT JOIN admin_users a ON a.id = t.assigned_admin_id
             LEFT JOIN (
                SELECT ticket_id, MIN(created_at) AS first_response_at
                FROM admin_support_messages
                WHERE sender_type = "admin" AND is_internal = 0
                GROUP BY ticket_id
             ) first_reply ON first_reply.ticket_id = t.id
             WHERE t.created_at >= :date_from AND t.created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)
             GROUP BY t.assigned_admin_id, a.first_name, a.last_name
             ORDER BY ticket_count DESC'
        );
        $agents->execute(['date_from' => $from . ' 00:00:00', 'date_to' => $to . ' 00:00:00']);

        return [
            'by_status_priority' => $byStatusPriority,
            'by_assignee' => $agents->fetchAll(PDO::FETCH_ASSOC),
        ];
    }
}
