<?php

declare(strict_types=1);

final class AuthenticationAuditService
{
    public function login(
        int $userId,
        string $identifier
    ): void {
        if ($userId < 1) {
            return;
        }

        Logger::info(
            'User login successful.',
            [
                'user_id' => $userId,
                'identifier' => $identifier,
                'ip_address' => Request::ip(),
            ]
        );
    }

    public function logout(
        ?int $userId
    ): void {
        Logger::info(
            'User logout.',
            [
                'user_id' => $userId,
                'ip_address' => Request::ip(),
            ]
        );
    }
}
