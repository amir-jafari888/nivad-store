<?php

/**
 * Non-DB validation and normalization for administrator account lifecycle input.
 */
final class AdminAccountPolicy
{
    public static function validate(array $input): array
    {
        $email = trim((string)($input['email'] ?? ''));
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('A valid administrator email is required.');
        }

        $name = trim((string)($input['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 120) {
            throw new InvalidArgumentException('Administrator name is invalid.');
        }

        $role = trim((string)($input['role'] ?? ''));
        if ($role === '' || !preg_match('/^[a-z0-9][a-z0-9._-]{1,79}$/', $role)) {
            throw new InvalidArgumentException('Administrator role is invalid.');
        }

        $active = array_key_exists('active', $input) ? (bool)$input['active'] : true;
        return [
            'email' => strtolower($email),
            'name' => $name,
            'role' => $role,
            'active' => $active,
        ];
    }

    public static function assertCanChangeLifecycle(int $actorId, int $targetId, string $action): void
    {
        if ($actorId <= 0 || $targetId <= 0) {
            throw new InvalidArgumentException('Administrator identifiers must be positive integers.');
        }
        $allowed = ['create', 'update', 'enable', 'disable', 'soft_delete', 'restore', 'role_change'];
        if (!in_array($action, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported administrator lifecycle action.');
        }
        if ($actorId === $targetId && in_array($action, ['disable', 'soft_delete'], true)) {
            throw new RuntimeException('An administrator cannot disable or delete the current account.');
        }
    }
}
