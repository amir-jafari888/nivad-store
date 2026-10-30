<?php

declare(strict_types=1);

/**
 * Canonical non-UI contract map for Admin Panel commands.
 *
 * This file deliberately describes application boundaries rather than storage
 * details. A DB-backed implementation may be attached later without changing
 * command names, permissions, approval semantics, or audit requirements.
 */
final class AdminCapabilityContractRegistry
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'admin_accounts' => self::domain('ADM-005', 'admin.admins.manage', [
                'list' => self::makeOperation('read', false),
                'create' => self::makeOperation('write', true, true),
                'update' => self::makeOperation('write', true),
                'enable' => self::makeOperation('write', true),
                'disable' => self::makeOperation('write', true),
                'soft_delete' => self::makeOperation('write', true, true),
                'restore' => self::makeOperation('write', true),
                'assign_role' => self::makeOperation('write', true, true),
                'reset_password' => self::makeOperation('write', true, true),
            ]),
            'admin_sessions' => self::domain('ADM-006', 'admin.sessions.view', [
                'list' => self::makeOperation('read', false),
                'revoke' => self::makeOperation('write', true, true, 'admin.sessions.revoke'),
                'revoke_all_except_current' => self::makeOperation('write', true, true, 'admin.sessions.revoke'),
            ]),
            'audit' => self::domain('ADM-008', 'admin.audit.view', [
                'list' => self::makeOperation('read', false),
                'detail' => self::makeOperation('read', false),
                'diff' => self::makeOperation('read', false),
                'timeline' => self::makeOperation('read', false),
                'export' => self::makeOperation('read', false),
            ]),
            'notifications' => self::domain('ADM-016', 'admin.notifications.view', [
                'list' => self::makeOperation('read', false),
                'unread_count' => self::makeOperation('read', false),
                'mark_read' => self::makeOperation('write', true),
                'mark_all_read' => self::makeOperation('write', true),
                'create' => self::makeOperation('write', true, true, 'admin.notifications.manage'),
                'queue_email' => self::makeOperation('write', true, true, 'admin.notifications.manage'),
            ]),
            'content' => self::domain('ADM-014', 'admin.content.manage', [
                'create_revision' => self::makeOperation('write', true, false, 'admin.content.manage'),
                'list_revisions' => self::makeOperation('read', false, false, 'admin.content.manage'),
                'preview' => self::makeOperation('read', false, false, 'admin.content.manage'),
                'publish' => self::makeOperation('write', true, true, 'admin.content.publish'),
                'schedule' => self::makeOperation('write', true, true, 'admin.content.publish'),
                'rollback' => self::makeOperation('write', true, true, 'admin.content.publish'),
            ]),
            'bulk_operations' => self::domain('ADM-031', 'admin.bulk_operations.execute', [
                'validate' => self::makeOperation('read', false),
                'enqueue' => self::makeOperation('write', true),
                'status' => self::makeOperation('read', false),
                'cancel' => self::makeOperation('write', true, true),
                'retry' => self::makeOperation('write', true, true),
                'save_view' => self::makeOperation('write', true),
                'list_views' => self::makeOperation('read', false),
            ]),
            'approvals' => self::domain('ADM-039', 'admin.approvals.decide', [
                'create_request' => self::makeOperation('write', true),
                'list' => self::makeOperation('read', false),
                'detail' => self::makeOperation('read', false),
                'approve' => self::makeOperation('write', true, true),
                'reject' => self::makeOperation('write', true, true),
            ]),
            'incidents' => self::domain('ADM-019', 'admin.incidents.manage', [
                'register' => self::makeOperation('write', true),
                'list' => self::makeOperation('read', false),
                'detail' => self::makeOperation('read', false),
                'assign' => self::makeOperation('write', true),
                'resolve' => self::makeOperation('write', true, true),
            ]),
            'media' => self::domain('ADM-015', 'admin.media.manage', [
                'validate_upload' => self::makeOperation('read', false),
                'store_file' => self::makeOperation('write', false),
                'register_metadata' => self::makeOperation('write', true),
                'list' => self::makeOperation('read', false),
                'attach' => self::makeOperation('write', true),
                'detach' => self::makeOperation('write', true),
                'delete' => self::makeOperation('write', true, true),
            ]),
            'support' => self::domain('ADM-022', 'admin.support.manage', [
                'create' => self::makeOperation('write', true),
                'list' => self::makeOperation('read', false),
                'detail' => self::makeOperation('read', false),
                'assign' => self::makeOperation('write', true),
                'reply' => self::makeOperation('write', true),
                'transition' => self::makeOperation('write', true),
            ]),
            'analytics' => self::domain('ADM-023', 'admin.analytics.view', [
                'record_event' => self::makeOperation('write', true),
                'aggregate' => self::makeOperation('read', false),
                'timeseries' => self::makeOperation('read', false),
                'top_entities' => self::makeOperation('read', false),
            ]),
            'search' => self::domain('ADM-030', 'admin.search.use', [
                'search' => self::makeOperation('read', false),
                'suggest' => self::makeOperation('read', false),
            ]),
            'reports' => self::domain('ADM-042', 'admin.reports.view', [
                'summary' => self::makeOperation('read', false),
                'orders' => self::makeOperation('read', false),
                'payments' => self::makeOperation('read', false),
                'inventory' => self::makeOperation('read', false),
                'customers' => self::makeOperation('read', false),
                'export_csv' => self::makeOperation('read', false),
            ]),
            'settings' => self::domain('ADM-034', 'admin.settings.view', [
                'read' => self::makeOperation('read', false),
                'update' => self::makeOperation('write', true, true),
            ]),
        ];
    }

    public static function find(string $domain): ?array
    {
        return self::all()[trim($domain)] ?? null;
    }

    public static function getOperation(string $domain, string $operation): ?array
    {
        $definition = self::find($domain);
        if ($definition === null) {
            return null;
        }

        return $definition['operations'][$operation] ?? null;
    }

    private static function domain(string $featureId, string $permission, array $operations): array
    {
        return [
            'feature_id' => $featureId,
            'permission' => $permission,
            'operations' => $operations,
        ];
    }

    private static function makeOperation(
        string $kind,
        bool $databaseRequired,
        bool $auditRequired = false,
        ?string $permission = null
    ): array {
        return [
            'kind' => $kind,
            'database_required' => $databaseRequired,
            'audit_required' => $auditRequired,
            'permission' => $permission,
        ];
    }
}
