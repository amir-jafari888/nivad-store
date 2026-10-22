<?php

declare(strict_types=1);

final class Route
{
    private string $method;
    private string $pattern;
    private mixed $handler;
    private array $middleware;
    private ?string $name;

    public function __construct(
        string $method,
        string $pattern,
        mixed $handler,
        array $middleware = [],
        ?string $name = null
    ) {
        $method = strtoupper(trim($method));
        $pattern = trim($pattern);

        if ($method === '') {
            throw new InvalidArgumentException(
                'Route method cannot be empty.'
            );
        }

        if ($pattern === '') {
            throw new InvalidArgumentException(
                'Route pattern cannot be empty.'
            );
        }

        if (
            !is_callable($handler) &&
            !(
                is_array($handler) &&
                count($handler) === 2 &&
                is_string($handler[0]) &&
                is_string($handler[1])
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid route handler.'
            );
        }

        if ($name !== null) {
            $name = trim($name);

            if ($name === '') {
                $name = null;
            }
        }

        $this->method = $method;
        $this->pattern = '/' . trim($pattern, '/');
        $this->handler = $handler;
        $this->middleware = $middleware;
        $this->name = $name;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function handler(): mixed
    {
        return $this->handler;
    }

    public function middleware(): array
    {
        return $this->middleware;
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
