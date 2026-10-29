<?php

declare(strict_types=1);

/**
 * Repository for the additive admin-notification/outbox schema.
 * The migration must be reviewed and applied before these methods are used.
 */
final class AdminNotificationRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function create(
        string $eventKey,
        string $severity,
        string $title,
        string $message,
        array $recipientAdminIds,
        ?int $createdByAdminId = null,
        ?string $entityType = null,
        int|string|null $entityId = null,
        ?array $payload = null
    ): int {
        $recipientAdminIds = array_values(array_unique(array_filter(
            array_map('intval', $recipientAdminIds),
            static fn(int $id): bool => $id > 0
        )));

        if ($recipientAdminIds === []) {
            throw new InvalidArgumentException(
                'At least one valid admin recipient is required.'
            );
        }

        $payloadJson = $payload === null
            ? null
            : json_encode(
                $payload,
                JSON_THROW_ON_ERROR |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO admin_notifications (
                    event_key, severity, title, message,
                    entity_type, entity_id, payload_json,
                    created_by_admin_id
                ) VALUES (
                    :event_key, :severity, :title, :message,
                    :entity_type, :entity_id, :payload_json,
                    :created_by_admin_id
                )'
            );

            $stmt->execute([
                'event_key' => $eventKey,
                'severity' => $severity,
                'title' => $title,
                'message' => $message,
                'entity_type' => $entityType,
                'entity_id' => $entityId !== null ? (string) $entityId : null,
                'payload_json' => $payloadJson,
                'created_by_admin_id' => $createdByAdminId,
            ]);

            $notificationId = (int) $this->db->lastInsertId();

            $recipientStmt = $this->db->prepare(
                'INSERT INTO admin_notification_recipients (
                    notification_id, admin_user_id
                )
                SELECT :notification_id, a.id
                FROM admin_users AS a
                WHERE a.id = :admin_user_id
                  AND a.status = :status
                  AND a.deleted_at IS NULL'
            );

            foreach ($recipientAdminIds as $recipientId) {
                $recipientStmt->execute([
                    'notification_id' => $notificationId,
                    'admin_user_id' => $recipientId,
                    'status' => 'active',
                ]);
            }

            if ($recipientStmt->rowCount() < 1) {
                // Verify that at least one active recipient was inserted.
                $verify = $this->db->prepare(
                    'SELECT COUNT(*)
                     FROM admin_notification_recipients
                     WHERE notification_id = :notification_id'
                );
                $verify->execute(['notification_id' => $notificationId]);

                if ((int) $verify->fetchColumn() < 1) {
                    throw new RuntimeException(
                        'No active admin recipients were found.'
                    );
                }
            }

            $this->db->commit();

            return $notificationId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function findForAdmin(
        int $notificationId,
        int $adminUserId
    ): ?array {
        if ($notificationId < 1 || $adminUserId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                n.id, n.event_key, n.severity, n.title, n.message,
                n.entity_type, n.entity_id, n.payload_json, n.created_at,
                r.read_at, r.acknowledged_at
             FROM admin_notifications AS n
             INNER JOIN admin_notification_recipients AS r
                ON r.notification_id = n.id
             WHERE n.id = :notification_id
               AND r.admin_user_id = :admin_user_id
             LIMIT 1'
        );
        $stmt->execute([
            'notification_id' => $notificationId,
            'admin_user_id' => $adminUserId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function listForAdmin(
        int $adminUserId,
        bool $unreadOnly = false,
        int $limit = 50
    ): array {
        if ($adminUserId < 1) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $unreadSql = $unreadOnly ? 'AND r.read_at IS NULL' : '';

        $stmt = $this->db->prepare(
            'SELECT
                n.id, n.event_key, n.severity, n.title, n.message,
                n.entity_type, n.entity_id, n.payload_json, n.created_at,
                r.read_at, r.acknowledged_at
             FROM admin_notifications AS n
             INNER JOIN admin_notification_recipients AS r
                ON r.notification_id = n.id
             WHERE r.admin_user_id = :admin_user_id
             ' . $unreadSql . '
             ORDER BY n.created_at DESC, n.id DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['admin_user_id' => $adminUserId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markReadForAdmin(
        int $notificationId,
        int $adminUserId
    ): bool {
        if ($notificationId < 1 || $adminUserId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE admin_notification_recipients
             SET read_at = COALESCE(read_at, CURRENT_TIMESTAMP)
             WHERE notification_id = :notification_id
               AND admin_user_id = :admin_user_id'
        );
        $stmt->execute([
            'notification_id' => $notificationId,
            'admin_user_id' => $adminUserId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function enqueueEmail(
        ?int $notificationId,
        ?int $recipientAdminId,
        string $recipientEmail,
        string $subject,
        string $bodyText
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_email_outbox (
                notification_id, recipient_admin_id, recipient_email,
                subject, body_text, status
            ) VALUES (
                :notification_id, :recipient_admin_id, :recipient_email,
                :subject, :body_text, :status
            )'
        );
        $stmt->execute([
            'notification_id' => $notificationId,
            'recipient_admin_id' => $recipientAdminId,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'body_text' => $bodyText,
            'status' => 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }
}
