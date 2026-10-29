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
                'list' => self::operation('read', false),
                'create' => self::operation('write', true, true),
                'update' => self::operation('write', true),
                'enable' => self::operation('write', true),
                'disable' => self::operation('write', true),
                'soft_delete' => self::operation('write', true, true),
                'restore' => self::operation('write', true),
                'assign_role' => self::operation('write', true, true),
                'reset_password' => self::operation('write', true, true),
            ]),
            'admin_sessions' => self::domain('ADM-006', 'admin.sessions.view', [
                'list' => self::operation('read', false),
                'revoke' => self::operation('write', true, true, 'admin.sessions.revoke'),
                'revoke_all_except_current' => self::operation('write', true, true, 'admin.sessions.revoke'),
            ]),
            'audit' => self::domain('ADM-008', 'admin.audit.view', [
                'list' => self::operation('read', false),
                'detail' => self::operation('read', false),
                'diff' => self::operation('read', false),
                'timeline' => self::operation('read', false),
                'export' => self::operation('read', false),
            ]),
            'notifications' => self::domain('ADM-016', 'admin.notifications.view', [
                'list' => self::operation('read', false),
                'unread_count' => self::operation('read', false),
                'mark_read' => self::operation('write', true),
                'mark_all_read' => self::operation('write', true),
                'create' => self::operation('write', true, true, 'admin.notifications.manage'),
                'queue_email' => self::operation('write', true, true, 'admin.notifications.manage'),
            ]),
            'content' => self::domain('ADM-032', 'admin.content.manage', [
                'create_revision' => self::operation('write', true),
                'list_revisions' => self::operation('read', false),
                'preview' => self::operation('read', false),
                'publish' => self::operation('write', true, true, 'admin.content.publish'),
                'schedule' => self::operation('write', true, true, 'admin.content.publish'),
                'rollback' => self::operation('write', true, true, 'admin.content.publish'),
            ]),
            'bulk_operations' => self::domain('ADM-031', 'admin.bulk_operations.execute', [
                'validate' => self::operation('read', false),
                'enqueue' => self::operation('write', true),
                'status' => self::operation('read', false),
                'cancel' => self::operation('write', true, true),
                'retry' => self::operation('write', true, true),
                'save_view' => self::operation('write', true),
                'list_views' => self::operation('read', false),
            ]),
            'approvals' => self::domain('ADM-039', 'admin.approvals.decide', [
                'create_request' => self::operation('write', true),
                'list' => self::operation('read', false),
                'detail' => self::operation('read', false),
                'approve' => self::operation('write', true, true),
                'reject' => self::operation('write', true, true),
            ]),
            'incidents' => self::domain('ADM-019', 'admin.incidents.manage', [
                'register' => self::operation('write', true),
                'list' => self::operation('read', false),
                'detail' => self::operation('read', false),
                'assign' => self::operation('write', true),
                'resolve' => self::operation('write', true, true),
            ]),
            'media' => self::domain('ADM-015', 'admin.media.manage', [
                'validate_upload' => self::operation('read', false),
                'store_file' => self::operation('write', false),
                'register_metadata' => self::operation('write', true),
                'list' => self::operation('read', false),
                'attach' => self::operation('write', true),
                'detach' => self::operation('write', true),
                'delete' => self::operation('write', true, true),
            ]),
            'support' => self::domain('ADM-022', 'admin.support.manage', [
                'create' => self::operation('write', true),
                'list' => self::operation('read', false),
                'detail' => self::operation('read', false),
                'assign' => self::operation('write', true),
                'reply' => self::operation('write', true),
                'transition' => self::operation('write', true),
            ]),
            'analytics' => self::domain('ADM-023', 'admin.analytics.view', [
                'record_event' => self::operation('write', true),
                'aggregate' => self::operation('read', false),
                'timeseries' => self::operation('read', false),
                'top_entities' => self::operation('read', false),
            ]),
            'search' => self::domain('ADM-030', 'admin.search.use', [
                'search' => self::operation('read', false),
                'suggest' => self::operation('read', false),
            ]),
            'reports' => self::domain('ADM-042', 'admin.reports.view', [
                'summary' => self::operation('read', false),
                'orders' => self::operation('read', false),
                'payments' => self::operation('read', false),
                'inventory' => self::operation('read', false),
                'customers' => self::operation('read', false),
                'export_csv' => self::operation('read', false),
            ]),
            'settings' => self::domain('ADM-034', 'admin.settings.view', [
                'read' => self::operation('read', false),
                'update' => self::operation('write', true, true),
            ]),
        ];
    }

    public static function find(string $domain): ?array
    {
        return self::all()[trim($domain)] ?? null;
    }

    public static function operation(string $domain, string $operation): ?array
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

    private static function operation(
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
