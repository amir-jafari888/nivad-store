<?php

declare(strict_types=1);

/**
 * Canonical event metadata for security-sensitive admin operations.
 * This class classifies events; it does not automatically block accounts or
 * claim an incident/email was persisted or delivered.
 */
final class AdminSecurityEventPolicy
{
    private const EVENTS = [
        'admin.auth.login_succeeded' => [
            'severity' => 'info',
            'notify_email' => false,
            'open_incident' => false,
        ],
        'admin.auth.logout' => [
            'severity' => 'info',
            'notify_email' => false,
            'open_incident' => false,
        ],
        'admin.auth.login_failed' => [
            'severity' => 'warning',
            'notify_email' => false,
            'open_incident' => false,
        ],
        'admin.auth.session_invalid' => [
            'severity' => 'warning',
            'notify_email' => false,
            'open_incident' => true,
        ],
        'admin.authz.denied' => [
            'severity' => 'warning',
            'notify_email' => false,
            'open_incident' => false,
        ],
        'admin.role.permission_changed' => [
            'severity' => 'warning',
            'notify_email' => true,
            'open_incident' => true,
        ],
        'admin.admin.disabled' => [
            'severity' => 'warning',
            'notify_email' => true,
            'open_incident' => true,
        ],
        'admin.admin.deleted' => [
            'severity' => 'critical',
            'notify_email' => true,
            'open_incident' => true,
        ],
        'admin.session.revoke_all' => [
            'severity' => 'warning',
            'notify_email' => true,
            'open_incident' => true,
        ],
        'admin.sensitive_data.exposure_suspected' => [
            'severity' => 'critical',
            'notify_email' => true,
            'open_incident' => true,
        ],
        'admin.bulk_operation.failed' => [
            'severity' => 'warning',
            'notify_email' => true,
            'open_incident' => true,
        ],
        'admin.preview_token.invalid' => [
            'severity' => 'info',
            'notify_email' => false,
            'open_incident' => false,
        ],
        'admin.email.delivery_failed' => [
            'severity' => 'warning',
            'notify_email' => true,
            'open_incident' => true,
        ],
        'admin.database.migration_failed' => [
            'severity' => 'critical',
            'notify_email' => true,
            'open_incident' => true,
        ],
    ];

    public function definition(string $eventKey): ?array
    {
        $eventKey = strtolower(trim($eventKey));

        return self::EVENTS[$eventKey] ?? null;
    }

    public function requireDefinition(string $eventKey): array
    {
        $definition = $this->definition($eventKey);
        if ($definition === null) {
            throw new InvalidArgumentException('Unknown admin security event key.');
        }

        return ['event_key' => strtolower(trim($eventKey))] + $definition;
    }

    public function all(): array
    {
        return self::EVENTS;
    }
}
