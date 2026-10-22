<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Request.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Core/Csrf.php';
require_once __DIR__ . '/../Core/Session.php';
require_once __DIR__ . '/../Core/Security.php';
require_once __DIR__ . '/../Core/MiddlewareInterface.php';

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        if (
            Request::isGet() ||
            Request::isHead() ||
            Request::isOptions()
        ) {
            return $next($context);
        }

        $token =
            Request::post('csrf_token');

        if (
            !is_string($token) ||
            $token === ''
        ) {
            $token =
                $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (!Csrf::verify($token)) {
            Response::json(
                [
                    'success' => false,
                    'message' => 'درخواست نامعتبر است.',
                ],
                419
            );
        }

        return $next($context);
    }
}
