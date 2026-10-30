<?php

declare(strict_types=1);

/**
 * Explicit permission contracts for admin routes that do not belong to one of
 * the 44 canonical feature buckets. Exact names are used to avoid accidentally
 * granting a broader permission to a read-only route.
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
        return array_values(array_unique(array_filter(array_map(
            static fn(string $route): ?string => self::permissionForRoute($route),
            array_keys(self::EXACT)
        ))));
    }
}
