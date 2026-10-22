<?php

declare(strict_types=1);

final class AdminRoutePermissionMiddleware
implements MiddlewareInterface
{
    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        $routeName =
            $context->routeName();

        if (
            $routeName === null ||
            trim($routeName) === ''
        ) {
            throw new AuthorizationException(
                'مجوز مسیر مدیریت مشخص نشده است.'
            );
        }

        if (
            !str_starts_with(
                $routeName,
                'admin.'
            )
        ) {
            return $next($context);
        }

        $service =
            new AdminAuthService();

        $service->authorize(
            $routeName
        );

        return $next($context);
    }
}
