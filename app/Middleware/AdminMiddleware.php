<?php

declare(strict_types=1);

final class AdminMiddleware
implements MiddlewareInterface
{
    public function __construct(
        private array $permissions = []
    ) {
        foreach (
            $this->permissions
            as $permission
        ) {
            if (
                !is_string($permission) ||
                trim($permission) === ''
            ) {
                throw new InvalidArgumentException(
                    'Invalid admin permission.'
                );
            }
        }
    }

    public function handle(
        RequestContext $context,
        callable $next
    ): mixed {
        $adminUserId =
            AdminAuth::id();

        if ($adminUserId === null) {
            Response::json(
                [
                    'success' => false,
                    'message' =>
                    'برای ادامه باید وارد پنل مدیریت شوید.',
                ],
                401
            );
        }

        $permissions =
            $this->permissions;

        if ($permissions === []) {
            $permissions = [
                'admin.access',
            ];
        }

        foreach (
            $permissions
            as $permission
        ) {
            $result =
                Authorization::check(
                    $adminUserId,
                    trim($permission)
                );

            if (!$result->allowed()) {
                Response::json(
                    [
                        'success' => false,
                        'message' =>
                        'دسترسی به بخش مدیریت مجاز نیست.',
                        'reason' =>
                        $result->reason(),
                    ],
                    403
                );
            }
        }

        return $next($context);
    }
}
