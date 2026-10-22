<?php

declare(strict_types=1);

final class UserSessionService
{
    private UserSessionRepository $sessions;

    public function __construct(
        ?UserSessionRepository $sessions = null
    ) {
        $this->sessions =
            $sessions ??
            new UserSessionRepository();
    }

    public function isValid(
        int $userId,
        string $sessionId
    ): bool {
        if (
            $userId < 1 ||
            $sessionId === ''
        ) {
            return false;
        }

        $session =
            $this->sessions
            ->findActiveBySessionId(
                $sessionId
            );

        if ($session === null) {
            return false;
        }

        return (int)$session['user_id']
            === $userId;
    }

    public function revokeCurrent(
        string $sessionId
    ): bool {
        return $this->sessions
            ->revokeBySessionId(
                $sessionId
            );
    }

    public function revokeAll(
        int $userId
    ): int {
        return $this->sessions
            ->revokeAllForUser(
                $userId
            );
    }

    public function cleanupExpired(): int
    {
        return $this->sessions
            ->deleteExpired();
    }

    public function revokeAllExceptCurrent(
        int $userId,
        string $currentSessionId
    ): int {
        if (
            $userId < 1 ||
            $currentSessionId === ''
        ) {
            return 0;
        }

        $sessionTokenHash = hash(
            'sha256',
            $currentSessionId
        );

        $stmt =
            Database::connection()->prepare(
                'UPDATE user_sessions
             SET revoked_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id
               AND session_token_hash != :session_token_hash
               AND revoked_at IS NULL'
            );

        $stmt->execute([
            'user_id' =>
            $userId,

            'session_token_hash' =>
            $sessionTokenHash,
        ]);

        return $stmt->rowCount();
    }

    public function touchCurrent(string $sessionId): bool
    {
        if ($sessionId === '') {
            return false;
        }

        return $this->sessions->touchBySessionId(
            $sessionId
        );
    }
}
