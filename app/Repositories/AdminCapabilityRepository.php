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
        $stmt = $this->db->prepare('SELECT 1 FROM admin_users WHERE id = :id AND status = "active" AND deleted_at IS NULL LIMIT 1');
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
                (:recipient, :category, :severity, :title, :message, :source_type, :source_id, :dedupe_key, :expires_at)'
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

    public function incidents(string $status = '', int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT i.*, a.first_name AS assigned_first_name, a.last_name AS assigned_last_name
                FROM admin_incidents i
                LEFT JOIN admin_users a ON a.id = i.assigned_admin_id';
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

    public function recordIncident(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_incidents
                (fingerprint, source, severity, title, description, context_json)
             VALUES
                (:fingerprint, :source, :severity, :title, :description, :context_json)
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
            'fingerprint' => $data['fingerprint'],
            'source' => $data['source'],
            'severity' => $data['severity'],
            'title' => $data['title'],
            'description' => $data['description'],
            'context_json' => $data['context_json'],
        ]);
        $find = $this->db->prepare('SELECT id FROM admin_incidents WHERE fingerprint = :fingerprint LIMIT 1');
        $find->execute(['fingerprint' => $data['fingerprint']]);
        return (int)$find->fetchColumn();
    }

    public function updateIncident(int $id, string $status, ?int $assignedAdminId, int $actorAdminId): bool
    {
        $resolved = $status === 'resolved';
        $stmt = $this->db->prepare(
            'UPDATE admin_incidents
             SET status = :status,
                 assigned_admin_id = :assigned,
                 resolved_by_admin_id = CASE WHEN :is_resolved_by = 1 THEN :actor ELSE NULL END,
                 resolved_at = CASE WHEN :is_resolved_at = 1 THEN CURRENT_TIMESTAMP ELSE NULL END,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'assigned' => $assignedAdminId,
            'is_resolved_by' => $resolved ? 1 : 0,
            'is_resolved_at' => $resolved ? 1 : 0,
            'actor' => $actorAdminId,
            'id' => $id,
        ]);
        $check = $this->db->prepare('SELECT 1 FROM admin_incidents WHERE id = :id LIMIT 1');
        $check->execute(['id' => $id]);
        return $check->fetchColumn() !== false;
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

    public function analytics(string $from, string $to, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->db->prepare(
            'SELECT p.id AS product_id, p.name AS product_name, e.event_name, COUNT(*) AS event_count,
                    COUNT(DISTINCT e.session_hash) AS sessions, COUNT(DISTINCT e.visitor_hash) AS visitors
             FROM product_analytics_events e
             LEFT JOIN products p ON p.id = e.product_id
             WHERE e.occurred_at >= :date_from AND e.occurred_at < :date_to
             GROUP BY p.id, p.name, e.event_name
             ORDER BY event_count DESC LIMIT ' . $limit
        );
        $stmt->execute(['date_from' => $from, 'date_to' => $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recordProductEvent(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO product_analytics_events
                (event_name, product_id, variant_id, user_id, visitor_hash, session_hash, search_term_hash, source, context_json)
             VALUES
                (:event_name, :product_id, :variant_id, :user_id, :visitor_hash, :session_hash, :search_term_hash, :source, :context_json)'
        );
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
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
            $results[] = ['type' => 'product', 'id' => (int)$row['id'], 'label' => $row['label'], 'detail' => $row['detail'], 'href' => url('/admin/products')];
        }

        $orders = $this->db->prepare(
            'SELECT id, order_number AS label, CONCAT(recipient_name, " · ", recipient_phone) AS detail
             FROM orders
             WHERE order_number LIKE :number OR recipient_name LIKE :recipient OR recipient_phone LIKE :phone
             ORDER BY id DESC LIMIT 6'
        );
        $orders->execute(['number' => $pattern, 'recipient' => $pattern, 'phone' => $pattern]);
        foreach ($orders->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = ['type' => 'order', 'id' => (int)$row['id'], 'label' => $row['label'], 'detail' => $row['detail'], 'href' => url('/admin/orders')];
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
            $results[] = ['type' => 'customer', 'id' => (int)$row['id'], 'label' => trim($row['label']) ?: ('Customer #' . $row['id']), 'detail' => $row['detail'], 'href' => url('/admin/users')];
        }

        $tickets = $this->db->prepare(
            'SELECT id, ticket_number AS label, subject AS detail
             FROM admin_support_tickets
             WHERE ticket_number LIKE :number OR subject LIKE :subject
             ORDER BY updated_at DESC LIMIT 6'
        );
        $tickets->execute(['number' => $pattern, 'subject' => $pattern]);
        foreach ($tickets->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = ['type' => 'ticket', 'id' => (int)$row['id'], 'label' => $row['label'], 'detail' => $row['detail'], 'href' => url('/admin/capabilities/index.php#admin-tickets-title')];
        }

        return array_slice($results, 0, 20);
    }

    public function revisions(string $entityType, int $entityId, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db->prepare(
            'SELECT id, entity_type, entity_id, revision_number, actor_admin_id, change_summary, created_at
             FROM admin_content_revisions WHERE entity_type = :entity_type AND entity_id = :entity_id
             ORDER BY revision_number DESC LIMIT ' . $limit
        );
        $stmt->execute(['entity_type' => $entityType, 'entity_id' => $entityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createRevision(string $entityType, int $entityId, int $actorAdminId, array $snapshot, string $summary): int
    {
        $this->db->beginTransaction();
        try {
            $next = $this->db->prepare(
                'SELECT COALESCE(MAX(revision_number), 0) + 1
                 FROM admin_content_revisions WHERE entity_type = :entity_type AND entity_id = :entity_id FOR UPDATE'
            );
            $next->execute(['entity_type' => $entityType, 'entity_id' => $entityId]);
            $number = (int)$next->fetchColumn();
            $stmt = $this->db->prepare(
                'INSERT INTO admin_content_revisions
                    (entity_type, entity_id, revision_number, actor_admin_id, snapshot_json, change_summary)
                 VALUES (:entity_type, :entity_id, :revision_number, :actor_admin_id, :snapshot_json, :change_summary)'
            );
            $stmt->execute([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'revision_number' => $number,
                'actor_admin_id' => $actorAdminId,
                'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'change_summary' => $summary !== '' ? $summary : null,
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
