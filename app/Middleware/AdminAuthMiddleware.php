<?php

declare(strict_types=1);

final class AdminAuthMiddleware implements MiddlewareInterface
{
    public function handle(RequestContext $context, callable $next): mixed
    {
        AdminRequestContext::publishResponseHeader();

        $service = new AdminAuthService();
        $expectsJson = AdminHttpErrorResponder::expectsJson();

        if (!$service->validateCurrentSession()) {
            if ($expectsJson) {
                Response::json([
                    'success' => false,
                    'message' => 'نشست مدیر معتبر نیست.',
                    'request_id' => AdminRequestContext::id(),
                ], 401);
            }

            Response::redirect(url('/admin/auth/login.php'));
        }

        $adminUserId = AdminAuth::id();

        if (!is_int($adminUserId) || $adminUserId < 1) {
            if ($expectsJson) {
                Response::json([
                    'success' => false,
                    'message' => 'هویت مدیر پیدا نشد.',
                    'request_id' => AdminRequestContext::id(),
                ], 401);
            }

            Response::redirect(url('/admin/auth/login.php'));
        }

        $authorization = Authorization::check($adminUserId, 'admin.access');

        if (!$authorization->allowed()) {
            AdminHttpErrorResponder::respond(
                403,
                'دسترسی به بخش مدیریت مجاز نیست.',
                ['reason' => $authorization->reason()]
            );
        }

        return $next($context);
    }
}
