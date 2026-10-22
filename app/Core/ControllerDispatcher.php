<?php

declare(strict_types=1);

final class ControllerDispatcher
{
    public static function dispatch(
        mixed $handler,
        array $parameters = []
    ): mixed {
        if (
            is_callable($handler) &&
            !is_array($handler)
        ) {
            return $handler(
                $parameters
            );
        }

        [
            $controller,
            $method
        ] = ControllerResolver::resolve(
            $handler
        );

        return $controller->{$method}(
            $parameters
        );
    }
}
