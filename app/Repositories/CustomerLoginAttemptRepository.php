<?php

declare(strict_types=1);

final class CustomerLoginAttemptRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db = $db ?? Database::connection();
    }

    public function record(
        ?int $userId,
        string $identifierHash,
        string $ipAddress,
        ?string $userAgent,
        string $status,
        ?string $failureReason = null
    ): int {
        if ($identifierHash === '') {
            throw new InvalidArgumentException(
                'Identifier hash cannot be empty.'
            );
        }

        if ($ipAddress === '') {
            throw new InvalidArgumentException(
                'IP address cannot be empty.'
            );
        }

        $allowedStatuses = [
            'success',
            'failed',
            'blocked',
            'rate_limited',
        ];

        if (!in_array(
            $status,
            $allowedStatuses,
            true
        )) {
            throw new InvalidArgumentException(
                'Invalid login attempt status.'
            );
        }

        if (
            $userId !== null &&
            $userId < 1
        ) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO customer_login_attempts (
                user_id,
                identifier_hash,
                ip_address,
                user_agent,
                status,
                failure_reason
            ) VALUES (
                :user_id,
                :identifier_hash,
                :ip_address,
                :user_agent,
                :status,
                :failure_reason
            )'
        );

        $stmt->execute([
            'user_id' => $userId,
            'identifier_hash' => $identifierHash,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => $status,
            'failure_reason' => $failureReason,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function countRecentFailuresByIdentifier(
        string $identifierHash,
        int $windowSeconds
    ): int {
        if (
            $identifierHash === '' ||
            $windowSeconds < 1
        ) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM customer_login_attempts
             WHERE identifier_hash = :identifier_hash
               AND status IN (
                    "failed",
                    "blocked",
                    "rate_limited"
               )
               AND created_at >= (
                    NOW() - INTERVAL :window_seconds SECOND
               )'
        );

        $stmt->bindValue(
            ':identifier_hash',
            $identifierHash,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':window_seconds',
            $windowSeconds,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function countRecentFailuresByIp(
        string $ipAddress,
        int $windowSeconds
    ): int {
        if (
            $ipAddress === '' ||
            $windowSeconds < 1
        ) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM customer_login_attempts
             WHERE ip_address = :ip_address
               AND status IN (
                    "failed",
                    "blocked",
                    "rate_limited"
               )
               AND created_at >= (
                    NOW() - INTERVAL :window_seconds SECOND
               )'
        );

        $stmt->bindValue(
            ':ip_address',
            $ipAddress,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':window_seconds',
            $windowSeconds,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }
}
