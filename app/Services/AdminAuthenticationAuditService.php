<?php

declare(strict_types=1);

final class AdminAuthenticationAuditService
{
    public function login(
        int $adminUserId,
        string $identifier
    ): void {
        if ($adminUserId < 1) {
            return;
        }

        Logger::info(
            'Admin login successful.',
            [
                'admin_user_id' => $adminUserId,
                'identifier' => $identifier,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    public function loginFailure(
        string $identifier,
        string $reason = 'authentication_failed'
    ): void {
        Logger::warning(
            'Admin login failed.',
            [
                'identifier' => $identifier,
                'reason' => $reason,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    public function failedLogin(
        string $identifier
    ): void {
        $this->loginFailure($identifier);
    }

    public function logout(
        ?int $adminUserId
    ): void {
        Logger::info(
            'Admin logout.',
            [
                'admin_user_id' => $adminUserId,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    public function sessionInvalid(
        ?int $adminUserId,
        string $reason
    ): void {
        Logger::warning(
            'Admin session invalid.',
            [
                'admin_user_id' => $adminUserId,
                'reason' => $reason,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    public function sessionExpired(
        int $adminUserId
    ): void {
        $this->sessionInvalid(
            $adminUserId,
            'session_expired'
        );
    }

    public function containsSensitiveData(
        array $context
    ): bool {
        $sensitiveKeys = [
            'password',
            'password_hash',
            'token',
            'session_id',
            'csrf_token',
            'secret',
            'api_key',
        ];

        foreach (array_keys($context) as $key) {
            if (
                in_array(
                    strtolower((string) $key),
                    $sensitiveKeys,
                    true
                )
            ) {
                return true;
            }
        }

        return false;
    }
}
