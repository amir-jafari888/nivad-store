<?php

declare(strict_types=1);

final class AdminPermissionLookupService
{
    private AdminPermissionRepository $permissions;

    public function __construct(
        ?AdminPermissionRepository $permissions = null
    ) {
        $this->permissions =
            $permissions ??
            new AdminPermissionRepository();
    }

    public function findByName(
        string $name
    ): ?array {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        return $this->permissions->findByName(
            $name
        );
    }
}
