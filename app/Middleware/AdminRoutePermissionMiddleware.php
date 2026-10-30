<?php

declare(strict_types=1);

final class AdminRoutePermissionMiddleware implements MiddlewareInterface
{
    public function handle(RequestContext $context, callable $next): mixed
    {
        $routeName = $context->routeName();

        if ($routeName === null || trim($routeName) === '') {
            throw new AuthorizationException(
                'مجوز مسیر مدیریت مشخص نشده است.'
            );
        }

        if (!str_starts_with($routeName, 'admin.')) {
            return $next($context);
        }

        // Always validate the session before reading the admin identity or
        // consulting permission data; never rely on a session ID alone.
        return (new AdminAuthMiddleware())->handle(
            $context,
            function (RequestContext $authenticatedContext) use ($routeName, $next): mixed {
                $adminUserId = AdminAuth::id();
                if (!is_int($adminUserId) || $adminUserId < 1) {
                    throw new AdminAuthenticationException(
                        'برای ادامه باید وارد پنل مدیریت شوید.'
                    );
                }

                $permission = AdminRoutePermissionMap::permissionForRoute($routeName);
                if ($permission === null) {
                    throw new AuthorizationException(
                        'برای این مسیر قرارداد مجوز ثبت نشده است.'
                    );
                }

                if (!(new AuthorizationService())->can($adminUserId, $permission)) {
                    throw new AuthorizationException(
                        'برای اجرای این قابلیت مجوز کافی ندارید.'
                    );
                }

                return $next($authenticatedContext);
            }
        );
    }
}
