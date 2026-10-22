<?php

declare(strict_types=1);

final class Application
{
    private Router $router;
    private bool $booted = false;

    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        Bootstrap::boot();

        $this->booted = true;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function handle(
        ?RequestContext $context = null
    ): mixed {
        $this->boot();

        $context ??= new RequestContext();

        return RouteDispatcher::dispatch(
            $this->router,
            $context
        );
    }
}
