<?php

declare(strict_types=1);

final class AdminPermissionMiddleware
implements MiddlewareInterface
{
    public function __construct(
        private string $permission
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
        $service =
            new AdminAuthService();

        $service->authorize(
            $this->permission
        );

        return $next($context);
    }
}
