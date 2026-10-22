<?php

declare(strict_types=1);

interface MiddlewareInterface
{
    public function handle(
        RequestContext $context,
        callable $next
    ): mixed;
}
