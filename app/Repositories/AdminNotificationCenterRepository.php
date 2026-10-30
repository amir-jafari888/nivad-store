<?php

declare(strict_types=1);

/**
 * Canonical Phase 3 persistence adapter. Uses only the normalized
 * admin_notifications + admin_notification_recipients schema.
 */
final class AdminNotificationCenterRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function activeAdmins(): array
    {
        $stmt = $this->db->query(
            'SELECT id, email, first_name, last_name
             FROM admin_users
             WHERE status = "active" AND deleted_at IS NULL
             ORDER BY id ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listForAdmin(int $adminId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, min(100000, $offset));
        $where = ['r.admin_user_id = :admin_id'];
        $params = ['admin_id' => $adminId];

        if (($filters['unread'] ?? false) === true) $where[] = 'r.read_at IS NULL';
        if (($filters['acknowledged'] ?? '') === 'yes') $where[] = 'r.acknowledged_at IS NOT NULL';
        if (($filters['acknowledged'] ?? '') === 'no') $where[] = 'r.acknowledged_at IS NULL';
        if (isset($filters['severity']) && in_array($filters['severity'], ['info','success','warning','critical'], true)) {
            $where[] = 'n.severity = :severity';
            $params['severity'] = $filters['severity'];
        }
        if (isset($filters['event_key']) && $filters['event_key'] !== '') {
            $where[] = 'n.event_key = :event_key';
            $params['event_key'] = $filters['event_key'];
        }
        if (isset($filters['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$filters['from'])) {
            $where[] = 'n.created_at >= :from_date';
            $params['from_date'] = $filters['from'] . ' 00:00:00';
        }
        if (isset($filters['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$filters['to'])) {
            $where[] = 'n.created_at <= :to_date';
            $params['to_date'] = $filters['to'] . ' 23:59:59';
        }

        $sql = 'SELECT n.id, n.event_key, n.severity, n.title, n.message,
                       n.entity_type, n.entity_id, n.payload_json, n.created_at,
                       r.read_at, r.acknowledged_at
                FROM admin_notifications n
                INNER JOIN admin_notification_recipients r ON r.notification_id = n.id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY n.created_at DESC, n.id DESC
                LIMIT ' . $limit . ' OFFSET ' . $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function unreadCount(int $adminId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM admin_notification_recipients
             WHERE admin_user_id = :admin_id AND read_at IS NULL'
        );
        $stmt->execute(['admin_id' => $adminId]);
        return (int)$stmt->fetchColumn();
    }

    public function markRead(int $notificationId, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_notification_recipients
             SET read_at = COALESCE(read_at, CURRENT_TIMESTAMP)
             WHERE notification_id = :notification_id AND admin_user_id = :admin_id'
        );
        $stmt->execute(['notification_id' => $notificationId, 'admin_id' => $adminId]);
        return $stmt->rowCount() > 0 || $this->recipientExists($notificationId, $adminId);
    }

    public function markAllRead(int $adminId): int
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_notification_recipients
             SET read_at = CURRENT_TIMESTAMP
             WHERE admin_user_id = :admin_id AND read_at IS NULL'
        );
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->rowCount();
    }

    public function acknowledge(int $notificationId, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_notification_recipients
             SET read_at = COALESCE(read_at, CURRENT_TIMESTAMP),
                 acknowledged_at = COALESCE(acknowledged_at, CURRENT_TIMESTAMP)
             WHERE notification_id = :notification_id AND admin_user_id = :admin_id'
        );
        $stmt->execute(['notification_id' => $notificationId, 'admin_id' => $adminId]);
        return $stmt->rowCount() > 0 || $this->isAcknowledged($notificationId, $adminId);
    }

    private function recipientExists(int $notificationId, int $adminId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM admin_notification_recipients WHERE notification_id = :id AND admin_user_id = :admin LIMIT 1');
        $stmt->execute(['id' => $notificationId, 'admin' => $adminId]);
        return $stmt->fetchColumn() !== false;
    }

    private function isAcknowledged(int $notificationId, int $adminId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM admin_notification_recipients WHERE notification_id = :id AND admin_user_id = :admin AND acknowledged_at IS NOT NULL LIMIT 1');
        $stmt->execute(['id' => $notificationId, 'admin' => $adminId]);
        return $stmt->fetchColumn() !== false;
    }

    public function preferences(int $adminId): array
    {
        $stmt = $this->db->prepare(
            'SELECT event_key, in_app_enabled, email_enabled, minimum_severity,
                    quiet_hours_start, quiet_hours_end, timezone, updated_at
             FROM admin_notification_preferences
             WHERE admin_user_id = :admin_id ORDER BY event_key'
        );
        $stmt->execute(['admin_id' => $adminId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = ['*' => [
            'in_app_enabled' => true, 'email_enabled' => false,
            'minimum_severity' => 'info', 'quiet_hours_start' => null,
            'quiet_hours_end' => null, 'timezone' => 'UTC'
        ]];
        foreach ($rows as $row) {
            $result[$row['event_key']] = [
                'in_app_enabled' => (bool)$row['in_app_enabled'],
                'email_enabled' => (bool)$row['email_enabled'],
                'minimum_severity' => $row['minimum_severity'],
                'quiet_hours_start' => $row['quiet_hours_start'],
                'quiet_hours_end' => $row['quiet_hours_end'],
                'timezone' => $row['timezone'],
                'updated_at' => $row['updated_at']
            ];
        }
        return $result;
    }

    public function savePreference(int $adminId, string $eventKey, array $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_notification_preferences
             (admin_user_id, event_key, in_app_enabled, email_enabled, minimum_severity,
              quiet_hours_start, quiet_hours_end, timezone)
             VALUES (:admin_id, :event_key, :in_app, :email, :severity, :quiet_start, :quiet_end, :timezone)
             ON DUPLICATE KEY UPDATE
              in_app_enabled = VALUES(in_app_enabled), email_enabled = VALUES(email_enabled),
              minimum_severity = VALUES(minimum_severity), quiet_hours_start = VALUES(quiet_hours_start),
              quiet_hours_end = VALUES(quiet_hours_end), timezone = VALUES(timezone),
              updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'admin_id' => $adminId, 'event_key' => $eventKey,
            'in_app' => (int)$value['in_app_enabled'], 'email' => (int)$value['email_enabled'],
            'severity' => $value['minimum_severity'], 'quiet_start' => $value['quiet_hours_start'],
            'quiet_end' => $value['quiet_hours_end'], 'timezone' => $value['timezone']
        ]);
    }

    public function eventPolicy(string $eventKey): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_notification_event_policies WHERE event_key = :event_key LIMIT 1');
        $stmt->execute(['event_key' => $eventKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Creates one event and recipient rows atomically. A locked policy-state row
     * serializes duplicate/rate checks for the same event/entity/recipient set.
     */
    public function publish(array $event, array $recipients, array $policy): int
    {
        $recipientIds = array_values(array_unique(array_map(static fn(array $r): int => (int)$r['id'], $recipients)));
        sort($recipientIds, SORT_NUMERIC);
        if ($recipientIds === []) return 0;

        $identity = implode('|', [
            $event['event_key'], (string)($event['entity_type'] ?? ''),
            (string)($event['entity_id'] ?? ''), implode(',', $recipientIds)
        ]);
        $dedupeHash = hash('sha256', $identity);
        $now = time();
        $this->db->beginTransaction();
        try {
            $seed = $this->db->prepare(
                'INSERT IGNORE INTO admin_notification_policy_state
                 (dedupe_hash, window_started_at, window_count, last_created_at)
                 VALUES (:hash, CURRENT_TIMESTAMP, 0, NULL)'
            );
            $seed->execute(['hash' => $dedupeHash]);

            $lock = $this->db->prepare('SELECT window_started_at, window_count, last_created_at FROM admin_notification_policy_state WHERE dedupe_hash = :hash FOR UPDATE');
            $lock->execute(['hash' => $dedupeHash]);
            $state = $lock->fetch(PDO::FETCH_ASSOC) ?: [];

            $windowSeconds = max(60, min(86400, (int)$policy['window_seconds']));
            $dedupeSeconds = max(0, min(86400, (int)$policy['dedupe_seconds']));
            $maximum = max(1, min(1000, (int)$policy['maximum_per_window']));
            $windowStart = isset($state['window_started_at']) ? strtotime((string)$state['window_started_at']) : false;
            $lastCreated = isset($state['last_created_at']) ? strtotime((string)$state['last_created_at']) : false;
            $windowCount = (int)($state['window_count'] ?? 0);

            if ($lastCreated !== false && $dedupeSeconds > 0 && ($now - $lastCreated) < $dedupeSeconds) {
                $this->db->commit();
                return 0;
            }
            if ($windowStart === false || ($now - $windowStart) >= $windowSeconds) {
                $windowStart = $now;
                $windowCount = 0;
            }
            if ($windowCount >= $maximum) {
                $this->db->commit();
                return 0;
            }

            $insert = $this->db->prepare(
                'INSERT INTO admin_notifications
                 (event_key, severity, title, message, entity_type, entity_id, payload_json, created_by_admin_id)
                 VALUES (:event_key, :severity, :title, :message, :entity_type, :entity_id, :payload_json, :actor_id)'
            );
            $insert->execute([
                'event_key' => $event['event_key'], 'severity' => $event['severity'],
                'title' => $event['title'], 'message' => $event['message'],
                'entity_type' => $event['entity_type'] ?? null,
                'entity_id' => isset($event['entity_id']) ? (string)$event['entity_id'] : null,
                'payload_json' => isset($event['payload']) ? json_encode($event['payload'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'actor_id' => $event['actor_admin_id'] ?? null
            ]);
            $notificationId = (int)$this->db->lastInsertId();

            $recipientInsert = $this->db->prepare(
                'INSERT INTO admin_notification_recipients (notification_id, admin_user_id)
                 VALUES (:notification_id, :admin_id)'
            );
            $outboxInsert = $this->db->prepare(
                'INSERT INTO admin_email_outbox
                 (notification_id, recipient_admin_id, recipient_email, subject, body_text, status)
                 VALUES (:notification_id, :admin_id, :email, :subject, :body, "pending")'
            );
            foreach ($recipients as $recipient) {
                $recipientInsert->execute(['notification_id' => $notificationId, 'admin_id' => (int)$recipient['id']]);
                if (($policy['email_enabled'] ?? false) && !empty($recipient['email_enabled']) && !empty($recipient['email'])) {
                    $outboxInsert->execute([
                        'notification_id' => $notificationId, 'admin_id' => (int)$recipient['id'],
                        'email' => $recipient['email'], 'subject' => $event['title'], 'body' => $event['message']
                    ]);
                }
            }

            $stateUpdate = $this->db->prepare(
                'UPDATE admin_notification_policy_state
                 SET window_started_at = :window_start, window_count = :window_count,
                     last_created_at = CURRENT_TIMESTAMP, last_notification_id = :notification_id,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE dedupe_hash = :hash'
            );
            $stateUpdate->execute([
                'window_start' => date('Y-m-d H:i:s', $windowStart),
                'window_count' => $windowCount + 1,
                'notification_id' => $notificationId,
                'hash' => $dedupeHash
            ]);
            $this->db->commit();
            return $notificationId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function eventPolicies(): array
    {
        $stmt = $this->db->query(
            'SELECT event_key, enabled, severity, email_enabled, dedupe_seconds,
                    maximum_per_window, window_seconds, updated_at
             FROM admin_notification_event_policies ORDER BY event_key'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveEventPolicy(string $eventKey, array $policy): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_notification_event_policies
             (event_key, enabled, severity, email_enabled, dedupe_seconds, maximum_per_window, window_seconds)
             VALUES (:event_key, :enabled, :severity, :email_enabled, :dedupe, :maximum, :window)
             ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), severity = VALUES(severity),
             email_enabled = VALUES(email_enabled), dedupe_seconds = VALUES(dedupe_seconds),
             maximum_per_window = VALUES(maximum_per_window), window_seconds = VALUES(window_seconds),
             updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'event_key' => $eventKey, 'enabled' => (int)$policy['enabled'],
            'severity' => $policy['severity'], 'email_enabled' => (int)$policy['email_enabled'],
            'dedupe' => $policy['dedupe_seconds'], 'maximum' => $policy['maximum_per_window'],
            'window' => $policy['window_seconds']
        ]);
    }
}
