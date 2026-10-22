<?php

declare(strict_types=1);

final class AdminRolePermissionLookupService
{
    private AdminRolePermissionRepository $rolePermissions;

    public function __construct(
        ?AdminRolePermissionRepository $rolePermissions = null
    ) {
        $this->rolePermissions =
            $rolePermissions ??
            new AdminRolePermissionRepository();
    }

    public function hasPermission(
        int $roleId,
        int $permissionId
    ): bool {
        if (
            $roleId < 1 ||
            $permissionId < 1
        ) {
            return false;
        }

        return $this->rolePermissions->hasPermission(
            $roleId,
            $permissionId
        );
    }

    public function permissionIdsForRole(
        int $roleId
    ): array {
        if ($roleId < 1) {
            return [];
        }

        return $this->rolePermissions
            ->permissionIdsForRole(
                $roleId
            );
    }
}
