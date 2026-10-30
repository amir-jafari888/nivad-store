<?php

declare(strict_types=1);

final class AdminAuthenticationAuditService
{
    private AdminSecurityEventPolicy $securityEventPolicy;

    public function __construct(?AdminSecurityEventPolicy $securityEventPolicy = null)
    {
        $this->securityEventPolicy = $securityEventPolicy ?? new AdminSecurityEventPolicy();
    }
    public function login(
        int $adminUserId,
        string $identifier
    ): void {
        if ($adminUserId < 1) {
            return;
        }

        Logger::info(
            'Admin login successful.',
            $this->eventContext('admin.auth.login_succeeded', [
                'admin_user_id' => $adminUserId,
                'identifier' => $identifier,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ])
        );
    }

    public function loginFailure(
        string $identifier,
        string $reason = 'authentication_failed'
    ): void {
        Logger::warning(
            'Admin login failed.',
            $this->eventContext('admin.auth.login_failed', [
                'identifier' => $identifier,
                'reason' => $reason,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ])
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
            $this->eventContext('admin.auth.logout', [
                'admin_user_id' => $adminUserId,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ])
        );
    }

    public function sessionInvalid(
        ?int $adminUserId,
        string $reason
    ): void {
        Logger::warning(
            'Admin session invalid.',
            $this->eventContext('admin.auth.session_invalid', [
                'admin_user_id' => $adminUserId,
                'reason' => $reason,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ])
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

    private function eventContext(string $eventKey, array $context): array
    {
        $definition = $this->securityEventPolicy->requireDefinition($eventKey);

        return SensitiveDataRedactor::redact(array_merge($context, [
            'event_key' => $definition['event_key'],
            'severity' => $definition['severity'],
            'notify_email' => $definition['notify_email'],
            'open_incident' => $definition['open_incident'],
            'request_id' => AdminRequestContext::id(),
        ]));
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
