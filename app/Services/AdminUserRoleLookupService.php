<?php

declare(strict_types=1);

final class AdminUserRoleLookupService
{
    private AdminUserRoleRepository $userRoles;

    public function __construct(
        ?AdminUserRoleRepository $userRoles = null
    ) {
        $this->userRoles =
            $userRoles ??
            new AdminUserRoleRepository();
    }

    public function hasRole(
        int $adminUserId,
        int $roleId
    ): bool {
        if (
            $adminUserId < 1 ||
            $roleId < 1
        ) {
            return false;
        }

        return $this->userRoles->hasRole(
            $adminUserId,
            $roleId
        );
    }

    public function roleIdsForUser(
        int $adminUserId
    ): array {
        if ($adminUserId < 1) {
            return [];
        }

        return $this->userRoles->roleIdsForUser(
            $adminUserId
        );
    }
}
