<?php

declare(strict_types=1);

/**
 * Explicit server-side permission contracts for every named Admin API route.
 * Unknown routes intentionally fail closed. Route names must be reviewed
 * individually; no wildcard or inferred CRUD permission is permitted here.
 */
final class AdminRoutePermissionMap
{
    private const EXACT = [
        'admin.notifications.create' => 'admin.notifications.manage',

        'admin.reviews.index' => 'admin.reviews.manage',
        'admin.reviews.show' => 'admin.reviews.manage',
        'admin.reviews.status' => 'admin.reviews.manage',
        'admin.reviews.note' => 'admin.reviews.manage',

        'admin.questions.index' => 'admin.questions.manage',
        'admin.questions.show' => 'admin.questions.manage',
        'admin.questions.status' => 'admin.questions.manage',
        'admin.questions.note' => 'admin.questions.manage',
        'admin.questions.answers.store' => 'admin.questions.manage',
        'admin.answers.status' => 'admin.questions.manage',

        'admin.users.index' => 'admin.users.view',
        'admin.users.show' => 'admin.users.view',
        'admin.users.status' => 'admin.users.manage',

        // Admin access management routes (Phase 2).
        'admin.access.sessions.index' => 'admin.sessions.view',
        'admin.access.sessions.history' => 'admin.sessions.view',
        'admin.access.sessions.destroy' => 'admin.sessions.revoke',
        'admin.access.admins.index' => 'admin.admins.view',
        'admin.access.admins.effective-permissions' => 'admin.permissions.manage',
        'admin.access.admins.store' => 'admin.admins.manage',
        'admin.access.admins.update' => 'admin.admins.manage',
        'admin.access.admins.destroy' => 'admin.admins.manage',
        'admin.access.admins.roles.update' => 'admin.permissions.manage',
        'admin.access.roles.index' => 'admin.roles.view',
        'admin.access.roles.store' => 'admin.roles.manage',
        'admin.access.roles.update' => 'admin.roles.manage',
        'admin.access.permissions.index' => 'admin.permissions.manage',
        'admin.access.roles.permissions.show' => 'admin.permissions.manage',
        'admin.access.roles.permissions.update' => 'admin.permissions.manage',

        // Admin capability routes (Phase 2 route-permission completion).
        'admin.capabilities.notifications.index' => 'admin.notifications.view',
        'admin.capabilities.notifications.store' => 'admin.notifications.manage',
        'admin.capabilities.notifications.read' => 'admin.notifications.manage',
        'admin.capabilities.notifications.acknowledge' => 'admin.notifications.manage',
        'admin.capabilities.notifications.read-all' => 'admin.notifications.manage',
        'admin.capabilities.notification-preferences.index' => 'admin.notifications.view',
        'admin.capabilities.notification-preferences.update' => 'admin.notifications.manage',
        'admin.capabilities.notification-policies.index' => 'admin.notifications.view',
        'admin.capabilities.notification-policies.update' => 'admin.notifications.manage',
        'admin.capabilities.incidents.index' => 'admin.incidents.manage',
        'admin.capabilities.incidents.analysis' => 'admin.incidents.manage',
        'admin.capabilities.incidents.timeline' => 'admin.incidents.manage',
        'admin.capabilities.jobs.health' => 'admin.system_health.view',
        'admin.capabilities.incidents.update' => 'admin.incidents.manage',
        'admin.capabilities.tickets.index' => 'admin.support.view',
        'admin.capabilities.tickets.show' => 'admin.support.view',
        'admin.capabilities.tickets.store' => 'admin.support.manage',
        'admin.capabilities.tickets.messages.store' => 'admin.support.manage',
        'admin.capabilities.tickets.update' => 'admin.support.manage',
        'admin.capabilities.support.metrics' => 'admin.support.view',
        'admin.capabilities.support.reports' => 'admin.support.view',
        'admin.capabilities.support.sla.index' => 'admin.support.view',
        'admin.capabilities.support.sla.update' => 'admin.support.manage',
        'admin.capabilities.analytics.index' => 'admin.analytics.view',
        'admin.capabilities.health.index' => 'admin.system_health.view',
        'admin.capabilities.search.index' => 'admin.search.use',
        'admin.capabilities.preferences.index' => 'admin.settings.view',
        'admin.capabilities.preferences.update' => 'admin.settings.manage',
        'admin.capabilities.saved-views.index' => 'admin.saved_views.manage',
        'admin.capabilities.saved-views.store' => 'admin.saved_views.manage',
        'admin.capabilities.saved-views.destroy' => 'admin.saved_views.manage',
        'admin.capabilities.revisions.index' => 'admin.content.manage',
        'admin.capabilities.revisions.store' => 'admin.content.manage',

        // Phase 5 — explicit read, edit and publish permissions.
        'admin.content.pages.index' => 'admin.content.view',
        'admin.content.sections.index' => 'admin.content.view',
        'admin.content.sections.revisions.index' => 'admin.content.view',
        'admin.content.sections.store' => 'admin.content.manage',
        'admin.content.sections.update' => 'admin.content.manage',
        'admin.content.sections.destroy' => 'admin.content.manage',
        'admin.content.sections.restore' => 'admin.content.manage',
        'admin.content.sections.reorder' => 'admin.content.manage',
        'admin.content.sections.publish' => 'admin.content.publish',
        'admin.content.sections.schedule' => 'admin.content.publish',
        'admin.content.sections.revisions.restore' => 'admin.content.manage',
        'admin.media.index' => 'admin.media.view',
        'admin.media.file' => 'admin.media.view',
        'admin.media.store' => 'admin.media.manage',
        'admin.media.update' => 'admin.media.manage',
        'admin.media.destroy' => 'admin.media.manage',

    ];

    public static function permissionForRoute(string $routeName): ?string
    {
        $routeName = trim($routeName);

        if (isset(self::EXACT[$routeName])) {
            return self::EXACT[$routeName];
        }

        $featureId = AdminRouteFeatureMap::featureForRoute($routeName);
        if ($featureId === null) {
            return null;
        }

        $feature = AdminFeatureRegistry::find($featureId);

        return is_string($feature['permission'] ?? null)
            ? $feature['permission']
            : null;
    }

    public static function exactMappings(): array
    {
        return self::EXACT;
    }

    public static function allMappedPermissions(): array
    {
        return array_values(array_unique(self::EXACT));
    }
}
