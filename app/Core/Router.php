<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];
    private string $groupPrefix = '';
    private array $groupMiddleware = [];
    private ?string $groupNamePrefix = null;

    public function get(
        string $pattern,
        mixed $handler,
        array $middleware = [],
        ?string $name = null
    ): self {
        return $this->add(
            'GET',
            $pattern,
            $handler,
            $middleware,
            $name
        );
    }

    public function post(
        string $pattern,
        mixed $handler,
        array $middleware = [],
        ?string $name = null
    ): self {
        return $this->add(
            'POST',
            $pattern,
            $handler,
            $middleware,
            $name
        );
    }

    public function put(
        string $pattern,
        mixed $handler,
        array $middleware = [],
        ?string $name = null
    ): self {
        return $this->add(
            'PUT',
            $pattern,
            $handler,
            $middleware,
            $name
        );
    }

    public function patch(
        string $pattern,
        mixed $handler,
        array $middleware = [],
        ?string $name = null
    ): self {
        return $this->add(
            'PATCH',
            $pattern,
            $handler,
            $middleware,
            $name
        );
    }

    public function delete(
        string $pattern,
        mixed $handler,
        array $middleware = [],
        ?string $name = null
    ): self {
        return $this->add(
            'DELETE',
            $pattern,
            $handler,
            $middleware,
            $name
        );
    }

    public function add(
        string $method,
        string $pattern,
        mixed $handler,
        array $middleware = [],
        ?string $name = null
    ): self {
        $fullPattern = $this->combinePaths(
            $this->groupPrefix,
            $pattern
        );

        $fullMiddleware = array_merge(
            $this->groupMiddleware,
            $middleware
        );

        $fullName = $name;

        if (
            $this->groupNamePrefix !== null &&
            $name !== null
        ) {
            $fullName =
                $this->groupNamePrefix .
                $name;
        }

        $this->routes[] = new Route(
            $method,
            $fullPattern,
            $handler,
            $fullMiddleware,
            $fullName
        );

        return $this;
    }

    public function group(
        string $prefix,
        callable $callback,
        array $middleware = [],
        ?string $namePrefix = null
    ): self {
        $oldPrefix = $this->groupPrefix;
        $oldMiddleware = $this->groupMiddleware;
        $oldNamePrefix = $this->groupNamePrefix;

        $this->groupPrefix = $this->combinePaths(
            $this->groupPrefix,
            $prefix
        );

        $this->groupMiddleware = array_merge(
            $this->groupMiddleware,
            $middleware
        );

        if ($namePrefix !== null) {
            $this->groupNamePrefix =
                ($this->groupNamePrefix ?? '') .
                $namePrefix;
        }

        try {
            $callback($this);
        } finally {
            $this->groupPrefix = $oldPrefix;
            $this->groupMiddleware = $oldMiddleware;
            $this->groupNamePrefix = $oldNamePrefix;
        }

        return $this;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function match(
        string $method,
        string $uri
    ): ?array {
        $method = strtoupper($method);

        $uri = '/' . trim(
            parse_url(
                $uri,
                PHP_URL_PATH
            ) ?: '/',
            '/'
        );

        foreach ($this->routes as $route) {
            if ($route->method() !== $method) {
                continue;
            }

            $parameters = [];

            if (
                $this->matches(
                    $route->pattern(),
                    $uri,
                    $parameters
                )
            ) {
                return [
                    'route' => $route,
                    'parameters' => $parameters,
                ];
            }
        }

        return null;
    }

    public function allowsPath(
        string $uri
    ): bool {
        $uri = '/' . trim(
            parse_url(
                $uri,
                PHP_URL_PATH
            ) ?: '/',
            '/'
        );

        foreach ($this->routes as $route) {
            $parameters = [];

            if (
                $this->matches(
                    $route->pattern(),
                    $uri,
                    $parameters
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function findByName(
        string $name
    ): ?Route {
        foreach ($this->routes as $route) {
            if ($route->name() === $name) {
                return $route;
            }
        }

        return null;
    }

    private function matches(
        string $pattern,
        string $uri,
        array &$parameters
    ): bool {
        $parameterNames = [];

        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static function (
                array $matches
            ) use (&$parameterNames): string {
                $parameterNames[] = $matches[1];

                return '([^/]+)';
            },
            $pattern
        );

        if (!is_string($regex)) {
            return false;
        }

        $regex = '#^' . $regex . '/?$#';

        if (
            preg_match(
                $regex,
                $uri,
                $matches
            ) !== 1
        ) {
            $parameters = [];

            return false;
        }

        array_shift($matches);

        $parameters = [];

        foreach (
            $parameterNames as $index => $name
        ) {
            $parameters[$name] = urldecode(
                $matches[$index] ?? ''
            );
        }

        return true;
    }

    private function combinePaths(
        string $first,
        string $second
    ): string {
        $first = trim($first);
        $second = trim($second);

        if ($first === '' || $first === '/') {
            return '/' . trim($second, '/');
        }

        if ($second === '' || $second === '/') {
            return '/' . trim($first, '/');
        }

        return '/' .
            trim($first, '/') .
            '/' .
            trim($second, '/');
    }
}
