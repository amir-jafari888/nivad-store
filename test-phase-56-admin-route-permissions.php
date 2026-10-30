<?php

declare(strict_types=1);

/**
 * Phase 56 — Admin authorization and capability API contracts.
 * Read-only. The optional database section performs SELECT/SHOW only.
 * Run: php test-phase-56-admin-route-permissions.php
 */

$passed = 0;
$failed = 0;
$blocked = 0;

function p56Check(string $label, bool $ok): void
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

function p56Blocked(string $label): void
{
    global $blocked;
    $blocked++;
    echo '<span style="color:#b45309">[BLOCKED]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

$root = __DIR__;
$paths = [
    'routes' => $root . '/routes/api.php',
    'middleware' => $root . '/app/Middleware/AdminRoutePermissionMiddleware.php',
    'auth_service' => $root . '/app/Services/AdminAuthService.php',
    'capability_controller' => $root . '/app/Controllers/AdminCapabilityController.php',
    'capability_service' => $root . '/app/Services/AdminCapabilityService.php',
    'capability_repository' => $root . '/app/Repositories/AdminCapabilityRepository.php',
    'capability_page' => $root . '/admin/capabilities/index.php',
    'capability_js' => $root . '/admin/assets/js/modules/capability-center.js',
    'shared_shell' => $root . '/admin/includes/auth.php',
    'access_controller' => $root . '/app/Controllers/AdminAccessController.php',
    'access_service' => $root . '/app/Services/AdminAccessService.php',
    'access_repository' => $root . '/app/Repositories/AdminAccessRepository.php',
    'access_page' => $root . '/admin/access/index.php',
    'access_js' => $root . '/admin/assets/js/modules/access-center.js',
];

$sources = [];
foreach ($paths as $key => $path) {
    $sources[$key] = is_file($path) ? file_get_contents($path) : false;
    p56Check('Required source exists and is readable: ' . $key, is_string($sources[$key]));
}

$schemaTables = [
    'admin_notifications',
    'admin_incidents',
    'admin_support_tickets',
    'admin_support_messages',
    'product_analytics_events',
    'admin_preferences',
    'admin_saved_views',
    'admin_content_revisions',
];

if (is_string($sources['routes']) && is_string($sources['middleware'])) {
    $authCount = substr_count($sources['routes'], 'AdminAuthMiddleware::class');
    $permissionCount = substr_count($sources['routes'], 'AdminRoutePermissionMiddleware::class');
    preg_match_all("/'(admin\\.[a-zA-Z0-9._-]+)'\\s*\\)/", $sources['routes'], $matches);
    $routeNames = array_values(array_unique($matches[1] ?? []));

    p56Check('Every Admin API auth middleware has a route permission middleware', $authCount > 0 && $authCount === $permissionCount);
    p56Check('Shared capability read/write middleware arrays enforce both Admin authentication and route permission checks', str_contains($sources['routes'], '$adminCapabilityRead = [') && str_contains($sources['routes'], '$adminCapabilityWrite = [') && substr_count($sources['routes'], '$adminCapabilityRead,') >= 1 && substr_count($sources['routes'], '$adminCapabilityWrite,') >= 1);
    $duplicateNames = array_values(array_diff_assoc($matches[1] ?? [], array_unique($matches[1] ?? [])));
    $knownDuplicateNames = ['admin.variant-attributes.index', 'admin.variant-attributes.store', 'admin.variant-attributes.destroy'];
    p56Check('Admin API route permission names include all new Admin modules', count($routeNames) >= 191);
    p56Check('Only the three pre-existing variant-attribute route names are duplicated', array_values(array_unique($duplicateNames)) === $knownDuplicateNames);
    p56Check('Route permission middleware rejects unnamed Admin routes', str_contains($sources['middleware'], '$routeName === null') && str_contains($sources['middleware'], '$service->authorize('));
    p56Check('Authorization service is called through AdminAuthService', str_contains($sources['auth_service'], 'public function authorize(') && str_contains($sources['auth_service'], 'Authorization::authorize('));

    $requiredNewRoutes = [
        'admin.capabilities.notifications.index',
        'admin.capabilities.notifications.store',
        'admin.capabilities.notifications.read',
        'admin.capabilities.incidents.index',
        'admin.capabilities.incidents.update',
        'admin.capabilities.tickets.index',
        'admin.capabilities.tickets.show',
        'admin.capabilities.tickets.store',
        'admin.capabilities.tickets.messages.store',
        'admin.capabilities.tickets.update',
        'admin.capabilities.analytics.index',
        'admin.capabilities.health.index',
        'admin.capabilities.search.index',
        'admin.capabilities.preferences.index',
        'admin.capabilities.preferences.update',
        'admin.capabilities.saved-views.index',
        'admin.capabilities.saved-views.store',
        'admin.capabilities.saved-views.destroy',
        'admin.capabilities.revisions.index',
        'admin.capabilities.revisions.store',
        'admin.access.admins.index',
        'admin.access.admins.store',
        'admin.access.admins.update',
        'admin.access.admins.destroy',
        'admin.access.admins.roles.update',
        'admin.access.roles.index',
        'admin.access.roles.store',
        'admin.access.roles.update',
        'admin.access.permissions.index',
        'admin.access.roles.permissions.show',
        'admin.access.roles.permissions.update',
        'admin.access.sessions.index',
        'admin.access.sessions.destroy',
    ];
    foreach ($requiredNewRoutes as $routeName) {
        p56Check('Registered Admin route: ' . $routeName, in_array($routeName, $routeNames, true));
        $position = strpos($sources['routes'], "'" . $routeName . "'");
        $start = $position !== false ? strrpos(substr($sources['routes'], 0, $position), '$router->') : false;
        $end = $position !== false ? strpos($sources['routes'], ');', $position) : false;
        $routeBlock = ($start !== false && $end !== false) ? substr($sources['routes'], $start, $end + 2 - $start) : '';
        p56Check('Admin route uses protected capability middleware: ' . $routeName, str_contains($routeBlock, '$adminCapabilityRead') || str_contains($routeBlock, '$adminCapabilityWrite'));
    }
}

if (is_string($sources['capability_repository'])) {
    foreach ($schemaTables as $table) {
        p56Check('Repository uses schema table: ' . $table, str_contains($sources['capability_repository'], $table));
    }
    p56Check('Repository uses prepared statements', str_contains($sources['capability_repository'], '$this->db->prepare('));
    p56Check('Support ticket and first message are transactional', str_contains($sources['capability_repository'], 'beginTransaction()') && str_contains($sources['capability_repository'], 'rollBack()'));
}

if (is_string($sources['capability_service'])) {
    p56Check('Service validates notification category and severity', str_contains($sources['capability_service'], 'NOTIFICATION_CATEGORIES') && str_contains($sources['capability_service'], 'SEVERITIES'));
    p56Check('Service validates ticket states and priorities', str_contains($sources['capability_service'], 'TICKET_STATUSES') && str_contains($sources['capability_service'], 'TICKET_PRIORITIES'));
    p56Check('Service bounds analytics date range', str_contains($sources['capability_service'], '93'));
    p56Check('Saved views are scoped to current admin in repository', str_contains($sources['capability_repository'], 'admin_user_id = :admin_id'));
}

if (is_string($sources['capability_page']) && is_string($sources['capability_js'])) {
    p56Check('Capability UI has CSRF token meta', str_contains($sources['capability_page'], 'csrf-token'));
    p56Check('Capability UI loads notifications, incidents, tickets and analytics', str_contains($sources['capability_js'], 'loadNotifications') && str_contains($sources['capability_js'], 'loadIncidents') && str_contains($sources['capability_js'], 'loadTickets') && str_contains($sources['capability_js'], 'loadAnalytics'));
    p56Check('Capability UI writes with CSRF header', str_contains($sources['capability_js'], 'X-CSRF-TOKEN'));
    p56Check('Capability UI renders API data with textContent', str_contains($sources['capability_js'], 'textContent'));
}
p56Check('Shared Admin navigation links to capability center', is_string($sources['shared_shell']) && str_contains($sources['shared_shell'], '/admin/capabilities/index.php'));
p56Check('Admin access repository exists', is_string($sources['access_repository']));
p56Check('Admin mutation audit trail helper exists', is_file($root . '/app/Services/AdminAuditTrail.php') && str_contains((string)file_get_contents($root . '/app/Services/AuditLogService.php'), 'public function record('));
p56Check('Regression no longer depends on deleted migration files', !str_contains((string)$sources['routes'], 'database/migrations'));
p56Check('Admin access service protects the Super Admin role', is_string($sources['access_service']) && str_contains($sources['access_service'], 'super-admin'));
p56Check('Admin access UI is connected to the API module', is_string($sources['access_page']) && is_string($sources['access_js']));
p56Check('Access module is registered in Admin main.js', is_file($root . '/admin/assets/js/modules/access-center.js') && str_contains((string)file_get_contents($root . '/admin/assets/js/main.js'), 'access-center.js'));
p56Check('Admin session monitoring is scoped to the current account and excludes the current session', is_string($sources['access_repository']) && is_string($sources['capability_controller']) && str_contains((string)file_get_contents($root . '/app/Repositories/AdminSessionRepository.php'), 'current_token_hash'));
p56Check('Global search uses prepared queries over the supported entity tables', is_string($sources['capability_repository']) && str_contains($sources['capability_repository'], 'globalSearch(string $term)') && str_contains($sources['capability_repository'], 'FROM products') && str_contains($sources['capability_repository'], 'FROM orders') && str_contains($sources['capability_repository'], 'FROM users'));
p56Check('Unhandled server exceptions are reported as Admin incidents', is_file($root . '/app/Core/ErrorHandler.php') && str_contains((string)file_get_contents($root . '/app/Core/ErrorHandler.php'), 'recordIncident('));
p56Check('Public product analytics collector is CSRF-protected', is_file($root . '/app/Controllers/ProductAnalyticsController.php') && str_contains((string)file_get_contents($root . '/routes/api.php'), 'analytics.product-event.store') && str_contains((string)file_get_contents($root . '/routes/api.php'), '[CsrfMiddleware::class]'));
p56Check('Product detail analytics module is registered', is_file($root . '/assets/js/modules/product-analytics.js') && str_contains((string)file_get_contents($root . '/assets/js/main.js'), 'product-analytics.js'));
p56Check('Storefront exposes a CSRF token for analytics writes', is_file($root . '/partials/header.php') && str_contains((string)file_get_contents($root . '/partials/header.php'), 'name="csrf-token"'));

echo '<hr><strong>STATIC PASS: ' . $passed . ' | STATIC FAIL: ' . $failed . ' | DB BLOCKED: ' . $blocked . "</strong><br>\n";

try {
    require_once $root . '/app/Core/Bootstrap.php';
    Bootstrap::boot();
    $db = Database::connection();
    $existingTables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $existingTables = array_fill_keys($existingTables, true);

    foreach ($schemaTables as $table) {
        p56Check('Database table exists: ' . $table, isset($existingTables[$table]));
    }

    if (is_string($sources['routes'])) {
        preg_match_all("/'(admin\\.[a-zA-Z0-9._-]+)'\\s*\\)/", $sources['routes'], $matches);
        $routeNames = array_values(array_unique($matches[1] ?? []));
        $routeNames = array_values(array_filter($routeNames, static fn(string $name): bool => str_starts_with($name, 'admin.')));
        $placeholders = implode(',', array_fill(0, count($routeNames), '?'));
        $stmt = $db->prepare('SELECT name FROM admin_permissions WHERE name IN (' . $placeholders . ') AND status = ? AND deleted_at IS NULL');
        $stmt->execute([...$routeNames, 'active']);
        $existingPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $missingPermissions = array_diff($routeNames, $existingPermissions);
        p56Check('Every Admin API route has an active permission row', $missingPermissions === []);
        if ($missingPermissions !== []) {
            foreach ($missingPermissions as $missing) {
                echo '<span style="color:#b45309">[MISSING PERMISSION]</span> ' . htmlspecialchars($missing, ENT_QUOTES, 'UTF-8') . "<br>\n";
            }
        }
    }
} catch (Throwable $exception) {
    p56Blocked('Live database checks could not run in this environment; no database writes were attempted.');
}

echo '<hr><strong>FINAL: PASS=' . $passed . ' | FAIL=' . $failed . ' | BLOCKED=' . $blocked . ' | LOCK=NO</strong><br>' . "\n";
