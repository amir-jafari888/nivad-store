<?php

declare(strict_types=1);

final class AdminRoleLookupService
{
    private AdminRoleRepository $roles;

    public function __construct(
        ?AdminRoleRepository $roles = null
    ) {
        $this->roles =
            $roles ??
            new AdminRoleRepository();
    }

    public function find(
        int $roleId
    ): ?array {
        if ($roleId < 1) {
            return null;
        }

        return $this->roles->findById(
            $roleId
        );
    }

    public function findActive(
        int $roleId
    ): ?array {
        $role = $this->find($roleId);

        if (
            $role === null ||
            $role['status'] !== 'active' ||
            $role['deleted_at'] !== null
        ) {
            return null;
        }

        return $role;
    }
}
