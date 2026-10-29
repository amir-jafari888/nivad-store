<?php

declare(strict_types=1);

/**
 * Single source of truth for the accepted Admin Panel feature register.
 *
 * This registry describes capability contracts; it does not claim that a
 * feature is complete, and it never substitutes for route/service checks.
 * Database-backed capabilities remain gated until their schema is verified.
 */
final class AdminFeatureRegistry
{
    public const DATABASE_REQUIRED = 'database_required';
    public const EXTERNAL_DEPENDENCY = 'external_dependency';
    public const CONDITIONAL = 'conditional';

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'ADM-001' => self::feature('Admin identity, login/logout and sessions', 'admin.dashboard.view', true, [], 'authentication'),
            'ADM-002' => self::feature('Admin route and API authorization', 'admin.dashboard.view', false, ['ADM-001', 'ADM-003'], 'security'),
            'ADM-003' => self::feature('Granular roles and permissions', 'admin.permissions.manage', true, ['ADM-001'], 'authorization'),
            'ADM-004' => self::feature('Super Admin full access', 'admin.dashboard.view', true, ['ADM-001', 'ADM-003'], 'authorization'),
            'ADM-005' => self::feature('Admin account lifecycle and role assignment', 'admin.admins.manage', true, ['ADM-003', 'ADM-008', 'ADM-039'], 'administration'),
            'ADM-006' => self::feature('Admin session inventory and last activity', 'admin.sessions.view', true, ['ADM-001'], 'security_operations'),
            'ADM-007' => self::feature('Revoke admin sessions', 'admin.sessions.revoke', true, ['ADM-006', 'ADM-008'], 'security_operations'),
            'ADM-008' => self::feature('Administrative audit trail', 'admin.audit.view', true, [], 'audit'),
            'ADM-009' => self::feature('Before/after change snapshots', 'admin.audit.view', true, ['ADM-008'], 'audit'),
            'ADM-010' => self::feature('Operational dashboard and KPIs', 'admin.dashboard.view', true, ['ADM-019', 'ADM-020', 'ADM-042'], 'dashboard'),
            'ADM-011' => self::feature('Catalog management', 'admin.products.manage', true, ['ADM-002', 'ADM-008', 'ADM-015'], 'commerce'),
            'ADM-012' => self::feature('Orders, payments, returns, refunds and coupons', 'admin.orders.manage', true, ['ADM-002', 'ADM-008', 'ADM-039'], 'commerce'),
            'ADM-013' => self::feature('Inventory, shipping and low-stock alerts', 'admin.inventory.manage', true, ['ADM-008', 'ADM-016'], 'commerce'),
            'ADM-014' => self::feature('Storefront content management', 'admin.content.manage', true, ['ADM-015', 'ADM-032', 'ADM-033'], 'content'),
            'ADM-015' => self::feature('Media library and file management', 'admin.media.manage', true, ['ADM-044'], 'content'),
            'ADM-016' => self::feature('In-app admin notification center', 'admin.notifications.view', true, ['ADM-018'], 'communications'),
            'ADM-017' => self::feature('Gmail SMTP delivery', 'admin.notifications.manage', true, ['ADM-016', 'ADM-018'], 'communications', ['gmail_smtp']),
            'ADM-018' => self::feature('Notification policy, deduplication and throttling', 'admin.notifications.manage', true, ['ADM-016', 'ADM-017'], 'communications'),
            'ADM-019' => self::feature('Central error and incident tracking', 'admin.incidents.manage', true, ['ADM-016', 'ADM-044'], 'observability'),
            'ADM-020' => self::feature('System health checks', 'admin.system_health.view', false, ['ADM-019'], 'observability'),
            'ADM-021' => self::feature('Background jobs, webhooks and gateway health', 'admin.system_health.view', true, ['ADM-019'], 'observability', [], true),
            'ADM-022' => self::feature('Admin support ticket workflow', 'admin.support.manage', true, ['ADM-016', 'ADM-008'], 'support'),
            'ADM-023' => self::feature('Product interaction and conversion analytics', 'admin.analytics.view', true, ['ADM-042'], 'analytics'),
            'ADM-024' => self::feature('Product buying guides and educational content', 'admin.content.manage', true, ['ADM-014'], 'storefront_intelligence'),
            'ADM-025' => self::feature('Product comparison contracts', 'admin.products.view', true, ['ADM-011'], 'storefront_intelligence'),
            'ADM-026' => self::feature('Product/accessory compatibility graph', 'admin.catalog.manage', true, ['ADM-011'], 'storefront_intelligence'),
            'ADM-027' => self::feature('Setup builder and compatible component suggestions', 'admin.catalog.manage', true, ['ADM-026', 'ADM-013'], 'storefront_intelligence'),
            'ADM-028' => self::feature('Recently viewed and compared events', 'admin.analytics.view', true, ['ADM-023'], 'storefront_intelligence'),
            'ADM-029' => self::feature('Rule-based recommendations and smart bundles', 'admin.analytics.view', true, ['ADM-023', 'ADM-026', 'ADM-027'], 'storefront_intelligence'),
            'ADM-030' => self::feature('Admin global search and command palette', 'admin.dashboard.view', true, ['ADM-002'], 'productivity'),
            'ADM-031' => self::feature('Bulk operations and saved views', 'admin.bulk_operations.execute', true, ['ADM-008', 'ADM-039'], 'productivity'),
            'ADM-032' => self::feature('Entity activity timeline and content revisions', 'admin.audit.view', true, ['ADM-008', 'ADM-009', 'ADM-033'], 'audit_content'),
            'ADM-033' => self::feature('Draft, preview and scheduled publishing', 'admin.content.publish', true, ['ADM-014', 'ADM-032'], 'content'),
            'ADM-034' => self::feature('Personal UI preferences and global settings', 'admin.settings.view', true, ['ADM-002'], 'settings'),
            'ADM-035' => self::feature('Consistent browser/API authorization errors', 'admin.dashboard.view', false, ['ADM-001', 'ADM-002'], 'shared_ux'),
            'ADM-036' => self::feature('RTL, accessibility, responsive shell and tokens', 'admin.dashboard.view', false, ['ADM-035'], 'shared_ux'),
            'ADM-037' => self::feature('Admin profile identity and safe fallback', 'admin.dashboard.view', true, ['ADM-001'], 'shared_ux'),
            'ADM-038' => self::feature('Security operations and suspicious activity signals', 'admin.security_events.view', true, ['ADM-019', 'ADM-044'], 'security_operations'),
            'ADM-039' => self::feature('Sensitive-operation approval and separation of duties', 'admin.approvals.decide', true, ['ADM-003', 'ADM-008'], 'security_operations'),
            'ADM-040' => self::feature('Backup/recovery awareness', 'admin.system_health.view', false, ['ADM-020'], 'observability', [], true),
            'ADM-041' => self::feature('Admin route discovery hardening', 'admin.dashboard.view', false, ['ADM-001', 'ADM-002'], 'security'),
            'ADM-042' => self::feature('Business reports and analytics', 'admin.reports.view', true, ['ADM-010', 'ADM-023'], 'reports'),
            'ADM-043' => self::feature('Audited admin lifecycle and bulk account changes', 'admin.admins.manage', true, ['ADM-005', 'ADM-008', 'ADM-009', 'ADM-039'], 'administration'),
            'ADM-044' => self::feature('Secrets and sensitive data protection', 'admin.security_events.manage', false, ['ADM-008', 'ADM-019'], 'security'),
        ];
    }

    public static function find(string $featureId): ?array
    {
        return self::all()[strtoupper(trim($featureId))] ?? null;
    }

    public static function allIds(): array
    {
        return array_keys(self::all());
    }

    public static function byArea(string $area): array
    {
        return array_filter(
            self::all(),
            static fn(array $feature): bool => $feature['area'] === $area
        );
    }

    private static function feature(
        string $title,
        string $permission,
        bool $databaseRequired,
        array $dependencies,
        string $area,
        array $externalDependencies = [],
        bool $conditional = false
    ): array {
        return [
            'title' => $title,
            'permission' => $permission,
            'database_required' => $databaseRequired,
            'dependencies' => $dependencies,
            'area' => $area,
            'external_dependencies' => $externalDependencies,
            'conditional' => $conditional,
        ];
    }
}
