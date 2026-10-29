<?php

declare(strict_types=1);

final class AdminAuthMiddleware
implements MiddlewareInterface
{
    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        $service = new AdminAuthService();

        $expectsJson =
            Request::acceptsJson() ||
            Request::isAjax() ||
            Request::isJson();

        if (!$service->validateCurrentSession()) {
            if ($expectsJson) {
                Response::json(
                    [
                        'success' => false,
                        'message' => 'نشست مدیر معتبر نیست.',
                    ],
                    401
                );
            }

            Response::redirect(
                url('/admin/auth/login.php')
            );
        }

        $adminUserId = AdminAuth::id();

        if ($adminUserId === null) {
            if ($expectsJson) {
                Response::json(
                    [
                        'success' => false,
                        'message' => 'هویت مدیر پیدا نشد.',
                    ],
                    401
                );
            }

            Response::redirect(
                url('/admin/auth/login.php')
            );
        }

        $authorization = Authorization::check(
            $adminUserId,
            'admin.access'
        );

        if (!$authorization->allowed()) {
            if ($expectsJson) {
                Response::json(
                    [
                        'success' => false,
                        'message' => 'دسترسی به بخش مدیریت مجاز نیست.',
                        'reason' => $authorization->reason(),
                    ],
                    403
                );
            }

            /*
             * Do not redirect to another protected admin route, and do not
             * return JSON to a normal browser document navigation.
             * A dedicated localized HTML 403 view can replace this response
             * once the shared error-page contract is implemented.
             */
            Response::text(
                'دسترسی به بخش مدیریت مجاز نیست.',
                403
            );
        }

        return $next($context);
    }
}
