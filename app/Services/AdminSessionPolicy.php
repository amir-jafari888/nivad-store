<?php

/** Non-DB session lifecycle contract. */
final class AdminSessionPolicy
{
    public static function normalize(array $session): array
    {
        $id = trim((string)($session['session_id'] ?? ''));
        if ($id === '' || strlen($id) > 128 || !preg_match('/^[A-Za-z0-9._:-]+$/', $id)) {
            throw new InvalidArgumentException('Invalid admin session identifier.');
        }

        $adminId = (int)($session['admin_id'] ?? 0);
        if ($adminId <= 0) {
            throw new InvalidArgumentException('Invalid admin session administrator identifier.');
        }

        $lastActivity = (int)($session['last_activity'] ?? 0);
        if ($lastActivity <= 0) {
            throw new InvalidArgumentException('Invalid admin session activity timestamp.');
        }

        $now = isset($session['now']) ? (int)$session['now'] : time();
        if ($now < $lastActivity) {
            throw new InvalidArgumentException('Admin session activity cannot be in the future.');
        }

        $timeout = isset($session['timeout']) ? (int)$session['timeout'] : 1800;
        if ($timeout < 60 || $timeout > 86400) {
            throw new InvalidArgumentException('Admin session timeout is outside the supported range.');
        }

        return [
            'session_id' => $id,
            'admin_id' => $adminId,
            'last_activity' => $lastActivity,
            'online' => ($now - $lastActivity) < $timeout,
        ];
    }

    public static function assertRevocationTarget(int $actorId, int $targetAdminId): void
    {
        if ($actorId <= 0 || $targetAdminId <= 0) {
            throw new InvalidArgumentException('Administrator identifiers must be positive integers.');
        }
        if ($actorId === $targetAdminId) {
            throw new RuntimeException('The current administrator session cannot be revoked through this policy.');
        }
    }
}
