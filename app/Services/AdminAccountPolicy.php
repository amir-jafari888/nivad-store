<?php

declare(strict_types=1);

/**
 * Pure guardrails for admin account lifecycle and role assignment.
 * Counts/role membership must come from a verified DB query at the call site;
 * values supplied by an HTTP request must never be used as evidence.
 */
final class AdminAccountPolicy
{
    public function validateCreate(
        string $displayName,
        string $email,
        string $password,
        array $roleSlugs
    ): array {
        $displayName = trim($displayName);
        $email = strtolower(trim($email));

        if ($displayName === '' || mb_strlen($displayName) > 120) {
            throw new InvalidArgumentException('Admin display name must contain 1–120 characters.');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 254) {
            throw new InvalidArgumentException('Admin email address is invalid.');
        }

        $this->validatePassword($password);

        $normalizedRoles = $this->normalizeRoleSlugs($roleSlugs);
        if ($normalizedRoles === []) {
            throw new InvalidArgumentException('At least one admin role must be assigned.');
        }

        return [
            'display_name' => $displayName,
            'email' => $email,
            'role_slugs' => $normalizedRoles,
        ];
    }

    public function validatePassword(string $password): void
    {
        if (
            strlen($password) < 12 ||
            strlen($password) > 1024 ||
            preg_match('/[a-z]/', $password) !== 1 ||
            preg_match('/[A-Z]/', $password) !== 1 ||
            preg_match('/\d/', $password) !== 1 ||
            preg_match('/[^A-Za-z0-9]/', $password) !== 1
        ) {
            throw new InvalidArgumentException(
                'Admin password must be at least 12 characters and include upper/lowercase letters, a number and a symbol.'
            );
        }
    }

    public function validateLifecycleAction(
        string $action,
        int $actorAdminId,
        int $targetAdminId,
        bool $targetIsSuperAdmin,
        int $activeSuperAdminCount
    ): array {
        $action = strtolower(trim($action));
        $allowedActions = ['disable', 'delete', 'restore', 'enable', 'reset_password'];

        if (!in_array($action, $allowedActions, true)) {
            throw new InvalidArgumentException('Unsupported admin lifecycle action.');
        }

        if ($actorAdminId < 1 || $targetAdminId < 1) {
            throw new InvalidArgumentException('Valid actor and target admin IDs are required.');
        }

        if (in_array($action, ['disable', 'delete'], true) && $actorAdminId === $targetAdminId) {
            throw new InvalidArgumentException('An admin cannot disable or delete their own account.');
        }

        if (
            $targetIsSuperAdmin &&
            in_array($action, ['disable', 'delete'], true) &&
            $activeSuperAdminCount <= 1
        ) {
            throw new InvalidArgumentException('The last active Super Admin cannot be disabled or deleted.');
        }

        return [
            'action' => $action,
            'actor_admin_id' => $actorAdminId,
            'target_admin_id' => $targetAdminId,
            'requires_confirmation' => in_array($action, ['disable', 'delete', 'restore'], true),
            'requires_approval' => in_array($action, ['delete'], true),
            'requires_separate_reviewer' => in_array($action, ['delete'], true),
        ];
    }

    public function validateRoleAssignment(
        int $actorAdminId,
        int $targetAdminId,
        array $requestedRoleSlugs,
        bool $actorIsSuperAdmin,
        bool $targetIsSuperAdmin,
        int $activeSuperAdminCount
    ): array {
        if ($actorAdminId < 1 || $targetAdminId < 1) {
            throw new InvalidArgumentException('Valid actor and target admin IDs are required.');
        }

        $roles = $this->normalizeRoleSlugs($requestedRoleSlugs);
        if ($roles === []) {
            throw new InvalidArgumentException('An admin must retain at least one role.');
        }

        if (in_array('super-admin', $roles, true) && !$actorIsSuperAdmin) {
            throw new InvalidArgumentException('Only a Super Admin may assign the Super Admin role.');
        }

        $retainsSuperAdmin = in_array('super-admin', $roles, true);
        if (
            $targetIsSuperAdmin &&
            !$retainsSuperAdmin &&
            $activeSuperAdminCount <= 1
        ) {
            throw new InvalidArgumentException('The last active Super Admin role cannot be removed.');
        }

        return [
            'actor_admin_id' => $actorAdminId,
            'target_admin_id' => $targetAdminId,
            'role_slugs' => $roles,
            'requires_confirmation' => $actorAdminId !== $targetAdminId || $targetIsSuperAdmin,
            'requires_approval' => $targetIsSuperAdmin || in_array('super-admin', $roles, true),
        ];
    }

    private function normalizeRoleSlugs(array $roleSlugs): array
    {
        $normalized = [];

        foreach ($roleSlugs as $roleSlug) {
            if (!is_string($roleSlug)) {
                throw new InvalidArgumentException('Role slugs must be strings.');
            }

            $roleSlug = strtolower(trim($roleSlug));
            if (preg_match('/^[a-z0-9][a-z0-9-]{0,79}$/', $roleSlug) !== 1) {
                throw new InvalidArgumentException('Role slug is invalid.');
            }

            $normalized[] = $roleSlug;
        }

        return array_values(array_unique($normalized));
    }
}
