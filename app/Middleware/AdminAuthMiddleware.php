<?php

declare(strict_types=1);

final class AdminAuthMiddleware
implements MiddlewareInterface
{
    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        $service =
            new AdminAuthService();

        if (
            !$service->validateCurrentSession()
        ) {
            if (
                Request::acceptsJson() ||
                Request::isAjax() ||
                Request::isJson()
            ) {
                Response::json(
                    [
                        'success' => false,
                        'message' =>
                        'نشست مدیر معتبر نیست.',
                    ],
                    401
                );
            }

            Response::redirect(
                url('/admin/login')
            );
        }

        $adminUserId = AdminAuth::id();

        if ($adminUserId === null) {
            Response::json(
                [
                    'success' => false,
                    'message' =>
                    'هویت مدیر پیدا نشد.',
                ],
                401
            );
        }

        $authorization =
            Authorization::check(
                $adminUserId,
                'admin.access'
            );

        if (!$authorization->allowed()) {
            Response::json(
                [
                    'success' => false,
                    'message' =>
                    'دسترسی به بخش مدیریت مجاز نیست.',
                    'reason' =>
                    $authorization->reason(),
                ],
                403
            );
        }

        return $next($context);
    }
}
