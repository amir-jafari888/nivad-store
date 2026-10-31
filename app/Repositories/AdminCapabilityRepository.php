<?php

declare(strict_types=1);

final class AdminCapabilityRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function adminExists(int $adminId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM admin_users WHERE id = :id AND status IN ("active", "out_of_stock") AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $adminId]);
        return $stmt->fetchColumn() !== false;
    }

    public function customerExists(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchColumn() !== false;
    }

    public function notifications(int $adminId, int $limit = 50, bool $unreadOnly = false): array
    {
        $limit = max(1, min(100, $limit));
        $sql = 'SELECT id, category, severity, title, message, source_type, source_id, is_read, read_at, acknowledged_at, expires_at, created_at
                FROM admin_notifications
                WHERE (recipient_admin_user_id = :admin_id OR recipient_admin_user_id IS NULL)
                  AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)';
        if ($unreadOnly) {
            $sql .= ' AND is_read = 0';
        }
        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function unreadNotificationCount(int $adminId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM admin_notifications
             WHERE (recipient_admin_user_id = :admin_id OR recipient_admin_user_id IS NULL)
               AND is_read = 0
               AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)'
        );
        $stmt->execute(['admin_id' => $adminId]);
        return (int)$stmt->fetchColumn();
    }

    public function markNotificationRead(int $notificationId, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_notifications
             SET is_read = 1, read_at = COALESCE(read_at, CURRENT_TIMESTAMP)
             WHERE id = :id
               AND (recipient_admin_user_id = :admin_id OR recipient_admin_user_id IS NULL)'
        );
        $stmt->execute(['id' => $notificationId, 'admin_id' => $adminId]);
        $check = $this->db->prepare(
            'SELECT 1 FROM admin_notifications
             WHERE id = :id
               AND (recipient_admin_user_id = :admin_id OR recipient_admin_user_id IS NULL)
             LIMIT 1'
        );
        $check->execute(['id' => $notificationId, 'admin_id' => $adminId]);
        return $check->fetchColumn() !== false;
    }

    public function createNotification(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_notifications
                (recipient_admin_user_id, category, severity, title, message, source_type, source_id, dedupe_key, expires_at)
             VALUES
                (:recipient, :category, :severity, :title, :message, :source_type, :source_id, :dedupe_key, :expires_at)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $stmt->execute([
            'recipient' => $data['recipient_admin_user_id'],
            'category' => $data['category'],
            'severity' => $data['severity'],
            'title' => $data['title'],
            'message' => $data['message'],
            'source_type' => $data['source_type'],
            'source_id' => $data['source_id'],
            'dedupe_key' => $data['dedupe_key'],
            'expires_at' => $data['expires_at'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function notifyIncident(int $incidentId, string $title, string $description): void
    {
        if ($incidentId < 1) return;
        $adminIds = $this->db->query(
            'SELECT id FROM admin_users WHERE status = "active" AND deleted_at IS NULL'
        )->fetchAll(PDO::FETCH_COLUMN);
        $bucket = (int) floor(time() / 600);
        foreach ($adminIds as $adminId) {
            $adminId = (int) $adminId;
            try {
                $this->createNotification([
                    'recipient_admin_user_id' => $adminId,
                    'category' => 'errors',
                    'severity' => 'critical',
                    'title' => mb_substr($title, 0, 255),
                    'message' => $description !== '' ? $description : 'A critical system incident was recorded.',
                    'source_type' => 'incident',
                    'source_id' => $incidentId,
                    'dedupe_key' => hash('sha256', 'incident:' . $incidentId . ':' . $adminId . ':' . $bucket),
                    'expires_at' => null,
                ]);
            } catch (Throwable $failure) {
                Logger::error('Incident notification dispatch failed.', [
                    'incident_id' => $incidentId,
                    'admin_id' => $adminId,
                    'failure_type' => get_class($failure),
                ]);
            }
        }
        $touch = $this->db->prepare(
            'INSERT INTO admin_incident_metadata (incident_id, last_notification_at)
             VALUES (:id, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE last_notification_at = CURRENT_TIMESTAMP'
        );
        $touch->execute(['id' => $incidentId]);
    }
    public function notifyTicketCreated(int $ticketId, string $ticketNumber, string $subject): void
    {
        if ($ticketId < 1) return;
        $adminIds = $this->db->query(
            'SELECT id FROM admin_users WHERE status = "active" AND deleted_at IS NULL'
        )->fetchAll(PDO::FETCH_COLUMN);

        foreach ($adminIds as $adminId) {
            $adminId = (int)$adminId;
            $this->createNotification([
                'recipient_admin_user_id' => $adminId,
                'category' => 'customer_support',
                'severity' => 'info',
                'title' => 'New support ticket: ' . mb_substr($ticketNumber, 0, 50),
                'message' => mb_substr($subject, 0, 500),
                'source_type' => 'support_ticket',
                'source_id' => $ticketId,
                'dedupe_key' => hash('sha256', 'support-ticket:' . $ticketId . ':' . $adminId),
                'expires_at' => null,
            ]);
        }
    }

    public function incidents(string $status = '', int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT i.id, i.incident_key, i.severity, i.status, i.source, i.title,
                       i.description, i.occurrence_count, i.first_seen_at, i.last_seen_at,
                       i.resolved_by_admin_id, i.resolved_at, i.created_at, i.updated_at,
                       m.assigned_admin_id, m.last_notification_at,
                       a.first_name AS assigned_first_name, a.last_name AS assigned_last_name
                FROM admin_system_incidents i
                LEFT JOIN admin_incident_metadata m ON m.incident_id = i.id
                LEFT JOIN admin_users a ON a.id = m.assigned_admin_id';
        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE i.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY FIELD(i.severity, "critical", "warning", "info"), i.last_seen_at DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countIncidentsByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM admin_system_incidents WHERE status = :status'
        );
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }
    public function countTicketsByStatuses(array $statuses): int
    {
        if ($statuses === []) {
            return 0;
        }

        $allowed = ['open', 'pending_customer', 'pending_admin', 'resolved', 'closed'];
        $statuses = array_values(array_unique(array_filter(
            $statuses,
            static fn($status): bool => is_string($status) && in_array($status, $allowed, true)
        )));
        if ($statuses === []) {
            return 0;
        }

        $placeholders = [];
        $params = [];
        foreach ($statuses as $index => $status) {
            $key = 'status_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $status;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM admin_support_tickets
             WHERE status IN (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function recordIncident(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_system_incidents
                (incident_key, severity, status, source, title, description,
                 context_json, occurrence_count, first_seen_at, last_seen_at)
             VALUES
                (:incident_key, :severity, "open", :source, :title, :description,
                 :context_json, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE
                last_seen_at = CURRENT_TIMESTAMP,
                occurrence_count = occurrence_count + 1,
                severity = VALUES(severity),
                title = VALUES(title),
                description = VALUES(description),
                context_json = VALUES(context_json),
                resolved_by_admin_id = IF(status = "resolved", NULL, resolved_by_admin_id),
                resolved_at = IF(status = "resolved", NULL, resolved_at),
                status = IF(status = "resolved", "open", status),
                updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'incident_key' => $data['fingerprint'],
            'severity' => $data['severity'],
            'source' => $data['source'],
            'title' => $data['title'],
            'description' => $data['description'],
            'context_json' => $data['context_json'],
        ]);

        $find = $this->db->prepare(
            'SELECT id FROM admin_system_incidents WHERE incident_key = :incident_key LIMIT 1'
        );
        $find->execute(['incident_key' => $data['fingerprint']]);
        $incidentId = (int) $find->fetchColumn();
        if ($incidentId < 1) {
            throw new RuntimeException('Incident record could not be resolved after persistence.');
        }

        $context = is_string($data['context_json'] ?? null) ? json_decode($data['context_json'], true) : [];
        if (!is_array($context)) $context = [];

        $event = $this->db->prepare(
            'INSERT INTO admin_incident_events
                (incident_id, event_type, severity, source, request_id, http_status,
                 request_method, route_path, exception_type, file_basename, line_number, context_json)
             VALUES
                (:incident_id, :event_type, :severity, :source, :request_id, :http_status,
                 :request_method, :route_path, :exception_type, :file_basename, :line_number, :context_json)'
        );
        $event->execute([
            'incident_id' => $incidentId,
            'event_type' => mb_substr((string) ($context['event_type'] ?? 'server.error'), 0, 80),
            'severity' => $data['severity'],
            'source' => $data['source'],
            'request_id' => isset($context['request_id']) ? mb_substr((string) $context['request_id'], 0, 64) : null,
            'http_status' => isset($context['http_status']) ? max(100, min(599, (int) $context['http_status'])) : null,
            'request_method' => isset($context['request_method']) ? mb_substr((string) $context['request_method'], 0, 10) : null,
            'route_path' => isset($context['route_path']) ? mb_substr((string) $context['route_path'], 0, 255) : null,
            'exception_type' => isset($context['exception_type']) ? mb_substr((string) $context['exception_type'], 0, 160) : null,
            'file_basename' => isset($context['file']) ? basename((string) $context['file']) : null,
            'line_number' => isset($context['line']) ? max(0, (int) $context['line']) : null,
            'context_json' => $data['context_json'],
        ]);
        return $incidentId;
    }
    public function updateIncident(int $id, string $status, ?int $assignedAdminId, int $actorAdminId): bool
    {
        $resolved = $status === 'resolved';
        $stmt = $this->db->prepare(
            'UPDATE admin_system_incidents
             SET status = :status,
                 resolved_by_admin_id = CASE WHEN :is_resolved_by = 1 THEN :actor ELSE NULL END,
                 resolved_at = CASE WHEN :is_resolved_at = 1 THEN CURRENT_TIMESTAMP ELSE NULL END,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'is_resolved_by' => $resolved ? 1 : 0,
            'is_resolved_at' => $resolved ? 1 : 0,
            'actor' => $actorAdminId,
            'id' => $id,
        ]);
        $check = $this->db->prepare('SELECT 1 FROM admin_system_incidents WHERE id = :id LIMIT 1');
        $check->execute(['id' => $id]);
        if ($check->fetchColumn() === false) return false;

        $metadata = $this->db->prepare(
            'INSERT INTO admin_incident_metadata (incident_id, assigned_admin_id)
             VALUES (:incident_id, :assigned_admin_id)
             ON DUPLICATE KEY UPDATE assigned_admin_id = VALUES(assigned_admin_id)'
        );
        $metadata->execute(['incident_id' => $id, 'assigned_admin_id' => $assignedAdminId]);
        return true;
    }
    public function tickets(string $status = '', int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT t.id, t.ticket_number, t.user_id, t.category, t.priority, t.status, t.subject,
                       t.assigned_admin_id, t.last_message_at, t.resolved_at, t.closed_at, t.created_at, t.updated_at,
                       a.first_name AS assigned_first_name, a.last_name AS assigned_last_name
                FROM admin_support_tickets t
                LEFT JOIN admin_users a ON a.id = t.assigned_admin_id';
        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE t.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY FIELD(t.priority, "urgent", "high", "normal", "low"), t.updated_at DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ticket(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_support_tickets WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) {
            return null;
        }
        $messages = $this->db->prepare(
            'SELECT id, sender_type, sender_user_id, sender_admin_id, message, is_internal, created_at
             FROM admin_support_messages WHERE ticket_id = :ticket_id ORDER BY created_at ASC, id ASC'
        );
        $messages->execute(['ticket_id' => $id]);
        $ticket['messages'] = $messages->fetchAll(PDO::FETCH_ASSOC);
        return $ticket;
    }

    public function createTicket(array $data): int
    {
        $ticketNumber = 'NV-' . gmdate('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO admin_support_tickets (ticket_number, user_id, category, priority, status, subject, assigned_admin_id, last_message_at)
                 VALUES (:number, :user_id, :category, :priority, "open", :subject, :assigned, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'number' => $ticketNumber,
                'user_id' => $data['user_id'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'subject' => $data['subject'],
                'assigned' => $data['assigned_admin_id'],
            ]);
            $id = (int)$this->db->lastInsertId();
            $message = $this->db->prepare(
                'INSERT INTO admin_support_messages (ticket_id, sender_type, sender_admin_id, message, is_internal)
                 VALUES (:ticket_id, "admin", :admin_id, :message, 0)'
            );
            $message->execute([
                'ticket_id' => $id,
                'admin_id' => $data['actor_admin_id'],
                'message' => $data['message'],
            ]);
            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function addTicketMessage(int $ticketId, int $adminId, string $message, bool $internal): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO admin_support_messages (ticket_id, sender_type, sender_admin_id, message, is_internal)
                 VALUES (:ticket_id, "admin", :admin_id, :message, :internal)'
            );
            $stmt->execute([
                'ticket_id' => $ticketId,
                'admin_id' => $adminId,
                'message' => $message,
                'internal' => $internal ? 1 : 0,
            ]);
            $messageId = (int)$this->db->lastInsertId();
            $touch = $this->db->prepare(
                'UPDATE admin_support_tickets SET last_message_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $touch->execute(['id' => $ticketId]);
            $this->db->commit();
            return $messageId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function updateTicket(int $id, string $status, string $priority, ?int $assignedAdminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_support_tickets
             SET status = :status, priority = :priority, assigned_admin_id = :assigned,
                 resolved_at = CASE WHEN :resolved = 1 THEN COALESCE(resolved_at, CURRENT_TIMESTAMP) ELSE NULL END,
                 closed_at = CASE WHEN :closed = 1 THEN COALESCE(closed_at, CURRENT_TIMESTAMP) ELSE NULL END,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'priority' => $priority,
            'assigned' => $assignedAdminId,
            'resolved' => $status === 'resolved' ? 1 : 0,
            'closed' => $status === 'closed' ? 1 : 0,
            'id' => $id,
        ]);
        $check = $this->db->prepare('SELECT 1 FROM admin_support_tickets WHERE id = :id LIMIT 1');
        $check->execute(['id' => $id]);
        return $check->fetchColumn() !== false;
    }

    public function customerTickets(int $userId, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db->prepare(
            'SELECT id, ticket_number, category, priority, status, subject, last_message_at, created_at, updated_at
             FROM admin_support_tickets
             WHERE user_id = :user_id
             ORDER BY updated_at DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function customerTicket(int $ticketId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, ticket_number, category, priority, status, subject, last_message_at, created_at, updated_at
             FROM admin_support_tickets
             WHERE id = :id AND user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['id' => $ticketId, 'user_id' => $userId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) return null;

        $messages = $this->db->prepare(
            'SELECT id, sender_type, message, created_at
             FROM admin_support_messages
             WHERE ticket_id = :ticket_id AND is_internal = 0
             ORDER BY created_at ASC, id ASC'
        );
        $messages->execute(['ticket_id' => $ticketId]);
        $ticket['messages'] = $messages->fetchAll(PDO::FETCH_ASSOC);
        return $ticket;
    }

    public function createCustomerTicket(int $userId, array $data): int
    {
        $ticketNumber = 'NV-' . gmdate('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $this->db->beginTransaction();
        try {
            $ticket = $this->db->prepare(
                'INSERT INTO admin_support_tickets (ticket_number, user_id, category, priority, status, subject, last_message_at)
                 VALUES (:number, :user_id, :category, :priority, "open", :subject, CURRENT_TIMESTAMP)'
            );
            $ticket->execute([
                'number' => $ticketNumber,
                'user_id' => $userId,
                'category' => $data['category'],
                'priority' => $data['priority'],
                'subject' => $data['subject'],
            ]);
            $ticketId = (int)$this->db->lastInsertId();

            $message = $this->db->prepare(
                'INSERT INTO admin_support_messages (ticket_id, sender_type, sender_user_id, message, is_internal)
                 VALUES (:ticket_id, "customer", :user_id, :message, 0)'
            );
            $message->execute([
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'message' => $data['message'],
            ]);
            $this->db->commit();
            return $ticketId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function addCustomerTicketMessage(int $ticketId, int $userId, string $message): int
    {
        $this->db->beginTransaction();
        try {
            $check = $this->db->prepare(
                'SELECT status FROM admin_support_tickets
                 WHERE id = :ticket_id AND user_id = :user_id
                 LIMIT 1 FOR UPDATE'
            );
            $check->execute(['ticket_id' => $ticketId, 'user_id' => $userId]);
            $status = $check->fetchColumn();
            if ($status === false || $status === 'closed') {
                $this->db->rollBack();
                throw new InvalidArgumentException('این تیکت پیدا نشد یا بسته شده است.');
            }

            $insert = $this->db->prepare(
                'INSERT INTO admin_support_messages (ticket_id, sender_type, sender_user_id, message, is_internal)
                 VALUES (:ticket_id, "customer", :user_id, :message, 0)'
            );
            $insert->execute([
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'message' => $message,
            ]);
            $messageId = (int)$this->db->lastInsertId();

            $update = $this->db->prepare(
                'UPDATE admin_support_tickets
                 SET status = "pending_admin", last_message_at = CURRENT_TIMESTAMP,
                     resolved_at = NULL, closed_at = NULL, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :ticket_id AND user_id = :user_id'
            );
            $update->execute(['ticket_id' => $ticketId, 'user_id' => $userId]);
            $this->db->commit();
            return $messageId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function analytics(string $from, string $to, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $summaryStmt = $this->db->prepare(<<<'SQL'
            SELECT
                COUNT(*) AS total_events,
                COUNT(DISTINCT CASE WHEN event_key = 'product_view'
                    THEN JSON_UNQUOTE(JSON_EXTRACT(dimensions_json, '$.visitor_hash')) END) AS unique_viewers,
                SUM(event_key = 'search_impression') AS search_impressions,
                SUM(event_key = 'search_click') AS search_clicks,
                SUM(event_key = 'checkout_started') AS checkout_starts,
                SUM(event_key = 'checkout_address_selected') AS checkout_address_steps,
                SUM(event_key = 'checkout_shipping_selected') AS checkout_shipping_steps,
                SUM(event_key = 'checkout_order_created') AS checkout_orders_created,
                COUNT(DISTINCT CASE WHEN event_key = 'checkout_started'
                    THEN JSON_UNQUOTE(JSON_EXTRACT(dimensions_json, '$.session_hash')) END) AS checkout_sessions,
                COUNT(DISTINCT CASE WHEN event_key = 'purchase_completed'
                    THEN JSON_UNQUOTE(JSON_EXTRACT(dimensions_json, '$.session_hash')) END) AS purchase_sessions,
                SUM(event_key = 'related_product_impression') AS related_impressions,
                SUM(event_key = 'related_product_click') AS related_clicks,
                SUM(event_key = 'discovery_impression') AS discovery_impressions,
                SUM(event_key = 'discovery_option_impression') AS discovery_option_impressions,
                SUM(event_key = 'discovery_click') AS discovery_clicks,
                SUM(event_key = 'discovery_select') AS discovery_selections,
                SUM(event_key = 'comparison_view') AS comparison_views,
                SUM(event_key IN ('comparison_add', 'comparison_remove')) AS comparison_selections
            FROM admin_analytics_events
            WHERE occurred_at >= :summary_from AND occurred_at < :summary_to
            SQL);
        $summaryStmt->execute(['summary_from' => $from, 'summary_to' => $to]);
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $summary['search_click_rate'] = (int)($summary['search_impressions'] ?? 0) > 0
            ? round(((int)$summary['search_clicks'] / (int)$summary['search_impressions']) * 100, 2)
            : null;
        $summary['related_click_rate'] = (int)($summary['related_impressions'] ?? 0) > 0
            ? round(((int)$summary['related_clicks'] / (int)$summary['related_impressions']) * 100, 2)
            : null;
        $summary['discovery_selection_rate'] = (int)($summary['discovery_option_impressions'] ?? 0) > 0
            ? round(((int)$summary['discovery_selections'] / (int)$summary['discovery_option_impressions']) * 100, 2)
            : null;
        $summary['checkout_conversion_rate'] = (int)($summary['checkout_sessions'] ?? 0) > 0
            ? round(((int)$summary['purchase_sessions'] / (int)$summary['checkout_sessions']) * 100, 2)
            : null;

        $sql = '
            SELECT e.event_key AS event_name,
                   e.entity_type,
                   e.entity_id,
                   CASE
                     WHEN e.entity_type IN ("product", "discovery") AND MAX(p.name) IS NOT NULL THEN MAX(p.name)
                     WHEN e.entity_type = "product" THEN "محصول حذف‌شده/نامشخص"
                     WHEN e.entity_type IS NULL THEN "رویداد عمومی"
                     ELSE CONCAT(e.entity_type, " · ", COALESCE(e.entity_id, "بدون شناسه"))
                   END AS product_name,
                   COUNT(*) AS event_count,
                   COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(e.dimensions_json, "$.session_hash"))) AS sessions,
                   COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(e.dimensions_json, "$.visitor_hash"))) AS visitors
            FROM admin_analytics_events e
            LEFT JOIN products p
              ON (
                   e.entity_type IN ("product", "discovery")
                   OR (e.entity_type = "comparison" AND e.event_key IN ("comparison_add", "comparison_remove"))
                 )
             AND p.id = CAST(e.entity_id AS UNSIGNED)
            WHERE e.occurred_at >= :date_from AND e.occurred_at < :date_to
            GROUP BY e.event_key, e.entity_type, e.entity_id
            ORDER BY event_count DESC, e.event_key ASC
            LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['date_from' => $from, 'date_to' => $to]);

        return [
            'summary' => $summary,
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function productExists(int $productId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM products WHERE id = :id AND status = "active" AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchColumn() !== false;
    }

    public function variantBelongsToProduct(int $variantId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM product_variants
             WHERE id = :variant_id AND product_id = :product_id AND status IN ("active", "out_of_stock") AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['variant_id' => $variantId, 'product_id' => $productId]);
        return $stmt->fetchColumn() !== false;
    }

    public function recordProductEvent(array $data): int
    {
        return ProductAnalyticsRecorder::record(
            $this->db,
            (string)$data['event_key'],
            isset($data['entity_type']) ? (string)$data['entity_type'] : null,
            $data['entity_id'] ?? null,
            isset($data['numeric_value']) ? (float)$data['numeric_value'] : null,
            is_array($data['dimensions'] ?? null) ? $data['dimensions'] : []
        );
    }

    public function preferences(int $adminId): array
    {
        $stmt = $this->db->prepare(
            'SELECT preference_key, preference_value, updated_at
             FROM admin_preferences WHERE admin_user_id = :admin_id ORDER BY preference_key'
        );
        $stmt->execute(['admin_id' => $adminId]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $decoded = json_decode((string)$row['preference_value'], true);
            $result[$row['preference_key']] = [
                'value' => json_last_error() === JSON_ERROR_NONE ? $decoded : null,
                'updated_at' => $row['updated_at'],
            ];
        }
        return $result;
    }

    public function savePreference(int $adminId, string $key, string $jsonValue): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_preferences (admin_user_id, preference_key, preference_value)
             VALUES (:admin_id, :preference_key, :preference_value)
             ON DUPLICATE KEY UPDATE preference_value = VALUES(preference_value), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'admin_id' => $adminId,
            'preference_key' => $key,
            'preference_value' => $jsonValue,
        ]);
    }

    public function savedViews(int $adminId, string $module): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, module, name, filters_json, sort_json, is_default, created_at, updated_at
             FROM admin_saved_views WHERE admin_user_id = :admin_id AND module = :module
             ORDER BY is_default DESC, name ASC'
        );
        $stmt->execute(['admin_id' => $adminId, 'module' => $module]);
        return array_map(static function (array $row): array {
            $row['filters'] = json_decode((string)$row['filters_json'], true);
            $row['sort'] = $row['sort_json'] !== null ? json_decode((string)$row['sort_json'], true) : null;
            unset($row['filters_json'], $row['sort_json']);
            return $row;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function saveView(int $adminId, string $module, string $name, string $filtersJson, ?string $sortJson, bool $isDefault): int
    {
        $this->db->beginTransaction();
        try {
            if ($isDefault) {
                $clear = $this->db->prepare(
                    'UPDATE admin_saved_views SET is_default = 0 WHERE admin_user_id = :admin_id AND module = :module'
                );
                $clear->execute(['admin_id' => $adminId, 'module' => $module]);
            }
            $stmt = $this->db->prepare(
                'INSERT INTO admin_saved_views (admin_user_id, module, name, filters_json, sort_json, is_default)
                 VALUES (:admin_id, :module, :name, :filters_json, :sort_json, :is_default)
                 ON DUPLICATE KEY UPDATE filters_json = VALUES(filters_json), sort_json = VALUES(sort_json),
                                         is_default = VALUES(is_default), updated_at = CURRENT_TIMESTAMP'
            );
            $stmt->execute([
                'admin_id' => $adminId,
                'module' => $module,
                'name' => $name,
                'filters_json' => $filtersJson,
                'sort_json' => $sortJson,
                'is_default' => $isDefault ? 1 : 0,
            ]);
            $find = $this->db->prepare(
                'SELECT id FROM admin_saved_views WHERE admin_user_id = :admin_id AND module = :module AND name = :name LIMIT 1'
            );
            $find->execute(['admin_id' => $adminId, 'module' => $module, 'name' => $name]);
            $id = (int)$find->fetchColumn();
            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteView(int $id, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM admin_saved_views WHERE id = :id AND admin_user_id = :admin_id'
        );
        $stmt->execute(['id' => $id, 'admin_id' => $adminId]);
        return $stmt->rowCount() > 0;
    }

    public function globalSearch(string $term): array
    {
        $pattern = '%' . $term . '%';
        $results = [];

        $products = $this->db->prepare(
            'SELECT id, name AS label, sku AS detail
             FROM products
             WHERE deleted_at IS NULL AND (name LIKE :name OR sku LIKE :sku)
             ORDER BY name ASC LIMIT 6'
        );
        $products->execute(['name' => $pattern, 'sku' => $pattern]);
        foreach ($products->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = ['type' => 'product', 'id' => (int)$row['id'], 'label' => $row['label'], 'detail' => $row['detail']];
        }

        $orders = $this->db->prepare(
            'SELECT id, order_number AS label, CONCAT(recipient_name, " · ", recipient_phone) AS detail
             FROM orders
             WHERE order_number LIKE :number OR recipient_name LIKE :recipient OR recipient_phone LIKE :phone
             ORDER BY id DESC LIMIT 6'
        );
        $orders->execute(['number' => $pattern, 'recipient' => $pattern, 'phone' => $pattern]);
        foreach ($orders->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = ['type' => 'order', 'id' => (int)$row['id'], 'label' => $row['label'], 'detail' => $row['detail']];
        }

        $users = $this->db->prepare(
            'SELECT id, CONCAT(COALESCE(first_name, ""), " ", COALESCE(last_name, "")) AS label,
                    CONCAT(COALESCE(email, ""), " · ", COALESCE(phone, "")) AS detail
             FROM users
             WHERE deleted_at IS NULL
               AND (first_name LIKE :first_name OR last_name LIKE :last_name OR email LIKE :email OR phone LIKE :phone)
             ORDER BY id DESC LIMIT 6'
        );
        $users->execute(['first_name' => $pattern, 'last_name' => $pattern, 'email' => $pattern, 'phone' => $pattern]);
        foreach ($users->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = ['type' => 'customer', 'id' => (int)$row['id'], 'label' => trim($row['label']) ?: ('Customer #' . $row['id']), 'detail' => $row['detail']];
        }

        $tickets = $this->db->prepare(
            'SELECT id, ticket_number AS label, subject AS detail
             FROM admin_support_tickets
             WHERE ticket_number LIKE :number OR subject LIKE :subject
             ORDER BY updated_at DESC LIMIT 6'
        );
        $tickets->execute(['number' => $pattern, 'subject' => $pattern]);
        foreach ($tickets->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = ['type' => 'ticket', 'id' => (int)$row['id'], 'label' => $row['label'], 'detail' => $row['detail']];
        }

        return array_slice($results, 0, 20);
    }

    public function revisions(string $entityType, int $entityId, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db->prepare(
            'SELECT
                id,
                entity_type,
                entity_id,
                version_number AS revision_number,
                created_by_admin_id AS actor_admin_id,
                status,
                scheduled_at,
                published_at,
                created_at
             FROM admin_content_revisions
             WHERE entity_type = :entity_type
               AND entity_id = :entity_id
             ORDER BY version_number DESC
             LIMIT ' . $limit
        );
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id' => (string)$entityId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createRevision(string $entityType, int $entityId, int $actorAdminId, array $snapshot, string $summary): int
    {
        $snapshotJson = json_encode(
            $snapshot,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        $this->db->beginTransaction();
        try {
            $next = $this->db->prepare(
                'SELECT COALESCE(MAX(version_number), 0) + 1
                 FROM admin_content_revisions
                 WHERE entity_type = :entity_type
                   AND entity_id = :entity_id
                 FOR UPDATE'
            );
            $next->execute([
                'entity_type' => $entityType,
                'entity_id' => (string)$entityId,
            ]);
            $number = max(1, (int)$next->fetchColumn());

            $stmt = $this->db->prepare(
                'INSERT INTO admin_content_revisions (
                    entity_type,
                    entity_id,
                    version_number,
                    status,
                    snapshot_json,
                    scheduled_at,
                    published_at,
                    created_by_admin_id
                 ) VALUES (
                    :entity_type,
                    :entity_id,
                    :version_number,
                    :status,
                    :snapshot_json,
                    NULL,
                    NULL,
                    :created_by_admin_id
                 )'
            );
            $stmt->execute([
                'entity_type' => $entityType,
                'entity_id' => (string)$entityId,
                'version_number' => $number,
                'status' => 'draft',
                'snapshot_json' => $snapshotJson,
                'created_by_admin_id' => $actorAdminId,
            ]);

            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
