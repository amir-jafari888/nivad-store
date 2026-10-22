<?php

declare(strict_types=1);

final class Authorization
{
    public static function can(
        int $adminUserId,
        string $permission
    ): bool {
        if ($adminUserId < 1) {
            return false;
        }

        $permission = trim(
            $permission
        );

        if ($permission === '') {
            return false;
        }

        return (new AuthorizationService())
            ->can(
                $adminUserId,
                $permission
            );
    }

    public static function check(
        int $adminUserId,
        string $permission
    ): AuthorizationResult {
        if ($adminUserId < 1) {
            return new AuthorizationResult(
                false,
                'invalid_admin_user'
            );
        }

        $permission = trim(
            $permission
        );

        if ($permission === '') {
            return new AuthorizationResult(
                false,
                'invalid_permission'
            );
        }

        return (new AuthorizationService())
            ->check(
                $adminUserId,
                $permission
            );
    }

    public static function authorize(
        int $adminUserId,
        string $permission
    ): void {
        if (
            !self::can(
                $adminUserId,
                $permission
            )
        ) {
            throw new AuthorizationException();
        }
    }
}
