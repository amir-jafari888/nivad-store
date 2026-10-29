<?php

declare(strict_types=1);

/**
 * Central permission-aware feature access. Unknown feature IDs fail closed.
 *
 * This service is a shared authorization primitive; routes and mutation
 * services must still call it at their own trust boundary.
 */
final class AdminFeatureAccessService
{
    private AuthorizationService $authorization;

    public function __construct(?AuthorizationService $authorization = null)
    {
        $this->authorization = $authorization ?? new AuthorizationService();
    }

    public function canAccess(int $adminUserId, string $featureId): bool
    {
        if ($adminUserId < 1) {
            return false;
        }

        $feature = AdminFeatureRegistry::find($featureId);
        if ($feature === null) {
            return false;
        }

        return $this->authorization->can(
            $adminUserId,
            $feature['permission']
        );
    }

    public function canAccessAny(int $adminUserId, array $featureIds): bool
    {
        if ($adminUserId < 1 || $featureIds === []) {
            return false;
        }

        foreach ($featureIds as $featureId) {
            if (!is_string($featureId)) {
                continue;
            }

            if ($this->canAccess($adminUserId, $featureId)) {
                return true;
            }
        }

        return false;
    }

    public function canAccessAll(int $adminUserId, array $featureIds): bool
    {
        if ($adminUserId < 1 || $featureIds === []) {
            return false;
        }

        foreach ($featureIds as $featureId) {
            if (!is_string($featureId) || !$this->canAccess($adminUserId, $featureId)) {
                return false;
            }
        }

        return true;
    }

    public function requiredPermission(string $featureId): ?string
    {
        $feature = AdminFeatureRegistry::find($featureId);

        return $feature['permission'] ?? null;
    }
}
