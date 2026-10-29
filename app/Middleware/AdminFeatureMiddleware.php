<?php

declare(strict_types=1);

/**
 * Feature-level authorization middleware.
 *
 * Add it after route mapping has been audited. It deliberately authenticates
 * the admin session first and then applies the feature's canonical permission.
 * It must not be attached to public routes.
 */
final class AdminFeatureMiddleware implements MiddlewareInterface
{
    private string $featureId;

    public function __construct(string $featureId)
    {
        $featureId = strtoupper(trim($featureId));

        if (AdminFeatureRegistry::find($featureId) === null) {
            throw new InvalidArgumentException('Unknown Admin Panel feature ID.');
        }

        $this->featureId = $featureId;
    }

    public function handle(RequestContext $context, callable $next): mixed
    {
        $authentication = new AdminAuthMiddleware();

        return $authentication->handle(
            $context,
            function (RequestContext $authenticatedContext) use ($next): mixed {
                $adminUserId = AdminAuth::id();

                if (!is_int($adminUserId) || $adminUserId < 1) {
                    $this->deny('admin_identity_missing', 401);
                }

                $access = new AdminFeatureAccessService();

                if (!$access->canAccess($adminUserId, $this->featureId)) {
                    $this->deny('feature_permission_denied', 403);
                }

                return $next($authenticatedContext);
            }
        );
    }

    private function deny(string $reason, int $status): never
    {
        AdminHttpErrorResponder::respond(
            $status,
            $status === 401
                ? 'هویت مدیر معتبر نیست.'
                : 'دسترسی به این قابلیت مجاز نیست.',
            [
                'reason' => $reason,
                'feature' => $this->featureId,
            ]
        );
    }
}
