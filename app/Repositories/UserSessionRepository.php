<?php

declare(strict_types=1);

final class UserSessionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    private function hashSessionId(
        string $sessionId
    ): string {
        return hash(
            'sha256',
            $sessionId
        );
    }

    public function create(
        int $userId,
        string $sessionId,
        string $ipAddress,
        ?string $userAgent,
        int $expiresInSeconds
    ): int {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        if ($sessionId === '') {
            throw new InvalidArgumentException(
                'Session ID cannot be empty.'
            );
        }

        if ($expiresInSeconds < 60) {
            throw new InvalidArgumentException(
                'Session expiration is too short.'
            );
        }

        $expiresAt = date(
            'Y-m-d H:i:s',
            time() + $expiresInSeconds
        );

        $sessionTokenHash =
            $this->hashSessionId(
                $sessionId
            );

        $stmt = $this->db->prepare(
            'INSERT INTO user_sessions (
                user_id,
                session_token_hash,
                ip_address,
                user_agent,
                last_activity_at,
                expires_at
            ) VALUES (
                :user_id,
                :session_token_hash,
                :ip_address,
                :user_agent,
                CURRENT_TIMESTAMP,
                :expires_at
            )'
        );

        $stmt->execute([
            'user_id' =>
            $userId,

            'session_token_hash' =>
            $sessionTokenHash,

            'ip_address' =>
            $ipAddress,

            'user_agent' =>
            $userAgent,

            'expires_at' =>
            $expiresAt,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function existsActiveForUser(
        int $userId,
        string $sessionId
    ): bool {
        if (
            $userId < 1 ||
            $sessionId === ''
        ) {
            return false;
        }

        $sessionTokenHash =
            $this->hashSessionId(
                $sessionId
            );

        $stmt = $this->db->prepare(
            'SELECT id
             FROM user_sessions
             WHERE user_id = :user_id
               AND session_token_hash = :session_token_hash
               AND revoked_at IS NULL
               AND expires_at > CURRENT_TIMESTAMP
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' =>
            $userId,

            'session_token_hash' =>
            $sessionTokenHash,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveBySessionId(
        string $sessionId
    ): ?array {
        if ($sessionId === '') {
            return null;
        }

        $sessionTokenHash =
            $this->hashSessionId(
                $sessionId
            );

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                session_token_hash,
                ip_address,
                user_agent,
                last_activity_at,
                expires_at,
                created_at,
                revoked_at
             FROM user_sessions
             WHERE session_token_hash = :session_token_hash
               AND revoked_at IS NULL
               AND expires_at > CURRENT_TIMESTAMP
             LIMIT 1'
        );

        $stmt->execute([
            'session_token_hash' =>
            $sessionTokenHash,
        ]);

        $session = $stmt->fetch();

        return $session !== false
            ? $session
            : null;
    }

    public function revokeBySessionId(
        string $sessionId
    ): bool {
        if ($sessionId === '') {
            return false;
        }

        $sessionTokenHash =
            $this->hashSessionId(
                $sessionId
            );

        $stmt = $this->db->prepare(
            'UPDATE user_sessions
             SET revoked_at = CURRENT_TIMESTAMP
             WHERE session_token_hash = :session_token_hash
               AND revoked_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'session_token_hash' =>
            $sessionTokenHash,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function revokeAllForUser(
        int $userId
    ): int {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE user_sessions
             SET revoked_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id
               AND revoked_at IS NULL'
        );

        $stmt->execute([
            'user_id' =>
            $userId,
        ]);

        return $stmt->rowCount();
    }

    public function deleteExpired(): int
    {
        $stmt = $this->db->prepare(
            'DELETE FROM user_sessions
             WHERE expires_at <= CURRENT_TIMESTAMP
                OR revoked_at IS NOT NULL'
        );

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function findActiveByUserId(
        int $userId
    ): array {
        if ($userId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                session_token_hash,
                ip_address,
                user_agent,
                last_activity_at,
                expires_at,
                created_at,
                revoked_at
             FROM user_sessions
             WHERE user_id = :user_id
               AND revoked_at IS NULL
               AND expires_at > CURRENT_TIMESTAMP
             ORDER BY last_activity_at DESC, id DESC'
        );

        $stmt->execute([
            'user_id' =>
            $userId,
        ]);

        return $stmt->fetchAll();
    }

    public function findByIdForUser(
        int $sessionRecordId,
        int $userId
    ): ?array {
        if (
            $sessionRecordId < 1 ||
            $userId < 1
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                session_token_hash,
                ip_address,
                user_agent,
                last_activity_at,
                expires_at,
                created_at,
                revoked_at
             FROM user_sessions
             WHERE id = :id
               AND user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            'id' =>
            $sessionRecordId,

            'user_id' =>
            $userId,
        ]);

        $session = $stmt->fetch();

        return $session !== false
            ? $session
            : null;
    }

    public function revokeByIdForUser(
        int $sessionRecordId,
        int $userId
    ): bool {
        if (
            $sessionRecordId < 1 ||
            $userId < 1
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE user_sessions
             SET revoked_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND user_id = :user_id
               AND revoked_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' =>
            $sessionRecordId,

            'user_id' =>
            $userId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function touchBySessionId(
        string $sessionId
    ): bool {
        if ($sessionId === '') {
            return false;
        }

        $sessionTokenHash =
            $this->hashSessionId(
                $sessionId
            );

        $stmt = $this->db->prepare(
            'UPDATE user_sessions
             SET last_activity_at = CURRENT_TIMESTAMP
             WHERE session_token_hash = :session_token_hash
               AND revoked_at IS NULL
               AND expires_at > CURRENT_TIMESTAMP
             LIMIT 1'
        );

        $stmt->execute([
            'session_token_hash' =>
            $sessionTokenHash,
        ]);

        return $stmt->rowCount() === 1;
    }
}
