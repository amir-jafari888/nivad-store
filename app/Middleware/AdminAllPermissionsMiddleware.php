<?php

declare(strict_types=1);

final class AdminAllPermissionsMiddleware
implements MiddlewareInterface
{
    public function __construct(
        private array $permissions
    ) {
        if ($this->permissions === []) {
            throw new InvalidArgumentException(
                'Permissions cannot be empty.'
            );
        }
    }

    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        $service =
            new AdminAuthService();

        if (
            !$service->canAll(
                $this->permissions
            )
        ) {
            throw new AuthorizationException();
        }

        return $next($context);
    }
}
