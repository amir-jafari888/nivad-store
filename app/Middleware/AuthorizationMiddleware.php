<?php

declare(strict_types=1);

final class AuthorizationMiddleware
implements MiddlewareInterface
{
    private string $permission;

    public function __construct(
        string $permission
    ) {
        $permission =
            trim($permission);

        if ($permission === '') {
            throw new InvalidArgumentException(
                'Permission cannot be empty.'
            );
        }

        $this->permission =
            $permission;
    }

    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        $adminUserId =
            AdminAuth::id();

        if ($adminUserId === null) {
            throw new AdminAuthenticationException(
                'برای ادامه باید وارد پنل مدیریت شوید.'
            );
        }

        Authorization::authorize(
            $adminUserId,
            $this->permission
        );

        return $next($context);
    }
}
