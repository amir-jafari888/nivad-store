<?php

declare(strict_types=1);

final class RouteDispatcher
{
    public static function dispatch(
        Router $router,
        RequestContext $context
    ): mixed {
        $match = $router->match(
            $context->method(),
            $context->uri()
        );

        if ($match === null) {
            $allowedMethods = self::allowedMethods(
                $router,
                $context->uri()
            );

            if ($allowedMethods !== []) {
                if (!headers_sent()) {
                    header(
                        'Allow: ' .
                            implode(', ', $allowedMethods)
                    );
                }

                throw new MethodNotAllowedException();
            }

            throw new NotFoundException(
                'مسیر موردنظر پیدا نشد.'
            );
        }

        $route = $match['route'];
        $parameters = $match['parameters'];

        $context->setRouteParameters(
            $parameters
        );

        $context->setRouteName(
            $route->name()
        );

        $pipeline = new MiddlewarePipeline();

        foreach ($route->middleware() as $middleware) {
            $pipeline->pipe(
                self::resolveMiddleware($middleware)
            );
        }

        return $pipeline->then(
            $context,
            function () use (
                $route,
                $parameters
            ): mixed {
                return ControllerDispatcher::dispatch(
                    $route->handler(),
                    $parameters
                );
            }
        );
    }

    public static function allowedMethods(
        Router $router,
        string $uri
    ): array {
        $methods = [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'HEAD',
            'OPTIONS',
        ];

        $allowed = [];

        foreach ($methods as $method) {
            if (
                $router->match(
                    $method,
                    $uri
                ) !== null
            ) {
                $allowed[] = $method;
            }
        }

        return $allowed;
    }

    private static function resolveMiddleware(
        mixed $middleware
    ): MiddlewareInterface {
        if (
            is_object($middleware) &&
            $middleware instanceof MiddlewareInterface
        ) {
            return $middleware;
        }

        if (is_string($middleware)) {
            if (!class_exists($middleware)) {
                throw new NotFoundException(
                    'Middleware not found.'
                );
            }

            $instance = new $middleware();

            if (
                !(
                    $instance instanceof
                    MiddlewareInterface
                )
            ) {
                throw new InvalidArgumentException(
                    'Middleware must implement MiddlewareInterface.'
                );
            }

            return $instance;
        }

        throw new InvalidArgumentException(
            'Invalid middleware definition.'
        );
    }
}
