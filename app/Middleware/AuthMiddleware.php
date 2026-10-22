<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Request.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Core/MiddlewareInterface.php';

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        if (Auth::check()) {
            return $next($context);
        }

        $sessionService =
            new UserSessionService();

        $userId = Auth::id();

        if (
            $userId === null ||
            !$sessionService->isValid(
                $userId,
                Session::id()
            )
        ) {
            Auth::logout();

            Response::json(
                [
                    'success' => false,
                    'message' =>
                    'نشست شما منقضی شده است.',
                ],
                401
            );
        }

        if (Request::isAjax()) {
            Response::json(
                [
                    'success' => false,
                    'message' => 'برای ادامه باید وارد حساب شوید.',
                ],
                401
            );
        }

        Response::status(401);

        exit('برای ادامه باید وارد حساب شوید.');
    }
}
