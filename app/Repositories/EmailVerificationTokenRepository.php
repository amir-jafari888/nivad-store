<?php

declare(strict_types=1);

final class EmailVerificationTokenRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function create(
        int $userId,
        string $tokenHash,
        string $ipAddress,
        ?string $userAgent,
        int $expiresInSeconds
    ): int {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        if ($tokenHash === '') {
            throw new InvalidArgumentException(
                'Token hash cannot be empty.'
            );
        }

        if ($expiresInSeconds < 60) {
            throw new InvalidArgumentException(
                'Token expiration is too short.'
            );
        }

        $expiresAt = date(
            'Y-m-d H:i:s',
            time() + $expiresInSeconds
        );

        $stmt = $this->db->prepare(
            'INSERT INTO email_verification_tokens (
                user_id,
                token_hash,
                ip_address,
                user_agent,
                expires_at
            ) VALUES (
                :user_id,
                :token_hash,
                :ip_address,
                :user_agent,
                :expires_at
            )'
        );

        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'expires_at' => $expiresAt,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findValidByHash(
        string $tokenHash
    ): ?array {
        if ($tokenHash === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                token_hash,
                ip_address,
                user_agent,
                expires_at,
                used_at,
                created_at
             FROM email_verification_tokens
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > CURRENT_TIMESTAMP
             LIMIT 1'
        );

        $stmt->execute([
            'token_hash' => $tokenHash,
        ]);

        $token = $stmt->fetch();

        return $token !== false
            ? $token
            : null;
    }

    public function markUsed(
        int $tokenId
    ): bool {
        if ($tokenId < 1) {
            throw new InvalidArgumentException(
                'Invalid token ID.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE email_verification_tokens
             SET used_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND used_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $tokenId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function revokeActiveForUser(
        int $userId
    ): int {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE email_verification_tokens
             SET used_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id
               AND used_at IS NULL
               AND expires_at > CURRENT_TIMESTAMP'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $stmt->rowCount();
    }

    public function findValidByHashForUpdate(
        string $tokenHash
    ): ?array {
        if ($tokenHash === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
            id,
            user_id,
            token_hash,
            ip_address,
            user_agent,
            expires_at,
            used_at,
            created_at
         FROM email_verification_tokens
         WHERE token_hash = :token_hash
           AND used_at IS NULL
           AND expires_at > CURRENT_TIMESTAMP
         LIMIT 1
         FOR UPDATE'
        );

        $stmt->execute([
            'token_hash' => $tokenHash,
        ]);

        $token = $stmt->fetch();

        return $token !== false ? $token : null;
    }

    public function deleteExpired(): int
    {
        $stmt = $this->db->prepare(
            'DELETE FROM email_verification_tokens
         WHERE expires_at <= CURRENT_TIMESTAMP
            OR used_at IS NOT NULL'
        );

        $stmt->execute();

        return $stmt->rowCount();
    }
}
