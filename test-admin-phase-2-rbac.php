<?php

declare(strict_types=1);

/**
 * Admin Panel Phase 2 — RBAC and access-security source contracts.
 * Static/source-level only. This test does not connect to MySQL or mutate data.
 * Run: php test-admin-phase-2-rbac.php
 */

$passed = 0;
$failed = 0;

function phase2Check(string $label, bool $ok): void
{
    global $passed, $failed;
    if ($ok) {
        $passed++;
        echo '<span style="color:green">[PASS]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
        return;
    }
    $failed++;
    echo '<span style="color:red">[FAIL]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

$root = __DIR__;
$read = static function (string $path) use ($root): string|false {
    $file = $root . '/' . ltrim($path, '/');
    return is_file($file) ? file_get_contents($file) : false;
};

$routes = $read('routes/api.php');
$permissionMap = $read('app/Core/AdminRoutePermissionMap.php');
$featureMap = $read('app/Core/AdminRouteFeatureMap.php');
$registry = $read('app/Core/AdminFeatureRegistry.php');
$authorization = $read('app/Services/AuthorizationService.php');
$permissionRepository = $read('app/Repositories/AdminPermissionRepository.php');
$roleRepository = $read('app/Repositories/AdminRoleRepository.php');
$userRoleRepository = $read('app/Repositories/AdminUserRoleRepository.php');
$rolePermissionRepository = $read('app/Repositories/AdminRolePermissionRepository.php');
$middleware = $read('app/Middleware/AdminRoutePermissionMiddleware.php');
$authMiddleware = $read('app/Middleware/AdminAuthMiddleware.php');
$accessRepository = $read('app/Repositories/AdminAccessRepository.php');
$accessService = $read('app/Services/AdminAccessService.php');
$accessController = $read('app/Controllers/AdminAccessController.php');
$reauth = $read('app/Services/AdminReauthenticationService.php');
$accessJs = $read('admin/assets/js/modules/access-center.js');
$sessionService = $read('app/Services/AdminSessionService.php');
$sessionRepository = $read('app/Repositories/AdminSessionRepository.php');
$authService = $read('app/Services/AdminAuthService.php');
$authAudit = $read('app/Services/AdminAuthenticationAuditService.php');
$deniedPage = $read('admin/access-denied.php');
$catalog = $read('app/Core/AdminPermissionCatalog.php');
$seed = $read('database/seeds/20261029_admin_permission_catalog.sql');

foreach ([
    'routes' => $routes,
    'permission map' => $permissionMap,
    'feature map' => $featureMap,
    'feature registry' => $registry,
    'authorization service' => $authorization,
    'route permission middleware' => $middleware,
    'access repository' => $accessRepository,
    'access controller' => $accessController,
    'reauthentication service' => $reauth,
    'access UI' => $accessJs,
] as $name => $source) {
    phase2Check('Required source exists: ' . $name, is_string($source));
}

if (
    is_string($routes) &&
    is_string($permissionMap) &&
    is_string($featureMap) &&
    is_string($registry)
) {
    require_once $root . '/app/Core/Bootstrap.php';
    Bootstrap::boot();

    preg_match_all("/'(admin\\.[a-zA-Z0-9._-]+)'\\s*\\)/", $routes, $matches);
    $routeNames = array_values(array_unique($matches[1] ?? []));
    $unmapped = [];
    $missingCatalogPermissions = [];

    foreach ($routeNames as $routeName) {
        $permission = AdminRoutePermissionMap::permissionForRoute($routeName);
        if (!is_string($permission) || trim($permission) === '') {
            $unmapped[] = $routeName;
            continue;
        }
        if (!in_array($permission, AdminPermissionCatalog::allNames(), true)) {
            $missingCatalogPermissions[] = $routeName . ' => ' . $permission;
        }
    }

    phase2Check('Admin API route inventory includes at least 190 unique named routes', count($routeNames) >= 190);
    phase2Check('Every named Admin API route resolves to an explicit permission contract', $unmapped === []);
    phase2Check('Every mapped route permission exists in the canonical permission catalog', $missingCatalogPermissions === []);
    phase2Check('Access and capability route families have explicit mappings', str_contains($permissionMap, "'admin.access.admins.index'") && str_contains($permissionMap, "'admin.capabilities.notifications.index'"));
    phase2Check('The effective-permission report route is registered and mapped', str_contains($routes, 'admin.access.admins.effective-permissions') && str_contains($permissionMap, "'admin.access.admins.effective-permissions'"));
    phase2Check('Unknown Admin routes fail closed', str_contains($middleware, 'permissionForRoute($routeName)') && str_contains($middleware, 'if ($permission === null)'));
    phase2Check('Every Admin API route includes both authentication and route-permission middleware', substr_count($routes, 'AdminAuthMiddleware::class') > 0 && substr_count($routes, 'AdminAuthMiddleware::class') === substr_count($routes, 'AdminRoutePermissionMiddleware::class'));

    if ($unmapped !== []) {
        foreach ($unmapped as $route) {
            echo '<span style="color:red">[UNMAPPED]</span> ' . htmlspecialchars($route, ENT_QUOTES, 'UTF-8') . "<br>\n";
        }
    }
    if ($missingCatalogPermissions !== []) {
        foreach ($missingCatalogPermissions as $route) {
            echo '<span style="color:red">[MISSING CATALOG PERMISSION]</span> ' . htmlspecialchars($route, ENT_QUOTES, 'UTF-8') . "<br>\n";
        }
    }
}

phase2Check('Base admin access permission is defined and seeded', is_string($catalog) && str_contains($catalog, 'ADMIN_ACCESS') && is_string($seed) && str_contains($seed, "SELECT 'admin.access' AS name"));
phase2Check('Super Admin bypass requires the canonical active, non-deleted role slug', is_string($authorization) && str_contains($authorization, "=== 'super-admin'") && str_contains($authorization, "=== 'active'") && str_contains($authorization, "'deleted_at'] ?? null) === null"));
phase2Check('Authorization rejects invalid admin IDs and unknown permission definitions', is_string($authorization) && str_contains($authorization, 'REASON_INVALID_ADMIN_USER') && str_contains($authorization, 'REASON_PERMISSION_NOT_FOUND'));
phase2Check('Permission lookup excludes inactive and deleted permission definitions', is_string($permissionRepository) && str_contains($permissionRepository, "status = :status") && str_contains($permissionRepository, 'deleted_at IS NULL'));
phase2Check('Role lookup excludes inactive and deleted roles', is_string($roleRepository) && str_contains($roleRepository, 'status = :status') && str_contains($roleRepository, 'deleted_at IS NULL'));
phase2Check('User-role and role-permission lookups exclude inactive/deleted records', is_string($userRoleRepository) && str_contains($userRoleRepository, 'deleted_at IS NULL') && is_string($rolePermissionRepository) && str_contains($rolePermissionRepository, 'deleted_at IS NULL'));

phase2Check('Re-authentication verifies the current active admin password server-side', is_string($reauth) && str_contains($reauth, 'findActiveById') && str_contains($reauth, 'Password::verify'));
phase2Check('Re-authentication does not issue a reusable session step-up token', is_string($reauth) && !str_contains($reauth, '$_SESSION') && !str_contains($reauth, 'Session::set'));
phase2Check('All Admin access mutations require re-authentication', is_string($accessController) && substr_count($accessController, '$this->requireReauthentication(') >= 8);
phase2Check('Access UI uses a masked password input for re-authentication', is_string($accessJs) && str_contains($accessJs, 'input.type = "password"') && str_contains($accessJs, 'reauth_password'));
phase2Check('Re-authentication audit events never include the supplied password field', is_string($reauth) && !str_contains($reauth, "'reauth_password' =>") && !str_contains($reauth, "'password' =>"));

phase2Check('Effective permission report uses active role and permission assignments', is_string($accessRepository) && str_contains($accessRepository, 'effectivePermissionsForAdmin') && str_contains($accessRepository, 'r.status = "active"') && str_contains($accessRepository, 'p.status = "active"'));
phase2Check('Effective permission report includes the Super Admin full catalog contract', is_string($accessRepository) && str_contains($accessRepository, '"Super Admin" AS granted_by') && str_contains($accessRepository, 'adminHasRoleSlug($adminId, \'super-admin\')'));
phase2Check('Effective permission report is exposed through controller and UI', is_string($accessController) && str_contains($accessController, 'public function effectivePermissions') && is_string($accessJs) && str_contains($accessJs, 'نمایش دسترسی مؤثر'));
phase2Check('Account and role security snapshots are recorded around mutations', is_string($accessController) && str_contains($accessController, 'adminSecuritySnapshot') && str_contains($accessController, 'roleSecuritySnapshot') && str_contains($accessController, 'AdminAuditTrail::record'));
phase2Check('Admin session inventory and cross-admin revocation contracts exist', is_string($sessionService) && str_contains($sessionService, 'activeForAllAdmins') && str_contains($sessionService, 'revokeByIdForAnyAdmin'));
phase2Check('Authentication audit covers success, failure, logout and invalid session events', is_string($authAudit) && str_contains($authAudit, 'login_succeeded') && str_contains($authAudit, 'login_failed') && str_contains($authAudit, 'admin.auth.logout') && str_contains($authAudit, 'session_invalid'));
phase2Check('Logout invokes the authentication audit before clearing the session', is_string($authService) && str_contains($authService, '$this->audit->logout($adminUserId)'));
phase2Check('Admin session history endpoint and UI are connected', is_string($accessController) && str_contains($accessController, 'public function sessionsHistory') && is_string($routes) && str_contains($routes, 'admin.access.sessions.history') && is_string($accessJs) && str_contains($accessJs, 'loadSessionHistory'));
phase2Check('Session history exposes status transitions without username/email fields', is_string($sessionRepository) && str_contains($sessionRepository, 'session_status') && str_contains($sessionRepository, 'AS admin_name') && !str_contains($sessionRepository, 'a.username') && !str_contains($sessionRepository, 'a.email'));
phase2Check('Dedicated authenticated 403 page exists and avoids exposing account identifiers', is_string($deniedPage) && str_contains($deniedPage, '403') && !str_contains($deniedPage, "\$currentAdmin['email']") && !str_contains($deniedPage, "\$currentAdmin['username']"));

echo '<hr><strong>PHASE 2 STATIC RESULT: PASS=' . $passed . ' | FAIL=' . $failed . ' | LOCK=NO</strong><br>' . "\n";
exit($failed > 0 ? 1 : 0);
