<?php

declare(strict_types=1);

final class ControllerResolver
{
    public static function resolve(
        mixed $handler
    ): array {
        if (
            !is_array($handler) ||
            count($handler) !== 2 ||
            !is_string($handler[0]) ||
            !is_string($handler[1])
        ) {
            throw new InvalidArgumentException(
                'Invalid controller handler.'
            );
        }

        $controllerClass = $handler[0];
        $method = $handler[1];

        if (!class_exists($controllerClass)) {
            throw new NotFoundException(
                'Controller not found.'
            );
        }

        if (!is_subclass_of(
            $controllerClass,
            Controller::class
        )) {
            throw new InvalidArgumentException(
                'Handler class must extend Controller.'
            );
        }

        if (!method_exists(
            $controllerClass,
            $method
        )) {
            throw new NotFoundException(
                'Controller action not found.'
            );
        }

        $reflection = new ReflectionMethod(
            $controllerClass,
            $method
        );

        if (!$reflection->isPublic()) {
            throw new AuthorizationException(
                'Controller action is not accessible.'
            );
        }

        return [
            new $controllerClass(),
            $method,
        ];
    }
}
