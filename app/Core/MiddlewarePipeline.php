<?php

declare(strict_types=1);

final class MiddlewarePipeline
{
    private array $middlewares = [];

    public function pipe(
        mixed $middleware
    ): self {
        if (
            !is_object($middleware) ||
            !(
                $middleware instanceof MiddlewareInterface
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid middleware.'
            );
        }

        $this->middlewares[] = $middleware;

        return $this;
    }

    public function then(
        RequestContext $context,
        callable $destination
    ): mixed {
        $pipeline = $destination;

        foreach (
            array_reverse($this->middlewares)
            as $middleware
        ) {
            $next = $pipeline;

            $pipeline = function () use (
                $middleware,
                $context,
                $next
            ): mixed {
                return $middleware->handle(
                    $context,
                    $next
                );
            };
        }

        return $pipeline();
    }

    public function count(): int
    {
        return count($this->middlewares);
    }
}
