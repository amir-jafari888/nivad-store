<?php

declare(strict_types=1);

/**
 * Explicit mapping between existing named admin API routes and the canonical
 * Admin Feature Register. Unknown routes intentionally return null (fail
 * closed); do not infer a feature from a partial or ambiguous route name.
 *
 * Apply this map to routes only after the target DB's role/permission matrix
 * has been reviewed and the permission seed is approved.
 */
final class AdminRouteFeatureMap
{
    private const PREFIXES = [
        'admin.admins.' => 'ADM-005',
        'admin.roles.' => 'ADM-003',
        'admin.permissions.' => 'ADM-003',
        'admin.sessions.' => 'ADM-006',
        'admin.audit-logs.' => 'ADM-008',
        'admin.dashboard' => 'ADM-010',
        'admin.reports.' => 'ADM-042',
        'admin.notifications.' => 'ADM-016',
        'admin.inventory' => 'ADM-013',
        'admin.shipping.' => 'ADM-013',
        'admin.orders.' => 'ADM-012',
        'admin.payments.' => 'ADM-012',
        'admin.returns.' => 'ADM-012',
        'admin.refunds.' => 'ADM-012',
        'admin.coupons.' => 'ADM-012',
        'admin.attributes.' => 'ADM-011',
        'admin.attribute-values.' => 'ADM-011',
        'admin.variants.' => 'ADM-011',
        'admin.variant-attributes.' => 'ADM-011',
        'admin.products.' => 'ADM-011',
        'admin.product-specifications.' => 'ADM-011',
        'admin.product-media.' => 'ADM-015',
        'admin.variant-media.' => 'ADM-015',
        'admin.categories.' => 'ADM-011',
        'admin.brands.' => 'ADM-011',
        'admin.product-types.' => 'ADM-011',
        'admin.specifications.' => 'ADM-011',
        'admin.collections.' => 'ADM-014',
        'admin.sliders.' => 'ADM-014',
        'admin.discovery.' => 'ADM-014',
        'admin.compatibility.' => 'ADM-026',
        'admin.setups.' => 'ADM-027',
        'admin.reviews.' => 'ADM-014',
        'admin.questions.' => 'ADM-014',
        'admin.answers.' => 'ADM-014',
        'admin.content.' => 'ADM-014',
        'admin.media.' => 'ADM-015',
        'admin.support.' => 'ADM-022',
        'admin.approvals.' => 'ADM-039',
        'admin.bulk.' => 'ADM-031',
        'admin.saved-views.' => 'ADM-031',
        'admin.analytics.' => 'ADM-023',
        'admin.health.' => 'ADM-020',
        'admin.incidents.' => 'ADM-019',
        'admin.security-events.' => 'ADM-038',
        'admin.settings.' => 'ADM-034',
        'admin.auth.' => 'ADM-001',
    ];

    public static function featureForRoute(string $routeName): ?string
    {
        $routeName = trim($routeName);

        if ($routeName === 'admin' || $routeName === 'admin.dashboard') {
            return 'ADM-010';
        }

        if ($routeName === 'admin.login' || $routeName === 'admin.auth.login') {
            return 'ADM-001';
        }

        foreach (self::PREFIXES as $prefix => $featureId) {
            if (str_starts_with($routeName, $prefix)) {
                return $featureId;
            }
        }

        return null;
    }

    public static function mappings(): array
    {
        return self::PREFIXES;
    }

    public static function isMapped(string $routeName): bool
    {
        return self::featureForRoute($routeName) !== null;
    }
}
