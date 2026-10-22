<?php

declare(strict_types=1);

final class NotificationRepository
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
        int $notificationId
    ): ?Notification {
        if ($notificationId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                type,
                title,
                message,
                data,
                is_read,
                read_at,
                created_at
             FROM notifications
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $notificationId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findByUserId(
        int $userId
    ): array {
        if ($userId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                type,
                title,
                message,
                data,
                is_read,
                read_at,
                created_at
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return array_map(
            fn(array $row): Notification =>
            $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function findUnreadByUserId(
        int $userId
    ): array {
        if ($userId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                type,
                title,
                message,
                data,
                is_read,
                read_at,
                created_at
             FROM notifications
             WHERE user_id = :user_id
               AND is_read = 0
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return array_map(
            fn(array $row): Notification =>
            $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function countUnreadByUserId(
        int $userId
    ): int {
        if ($userId < 1) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM notifications
             WHERE user_id = :user_id
               AND is_read = 0'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $data = null
    ): Notification {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (
                user_id,
                type,
                title,
                message,
                data
             ) VALUES (
                :user_id,
                :type,
                :title,
                :message,
                :data
             )'
        );

        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        $notificationId =
            (int) $this->db->lastInsertId();

        $notification =
            $this->findById($notificationId);

        if (!$notification instanceof Notification) {
            throw new RuntimeException(
                'Notification could not be loaded after creation.'
            );
        }

        return $notification;
    }

    public function markAsRead(
        int $notificationId
    ): ?Notification {
        if ($notificationId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET
                is_read = 1,
                read_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $notificationId,
        ]);

        return $this->findById(
            $notificationId
        );
    }

    public function markAllAsRead(
        int $userId
    ): int {
        if ($userId < 1) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET
                is_read = 1,
                read_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id
               AND is_read = 0'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $stmt->rowCount();
    }

    private function hydrate(
        array $row
    ): Notification {
        return new Notification(
            (int) $row['id'],
            (int) $row['user_id'],
            (string) $row['type'],
            (string) $row['title'],
            (string) $row['message'],
            $row['data'] !== null
                ? (string) $row['data']
                : null,
            (bool) $row['is_read'],
            $row['read_at'] !== null
                ? (string) $row['read_at']
                : null,
            (string) $row['created_at']
        );
    }
}
