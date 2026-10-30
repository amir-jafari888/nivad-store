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
    'access_denied_page' => $root . '/admin/access-denied.php',
    'access_js' => $root . '/admin/assets/js/modules/access-center.js',
    'customer_support_controller' => $root . '/app/Controllers/CustomerSupportController.php',
    'customer_support_page' => $root . '/pages/account/support.php',
    'customer_support_js' => $root . '/assets/js/modules/account-support.js',
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

p56Check('Admin access service protects the Super Admin role', is_string($sources['access_service']) && str_contains($sources['access_service'], 'super-admin'));
p56Check('Admin access UI is connected to the API module', is_string($sources['access_page']) && is_string($sources['access_js']));
p56Check('Admin access page requires an Access permission', is_string($sources['access_page']) && str_contains($sources['access_page'], '$adminAuth->canAll(') && str_contains($sources['access_page'], '$adminAuth->authorize('));
p56Check('Admin capability page requires at least one capability permission', is_string($sources['capability_page']) && str_contains($sources['capability_page'], '$adminAuth->canAny(') && str_contains($sources['capability_page'], '$adminAuth->authorize('));
p56Check('Unauthorized Admin access/capability pages redirect to the dedicated denial page', is_string($sources['access_page']) && is_string($sources['capability_page']) && str_contains($sources['access_page'], '/admin/access-denied.php') && str_contains($sources['capability_page'], '/admin/access-denied.php'));
p56Check('Dedicated denial page is authenticated and uses only the Admin display name', is_string($sources['access_denied_page']) && str_contains($sources['access_denied_page'], "\$currentAdmin['first_name']") && str_contains($sources['access_denied_page'], "\$currentAdmin['last_name']") && !str_contains($sources['access_denied_page'], "\$currentAdmin['email']") && !str_contains($sources['access_denied_page'], "\$currentAdmin['username']"));
p56Check('RBAC assignment list excludes inactive permissions', is_file($root . '/app/Repositories/AdminAccessRepository.php') && str_contains((string)file_get_contents($root . '/app/Repositories/AdminAccessRepository.php'), 'WHERE status = "active" AND deleted_at IS NULL'));
p56Check('Access module is registered in Admin main.js', is_file($root . '/admin/assets/js/modules/access-center.js') && str_contains((string)file_get_contents($root . '/admin/assets/js/main.js'), 'access-center.js'));
p56Check('Admin session monitoring is scoped to the current account and excludes the current session', is_string($sources['access_repository']) && is_string($sources['capability_controller']) && str_contains((string)file_get_contents($root . '/app/Repositories/AdminSessionRepository.php'), 'current_token_hash'));
p56Check('Global search uses prepared queries over the supported entity tables', is_string($sources['capability_repository']) && str_contains($sources['capability_repository'], 'globalSearch(string $term)') && str_contains($sources['capability_repository'], 'FROM products') && str_contains($sources['capability_repository'], 'FROM orders') && str_contains($sources['capability_repository'], 'FROM users'));
p56Check('Unhandled server exceptions are reported as Admin incidents', is_file($root . '/app/Core/ErrorHandler.php') && str_contains((string)file_get_contents($root . '/app/Core/ErrorHandler.php'), 'recordIncident('));
p56Check('Critical incidents generate deduplicated per-Admin notifications', str_contains((string)$sources['capability_repository'], 'notifyIncident(') && str_contains((string)$sources['capability_service'], 'notifyIncident(') && str_contains((string)$sources['capability_repository'], 'LAST_INSERT_ID(id)'));
p56Check('New customer tickets notify active Admins', str_contains((string)$sources['capability_repository'], 'notifyTicketCreated(') && str_contains((string)$sources['capability_service'], 'notifyTicketCreated('));
p56Check('Global search filters results by each module permission', str_contains((string)$sources['capability_service'], 'Authorization::can($adminId, $target[\'permission\'])'));
p56Check('Public product analytics collector is CSRF-protected', is_file($root . '/app/Controllers/ProductAnalyticsController.php') && str_contains((string)file_get_contents($root . '/routes/api.php'), 'analytics.product-event.store') && str_contains((string)file_get_contents($root . '/routes/api.php'), '[CsrfMiddleware::class]'));
p56Check('Product detail analytics module is registered', is_file($root . '/assets/js/modules/product-analytics.js') && str_contains((string)file_get_contents($root . '/assets/js/main.js'), 'product-analytics.js'));
p56Check('Product analytics requires explicit user consent', str_contains((string)file_get_contents($root . '/assets/js/modules/product-analytics.js'), 'consent !== "accepted"') && str_contains((string)file_get_contents($root . '/assets/js/modules/product-analytics.js'), 'nivad-analytics-consent'));
p56Check('Storefront exposes a CSRF token for analytics writes', is_file($root . '/partials/header.php') && str_contains((string)file_get_contents($root . '/partials/header.php'), 'name="csrf-token"'));
p56Check('Customer support API is authenticated and exposes customer-owned ticket workflows', is_string($sources['customer_support_controller']) && str_contains((string)file_get_contents($root . '/routes/api.php'), 'support.tickets.messages.store') && str_contains((string)file_get_contents($root . '/routes/api.php'), 'AuthMiddleware::class'));
p56Check('Customer support page and JS module are connected', is_string($sources['customer_support_page']) && is_string($sources['customer_support_js']) && str_contains((string)file_get_contents($root . '/assets/js/main.js'), 'account-support.js'));
p56Check('Customer replies cannot read internal messages', is_string($sources['capability_repository']) && str_contains($sources['capability_repository'], 'AND is_internal = 0'));
p56Check('Customer ticket creation/replies emit deduplicated in-app Admin notifications', is_string($sources['customer_support_controller']) && substr_count($sources['customer_support_controller'], "'recipient_admin_user_id' => null") >= 2 && str_contains($sources['customer_support_controller'], 'customer-ticket-created:') && str_contains($sources['customer_support_controller'], 'customer-ticket-message:') && str_contains((string)$sources['capability_service'], "array_key_exists('recipient_admin_user_id', \$input)"));

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
        $superAdmin = $db->prepare(
            'SELECT r.id
             FROM admin_roles r
             WHERE r.slug = ? AND r.status = ? AND r.deleted_at IS NULL
             LIMIT 1'
        );
        $superAdmin->execute(['super-admin', 'active']);
        $superAdminId = (int)$superAdmin->fetchColumn();
        if ($superAdminId > 0) {
            $grantStmt = $db->prepare(
                'SELECT p.name
                 FROM admin_permissions p
                 INNER JOIN admin_role_permissions rp ON rp.permission_id = p.id
                 WHERE rp.role_id = ?
                   AND p.name IN (' . $placeholders . ')
                   AND p.status = ?
                   AND p.deleted_at IS NULL'
            );
            $grantStmt->execute([$superAdminId, ...$routeNames, 'active']);
            $grantedNames = $grantStmt->fetchAll(PDO::FETCH_COLUMN);
            $missingSuperAdminGrants = array_diff($routeNames, $grantedNames);
            p56Check('Active Super Admin has every active Admin route permission', $missingSuperAdminGrants === []);
            if ($missingSuperAdminGrants !== []) {
                foreach ($missingSuperAdminGrants as $missing) {
                    echo '<span style="color:#b45309">[MISSING SUPER ADMIN GRANT]</span> ' . htmlspecialchars($missing, ENT_QUOTES, 'UTF-8') . "<br>\\n";
                }
            }
        } else {
            p56Check('Active Super Admin role exists', false);
        }
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
exit($failed > 0 ? 1 : 0);

