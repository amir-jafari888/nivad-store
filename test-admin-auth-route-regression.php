<?php

declare(strict_types=1);

/**
 * Admin authentication/authorization route regression — read-only static test.
 *
 * Run from repository root:
 *   php test-admin-auth-route-regression.php
 *
 * This test reads source files only. It does not boot the application,
 * connect to the database, send HTTP requests, or mutate project data.
 */

$root = __DIR__;
$passed = 0;
$failed = 0;

$read = static function (string $relativePath) use ($root): ?string {
    $path = $root . DIRECTORY_SEPARATOR
        . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);

    if (!is_file($path) || !is_readable($path)) {
        return null;
    }

    $content = file_get_contents($path);

    return is_string($content) ? $content : null;
};

$check = static function (string $label, bool $condition) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "[PASS] {$label}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$label}\n";
};

$middleware = $read('app/Middleware/AdminAuthMiddleware.php');
$authorization = $read('app/Services/AuthorizationService.php');
$login = $read('admin/auth/login.php');
$adminIndex = $read('admin/index.php');
$sharedAuth = $read('admin/includes/auth.php');
$logout = $read('admin/logout.php');
$htaccess = $read('.htaccess');

$check('Admin middleware exists', is_string($middleware));
$check('Authorization service exists', is_string($authorization));
$check('Physical admin dashboard entry exists', is_string($adminIndex));
$check('Physical admin login endpoint exists', is_string($login));
$check('Shared admin auth include exists', is_string($sharedAuth));

$check(
    'Middleware redirects browser requests to the real login path',
    is_string($middleware)
        && str_contains($middleware, "url('/admin/auth/login.php')")
        && !str_contains($middleware, "url('/admin/login')")
);

$check(
    'Invalid session returns JSON 401 only for JSON/AJAX requests',
    is_string($middleware)
        && str_contains($middleware, 'if ($expectsJson)')
        && str_contains($middleware, "'success' => false")
        && str_contains($middleware, '],\n                    401')
);

$check(
    'Missing admin identity uses the browser/API response split',
    is_string($middleware)
        && substr_count($middleware, 'if ($expectsJson)') >= 3
        && str_contains($middleware, 'AdminAuth::id()')
);

$check(
    'Permission denial preserves JSON 403 and gives browser requests HTTP 403',
    is_string($middleware)
        && str_contains($middleware, '],\n                    403')
        && str_contains($middleware, "'دسترسی به بخش مدیریت مجاز نیست.'")
        && str_contains($middleware, 'Response::text(')
);

$check(
    'Login form posts to its existing PHP endpoint',
    is_string($login)
        && str_contains($login, "url('/admin/auth/login.php')")
        && str_contains($login, 'Request::isPost()')
);

$csrfPosition = is_string($login) ? strpos($login, 'Csrf::verify(') : false;
$authPosition = is_string($login) ? strpos($login, '$adminAuth->authenticate(') : false;
$check(
    'Login POST validates CSRF before authentication',
    $csrfPosition !== false
        && $authPosition !== false
        && $csrfPosition < $authPosition
);

$check(
    'Successful login redirects to the physical admin entry',
    is_string($login)
        && str_contains($login, "url('/admin')")
        && str_contains($login, 'Response::redirect(')
);

$check(
    'Shared page auth redirects to the same login endpoint',
    is_string($sharedAuth)
        && str_contains($sharedAuth, "url('/admin/auth/login.php')")
);

$check(
    'Logout remains POST-only and CSRF-protected',
    is_string($logout)
        && str_contains($logout, '!Request::isPost()')
        && str_contains($logout, 'Csrf::verify(')
);

$check(
    'Super Admin bypass is server-side and uses the canonical role slug',
    is_string($authorization)
        && str_contains($authorization, 'hasSuperAdminRole')
        && str_contains($authorization, "=== 'super-admin'")
        && str_contains($authorization, "=== 'active'")
);

$check(
    'Inactive or soft-deleted Super Admin roles do not grant the bypass',
    is_string($authorization)
        && str_contains($authorization, '($role[\'status\'] ?? null) === \'active\'')
        && str_contains($authorization, '($role[\'deleted_at\'] ?? null) === null')
);

$check(
    'Invalid admin ID is rejected before the Super Admin bypass',
    is_string($authorization)
        && strpos($authorization, 'if ($adminUserId < 1)') !== false
        && strpos($authorization, 'if ($adminUserId < 1)') < strpos($authorization, '$this->hasSuperAdminRole($adminUserId)')
);

$check(
    'Non-Super-Admin permission checks remain role/permission based',
    is_string($authorization)
        && str_contains($authorization, '$this->permissions->findByName($permissionName)')
        && str_contains($authorization, 'permissionIdsForRole(')
);

$check(
    'Existing logout rewrite contract remains present',
    is_string($htaccess)
        && str_contains($htaccess, 'RewriteEngine On')
        && str_contains($htaccess, 'RewriteRule ^admin/logout/?$ admin/logout.php')
);

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
echo "Mode: read-only static source regression; runtime/browser/DB checks not performed.\n";

exit($failed === 0 ? 0 : 1);
