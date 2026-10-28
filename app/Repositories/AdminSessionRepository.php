<?php

declare(strict_types=1);

final class AdminSessionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    public function create(
        int $adminUserId,
        string $sessionTokenHash,
        ?string $ipAddress,
        ?string $userAgent,
        int $expiresInSeconds
    ): int {
        if ($adminUserId < 1) {
            throw new InvalidArgumentException(
                'Invalid admin user ID.'
            );
        }

        if ($sessionTokenHash === '') {
            throw new InvalidArgumentException(
                'Session token hash cannot be empty.'
            );
        }

        if ($expiresInSeconds < 60) {
            throw new InvalidArgumentException(
                'Session expiration is too short.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO admin_sessions (
                admin_user_id,
                session_token_hash,
                ip_address,
                user_agent,
                last_activity_at,
                expires_at
            ) VALUES (
                :admin_user_id,
                :session_token_hash,
                :ip_address,
                :user_agent,
                CURRENT_TIMESTAMP,
                TIMESTAMPADD(
                    SECOND,
                    :expires_in_seconds,
                    CURRENT_TIMESTAMP
                )
            )'
        );

        $stmt->execute([
            'admin_user_id' =>
            $adminUserId,

            'session_token_hash' =>
            $sessionTokenHash,

            'ip_address' =>
            $ipAddress,

            'user_agent' =>
            $userAgent,

            'expires_in_seconds' =>
            $expiresInSeconds,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findActiveByTokenHash(
        string $sessionTokenHash
    ): ?array {
        if ($sessionTokenHash === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                admin_user_id,
                session_token_hash,
                ip_address,
                user_agent,
                last_activity_at,
                expires_at,
                created_at,
                revoked_at
             FROM admin_sessions
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

    public function revokeByTokenHash(
        string $sessionTokenHash
    ): bool {
        if ($sessionTokenHash === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE admin_sessions
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

    public function revokeAllForAdmin(
        int $adminUserId
    ): int {
        if ($adminUserId < 1) {
            throw new InvalidArgumentException(
                'Invalid admin user ID.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE admin_sessions
             SET revoked_at = CURRENT_TIMESTAMP
             WHERE admin_user_id = :admin_user_id
               AND revoked_at IS NULL'
        );

        $stmt->execute([
            'admin_user_id' =>
            $adminUserId,
        ]);

        return $stmt->rowCount();
    }

    public function touchByTokenHash(
        string $sessionTokenHash
    ): bool {
        if ($sessionTokenHash === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE admin_sessions
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

    public function deleteExpired(): int
    {
        $stmt = $this->db->prepare(
            'DELETE FROM admin_sessions
             WHERE expires_at <= CURRENT_TIMESTAMP
                OR revoked_at IS NOT NULL'
        );

        $stmt->execute();

        return $stmt->rowCount();
    }
}
