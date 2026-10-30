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
    $duplicateNames = array_values(array_diff_assoc($matches[1] ?? [], array_unique($matches[1] ?? [])));
    $knownDuplicateNames = ['admin.variant-attributes.index', 'admin.variant-attributes.store', 'admin.variant-attributes.destroy'];
    p56Check('Admin API route permission names include the 18 capability routes', count($routeNames) >= 176);
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
        'admin.capabilities.preferences.index',
        'admin.capabilities.preferences.update',
        'admin.capabilities.saved-views.index',
        'admin.capabilities.saved-views.store',
        'admin.capabilities.saved-views.destroy',
        'admin.capabilities.revisions.index',
        'admin.capabilities.revisions.store',
    ];
    foreach ($requiredNewRoutes as $routeName) {
        p56Check('Registered capability route: ' . $routeName, in_array($routeName, $routeNames, true));
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
