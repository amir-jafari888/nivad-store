<?php

declare(strict_types=1);

final class AdminLoginAttemptRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ??
            Database::connection();
    }

    public function record(
        ?int $adminUserId,
        string $identifier,
        string $ipAddress,
        ?string $userAgent,
        string $status,
        ?string $failureReason = null
    ): int {
        if (
            $identifier === '' ||
            $ipAddress === ''
        ) {
            throw new InvalidArgumentException(
                'Identifier and IP address cannot be empty.'
            );
        }

        if (
            $adminUserId !== null &&
            $adminUserId < 1
        ) {
            throw new InvalidArgumentException(
                'Invalid admin user ID.'
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

        $stmt =
            $this->db->prepare(
                'INSERT INTO login_attempts (
                    admin_user_id,
                    identifier,
                    ip_address,
                    user_agent,
                    status,
                    failure_reason
                ) VALUES (
                    :admin_user_id,
                    :identifier,
                    :ip_address,
                    :user_agent,
                    :status,
                    :failure_reason
                )'
            );

        $stmt->execute([
            'admin_user_id' =>
            $adminUserId,

            'identifier' =>
            $identifier,

            'ip_address' =>
            $ipAddress,

            'user_agent' =>
            $userAgent,

            'status' =>
            $status,

            'failure_reason' =>
            $failureReason,
        ]);

        return (int)
        $this->db->lastInsertId();
    }

    public function countRecentFailuresByIdentifier(
        string $identifier,
        int $windowSeconds
    ): int {
        if (
            $identifier === '' ||
            $windowSeconds < 1
        ) {
            return 0;
        }

        $stmt =
            $this->db->prepare(
                'SELECT COUNT(*)
                 FROM login_attempts
                 WHERE identifier = :identifier
                   AND status IN (
                        "failed",
                        "blocked",
                        "rate_limited"
                   )
                   AND created_at >= (
                        NOW() -
                        INTERVAL :window_seconds SECOND
                   )'
            );

        $stmt->bindValue(
            ':identifier',
            $identifier,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':window_seconds',
            $windowSeconds,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int)
        $stmt->fetchColumn();
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

        $stmt =
            $this->db->prepare(
                'SELECT COUNT(*)
                 FROM login_attempts
                 WHERE ip_address = :ip_address
                   AND status IN (
                        "failed",
                        "blocked",
                        "rate_limited"
                   )
                   AND created_at >= (
                        NOW() -
                        INTERVAL :window_seconds SECOND
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

        return (int)
        $stmt->fetchColumn();
    }
}
