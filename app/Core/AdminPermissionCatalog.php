<?php

declare(strict_types=1);

/**
 * Canonical admin permission vocabulary.
 *
 * Permission names are server-side identifiers stored in admin_permissions.name.
 * Registering a permission here does not grant it to any role. Role assignments
 * must be managed explicitly, audited, and tested against the target database.
 */
final class AdminPermissionCatalog
{
    public const ADMIN_ACCESS = 'admin.access';
    public const DASHBOARD_VIEW = 'admin.dashboard.view';
    public const ADMINS_VIEW = 'admin.admins.view';
    public const ADMINS_MANAGE = 'admin.admins.manage';
    public const ROLES_VIEW = 'admin.roles.view';
    public const ROLES_MANAGE = 'admin.roles.manage';
    public const PERMISSIONS_MANAGE = 'admin.permissions.manage';
    public const SESSIONS_VIEW = 'admin.sessions.view';
    public const SESSIONS_REVOKE = 'admin.sessions.revoke';
    public const AUDIT_VIEW = 'admin.audit.view';
    public const AUDIT_EXPORT = 'admin.audit.export';
    public const NOTIFICATIONS_VIEW = 'admin.notifications.view';
    public const NOTIFICATIONS_MANAGE = 'admin.notifications.manage';
    public const SYSTEM_HEALTH_VIEW = 'admin.system_health.view';
    public const INCIDENTS_MANAGE = 'admin.incidents.manage';
    public const SETTINGS_VIEW = 'admin.settings.view';
    public const SETTINGS_MANAGE = 'admin.settings.manage';
    public const REPORTS_VIEW = 'admin.reports.view';
    public const ANALYTICS_VIEW = 'admin.analytics.view';
    public const PRODUCTS_VIEW = 'admin.products.view';
    public const PRODUCTS_MANAGE = 'admin.products.manage';
    public const CATALOG_MANAGE = 'admin.catalog.manage';
    public const ORDERS_VIEW = 'admin.orders.view';
    public const ORDERS_MANAGE = 'admin.orders.manage';
    public const PAYMENTS_VIEW = 'admin.payments.view';
    public const REFUNDS_MANAGE = 'admin.refunds.manage';
    public const INVENTORY_VIEW = 'admin.inventory.view';
    public const INVENTORY_MANAGE = 'admin.inventory.manage';
    public const SHIPPING_MANAGE = 'admin.shipping.manage';
    public const USERS_VIEW = 'admin.users.view';
    public const USERS_MANAGE = 'admin.users.manage';
    public const REVIEWS_MANAGE = 'admin.reviews.manage';
    public const QUESTIONS_MANAGE = 'admin.questions.manage';
    public const CONTENT_VIEW = 'admin.content.view';
    public const CONTENT_MANAGE = 'admin.content.manage';
    public const CONTENT_PUBLISH = 'admin.content.publish';
    public const MEDIA_VIEW = 'admin.media.view';
    public const MEDIA_MANAGE = 'admin.media.manage';
    public const SUPPORT_VIEW = 'admin.support.view';
    public const SUPPORT_MANAGE = 'admin.support.manage';
    public const APPROVALS_VIEW = 'admin.approvals.view';
    public const APPROVALS_DECIDE = 'admin.approvals.decide';
    public const BULK_OPERATIONS = 'admin.bulk_operations.execute';
    public const SAVED_VIEWS_MANAGE = 'admin.saved_views.manage';
    public const SECURITY_EVENTS_VIEW = 'admin.security_events.view';
    public const SECURITY_EVENTS_MANAGE = 'admin.security_events.manage';

    /** @return array<string, array{module:string, action:string, description:string}> */
    public static function definitions(): array
    {
        $definitions = [
            self::ADMIN_ACCESS => ['admin_core', 'access', 'Access the authenticated admin application shell'],
            self::DASHBOARD_VIEW => ['dashboard', 'view', 'View operational dashboard'],
            self::ADMINS_VIEW => ['admins', 'view', 'View admin accounts'],
            self::ADMINS_MANAGE => ['admins', 'manage', 'Create, update, disable and restore admin accounts'],
            self::ROLES_VIEW => ['roles', 'view', 'View admin roles'],
            self::ROLES_MANAGE => ['roles', 'manage', 'Create and update admin roles'],
            self::PERMISSIONS_MANAGE => ['permissions', 'manage', 'Manage role permission assignments'],
            self::SESSIONS_VIEW => ['sessions', 'view', 'View admin session inventory'],
            self::SESSIONS_REVOKE => ['sessions', 'revoke', 'Revoke admin sessions'],
            self::AUDIT_VIEW => ['audit', 'view', 'View administrative audit trail'],
            self::AUDIT_EXPORT => ['audit', 'export', 'Export audit records'],
            self::NOTIFICATIONS_VIEW => ['notifications', 'view', 'View admin notifications'],
            self::NOTIFICATIONS_MANAGE => ['notifications', 'manage', 'Create and acknowledge admin notifications'],
            self::SYSTEM_HEALTH_VIEW => ['system_health', 'view', 'View system health indicators'],
            self::INCIDENTS_MANAGE => ['incidents', 'manage', 'Manage system incidents'],
            self::SETTINGS_VIEW => ['settings', 'view', 'View admin settings'],
            self::SETTINGS_MANAGE => ['settings', 'manage', 'Update admin settings'],
            self::REPORTS_VIEW => ['reports', 'view', 'View business reports'],
            self::ANALYTICS_VIEW => ['analytics', 'view', 'View product and operational analytics'],
            self::PRODUCTS_VIEW => ['products', 'view', 'View products'],
            self::PRODUCTS_MANAGE => ['products', 'manage', 'Create and update products'],
            self::CATALOG_MANAGE => ['catalog', 'manage', 'Manage categories, brands, attributes and specifications'],
            self::ORDERS_VIEW => ['orders', 'view', 'View orders'],
            self::ORDERS_MANAGE => ['orders', 'manage', 'Perform permitted order state transitions'],
            self::PAYMENTS_VIEW => ['payments', 'view', 'View payment records'],
            self::REFUNDS_MANAGE => ['refunds', 'manage', 'Manage refund requests'],
            self::INVENTORY_VIEW => ['inventory', 'view', 'View inventory and movements'],
            self::INVENTORY_MANAGE => ['inventory', 'manage', 'Adjust inventory and reservations'],
            self::SHIPPING_MANAGE => ['shipping', 'manage', 'Manage shipping and shipments'],
            self::USERS_VIEW => ['users', 'view', 'View customer accounts'],
            self::USERS_MANAGE => ['users', 'manage', 'Manage customer accounts within policy'],
            self::REVIEWS_MANAGE => ['reviews', 'manage', 'Moderate reviews'],
            self::QUESTIONS_MANAGE => ['questions', 'manage', 'Moderate product questions and answers'],
            self::CONTENT_VIEW => ['content', 'view', 'View storefront content'],
            self::CONTENT_MANAGE => ['content', 'manage', 'Edit storefront content'],
            self::CONTENT_PUBLISH => ['content', 'publish', 'Publish or schedule storefront content'],
            self::MEDIA_VIEW => ['media', 'view', 'View media library'],
            self::MEDIA_MANAGE => ['media', 'manage', 'Upload and manage media assets'],
            self::SUPPORT_VIEW => ['support', 'view', 'View support tickets'],
            self::SUPPORT_MANAGE => ['support', 'manage', 'Assign and update support tickets'],
            self::APPROVALS_VIEW => ['approvals', 'view', 'View sensitive-operation approval requests'],
            self::APPROVALS_DECIDE => ['approvals', 'decide', 'Approve or reject sensitive operations'],
            self::BULK_OPERATIONS => ['bulk_operations', 'execute', 'Execute authorized bulk operations'],
            self::SAVED_VIEWS_MANAGE => ['saved_views', 'manage', 'Manage saved filters and views'],
            self::SECURITY_EVENTS_VIEW => ['security_events', 'view', 'View security events'],
            self::SECURITY_EVENTS_MANAGE => ['security_events', 'manage', 'Respond to security events'],
        ];

        $result = [];
        foreach ($definitions as $name => [$module, $action, $description]) {
            $result[$name] = [
                'module' => $module,
                'action' => $action,
                'description' => $description,
            ];
        }

        return $result;
    }

    public static function allNames(): array
    {
        return array_keys(self::definitions());
    }
}
