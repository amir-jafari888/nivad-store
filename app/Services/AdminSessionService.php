<?php

declare(strict_types=1);

final class AdminSessionService
{
    private AdminSessionRepository $sessions;

    public function __construct(
        ?AdminSessionRepository $sessions = null
    ) {
        $this->sessions =
            $sessions ??
            new AdminSessionRepository();
    }

    public function create(
        int $adminUserId,
        string $sessionTokenHash,
        ?string $ipAddress,
        ?string $userAgent,
        int $expiresInSeconds
    ): int {
        return $this->sessions->create(
            $adminUserId,
            $sessionTokenHash,
            $ipAddress,
            $userAgent,
            $expiresInSeconds
        );
    }

    public function isValid(
        string $sessionTokenHash,
        int $adminUserId
    ): bool {
        if (
            $sessionTokenHash === '' ||
            $adminUserId < 1
        ) {
            return false;
        }

        $session =
            $this->sessions
            ->findActiveByTokenHash(
                $sessionTokenHash
            );

        if ($session === null) {
            return false;
        }

        return (int)$session['admin_user_id']
            === $adminUserId;
    }

    public function revokeCurrent(
        string $sessionTokenHash
    ): bool {
        return $this->sessions
            ->revokeByTokenHash(
                $sessionTokenHash
            );
    }

    public function revokeAll(
        int $adminUserId
    ): int {
        return $this->sessions
            ->revokeAllForAdmin(
                $adminUserId
            );
    }

    public function touch(
        string $sessionTokenHash
    ): bool {
        return $this->sessions
            ->touchByTokenHash(
                $sessionTokenHash
            );
    }

    public function activeForAdmin(int $adminUserId, string $currentTokenHash): array
    {
        if ($adminUserId < 1 || $currentTokenHash === '') {
            return [];
        }
        return $this->sessions->activeForAdmin($adminUserId, $currentTokenHash);
    }

    public function revokeByIdForAdmin(int $sessionId, int $adminUserId, string $currentTokenHash): bool
    {
        if ($sessionId < 1 || $adminUserId < 1 || $currentTokenHash === '') {
            return false;
        }
        return $this->sessions->revokeByIdForAdmin($sessionId, $adminUserId, $currentTokenHash);
    }

    public function cleanupExpired(): int
    {
        return $this->sessions
            ->deleteExpired();
    }
}
