<?php

declare(strict_types=1);

final class RequestContext
{
    private string $method;
    private string $uri;
    private string $path;
    private array $routeParameters = [];
    private ?string $routeName = null;

    public function __construct(
        ?string $method = null,
        ?string $uri = null
    ) {
        $this->method = strtoupper(
            $method ?? Request::method()
        );

        $this->uri = $uri ?? Request::uri();

        $this->path = '/' . trim(
            parse_url(
                $this->uri,
                PHP_URL_PATH
            ) ?: '/',
            '/'
        );

        if ($this->path === '') {
            $this->path = '/';
        }
    }

    public function setRouteName(?string $name): void
    {
        $name = $name !== null
            ? trim($name)
            : null;

        $this->routeName = $name !== ''
            ? $name
            : null;
    }

    public function routeName(): ?string
    {
        return $this->routeName;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function setRouteParameters(
        array $parameters
    ): void {
        $this->routeParameters = $parameters;
    }

    public function routeParameter(
        string $name,
        mixed $default = null
    ): mixed {
        return $this->routeParameters[$name] ?? $default;
    }

    public function routeParameters(): array
    {
        return $this->routeParameters;
    }
}
