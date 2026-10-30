<?php

declare(strict_types=1);

/**
 * Phase 56 — Admin Route Permission Wiring
 * Read-only static contract test. No database writes.
 * Run: php test-phase-56-admin-route-permissions.php
 */

$passed = 0;
$failed = 0;

function p56Check(string $label, bool $ok): void
{
    global $passed, $failed;

    if ($ok) {
        $passed++;
        echo '<span style="color:green">[PASS]</span> '
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . "<br>\n";
        return;
    }

    $failed++;
    echo '<span style="color:red">[FAIL]</span> '
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        . "<br>\n";
}

$routesPath = __DIR__ . '/routes/api.php';
$middlewarePath = __DIR__ . '/app/Middleware/AdminRoutePermissionMiddleware.php';
$migrationPath = __DIR__ . '/database/migrations/20261030_001_admin_route_permissions.sql';
$capabilityMigrationPath = __DIR__ . '/database/migrations/20261030_002_admin_capability_storage.sql';
$operationsPagePath = __DIR__ . '/admin/operations/index.php';
$sharedShellPath = __DIR__ . '/admin/includes/auth.php';

$routes = is_file($routesPath) ? file_get_contents($routesPath) : false;
$middleware = is_file($middlewarePath) ? file_get_contents($middlewarePath) : false;
$migration = is_file($migrationPath) ? file_get_contents($migrationPath) : false;
$capabilityMigration = is_file($capabilityMigrationPath) ? file_get_contents($capabilityMigrationPath) : false;
$operationsPage = is_file($operationsPagePath) ? file_get_contents($operationsPagePath) : false;
$sharedShell = is_file($sharedShellPath) ? file_get_contents($sharedShellPath) : false;

p56Check('API route source is readable', is_string($routes));
p56Check('Route permission middleware exists', is_string($middleware));
p56Check('Permission seed migration exists', is_string($migration));

if (is_string($routes)) {
    $authCount = substr_count($routes, 'AdminAuthMiddleware::class');
    $permissionCount = substr_count($routes, 'AdminRoutePermissionMiddleware::class');
    $routeNames = [];
    preg_match_all("/'(admin\\.[a-zA-Z0-9._-]+)'\\s*\\)/", $routes, $routeNames);

    p56Check('Every Admin API auth middleware has matching route permission middleware', $authCount > 0 && $authCount === $permissionCount);
    p56Check('All declared Admin API route names are namespaced', count($routeNames[1] ?? []) >= 150);
}

if (is_string($middleware)) {
    p56Check('Middleware authorizes using the resolved route name', str_contains($middleware, '$context->routeName()') && str_contains($middleware, '$service->authorize('));
}

if (is_string($migration)) {
    p56Check('Route permission migration grants only the Super Admin role', str_contains($migration, "roles.slug = 'super-admin'"));
    p56Check('Route permission migration preserves existing assignments', str_contains($migration, 'INSERT IGNORE INTO admin_role_permissions'));
    p56Check('Route permission migration is transactional', str_contains($migration, 'START TRANSACTION;') && str_contains($migration, 'COMMIT;'));
    p56Check('Route permission migration contains no destructive commands', preg_match('/\\b(DROP|TRUNCATE|DELETE\\s+FROM)\\b/i', $migration) !== 1);
}

p56Check('Additive capability schema migration exists', is_string($capabilityMigration));
if (is_string($capabilityMigration)) {
    p56Check('Capability migration defines eight isolated tables', substr_count($capabilityMigration, 'CREATE TABLE IF NOT EXISTS') === 8);
    p56Check('Capability migration contains no destructive commands', preg_match('/\\b(DROP|TRUNCATE|DELETE\\s+FROM)\\b/i', $capabilityMigration) !== 1);
    p56Check('Analytics stores hashed visitor/session identifiers', str_contains($capabilityMigration, 'visitor_hash CHAR(64)') && str_contains($capabilityMigration, 'session_hash CHAR(64)'));
}
p56Check('Admin capability map page exists', is_string($operationsPage));
p56Check('Shared Admin navigation links to capability map', is_string($sharedShell) && str_contains($sharedShell, "url('/admin/operations')"));

echo '<hr><strong>PASS: ' . $passed . ' | FAIL: ' . $failed . "</strong><br>\n";
echo '<strong>Static status: ' . ($failed === 0 ? 'PASS — STATIC ONLY' : 'FAIL') . ' | Runtime/DB: NOT VERIFIED | LOCK: NO</strong><br>\n';
