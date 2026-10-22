<?php

declare(strict_types=1);

final class UserSessionManagementService
{
    private UserSessionRepository $sessions;

    public function __construct(
        ?UserSessionRepository $sessions = null
    ) {
        $this->sessions =
            $sessions ??
            new UserSessionRepository();
    }

    public function listActive(
        int $userId
    ): array {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        return $this->sessions
            ->findActiveByUserId($userId);
    }

    public function revoke(
        int $sessionRecordId,
        int $userId
    ): bool {
        if (
            $sessionRecordId < 1 ||
            $userId < 1
        ) {
            return false;
        }

        return $this->sessions
            ->revokeByIdForUser(
                $sessionRecordId,
                $userId
            );
    }

    public function revokeOtherSessions(
        int $userId,
        string $currentSessionId
    ): int {
        if (
            $userId < 1 ||
            $currentSessionId === ''
        ) {
            return 0;
        }

        $service =
            new UserSessionService(
                $this->sessions
            );

        return $service->revokeAllExceptCurrent(
            $userId,
            $currentSessionId
        );
    }

    public function listActiveExceptCurrent(
        int $userId,
        string $currentSessionId
    ): array {
        if (
            $userId < 1 ||
            $currentSessionId === ''
        ) {
            return [];
        }

        $sessions =
            $this->sessions
            ->findActiveByUserId($userId);

        return array_values(
            array_filter(
                $sessions,
                static function (array $session) use (
                    $currentSessionId
                ): bool {
                    return !hash_equals(
                        (string)$session['session_id'],
                        $currentSessionId
                    );
                }
            )
        );
    }

    public function findByIdForUser(
        int $sessionRecordId,
        int $userId
    ): ?array {
        return $this->sessions->findByIdForUser(
            $sessionRecordId,
            $userId
        );
    }
}
