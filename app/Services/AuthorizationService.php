<?php

declare(strict_types=1);

final class AuthorizationService
{
    private AdminPermissionRepository $permissions;
    private AdminUserRoleRepository $userRoles;
    private AdminRolePermissionRepository $rolePermissions;
    private AdminRoleRepository $roles;

    public function __construct(
        ?AdminPermissionRepository $permissions = null,
        ?AdminUserRoleRepository $userRoles = null,
        ?AdminRolePermissionRepository $rolePermissions = null,
        ?AdminRoleRepository $roles = null
    ) {
        $this->permissions =
            $permissions ??
            new AdminPermissionRepository();

        $this->userRoles =
            $userRoles ??
            new AdminUserRoleRepository();

        $this->rolePermissions =
            $rolePermissions ??
            new AdminRolePermissionRepository();

        $this->roles =
            $roles ??
            new AdminRoleRepository();
    }

    /**
     * The Super Admin bypass is server-side and depends on an active,
     * non-deleted role assigned to the authenticated admin.
     *
     * The canonical role slug is "super-admin". A role name/label alone is
     * never used to grant access.
     */
    private function hasSuperAdminRole(
        int $adminUserId
    ): bool {
        foreach (
            $this->userRoles->roleIdsForUser($adminUserId)
            as $roleId
        ) {
            $role = $this->roles->findById((int) $roleId);

            if (
                $role !== null &&
                ($role['slug'] ?? null) === 'super-admin' &&
                ($role['status'] ?? null) === 'active' &&
                ($role['deleted_at'] ?? null) === null
            ) {
                return true;
            }
        }

        return false;
    }

    public function check(
        int $adminUserId,
        string $permissionName
    ): AuthorizationResult {
        if ($adminUserId < 1) {
            return AuthorizationResult::rejected(
                AuthorizationResult::REASON_INVALID_ADMIN_USER
            );
        }

        $permissionName = trim($permissionName);

        if ($permissionName === '') {
            return AuthorizationResult::rejected(
                AuthorizationResult::REASON_INVALID_PERMISSION
            );
        }

        /*
         * Super Admin has full server-side permission access, including
         * permissions added later. Authentication/session validation remains
         * mandatory in the caller and is not bypassed here.
         */
        if ($this->hasSuperAdminRole($adminUserId)) {
            return AuthorizationResult::granted();
        }

        $permission = $this->permissions->findByName($permissionName);

        if ($permission === null) {
            return AuthorizationResult::rejected(
                AuthorizationResult::REASON_PERMISSION_NOT_FOUND
            );
        }

        $permissionId = (int) $permission['id'];

        $roleIds = $this->userRoles->roleIdsForUser($adminUserId);

        foreach ($roleIds as $roleId) {
            $role = $this->roles->findById((int) $roleId);

            if (
                $role === null ||
                ($role['status'] ?? null) !== 'active' ||
                ($role['deleted_at'] ?? null) !== null
            ) {
                continue;
            }

            $permissionIds = $this->rolePermissions->permissionIdsForRole(
                (int) $roleId
            );

            if (in_array($permissionId, $permissionIds, true)) {
                return AuthorizationResult::granted();
            }
        }

        return AuthorizationResult::rejected(
            AuthorizationResult::REASON_PERMISSION_DENIED
        );
    }

    public function can(
        int $adminUserId,
        string $permissionName
    ): bool {
        return $this
            ->check($adminUserId, $permissionName)
            ->allowed();
    }

    public function hasPermission(
        int $adminUserId,
        string $permissionName
    ): bool {
        return $this->can($adminUserId, $permissionName);
    }

    public function hasAnyPermission(
        int $adminUserId,
        array $permissions
    ): bool {
        if (
            $adminUserId < 1 ||
            $permissions === []
        ) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!is_string($permission)) {
                continue;
            }

            $permission = trim($permission);

            if ($permission === '') {
                continue;
            }

            if ($this->can($adminUserId, $permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(
        int $adminUserId,
        array $permissions
    ): bool {
        if (
            $adminUserId < 1 ||
            $permissions === []
        ) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!is_string($permission)) {
                return false;
            }

            $permission = trim($permission);

            if ($permission === '') {
                return false;
            }

            if (!$this->can($adminUserId, $permission)) {
                return false;
            }
        }

        return true;
    }
}
